<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;

class RegionDirectoryController extends Controller
{
    private function kecamatanFokus(): Region
    {
        return Region::where('type', 'kecamatan')
            ->where('name', 'Kecamatan Bengkalis')
            ->firstOrFail();
    }

    /** Pengarah katalog layanan dalam cakupan Kecamatan Bengkalis. */
    public function index(Request $request)
    {
        return $this->showDesa($request, $this->kecamatanFokus()->id);
    }

    /**
     * Tampilkan layanan hanya di Kecamatan Bengkalis; pilihan kecamatan lain tidak
     * menjadi bagian dari cakupan layanan ini.
     */
    public function showDesa(Request $request, $id)
    {
        $kecamatanFokus = $this->kecamatanFokus();

        if ((int) $id !== (int) $kecamatanFokus->id) {
            return redirect()->route('bumdes.profil', $request->query());
        }

        $kecamatan = Region::where('type', 'kecamatan')
            ->with(['services' => function($q) {
                $q->where('is_active', true);
            }])
            ->findOrFail($kecamatanFokus->id);
            
        $region = $kecamatan;
        $activeServices = $region->services->pluck('name')->toArray();
        $villages = \App\Support\VillageParticipation::query(Region::where('parent_id', $kecamatan->id)
            ->whereIn('type', ['desa', 'kelurahan'])
            ->with(['services' => fn ($query) => $query->where('is_active', true)]))
            ->orderBy('name')->get();
        $joinedVillages = $villages->filter(fn ($village) => \App\Support\VillageParticipation::joined($village))->values();
        $villageServices = $villages->flatMap(fn ($desa) => $desa->services->pluck('name'))->all();
        $activeServices = array_values(array_unique(array_merge($activeServices, $villageServices)));

        return view('users.region-directory-desa', compact('kecamatan', 'region', 'activeServices', 'joinedVillages'));
    }
}
