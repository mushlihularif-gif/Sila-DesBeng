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
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . Str::random(16) . '.' . $extension;
        
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

        // Kirim notifikasi berjenjang
        try {
            $regionIds = [];
            $currentRegion = Region::find($user->region_id);
            $regionName = $currentRegion ? $currentRegion->name : 'Unknown';
            while ($currentRegion) {
                $regionIds[] = $currentRegion->id;
                $currentRegion = $currentRegion->parent;
            }

            $admins = User::whereIn('role', ['admin', 'super_admin', 'admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt', 'lurah'])
                ->whereIn('region_id', $regionIds)
                ->get();

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'laporan_id' => $laporan->id,
                    'type' => 'laporan_baru',
                    'title' => '📋 Laporan Baru Masuk',
                    'message' => "User {$user->name} telah melakukan pelaporan dari {$regionName}. Kategori: {$laporan->kategori}",
                    'link' => '/admin/laporan/' . $laporan->id,
                    'icon' => '📋',
                ]);
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

        if ($user->role === 'rt') {
            $query->where('rt', $user->rt)
                  ->where('rw', $user->rw);
        } elseif ($user->role === 'rw') {
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
                'catatan_rw' => $user->role === 'rw' ? $catatan : $laporan->catatan_rw,
                'catatan_rt' => $user->role === 'rt' ? $catatan : $laporan->catatan_rt,
                'rt_handler_id' => $user->role === 'rt' ? $user->id : $laporan->rt_handler_id,
                'rw_handler_id' => $user->role === 'rw' ? $user->id : $laporan->rw_handler_id,
            ]);
        } elseif ($action === 'reject') {
            $laporan->update(['status' => 'Ditolak', 'catatan_admin' => $catatan]);
        } elseif ($action === 'resolve') {
            $laporan->update(['status' => 'Selesai', 'catatan_admin' => $catatan]);
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
        } catch (\Exception $e) {
            Log::error('Notif error via API: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Status laporan berhasil diperbarui',
            'data' => $laporan
        ], 200);
    }
}
