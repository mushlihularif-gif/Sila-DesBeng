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
        $targetRegionId = $request->query('region_id');

        // 1. Gedung & Ruang Publik
        $itemsQuery = FasilitasUmum::with('pengurus')->where('status', '!=', 'Tidak Tersedia');

        // 2. Armada Ambulans & Kendaraan Layanan
        $kendaraansQuery = Mobil::with('supirs')
            ->whereIn('kategori', ['ambulans', 'kendaraan_operasional'])
            ->where('status', '!=', 'rusak');

        if ($targetRegionId) {
            // Ketika pengunjung memilih desa/wilayah tertentu dari direktori layanan
            $targetRegion = Region::find($targetRegionId);
            $targetRegionIds = array_merge([(int) $targetRegionId], Region::getDescendantIds($targetRegionId));

            // Fasilitas gedung hanya menampilkan milik desa tersebut
            $itemsQuery->whereIn('region_id', $targetRegionIds);

            // Ambulans/kendaraan menampilkan milik wilayah tersebut atau induknya jika disediakan terpusat
            $vehicleRegionIds = array_merge($targetRegionIds, Region::getAncestorIds($targetRegionId));
            $kendaraansQuery->whereIn('region_id', $vehicleRegionIds);

            $region = $targetRegion ?: Region::where('type', 'kabupaten')->first();
        } else {
            // Akses umum / dari beranda berdasarkan akun user login
            $userRegionId = ($user && $user->role === 'user') ? $user->region_id : null;

            if ($userRegionId) {
                $relevantRegionIds = array_merge([$userRegionId], Region::getAncestorIds($userRegionId));
                $allowed = Region::wilayahLayananTerlihat($userRegionId, 'Fasilitas Umum');

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

        $items = $itemsQuery->orderBy('created_at', 'desc')->get();
        $kendaraans = $kendaraansQuery->orderBy('created_at', 'desc')->get();

        // Kontak darurat dan konfigurasi wilayah
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
