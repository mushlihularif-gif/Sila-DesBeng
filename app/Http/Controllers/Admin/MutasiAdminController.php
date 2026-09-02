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
        
        // Hanya membatasi jika admin desa. Super admin bebas.
        $region_id = in_array($admin->role, ['admin_desa', 'admin_rt', 'admin_rw']) ? $admin->region_id : null;
        
        // Pengajuan Keluar = Warga KITA yang sedang dalam proses KELUAR
        $pengajuanKeluar = MutasiPenduduk::with(['user', 'toRegion'])
            ->where('from_region_id', $region_id)
            ->where('status', 'pending')
            ->get();
            
        // Pengajuan Masuk = Warga LUAR yang sedang dalam proses MASUK
        $pengajuanMasuk = MutasiPenduduk::with(['user', 'fromRegion'])
            ->where('to_region_id', $region_id)
            ->where('status', 'pending')
            ->get();

        // Riwayat
        $riwayat = MutasiPenduduk::with(['user', 'fromRegion', 'toRegion'])
            ->where(function($q) use ($region_id) {
                $q->where('from_region_id', $region_id)
                  ->orWhere('to_region_id', $region_id);
            })
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('admin.warga.mutasi', compact('pengajuanKeluar', 'pengajuanMasuk', 'riwayat'));
    }

    public function searchGlobal(Request $request)
    {
        $search = $request->get('q');
        $region_id = $request->get('region_id');
        
        if(!$search) return response()->json([]);

        $query = User::where('role', 'warga')
            ->where(function($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });

        if ($region_id) {
            $query->where('region_id', $region_id);
        }

        $users = $query->limit(20)->get();

        $result = [];
        foreach($users as $u) {
            $result[] = [
                'id' => $u->nik, // Use NIK as ID for the existing logic
                'text' => $u->nik . ' - ' . $u->name
            ];
        }
        return response()->json(['results' => $result]);
    }

    public function searchLocal(Request $request)
    {
        $search = $request->get('q');
        $admin = Auth::user();
        if(!$search) return response()->json([]);

        $users = User::where('role', 'warga')
            ->where('region_id', $admin->region_id)
            ->where(function($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get();

        $result = [];
        foreach($users as $u) {
            $result[] = [
                'id' => $u->id, // Use User ID for push
                'text' => $u->nik . ' - ' . $u->name
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
        $user = User::where('nik', $request->nik)->first();
        
        if (!$user) return redirect()->back()->with('error', 'Warga tidak ditemukan.');
        if ($user->region_id == $admin->region_id) return redirect()->back()->with('error', 'Warga ini sudah di desa Anda.');

        $existing = MutasiPenduduk::where('user_id', $user->id)->where('status', 'pending')->first();
        if ($existing) return redirect()->back()->with('error', 'Warga ini sedang dalam proses mutasi.');

        MutasiPenduduk::create([
            'user_id' => $user->id,
            'from_region_id' => $user->region_id,
            'to_region_id' => $admin->region_id,
            'status' => 'pending',
            'requested_by' => 'admin',
            'reason' => $request->reason,
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

        MutasiPenduduk::create([
            'user_id' => $user->id,
            'from_region_id' => $admin->region_id,
            'to_region_id' => $request->to_region_id,
            'status' => 'pending',
            'requested_by' => 'admin_asal',
            'reason' => $request->reason,
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
