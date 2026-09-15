<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Mobil;

class MobilRentalUserController extends Controller
{
    public function index()
    {
        $targetRegionId = request('region_id');
        $query = Mobil::where('status', '!=', 'rusak')
                      ->where(function($q) {
                          $q->whereNotIn('kategori', ['ambulans', 'kendaraan_operasional'])->orWhereNull('kategori');
                      });

        $targetRegion = null;
        if ($targetRegionId) {
            $targetRegion = \App\Models\Region::find($targetRegionId);
            $query->where('region_id', $targetRegionId);
        } elseif (auth()->check() && auth()->user()->role === 'user' && auth()->user()->region_id) {
            $allowed = \App\Models\Region::wilayahLayananTerlihat(auth()->user()->region_id, 'Penyewaan Mobil');
            $query->where(function($sub) use ($allowed) {
                $sub->whereIn('region_id', $allowed)
                    ->orWhereNull('region_id');
            });
        }

        $items = $query->orderBy('created_at', 'desc')->get();
        
        return view('users.mobil-rental-equipment', compact('items', 'targetRegion'));
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
