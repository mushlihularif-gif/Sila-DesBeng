<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;

class RegionDirectoryController extends Controller
{
    /**
     * Halaman 1: List Kecamatan
     */
    public function index(Request $request)
    {
        $kecamatans = Region::where('type', 'kecamatan')->orderBy('name')->get();

        return view('users.region-directory', compact('kecamatans'));
    }

    /**
     * Halaman 2: List Desa di Kecamatan tertentu (halaman terpisah)
     */
    public function showDesa($id)
    {
        $kecamatan = Region::where('type', 'kecamatan')->with('children')->findOrFail($id);
        $desas = $kecamatan->children()->orderBy('name')->get();

        return view('users.region-directory-desa', compact('kecamatan', 'desas'));
    }
}
