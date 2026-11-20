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

        $kecamatan = $kecamatanFokus;
        $villages = \App\Support\VillageParticipation::query(Region::where('parent_id', $kecamatan->id)
            ->whereIn('type', ['desa', 'kelurahan']))
            ->orderBy('name')->get();
        $joinedVillages = $villages->filter(fn ($village) => \App\Support\VillageParticipation::joined($village))->values();

        return view('users.region-directory-desa', compact('kecamatan', 'joinedVillages'));
    }
}
