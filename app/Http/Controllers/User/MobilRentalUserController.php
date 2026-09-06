<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Mobil;

class MobilRentalUserController extends Controller
{
    public function index()
    {
        $items = Mobil::where('status', '!=', 'rusak')
                       // Dulu daftar ini TIDAK disaring sama sekali: warga melihat
                       // barang milik desa lain, lalu ditolak saat memesan. Sekarang
                       // mengikuti sakelar "Eksklusif Warga Lokal" tiap wilayah.
                       ->when(auth()->check() && auth()->user()->role === 'user', function ($q) {
                           $q->whereIn('region_id', \App\Models\Region::wilayahLayananTerlihat(auth()->user()->region_id, 'Penyewaan Mobil'));
                       })
                       ->orderBy('created_at', 'desc')
                       ->get();
        
        return view('users.mobil-rental-equipment', compact('items'));
    }

    public function show($id)
    {
        $item = Mobil::findOrFail($id);
        
        // Rekening & metode pembayaran milik WILAYAH layanan ini, bukan rekening
        // pusat. Pemasukan tiap daerah menjadi tanggung jawab daerahnya sendiri.
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($item->region_id);
        
        return view('users.mobil-rental-detail', compact('item', 'setting'));
    }
}
