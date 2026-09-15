<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AmbulansUserController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $region = $user && $user->region_id ? Region::find($user->region_id) : null;
        
        if (!$region) {
            $region = Region::where('type', 'desa')->first() ?? Region::first();
        }

        $relevantRegionIds = [];
        if ($user && $user->region_id) {
            $relevantRegionIds = array_merge([$user->region_id], Region::getAncestorIds($user->region_id));
        }

        // Ambil data ambulans untuk wilayah pengguna, wilayah leluhur (desa/kecamatan/kabupaten), atau umum
        $ambulansQuery = Mobil::with('supirs')
                             ->where('kategori', 'ambulans')
                             ->where('status', '!=', 'rusak');

        if (!empty($relevantRegionIds)) {
            $ambulansQuery->where(function($q) use ($relevantRegionIds) {
                $q->whereIn('region_id', $relevantRegionIds)
                  ->orWhereNull('region_id');
            });
        }

        $ambulansList = $ambulansQuery->get();
        $regionSettings = $region ? ($region->settings ?? []) : [];

        return view('users.ambulans-layanan', compact('ambulansList', 'regionSettings', 'region'));
    }

    public function show($id)
    {
        $ambulans = Mobil::whereIn('kategori', ['ambulans', 'kendaraan_operasional'])
            ->with(['supirs', 'region'])
            ->findOrFail($id);

        $user = Auth::user();
        $userRegionId = $user ? $user->region_id : $ambulans->region_id;
        $region = $userRegionId ? Region::find($userRegionId) : $ambulans->region;
        $regionSettings = $region ? ($region->settings ?? []) : [];

        return view('users.ambulans-detail', compact('ambulans', 'region', 'regionSettings'));
    }
}
