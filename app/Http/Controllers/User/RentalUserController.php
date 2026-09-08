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
                       // Dulu daftar ini TIDAK disaring sama sekali: warga melihat
                       // barang milik desa lain, lalu ditolak saat memesan. Sekarang
                       // mengikuti sakelar "Eksklusif Warga Lokal" tiap wilayah.
                       ->when(auth()->check() && auth()->user()->role === 'user', function ($q) {
                           $q->whereIn('region_id', \App\Models\Region::wilayahLayananTerlihat(auth()->user()->region_id, 'Penyewaan Alat'));
                       })
                       ->orderBy('created_at', 'desc')
                       ->get();
        
        return view('users.rental-equipment', compact('items'));
    }

    public function show($id)
    {
        // Ambil item penyewaan spesifik
        $item = Barang::findOrFail($id);
        
        // Ambil pengaturan sistem untuk lokasi
        // Rekening & metode pembayaran milik WILAYAH layanan ini, bukan rekening
        // pusat. Pemasukan tiap daerah menjadi tanggung jawab daerahnya sendiri.
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($item->region_id);
        
        return view('users.rental-detail', compact('item', 'setting'));
    }
}
