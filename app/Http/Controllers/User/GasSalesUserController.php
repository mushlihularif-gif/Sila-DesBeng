<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Gas;

class GasSalesUserController extends Controller
{
    public function index()
    {
        $items = Gas::where('status', '!=', 'rusak')
                    ->orderBy('created_at', 'desc')
                    ->get();
        
        return view('users.gas-sales', compact('items'));
    }

    public function show($id)
    {
        // Ambil data produk gas spesifik
        $item = Gas::findOrFail($id);
        
        // Ambil pengaturan sistem untuk lokasi
        $setting = \App\Models\SystemSetting::first();
        
        return view('users.gas-detail', compact('item', 'setting'));
    }

    public function booking($id)
    {
        // Ambil data produk gas spesifik
        $item = Gas::findOrFail($id);
        
        // Ambil jumlah dari parameter query, default ke 1
        $quantity = request()->query('quantity', 1);
        
        // Validasi jumlah
        if ($quantity < 1) {
            $quantity = 1;
        }
        if ($quantity > $item->stok) {
            $quantity = $item->stok;
        }
        
        // Ambil pengaturan sistem untuk metode pembayaran dan detail bank
        $setting = \App\Models\SystemSetting::first();
        
        return view('users.gas-booking', compact('item', 'quantity', 'setting'));
    }
}
