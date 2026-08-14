<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        // MATRIKS ESKALASI: Cek batas SLA pelaporan warga setiap jam
        $schedule->command('laporan:auto-escalate')->hourly();
    })
    ->withMiddleware(function (Middleware $middleware) {
        // Register global middleware (Skenario 1, 2: Security Headers & Input Sanitization)
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\SanitizeInput::class);
        $middleware->append(\App\Http\Middleware\DDoSProtection::class); // Advanced DDoS Protection

        // Register custom middleware aliases
        $middleware->alias([
            'auth' => \App\Http\Middleware\CheckRole::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'admin' => \App\Http\Middleware\CheckRole::class, // For backward compatibility
            'region.service' => \App\Http\Middleware\CheckRegionService::class,
            'staff.permission' => \App\Http\Middleware\CheckStaffPermission::class,
        ]);

        // Global rate limiting for all web routes (Skenario 21: DoS defense)
        // $middleware->throttleWithRedis = false; // Use default cache

        $middleware->validateCsrfTokens(except: [
            'payment/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect('/beranda')->with('error', 'Sesi halaman telah berakhir (Kedaluwarsa). Silakan ulangi tindakan Anda atau login kembali.');
        });
    })->create();
