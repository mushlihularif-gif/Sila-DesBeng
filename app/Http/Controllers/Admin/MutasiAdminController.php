<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MutasiPenduduk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MutasiAdminController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        
        // Hanya membatasi jika admin desa/rt/rw. Super admin bebas.
        $region_id = in_array($admin->role, ['admin_desa', 'admin_rt', 'admin_rw']) ? $admin->region_id : null;
        
        // Pengajuan Keluar = Warga KITA yang sedang dalam proses KELUAR
        $pengajuanKeluar = MutasiPenduduk::with(['user', 'toRegion', 'fromRegion'])
            ->when($region_id, function($q) use ($region_id) {
                $q->where('from_region_id', $region_id);
            })
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Pengajuan Masuk = Warga LUAR yang sedang dalam proses MASUK
        $pengajuanMasuk = MutasiPenduduk::with(['user', 'fromRegion', 'toRegion'])
            ->when($region_id, function($q) use ($region_id) {
                $q->where('to_region_id', $region_id);
            })
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        // Riwayat (Disetujui / Ditolak)
        $riwayat = MutasiPenduduk::with(['user', 'fromRegion', 'toRegion'])
            ->when($region_id, function($q) use ($region_id) {
                $q->where(function($sub) use ($region_id) {
                    $sub->where('from_region_id', $region_id)
                        ->orWhere('to_region_id', $region_id);
                });
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10, ['*'], 'riwayat_page');

        // Semua Mutasi (Pengajuan Aktif + Riwayat)
        $semuaMutasi = MutasiPenduduk::with(['user', 'fromRegion', 'toRegion'])
            ->when($region_id, function($q) use ($region_id) {
                $q->where(function($sub) use ($region_id) {
                    $sub->where('from_region_id', $region_id)
                        ->orWhere('to_region_id', $region_id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'semua_page');

        // Daftar warga desa admin untuk mutasi keluar (ekspor)
        $regionIds = $region_id ? array_merge([(int)$region_id], \App\Models\Region::getDescendantIds($region_id)) : [];
        $wargaDesa = User::whereIn('role', ['user', 'warga'])
            ->when($region_id, function($q) use ($regionIds) {
                $q->whereIn('region_id', $regionIds);
            })
            ->get()
            ->sortBy(function($u) {
                return strtolower($u->name ?? '');
            });

        $counts = [
            'semua' => MutasiPenduduk::when($region_id, function($q) use ($region_id) {
                $q->where(function($sub) use ($region_id) {
                    $sub->where('from_region_id', $region_id)
                        ->orWhere('to_region_id', $region_id);
                });
            })->count(),
            'keluar' => $pengajuanKeluar->count(),
            'masuk' => $pengajuanMasuk->count(),
            'riwayat' => MutasiPenduduk::when($region_id, function($q) use ($region_id) {
                $q->where(function($sub) use ($region_id) {
                    $sub->where('from_region_id', $region_id)
                        ->orWhere('to_region_id', $region_id);
                });
            })->whereIn('status', ['approved', 'rejected'])->count(),
        ];

        return view('admin.warga.mutasi', compact(
            'pengajuanKeluar', 
            'pengajuanMasuk', 
            'riwayat', 
            'semuaMutasi', 
            'counts',
            'wargaDesa'
        ));
    }

    public function searchGlobal(Request $request)
    {
        $search = trim($request->get('q', ''));
        $region_id = $request->get('region_id');
        
        $query = User::whereIn('role', ['user', 'warga'])->with('region.parent');

        if ($region_id) {
            $regionIds = array_merge([(int)$region_id], \App\Models\Region::getDescendantIds($region_id));
            $query->whereIn('region_id', $regionIds);
        }

        // Jangan sertakan warga dari desa admin sendiri (karena sudah berada di desa admin)
        $admin = Auth::user();
        if ($admin && $admin->region_id) {
            $adminRegionIds = array_merge([(int)$admin->region_id], \App\Models\Region::getDescendantIds($admin->region_id));
            $query->whereNotIn('region_id', $adminRegionIds);
        }

        if ($search !== '') {
            $users = $query->get()->filter(function($u) use ($search) {
                $nik = (string)($u->nik ?? '');
                $name = (string)($u->name ?? '');
                return (stripos($nik, $search) !== false) || (stripos($name, $search) !== false);
            })->take(25);
        } else {
            $users = $query->take(25)->get();
        }

        $result = [];
        foreach($users as $u) {
            $nikDisplay = $u->nik ? $u->nik : 'Tanpa NIK';

            // Dapatkan info desa dan kecamatan asal warga
            $desaName = '';
            $desaId = null;
            $kecName = '';
            $kecId = null;

            if ($u->region) {
                if ($u->region->type === 'desa') {
                    $desaName = $u->region->name;
                    $desaId = $u->region->id;
                    if ($u->region->parent && $u->region->parent->type === 'kecamatan') {
                        $kecName = $u->region->parent->name;
                        $kecId = $u->region->parent->id;
                    }
                } elseif (in_array($u->region->type, ['rw', 'rt'])) {
                    $curr = $u->region->parent;
                    while ($curr && $curr->type !== 'desa' && $curr->parent) {
                        $curr = $curr->parent;
                    }
                    if ($curr && $curr->type === 'desa') {
                        $desaName = $curr->name;
                        $desaId = $curr->id;
                        if ($curr->parent && $curr->parent->type === 'kecamatan') {
                            $kecName = $curr->parent->name;
                            $kecId = $curr->parent->id;
                        }
                    }
                }
            }

            $result[] = [
                'id' => $u->nik ?: $u->id,
                'nik' => $u->nik ?: 'Tanpa NIK',
                'name' => $u->name,
                'text' => $nikDisplay . ' - ' . $u->name,
                'desa_id' => $desaId,
                'desa_name' => $desaName,
                'kec_id' => $kecId,
                'kec_name' => $kecName,
            ];
        }
        return response()->json(['results' => $result]);
    }

    public function searchLocal(Request $request)
    {
        $search = trim($request->get('q', ''));
        $admin = Auth::user();
        if(!$search) return response()->json(['results' => []]);

        $regionIds = array_merge([(int)$admin->region_id], \App\Models\Region::getDescendantIds($admin->region_id));

        $users = User::whereIn('role', ['user', 'warga'])
            ->whereIn('region_id', $regionIds)
            ->get()
            ->filter(function($u) use ($search) {
                $nik = (string)($u->nik ?? '');
                $name = (string)($u->name ?? '');
                return (stripos($nik, $search) !== false) || (stripos($name, $search) !== false);
            })
            ->take(20);

        $result = [];
        foreach($users as $u) {
            $nikDisplay = $u->nik ? $u->nik : 'Tanpa NIK';
            $result[] = [
                'id' => $u->id,
                'text' => $nikDisplay . ' - ' . $u->name
            ];
        }
        return response()->json(['results' => $result]);
    }

    public function tarikWarga(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'reason' => 'required|string'
        ]);

        $admin = Auth::user();
        
        // Cari user berdasarkan NIK atau ID, dan fallback ke dekripsi jika perlu
        $user = User::where('nik', $request->nik)->first();
        if (!$user && is_numeric($request->nik)) {
            $user = User::find($request->nik);
        }
        if (!$user) {
            $user = User::all()->first(function($u) use ($request) {
                return $u->nik === $request->nik;
            });
        }
        
        if (!$user) return redirect()->back()->with('error', 'Warga tidak ditemukan.');
        if ($user->region_id == $admin->region_id) return redirect()->back()->with('error', 'Warga ini sudah di desa Anda.');

        $existing = MutasiPenduduk::where('user_id', $user->id)->where('status', 'pending')->first();
        if ($existing) return redirect()->back()->with('error', 'Warga ini sedang dalam proses mutasi.');

        $mutasi = MutasiPenduduk::create([
            'user_id' => $user->id,
            'from_region_id' => $user->region_id,
            'to_region_id' => $admin->region_id,
            'status' => 'pending',
            'requested_by' => 'admin',
            'reason' => $request->reason,
        ]);

        \App\Models\AdminNotification::create([
            'type' => 'mutasi',
            'title' => 'Permintaan Tarik Warga',
            'message' => 'Desa pemohon mengajukan penarikan data warga ' . ($user->name ?? 'Warga') . '.',
            'reference_id' => $mutasi->id,
            'region_id' => $user->region_id,
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'Berhasil menarik data. Menunggu persetujuan desa asal.');
    }

    public function pushWarga(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'to_region_id' => 'required|exists:regions,id',
            'reason' => 'required|string'
        ]);

        $admin = Auth::user();
        $user = User::findOrFail($request->user_id);

        if ($user->region_id != $admin->region_id) return redirect()->back()->with('error', 'Warga ini bukan penduduk desa Anda.');
        if ($request->to_region_id == $admin->region_id) return redirect()->back()->with('error', 'Desa tujuan tidak boleh sama dengan desa asal.');

        $existing = MutasiPenduduk::where('user_id', $user->id)->where('status', 'pending')->first();
        if ($existing) return redirect()->back()->with('error', 'Warga ini sedang dalam proses mutasi.');

        $mutasi = MutasiPenduduk::create([
            'user_id' => $user->id,
            'from_region_id' => $admin->region_id,
            'to_region_id' => $request->to_region_id,
            'status' => 'pending',
            'requested_by' => 'admin_asal',
            'reason' => $request->reason,
        ]);

        \App\Models\AdminNotification::create([
            'type' => 'mutasi',
            'title' => 'Pengajuan Mutasi Masuk',
            'message' => 'Pengajuan pemindahan data warga ' . ($user->name ?? 'Warga') . ' ke desa Anda.',
            'reference_id' => $mutasi->id,
            'region_id' => $request->to_region_id,
            'is_read' => false,
        ]);

        return redirect()->back()->with('success', 'Berhasil melempar data warga. Menunggu persetujuan desa tujuan.');
    }

    public function approve($id)
    {
        $mutasi = MutasiPenduduk::findOrFail($id);
        $admin = Auth::user();

        $berhakApprove = false;
        if ($mutasi->requested_by == 'admin_asal') {
            if ($mutasi->to_region_id == $admin->region_id) $berhakApprove = true;
        } else {
            if ($mutasi->from_region_id == $admin->region_id) $berhakApprove = true;
        }
        if (in_array($admin->role, ['super_admin', 'admin_kecamatan'])) $berhakApprove = true;

        if (!$berhakApprove) abort(403, 'Anda tidak berhak menyetujui mutasi ini.');

        $mutasi->status = 'approved';
        if ($mutasi->ktp_image_path) {
            \Illuminate\Support\Facades\Storage::disk('private')->delete($mutasi->ktp_image_path);
            $mutasi->ktp_image_path = null;
        }
        $mutasi->save();

        $user = User::findOrFail($mutasi->user_id);
        $user->region_id = $mutasi->to_region_id;
        if ($mutasi->alamat_baru) $user->address = $mutasi->alamat_baru;
        $user->save();

        // Notifikasi ke warga pemohon
        \App\Services\NotificationService::notifyMutasiApproved($mutasi);

        return redirect()->back()->with('success', "Mutasi disetujui. Warga {$user->name} telah resmi berpindah.");
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['rejection_reason' => 'required|string']);
        $mutasi = MutasiPenduduk::findOrFail($id);
        $admin = Auth::user();

        $berhakApprove = false;
        if ($mutasi->requested_by == 'admin_asal') {
            if ($mutasi->to_region_id == $admin->region_id) $berhakApprove = true;
        } else {
            if ($mutasi->from_region_id == $admin->region_id) $berhakApprove = true;
        }
        if (in_array($admin->role, ['super_admin', 'admin_kecamatan'])) $berhakApprove = true;

        if (!$berhakApprove) abort(403, 'Anda tidak berhak menolak mutasi ini.');

        $mutasi->status = 'rejected';
        $mutasi->rejection_reason = $request->rejection_reason;
        
        if ($mutasi->ktp_image_path) {
            \Illuminate\Support\Facades\Storage::disk('private')->delete($mutasi->ktp_image_path);
            $mutasi->ktp_image_path = null;
        }
        $mutasi->save();

        // Notifikasi ke warga pemohon
        \App\Services\NotificationService::notifyMutasiRejected($mutasi, $request->rejection_reason);

        return redirect()->back()->with('success', 'Mutasi ditolak dan dibatalkan.');
    }

    public function showKtp($id)
    {
        $mutasi = MutasiPenduduk::findOrFail($id);
        $admin = Auth::user();

        if ($mutasi->from_region_id != $admin->region_id && $mutasi->to_region_id != $admin->region_id && !in_array($admin->role, ['super_admin', 'admin_kecamatan'])) {
            abort(403);
        }
        if (!$mutasi->ktp_image_path) abort(404, 'KTP tidak ditemukan atau sudah dihapus.');

        $path = storage_path('app/private/' . $mutasi->ktp_image_path);
        if (!file_exists($path)) abort(404, 'File fisik tidak ditemukan.');

        return response()->file($path);
    }
}
