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
        $banners = Banner::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $banners->transform(function ($banner) {
            $banner->image_url = asset('storage/' . $banner->image_path);
            return $banner;
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
        $announcements = Announcement::with(['region', 'admin', 'images'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Format to match mobile expectations (same as NewsApiController)
        $formatted = $announcements->map(function ($item) {
            $image = null;
            if ($item->image_path) {
                $image = asset('storage/' . $item->image_path);
            } elseif ($item->images && $item->images->count() > 0) {
                $image = asset('storage/' . $item->images->first()->image_path);
            }

            return [
                'id' => $item->id,
                'title' => $item->title,
                'category' => $item->type ?? 'Pengumuman',
                'date' => $item->event_date ? $item->event_date->format('Y-m-d') : $item->created_at->format('Y-m-d'),
                'desc' => $item->description,
                'content' => $item->description,
                'image' => $image,
                'location' => $item->location,
                'author' => $item->admin ? $item->admin->name : 'Admin Desa',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formatted
        ]);
    }

    /**
     * Get available services/units
     */
    public function services()
    {
        // Simple aggregation of services for mobile display
        $rentals = Barang::where('status', 'tersedia')->take(10)->get()->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->nama_barang,
                'type' => 'rental',
                'image' => asset('storage/' . $item->foto),
                'price' => $item->harga_sewa
            ];
        });

        $gases = Gas::where('stok', '>', 0)->take(10)->get()->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->jenis_gas,
                'type' => 'gas',
                'image' => asset('storage/' . $item->foto),
                'price' => $item->harga_satuan
            ];
        });

        $mobils = \App\Models\Mobil::where('status', 'tersedia')->take(10)->get()->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->nama_mobil,
                'type' => 'mobil',
                'image' => asset('storage/' . $item->foto),
                'price' => $item->harga_sewa
            ];
        });

        $fasilitas = \App\Models\FasilitasUmum::where('status', 'tersedia')->take(10)->get()->map(function($item) {
            return [
                'id' => $item->id,
                'name' => $item->nama_fasilitas,
                'type' => 'fasilitas',
                'image' => asset('storage/' . $item->foto),
                'price' => 0
            ];
        });

        $services = $rentals->concat($gases)->concat($mobils)->concat($fasilitas);

        return response()->json([
            'status' => 'success',
            'data' => $services
        ]);
    }

    /**
     * Get 4 main unit pelayanan menus.
     * If user is authenticated, filter by their active services for their region.
     */
    public function unitPelayanan(Request $request)
    {
        $menus = [
            ['slug' => 'pasar-daerah', 'title' => 'Pasar Daerah', 'image' => 'PasarDaerah.png', 'color' => 'teal', 'action' => 'Toko BUMDes'],
            ['slug' => 'penyewaan-mobil', 'title' => 'Penyewaan Mobil', 'image' => 'mobil.png', 'color' => 'blue', 'action' => 'Sewa Mobil'],
            ['slug' => 'penyewaan-alat', 'title' => 'Penyewaan Alat', 'image' => 'F1.png', 'color' => 'orange', 'action' => 'Sewa Alat'],
            ['slug' => 'pelaporan-warga', 'title' => 'Pelaporan', 'image' => 'lapor.png', 'color' => 'red', 'action' => 'Buat Laporan'],
            ['slug' => 'penjualan-gas', 'title' => 'Pembelian Gas', 'image' => 'F2.png', 'color' => 'green', 'action' => 'Beli Gas'],
            ['slug' => 'fasilitas-umum', 'title' => 'Fasilitas Umum', 'image' => 'fasilitas.png', 'color' => 'purple', 'action' => 'Sewa Fasilitas'],
        ];

        // Ensure sanctum auth is parsed if token exists
        $user = auth('sanctum')->user();
        if ($user && $user->region_id) {
            $regionId = $user->region_id;
            // Get active services for this region
            $activeSlugs = \Illuminate\Support\Facades\DB::table('region_services')
                ->join('services', 'services.id', '=', 'region_services.service_id')
                ->where('region_services.region_id', $regionId)
                ->where('region_services.is_active', true)
                ->pluck('services.slug')
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

    /**
     * Get popular items across all services based on order counts (or random if no orders)
     */
    public function popular(Request $request)
    {
        $limitPerCategory = 5;

        // Pasar Daerah - Most ordered
        $pasarProducts = \App\Models\PasarProduk::where('status', 'tersedia')
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->take($limitPerCategory)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->nama_produk,
                    'price' => $item->harga,
                    'type' => 'pasar',
                    'category' => 'Pasar Daerah',
                    'image_url' => $item->foto ? asset('storage/' . $item->foto) : null,
                    'satuan' => $item->satuan,
                    'order_count' => $item->order_items_count,
                    'original_data' => $item,
                ];
            });

        // Gas - Most ordered
        $gasProducts = \App\Models\Gas::where('stok', '>', 0)
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
                    'original_data' => $item,
                ];
            });

        // Sewa Alat
        $alatProducts = \App\Models\Barang::where('status', 'tersedia')
            ->take($limitPerCategory)
            ->get()
            ->map(function ($item) {
                // RentalBookings doesn't have direct belongsTo Barang in some cases, so we might just use random or generic
                $orderCount = \App\Models\RentalBooking::where('barang_id', $item->id)->count();
                return [
                    'id' => $item->id,
                    'name' => $item->nama_barang,
                    'price' => $item->harga_sewa,
                    'type' => 'alat',
                    'category' => 'Penyewaan Alat',
                    'image_url' => $item->foto ? asset('storage/' . $item->foto) : null,
                    'satuan' => $item->satuan ?? 'hari',
                    'order_count' => $orderCount,
                    'original_data' => $item,
                ];
            })->sortByDesc('order_count')->values();

        // Sewa Mobil
        $mobilProducts = \App\Models\Mobil::where('status', 'tersedia')
            ->take($limitPerCategory)
            ->get()
            ->map(function ($item) {
                $orderCount = \App\Models\MobilBooking::where('mobil_id', $item->id)->count();
                return [
                    'id' => $item->id,
                    'name' => $item->nama_mobil,
                    'price' => $item->harga_sewa,
                    'type' => 'mobil',
                    'category' => 'Penyewaan Mobil',
                    'image_url' => $item->foto ? asset('storage/' . $item->foto) : null,
                    'satuan' => 'hari',
                    'order_count' => $orderCount,
                    'original_data' => $item,
                ];
            })->sortByDesc('order_count')->values();

        // Merge all
        $allPopular = $pasarProducts->concat($gasProducts)
            ->concat($alatProducts)
            ->concat($mobilProducts)
            ->sortByDesc('order_count')
            ->values()
            ->take(10); // Show top 10 overall

        return response()->json([
            'status' => 'success',
            'data' => $allPopular
        ]);
    }
}
