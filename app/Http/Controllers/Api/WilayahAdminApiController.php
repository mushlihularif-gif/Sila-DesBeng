<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\User;
use App\Models\Announcement;
use Illuminate\Support\Facades\Storage;

class WilayahAdminApiController extends Controller
{
    public function getDashboardStats(Request $request)
    {
        $user = $request->user();

        // 1. Dapatkan Region Id (Kita bisa mengambil descendant Ids jika diperlukan, tapi untuk API mobile kita asumsikan berdasarkan rt dan rw)
        $regionId = $user->region_id;
        $rt = $user->rt;
        $rw = $user->rw;

        // Base Query untuk Laporan
        $laporanQuery = Laporan::where('region_id', $regionId);
        
        // Base Query untuk Warga
        $wargaQuery = User::where('region_id', $regionId)->where('role', 'user');

        if ($user->role === 'admin_rt') {
            $laporanQuery->where('rt', $rt)->where('rw', $rw);
            $wargaQuery->where('rt', $rt)->where('rw', $rw);
        } elseif ($user->role === 'admin_rw') {
            $laporanQuery->where('rw', $rw);
            $wargaQuery->where('rw', $rw);
        }

        // Ambil Data Statistik Laporan
        $totalLaporan = (clone $laporanQuery)->count();
        $laporanPending = (clone $laporanQuery)->where('status', 'Pending')->count();
        $laporanSelesai = (clone $laporanQuery)->where('status', 'Selesai')->count();

        // Ambil Data Warga
        $totalWarga = (clone $wargaQuery)->count();
        
        // Cek SLA (Target Respon)
        // Kita hitung berdasarkan laporan pending tertua
        // Jika ada laporan pending, SLA = (Waktu sekarang) - (Waktu laporan dibuat)
        $oldestPending = (clone $laporanQuery)->where('status', 'Pending')->orderBy('created_at', 'asc')->first();
        $slaStatus = 'aman';
        $oldestPendingMinutes = 0;
        
        if ($oldestPending) {
            $oldestPendingMinutes = $oldestPending->created_at->diffInMinutes(now());
            // Misal target respon adalah 3 jam (180 menit)
            if ($oldestPendingMinutes > 180) {
                $slaStatus = 'kritis';
            } elseif ($oldestPendingMinutes > 120) {
                $slaStatus = 'peringatan';
            }
        }

        // Ambil data laporan terbaru (Feed/Aktivitas)
        $laporanTerbaru = (clone $laporanQuery)->with('user:id,name,avatar')->orderBy('created_at', 'desc')->take(5)->get();

        $user->load(['region', 'file']);
        $avatarUrl = null;
        if ($user->avatar) {
            $avatarUrl = str_starts_with($user->avatar, 'http') ? $user->avatar : url('storage/' . $user->avatar);
        } elseif ($user->file && Storage::disk('local')->exists($user->file->path)) {
            $avatarUrl = route('media.profile', ['filename' => $user->file->filename]);
        }

        $desaName = $user->region ? $user->region->name : 'Desa Pematang';

        return response()->json([
            'status' => 'success',
            'data' => [
                'pengurus' => [
                    'name' => $user->name,
                    'role' => $user->role,
                    'rt' => $user->rt ? sprintf('%02d', (int)$user->rt) : '02',
                    'rw' => $user->rw ? sprintf('%02d', (int)$user->rw) : '01',
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
}
