<?php

namespace App\Http\Controllers\Admin;

use App\Models\RentalBooking;
use App\Models\GasOrder;
use App\Models\ManualReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\ActivityLog;

class ReportController extends Controller
{
    public function transactions(Request $request)
    {
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Query Penyewaan
        $rentalQuery = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)->with('user')->orderByDesc('created_at');
        
        // Query Gas
        $gasQuery = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)->with('user')->orderByDesc('created_at');

        // Query Mobil
        $mobilQuery = $this->applyRegionFilter(\App\Models\MobilBooking::withTrashed(), 'mobil', true)->with(['user', 'mobil'])->orderByDesc('created_at');

        // Query Fasilitas Umum
        $fasilitasQuery = $this->applyRegionFilter(\App\Models\FasilitasUmumBooking::withTrashed(), 'fasilitas', true)->with(['user', 'fasilitas'])->orderByDesc('created_at');

        // Terapkan Filter
        if ($status && $status !== 'all') {
            $rentalQuery->where('status', $status);
            $gasQuery->where('status', $status);
            $mobilQuery->where('status', $status);
            $fasilitasQuery->where('status', $status);
        }

        if ($startDate) {
            $rentalQuery->whereDate('created_at', '>=', $startDate);
            $gasQuery->whereDate('created_at', '>=', $startDate);
            $mobilQuery->whereDate('created_at', '>=', $startDate);
            $fasilitasQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $rentalQuery->whereDate('created_at', '<=', $endDate);
            $gasQuery->whereDate('created_at', '<=', $endDate);
            $mobilQuery->whereDate('created_at', '<=', $endDate);
            $fasilitasQuery->whereDate('created_at', '<=', $endDate);
        }

        $rentalRequests = $rentalQuery->get();
        $gasOrders = $gasQuery->get();
        $mobilBookings = $mobilQuery->get();
        $fasilitasBookings = $fasilitasQuery->get();

        $activeServices = $this->getActivatedServices();
        if ($request->ajax()) {
            return view('admin.laporan.partials.transactions_content', compact('rentalRequests', 'gasOrders', 'mobilBookings', 'fasilitasBookings', 'activeServices'))->render();
        }

        return view('admin.laporan.transactions', compact('rentalRequests', 'gasOrders', 'mobilBookings', 'fasilitasBookings', 'status', 'startDate', 'endDate', 'activeServices'));
    }

    public function income(Request $request)
    {
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
            
        $mobilYears = \App\Models\MobilBooking::withTrashed()
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->pluck('year')
            ->map(fn($y) => (int)$y)
            ->toArray();
        
        // Gabungkan dengan tahun sekarang secara eksplisit (Hard Merge)
        $allYears = array_unique(array_merge($rentalYears, $gasYears, $mobilYears, [(int)now()->year]));
        $availableYears = array_values($allYears);
        rsort($availableYears);

        // Hitung total pendapatan per unit dari sistem (Filter Tahunan)
        $totalPenyewaan = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->sum('total_amount');
            
        $totalGas = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->selectRaw('SUM(price * quantity) as total')
            ->value('total') ?? 0;
            
        $totalMobil = $this->applyRegionFilter(\App\Models\MobilBooking::withTrashed(), 'mobil', true)->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->sum('total_amount');
        
        // Hitung total dari laporan manual (Filter Tahunan)
        $manualPenyewaan = $this->applyRegionFilter(ManualReport::query(), 'creator', true)->whereYear('transaction_date', $year)
            ->where('category', 'penyewaan')
            ->sum(\DB::raw('amount * quantity'));
            
        $manualGas = $this->applyRegionFilter(ManualReport::query(), 'creator', true)->whereYear('transaction_date', $year)
            ->where('category', 'gas')
            ->sum(\DB::raw('amount * quantity'));
            
        $manualLainnya = $this->applyRegionFilter(ManualReport::query(), 'creator', true)->whereYear('transaction_date', $year)
            ->where('category', 'lainnya')
            ->sum(\DB::raw('amount * quantity'));
        
        // Total keseluruhan
        $totalPenyewaan += $manualPenyewaan;
        $totalGas += $manualGas;
        $totalPendapatan = $totalPenyewaan + $totalGas + $totalMobil + $manualLainnya;

        // Hitung pendapatan per bulan
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $monthlyIncome = array_fill_keys($months, 0);

        // Pendapatan dari sistem (RentalBooking)
        $rentalMonthly = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)->selectRaw('SUM(total_amount) as total, MONTH(created_at) as month')
            ->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->groupBy('month')
            ->pluck('total', 'month');

        foreach ($rentalMonthly as $month => $amount) {
            $monthlyIncome[self::getMonthName($month)] += $amount;
        }

        // Pendapatan dari sistem (GasOrder)
        $gasMonthly = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)->selectRaw('SUM(price * quantity) as total, MONTH(created_at) as month')
            ->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->groupBy('month')
            ->pluck('total', 'month');

        foreach ($gasMonthly as $month => $amount) {
            $monthlyIncome[self::getMonthName($month)] += $amount;
        }

        // Pendapatan dari sistem (MobilBooking)
        $mobilMonthly = $this->applyRegionFilter(\App\Models\MobilBooking::withTrashed(), 'mobil', true)->selectRaw('SUM(total_amount) as total, MONTH(created_at) as month')
            ->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->groupBy('month')
            ->pluck('total', 'month');

        foreach ($mobilMonthly as $month => $amount) {
            $monthlyIncome[self::getMonthName($month)] += $amount;
        }
        
        // Pendapatan dari laporan manual
        $manualMonthly = $this->applyRegionFilter(ManualReport::query(), 'creator', true)->selectRaw('SUM(amount * quantity) as total, MONTH(transaction_date) as month')
            ->whereYear('transaction_date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        foreach ($manualMonthly as $month => $amount) {
            $monthlyIncome[self::getMonthName($month)] += $amount;
        }

        // Data untuk chart
        $dataPoints = [];
        foreach ($monthlyIncome as $month => $income) {
            $dataPoints[] = ['label' => $month, 'y' => $income];
        }

        // Ambil data untuk detail per unit (Difilter Berdasarkan Tahun)
        $rentalRequests = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)->whereYear('created_at', $year)->get(); // For count & stats
        $gasOrders = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)->whereYear('created_at', $year)->get();
        
        // Ambil laporan manual (Difilter Berdasarkan Tahun)
        $manualReports = $this->applyRegionFilter(ManualReport::with('creator'), 'creator', true)
            ->whereYear('transaction_date', $year)
            ->orderByDesc('transaction_date')
            ->get();

        // Hitung total transaksi untuk Donut Chart (Filter Tahunan)
        $rentalCount = RentalBooking::withTrashed()->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->count();
            
        $gasCount = GasOrder::withTrashed()->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->count();
            
        $mobilCount = \App\Models\MobilBooking::withTrashed()->whereYear('created_at', $year)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->count();
            
        // Ambil Data Tahun Saat Ini (Yearly Total)
        $currentYearData = [
            'rental' => $totalPenyewaan,
            'gas' => $totalGas,
            'mobil' => $totalMobil,
            'total' => $totalPendapatan
        ];

        // Ambil Data Tahun Sebelumnya
        $prevYear = $year - 1;
        
        $prevTotalPenyewaan = RentalBooking::withTrashed()->whereYear('created_at', $prevYear)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->sum('total_amount');
            
        $prevTotalGas = GasOrder::withTrashed()->whereYear('created_at', $prevYear)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->sum(\DB::raw('price * quantity'));
            
        $prevTotalMobil = \App\Models\MobilBooking::withTrashed()->whereYear('created_at', $prevYear)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->sum('total_amount');
            
        $prevManualRevenue = ManualReport::whereYear('transaction_date', $prevYear)
            ->sum(\DB::raw('amount * quantity'));
            
        $prevTotalPendapatan = $prevTotalPenyewaan + $prevTotalGas + $prevTotalMobil + $prevManualRevenue;
        
        $prevYearData = [
            'rental' => $prevTotalPenyewaan,
            'gas' => $prevTotalGas,
            'mobil' => $prevTotalMobil,
            'total' => $prevTotalPendapatan
        ];

        // Hitung Fungsi Pertumbuhan
        $calculateGrowth = function($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }
            return round((($current - $previous) / $previous) * 100, 1);
        };

        // Ambil Bulan Terpilih (untuk Tampilan Detail Chart)
        $selectedMonth = $request->input('month', date('m'));
        $currentMonthData = $this->getTotalPendapatanData($selectedMonth, $year);

        $growth = [
            'total' => $calculateGrowth($currentYearData['total'], $prevYearData['total']),
            'rental' => $calculateGrowth($currentYearData['rental'], $prevYearData['rental']),
            'gas' => $calculateGrowth($currentYearData['gas'], $prevYearData['gas']),
            'mobil' => $calculateGrowth($currentYearData['mobil'], $prevYearData['mobil']),
        ];

        // Teruskan Data Total Pendapatan (untuk Tampilan Detail) - sama seperti currentMonthData
        $totalPendapatanData = $currentMonthData;
        
        // Ambil data Unit Populer (perbandingan penyewaan vs gas)
        $unitPopulerData = $this->getUnitPopulerData($year);

        $activeServices = $this->getActivatedServices();

        return view('admin.laporan.income', compact(
            'totalPenyewaan',
            'totalGas',
            'totalMobil',
            'totalPendapatan',
            'monthlyIncome',
            'dataPoints',
            'rentalRequests',
            'gasOrders',
            'manualReports',
            'manualLainnya',
            'rentalCount',
            'gasCount',
            'mobilCount',
            'year',
            'totalPendapatanData',
            'unitPopulerData',
            'growth',
            'availableYears'
        ));
    }

    /**
     * Laporan Wilayah - Grafik tren kinerja layanan
     */
    public function wilayah(Request $request)
    {
        // Parameter filter
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        
        $selectedKecamatanId = $request->input('kecamatan_id');
        $selectedDesaId = $request->input('desa_id');
        
        // Base query for transactions (NOT strict, to see descendant activity)
        $baseRental = $this->applyRegionFilter(\App\Models\RentalBooking::withTrashed(), 'barang', false);
        $baseGas = $this->applyRegionFilter(\App\Models\GasOrder::withTrashed(), 'gas', false);
        $baseMobil = $this->applyRegionFilter(\App\Models\MobilBooking::withTrashed(), 'mobil', false);
        $baseFasilitas = $this->applyRegionFilter(\App\Models\FasilitasUmumBooking::withTrashed(), 'fasilitas', false);
        $baseLaporan = $this->applyRegionFilter(\App\Models\Laporan::query(), 'user', false);

        // Terapkan filter dropdown Kecamatan/Desa jika ada
        if ($selectedDesaId && $selectedDesaId !== 'all') {
            $baseRental->whereHas('barang', function($q) use ($selectedDesaId) { $q->where('region_id', $selectedDesaId); });
            $baseGas->whereHas('gas', function($q) use ($selectedDesaId) { $q->where('region_id', $selectedDesaId); });
            $baseMobil->whereHas('mobil', function($q) use ($selectedDesaId) { $q->where('region_id', $selectedDesaId); });
            $baseFasilitas->whereHas('fasilitas', function($q) use ($selectedDesaId) { $q->where('region_id', $selectedDesaId); });
            $baseLaporan->whereHas('user', function($q) use ($selectedDesaId) { $q->where('region_id', $selectedDesaId); });
        } elseif ($selectedKecamatanId && $selectedKecamatanId !== 'all') {
            $desaIds = \App\Models\Region::where('parent_id', $selectedKecamatanId)->pluck('id')->toArray();
            $desaIds[] = $selectedKecamatanId;
            $baseRental->whereHas('barang', function($q) use ($desaIds) { $q->whereIn('region_id', $desaIds); });
            $baseGas->whereHas('gas', function($q) use ($desaIds) { $q->whereIn('region_id', $desaIds); });
            $baseMobil->whereHas('mobil', function($q) use ($desaIds) { $q->whereIn('region_id', $desaIds); });
            $baseFasilitas->whereHas('fasilitas', function($q) use ($desaIds) { $q->whereIn('region_id', $desaIds); });
            $baseLaporan->whereHas('user', function($q) use ($desaIds) { $q->whereIn('region_id', $desaIds); });
        }

        // Kinerja Per Bulan (Januari - Desember)
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlyPerformance = array_fill_keys($months, 0);
        
        $rentalMonthly = $baseRental->clone()->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', $year)->whereNotIn('status', ['pending', 'cancelled', 'rejected'])->groupBy('month')->pluck('total', 'month');
        $gasMonthly = $baseGas->clone()->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', $year)->whereNotIn('status', ['pending', 'cancelled', 'rejected'])->groupBy('month')->pluck('total', 'month');
        $mobilMonthly = $baseMobil->clone()->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', $year)->whereNotIn('status', ['pending', 'cancelled', 'rejected'])->groupBy('month')->pluck('total', 'month');
        $fasilitasMonthly = $baseFasilitas->clone()->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', $year)->whereNotIn('status', ['pending', 'cancelled', 'rejected'])->groupBy('month')->pluck('total', 'month');
        $laporanMonthly = $baseLaporan->clone()->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', $year)->groupBy('month')->pluck('total', 'month');

        for ($i = 1; $i <= 12; $i++) {
            $monthName = $months[$i - 1];
            $monthlyPerformance[$monthName] = ($rentalMonthly[$i] ?? 0) + ($gasMonthly[$i] ?? 0) + ($mobilMonthly[$i] ?? 0) + ($fasilitasMonthly[$i] ?? 0) + ($laporanMonthly[$i] ?? 0);
        }

        // Kalkulasi Persentase Kinerja (Bulan ini vs Bulan lalu)
        $currentMonthTotal = $monthlyPerformance[$months[$month - 1]] ?? 0;
        $lastMonthTotal = $month > 1 ? ($monthlyPerformance[$months[$month - 2]] ?? 0) : 0;
        
        $growth = 0;
        if ($lastMonthTotal > 0) {
            $growth = round((($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1);
        } elseif ($currentMonthTotal > 0) {
            $growth = 100;
        }

        // Daftar Kecamatan & Desa untuk filter
        $kecamatanList = collect();
        $desaList = collect();
        if (in_array(auth()->user()->role, ['super_admin', 'admin'])) {
            $kecamatanList = \App\Models\Region::where('type', 'kecamatan')->get();
            if ($selectedKecamatanId && $selectedKecamatanId !== 'all') {
                $desaList = \App\Models\Region::where('type', 'desa')->where('parent_id', $selectedKecamatanId)->get();
            } else {
                $desaList = \App\Models\Region::where('type', 'desa')->get();
            }
        } elseif (auth()->user()->role === 'admin_kecamatan') {
            $desaList = \App\Models\Region::where('type', 'desa')->where('parent_id', auth()->user()->region_id)->get();
        }

        // Dynamic Year Generation
        $rentalYears = \App\Models\RentalBooking::withTrashed()->selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
        $gasYears = \App\Models\GasOrder::withTrashed()->selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
        $mobilYears = \App\Models\MobilBooking::withTrashed()->selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
        $fasilitasYears = \App\Models\FasilitasUmumBooking::withTrashed()->selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
        $laporanYears = \App\Models\Laporan::selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
        
        $availableYears = array_unique(array_merge($rentalYears, $gasYears, $mobilYears, $fasilitasYears, $laporanYears, [now()->year]));
        rsort($availableYears);
        $years = collect($availableYears);
        
        if ($request->ajax()) {
            // Render HTML options for desa dropdown
            $desaOptionsHtml = '<option value="all">-- Semua Desa --</option>';
            foreach($desaList as $desa) {
                $selected = ($selectedDesaId == $desa->id) ? 'selected' : '';
                $desaOptionsHtml .= '<option value="'.$desa->id.'" '.$selected.'>'.$desa->name.'</option>';
            }

            return response()->json([
                'performanceData' => array_values($monthlyPerformance),
                'months' => array_keys($monthlyPerformance),
                'growth' => $growth,
                'desaOptionsHtml' => $desaOptionsHtml
            ]);
        }

        return view('admin.laporan.wilayah', compact(
            'monthlyPerformance', 'months', 'growth', 'currentMonthTotal',
            'year', 'month', 'years', 'kecamatanList', 'desaList',
            'selectedKecamatanId', 'selectedDesaId'
        ));
    }
    
    /**
     * Ambil data Unit Populer - Perbandingan antara penyewaan dan penjualan gas
     */
    private function getUnitPopulerData($year)
    {
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $rentalData = [];
        $gasData = [];
        $mobilData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            // Hitung pesanan penyewaan
            $rentalCount = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count();
            
            // Hitung pesanan gas
            $gasCount = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count();
                
            // Hitung pesanan mobil
            $mobilCount = $this->applyRegionFilter(\App\Models\MobilBooking::withTrashed(), 'mobil', true)->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count();
            
            $rentalData[] = $rentalCount;
            $gasData[] = $gasCount;
            $mobilData[] = $mobilCount;
        }
        
        return [
            'categories' => $months,
            'rental' => $rentalData,
            'gas' => $gasData,
            'mobil' => $mobilData
        ];
    }

    /**
     * Ambil data Total Pendapatan - Rincian pendapatan berdasarkan unit
     */
    private function getTotalPendapatanData($month, $year)
    {   
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
            
        // Pendapatan Sewa Mobil
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
            'rental' => [
                'revenue' => $rentalRevenue,
                'transactions' => $rentalTransactions,
                'percentage' => $totalRevenue > 0 ? round(($rentalRevenue / $totalRevenue) * 100, 1) : 0
            ],
            'gas' => [
                'revenue' => $gasRevenue,
                'transactions' => $gasTransactions,
                'percentage' => $totalRevenue > 0 ? round(($gasRevenue / $totalRevenue) * 100, 1) : 0
            ],
            'mobil' => [
                'revenue' => $mobilRevenue,
                'transactions' => $mobilTransactions,
                'percentage' => $totalRevenue > 0 ? round(($mobilRevenue / $totalRevenue) * 100, 1) : 0
            ],
            'total' => [
                'revenue' => $totalRevenue,
                'transactions' => $totalTransactions
            ],
            'month' => $month,
            'year' => $year
        ];
    }



    public function logs(Request $request)
    {
        $query = $this->applyRegionFilter(ActivityLog::query(), 'user', false)->with('user')->orderByDesc('created_at');

        // Cari (Deskripsi atau Nama Pengguna)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->searchWhereLike(['description', 'action'], $search)
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->searchWhereLike('name', $search);
                  });
            });
        }

        // Filter berdasarkan Aksi
        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        // Filter berdasarkan Tanggal
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(10)->withQueryString();

        return view('admin.laporan.logs', compact('logs'));
    }

    /**
     * Bersihkan semua log aktivitas (Soft Delete)
     * Requires password confirmation (Sudo Mode) for security.
     */
    public function clearLogs(Request $request)
    {
        // Hanya super_admin atau admin kabupaten yang boleh membersihkan log
        if (!in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk membersihkan log.');
        }

        // Sudo Mode: require password confirmation
        $request->validate([
            'password' => 'required',
        ]);

        if (!Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', 'Password tidak valid. Pembersihan log dibatalkan.');
        }

        // Log the clear action BEFORE clearing (to a separate, undeletable record)
        \Log::channel('single')->critical('SECURITY AUDIT: Activity logs cleared by admin', [
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()->email,
            'ip_address' => $request->ip(),
            'timestamp' => now()->toISOString(),
            'logs_count' => ActivityLog::count(),
        ]);

        // Soft delete instead of hard delete
        ActivityLog::query()->delete(); // This triggers soft delete since model uses SoftDeletes

        return back()->with('success', 'Log aktivitas berhasil dibersihkan.');
    }
    
    /**
     * Simpan transaksi manual baru
     */
    public function storeManualTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|in:penyewaan,gas,lainnya',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:tunai',
            'transaction_date' => 'required|date',
            'proof_image' => 'nullable|image|max:2048', // Maks 2MB
        ], [
            'category.required' => 'Kategori harus dipilih',
            'category.in' => 'Kategori tidak valid',
            'name.required' => 'Nama item harus diisi',
            'amount.required' => 'Harga harus diisi',
            'amount.numeric' => 'Harga harus berupa angka',
            'amount.min' => 'Harga tidak boleh negatif',
            'quantity.required' => 'Jumlah harus diisi',
            'quantity.integer' => 'Jumlah harus berupa angka bulat',
            'quantity.min' => 'Jumlah minimal 1',
            'payment_method.required' => 'Metode pembayaran harus dipilih',
            'payment_method.in' => 'Metode pembayaran tidak valid',
            'transaction_date.required' => 'Tanggal transaksi harus diisi',
            'transaction_date.date' => 'Format tanggal tidak valid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $proofImagePath = null;
            if ($request->hasFile('proof_image')) {
                $path = $request->file('proof_image')->store('manual-reports', 'public');
                $proofImagePath = $path;
            }

            $manualReport = ManualReport::create([
                'category' => $request->category,
                'name' => $request->name,
                'description' => $request->description,
                'amount' => $request->amount,
                'quantity' => $request->quantity,
                'payment_method' => $request->payment_method,
                'transaction_date' => $request->transaction_date,
                'created_by' => Auth::id(),
                'proof_image' => $proofImagePath,
            ]);

            // Log Activity
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Create Manual Report',
                'description' => "Membuat laporan manual: {$request->name} ({$request->category})",
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan transaksi berhasil ditambahkan',
                'data' => $manualReport
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Perbarui transaksi manual yang ada
     */
    public function updateManualTransaction(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|in:penyewaan,gas,lainnya',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:tunai',
            'transaction_date' => 'required|date',
            'proof_image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $manualReport = ManualReport::findOrFail($id);
            
            $data = [
                'category' => $request->category,
                'name' => $request->name,
                'description' => $request->description,
                'amount' => $request->amount,
                'quantity' => $request->quantity,
                'payment_method' => $request->payment_method,
                'transaction_date' => $request->transaction_date,
            ];

            if ($request->hasFile('proof_image')) {
                // Hapus gambar lama jika ada (from storage disk)
                if ($manualReport->proof_image && \Storage::disk('public')->exists($manualReport->proof_image)) {
                    \Storage::disk('public')->delete($manualReport->proof_image);
                }

                $path = $request->file('proof_image')->store('manual-reports', 'public');
                $data['proof_image'] = $path;
            }

            $manualReport->update($data);

            // Log Activity
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Update Manual Report',
                'description' => "Memperbarui laporan manual: {$request->name}",
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan transaksi berhasil diperbarui',
                'data' => $manualReport
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Hapus transaksi manual
     */
    public function destroyManualTransaction($id)
    {
        try {
            $manualReport = ManualReport::findOrFail($id);
            
            if ($manualReport->proof_image && \Illuminate\Support\Facades\Storage::disk('public')->exists($manualReport->proof_image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($manualReport->proof_image);
            }
            
            $manualReport->delete();

            return response()->json([
                'success' => true,
                'message' => 'Laporan transaksi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
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

    /**
     * Riwayat Pendapatan - Menampilkan daftar semua transaksi pendapatan
     */
    public function incomeHistory(Request $request)
    {
        $filter = $request->input('filter', 'bulan_ini');
        $activeServices = $this->getActivatedServices();
        
        $isRentalActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'alat'));
        $isGasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'gas'));
        $isMobilActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'mobil'));
        $isFasilitasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'fasilitas'));

        $queryStartDate = null;
        $queryEndDate = now();

        if ($filter == 'minggu_ini') {
            $queryStartDate = now()->startOfWeek();
        } elseif ($filter == 'bulan_ini') {
            $queryStartDate = now()->startOfMonth();
        } elseif ($filter == 'tahun_ini') {
            $queryStartDate = now()->startOfYear();
        }

        $history = collect();

        if ($isRentalActive) {
            $rentalQuery = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->with(['user', 'barang']);
            
            if ($queryStartDate) {
                $rentalQuery->whereBetween('created_at', [$queryStartDate, $queryEndDate]);
            }
            
            $rentals = $rentalQuery->get()->map(function($item) {
                return (object)[
                    'id' => $item->id,
                    'type' => 'Penyewaan Alat',
                    'item_name' => $item->barang->nama_barang ?? 'Barang',
                    'date' => $item->created_at,
                    'amount' => $item->total_amount,
                    'status' => $item->status,
                    'user_name' => $item->user->name ?? 'User',
                    'user_photo' => $item->user->profile_photo_url ?? null,
                    'location' => $item->user->address ?? '-',
                    'proof' => $item->payment_proof,
                    'proof_route' => route('receipt.rental.view', $item->id),
                    'proof_download' => route('receipt.rental.download', $item->id),
                ];
            });
            $history = $history->merge($rentals);
        }

        if ($isGasActive) {
            $gasQuery = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->with(['user', 'gas']);
            
            if ($queryStartDate) {
                $gasQuery->whereBetween('created_at', [$queryStartDate, $queryEndDate]);
            }
            
            $gas = $gasQuery->get()->map(function($item) {
                return (object)[
                    'id' => $item->id,
                    'type' => 'Pembelian Gas',
                    'item_name' => $item->gas->type ?? 'Gas',
                    'date' => $item->created_at,
                    'amount' => $item->price * $item->quantity,
                    'status' => $item->status,
                    'user_name' => $item->user->name ?? 'User',
                    'user_photo' => $item->user->profile_photo_url ?? null,
                    'location' => $item->user->address ?? '-',
                    'proof' => $item->payment_proof,
                    'proof_route' => route('receipt.gas.view', $item->id),
                    'proof_download' => route('receipt.gas.download', $item->id),
                ];
            });
            $history = $history->merge($gas);
        }

        if ($isMobilActive) {
            $mobilQuery = $this->applyRegionFilter(\App\Models\MobilBooking::withTrashed(), 'mobil', true)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->with(['user', 'mobil']);
            
            if ($queryStartDate) {
                $mobilQuery->whereBetween('created_at', [$queryStartDate, $queryEndDate]);
            }
            
            $mobil = $mobilQuery->get()->map(function($item) {
                return (object)[
                    'id' => $item->id,
                    'type' => 'Penyewaan Mobil',
                    'item_name' => $item->mobil->nama_mobil ?? 'Mobil',
                    'date' => $item->created_at,
                    'amount' => $item->total_amount,
                    'status' => $item->status,
                    'user_name' => $item->user->name ?? 'User',
                    'user_photo' => $item->user->profile_photo_url ?? null,
                    'location' => $item->user->address ?? '-',
                    'proof' => $item->payment_proof,
                    'proof_route' => route('receipt.mobil.view', $item->id),
                    'proof_download' => route('receipt.mobil.download', $item->id),
                ];
            });
            $history = $history->merge($mobil);
        }

        if ($isFasilitasActive) {
            $fasilitasQuery = $this->applyRegionFilter(\App\Models\FasilitasUmumBooking::withTrashed(), 'fasilitas', true)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->with(['user', 'fasilitas']);
            
            if ($queryStartDate) {
                $fasilitasQuery->whereBetween('created_at', [$queryStartDate, $queryEndDate]);
            }
            
            $fasilitas = $fasilitasQuery->get()->map(function($item) {
                return (object)[
                    'id' => $item->id,
                    'type' => 'Fasilitas Umum',
                    'item_name' => $item->fasilitas->nama_fasilitas ?? 'Fasilitas',
                    'date' => $item->created_at,
                    'amount' => $item->total_amount,
                    'status' => $item->status,
                    'user_name' => $item->user->name ?? 'User',
                    'user_photo' => $item->user->profile_photo_url ?? null,
                    'location' => $item->user->address ?? '-',
                    'proof' => $item->payment_proof,
                    'proof_route' => route('receipt.fasilitas.view', $item->id),
                    'proof_download' => route('receipt.fasilitas.download', $item->id),
                ];
            });
            $history = $history->merge($fasilitas);
        }

        // Laporan Manual
        $manualQuery = $this->applyRegionFilter(ManualReport::query(), 'creator', true)
            ->with('creator');
        
        if ($queryStartDate) {
            $manualQuery->whereBetween('transaction_date', [$queryStartDate->format('Y-m-d'), $queryEndDate->format('Y-m-d')]);
        }
        
        $manual = $manualQuery->get()->map(function($item) {
            return (object)[
                'id' => $item->id,
                'type' => 'Laporan Manual',
                'item_name' => ucfirst($item->category) . ($item->description ? ' - '.$item->description : ''),
                'date' => \Carbon\Carbon::parse($item->transaction_date),
                'amount' => $item->amount * $item->quantity,
                'status' => 'completed',
                'user_name' => $item->creator->name ?? 'Admin',
                'user_photo' => $item->creator->profile_photo_url ?? null,
                'location' => '-',
                'proof' => $item->proof_path,
                'proof_route' => $item->proof_path ? asset('storage/' . $item->proof_path) : null,
                'proof_download' => null,
            ];
        });
        
        $history = $history->merge($manual);

        // Sort by date descending
        $history = $history->sortByDesc('date')->values();

        return view('admin.laporan.income_history', compact('history', 'filter', 'activeServices'));
    }
}