import os
import re

file_path = r"D:\laragon\www\SilaDesBeng\app\Http\Controllers\Admin\DashboardController.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update the Count variables for Donut Chart (Total Transaksi per Kategori)
old_counts = """    $mobilCount = $baseMobil->clone()->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->count();"""

new_counts = """    $mobilCount = $baseMobil->clone()->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->count();

    $fasilitasCount = $baseFasilitas->clone()->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
        ->count();
        
    $pasarCount = $basePasar->clone()->whereYear('created_at', $selectedYear)
        ->whereNotIn('status', ['waiting', 'processing', 'cancelled', 'rejected'])
        ->count();"""

content = content.replace(old_counts, new_counts)

# Pass them to view
old_compact = "return view('admin.dashboard.index', compact("
# Since it might be using an array $data, let's check how view is called.
# Wait, let's look at how it's called.
"""
    }

    $totalPending = $rentalRequests->count() + $gasRequests->count();
"""
# Actually, let's check how the variables are passed in DashboardController.php.
