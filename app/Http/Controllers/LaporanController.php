<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    public function index()
    {
        $laporans = Laporan::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('user.laporan.index', compact('laporans'));
    }

    public function create()
    {
        $user = auth()->user();
        
        // Ambil desa_id user (parent dari RT/RW user)
        $desaId = null;
        if ($user->region_id) {
            $userRegion = \App\Models\Region::find($user->region_id);
            if ($userRegion) {
                // Naik ke atas hingga menemukan region bertipe 'desa'
                $temp = $userRegion;
                while ($temp) {
                    if ($temp->type === 'desa' || $temp->type === 'kelurahan') {
                        $desaId = $temp->id;
                        break;
                    }
                    $temp = $temp->parent;
                }
                // Fallback: jika tidak ketemu desa, gunakan parent dari RW
                if (!$desaId && $userRegion->type === 'rt') {
                    $rwRegion = $userRegion->parent;
                    if ($rwRegion && $rwRegion->parent_id) {
                        $desaId = $rwRegion->parent_id;
                    }
                } elseif (!$desaId && $userRegion->type === 'rw') {
                    $desaId = $userRegion->parent_id;
                }
            }
        }

        // Ambil semua RW dan RT di bawah desa ini, dengan status admin-nya
        $allRWData = collect();
        $allRTData = collect();
        $hasAdminRT = false;
        $hasAdminRW = false;

        if ($desaId) {
            // Ambil semua RW di desa
            $allRWs = \App\Models\Region::where('parent_id', $desaId)
                ->where('type', 'rw')
                ->get();

            foreach ($allRWs as $rw) {
                // Cek apakah RW ini punya admin
                $hasAdmin = User::where('role', 'admin_rw')
                    ->where('region_id', $rw->id)
                    ->exists();
                
                if ($hasAdmin) $hasAdminRW = true;
                
                $rw->has_admin = $hasAdmin;
                $allRWData->push($rw);

                // Ambil semua RT di bawah RW ini
                $rts = \App\Models\Region::where('parent_id', $rw->id)
                    ->where('type', 'rt')
                    ->get();

                foreach ($rts as $rt) {
                    $hasRtAdmin = User::where('role', 'admin_rt')
                        ->where('region_id', $rt->id)
                        ->exists();
                    
                    if ($hasRtAdmin) $hasAdminRT = true;

                    // Tambahkan info RW parent ke RT untuk label dropdown
                    $rt->rw_name = $rw->name;
                    $rt->has_admin = $hasRtAdmin;
                    $allRTData->push($rt);
                }
            }
        }

        return view('user.laporan.create', compact(
            'hasAdminRT', 'hasAdminRW', 'allRTData', 'allRWData'
        ));
    }

    public function store(Request $request)
    {
        // Validasi
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string|min:20',
            'kategori' => 'required|string',
            'lokasi' => 'required|string|max:255',
            'latitude' => 'nullable|string|max:50',
            'longitude' => 'nullable|string|max:50',
            'tujuan_laporan' => 'required|in:rt,rw,desa',
            'target_region_id' => 'nullable|integer|exists:regions,id',
            'bukti' => 'nullable|array|max:3',
            'bukti.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();

        // Tentukan region_id tujuan laporan
        // Jika user memilih RT/RW dari dropdown, gunakan target_region_id
        // Jika tidak (pilih Desa), fallback ke region_id domisili user
        $targetRegionId = $validated['target_region_id'] ?? $user->region_id;

        // Prepare data TANPA bukti dulu
        $data = [
            'user_id' => $user->id,
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'],
            'kategori' => $validated['kategori'],
            'lokasi' => $validated['lokasi'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'tujuan_laporan' => $validated['tujuan_laporan'],
            'status' => 'Pending',
            'rw' => $user->rw,
            'rt' => $user->rt,
            'rw_number' => $user->rw,
            'rt_number' => $user->rt,
            'region_id' => $targetRegionId,
        ];

        // Upload bukti SETELAH validasi
        // Upload bukti SETELAH validasi
        if ($request->hasFile('bukti')) {
            $buktiPaths = [];
            // PATH KE ROOT SUBDOMAIN (INI KUNCI)
            $destination = $_SERVER['DOCUMENT_ROOT'] . '/storage/laporan';
            if (!is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            foreach ($request->file('bukti') as $file) {
                if ($file->isValid()) {
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '_' . Str::random(16) . '.' . $extension;
                    $file->move($destination, $filename);
                    
                    // SIMPAN RELATIVE URL
                    $buktiPaths[] = 'laporan/' . $filename;
                }
            }

            if (!empty($buktiPaths)) {
                $data['bukti'] = json_encode($buktiPaths);
            }
        }






        // Simpan laporan
        $laporan = Laporan::create($data);

        Log::info('Laporan created', [
            'id' => $laporan->id,
            'has_bukti' => isset($data['bukti']),
            'bukti_value' => $data['bukti'] ?? 'null',
        ]);

        // ✅ Smart Routing - Kirim notifikasi berdasarkan region tujuan yang dipilih
        try {
            $targetRegion = \App\Models\Region::find($targetRegionId);
            $regionName = $targetRegion ? $targetRegion->name : 'Unknown';

            // Kumpulkan region_id dari target ke kabupaten (untuk fallback)
            $regionIds = [];
            $tempRegion = $targetRegion;
            while ($tempRegion) {
                $regionIds[] = $tempRegion->id;
                $tempRegion = $tempRegion->parent;
            }

            $targetAdmins = collect();
            $actualDestination = $validated['tujuan_laporan']; // rt, rw, atau desa

            // STEP 1: Cek admin di region tujuan yang dipilih (RT)
            if ($actualDestination === 'rt' && $targetRegionId) {
                $adminRt = User::where('role', 'admin_rt')
                    ->where('region_id', $targetRegionId)
                    ->get();

                if ($adminRt->isNotEmpty()) {
                    $targetAdmins = $adminRt;
                    Log::info('Laporan dikirim ke Admin RT', ['region_id' => $targetRegionId]);
                } else {
                    // RT belum ada admin, eskalasi otomatis ke RW
                    $actualDestination = 'rw';
                    Log::info('Admin RT belum ada, eskalasi otomatis ke RW');
                }
            }

            // STEP 2: Cek ketersediaan Admin RW
            if ($targetAdmins->isEmpty() && in_array($actualDestination, ['rw', 'rt'])) {
                // Cari RW region: jika target adalah RT, naik ke parent (RW)
                $rwRegionId = $targetRegionId;
                if ($targetRegion && $targetRegion->type === 'rt') {
                    $rwRegionId = $targetRegion->parent_id;
                }

                if ($rwRegionId) {
                    $adminRw = User::where('role', 'admin_rw')
                        ->where('region_id', $rwRegionId)
                        ->get();

                    if ($adminRw->isNotEmpty()) {
                        $targetAdmins = $adminRw;
                        $actualDestination = 'rw';
                        Log::info('Laporan dikirim ke Admin RW', ['region_id' => $rwRegionId]);
                    } else {
                        $actualDestination = 'desa';
                        Log::info('Admin RW belum ada, eskalasi otomatis ke Desa');
                    }
                } else {
                    $actualDestination = 'desa';
                }
            }

            // STEP 3: Fallback ke Admin Desa / Super Admin
            if ($targetAdmins->isEmpty()) {
                $targetAdmins = User::whereIn('role', ['admin', 'super_admin', 'admin_desa'])
                    ->whereIn('region_id', $regionIds)
                    ->get();
                $actualDestination = 'desa';
                Log::info('Laporan dikirim ke Admin Desa (fallback)');
            }

            // Update tujuan laporan jika berubah karena eskalasi otomatis
            if ($actualDestination !== $validated['tujuan_laporan']) {
                $laporan->update(['tujuan_laporan' => $actualDestination]);
            }

            // Kirim notifikasi ke admin yang sudah ditentukan
            foreach ($targetAdmins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'laporan_id' => $laporan->id,
                    'type' => 'laporan_baru',
                    'title' => 'Laporan Baru Masuk',
                    'message' => "User {$user->name} telah melakukan pelaporan dari {$regionName}. Kategori: {$laporan->kategori}",
                    'link' => '/admin/laporan/' . $laporan->id,
                    'icon' => 'fas fa-file-alt',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Notif error: ' . $e->getMessage());
        }

        return redirect()->route('user.laporan.show', $laporan->id)
            ->with('success', 'Laporan berhasil dibuat!');
    }

       public function exportPdf($id)
{
    // Pastikan user login
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $laporan = Laporan::with('user')->findOrFail($id);

    // Cast ke int untuk menghindari null / string mismatch
    if ((int) $laporan->user_id !== (int) auth()->user()->id) {
        abort(403);
    }

    // Fetch QR Code from Google Charts API safely bypassing local SSL issues
    $qrData = urlencode(url('/validasi/laporan/' . $laporan->id . '?token=' . hash_hmac('sha256', $laporan->id . $laporan->created_at, config('app.key'))));
    $qrUrl = "https://chart.googleapis.com/chart?chs=80x80&cht=qr&chl=" . $qrData;
    
    try {
        $qrImage = \Illuminate\Support\Facades\Http::withoutVerifying()->timeout(10)->get($qrUrl)->body();
        $qrBase64 = base64_encode($qrImage);
    } catch (\Exception $e) {
        $qrBase64 = null;
    }

    return Pdf::loadView('pdf.bukti_laporan', [
        'laporan' => $laporan,
        'handler_name' => '',
        'waktu_cetak' => now()->format('d F Y, H:i'),
        'qrBase64' => $qrBase64
    ])->download('Bukti_Laporan_'.$laporan->id.'.pdf');
}

public function show($id)
{
    $userId = auth()->id();

    if (!$userId) {
        abort(403);
    }

    $laporan = Laporan::with(['user', 'rating'])
        ->where('id', $id)
        ->where('user_id', $userId)
        ->firstOrFail();

    return view('user.laporan.show', compact('laporan'));
}


    public function destroy(Laporan $laporan)
{
    // Pastikan login
    if (!auth()->check()) {
        abort(403, 'Unauthorized');
    }

    // Pastikan pemilik
    if ((int) $laporan->user_id !== (int) auth()->id()) {
        abort(403, 'Anda tidak berhak menghapus laporan ini.');
    }

    // Status harus Pending (WAJIB KONSISTEN)
    if ($laporan->status !== 'Pending') {
        return back()->with('error', '❌ Laporan yang sudah diproses tidak dapat dihapus.');
    }

    // Batas waktu 24 jam
    if ($laporan->created_at->diffInHours(now()) >= 24) {
        return back()->with('error', '❌ Laporan sudah melewati batas waktu penghapusan (24 jam).');
    }

    // Hapus file bukti
    if ($laporan->bukti) {
        $filePath = public_path('storage/' . $laporan->bukti);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Hapus notifikasi terkait
    Notification::where('laporan_id', $laporan->id)->delete();

    // Hapus laporan
    $laporan->delete();

    return redirect()
        ->route('user.laporan.index')
        ->with('success', '✅ Laporan berhasil dihapus.');
}


}
