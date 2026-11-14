<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Barang;
use App\Models\Gas;
use App\Models\RentalBooking;
use App\Models\RentalRequest;
use App\Models\GasOrder;
use App\Models\ManualReport;
use App\Models\Mobil;
use App\Models\FasilitasUmum;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
   /**
 * Tampilkan Dashboard
 */
public function index(Request $request)
{
    // Super Admin memakai dashboard platform; staf memakai dashboard layanan yang ditugaskan.
    if (auth()->user()->isStaff()) return view('admin.staff.dashboard');
    if (auth()->user()->bolehAksesPlatform()) {
        return app(SuperAdminDashboardController::class)->index();
    }

    abort_unless(auth()->user()->role === 'admin_desa' && auth()->user()->region_id, 403);
    // Ambil pemesanan penyewaan yang tertunda atau minta batal
    $selectedYear = $request->input('year', now()->year);
    
    // Ambil daftar tahun yang tersedia dari database
    $rentalYears = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)
        ->selectRaw('YEAR(created_at) as year')
        ->distinct()
        ->pluck('year')
        ->toArray();
        
    $gasYears = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)
        ->selectRaw('YEAR(created_at) as year')
        ->distinct()
        ->pluck('year')
        ->toArray();

    $mobilYears = $this->applyRegionFilter(\App\Models\MobilBooking::withTrashed(), 'mobil', true)
        ->selectRaw('YEAR(created_at) as year')
        ->distinct()
        ->pluck('year')
        ->toArray();

    $fasilitasYears = $this->applyRegionFilter(\App\Models\FasilitasUmumBooking::withTrashed(), 'fasilitas', true)
        ->selectRaw('YEAR(created_at) as year')
        ->distinct()
        ->pluck('year')
        ->toArray();

        
    $availableYears = array_unique(array_merge($rentalYears, $gasYears, $mobilYears, $fasilitasYears, [now()->year]));
    rsort($availableYears);

    $baseRental = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true);
    $baseGas = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true);
    $baseMobil = $this->applyRegionFilter(\App\Models\MobilBooking::withTrashed(), 'mobil', true);
    $baseFasilitas = $this->applyRegionFilter(\App\Models\FasilitasUmumBooking::withTrashed(), 'fasilitas', true);
    $baseKyc = $this->applyRegionFilter(\App\Models\KycVerification::query(), 'user');

    // Dashboard operasional dibatasi ke desa-desa Kecamatan Bengkalis.
    $selectedKecamatanId = \App\Models\Region::where('type', 'kecamatan')
        ->where('name', 'Kecamatan Bengkalis')
        ->value('id');
    abort_if(! $selectedKecamatanId, 404, 'Kecamatan Bengkalis belum terdaftar.');
    $selectedDesaId = auth()->user()->region_id;

    $latestRequests = collect();
    $totalPending = 0;

    if (in_array(auth()->user()->role, ['admin_desa', 'admin_rw', 'admin_rt', 'super_admin'])) {
        $rentalRequests = $baseRental->clone()->with(['user', 'barang'])->where(function($q) { $q->where('status', 'pending')->orWhere('cancellation_status', 'pending'); })->get()->map(function ($i) { $i->type = 'rental'; $i->item_name = $i->barang->nama_barang ?? 'Alat'; return $i; });
        $gasRequests = $baseGas->clone()->with('user')->where(function($q) { $q->where('status', 'pending')->orWhere('cancellation_status', 'pending'); })->get()->map(function ($i) { $i->type = 'gas'; $i->item_name = $i->item_name ?? 'Tabung Gas'; return $i; });
        
        $mobilRequests = $baseMobil->clone()->with(['user', 'mobil' => function($q) { $q->withTrashed(); }])->where(function($q) { $q->where('status', 'pending')->orWhere('cancellation_status', 'pending'); })->get()->map(function ($i) { $i->type = 'mobil'; $i->item_name = $i->mobil->nama_mobil ?? 'Mobil'; return $i; });
        
        $fasilitasRequests = $baseFasilitas->clone()->with(['user', 'fasilitas' => function($q) { $q->withTrashed(); }])->where(function($q) { $q->where('status', 'pending')->orWhere('cancellation_status', 'pending'); })->get()->map(function ($i) { $i->type = 'fasilitas_umum'; $i->item_name = $i->fasilitas->nama_fasilitas ?? 'Fasilitas'; return $i; });
        
        
        $kycRequests = collect();
        if (in_array(auth()->user()->role, ['admin_desa'])) {
            $kycRequests = $baseKyc->clone()->with('user')
                ->where('status', 'pending')
                ->where(function($q) {
                    $q->whereNotNull('face_scan_data')->orWhereNotNull('face_image_path');
                })
                ->get()->map(function ($i) { $i->type = 'kyc'; $i->item_name = 'Verifikasi Identitas (KYC)'; return $i; });
        }

        $baseMutasi = \App\Models\MutasiPenduduk::query();
        $adminUser = auth()->user();
        if ($adminUser && in_array($adminUser->role, ['admin_desa', 'admin_rw', 'admin_rt'])) {
            $mutasiRegionIds = array_merge([(int)$adminUser->region_id], \App\Models\Region::getDescendantIds($adminUser->region_id));
            $baseMutasi->where(function($q) use ($mutasiRegionIds) {
                $q->whereIn('from_region_id', $mutasiRegionIds)
                  ->orWhereIn('to_region_id', $mutasiRegionIds);
            });
        }
        $mutasiRequests = $baseMutasi->clone()->with('user')->where('status', 'pending')->get()->map(function ($i) {
            $i->type = 'mutasi';
            $i->item_name = 'Pengajuan Mutasi Domisili';
            return $i;
        });

        $latestRequests = collect()
            ->concat($rentalRequests)
            ->concat($gasRequests)
            ->concat($mobilRequests)
            ->concat($fasilitasRequests)
            ->concat($kycRequests)
            ->concat($mutasiRequests)
            ->sortByDesc('created_at')
            ->take(10);
        
        $totalPending = $rentalRequests->count() 
            + $gasRequests->count() 
            + $mobilRequests->count() 
            + $fasilitasRequests->count() 
            + $kycRequests->count()
            + $mutasiRequests->count();
    }

    // Hitung statistik nyata (semua unit layanan aktif)
    $totalOrders = $baseRental->clone()->count() 
        + $baseGas->clone()->count() 
        + $baseMobil->clone()->count() 
        + $baseFasilitas->clone()->count() 
;
    
    // Hitung total order selesai/sukses
    $completedRentals = $baseRental->clone()->where('status', 'completed')->count();
    $completedGas = $baseGas->clone()->where('status', 'completed')->count();
    $completedMobil = $baseMobil->clone()->whereIn('status', ['completed', 'resolved'])->count();
    $completedFasilitas = $baseFasilitas->clone()->whereIn('status', ['completed', 'resolved'])->count();
    $completedOrders = $completedRentals + $completedGas + $completedMobil + $completedFasilitas;

    // Hitung statistik untuk Donut Chart (Total Transaksi per Kategori) - Filter Tahun Ini & Tidak Cancel
    $rentalCount = $baseRental->clone()->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->count();

    $gasCount = $baseGas->clone()->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->count();

    $mobilCount = $baseMobil->clone()->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->count();

    $fasilitasCount = $baseFasilitas->clone()->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->count();


    // ========================================
    // PERHITUNGAN DATA NYATA UNTUK GRAFIK
    // ========================================
    
    // Hitung performa bulanan (total transaksi per bulan)
    $monthlyPerformance = array_fill(0, 12, 0);
    
    $rentalCounts = $baseRental->clone()
        ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
        ->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->groupBy('month')
        ->pluck('count', 'month');

    $gasCounts = $baseGas->clone()
        ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
        ->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->groupBy('month')
        ->pluck('count', 'month');

    $mobilCounts = $baseMobil->clone()
        ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
        ->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->groupBy('month')
        ->pluck('count', 'month');

    $fasilitasCounts = $baseFasilitas->clone()
        ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
        ->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->groupBy('month')
        ->pluck('count', 'month');


    for ($month = 1; $month <= 12; $month++) {
        $monthlyPerformance[$month - 1] = ($rentalCounts[$month] ?? 0) 
            + ($gasCounts[$month] ?? 0) 
            + ($mobilCounts[$month] ?? 0) 
            + ($fasilitasCounts[$month] ?? 0) 
;
    }
    
    // Hitung pendapatan dan pengeluaran bulanan (SAMA SEPERTI ReportController)
    $monthlyIncome = [
        'Januari' => 0,
        'Februari' => 0,
        'Maret' => 0,
        'April' => 0,
        'Mei' => 0,
        'Juni' => 0,
        'Juli' => 0,
        'Agustus' => 0,
        'September' => 0,
        'Oktober' => 0,
        'November' => 0,
        'Desember' => 0,
    ];
    
    // Hanya pembayaran yang sudah diperiksa; persetujuan pesanan belum berarti lunas.
    foreach (['rental', 'gas', 'mobil', 'fasilitas'] as $type) {
        foreach ($this->receivedPayments($type)->whereYear('created_at', $selectedYear)
            ->selectRaw('SUM(amount) as total, MONTH(created_at) as month')->groupBy('month')->pluck('total', 'month') as $month => $amount) {
            $monthlyIncome[self::getMonthName($month)] += $amount;
        }
    }

    // Pendapatan dari laporan manual
    $strictManual = $this->applyRegionFilter(ManualReport::query(), 'creator', true);
    foreach ($strictManual->clone()->selectRaw('SUM(amount * quantity) as total, MONTH(transaction_date) as month')
        ->whereYear('transaction_date', $selectedYear)
        ->groupBy('month')
        ->pluck('total', 'month') as $month => $amount) {
        $monthlyIncome[self::getMonthName($month)] += $amount;
    }
    
    // Pengeluaran - setel ke 0 untuk saat ini
    $monthlyExpenses = array_fill(0, 12, 0);
    
    // Hitung statistik item populer (data nyata dari database) - Filter Tahun Ini
    $popularItems = [
        'gas_lpg_3kg' => $baseGas->clone()->whereYear('created_at', $selectedYear)->where('item_name', 'LIKE', '%3%')->count(),
        'sound_system' => $baseRental->clone()->whereYear('created_at', $selectedYear)->whereHas('barang', function($q) {
                $q->where('nama_barang', 'LIKE', '%Sound%');
            })->count(),
        'tenda_komplit' => $baseRental->clone()->whereYear('created_at', $selectedYear)->whereHas('barang', function($q) {
                $q->where('nama_barang', 'LIKE', '%Tenda%');
            })->count(),
        'meja_kursi' => $baseRental->clone()->whereYear('created_at', $selectedYear)->whereHas('barang', function($q) {
                $q->where('nama_barang', 'LIKE', '%Meja%')
                  ->orWhere('nama_barang', 'LIKE', '%Kursi%');
            })->count(),
    ];

    $baseUser = $this->managedUsers();

    // Dropdown hanya memuat desa di Kecamatan Bengkalis.
    $desaList = \App\Models\Region::where('parent_id', $selectedKecamatanId)
        ->whereIn('type', ['desa', 'kelurahan'])
        ->orderBy('name')
        ->get();

    if (auth()->user()->role === 'admin_desa') {
        $desaList = \App\Models\Region::where('id', auth()->user()->region_id)->get();
    }

    $data = [
        'totalUsers' => $baseUser->clone()->count(),
        'totalOrders' => $totalOrders,
        'newUsers' => $baseUser->clone()->whereDate('created_at', '>=', now()->subMonth())->count(),
        'completedOrders' => $completedOrders,
        'latestRequests' => $latestRequests,
        'totalPending' => $totalPending,
        'rentalCount' => $rentalCount,
        'gasCount' => $gasCount,
        'mobilCount' => $mobilCount,
        'fasilitasCount' => $fasilitasCount ?? 0,
        'selectedYear' => $selectedYear,
        'availableYears' => $availableYears,
        'desaList' => $desaList,
        'selectedKecamatanId' => $selectedKecamatanId,
        'selectedDesaId' => $selectedDesaId,
        // Data nyata untuk grafik
        'monthlyPerformance' => $monthlyPerformance,
        'monthlyIncome' => $monthlyIncome,
        'monthlyExpenses' => $monthlyExpenses,
        'popularItems' => $popularItems,
    ];

    // Ambil jumlah item untuk setiap unit layanan
    $adminUser = auth()->user();
    $adminRegionId = $adminUser ? $adminUser->region_id : null;

    $data['unitPenyewaan'] = Barang::where('region_id', $adminRegionId)->count();
    $data['unitGas'] = Gas::where('region_id', $adminRegionId)->count();
    $data['unitMobil'] = Mobil::inCatalog()->forRental()
        ->when($adminRegionId, fn($q) => $q->where('region_id', $adminRegionId))
        ->count();
    $data['unitFasilitas'] = FasilitasUmum::inCatalog()->where('region_id', $adminRegionId)->count();

    // Ambil data Total Pendapatan untuk grafik baru (Pastikan pass selectedYear)
    // Override request year jika diperlukan agar konsisten
    $request->merge(['year' => $selectedYear]);
    $data['totalPendapatanData'] = $this->getTotalPendapatanData($selectedYear);
    
    // Ambil Produk Populer (pass selectedYear)
    $data['popularProducts'] = $this->getPopularProducts($selectedYear);

    // Ambil Active Services jika user login dan punya region
    $activeServices = [];
    $user = auth()->user();
    if ($user && $user->region_id) {
        $userRegion = \App\Models\Region::with(['services' => function($q) {
            $q->where('is_active', true);
        }])->find($user->region_id);
        
        if ($userRegion) {
            $activeServices = $userRegion->services->pluck('name')->unique()->values()->toArray();
        }
    }

    // Staf unit memakai region_id warisan pembuatnya, jadi $activeServices di
    // atas berisi SELURUH layanan aktif wilayah itu. Dipersempit ke unit yang
    // benar-benar dipegangnya, supaya dashboard menampilkan pekerjaannya
    // sendiri — bukan unit milik rekan yang tidak bisa ia buka.
    if ($user && $user->role === 'staff') {
        $activeServices = array_values(
            array_intersect($activeServices, $user->layananDipegang())
        );
    }

    $data['activeServices'] = $activeServices;

    return view('admin.dashboard.index', $data);
}

/**
 * Ambil data Total Pendapatan - Rincian pendapatan berdasarkan unit
 */
    private function managedUsers()
    {
        $user = auth()->user();
        abort_unless($user && in_array($user->role, ['admin_desa', 'super_admin'], true), 403);
        if ($user->role === 'super_admin') return User::query();
        abort_unless($user->region_id, 403);
        return User::whereIn('region_id', array_merge([$user->region_id], \App\Models\Region::getDescendantIds($user->region_id)));
    }

    private function receivedPayments(string $type)
    {
        $query = \App\Models\WalletTransaction::where('reference_type', $type)->where('status', 'verified')->where('type', 'masuk');
        $user = auth()->user();
        if (!$user?->region_id || !$user->isAdmin()) return $query->whereRaw('1 = 0');
        return $query->where('region_id', $user->region_id);
    }

    private function getTotalPendapatanData($selectedYear = null)
    {
        // Ambil bulan/tahun saat ini atau dari permintaan
        $month = request('month', date('m'));
        
        // Gunakan tahun yang dipilih jika ada, jika tidak ambil dari request, jika tidak tahun ini
        $year = $selectedYear ?? request('year', date('Y'));
        
        foreach (['rental', 'gas', 'mobil', 'fasilitas'] as $type) {
            $paid = $this->receivedPayments($type)->whereYear('created_at', $year)->whereMonth('created_at', $month);
            ${$type.'Revenue'} = (float) (clone $paid)->sum('amount');
            ${$type.'Transactions'} = (clone $paid)->distinct()->count('reference_id');
        }

        // Pendapatan Laporan Manual
        $manualRevenue = $this->applyRegionFilter(ManualReport::query(), 'creator', true)->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->sum(\DB::raw('amount * quantity'));
        
        $manualTransactions = $this->applyRegionFilter(ManualReport::query(), 'creator', true)->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->count();
        
        $totalRevenue = $rentalRevenue + $gasRevenue + $mobilRevenue + $fasilitasRevenue + $manualRevenue;
        $totalTransactions = $rentalTransactions + $gasTransactions + $mobilTransactions + $fasilitasTransactions + $manualTransactions;
        
        return [
            'Penyewaan Alat' => [
                'revenue' => $rentalRevenue,
                'transactions' => $rentalTransactions,
                'percentage' => $totalRevenue > 0 ? round(($rentalRevenue / $totalRevenue) * 100, 1) : 0,
                'color' => 'warning'
            ],
            'Penjualan Gas' => [
                'revenue' => $gasRevenue,
                'transactions' => $gasTransactions,
                'percentage' => $totalRevenue > 0 ? round(($gasRevenue / $totalRevenue) * 100, 1) : 0,
                'color' => 'danger'
            ],
            'Penyewaan Mobil' => [
                'revenue' => $mobilRevenue,
                'transactions' => $mobilTransactions,
                'percentage' => $totalRevenue > 0 ? round(($mobilRevenue / $totalRevenue) * 100, 1) : 0,
                'color' => 'info'
            ],
            'Fasilitas Umum' => [
                'revenue' => $fasilitasRevenue,
                'transactions' => $fasilitasTransactions,
                'percentage' => $totalRevenue > 0 ? round(($fasilitasRevenue / $totalRevenue) * 100, 1) : 0,
                'color' => 'success'
            ],
'total' => [
                'revenue' => $totalRevenue,
                'transactions' => $totalTransactions
            ],
            'month' => $month,
            'year' => $year
        ];
    }

    /**
     * Pencarian Global - Cari di semua tabel
     */
    public function globalSearch(Request $request)
    {
        $search = $request->get('search');
        
        if (!$search) {
            return redirect()->route('admin.dashboard');
        }
        
        // Cari di Produk Penyewaan (Barang)
        $rentalProducts = Barang::where('region_id', $request->user()->region_id)
            ->when(!$request->user()->hasUnitPermission('sewa_alat'), fn ($q) => $q->whereRaw('1 = 0'))
            ->searchWhereLike(['nama_barang', 'kategori'], $search)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'rental_product',
                    'title' => $item->nama_barang,
                    'subtitle' => 'Kategori: ' . $item->kategori,
                    'description' => $item->deskripsi,
                    'image' => $item->foto,
                    'link' => route('admin.unit.penyewaan.index'),
                    'badge' => 'Penyewaan Alat',
                    'badge_color' => 'primary'
                ];
            });
        
        // Cari di Produk Gas
        $gasProducts = Gas::where('region_id', $request->user()->region_id)->searchWhereLike(['jenis_gas', 'kategori'], $search)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'gas_product',
                    'title' => $item->jenis_gas,
                    'subtitle' => 'Kategori: ' . $item->kategori,
                    'description' => $item->deskripsi,
                    'image' => $item->foto,
                    'link' => route('admin.unit.penjualan_gas.index'),
                    'badge' => 'Penjualan Gas',
                    'badge_color' => 'warning'
                ];
            });
        
        // Cari di Pengguna
        $users = $this->managedUsers()->searchWhereLike(['name', 'email'], $search)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'user',
                    'title' => $item->name,
                    'subtitle' => $item->email,
                    'description' => 'Bergabung: ' . ($item->created_at?->format('d M Y') ?? '-'),
                    'image' => $item->avatar,
                    'link' => route('admin.manajemen-pengguna.index'),
                    'badge' => 'Pengguna',
                    'badge_color' => 'success'
                ];
            });
        
        // Cari di Anggota BUMDes
        $bumdesMembers = \App\Models\BumdesMember::where('region_id', $request->user()->region_id)->searchWhereLike(['name', 'position'], $search)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'bumdes_member',
                    'title' => $item->name,
                    'subtitle' => $item->position,
                    'description' => 'Anggota Struktur BUMDes',
                    'image' => $item->photo,
                    'link' => route('admin.SiladesBeng.bumdes.index'),
                    'badge' => 'Profil Pemerintah Daerah',
                    'badge_color' => 'info'
                ];
            });
        
        // Cari di Transaksi (Penyewaan)
        $rentalTransactions = $this->applyRegionFilter(RentalBooking::with('user'), 'barang', true)
            ->where(function($q) use ($search) {
                $q->searchWhereLike('status', $search)
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->searchWhereLike('name', $search);
                  });
            })
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'rental_transaction',
                    'title' => 'Transaksi Penyewaan #' . $item->id,
                    'subtitle' => 'User: ' . ($item->user->name ?? 'N/A'),
                    'description' => 'Status: ' . ucfirst($item->status) . ' | Tanggal: ' . ($item->created_at?->format('d M Y') ?? '-'),
                    'image' => null,
                    'link' => route('admin.aktivitas.permintaan-pengajuan.index'),
                    'badge' => 'Transaksi',
                    'badge_color' => 'secondary'
                ];
            });
        
        
        // Cari di Transaksi (Gas)
        $gasTransactions = $this->applyRegionFilter(GasOrder::with('user'), 'gas', true)
            ->where(function($q) use ($search) {
                $q->searchWhereLike('status', $search)
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->searchWhereLike('name', $search);
                  });
            })
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'gas_transaction',
                    'title' => 'Transaksi Gas #' . $item->id,
                    'subtitle' => 'User: ' . ($item->user->name ?? 'N/A'),
                    'description' => 'Status: ' . ucfirst($item->status) . ' | Rp ' . number_format($item->price * $item->quantity, 0, ',', '.'),
                    'image' => null,
                    'link' => route('admin.aktivitas.permintaan-pengajuan.index'),
                    'badge' => 'Transaksi',
                    'badge_color' => 'secondary'
                ];
            });
        
        // Gabungkan semua hasil
        $results = collect()
            ->concat($rentalProducts)
            ->concat($gasProducts)
            ->concat($users)
            ->concat($bumdesMembers)
            ->concat($rentalTransactions)
            ->concat($gasTransactions);
        
        $totalResults = $results->count();
        
        return view('admin.dashboard.search-results', compact('results', 'search', 'totalResults'));
    }


    /**
     * Tampilkan Halaman Profil
     */
    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile.MyProfile', compact('user'));
    }

    /**
     * Perbarui Profil
     */
    public function profileUpdate(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
        ], [
            'name.required' => 'Name is required',
            'username.required' => 'Username is required',
            'username.unique' => 'Username already exists',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Tampilkan Halaman Pengaturan
     */
    public function settings()
    {
        $user = Auth::user();
        return view('admin.settings.MySettings', compact('user'));
    }

    /**
     * Perbarui Pengaturan (Ganti Password)
     */
    public function settingsUpdate(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ], [
            'current_password.required' => 'Current password is required',
            'password.required' => 'New password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
        ]);

        // Periksa apakah password saat ini benar
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        // Perbarui password
        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return back()->with('success', 'Password changed successfully!');
    }

    /**
     * Tampilkan Daftar Pengguna
     */
    public function usersList()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Tampilkan Formulir Buat Pengguna
     */
    public function usersCreate()
    {
        return view('admin.users.create');
    }

    /**
     * Simpan Pengguna Baru
     */
    public function usersStore(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ], [
            'username.required' => 'Username is required',
            'username.unique' => 'Username already exists',
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
        ]);

        User::create([
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.manajemen-pengguna.index')->with('success', 'User created successfully!');
    }

    /**
     * Tampilkan Formulir Edit Pengguna
     */
    public function usersEdit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Perbarui Pengguna
     */
    public function usersUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ], [
            'username.required' => 'Username is required',
            'username.unique' => 'Username already exists',
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
        ]);

        // Perbarui password jika disediakan
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|min:8|confirmed',
            ], [
                'password.min' => 'Password must be at least 8 characters',
                'password.confirmed' => 'Password confirmation does not match',
            ]);

            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->route('admin.manajemen-pengguna.index')->with('success', 'User updated successfully!');
    }

    /**
     * Hapus Pengguna
     */
    public function usersDestroy($id)
    {
        $user = User::findOrFail($id);

        // Cegah penghapusan akun sendiri
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.manajemen-pengguna.index')->with('error', 'You cannot delete your own account!');
        }
        $user->delete();

        return redirect()->route('admin.manajemen-pengguna.index')->with('success', 'User deleted successfully!');
    }
    /**
     * Tampilkan Halaman Koneksi
     */
    public function connections()
    {
        $user = Auth::user();
        return view('admin.settings.connections', compact('user'));
    }
    /**
     * Tampilkan Halaman Notifikasi
     */
    public function notifications()
    {
        $user = Auth::user();
        return view('admin.settings.notifications', compact('user'));
    }
    /**
     * Perbarui Pengaturan Notifikasi
     */
    public function notificationsUpdate(Request $request)
    {
        // Retire the old template form: it never stored or applied preferences.
        return redirect()->route('admin.settings.notifications')
            ->with('info', 'Notifikasi layanan dapat dibaca melalui pusat notifikasi.');
    }
    /**
     * Tampilkan Halaman Pemeliharaan
     */
    public function maintenance()
    {
        return response()->view('errors.503', [], 503);
    }

    /**
     * Ambil Produk Populer (4 item paling banyak disewa/dijual)
     */
    private function getPopularProducts($year = null)
    {
        $year = $year ?? date('Y');

        // 1. Ambil Skor Penyewaan
        $rentalPopularity = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)->select('barang_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->whereNotNull('barang_id')
            ->groupBy('barang_id')
            ->with('barang')
            ->get();

        // 2. Petakan Penyewaan ke format umum
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
                'link' => route('admin.unit.penyewaan.show', $item->barang->id)
            ];
        })->filter();

        // 3. Ambil Skor Gas
        $gasPopularity = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)->select('gas_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->whereNotNull('gas_id')
            ->groupBy('gas_id')
            ->with('gas')
            ->get();

        // 4. Petakan Gas ke format umum
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
                'link' => route('admin.unit.penjualan_gas.show', $item->gas->id)
            ];
        })->filter();

        // 5. Ambil Skor Mobil
        $mobilPopularity = $this->applyRegionFilter(\App\Models\MobilBooking::withTrashed(), 'mobil', true)->select('mobil_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->whereNotNull('mobil_id')
            ->groupBy('mobil_id')
            ->with('mobil')
            ->get();

        // 6. Petakan Mobil ke format umum
        $mobilProducts = $mobilPopularity->map(function ($item) {
            if (!$item->mobil) return null;
            return (object) [
                'id' => $item->mobil->id,
                'name' => $item->mobil->nama_mobil,
                'image' => $item->mobil->foto,
                'price' => $item->mobil->harga_sewa,
                'price_formatted' => 'Rp ' . number_format($item->mobil->harga_sewa, 0, ',', '.'),
                'stock' => $item->mobil->stok,
                'sold' => $item->total_sold,
                'type' => 'mobil',
                'category' => 'Unit Penyewaan Transportasi',
                'unit' => $item->mobil->satuan ?? 'unit',
                'link' => route('admin.unit.mobil.show', $item->mobil->id)
            ];
        })->filter();

        // 7. Gabungkan semua, Urutkan, Ambil 4 teratas
        return $products->concat($gasProducts)->concat($mobilProducts)->sortByDesc('sold')->take(4);
    }

    /**
     * Helper: Konversi nomor bulan ke nama bulan Indonesia
     */
    private static function getMonthName($month)
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[$month] ?? 'Unknown';
    }
}
