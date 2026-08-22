<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminWilayahKyc
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->verification_status !== 'verified') {
            return redirect()->route('beranda')
                ->with('show_kyc_modal', true)
                ->with('error_kyc_wilayah', 'Sebagai Pengelola Wilayah (RT/RW), Anda wajib melakukan verifikasi KTP terlebih dahulu.');
        }
        return $next($request);
    }
}