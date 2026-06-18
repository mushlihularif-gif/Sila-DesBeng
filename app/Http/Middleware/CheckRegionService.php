<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RegionService;

class CheckRegionService
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $serviceSlug
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $serviceSlug)
    {
        $regionId = null;

        // Tentukan Region ID berdasarkan status login
        if (auth()->check()) {
            $regionId = auth()->user()->region_id;
        } else {
            $regionId = $request->query('region_id');
        }

        // Jika tidak ada region ID sama sekali, lemparkan ke halaman direktori untuk memilih desa
        if (!$regionId) {
            $currentRoute = \Route::currentRouteName();
            return redirect()->route('bumdes.profil', ['redirect' => $currentRoute]);
        }

        // Cek apakah region ini mengaktifkan layanan tersebut
        $regionService = RegionService::where('region_id', $regionId)
            ->whereHas('service', function($q) use ($serviceSlug) {
                $q->where('slug', $serviceSlug);
            })
            ->first();

        if (!$regionService || !$regionService->is_active) {
            // Redirect back with an error session.
            $fallback = route('beranda');
            if (url()->previous() && url()->previous() !== url()->current()) {
                $fallback = url()->previous();
            }
            return redirect($fallback)->with('error_service_unavailable', 'Mohon Maaf, Daerah ini belum menyediakan Layanan ini');
        }

        // Lanjut ke request selanjutnya jika aktif
        return $next($request);
    }
}
