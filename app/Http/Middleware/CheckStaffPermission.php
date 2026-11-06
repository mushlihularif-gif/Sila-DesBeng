<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckStaffPermission
{
    // Nama alias dipertahankan agar URL dan grup rute lama tetap kompatibel.
    public function handle(Request $request, Closure $next, $unitKey)
    {
        $user = $request->user();
        $shared = ['platform_banner', 'platform_aktivitas'];
        $allowed = $user && $user->hasSupportedRole() && (
            $user->hasUnitPermission($unitKey)
            || $user->hasPlatformPermission($unitKey)
            || ($user->role === 'admin_desa' && in_array($unitKey, $shared, true))
        );
        abort_unless($allowed, 403, 'Anda tidak memiliki izin mengelola layanan ini.');
        return $next($request);
    }
}
