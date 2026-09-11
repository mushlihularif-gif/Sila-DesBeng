<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Barang;

class RentalUserController extends Controller
{
    public function index()
    {
        $targetRegionId = request('region_id');
        $query = Barang::where('status', '!=', 'rusak');

        if ($targetRegionId) {
            $regionIds = array_merge([(int) $targetRegionId], \App\Models\Region::getDescendantIds($targetRegionId));
            $query->whereIn('region_id', $regionIds);
        } elseif (auth()->check() && auth()->user()->role === 'user' && auth()->user()->region_id) {
            $allowed = \App\Models\Region::wilayahLayananTerlihat(auth()->user()->region_id, 'Penyewaan Alat');
            $query->where(function($sub) use ($allowed) {
                $sub->whereIn('region_id', $allowed)
                    ->orWhereNull('region_id');
            });
        }

        $items = $query->orderBy('created_at', 'desc')->get();
        
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
