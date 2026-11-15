<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Announcement;
use Illuminate\Support\Facades\Storage;

class WilayahAdminApiController extends Controller
{
    public function getDashboardStats(Request $request)
    {
        $user = $request->user();

        $wargaQuery = \App\Support\RegionalCitizens::query($user);
        // Compatibility fields remain empty for clients predating retirement of reports.
        $totalLaporan = 0; $laporanPending = 0; $laporanSelesai = 0; $oldestPending = null; $laporanTerbaru = collect();
        $slaStatus = 'nonaktif'; $oldestPendingMinutes = 0;

        // Ambil Data Warga
        $totalWarga = (clone $wargaQuery)->count();
        
        if ($oldestPending) {
            $oldestPendingMinutes = $oldestPending->created_at->diffInMinutes(now());
            // Misal target respon adalah 3 jam (180 menit)
            if ($oldestPendingMinutes > 180) {
                $slaStatus = 'kritis';
            } elseif ($oldestPendingMinutes > 120) {
                $slaStatus = 'peringatan';
            }
        }

        

        $user->load(['region', 'file']);
        $avatarUrl = null;
        if ($user->avatar) {
            $avatarUrl = str_starts_with($user->avatar, 'http') ? $user->avatar : url('storage/' . $user->avatar);
        } elseif ($user->file && Storage::disk('local')->exists($user->file->path)) {
            $avatarUrl = route('media.profile', ['filename' => $user->file->filename]);
        }

        $desa = $user->region;
        while ($desa && !in_array($desa->type, ['desa', 'kelurahan'], true)) $desa = $desa->parent;
        $desaName = $desa?->name;

        return response()->json([
            'status' => 'success',
            'data' => [
                'pengurus' => [
                    'name' => $user->name,
                    'role' => $user->role,
                    'rt' => $user->rt ? sprintf('%02d', (int)$user->rt) : null,
                    'rw' => $user->rw ? sprintf('%02d', (int)$user->rw) : null,
                    'avatar_url' => $avatarUrl,
                    'desa' => $desaName,
                ],
                'statistik' => [
                    'total_laporan' => $totalLaporan,
                    'laporan_baru' => $laporanPending,
                    'laporan_selesai' => $laporanSelesai,
                    'total_warga' => $totalWarga,
                ],
                'sla' => [
                    'status' => $slaStatus,
                    'oldest_pending_minutes' => $oldestPendingMinutes,
                    'has_pending' => $oldestPending ? true : false,
                ],
                'laporan_terbaru' => $laporanTerbaru->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'kategori' => $item->kategori,
                        'deskripsi' => $item->deskripsi,
                        'status' => $item->status,
                        'created_at' => $item->created_at,
                        'pelapor' => $item->user->name ?? 'Warga',
                        'avatar' => $item->user->avatar ?? null
                    ];
                })
            ]
        ]);
    }

    public function storeBerita(Request $request)
    {
        return response()->json(['message' => 'Publikasi langsung tidak lagi tersedia. Gunakan alur kiriman dan editor Kabar terbaru.', 'workspace_url' => route('content.index')], 409);
    }

}
