import os
import re

file_path = r"D:\laragon\www\SilaDesBeng\app\Http\Controllers\Admin\DashboardController.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update getTotalPendapatanData to include Fasilitas Umum and Pasar Daerah
old_revenue = """        // Pendapatan Laporan Manual
        $manualRevenue = $this->applyRegionFilter(ManualReport::query(), 'creator', true)->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->sum(\DB::raw('amount * quantity'));
        
        $manualTransactions = $this->applyRegionFilter(ManualReport::query(), 'creator', true)->whereYear('transaction_date', $year)
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
            'total' => ["""

new_revenue = """        // Pendapatan Fasilitas Umum
        $fasilitasRevenue = $this->applyRegionFilter(\App\Models\FasilitasUmumBooking::withTrashed(), 'user', true)->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->sum('total_amount');
            
        $fasilitasTransactions = $this->applyRegionFilter(\App\Models\FasilitasUmumBooking::withTrashed(), 'user', true)->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
            ->count();

        // Pendapatan Pasar Daerah
        $pasarRevenue = $this->applyRegionFilter(\App\Models\PasarOrder::withTrashed(), 'user', true)->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['waiting', 'processing', 'cancelled', 'rejected'])
            ->sum('grand_total');
            
        $pasarTransactions = $this->applyRegionFilter(\App\Models\PasarOrder::withTrashed(), 'user', true)->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->whereNotIn('status', ['waiting', 'processing', 'cancelled', 'rejected'])
            ->count();

        // Pendapatan Laporan Manual
        $manualRevenue = $this->applyRegionFilter(ManualReport::query(), 'creator', true)->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->sum(\DB::raw('amount * quantity'));
        
        $manualTransactions = $this->applyRegionFilter(ManualReport::query(), 'creator', true)->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->count();
        
        $totalRevenue = $rentalRevenue + $gasRevenue + $mobilRevenue + $fasilitasRevenue + $pasarRevenue + $manualRevenue;
        $totalTransactions = $rentalTransactions + $gasTransactions + $mobilTransactions + $fasilitasTransactions + $pasarTransactions + $manualTransactions;
        
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
            'Pasar Daerah' => [
                'revenue' => $pasarRevenue,
                'transactions' => $pasarTransactions,
                'percentage' => $totalRevenue > 0 ? round(($pasarRevenue / $totalRevenue) * 100, 1) : 0,
                'color' => 'primary'
            ],
            'total' => ["""

content = content.replace(old_revenue, new_revenue)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
print("DashboardController updated!")
