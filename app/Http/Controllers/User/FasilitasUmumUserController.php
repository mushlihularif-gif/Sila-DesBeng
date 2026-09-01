<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FasilitasUmum;

class FasilitasUmumUserController extends Controller
{
    public function index()
    {
        $items = FasilitasUmum::where('status', '!=', 'Tidak Tersedia')
                       ->orderBy('created_at', 'desc')
                       ->get();
        
        return view('users.fasilitas-umum-equipment', compact('items'));
    }

    public function show($id)
    {
        $item = FasilitasUmum::findOrFail($id);
        
        // Rekening & metode pembayaran milik WILAYAH layanan ini, bukan rekening
        // pusat. Pemasukan tiap daerah menjadi tanggung jawab daerahnya sendiri.
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($item->region_id);
        
        return view('users.fasilitas-umum-detail', compact('item', 'setting'));
    }
}
