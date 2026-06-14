<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RentalBooking;
use App\Models\GasOrder;
use App\Models\ManualReport;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BumdesLaporanController extends Controller
{
    public function index()
    {
        // Dapatkan tahun yang dipilih (default ke tahun sekarang)
        $yearRequest = request('year', now()->year);
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

        // Get Kinerja BUMDes data (monthly revenue)
        $kinerjaData = $this->getKinerjaData($year);
        
        // Get Unit Populer data (rental vs gas comparison)
        $unitPopulerData = $this->getUnitPopulerData($year);
        
        // Get Total Pendapatan data
        $totalPendapatanData = $this->getTotalPendapatanData($year);
        
        return view('users.bumdes-laporan', compact(
            'kinerjaData',
            'unitPopulerData',
            'totalPendapatanData',
            'year',
            'availableYears' // Pass available years
        ));
    }
    
    /**
     * Get Kinerja BUMDes data - Monthly revenue from both rental and gas
     */
    private function getKinerjaData($year)
    {
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $monthlyData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            // Get rental revenue for this month (excluding cancelled)
            $rentalRevenue = RentalBooking::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->sum('total_amount');
            
            // Get gas revenue for this month (excluding cancelled)
            $gasRevenue = GasOrder::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->sum(DB::raw('price * quantity'));

            // Get Manual Report revenue for this month
            $manualRevenue = ManualReport::whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
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
    private function getUnitPopulerData($year)
    {
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $rentalData = [];
        $gasData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            // Count rental orders
            $rentalCount = RentalBooking::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count();
            
            // Count gas orders
            $gasCount = GasOrder::withTrashed()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count();
            
            $rentalData[] = $rentalCount;
            $gasData[] = $gasCount;
        }
        
        return [
            'categories' => $months,
            'rental' => $rentalData,
            'gas' => $gasData
        ];
    }
    
    /**
     * Get Total Pendapatan data - Revenue breakdown by unit
     */
    private function getTotalPendapatanData($selectedYear = null)
    {
        // Get current month/year or from request
        $month = request('month', date('m'));
        $year = $selectedYear ?? (int)request('year', date('Y'));
        
        // Rental Equipment Revenue
        $rentalRevenue = RentalBooking::withTrashed()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->sum('total_amount');
        
        $rentalTransactions = RentalBooking::withTrashed()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->count();
        
        // Gas Sales Revenue
        $gasRevenue = GasOrder::withTrashed()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->sum(DB::raw('price * quantity'));
        
        $gasTransactions = GasOrder::withTrashed()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->count();

        // Manual Reports Revenue
        $manualRevenue = ManualReport::whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->sum(DB::raw('amount * quantity'));
        
        $manualTransactions = ManualReport::whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->count();
        
        $totalRevenue = $rentalRevenue + $gasRevenue + $manualRevenue;
        $totalTransactions = $rentalTransactions + $gasTransactions + $manualTransactions;
        
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
            'total' => [
                'revenue' => $totalRevenue,
                'transactions' => $totalTransactions
            ],
            'month' => $month,
            'year' => $year
        ];
    }
}
