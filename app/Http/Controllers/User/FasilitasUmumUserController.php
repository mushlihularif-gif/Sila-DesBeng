<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FasilitasUmum;
use App\Models\Mobil;
use App\Models\Region;
use Illuminate\Support\Facades\Auth;

class FasilitasUmumUserController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRegionId = $user ? $user->region_id : null;
        
        $relevantRegionIds = [];
        if ($userRegionId) {
            $relevantRegionIds = array_merge([$userRegionId], Region::getAncestorIds($userRegionId));
        }

        // 1. Gedung & Ruang Publik
        $itemsQuery = FasilitasUmum::where('status', '!=', 'Tidak Tersedia');
        if ($user && $user->role === 'user' && $userRegionId) {
            $allowed = Region::wilayahLayananTerlihat($userRegionId, 'Fasilitas Umum');
            $itemsQuery->where(function($sub) use ($allowed, $relevantRegionIds) {
                $sub->whereIn('region_id', $allowed)
                    ->orWhereIn('region_id', $relevantRegionIds)
                    ->orWhereNull('region_id');
            });
        }
        $items = $itemsQuery->orderBy('created_at', 'desc')->get();

        // 2. Armada Ambulans & Kendaraan Layanan
        $kendaraansQuery = Mobil::with('supirs')
            ->whereIn('kategori', ['ambulans', 'kendaraan_operasional'])
            ->where('status', '!=', 'rusak');

        if ($user && $userRegionId) {
            $kendaraansQuery->where(function($q) use ($relevantRegionIds) {
                $q->whereIn('region_id', $relevantRegionIds)
                  ->orWhereNull('region_id');
            });
        }
        $kendaraans = $kendaraansQuery->orderBy('created_at', 'desc')->get();

        // Kontak darurat dan konfigurasi wilayah
        $region = $userRegionId ? Region::find($userRegionId) : Region::first();
        $regionSettings = $region ? ($region->settings ?? []) : [];

        return view('users.fasilitas-umum-equipment', compact('items', 'kendaraans', 'regionSettings', 'region'));
    }

    public function show($id)
    {
        $item = FasilitasUmum::with('pengurus')->findOrFail($id);
        
        // Rekening & metode pembayaran milik WILAYAH layanan ini, bukan rekening
        // pusat. Pemasukan tiap daerah menjadi tanggung jawab daerahnya sendiri.
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($item->region_id);
        
        return view('users.fasilitas-umum-detail', compact('item', 'setting'));
    }
}
