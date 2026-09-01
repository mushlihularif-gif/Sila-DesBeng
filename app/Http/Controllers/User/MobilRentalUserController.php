<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Mobil;

class MobilRentalUserController extends Controller
{
    public function index()
    {
        $items = Mobil::where('status', '!=', 'rusak')
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
