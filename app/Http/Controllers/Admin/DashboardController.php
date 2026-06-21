<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Barang;
use App\Models\Gas;
use App\Models\RentalBooking;
use App\Models\RentalRequest;
use App\Models\GasOrder;
use App\Models\ManualReport;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
   /**
 * Tampilkan Dashboard
 */
public function index(Request $request)
{
    // Ambil pemesanan penyewaan yang tertunda atau minta batal
    $selectedYear = $request->input('year', now()->year);
    Log::info('DashboardController: Starting index. Selected Year: ' . $selectedYear);
    
    // Ambil daftar tahun yang tersedia dari database
    $rentalYears = RentalBooking::withTrashed()
        ->selectRaw('YEAR(created_at) as year')
        ->distinct()
        ->pluck('year')
        ->toArray();
        
    $gasYears = GasOrder::withTrashed()
        ->selectRaw('YEAR(created_at) as year')
        ->distinct()
        ->pluck('year')
        ->toArray();
        
    $availableYears = array_unique(array_merge($rentalYears, $gasYears, [now()->year]));
    rsort($availableYears);

    $rentalRequests = RentalBooking::withTrashed()->with(['user', 'barang'])
        ->where(function($q) {
            $q->where('status', 'pending')
              ->orWhere('cancellation_status', 'pending');
        })
        ->get()
        ->map(function ($item) {
            $item->type = 'rental';
            $item->item_name = $item->barang->nama_barang ?? 'Unknown Item';
            return $item;
        });
    Log::info('DashboardController: Rental requests fetched. Count: ' . $rentalRequests->count());

    // Ambil pesanan gas yang tertunda atau minta batal
    $gasRequests = GasOrder::withTrashed()->with('user')
        ->where(function($q) {
             $q->where('status', 'pending')
               ->orWhere('cancellation_status', 'pending');
        })
        ->get()
        ->map(function ($item) {
            $item->type = 'gas';
            $item->item_name = $item->item_name ?? 'Gas Order'; 
            return $item;
        });

    // Gabungkan dan urutkan berdasarkan created_at desc
    $latestRequests = $rentalRequests->concat($gasRequests)->sortByDesc('created_at')->take(5);

    // Hitung statistik nyata
    $totalOrders = RentalBooking::withTrashed()->count() + GasOrder::withTrashed()->count();
    
    // Hitung total order selesai/sukses
    $completedRentals = RentalBooking::withTrashed()->where('status', 'completed')->count();
    $completedGas = GasOrder::withTrashed()->where('status', 'completed')->count();
    $completedOrders = $completedRentals + $completedGas;

    // Hitung statistik untuk Donut Chart (Total Transaksi per Kategori) - Filter Tahun Ini & Tidak Cancel
    $rentalCount = RentalBooking::withTrashed()->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->count();

    $gasCount = GasOrder::withTrashed()->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->count();

    $mobilCount = \App\Models\MobilBooking::withTrashed()->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->count();

    $totalPending = $rentalRequests->count() + $gasRequests->count();

    // ========================================
    // PERHITUNGAN DATA NYATA UNTUK GRAFIK
    // ========================================
    
    // Hitung performa bulanan (total transaksi per bulan)
    $monthlyPerformance = [];
    
    for ($month = 1; $month <= 12; $month++) {
        $rentalMonthCount = RentalBooking::withTrashed()->whereMonth('created_at', $month)
            ->whereYear('created_at', $selectedYear)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->count();
        
        $gasMonthCount = GasOrder::withTrashed()->whereMonth('created_at', $month)
            ->whereYear('created_at', $selectedYear)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->count();
        
        $monthlyPerformance[] = $rentalMonthCount + $gasMonthCount;
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
    
    // Pendapatan dari sistem (RentalBooking)
    foreach (RentalBooking::withTrashed()->selectRaw('SUM(total_amount) as total, MONTH(created_at) as month')
        ->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->groupBy('month')
        ->pluck('total', 'month') as $month => $amount) {
        $monthlyIncome[self::getMonthName($month)] += $amount;
    }

    // Pendapatan dari pesanan gas
    foreach (GasOrder::withTrashed()->selectRaw('SUM(price * quantity) as total, MONTH(created_at) as month')
        ->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->groupBy('month')
        ->pluck('total', 'month') as $month => $amount) {
        $monthlyIncome[self::getMonthName($month)] += $amount;
    }
    
    // Pendapatan dari laporan manual
    foreach (ManualReport::selectRaw('SUM(amount * quantity) as total, MONTH(transaction_date) as month')
        ->whereYear('transaction_date', $selectedYear)
        ->groupBy('month')
        ->pluck('total', 'month') as $month => $amount) {
        $monthlyIncome[self::getMonthName($month)] += $amount;
    }
    
    // Pengeluaran - setel ke 0 untuk saat ini
    $monthlyExpenses = array_fill(0, 12, 0);
    
    // Hitung statistik item populer (data nyata dari database) - Filter Tahun Ini
    $popularItems = [
        'gas_lpg_3kg' => GasOrder::withTrashed()->whereYear('created_at', $selectedYear)->where('item_name', 'LIKE', '%3%')->count(),
        'sound_system' => RentalBooking::withTrashed()->whereYear('created_at', $selectedYear)->whereHas('barang', function($q) {
                $q->where('nama_barang', 'LIKE', '%Sound%');
            })->count(),
        'tenda_komplit' => RentalBooking::withTrashed()->whereYear('created_at', $selectedYear)->whereHas('barang', function($q) {
                $q->where('nama_barang', 'LIKE', '%Tenda%');
            })->count(),
        'meja_kursi' => RentalBooking::withTrashed()->whereYear('created_at', $selectedYear)->whereHas('barang', function($q) {
                $q->where('nama_barang', 'LIKE', '%Meja%')
                  ->orWhere('nama_barang', 'LIKE', '%Kursi%');
            })->count(),
    ];

    $data = [
        'totalUsers' => User::count(),
        'totalOrders' => $totalOrders,
        'newUsers' => User::whereDate('created_at', '>=', now()->subMonth())->count(),
        'completedOrders' => $completedOrders,
        'latestRequests' => $latestRequests,
        'totalPending' => $totalPending,
        'rentalCount' => $rentalCount,
        'gasCount' => $gasCount,
        'mobilCount' => $mobilCount,
        'selectedYear' => $selectedYear,
        'availableYears' => $availableYears,
        // Data nyata untuk grafik
        'monthlyPerformance' => $monthlyPerformance,
        'monthlyIncome' => $monthlyIncome,
        'monthlyExpenses' => $monthlyExpenses,
        'popularItems' => $popularItems,
    ];

    // Ambil jumlah item untuk setiap unit layanan
    $data['unitPenyewaan'] = Barang::count(); 
    $data['unitGas'] = Gas::count();

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
            $activeServices = $userRegion->services->pluck('name')->toArray();
        }
    }
    $data['activeServices'] = $activeServices;

    Log::info('DashboardController: All data prepared. Rendering view.');
    return view('admin.dashboard.index', $data);
}

/**
 * Ambil data Total Pendapatan - Rincian pendapatan berdasarkan unit
 */
    private function getTotalPendapatanData($selectedYear = null)
    {
        // Ambil bulan/tahun saat ini atau dari permintaan
        $month = request('month', date('m'));
        
        // Gunakan tahun yang dipilih jika ada, jika tidak ambil dari request, jika tidak tahun ini
        $year = $selectedYear ?? request('year', date('Y'));
        
        // Pendapatan Penyewaan Alat
        $rentalRevenue = RentalBooking::withTrashed()->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->sum('total_amount');
        
        $rentalTransactions = RentalBooking::withTrashed()->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->count();
        
        // Pendapatan Penjualan Gas
        $gasRevenue = GasOrder::withTrashed()->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->sum(\DB::raw('price * quantity'));
        
        $gasTransactions = GasOrder::withTrashed()->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->count();
        // Pendapatan Penyewaan Mobil
        $mobilRevenue = \App\Models\MobilBooking::withTrashed()->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->sum('total_amount');
        
        $mobilTransactions = \App\Models\MobilBooking::withTrashed()->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->count();
    
        // Pendapatan Laporan Manual
        $manualRevenue = ManualReport::whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->sum(\DB::raw('amount * quantity'));
        
        $manualTransactions = ManualReport::whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->count();
        
        $totalRevenue = $rentalRevenue + $gasRevenue + $mobilRevenue + $manualRevenue;
        $totalTransactions = $rentalTransactions + $gasTransactions + $mobilTransactions + $manualTransactions;
        
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
                'color' => 'primary'
            ],
            'Penyewaan Mobil' => [
                'revenue' => $mobilRevenue,
                'transactions' => $mobilTransactions,
                'percentage' => $totalRevenue > 0 ? round(($mobilRevenue / $totalRevenue) * 100, 1) : 0,
                'color' => 'info'
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
        $rentalProducts = Barang::where('nama_barang', 'LIKE', "%{$search}%")
            ->orWhere('kategori', 'LIKE', "%{$search}%")
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
        $gasProducts = Gas::where('jenis_gas', 'LIKE', "%{$search}%")
            ->orWhere('kategori', 'LIKE', "%{$search}%")
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
        $users = User::where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'user',
                    'title' => $item->name,
                    'subtitle' => $item->email,
                    'description' => 'Bergabung: ' . $item->created_at->format('d M Y'),
                    'image' => $item->avatar,
                    'link' => route('admin.manajemen-pengguna.index'),
                    'badge' => 'Pengguna',
                    'badge_color' => 'success'
                ];
            });
        
        // Cari di Anggota BUMDes
        $bumdesMembers = \App\Models\BumdesMember::where('name', 'LIKE', "%{$search}%")
            ->orWhere('position', 'LIKE', "%{$search}%")
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'bumdes_member',
                    'title' => $item->name,
                    'subtitle' => $item->position,
                    'description' => 'Anggota Struktur BUMDes',
                    'image' => $item->photo,
                    'link' => route('admin.isewa.bumdes.index'),
                    'badge' => 'Profil BUMDes',
                    'badge_color' => 'info'
                ];
            });
        
        // Cari di Transaksi (Penyewaan)
        $rentalTransactions = RentalBooking::with('user')
            ->where('status', 'LIKE', "%{$search}%")
            ->orWhereHas('user', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            })
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'rental_transaction',
                    'title' => 'Transaksi Penyewaan #' . $item->id,
                    'subtitle' => 'User: ' . ($item->user->name ?? 'N/A'),
                    'description' => 'Status: ' . ucfirst($item->status) . ' | Tanggal: ' . $item->created_at->format('d M Y'),
                    'image' => null,
                    'link' => route('admin.aktivitas.permintaan-pengajuan.index'),
                    'badge' => 'Transaksi',
                    'badge_color' => 'secondary'
                ];
            });
        
        
        // Cari di Transaksi (Gas)
        $gasTransactions = GasOrder::with('user')
            ->where('status', 'LIKE', "%{$search}%")
            ->orWhereHas('user', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
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
        // Tangani pembaruan preferensi notifikasi
        // Anda dapat menyimpan preferensi ini ke database jika diperlukan

        return back()->with('success', 'Notification preferences updated successfully!');
    }
    /**
     * Tampilkan Halaman Pemeliharaan
     */
    public function maintenance()
    {
        return view('maintenance');
    }

    /**
     * Ambil Produk Populer (4 item paling banyak disewa/dijual)
     */
    private function getPopularProducts($year = null)
    {
        $year = $year ?? date('Y');

        // 1. Ambil Skor Penyewaan
        $rentalPopularity = RentalBooking::withTrashed()->select('barang_id', DB::raw('SUM(quantity) as total_sold'))
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
        $gasPopularity = GasOrder::withTrashed()->select('gas_id', DB::raw('SUM(quantity) as total_sold'))
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
        $mobilPopularity = \App\Models\MobilBooking::withTrashed()->select('mobil_id', DB::raw('SUM(quantity) as total_sold'))
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
                'category' => 'Unit Penyewaan Mobil',
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