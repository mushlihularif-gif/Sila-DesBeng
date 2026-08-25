<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\Notification;
use App\Models\User;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    /**
     * Get all reports by the authenticated user
     */
    public function getMyReports(Request $request)
    {
        $user = auth('sanctum')->user();
        
        $reports = Laporan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $reports->transform(function ($report) {
            $report->image_url = $report->foto_bukti ? asset('storage/' . $report->foto_bukti) : null;
            return $report;
        });
            
        return response()->json([
            'status' => 'success',
            'data' => $reports
        ]);
    }

    /**
     * Store a newly created report in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'required|string|min:20',
            'kategori' => 'required|string',
            'lokasi' => 'nullable|string|max:255',
            'tujuan_laporan' => 'required|in:rt,rw,desa',
            'bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = auth()->user();

        // Prepare data TANPA bukti dulu
        $data = [
            'user_id' => $user->id,
            'nama' => $validated['nama'],
            'deskripsi' => $validated['deskripsi'],
            'kategori' => $validated['kategori'],
            'lokasi' => $validated['lokasi'] ?? null,
            'tujuan_laporan' => $validated['tujuan_laporan'],
            'status' => 'Pending',
            'rw' => $user->rw,
            'rt' => $user->rt,
            'rw_number' => $user->rw,
            'rt_number' => $user->rt,
            'region_id' => $user->region_id,
        ];

        // Upload bukti
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
        
            if ($file->isValid()) {
                // SECURITY HARDENING: Gunakan ->extension() bawaan Laravel yang mengecek MIME type isi file
                // BUKAN ->getClientOriginalExtension() yang bisa dimanipulasi attacker
                $extension = strtolower($file->extension());
                
                // Strict whitelist extension
                $allowedExtensions = ['jpg', 'jpeg', 'png'];
                if (!in_array($extension, $allowedExtensions)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Format file tidak valid. Keamanan sistem mendeteksi anomali.'
                    ], 403);
                }

                $filename = time() . '_' . Str::random(24) . '.' . $extension;
        
                // PATH KE ROOT SUBDOMAIN (Sesuai Web)
                $destination = $_SERVER['DOCUMENT_ROOT'] . '/storage/laporan';
                if (!is_dir($destination)) {
                    mkdir($destination, 0755, true);
                }
        
                $file->move($destination, $filename);
        
                // SIMPAN RELATIVE URL
                $data['bukti'] = 'laporan/' . $filename;
            }
        }

        // Simpan laporan
        $laporan = Laporan::create($data);

        Log::info('✅ Laporan created via API', [
            'id' => $laporan->id,
            'has_bukti' => isset($data['bukti']),
        ]);

        // ✅ Fix 4: Smart Routing - Kirim notifikasi berdasarkan ketersediaan admin
        try {
            $regionName = '';
            $currentRegion = Region::find($user->region_id);
            $regionName = $currentRegion ? $currentRegion->name : 'Unknown';

            // Kumpulkan region_id dari desa ke kabupaten (untuk fallback)
            $regionIds = [];
            $tempRegion = $currentRegion;
            while ($tempRegion) {
                $regionIds[] = $tempRegion->id;
                $tempRegion = $tempRegion->parent;
            }

            $targetAdmins = collect();
            $actualDestination = $validated['tujuan_laporan'];

            // STEP 1: Cek ketersediaan Admin RT
            if ($actualDestination === 'rt' && $user->rt && $user->rw) {
                $adminRt = User::where('role', 'admin_rt')
                    ->where('region_id', $user->region_id)
                    ->where('rt', $user->rt)
                    ->where('rw', $user->rw)
                    ->get();

                if ($adminRt->isNotEmpty()) {
                    $targetAdmins = $adminRt;
                    Log::info('[API] Laporan dikirim ke Admin RT', ['rt' => $user->rt, 'rw' => $user->rw]);
                } else {
                    $actualDestination = 'rw';
                    Log::info('[API] Admin RT belum ada, eskalasi ke RW');
                }
            }

            // STEP 2: Cek ketersediaan Admin RW
            if ($targetAdmins->isEmpty() && in_array($actualDestination, ['rw', 'rt']) && $user->rw) {
                $adminRw = User::where('role', 'admin_rw')
                    ->where('region_id', $user->region_id)
                    ->where('rw', $user->rw)
                    ->get();

                if ($adminRw->isNotEmpty()) {
                    $targetAdmins = $adminRw;
                    $actualDestination = 'rw';
                    Log::info('[API] Laporan dikirim ke Admin RW', ['rw' => $user->rw]);
                } else {
                    $actualDestination = 'desa';
                    Log::info('[API] Admin RW belum ada, eskalasi ke Desa');
                }
            }

            // STEP 3: Fallback ke Admin Desa
            if ($targetAdmins->isEmpty()) {
                $targetAdmins = User::whereIn('role', ['admin', 'super_admin', 'admin_desa'])
                    ->whereIn('region_id', $regionIds)
                    ->get();
                $actualDestination = 'desa';
                Log::info('[API] Laporan dikirim ke Admin Desa (fallback)');
            }

            // Update tujuan laporan jika berubah
            if ($actualDestination !== $validated['tujuan_laporan']) {
                $laporan->update(['tujuan_laporan' => $actualDestination]);
            }

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

                if ($admin->fcm_token) {
                    $firebase = new \App\Services\FirebaseService();
                    $firebase->sendPushNotification(
                        $admin->fcm_token,
                        'Laporan Baru Masuk',
                        "User {$user->name} telah melakukan pelaporan. Kategori: {$laporan->kategori}"
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Notif error via API: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan berhasil dibuat',
            'data' => $laporan
        ], 200);
    }

    public function getAdminReports(Request $request)
    {
        $user = $request->user();
        
        $query = Laporan::with('user')->where('region_id', $user->region_id);

        if ($user->role === 'admin_rt') {
            $query->where('rt', $user->rt)
                  ->where('rw', $user->rw);
        } elseif ($user->role === 'admin_rw') {
            $query->where('rw', $user->rw);
        }

        $reports = $query->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $reports
        ], 200);
    }

    public function forwardReport(Request $request, $id)
    {
        $laporan = Laporan::findOrFail($id);
        $user = $request->user();
        $action = $request->action; // 'forward_rw', 'forward_desa', 'reject', 'resolve'
        $catatan = $request->catatan;

        if ($action === 'forward_rw') {
            $laporan->update([
                'status' => 'Diteruskan ke RW',
                'escalation_level' => 2,
                'catatan_rt' => $catatan,
                'rt_handler_id' => $user->id,
                'escalated_to_rw_at' => now(),
            ]);
        } elseif ($action === 'forward_desa') {
            $laporan->update([
                'status' => 'Diteruskan ke Desa',
                'escalation_level' => 3,
                'catatan_rw' => $user->role === 'admin_rw' ? $catatan : $laporan->catatan_rw,
                'catatan_rt' => $user->role === 'admin_rt' ? $catatan : $laporan->catatan_rt,
                'rt_handler_id' => $user->role === 'admin_rt' ? $user->id : $laporan->rt_handler_id,
                'rw_handler_id' => $user->role === 'admin_rw' ? $user->id : $laporan->rw_handler_id,
            ]);
        } elseif ($action === 'reject') {
            $laporan->update(['status' => 'Ditolak', 'catatan_admin' => $catatan]);
        } elseif ($action === 'resolve') {
            $laporan->update(['status' => 'Selesai', 'catatan_admin' => $catatan]);
        } elseif ($action === 'process') {
            $laporan->update(['status' => 'Diproses', 'catatan_admin' => $catatan]);
        } elseif ($action === 'process_rw') {
            $laporan->update(['status' => 'Diproses RW', 'catatan_admin' => $catatan]);
        }

        // Notify user
        try {
            Notification::create([
                'user_id' => $laporan->user_id,
                'laporan_id' => $laporan->id,
                'type' => 'update_laporan',
                'title' => 'Status Laporan Diperbarui',
                'message' => "Laporan Anda telah diperbarui menjadi: {$laporan->status}",
                'link' => '/laporan/' . $laporan->id,
                'icon' => '📝',
            ]);

            // Send FCM Push Notification
            $laporanOwner = User::find($laporan->user_id);
            if ($laporanOwner && $laporanOwner->fcm_token) {
                $firebase = new \App\Services\FirebaseService();
                $firebase->sendPushNotification(
                    $laporanOwner->fcm_token,
                    'Status Laporan Diperbarui',
                    "Laporan Anda telah diperbarui menjadi: {$laporan->status}"
                );
            }
        } catch (\Exception $e) {
            Log::error('Notif error via API: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Status laporan berhasil diperbarui',
            'data' => $laporan
        ], 200);
    }

    /**
     * Get comprehensive report detail by ID
     */
    public function show($id)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $laporan = Laporan::with(['user', 'admin'])->find($id);

        if (!$laporan) {
            return response()->json(['status' => 'error', 'message' => 'Laporan tidak ditemukan'], 404);
        }

        if ((int) $laporan->user_id !== (int) $user->id && !in_array($user->role, ['admin_desa', 'superadmin', 'admin_rt', 'admin_rw'])) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak'], 403);
        }

        $buktiUrls = [];
        if (!empty($laporan->bukti_array)) {
            foreach ($laporan->bukti_array as $b) {
                $buktiUrls[] = asset('storage/' . $b);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $laporan->id,
                'user_id' => $laporan->user_id,
                'nama' => $laporan->nama,
                'judul_laporan' => $laporan->judul_laporan,
                'deskripsi' => $laporan->deskripsi,
                'kategori' => $laporan->kategori,
                'lokasi' => $laporan->lokasi,
                'latitude' => $laporan->latitude,
                'longitude' => $laporan->longitude,
                'status' => $laporan->status,
                'tingkat_prioritas' => $laporan->tingkat_prioritas ?? 'Normal',
                'escalation_level' => $laporan->escalation_level,
                'catatan_rt' => $laporan->catatan_rt,
                'catatan_rw' => $laporan->catatan_rw,
                'catatan_admin' => $laporan->catatan_admin,
                'rt_number' => $laporan->rt_number ?? $laporan->rt,
                'rw_number' => $laporan->rw_number ?? $laporan->rw,
                'bukti_urls' => $buktiUrls,
                'created_at' => $laporan->created_at->format('d F Y, H:i') . ' WIB',
                'updated_at' => $laporan->updated_at->format('d F Y, H:i') . ' WIB',
                'handler_name' => $laporan->admin ? $laporan->admin->name : 'Pemerintah Desa Bengkalis',
                'user' => [
                    'name' => $laporan->user ? $laporan->user->name : $laporan->nama,
                    'nik' => $laporan->user ? $laporan->user->nik : null,
                    'is_verified' => $laporan->user && $laporan->user->kycVerification && $laporan->user->kycVerification->status === 'approved',
                ]
            ]
        ]);
    }
}
