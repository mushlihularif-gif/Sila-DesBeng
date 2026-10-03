<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FasilitasUmum;
use App\Models\Mobil;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FasilitasUmumUserController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $targetRegionId = $request->integer('region_id') ?: null;
        $filterRegionId = $request->integer('filter_region_id') ?: null;
        $visibleRegionIds = null;

        // 1. Gedung & Ruang Publik
        $itemsQuery = FasilitasUmum::with('pengurus')->where('status', '!=', 'Tidak Tersedia');

        // 2. Armada Ambulans & Kendaraan Layanan
        $kendaraansQuery = Mobil::with('supirs')
            ->whereIn('kategori', ['ambulans', 'kendaraan_operasional'])
            ->where('status', '!=', 'rusak');

        if ($targetRegionId) {
            // Ketika pengunjung memilih desa/wilayah tertentu dari direktori layanan
            $targetRegion = Region::find($targetRegionId);
            $visibleRegionIds = array_merge([$targetRegionId], Region::getDescendantIds($targetRegionId));

            // Fasilitas gedung hanya menampilkan milik wilayah yang dipilih
            $itemsQuery->whereIn('region_id', $visibleRegionIds);

            // Ambulans/kendaraan menampilkan milik wilayah tersebut atau induknya jika disediakan terpusat
            $vehicleRegionIds = array_values(array_unique(array_merge($visibleRegionIds, Region::getAncestorIds($targetRegionId))));
            $kendaraansQuery->whereIn('region_id', $vehicleRegionIds);
            $visibleRegionIds = $vehicleRegionIds;

            $region = $targetRegion ?: Region::where('type', 'kabupaten')->first();
        } else {
            // Akses umum / dari beranda berdasarkan akun user login
            $userRegionId = ($user && $user->role === 'user') ? $user->region_id : null;

            if ($userRegionId) {
                $relevantRegionIds = array_merge([$userRegionId], Region::getAncestorIds($userRegionId));
                $allowed = Region::wilayahLayananTerlihat($userRegionId, 'Fasilitas Umum');
                $visibleRegionIds = array_values(array_unique(array_merge($allowed, $relevantRegionIds)));

                $itemsQuery->where(function($sub) use ($allowed, $relevantRegionIds) {
                    $sub->whereIn('region_id', $allowed)
                        ->orWhereIn('region_id', $relevantRegionIds);
                });

                $kendaraansQuery->where(function($q) use ($relevantRegionIds) {
                    $q->whereIn('region_id', $relevantRegionIds);
                });

                $region = Region::find($userRegionId);
            } else {
                $region = Region::where('type', 'kabupaten')->first() ?: Region::first();
            }
        }

        $availableRegionIds = array_values(array_unique(array_merge(
            (clone $itemsQuery)->whereNotNull('region_id')->distinct()->pluck('region_id')->map(fn ($id) => (int) $id)->all(),
            (clone $kendaraansQuery)->whereNotNull('region_id')->distinct()->pluck('region_id')->map(fn ($id) => (int) $id)->all(),
        )));
        if ($filterRegionId) {
            $filterIds = \App\Support\RegionCatalogFilter::idsFor($filterRegionId, $visibleRegionIds);
            $vehicleFilterIds = array_values(array_unique(array_merge(
                $filterIds,
                Region::getAncestorIds($filterRegionId)
            )));
            if ($visibleRegionIds !== null) {
                $vehicleFilterIds = array_values(array_intersect($vehicleFilterIds, $visibleRegionIds));
            }
            $itemsQuery->whereIn('region_id', $filterIds);
            $kendaraansQuery->whereIn('region_id', $vehicleFilterIds);
        }

        $items = $itemsQuery->orderBy('created_at', 'desc')->get();
        $kendaraans = $kendaraansQuery->orderBy('created_at', 'desc')->get();
        $filterRegions = \App\Support\RegionCatalogFilter::options($availableRegionIds, $visibleRegionIds);

        // Kontak darurat dan konfigurasi wilayah
        $regionSettings = $region ? ($region->settings ?? []) : [];

        return view('users.fasilitas-umum-equipment', compact('items', 'kendaraans', 'regionSettings', 'region', 'targetRegionId', 'filterRegions', 'filterRegionId'));
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
