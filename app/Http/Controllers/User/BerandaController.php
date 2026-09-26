<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RentalBooking;
use App\Models\GasOrder;
use App\Models\ManualReport;
use App\Models\Barang;
use App\Models\Gas;
use App\Models\Mobil;
use App\Models\MobilBooking;
use App\Models\FasilitasUmum;
use App\Models\FasilitasUmumBooking;
use App\Models\PasarOrderItem;
use App\Models\PasarProduk;
use App\Models\Region;
use App\Models\BumdesMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BerandaController extends Controller
{
    public function index(Request $request)
    {
        // $year = date('Y'); // Removed, moved to logic below
        $search = $request->input('search');
        $searchResults = [];
        // Handle Search
        if ($search) {
            $searchResults = $this->performGlobalSearch($search);
            if ($request->ajax() || $request->wantsJson() || $request->has('ajax')) {
                return response()->json([
                    'success' => true,
                    'query' => $search,
                    'count' => $searchResults->count(),
                    'results' => $searchResults->values()
                ]);
            }
        } elseif ($request->has('ajax')) {
            return response()->json([
                'success' => true,
                'query' => '',
                'count' => 0,
                'results' => []
            ]);
        }

        // Dapatkan tahun yang dipilih (default ke tahun sekarang)
        $yearRequest = $request->input('year', now()->year);
        $year = (int)$yearRequest; // Strict integer cast

        // Ambil daftar tahun yang tersedia dari database (Strict Integer)
        $rentalYears = RentalBooking::withTrashed()
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->toArray();
            
        $gasYears = GasOrder::withTrashed()
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->toArray();
        
        // Gabungkan dengan tahun sekarang secara eksplisit (Hard Merge)
        $allYears = array_unique(array_merge($rentalYears, $gasYears, [(int)now()->year]));
        $availableYears = array_values($allYears);
        rsort($availableYears);

        // Handle Cascading Region Selection
        $kabupatenId = 1; // Hardcode Kabupaten Bengkalis
        $kecamatanId = $request->input('kecamatan_id', 'all');
        $desaId = $request->input('desa_id', 'all');
        
        // Determine the effective regionId for data fetching
        $regionId = $kabupatenId;
        if ($desaId !== 'all' && !empty($desaId)) {
            $regionId = (int)$desaId;
        } elseif ($kecamatanId !== 'all' && !empty($kecamatanId)) {
            $regionId = (int)$kecamatanId;
        } else {
            // Default to user's region if logged in and no filter is explicitly applied
            if (auth()->check() && auth()->user()->region_id) {
                $userRegion = \App\Models\Region::find(auth()->user()->region_id);
                if ($userRegion) {
                    if ($userRegion->type === 'desa' || $userRegion->type === 'kelurahan') {
                        $desaId = $userRegion->id;
                        $kecamatanId = $userRegion->parent_id ?? 'all';
                        $regionId = $userRegion->id;
                    } elseif ($userRegion->type === 'kecamatan') {
                        $kecamatanId = $userRegion->id;
                        $regionId = $userRegion->id;
                    }
                }
            }
        }

        // Prepare Region Data for Dropdowns
        $kecamatans = \App\Models\Region::where('parent_id', $kabupatenId)
            ->where('type', 'kecamatan')
            ->get();
        
        $desas = collect([]);
        if ($kecamatanId !== 'all' && !empty($kecamatanId)) {
            $desas = \App\Models\Region::where('parent_id', $kecamatanId)
                ->where('type', 'desa')
                ->get();
        }

        // Get Kinerja BUMDes data (monthly revenue)
        $kinerjaData = $this->getKinerjaData($year, $regionId);
        
        // Get Unit Populer data (rental vs gas comparison)
        $unitPopulerData = $this->getUnitPopulerData($year, $regionId);
        
        // Get Popular Products (Filtered by Year and Region)
        $popularProducts = $this->getPopularProducts($year, $regionId);

        // Get Active Banners
        $activeBanners = \App\Models\Banner::where('is_active', true)
                                           ->orderBy('sort_order', 'asc')
                                           ->get();
                                           
        // Get Recent Announcements
        $announcementQuery = \App\Models\Announcement::with(['region'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc');

        if (auth()->check() && auth()->user()->region_id) {
            $userRegionId = auth()->user()->region_id;
            $ancestorIds = \App\Models\Region::getAncestorIds($userRegionId);
            $relevantRegionIds = array_merge([$userRegionId], $ancestorIds);
            
            $announcementQuery->where(function($q) use ($relevantRegionIds) {
                $q->whereIn('region_id', $relevantRegionIds)
                  ->orWhereNull('region_id');
            });
        }

        $recentAnnouncements = $announcementQuery->take(4)->get();
        
        // Ambil Active Services jika user login dan punya region
        $activeServices = [];
        if (auth()->check() && auth()->user()->region_id) {
            $userRegionId = auth()->user()->region_id;
            
            // Dapatkan ID wilayah user dan semua leluhurnya (RT -> RW -> Desa -> Kec)
            $ancestorIds = \App\Models\Region::getAncestorIds($userRegionId);
            $relevantIds = array_merge([$userRegionId], $ancestorIds);
            
            // Cari layanan aktif di wilayah user atau leluhurnya (biasanya nempel di Desa)
            $regionsWithServices = \App\Models\Region::with(['services' => function($q) {
                $q->where('is_active', true);
            }])->whereIn('id', $relevantIds)->get();
            
            foreach ($regionsWithServices as $region) {
                if ($region->services->isNotEmpty()) {
                    $activeServices = array_merge($activeServices, $region->services->pluck('name')->toArray());
                }
            }

            // Jaminan ketersediaan: Jika admin telah menambahkan produk/armada di wilayah ini,
            // maka otomatis layanan tersebut aktif dan dapat diakses warga dari Beranda
            if (\App\Models\FasilitasUmum::whereIn('region_id', $relevantIds)->where('status', '!=', 'Tidak Tersedia')->exists()
                || \App\Models\Mobil::whereIn('region_id', $relevantIds)->whereIn('kategori', ['ambulans', 'kendaraan_operasional'])->exists()) {
                $activeServices[] = 'Fasilitas Umum';
            }
            if (\App\Models\Barang::whereIn('region_id', $relevantIds)->exists()) {
                $activeServices[] = 'Penyewaan Alat';
            }
            if (\App\Models\Gas::whereIn('region_id', $relevantIds)->exists()) {
                $activeServices[] = 'Penjualan Gas';
            }
            if (\App\Models\Mobil::whereIn('region_id', $relevantIds)->whereNotIn('kategori', ['ambulans', 'kendaraan_operasional'])->exists()) {
                $activeServices[] = 'Penyewaan Mobil';
            }

            $activeServices = array_unique($activeServices);
        }
        
        return view('beranda.index', compact(
            'kinerjaData',
            'unitPopulerData',
            'year',
            'availableYears', // Pass available years
            'popularProducts',
            'searchResults',
            'search',
            'activeServices',
            'activeBanners',
            'recentAnnouncements',
            'kecamatans',
            'desas',
            'kecamatanId',
            'desaId'
        ));
    }

    /**
     * Get Popular Products (Top 4 most rented/sold items with smart fallback)
     */
    private function getPopularProducts($year = null, $regionId = 1)
    {
        $year = $year ?? (int)date('Y');
        $regionIds = array_merge([$regionId], \App\Models\Region::getDescendantIds($regionId));

        // 1. Get Rental Scores
        $rentalPopularity = RentalBooking::withTrashed()
            ->select('barang_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereYear('created_at', $year)
            ->whereIn('region_id', $regionIds)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->whereNotNull('barang_id')
            ->groupBy('barang_id')
            ->with('barang')
            ->get();

        $products = $rentalPopularity->map(function ($item) {
            if (!$item->barang) return null;
            return (object) [
                'id' => $item->barang->id,
                'name' => $item->barang->nama_barang,
                'image' => $item->barang->foto,
                'price' => $item->barang->harga_sewa,
                'price_formatted' => 'Rp ' . number_format($item->barang->harga_sewa, 0, ',', '.'),
                'stock' => $item->barang->stok,
                'sold' => (int)$item->total_sold,
                'type' => 'rental',
                'category' => 'Unit Penyewaan Alat',
                'badge_color' => 'bg-emerald-600 text-white',
                'unit' => $item->barang->satuan ?? 'hari',
                'link' => route('rental.equipment.show', $item->barang->id)
            ];
        })->filter();

        // 2. Get Gas Scores
        $gasPopularity = GasOrder::withTrashed()
            ->select('gas_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereYear('created_at', $year)
            ->whereIn('region_id', $regionIds)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->whereNotNull('gas_id')
            ->groupBy('gas_id')
            ->with('gas')
            ->get();

        $gasProducts = $gasPopularity->map(function ($item) {
            if (!$item->gas) return null;
            return (object) [
                'id' => $item->gas->id,
                'name' => $item->gas->jenis_gas,
                'image' => $item->gas->foto,
                'price' => $item->gas->harga_satuan,
                'price_formatted' => 'Rp ' . number_format($item->gas->harga_satuan, 0, ',', '.'),
                'stock' => $item->gas->stok,
                'sold' => (int)$item->total_sold,
                'type' => 'gas',
                'category' => 'Unit Penjualan Gas',
                'badge_color' => 'bg-orange-500 text-white',
                'unit' => 'tabung',
                'link' => route('gas.sales.show', $item->gas->id)
            ];
        })->filter();

        // 3. Get Mobil Scores
        $mobilPopularity = \App\Models\MobilBooking::withTrashed()
            ->select('mobil_id', DB::raw('COUNT(id) as total_sold'))
            ->whereYear('created_at', $year)
            ->whereIn('region_id', $regionIds)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->whereNotNull('mobil_id')
            ->groupBy('mobil_id')
            ->with('mobil')
            ->get();

        $mobilProducts = $mobilPopularity->map(function ($item) {
            if (!$item->mobil) return null;
            return (object) [
                'id' => $item->mobil->id,
                'name' => $item->mobil->nama_mobil,
                'image' => $item->mobil->foto,
                'price' => $item->mobil->harga_sewa,
                'price_formatted' => 'Rp ' . number_format($item->mobil->harga_sewa, 0, ',', '.'),
                'stock' => $item->mobil->stok,
                'sold' => (int)$item->total_sold,
                'type' => 'mobil',
                'category' => 'Unit Penyewaan Mobil',
                'badge_color' => 'bg-blue-600 text-white',
                'unit' => $item->mobil->satuan ?? 'hari',
                'link' => route('mobil.rental.show', $item->mobil->id)
            ];
        })->filter();

        // 4. Get Pasar Daerah Scores
        $pasarPopularity = \App\Models\PasarOrderItem::select('pasar_produk_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function($q) use ($year, $regionIds) {
                $q->withTrashed()
                  ->whereYear('created_at', $year)
                  ->whereIn('region_id', $regionIds)
                  ->whereNotIn('status', ['pending', 'cancelled', 'rejected']);
            })
            ->groupBy('pasar_produk_id')
            ->with('produk')
            ->get();

        $pasarProducts = $pasarPopularity->map(function ($item) {
            if (!$item->produk) return null;
            return (object) [
                'id' => $item->produk->id,
                'name' => $item->produk->nama_produk,
                'image' => $item->produk->foto,
                'price' => $item->produk->harga,
                'price_formatted' => 'Rp ' . number_format($item->produk->harga, 0, ',', '.'),
                'stock' => $item->produk->stok,
                'sold' => (int)$item->total_sold,
                'type' => 'pasar',
                'category' => 'Pasar Daerah',
                'badge_color' => 'bg-amber-600 text-white',
                'unit' => $item->produk->satuan ?? 'pcs',
                'link' => route('pasar.show', $item->produk->id)
            ];
        })->filter();

        // Gabungkan produk berdasarkan penjualan terlaris
        $combined = $products->concat($gasProducts)->concat($mobilProducts)->concat($pasarProducts)->sortByDesc('sold');

        // SMART FALLBACK: Jika riwayat transaksi masih kurang dari 4 (Cold Start)
        // Isi slot produk dengan produk yang tersedia/ready stock di wilayah tersebut!
        if ($combined->count() < 4) {
            $existingKeys = $combined->map(fn($p) => $p->type . '-' . $p->id)->toArray();

            // Fallback 1: Gas Elpiji Ready
            $fallbackGas = \App\Models\Gas::whereIn('region_id', $regionIds)
                ->where('stok', '>', 0)
                ->latest()
                ->take(2)
                ->get();
            foreach ($fallbackGas as $g) {
                $key = 'gas-' . $g->id;
                if (!in_array($key, $existingKeys) && $combined->count() < 4) {
                    $existingKeys[] = $key;
                    $combined->push((object) [
                        'id' => $g->id,
                        'name' => $g->jenis_gas,
                        'image' => $g->foto,
                        'price' => $g->harga_satuan,
                        'price_formatted' => 'Rp ' . number_format($g->harga_satuan, 0, ',', '.'),
                        'stock' => $g->stok,
                        'sold' => 0,
                        'type' => 'gas',
                        'category' => 'Unit Penjualan Gas',
                        'badge_color' => 'bg-orange-500 text-white',
                        'unit' => 'tabung',
                        'link' => route('gas.sales.show', $g->id)
                    ]);
                }
            }

            // Fallback 2: Alat Sewa Ready
            $fallbackBarang = \App\Models\Barang::whereIn('region_id', $regionIds)
                ->where('stok', '>', 0)
                ->latest()
                ->take(2)
                ->get();
            foreach ($fallbackBarang as $b) {
                $key = 'rental-' . $b->id;
                if (!in_array($key, $existingKeys) && $combined->count() < 4) {
                    $existingKeys[] = $key;
                    $combined->push((object) [
                        'id' => $b->id,
                        'name' => $b->nama_barang,
                        'image' => $b->foto,
                        'price' => $b->harga_sewa,
                        'price_formatted' => 'Rp ' . number_format($b->harga_sewa, 0, ',', '.'),
                        'stock' => $b->stok,
                        'sold' => 0,
                        'type' => 'rental',
                        'category' => 'Unit Penyewaan Alat',
                        'badge_color' => 'bg-emerald-600 text-white',
                        'unit' => $b->satuan ?? 'hari',
                        'link' => route('rental.equipment.show', $b->id)
                    ]);
                }
            }

            // Fallback 3: Mobil Sewa Ready
            $fallbackMobil = \App\Models\Mobil::whereIn('region_id', $regionIds)
                ->whereNotIn('kategori', ['ambulans', 'kendaraan_operasional'])
                ->where('stok', '>', 0)
                ->latest()
                ->take(2)
                ->get();
            foreach ($fallbackMobil as $m) {
                $key = 'mobil-' . $m->id;
                if (!in_array($key, $existingKeys) && $combined->count() < 4) {
                    $existingKeys[] = $key;
                    $combined->push((object) [
                        'id' => $m->id,
                        'name' => $m->nama_mobil,
                        'image' => $m->foto,
                        'price' => $m->harga_sewa,
                        'price_formatted' => 'Rp ' . number_format($m->harga_sewa, 0, ',', '.'),
                        'stock' => $m->stok,
                        'sold' => 0,
                        'type' => 'mobil',
                        'category' => 'Unit Penyewaan Mobil',
                        'badge_color' => 'bg-blue-600 text-white',
                        'unit' => $m->satuan ?? 'hari',
                        'link' => route('mobil.rental.show', $m->id)
                    ]);
                }
            }

            // Fallback 4: Fasilitas Umum Desa
            $fallbackFasilitas = \App\Models\FasilitasUmum::whereIn('region_id', $regionIds)
                ->latest()
                ->take(1)
                ->get();
            foreach ($fallbackFasilitas as $f) {
                $key = 'fasilitas-' . $f->id;
                if (!in_array($key, $existingKeys) && $combined->count() < 4) {
                    $existingKeys[] = $key;
                    $combined->push((object) [
                        'id' => $f->id,
                        'name' => $f->nama_fasilitas,
                        'image' => $f->foto,
                        'price' => 0,
                        'price_formatted' => 'Izin Kegiatan',
                        'stock' => $f->stok ?? 1,
                        'sold' => 0,
                        'type' => 'fasilitas',
                        'category' => 'Fasilitas Umum',
                        'badge_color' => 'bg-purple-600 text-white',
                        'unit' => 'acara',
                        'link' => route('user.fasilitas-umum.show', $f->id)
                    ]);
                }
            }

            // Fallback 5: Pasar Produk Daerah (Sentral se-Kabupaten)
            if ($combined->count() < 4) {
                $fallbackPasar = \App\Models\PasarProduk::where('stok', '>', 0)
                    ->latest()
                    ->take(4)
                    ->get();
                foreach ($fallbackPasar as $p) {
                    $key = 'pasar-' . $p->id;
                    if (!in_array($key, $existingKeys) && $combined->count() < 4) {
                        $existingKeys[] = $key;
                        $combined->push((object) [
                            'id' => $p->id,
                            'name' => $p->nama_produk,
                            'image' => $p->foto,
                            'price' => $p->harga,
                            'price_formatted' => 'Rp ' . number_format($p->harga, 0, ',', '.'),
                            'stock' => $p->stok,
                            'sold' => 0,
                            'type' => 'pasar',
                            'category' => 'Pasar Daerah',
                            'badge_color' => 'bg-amber-600 text-white',
                            'unit' => $p->satuan ?? 'pcs',
                            'link' => route('pasar.show', $p->id)
                        ]);
                    }
                }
            }

            // Fallback 6: Master Produk se-Kabupaten Bengkalis jika desa benar-benar kosong
            if ($combined->count() < 4) {
                $globalBarang = \App\Models\Barang::where('stok', '>', 0)->latest()->take(4)->get();
                foreach ($globalBarang as $b) {
                    $key = 'rental-' . $b->id;
                    if (!in_array($key, $existingKeys) && $combined->count() < 4) {
                        $existingKeys[] = $key;
                        $combined->push((object) [
                            'id' => $b->id,
                            'name' => $b->nama_barang,
                            'image' => $b->foto,
                            'price' => $b->harga_sewa,
                            'price_formatted' => 'Rp ' . number_format($b->harga_sewa, 0, ',', '.'),
                            'stock' => $b->stok,
                            'sold' => 0,
                            'type' => 'rental',
                            'category' => 'Unit Penyewaan Alat',
                            'badge_color' => 'bg-emerald-600 text-white',
                            'unit' => $b->satuan ?? 'hari',
                            'link' => route('rental.equipment.show', $b->id)
                        ]);
                    }
                }
            }
        }

        return $combined->take(4);
    }
    
    /**
     * Get Kinerja BUMDes data - Monthly revenue from both rental and gas + Manual Reports
     */
    private function getKinerjaData($year, $regionId = 1)
    {
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $monthlyData = [];
        $regionIds = array_merge([$regionId], \App\Models\Region::getDescendantIds($regionId));
        
        for ($month = 1; $month <= 12; $month++) {
            // Get rental revenue for this month (excluding cancelled)
            $rentalRevenue = RentalBooking::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('region_id', $regionIds)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->sum('total_amount');
            
            // Get gas revenue for this month (excluding cancelled)
            $gasRevenue = GasOrder::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('region_id', $regionIds)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->sum(DB::raw('price * quantity'));

            // Get Manual Report revenue for this month
            $manualRevenue = ManualReport::whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->whereIn('region_id', $regionIds)
                ->sum(DB::raw('amount * quantity'));
            
            // Total revenue in millions
            $totalRevenue = ($rentalRevenue + $gasRevenue + $manualRevenue) / 1000000;
            
            $monthlyData[] = round($totalRevenue, 1);
        }
        
        return [
            'categories' => $months,
            'data' => $monthlyData
        ];
    }
    
    /**
     * Get Unit Populer data - Comparison between rental and gas sales
     */
    private function getUnitPopulerData($year, $regionId = 1)
    {
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $rentalData = [];
        $gasData = [];
        $mobilData = [];
        $fasilitasData = [];
        $laporanData = [];
        $pengumumanData = [];
        $pasarData = [];
        
        $regionIds = array_merge([$regionId], \App\Models\Region::getDescendantIds($regionId));
        
        for ($month = 1; $month <= 12; $month++) {
            // Count rental orders
            $rentalCount = RentalBooking::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('region_id', $regionIds)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count();
            
            // Count gas orders
            $gasCount = \App\Models\GasOrder::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('region_id', $regionIds)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count();
                
            // Count mobil orders
            $mobilCount = \App\Models\MobilBooking::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('region_id', $regionIds)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count();
                
            // Count fasilitas umum orders
            $fasilitasCount = \App\Models\FasilitasUmumBooking::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('region_id', $regionIds)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count();
                
            // Count laporan
            $laporanCount = \App\Models\Laporan::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereHas('user', function($q) use ($regionIds) {
                    $q->whereIn('region_id', $regionIds);
                })
                ->count();
                
            // Count announcements
            $pengumumanCount = \App\Models\Announcement::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('region_id', $regionIds)
                ->count();
                
            // Count pasar orders
            $pasarCount = \App\Models\PasarOrder::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('region_id', $regionIds)
                ->whereNotIn('status', ['waiting', 'processing', 'cancelled', 'rejected'])
                ->count();
            
            $rentalData[] = $rentalCount;
            $gasData[] = $gasCount;
            $mobilData[] = $mobilCount;
            $fasilitasData[] = $fasilitasCount;
            $laporanData[] = $laporanCount;
            $pengumumanData[] = $pengumumanCount;
            $pasarData[] = $pasarCount;
        }
        
        return [
            'categories' => $months,
            'rental' => $rentalData,
            'gas' => $gasData,
            'mobil' => $mobilData,
            'fasilitas' => $fasilitasData,
            'laporan' => $laporanData,
            'pengumuman' => $pengumumanData,
            'pasar' => $pasarData
        ];
    }

    /**
     * API for Live Search Dropdown
     */
    public function liveSearch(Request $request)
    {
        $search = $request->input('search');
        
        if (!$search || strlen($search) < 2) {
            return response()->json([]);
        }

        $results = $this->performGlobalSearch($search);
        
        // Limit total results for dropdown to keep it clean
        $results = collect($results)->take(8)->values();

        // Format image URLs for frontend
        $results = $results->map(function ($item) {
            $item->image_url = $item->image ? (
                \Illuminate\Support\Str::startsWith($item->image, ['http', 'https', 'User', 'Admin']) 
                    ? asset($item->image) 
                    : asset('storage/' . $item->image)
            ) : null;
            return $item;
        });

        return response()->json($results);
    }

    /**
     * Perform global search across all modules (Intelligent multi-keyword fuzzy search)
     */
    private function performGlobalSearch($search)
    {
        $search = trim((string)$search);
        if (empty($search)) return collect([]);

        $rawSearch = strtolower($search);
        $cleanSearch = strtolower($search);
        
        // Remove noise words
        $noiseWords = ['desa', 'kelurahan', 'kecamatan', 'kabupaten', 'rt', 'rw', 'unit', 'sewa', 'beli', 'pinjam'];
        foreach ($noiseWords as $noise) {
            $cleanSearch = trim(preg_replace('/\b' . preg_quote($noise, '/') . '\b/u', '', $cleanSearch));
        }
        if (empty($cleanSearch)) {
            $cleanSearch = $rawSearch;
        }

        // Keywords and synonyms
        $terms = array_filter(explode(' ', $cleanSearch));
        $variations = [$cleanSearch, $rawSearch];

        if (str_contains($rawSearch, 'pick') || str_contains($rawSearch, 'pikap') || str_contains($rawSearch, 'carry')) {
            $variations[] = 'pikap';
            $variations[] = 'pick up';
            $variations[] = 'pickup';
            $variations[] = 'carry';
        }
        if (str_contains($rawSearch, '3kg') || str_contains($rawSearch, '3 kg')) {
            $variations[] = '3 kg';
            $variations[] = '3kg';
            $variations[] = 'melon';
            $variations[] = 'lpg';
        }
        if (str_contains($rawSearch, '5.5') || str_contains($rawSearch, '5,5')) {
            $variations[] = '5.5';
            $variations[] = '5,5';
            $variations[] = 'bright';
        }
        if (str_contains($rawSearch, 'tenda')) {
            $variations[] = 'tenda';
            $variations[] = 'terop';
        }

        $allTerms = array_unique(array_filter(array_merge($terms, $variations)));

        // 1. Search Mobil Items
        $mobilQuery = \App\Models\Mobil::whereNotIn('kategori', ['ambulans', 'kendaraan_operasional']);
        $mobilQuery->where(function ($q) use ($allTerms, $cleanSearch, $rawSearch) {
            if (trim($rawSearch) === 'mobil' || trim($rawSearch) === 'sewa mobil') {
                $q->orWhereRaw('1 = 1');
            } else {
                $q->where('nama_mobil', 'LIKE', "%{$cleanSearch}%")
                  ->orWhere('kategori', 'LIKE', "%{$cleanSearch}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$cleanSearch}%");
                foreach ($allTerms as $term) {
                    if (strlen($term) >= 2 && $term !== 'mobil') {
                        $q->orWhere('nama_mobil', 'LIKE', "%{$term}%")
                          ->orWhere('kategori', 'LIKE', "%{$term}%");
                    }
                }
            }
        });
        $mobilResults = $mobilQuery->get()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'name' => $item->nama_mobil,
                'image' => $item->foto,
                'price' => $item->harga_sewa,
                'price_formatted' => 'Rp ' . number_format($item->harga_sewa, 0, ',', '.'),
                'stock' => $item->stok,
                'type' => 'mobil',
                'category' => 'Unit Penyewaan Mobil',
                'badge_color' => 'bg-blue-600 text-white',
                'real_category' => $item->kategori,
                'unit' => $item->satuan ?? 'hari',
                'link' => route('mobil.rental.show', $item->id)
            ];
        });

        // 2. Search Gas Items
        $gasQuery = \App\Models\Gas::query();
        $gasQuery->where(function ($q) use ($allTerms, $cleanSearch, $rawSearch) {
            if (trim($rawSearch) === 'gas' || trim($rawSearch) === 'beli gas' || trim($rawSearch) === 'elpiji' || trim($rawSearch) === 'lpg') {
                $q->orWhereRaw('1 = 1');
            } else {
                $q->where('jenis_gas', 'LIKE', "%{$cleanSearch}%")
                  ->orWhere('kategori', 'LIKE', "%{$cleanSearch}%");
                foreach ($allTerms as $term) {
                    if (strlen($term) >= 2 && $term !== 'gas') {
                        $q->orWhere('jenis_gas', 'LIKE', "%{$term}%")
                          ->orWhere('kategori', 'LIKE', "%{$term}%");
                    }
                }
            }
        });
        $gasResults = $gasQuery->get()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'name' => $item->jenis_gas,
                'image' => $item->foto,
                'price' => $item->harga_satuan,
                'price_formatted' => 'Rp ' . number_format($item->harga_satuan, 0, ',', '.'),
                'stock' => $item->stok,
                'type' => 'gas',
                'category' => 'Unit Penjualan Gas',
                'badge_color' => 'bg-orange-500 text-white',
                'real_category' => 'Gas',
                'unit' => 'tabung',
                'link' => route('gas.sales.show', $item->id)
            ];
        });

        // 3. Search Rental Items (Barang)
        $rentalQuery = \App\Models\Barang::query();
        $rentalQuery->where(function ($q) use ($allTerms, $cleanSearch, $rawSearch) {
            if (trim($rawSearch) === 'alat' || trim($rawSearch) === 'sewa alat') {
                $q->orWhereRaw('1 = 1');
            } else {
                $q->where('nama_barang', 'LIKE', "%{$cleanSearch}%")
                  ->orWhere('kategori', 'LIKE', "%{$cleanSearch}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$cleanSearch}%");
                foreach ($allTerms as $term) {
                    if (strlen($term) >= 2 && $term !== 'alat') {
                        $q->orWhere('nama_barang', 'LIKE', "%{$term}%")
                          ->orWhere('kategori', 'LIKE', "%{$term}%");
                    }
                }
            }
        });
        $rentalResults = $rentalQuery->get()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'name' => $item->nama_barang,
                'image' => $item->foto,
                'price' => $item->harga_sewa,
                'price_formatted' => 'Rp ' . number_format($item->harga_sewa, 0, ',', '.'),
                'stock' => $item->stok,
                'type' => 'rental',
                'category' => 'Unit Penyewaan Alat',
                'badge_color' => 'bg-emerald-600 text-white',
                'real_category' => $item->kategori,
                'unit' => $item->satuan ?? 'hari',
                'link' => route('rental.equipment.show', $item->id)
            ];
        });

        // 4. Search Fasilitas Umum Items
        $fasilitasQuery = \App\Models\FasilitasUmum::query();
        $fasilitasQuery->where(function ($q) use ($allTerms, $cleanSearch, $rawSearch) {
            if (trim($rawSearch) === 'fasilitas' || trim($rawSearch) === 'gedung') {
                $q->orWhereRaw('1 = 1');
            } else {
                $q->where('nama_fasilitas', 'LIKE', "%{$cleanSearch}%")
                  ->orWhere('kategori', 'LIKE', "%{$cleanSearch}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$cleanSearch}%");
                foreach ($allTerms as $term) {
                    if (strlen($term) >= 2 && $term !== 'fasilitas') {
                        $q->orWhere('nama_fasilitas', 'LIKE', "%{$term}%")
                          ->orWhere('kategori', 'LIKE', "%{$term}%");
                    }
                }
            }
        });
        $fasilitasResults = $fasilitasQuery->get()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'name' => $item->nama_fasilitas,
                'image' => $item->foto,
                'price' => 0,
                'price_formatted' => 'Izin Kegiatan',
                'stock' => $item->stok,
                'type' => 'fasilitas',
                'category' => 'Fasilitas Umum',
                'badge_color' => 'bg-purple-600 text-white',
                'real_category' => $item->kategori,
                'unit' => 'kegiatan',
                'link' => route('user.fasilitas-umum.show', $item->id)
            ];
        });

        // 5. Search Pasar Daerah Items
        $pasarQuery = \App\Models\PasarProduk::query();
        $pasarQuery->where(function ($q) use ($allTerms, $cleanSearch, $rawSearch) {
            if (trim($rawSearch) === 'pasar' || trim($rawSearch) === 'produk') {
                $q->orWhereRaw('1 = 1');
            } else {
                $q->where('nama_produk', 'LIKE', "%{$cleanSearch}%")
                  ->orWhere('kategori', 'LIKE', "%{$cleanSearch}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$cleanSearch}%");
                foreach ($allTerms as $term) {
                    if (strlen($term) >= 2 && $term !== 'pasar') {
                        $q->orWhere('nama_produk', 'LIKE', "%{$term}%")
                          ->orWhere('kategori', 'LIKE', "%{$term}%");
                    }
                }
            }
        });
        $pasarResults = $pasarQuery->get()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'name' => $item->nama_produk,
                'image' => $item->foto,
                'price' => $item->harga,
                'price_formatted' => 'Rp ' . number_format($item->harga, 0, ',', '.'),
                'stock' => $item->stok,
                'type' => 'pasar',
                'category' => 'Pasar Daerah',
                'badge_color' => 'bg-amber-600 text-white',
                'real_category' => $item->kategori,
                'unit' => $item->satuan ?? 'pcs',
                'link' => route('pasar.show', $item->id)
            ];
        });

        return collect([])
            ->concat($mobilResults)
            ->concat($gasResults)
            ->concat($rentalResults)
            ->concat($fasilitasResults)
            ->concat($pasarResults);
    }
}
