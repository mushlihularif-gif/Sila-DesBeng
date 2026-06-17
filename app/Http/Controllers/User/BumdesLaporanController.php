<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RentalBooking;
use App\Models\GasOrder;
use App\Models\ManualReport;
use App\Models\MobilBooking;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BumdesLaporanController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
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
        
        // Get Total Pendapatan data
        $totalPendapatanData = $this->getTotalPendapatanData($year, $regionId);
        
        return view('users.bumdes-laporan', compact(
            'kinerjaData',
            'unitPopulerData',
            'totalPendapatanData',
            'year',
            'availableYears', // Pass available years
            'kecamatans',
            'desas',
            'kecamatanId',
            'desaId'
        ));
    }
    
    /**
     * Get Kinerja BUMDes data - Monthly revenue from both rental and gas
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

            // Get Mobil revenue for this month
            $mobilRevenue = MobilBooking::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('region_id', $regionIds)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->sum('total_amount');
            
            // Total revenue in millions
            $totalRevenue = ($rentalRevenue + $gasRevenue + $manualRevenue + $mobilRevenue) / 1000000;
            
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
            $gasCount = GasOrder::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('region_id', $regionIds)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count();

            // Count mobil orders
            $mobilCount = MobilBooking::withTrashed()
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
     * Get Total Pendapatan data - Revenue breakdown by unit
     */
    private function getTotalPendapatanData($selectedYear = null, $regionId = 1)
    {
        // Get current month/year or from request
        $month = request('month', date('m'));
        $year = $selectedYear ?? (int)request('year', date('Y'));
        $regionIds = array_merge([$regionId], \App\Models\Region::getDescendantIds($regionId));
        
        $applyMonthFilter = function($query, $column = 'created_at') use ($month) {
            if ($month !== 'all') {
                return $query->whereMonth($column, $month);
            }
            return $query;
        };
        
        // Rental Equipment Revenue
        $rentalQuery = RentalBooking::withTrashed()
            ->whereYear('created_at', $year)
            ->whereIn('region_id', $regionIds)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected']);
        $rentalQuery = $applyMonthFilter($rentalQuery);
            
        $rentalRevenue = (clone $rentalQuery)->sum('total_amount');
        $rentalTransactions = (clone $rentalQuery)->count();
        
        // Gas Sales Revenue
        $gasQuery = GasOrder::withTrashed()
            ->whereYear('created_at', $year)
            ->whereIn('region_id', $regionIds)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected']);
        $gasQuery = $applyMonthFilter($gasQuery);
            
        $gasRevenue = (clone $gasQuery)->sum(DB::raw('price * quantity'));
        $gasTransactions = (clone $gasQuery)->count();

        // Manual Reports Revenue
        $manualQuery = ManualReport::whereYear('transaction_date', $year)
            ->whereIn('region_id', $regionIds);
        $manualQuery = $applyMonthFilter($manualQuery, 'transaction_date');
            
        $manualRevenue = (clone $manualQuery)->sum(DB::raw('amount * quantity'));
        $manualTransactions = (clone $manualQuery)->count();

        // Mobil Rental Revenue
        $mobilQuery = MobilBooking::withTrashed()
            ->whereYear('created_at', $year)
            ->whereIn('region_id', $regionIds)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected']);
        $mobilQuery = $applyMonthFilter($mobilQuery);
            
        $mobilRevenue = (clone $mobilQuery)->sum('total_amount');
        $mobilTransactions = (clone $mobilQuery)->count();
        
        $totalRevenue = $rentalRevenue + $gasRevenue + $manualRevenue + $mobilRevenue;
        $totalTransactions = $rentalTransactions + $gasTransactions + $manualTransactions + $mobilTransactions;
        
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
}
