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
use App\Models\FasilitasUmum;
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

        $recentAnnouncements = $announcementQuery->take(3)->get();
        
        // Ambil Active Services jika user login dan punya region
        $activeServices = [];
        if (auth()->check() && auth()->user()->region_id) {
            $userRegion = \App\Models\Region::with(['services' => function($q) {
                $q->where('is_active', true);
            }])->find(auth()->user()->region_id);
            
            if ($userRegion) {
                $activeServices = $userRegion->services->pluck('name')->toArray();
            }
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
     * Get Popular Products (Top 4 most rented/sold items)
     */
    private function getPopularProducts($year = null, $regionId = 1)
    {
        $year = $year ?? (int)date('Y');
        $regionIds = array_merge([$regionId], \App\Models\Region::getDescendantIds($regionId));

        // 1. Get Rental Scores
        $rentalPopularity = RentalBooking::withTrashed()
            ->select('barang_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereYear('created_at', $year) // Filter by Year
            ->whereIn('region_id', $regionIds) // Filter by Region
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->whereNotNull('barang_id')
            ->groupBy('barang_id')
            ->with('barang')
            ->get();

        // 2. Map Rental to common format
        $products = $rentalPopularity->map(function ($item) {
            if (!$item->barang) return null;
            return (object) [
                'id' => $item->barang->id,
                'name' => $item->barang->nama_barang,
                'image' => $item->barang->foto,
                'price' => $item->barang->harga_sewa,
                'price_formatted' => 'Rp ' . number_format($item->barang->harga_sewa, 0, ',', '.'),
                'stock' => $item->barang->stok,
                'sold' => $item->total_sold,
                'type' => 'rental',
                'category' => 'Unit Penyewaan Alat',
                'unit' => $item->barang->satuan ?? 'unit',
                'link' => route('rental.equipment.show', $item->barang->id)
            ];
        })->filter();

        // 3. Get Gas Scores
        $gasPopularity = GasOrder::withTrashed()
            ->select('gas_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereYear('created_at', $year) // Filter by Year
            ->whereIn('region_id', $regionIds) // Filter by Region
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->whereNotNull('gas_id')
            ->groupBy('gas_id')
            ->with('gas')
            ->get();

        // 4. Map Gas to common format
        $gasProducts = $gasPopularity->map(function ($item) {
            if (!$item->gas) return null;
            return (object) [
                'id' => $item->gas->id,
                'name' => $item->gas->jenis_gas,
                'image' => $item->gas->foto,
                'price' => $item->gas->harga_satuan,
                'price_formatted' => 'Rp ' . number_format($item->gas->harga_satuan, 0, ',', '.'),
                'stock' => $item->gas->stok,
                'sold' => $item->total_sold,
                'type' => 'gas',
                'category' => 'Unit Penjualan Gas',
                'unit' => 'tabung',
                'link' => route('gas.sales.show', $item->gas->id)
            ];
        })->filter();

        // 5. Merge, Sort, Take 2 (Only Hot)
        return $products->concat($gasProducts)->sortByDesc('sold')->take(2);
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
            
            $rentalData[] = $rentalCount;
            $gasData[] = $gasCount;
            $mobilData[] = $mobilCount;
            $fasilitasData[] = $fasilitasCount;
            $laporanData[] = $laporanCount;
            $pengumumanData[] = $pengumumanCount;
        }
        
        return [
            'categories' => $months,
            'rental' => $rentalData,
            'gas' => $gasData,
            'mobil' => $mobilData,
            'fasilitas' => $fasilitasData,
            'laporan' => $laporanData,
            'pengumuman' => $pengumumanData
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
     * Perform global search across all modules
     */
    private function performGlobalSearch($search)
    {
        // Search Rental Items
        $rentalResults = \App\Models\Barang::searchWhereLike(['nama_barang', 'kategori'], $search)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'id' => $item->id,
                    'name' => $item->nama_barang,
                    'image' => $item->foto,
                    'price' => $item->harga_sewa,
                    'price_formatted' => 'Rp ' . number_format($item->harga_sewa, 0, ',', '.'),
                    'stock' => $item->stok,
                    'type' => 'rental',
                    'category' => 'Unit Penyewaan Alat',
                    'real_category' => $item->kategori,
                    'unit' => $item->satuan ?? 'unit',
                    'link' => route('rental.equipment.show', $item->id)
                ];
            });

        // Search Gas Items
        $gasResults = \App\Models\Gas::searchWhereLike(['jenis_gas', 'kategori'], $search)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'id' => $item->id,
                    'name' => $item->jenis_gas,
                    'image' => $item->foto,
                    'price' => $item->harga_satuan,
                    'price_formatted' => 'Rp ' . number_format($item->harga_satuan, 0, ',', '.'),
                    'stock' => $item->stok,
                    'type' => 'gas',
                    'category' => 'Unit Penjualan Gas',
                    'real_category' => 'Gas',
                    'unit' => 'tabung',
                    'link' => route('gas.sales.show', $item->id)
                ];
            });

        // Search Mobil Items
        $mobilResults = \App\Models\Mobil::searchWhereLike(['nama_mobil', 'kategori'], $search)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'id' => $item->id,
                    'name' => $item->nama_mobil,
                    'image' => $item->foto,
                    'price' => $item->harga_sewa,
                    'price_formatted' => 'Rp ' . number_format($item->harga_sewa, 0, ',', '.'),
                    'stock' => $item->stok,
                    'type' => 'mobil',
                    'category' => 'Unit Penyewaan Mobil',
                    'real_category' => $item->kategori,
                    'unit' => $item->satuan ?? 'hari',
                    'link' => route('mobil.rental.show', $item->id)
                ];
            });

        // Search Fasilitas Umum Items
        $fasilitasResults = \App\Models\FasilitasUmum::searchWhereLike(['nama_fasilitas', 'kategori'], $search)
            ->get()
            ->map(function ($item) {
                return (object) [
                    'id' => $item->id,
                    'name' => $item->nama_fasilitas,
                    'image' => $item->foto,
                    'price' => 0,
                    'price_formatted' => 'Peminjaman',
                    'stock' => $item->stok,
                    'type' => 'fasilitas',
                    'category' => 'Fasilitas Umum',
                    'real_category' => $item->kategori,
                    'unit' => 'kegiatan',
                    'link' => route('fasilitas.show', $item->id)
                ];
            });

        // Search BUMDes Members
        $bumdesResults = \App\Models\BumdesMember::searchWhereLike(['name', 'position'], $search)
            ->get()
            ->map(function ($item) {
                $photoUrl = $item->photo ? $item->photo : 'User/img/avatars/logodomain.png';
                return (object) [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image' => $photoUrl,
                    'price' => 0,
                    'price_formatted' => $item->position,
                    'stock' => 0,
                    'type' => 'profile',
                    'category' => 'Profil BUMDes',
                    'real_category' => 'Personil',
                    'unit' => '',
                    'link' => route('bumdes.detail')
                ];
            });

        // Search Static Developers
        $developers = [
            [
                'name' => 'Rizqy Hamadi Ken',
                'image' => 'User/img/avatars/ken.png',
                'position' => 'Pengembang SiladesBeng',
                'link' => '#'
            ],
            [
                'name' => 'Mushlihul Arif',
                'image' => 'User/img/avatars/ayep123.jpg',
                'position' => 'Pengembang SiladesBeng',
                'link' => '#'
            ],
            [
                'name' => 'Dicki Wahyudi',
                'image' => 'User/img/avatars/dicki.png',
                'position' => 'Pengembang SiladesBeng',
                'link' => '#'
            ]
        ];

        $developerResults = collect($developers)->filter(function ($dev) use ($search) {
            $cleanSearch = strtolower(str_replace(['desa ', 'kelurahan ', 'kecamatan ', 'kabupaten '], '', strtolower($search)));
            return \Illuminate\Support\Str::contains(strtolower($dev['name']), $cleanSearch) || 
                   \Illuminate\Support\Str::contains(strtolower($dev['position']), $cleanSearch);
        })->map(function ($dev) {
            return (object) [
                'id' => 'dev-' . \Illuminate\Support\Str::slug($dev['name']),
                'name' => $dev['name'],
                'image' => $dev['image'],
                'price' => 0,
                'price_formatted' => $dev['position'],
                'stock' => 0,
                'type' => 'developer',
                'category' => 'Tim SiladesBeng',
                'real_category' => 'Pengembang',
                'unit' => '',
                'link' => $dev['link']
            ];
        });

        // Search Region
        $regionResults = \App\Models\Region::with('parent')->where('name', 'LIKE', "%{$search}%")
            ->get()
            ->map(function ($item) {
                $typeStr = ucfirst($item->type ?? 'Wilayah');
                $parentStr = $item->parent ? $item->parent->name : 'Kabupaten Bengkalis';
                $description = "{$typeStr} dari {$parentStr}";

                return (object) [
                    'id' => $item->id,
                    'name' => $item->name,
                    'image' => null,
                    'price' => 0,
                    'price_formatted' => $description,
                    'stock' => 0,
                    'type' => 'region',
                    'category' => 'Wilayah / Desa',
                    'real_category' => 'Wilayah',
                    'unit' => '',
                    'link' => '#'
                ];
            });

        return collect([])
            ->concat($rentalResults)
            ->concat($gasResults)
            ->concat($mobilResults)
            ->concat($fasilitasResults)
            ->concat($bumdesResults)
            ->concat($regionResults)
            ->concat($developerResults)
            ->values()
            ->toArray();
    }
}