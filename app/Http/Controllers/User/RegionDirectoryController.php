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

        $settings = \App\Models\SystemSetting::first();
        $whatsappNumber = $settings->whatsapp_number ?? '+6281234567890';
        $cleanNumber = preg_replace('/[^0-9+]/', '', $whatsappNumber);
        $whatsappLink = 'https://wa.me/' . ltrim($cleanNumber, '+');

        return view('users.region-directory', compact('kecamatans', 'whatsappLink'));
    }

    /**
     * Halaman 2: List Desa di Kecamatan tertentu (halaman terpisah)
     */
    public function showDesa($id)
    {
        $kecamatan = Region::where('type', 'kecamatan')->with('children')->findOrFail($id);
        $desas = $kecamatan->children()->orderBy('name')->get();

        $settings = \App\Models\SystemSetting::first();
        $whatsappNumber = $settings->whatsapp_number ?? '+6281234567890';
        $cleanNumber = preg_replace('/[^0-9+]/', '', $whatsappNumber);
        $whatsappLink = 'https://wa.me/' . ltrim($cleanNumber, '+');

        return view('users.region-directory-desa', compact('kecamatan', 'desas', 'whatsappLink'));
    }
}
