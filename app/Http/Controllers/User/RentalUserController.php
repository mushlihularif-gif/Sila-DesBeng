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
        $query = Barang::where('status', '!=', 'rusak');

        $targetRegion = null;
        $visibleRegionIds = null;
        if ($targetRegionId) {
            $targetRegion = \App\Models\Region::find($targetRegionId);
            $visibleRegionIds = array_merge(
                [$targetRegionId],
                \App\Models\Region::getDescendantIds($targetRegionId)
            );
            $query->whereIn('region_id', $visibleRegionIds);
        } elseif (auth()->check() && auth()->user()->role === 'user' && auth()->user()->region_id) {
            $allowed = \App\Models\Region::wilayahLayananTerlihat(auth()->user()->region_id, 'Penyewaan Alat');
            $visibleRegionIds = array_values(array_unique($allowed));
            $query->where(function($sub) use ($visibleRegionIds) {
                $sub->whereIn('region_id', $visibleRegionIds)
                    ->orWhereNull('region_id');
            });
        }

        if ($filterRegionId) {
            $filterRegionIds = array_merge(
                [$filterRegionId],
                \App\Models\Region::getDescendantIds($filterRegionId)
            );

            if ($visibleRegionIds !== null) {
                $filterRegionIds = array_values(array_intersect($filterRegionIds, $visibleRegionIds));
            }

            $query->whereIn('region_id', $filterRegionIds);
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        $availableRegionIds = Barang::where('status', '!=', 'rusak')
            ->whereNotNull('region_id')
            ->distinct()
            ->pluck('region_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $regionTree = \App\Models\Region::query()
            ->get(['id', 'name', 'parent_id'])
            ->keyBy('id');
        $filterableRegionIds = $availableRegionIds;
        foreach ($availableRegionIds as $availableRegionId) {
            $current = $regionTree->get($availableRegionId);
            $depth = 0;
            while ($current && $depth++ < 20) {
                $filterableRegionIds[] = (int) $current->id;
                $current = $current->parent_id ? $regionTree->get($current->parent_id) : null;
            }
        }
        $filterableRegionIds = array_values(array_unique(array_map('intval', $filterableRegionIds)));

        if ($visibleRegionIds !== null) {
            $filterableRegionIds = array_values(array_intersect($filterableRegionIds, $visibleRegionIds));
        }

        $filterRegions = $regionTree->only($filterableRegionIds)->sortBy('name')->values();
        $filterRegions->each(function ($filterRegion) use ($regionTree) {
            $parts = [$filterRegion->name];
            $current = $filterRegion;
            $depth = 0;
            while ($current->parent_id && $depth++ < 20) {
                $current = $regionTree->get($current->parent_id);
                if (!$current) break;
                $parts[] = $current->name;
            }

            $filterRegion->filter_label = implode(' › ', array_reverse($parts));
        });

        return view('users.rental-equipment', compact(
            'items', 'targetRegion', 'filterRegions', 'targetRegionId', 'filterRegionId'
        ));
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
