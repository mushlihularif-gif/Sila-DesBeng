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
        $query = Mobil::forRental()->where('status', '!=', 'rusak');

        $targetRegion = null;
        $viewer = auth()->user();
        $isSuperAdmin = $viewer && $viewer->role === 'super_admin';
        $allowedRegionIds = (! $isSuperAdmin && \App\Models\Region::adaPengaturanLayanan('Penyewaan Mobil'))
            ? \App\Models\Region::wilayahLayananTerlihat($viewer?->region_id, 'Penyewaan Mobil', $viewer === null)
            : null;
        $visibleRegionIds = $allowedRegionIds;

        if ($targetRegionId) {
            $targetRegion = \App\Models\Region::find($targetRegionId);
            $targetTreeIds = array_map('intval', array_merge([$targetRegionId], \App\Models\Region::getDescendantIds($targetRegionId)));
            $visibleRegionIds = $allowedRegionIds !== null
                ? array_values(array_intersect($targetTreeIds, array_map('intval', $allowedRegionIds)))
                : $targetTreeIds;
            $query->whereIn('region_id', $visibleRegionIds ?: [-1]);
        } elseif ($allowedRegionIds !== null) {
            $visibleRegionIds = array_values(array_unique(array_map('intval', $allowedRegionIds)));
            $query->whereIn('region_id', $visibleRegionIds ?: [-1]);
        }

        $availableRegionIds = (clone $query)->whereNotNull('region_id')->distinct()->pluck('region_id')->map(fn ($id) => (int) $id)->all();
        if ($filterRegionId) {
            $query->whereIn('region_id', \App\Support\RegionCatalogFilter::idsFor($filterRegionId, $visibleRegionIds));
        }

        $sort = request('sort', 'terbaru');
        if ($sort === 'terlama') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $items = $query->get();
        // harga_mulai includes daily and regional package tariffs; it is an
        // accessor, not a database column. Sort the already-loaded catalog by
        // the same value shown on its cards.
        if (in_array($sort, ['termurah', 'termahal'], true)) {
            $items = $items->sortBy(fn ($item) => $item->harga_mulai, SORT_REGULAR, $sort === 'termahal')->values();
        }
        $filterRegions = \App\Support\RegionCatalogFilter::options($availableRegionIds, $visibleRegionIds);
        
        return view('users.mobil-rental-equipment', compact('items', 'targetRegion', 'targetRegionId', 'filterRegions', 'filterRegionId'));
    }

    public function show($id)
    {
        $item = Mobil::inCatalog()->findOrFail($id);
        $viewer = auth()->user();
        if ($item->region_id && (! $viewer || $viewer->role !== 'super_admin')
            && \App\Models\Region::adaPengaturanLayanan('Penyewaan Mobil')
            && \App\Models\Region::whereKey($item->region_id)->exists()) {
            $allowed = \App\Models\Region::wilayahLayananTerlihat($viewer?->region_id, 'Penyewaan Mobil', $viewer === null);
            abort_unless(in_array((int) $item->region_id, array_map('intval', $allowed), true), 403, 'Layanan ini khusus warga lokal atau sedang tidak aktif.');
        }

        // Rekening & metode pembayaran milik WILAYAH layanan ini, bukan rekening
        // pusat. Pemasukan tiap daerah menjadi tanggung jawab daerahnya sendiri.
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($item->region_id);
        
        return view('users.mobil-rental-detail', compact('item', 'setting'));
    }
}
