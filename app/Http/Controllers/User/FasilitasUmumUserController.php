<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FasilitasUmum;
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

        // Gedung & Ruang Publik
        $itemsQuery = FasilitasUmum::inCatalog()->with('pengurus')->where('status', '!=', 'Tidak Tersedia');

        $isSuperAdmin = $user && $user->role === 'super_admin';
        $allowedRegionIds = (! $isSuperAdmin && Region::adaPengaturanLayanan('Fasilitas Umum'))
            ? Region::wilayahLayananTerlihat($user?->region_id, 'Fasilitas Umum')
            : null;

        if ($targetRegionId) {
            // Ketika pengunjung memilih desa/wilayah tertentu dari direktori layanan
            $targetRegion = Region::find($targetRegionId);
            $targetTreeIds = array_map('intval', array_merge([$targetRegionId], Region::getDescendantIds($targetRegionId)));
            $visibleRegionIds = $allowedRegionIds !== null
                ? array_values(array_intersect($targetTreeIds, array_map('intval', $allowedRegionIds)))
                : $targetTreeIds;

            // Fasilitas gedung hanya menampilkan milik wilayah yang dipilih dan memang boleh diakses
            $itemsQuery->whereIn('region_id', $visibleRegionIds ?: [-1]);

            $region = $targetRegion ?: Region::where('type', 'kabupaten')->first();
        } else {
            // Akses umum / dari beranda berdasarkan akun user login
            $userRegionId = $user?->region_id;

            if ($allowedRegionIds !== null) {
                $visibleRegionIds = array_values(array_unique(array_map('intval', $allowedRegionIds)));
                $itemsQuery->whereIn('region_id', $visibleRegionIds ?: [-1]);
            }

            if ($userRegionId) {
                $region = Region::find($userRegionId);
            } else {
                $region = Region::where('type', 'kabupaten')->first() ?: Region::first();
            }
        }

        $availableRegionIds = (clone $itemsQuery)->whereNotNull('region_id')->distinct()->pluck('region_id')->map(fn ($id) => (int) $id)->all();
        if ($filterRegionId) {
            $filterIds = \App\Support\RegionCatalogFilter::idsFor($filterRegionId, $visibleRegionIds);
            $itemsQuery->whereIn('region_id', $filterIds);
        }

        $sort = request('sort', 'terbaru');
        if ($sort === 'terlama') {
            $itemsQuery->orderBy('created_at', 'asc');
        } elseif ($sort === 'termurah') {
            $itemsQuery->orderBy('harga_sewa', 'asc');
        } elseif ($sort === 'termahal') {
            $itemsQuery->orderBy('harga_sewa', 'desc');
        } else {
            $itemsQuery->orderBy('created_at', 'desc');
        }

        $items = $itemsQuery->get();
        $filterRegions = \App\Support\RegionCatalogFilter::options($availableRegionIds, $visibleRegionIds);

        // Kontak darurat dan konfigurasi wilayah
        $regionSettings = $region ? ($region->settings ?? []) : [];

        return view('users.fasilitas-umum-equipment', compact('items', 'regionSettings', 'region', 'targetRegionId', 'filterRegions', 'filterRegionId'));
    }

    public function show($id)
    {
        $item = FasilitasUmum::inCatalog()->with('pengurus')->findOrFail($id);
        $viewer = Auth::user();
        if ($item->region_id && (! $viewer || $viewer->role !== 'super_admin')
            && Region::adaPengaturanLayanan('Fasilitas Umum')
            && Region::whereKey($item->region_id)->exists()) {
            $allowed = Region::wilayahLayananTerlihat($viewer?->region_id, 'Fasilitas Umum');
            abort_unless(in_array((int) $item->region_id, array_map('intval', $allowed), true), 403, 'Layanan ini khusus warga lokal atau sedang tidak aktif.');
        }

        // Rekening & metode pembayaran milik WILAYAH layanan ini, bukan rekening
        // pusat. Pemasukan tiap daerah menjadi tanggung jawab daerahnya sendiri.
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($item->region_id);
        
        return view('users.fasilitas-umum-detail', compact('item', 'setting'));
    }
}
