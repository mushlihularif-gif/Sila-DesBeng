<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Barang;

class RentalUserController extends Controller
{
    public function index()
    {
        // Ambil semua item penyewaan (kecuali item rusak)
        $items = Barang::where('status', '!=', 'rusak')
                       ->orderBy('created_at', 'desc')
                       ->get();
        
        return view('users.rental-equipment', compact('items'));
    }

    public function show($id)
    {
        // Ambil item penyewaan spesifik
        $item = Barang::findOrFail($id);
        
        // Ambil pengaturan sistem untuk lokasi
        $setting = \App\Models\SystemSetting::first();
        
        return view('users.rental-detail', compact('item', 'setting'));
    }
}
