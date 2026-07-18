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

        // Format image URLs
        $banners->transform(function ($banner) {
            $banner->image_url = asset('storage/' . $banner->image);
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
        $announcements = Announcement::with('region')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $announcements
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
}
