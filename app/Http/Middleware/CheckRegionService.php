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
        // Periksa region_id user dan semua parent-nya (RT -> RW -> Desa)
        $relevantRegionIds = [$regionId];
        $ancestorIds = \App\Models\Region::getAncestorIds($regionId);
        if ($ancestorIds) {
            $relevantRegionIds = array_merge($relevantRegionIds, $ancestorIds);
        }

        // Resolve target slugs and aliases
        $targetSlugs = match($serviceSlug) {
            'peminjaman-fasilitas-umum', 'fasilitas-umum' => ['peminjaman-fasilitas-umum', 'fasilitas-umum'],
            'layanan-ambulans', 'ambulans' => ['layanan-ambulans', 'ambulans', 'fasilitas-umum', 'penyewaan-mobil'],
            'penyewaan-mobil' => ['penyewaan-mobil'],
            'penyewaan-alat' => ['penyewaan-alat'],
            'penjualan-gas' => ['penjualan-gas'],
            'pelaporan-warga' => ['pelaporan-warga'],
            'pasar-daerah' => ['pasar-daerah'],
            default => [$serviceSlug]
        };

        $regionService = RegionService::whereIn('region_id', $relevantRegionIds)
            ->whereHas('service', function($q) use ($targetSlugs) {
                $q->whereIn('slug', $targetSlugs);
            })
            ->where('is_active', true)
            ->first();

        if (!$regionService) {
            // Fallback khusus jika wilayah atau leluhurnya memiliki data produk/armada langsung
            $hasProducts = false;
            if (in_array('fasilitas-umum', $targetSlugs) || in_array('peminjaman-fasilitas-umum', $targetSlugs)) {
                $hasProducts = \App\Models\FasilitasUmum::whereIn('region_id', $relevantRegionIds)->where('status', '!=', 'Tidak Tersedia')->exists()
                    || \App\Models\Mobil::whereIn('region_id', $relevantRegionIds)->whereIn('kategori', ['ambulans', 'kendaraan_operasional'])->exists();
            } elseif (in_array('layanan-ambulans', $targetSlugs)) {
                $hasProducts = \App\Models\Mobil::whereIn('region_id', $relevantRegionIds)->where('kategori', 'ambulans')->exists();
            } elseif (in_array('penyewaan-mobil', $targetSlugs)) {
                $hasProducts = \App\Models\Mobil::whereIn('region_id', $relevantRegionIds)->whereNotIn('kategori', ['ambulans', 'kendaraan_operasional'])->exists();
            } elseif (in_array('penyewaan-alat', $targetSlugs)) {
                $hasProducts = \App\Models\Barang::whereIn('region_id', $relevantRegionIds)->exists();
            } elseif (in_array('penjualan-gas', $targetSlugs)) {
                $hasProducts = \App\Models\Gas::whereIn('region_id', $relevantRegionIds)->exists();
            }

            if ($hasProducts) {
                // Auto-sync & aktifkan region_service agar akses cepat dan konsisten
                $service = \App\Models\Service::whereIn('slug', $targetSlugs)->first();
                if ($service && $regionId) {
                    \App\Models\RegionService::updateOrInsert(
                        ['region_id' => $regionId, 'service_id' => $service->id],
                        ['is_active' => true, 'is_exclusive' => false, 'updated_at' => now()]
                    );
                }
                return $next($request);
            }

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
        if ($regionService && $regionService->is_exclusive) {
            $isAuthorized = false;
            if (auth()->check()) {
                $userRegionId = auth()->user()->region_id;
                if ($userRegionId == $regionId) {
                    $isAuthorized = true;
                } else {
                    // Cek apakah region user adalah anak/turunan dari region layanan
                    $ancestorIds = \App\Models\Region::getAncestorIds($userRegionId);
                    if (in_array($regionId, $ancestorIds) || in_array($regionService->region_id, $ancestorIds) || $userRegionId == $regionService->region_id) {
                        $isAuthorized = true;
                    }
                }
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
