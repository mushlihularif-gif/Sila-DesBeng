<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Gas;
use App\Models\GasOrder;

class GasSalesUserController extends Controller
{
    public function index()
    {
        $kategori = request('kategori', '');

        $query = Gas::where('status', '!=', 'rusak');

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        // Statistik
        $stats = [
            'total_produk'   => Gas::where('status', '!=', 'rusak')->count(),
            'total_stok'     => Gas::where('status', '!=', 'rusak')->sum('stok'),
            'total_transaksi'=> GasOrder::count(),
            'selesai'        => GasOrder::where('status', 'completed')->orWhere('status', 'selesai')->count(),
        ];

        return view('users.gas-sales', compact('items', 'kategori', 'stats'));
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

        // Validasi: Warga hanya bisa memesan layanan di wilayahnya sendiri
        if (auth()->user()->region_id != $item->region_id) {
            return redirect()->back()->with('error', 'Anda Tidak Bisa Melanjutkan karena desa/wilayah ini hanya menyediakan layanan untuk warganya sendiri. Silakan sesuaikan dengan wilayah Anda.');
        }
        
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
