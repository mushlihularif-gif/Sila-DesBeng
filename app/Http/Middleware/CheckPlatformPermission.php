<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Penjaga modul Sistem Platform (dashboard Super Admin).
 *
 * Berbeda dari CheckStaffPermission yang menjaga unit layanan per wilayah,
 * penjaga ini melindungi modul tingkat platform: monitoring, kredensial API,
 * log keamanan, biaya server, dan kotak masuk email instansi.
 *
 * Aturannya SENGAJA ketat:
 *   - super_admin           -> selalu boleh
 *   - staff berizin platform -> boleh, hanya untuk modul yang dicentang
 *   - siapa pun selain itu   -> ditolak, termasuk admin kabupaten/kecamatan/desa
 *
 * Perhatikan bahwa User::hasUnitPermission() memberi lampu hijau ke semua role
 * admin lewat isSuperAdmin() yang penamaannya menyesatkan. Perilaku itu TIDAK
 * boleh ditiru di sini, karena akan membuka halaman kredensial API ke seluruh
 * admin wilayah. Karena itu pemeriksaannya memakai hasPlatformPermission().
 */
class CheckPlatformPermission
{
    public function handle(Request $request, Closure $next, string $permissionKey)
    {
        $user = auth()->user();

        if ($user && $user->hasPlatformPermission($permissionKey)) {
            return $next($request);
        }

        Log::warning('SECURITY: Percobaan akses modul platform tanpa izin', [
            'ip'      => $request->ip(),
            'path'    => $request->path(),
            'izin'    => $permissionKey,
            'user_id' => $user?->id,
            'role'    => $user?->role,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk modul platform ini.',
            ], 403);
        }

        return redirect()->route('admin.dashboard')
            ->with('error', 'Anda tidak memiliki izin untuk modul platform ini.');
    }
}
