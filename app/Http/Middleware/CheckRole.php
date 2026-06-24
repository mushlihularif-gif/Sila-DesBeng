<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Middleware untuk check authentication dan role-based access control.
     * 
     * Usage:
     * - middleware('auth') -> hanya cek sudah login
     * - middleware('role:admin') -> cek login + role admin
     * - middleware('role:user') -> cek login + role user
     * - middleware('role:user,guest') -> allow user atau guest (tidak login)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if 'guest' is allowed
        $allowGuest = in_array('guest', $roles);
        
        // Check if user is authenticated
        if (!Auth::check()) {
            // If guest is allowed, let them through
            if ($allowGuest) {
                return $next($request);
            }

            // Custom handle for Guest accessing Admin routes
            $adminRoles = ['admin', 'super_admin', 'admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt', 'lurah'];
            if (count(array_intersect($adminRoles, $roles)) > 0) {
                Log::warning('SECURITY: Unauthorized access attempt', [
                    'ip' => $request->ip(),
                    'path' => $request->path(),
                    'user_id' => null,
                    'user_agent' => $request->userAgent(),
                ]);

                 // Handle AJAX request
                 if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda tidak memiliki akses ke halaman ini'
                    ], 403);
                }
        
                // Handle web request
                return redirect()->route('beranda')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
            }
            
            // Handle AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi Anda telah berakhir. Silakan login kembali.'
                ], 401);
            }
            
            // Handle web request - redirect to beranda with login modal trigger
            Log::info('CheckRole: User not logged in, redirecting to beranda from ' . $request->path());
            return redirect()->route('beranda')->with('open_login_modal', true)->with('error', 'Sesi Anda telah berakhir karena tidak ada aktivitas, atau Anda belum login. Silakan login kembali.');
        }

        // User is authenticated
        $user = Auth::user();
        Log::info('CheckRole: User ' . $user->email . ' (Role: ' . $user->role . ') accessing ' . $request->path());
        
        // If no roles specified (only 'auth'), just check authentication (already passed above)
        if (empty($roles)) {
            return $next($request);
        }
        
        // Check if user has one of the required roles (excluding 'guest' from check)
        $requiredRoles = array_filter($roles, fn($role) => $role !== 'guest');
        
        // If no required roles (only 'guest' was specified), allow authenticated user too
        if (empty($requiredRoles)) {
            return $next($request);
        }
        
        foreach ($requiredRoles as $role) {
            if ($role === 'admin' || $role === 'lurah') {
                // 'admin' or 'lurah' pseudo-role matches any of the new admin hierarchy
                if (in_array($user->role, ['super_admin', 'admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt', 'admin', 'lurah'])) {
                    return $next($request);
                }
            } else if ($role === 'user') {
                // 'user' pseudo-role also matches admin_rw and admin_rt since they use frontend
                if (in_array($user->role, ['user', 'admin_rw', 'admin_rt'])) {
                    return $next($request);
                }
            } else if ($user->role === $role) {
                return $next($request);
            }
        }

        // User is authenticated but doesn't have required role
        Log::warning('SECURITY: Unauthorized access attempt', [
            'ip' => $request->ip(),
            'path' => $request->path(),
            'user_id' => Auth::id(),
            'user_agent' => $request->userAgent(),
        ]);

        // Special case: If admin tries to access user-only pages, redirect to admin dashboard
        // Pengecualian: admin_rw dan admin_rt diizinkan mengakses halaman frontend user
        $isAdminUser = in_array($user->role, ['super_admin', 'admin_kecamatan', 'admin_desa', 'admin', 'lurah']);
        if ($isAdminUser && (in_array('user', $requiredRoles) || $allowGuest)) {
            // Handle AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin harus menggunakan halaman admin',
                    'redirect' => route('admin.dashboard')
                ], 403);
            }
            
            // Handle web request
            return redirect()->route('admin.dashboard')->with('warning', 'Admin harus menggunakan halaman admin.');
        }
        
        // Handle AJAX request
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses ke halaman ini'
            ], 403);
        }

        // Handle web request
        return redirect()->route('beranda')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
    }
}
