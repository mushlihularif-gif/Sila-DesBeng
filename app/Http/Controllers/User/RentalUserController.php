<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Barang;

class RentalUserController extends Controller
{
    public function index()
    {
        $targetRegionId = request()->integer('region_id') ?: null;
        $filterRegionId = request()->integer('filter_region_id') ?: null;
        $query = app(\App\Services\RentalOrdering::class)->visibleQuery(auth()->user());

        $targetRegion = null;
        $visibleRegionIds = null;
        if ($targetRegionId) {
            $targetRegion = \App\Models\Region::find($targetRegionId);
            $visibleRegionIds = array_merge(
                [$targetRegionId],
                \App\Models\Region::getDescendantIds($targetRegionId)
            );
            $query->whereIn('region_id', $visibleRegionIds);
        }

        $availableRegionIds = (clone $query)->whereNotNull('region_id')
            ->distinct()
            ->pluck('region_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($filterRegionId) {
            $query->whereIn(
                'region_id',
                \App\Support\RegionCatalogFilter::idsFor($filterRegionId, $visibleRegionIds)
            );
        }

        $sort = request('sort', 'terbaru');
        if ($sort === 'terlama') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort === 'termurah') {
            $query->orderBy('harga_sewa', 'asc');
        } elseif ($sort === 'termahal') {
            $query->orderBy('harga_sewa', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $items = $query->get();
        $filterRegions = \App\Support\RegionCatalogFilter::options($availableRegionIds, $visibleRegionIds);

        return view('users.rental-equipment', compact(
            'items', 'targetRegion', 'filterRegions', 'targetRegionId', 'filterRegionId'
        ));
    }

    public function show($id)
    {
        // Ambil item penyewaan spesifik
        $item = app(\App\Services\RentalOrdering::class)->visibleQuery(auth()->user())->findOrFail($id);
        
        // Ambil pengaturan sistem untuk lokasi
        // Rekening & metode pembayaran milik WILAYAH layanan ini, bukan rekening
        // pusat. Pemasukan tiap daerah menjadi tanggung jawab daerahnya sendiri.
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($item->region_id);
        
        return view('users.rental-detail', compact('item', 'setting'));
    }
}
