<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckStaffPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $unitKey
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $unitKey)
    {
        $user = auth()->user();

        // Jika user tidak terautentikasi atau merupakan staff yang tidak memiliki izin untuk unit ini
        if ($user && $user->isStaff() && !$user->hasUnitPermission($unitKey)) {
            // Cek jika request adalah AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Akses ditolak. Anda tidak memiliki izin untuk mengelola unit ini.'], 403);
            }
            return redirect()->route('admin.dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengelola unit ini.');
        }

        return $next($request);
    }
}
