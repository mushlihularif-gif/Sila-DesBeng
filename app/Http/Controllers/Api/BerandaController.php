<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Announcement;
use App\Models\Barang;
use App\Models\Gas;
use Illuminate\Http\Request;

class BerandaController extends Controller
{
    /**
     * Get all active banners for the carousel
     */
    public function banners()
    {
        $banners = \Illuminate\Support\Facades\Cache::remember('api.beranda.banners', 300, function () {
            $data = Banner::where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->get();
                
            return $data->map(function ($banner) {
                $banner->image_url = asset('storage/' . $banner->image_path);
                return $banner;
            });
        });

        return response()->json([
            'status' => 'success',
            'data' => $banners
        ]);
    }

    /**
     * Get recent announcements
     */
    public function announcements()
    {
        $formatted = (function () {
            $announcements = Announcement::with(['region', 'admin', 'images'])
                ->visibleTo(auth('sanctum')->user())
                ->orderBy('published_at', 'desc')
                ->take(5)
                ->get();

            // Format to match mobile expectations (same as NewsApiController)
            return $announcements->map(function ($item) {
                $images = [];
                if ($item->image_path) {
                    $images[] = $item->mediaUrl();
                }
                if ($item->images && $item->images->count() > 0) {
                    foreach ($item->images as $img) {
                        $imgUrl = $img->url;
                        if (!in_array($imgUrl, $images)) {
                            $images[] = $imgUrl;
                        }
                    }
                }

                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'category' => $item->type ?? 'Pengumuman',
                    'date' => ($item->event_date ?? $item->published_at ?? $item->created_at)->format('Y-m-d'),
                    'published_at' => $item->published_at?->toIso8601String(),
                    'workflow_state' => $item->workflow_state,
                    'views_count' => $item->views_count,
                    'desc' => $item->description,
                    'content' => $item->description,
                    'image' => count($images) > 0 ? $images[0] : null,
                    'images' => $images,
                    'location' => $item->location,
                    'author' => $item->admin ? $item->admin->name : 'Admin Desa',
                ];
            });
        })();

        return response()->json([
            'status' => 'success',
            'data' => $formatted
        ])->header('Cache-Control', 'private, no-store')->header('Vary', 'Authorization, Cookie');
    }

    /**
     * Get available services/units
     */
    public function services()
    {
        $user = auth('sanctum')->user();
        $userRegionId = $user?->region_id;

        $rentals = app(\App\Services\RentalOrdering::class)->visibleQuery($user)
            ->whereRaw('LOWER(status) = ?', ['tersedia'])
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->nama_barang,
                    'type' => 'rental',
                    'image' => $item->foto ? asset('storage/' . $item->foto) : null,
                    'price' => $item->harga_sewa,
                ];
            });

        $gases = app(\App\Services\GasOrdering::class)->visibleQuery($user)
            ->where('stok', '>', 0)
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->jenis_gas,
                    'type' => 'gas',
                    'image' => $item->foto ? asset('storage/' . $item->foto) : null,
                    'price' => $item->harga_satuan,
                ];
            });

        $mobilQuery = \App\Models\Mobil::inCatalog()->forRental()->whereRaw('LOWER(status) = ?', ['tersedia']);
        if ($userRegionId) {
            $mobilQuery->whereIn('region_id', \App\Models\Region::wilayahLayananTerlihat($userRegionId, 'penyewaan-mobil'));
        }
        $mobils = $mobilQuery->take(10)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->nama_mobil,
                'type' => 'mobil',
                'image' => $item->foto ? asset('storage/' . $item->foto) : null,
                'price' => $item->harga_sewa,
            ];
        });

        $fasilitasQuery = \App\Models\FasilitasUmum::inCatalog()->whereRaw('LOWER(status) = ?', ['tersedia']);
        if ($userRegionId) {
            $fasilitasQuery->whereIn('region_id', \App\Models\Region::wilayahLayananTerlihat($userRegionId, 'fasilitas-umum'));
        }
        $fasilitas = $fasilitasQuery->take(10)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->nama_fasilitas,
                'type' => 'fasilitas',
                'image' => $item->foto ? asset('storage/' . $item->foto) : null,
                'price' => (float) ($item->harga_sewa ?? 0),
            ];
        });

        $services = $rentals->concat($gases)->concat($mobils)->concat($fasilitas)->values();

        return response()->json(['status' => 'success', 'data' => $services])
            ->header('Cache-Control', 'private, no-store')
            ->header('Vary', 'Authorization, Cookie');
    }

    /**
     * Get 4 main unit pelayanan menus.
     * If user is authenticated, filter by their active services for their region.
     */
    public function unitPelayanan(Request $request)
    {
        $menus = [
            ['slug' => 'penyewaan-mobil', 'title' => 'Penyewaan Transportasi', 'image' => 'mobil.png', 'color' => 'blue', 'action' => 'Sewa Transportasi'],
            ['slug' => 'penyewaan-alat', 'title' => 'Penyewaan Alat', 'image' => 'F1.png', 'color' => 'orange', 'action' => 'Sewa Alat'],
            ['slug' => 'penjualan-gas', 'title' => 'Pembelian Gas', 'image' => 'F2.png', 'color' => 'green', 'action' => 'Beli Gas'],
            ['slug' => 'fasilitas-umum', 'title' => 'Fasilitas Umum', 'image' => 'fasilitas.png', 'color' => 'purple', 'action' => 'Sewa Fasilitas'],
        ];

        // Ensure sanctum auth is parsed if token exists
        $user = auth('sanctum')->user();
        if ($user && $user->region_id) {
            $regionLineage = \App\Models\Region::garisLayananUntukWarga($user->region_id);
            $activeSlugs = \Illuminate\Support\Facades\DB::table('region_services')
                ->join('services', 'services.id', '=', 'region_services.service_id')
                ->whereIn('region_services.region_id', $regionLineage)
                ->where('region_services.is_active', true)
                ->pluck('services.slug')
                ->unique()
                ->toArray();
            
            $menus = array_filter($menus, function($m) use ($activeSlugs) {
                return in_array($m['slug'], $activeSlugs);
            });
            $menus = array_values($menus);
        }

        // Add full URL to image
        foreach($menus as &$m) {
            $m['imageUrl'] = asset('User/img/elemen/' . $m['image']);
        }

        return response()->json([
            'status' => 'success',
            'data' => $menus
        ]);
    }

    public function popular(Request $request)
    {
        $user = auth('sanctum')->user();
        $userRegionId = $user?->region_id;
        $limitPerCategory = 5;

        $gasProducts = app(\App\Services\GasOrdering::class)->visibleQuery($user)
            ->where('stok', '>', 0)
            ->withCount('gasOrders')
            ->orderBy('gas_orders_count', 'desc')
            ->take($limitPerCategory)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->jenis_gas,
                    'price' => $item->harga_satuan,
                    'type' => 'gas',
                    'category' => 'Penjualan Gas',
                    'image_url' => $item->foto ? asset('storage/' . $item->foto) : null,
                    'satuan' => 'tabung',
                    'order_count' => $item->gas_orders_count,
                ];
            });

        $alatProducts = app(\App\Services\RentalOrdering::class)->visibleQuery($user)
            ->whereRaw('LOWER(status) = ?', ['tersedia'])
            ->withCount('rentalBookings')
            ->orderBy('rental_bookings_count', 'desc')
            ->take($limitPerCategory)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->nama_barang,
                    'price' => $item->harga_sewa,
                    'type' => 'alat',
                    'category' => 'Penyewaan Alat',
                    'image_url' => $item->foto ? asset('storage/' . $item->foto) : null,
                    'satuan' => $item->satuan ?? 'hari',
                    'order_count' => $item->rental_bookings_count,
                ];
            });

        $mobilQuery = \App\Models\Mobil::inCatalog()->forRental()->whereRaw('LOWER(status) = ?', ['tersedia']);
        if ($userRegionId) {
            $mobilQuery->whereIn('region_id', \App\Models\Region::wilayahLayananTerlihat($userRegionId, 'penyewaan-mobil'));
        }
        $mobilProducts = $mobilQuery
            ->withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->take($limitPerCategory)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->nama_mobil,
                    'price' => $item->harga_sewa,
                    'type' => 'mobil',
                    'category' => 'Penyewaan Transportasi',
                    'image_url' => $item->foto ? asset('storage/' . $item->foto) : null,
                    'satuan' => 'hari',
                    'order_count' => $item->bookings_count,
                ];
            });

        $allPopular = $gasProducts
            ->concat($alatProducts)
            ->concat($mobilProducts)
            ->sortByDesc('order_count')
            ->values()
            ->take(10);

        return response()->json(['status' => 'success', 'data' => $allPopular])
            ->header('Cache-Control', 'private, no-store')
            ->header('Vary', 'Authorization, Cookie');
    }
}
