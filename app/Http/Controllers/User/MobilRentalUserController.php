<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Mobil;

class MobilRentalUserController extends Controller
{
    public function index()
    {
        $targetRegionId = request()->integer('region_id') ?: null;
        $filterRegionId = request()->integer('filter_region_id') ?: null;
        $query = Mobil::where('status', '!=', 'rusak')
                      ->where(function($q) {
                          $q->whereNotIn('kategori', ['ambulans', 'kendaraan_operasional'])->orWhereNull('kategori');
                      });

        $targetRegion = null;
        $visibleRegionIds = null;
        if ($targetRegionId) {
            $targetRegion = \App\Models\Region::find($targetRegionId);
            $visibleRegionIds = array_merge([$targetRegionId], \App\Models\Region::getDescendantIds($targetRegionId));
            $query->whereIn('region_id', $visibleRegionIds);
        } elseif (auth()->check() && auth()->user()->role === 'user' && auth()->user()->region_id) {
            $allowed = \App\Models\Region::wilayahLayananTerlihat(auth()->user()->region_id, 'Penyewaan Mobil');
            $visibleRegionIds = array_values(array_unique(array_map('intval', $allowed)));
            $query->where(function($sub) use ($allowed) {
                $sub->whereIn('region_id', $allowed)
                    ->orWhereNull('region_id');
            });
        }

        $availableRegionIds = (clone $query)->whereNotNull('region_id')->distinct()->pluck('region_id')->map(fn ($id) => (int) $id)->all();
        if ($filterRegionId) {
            $query->whereIn('region_id', \App\Support\RegionCatalogFilter::idsFor($filterRegionId, $visibleRegionIds));
        }

        $items = $query->orderBy('created_at', 'desc')->get();
        $filterRegions = \App\Support\RegionCatalogFilter::options($availableRegionIds, $visibleRegionIds);
        
        return view('users.mobil-rental-equipment', compact('items', 'targetRegion', 'targetRegionId', 'filterRegions', 'filterRegionId'));
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
