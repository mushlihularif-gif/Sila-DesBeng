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

        $regionId = $request->query('region_id');

        // Jika tidak ada region_id di URL, cek apakah user login dan punya region_id
        if (!$regionId && auth()->check()) {
            $regionId = auth()->user()->region_id;
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
            $currentRoute = \Route::currentRouteName();
            
            // Default fallback is beranda
            $fallback = route('beranda');
            
            if ($regionId) {
                $region = \App\Models\Region::find($regionId);
                if ($region && $region->type === 'desa' && $region->parent_id) {
                    $fallback = route('bumdes.profil.desa', $region->parent_id) . '?redirect=' . $currentRoute;
                } else {
                    $fallback = route('bumdes.profil', ['redirect' => $currentRoute]);
                }
            } else if (url()->previous() && url()->previous() !== url()->current()) {
                $fallback = url()->previous();
            }

            return redirect($fallback)->with('error_service_unavailable', 'Mohon Maaf, Daerah ini belum menyediakan Layanan ini');
        }

        // Cek eksklusivitas layanan (hanya untuk warga lokal)
        if ($regionService->is_exclusive) {
            $isAuthorized = false;
            if (auth()->check() && auth()->user()->region_id == $regionId) {
                $isAuthorized = true;
            }

            if (!$isAuthorized) {
                if (!auth()->check()) {
                    $fallback = route('beranda');
                    if ($regionId) {
                        $region = \App\Models\Region::find($regionId);
                        if ($region && $region->type === 'desa' && $region->parent_id) {
                            $fallback = route('bumdes.profil.desa', $region->parent_id);
                        } else {
                            $fallback = route('bumdes.profil');
                        }
                    } else if (url()->previous() && url()->previous() !== url()->current()) {
                        $fallback = url()->previous();
                    }
                    
                    return redirect($fallback)->with([
                        'error' => 'Layanan ini khusus untuk warga setempat. Silakan login terlebih dahulu untuk melanjutkan.',
                        'show_login_modal' => true
                    ]);
                }

                $currentRoute = \Route::currentRouteName();
                $fallback = route('beranda');
                
                if ($regionId) {
                    $region = \App\Models\Region::find($regionId);
                    if ($region && $region->type === 'desa' && $region->parent_id) {
                        $fallback = route('bumdes.profil.desa', $region->parent_id) . '?redirect=' . $currentRoute;
                    } else {
                        $fallback = route('bumdes.profil', ['redirect' => $currentRoute]);
                    }
                } else if (url()->previous() && url()->previous() !== url()->current()) {
                    $fallback = url()->previous();
                }

                return redirect($fallback)->with('error_service_unavailable', 'Maaf, Kelurahan/Desa ini hanya menyediakan layanan ini khusus untuk wilayahnya');
            }
        }

        // Lanjut ke request selanjutnya jika aktif dan terotorisasi
        return $next($request);
    }
}
