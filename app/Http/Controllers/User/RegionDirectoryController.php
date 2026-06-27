<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Region;

class RegionDirectoryController extends Controller
{
    private function getServiceSlugFromRoute($routeName)
    {
        if (!$routeName) return null;
        
        $routeMap = [
            'rental.equipment' => 'penyewaan-alat',
            'gas.sales' => 'penjualan-gas',
            'mobil.rental.equipment' => 'penyewaan-mobil',
            'user.fasilitas-umum.equipment' => 'peminjaman-fasilitas-umum',
            'pelaporan.landing' => 'pelaporan-warga',
            'announcements.index' => 'pengumuman-dan-event',
        ];
        
        return $routeMap[$routeName] ?? null;
    }

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
        $region = \App\Models\Region::with(['services' => function($q) {
            $q->where('is_active', true);
        }])->where('type', 'kabupaten')->first();
        
        $activeServices = [];
        if ($region) {
            $activeServices = $region->services->pluck('name')->toArray();
        }

        $members = \App\Models\BumdesMember::whereNull('region_id')->orWhere('region_id', 0)->orderBy('order')->get();
        
        $isWhatsappActive = $region && isset($region->payment_info['whatsapp_active']) ? $region->payment_info['whatsapp_active'] : false;

        return view('users.region-directory', compact('kecamatans', 'whatsappLink', 'members', 'region', 'activeServices', 'isWhatsappActive'));
    }

    /**
     * Halaman 2: List Desa di Kecamatan tertentu (halaman terpisah)
     */
    public function showDesa(Request $request, $id)
    {
        $kecamatan = Region::where('type', 'kecamatan')
            ->with(['children', 'services' => function($q) {
                $q->where('is_active', true);
            }])
            ->findOrFail($id);
            
        $desas = $kecamatan->children()->orderBy('name')->get();

        if ($kecamatan->contact_phone) {
            $whatsappNumber = $kecamatan->contact_phone;
        } else {
            $settings = \App\Models\SystemSetting::first();
            $whatsappNumber = $settings->whatsapp_number ?? '+6281234567890';
        }
        
        $cleanNumber = preg_replace('/[^0-9+]/', '', $whatsappNumber);
        $whatsappLink = 'https://wa.me/' . ltrim($cleanNumber, '+');
        
        $members = \App\Models\BumdesMember::where('region_id', $kecamatan->id)->orderBy('order')->get();
        
        $region = $kecamatan;
        $activeServices = $region->services->pluck('name')->toArray();
        $isWhatsappActive = $region && isset($region->payment_info['whatsapp_active']) ? $region->payment_info['whatsapp_active'] : false;

        return view('users.region-directory-desa', compact('kecamatan', 'desas', 'whatsappLink', 'members', 'region', 'activeServices', 'isWhatsappActive'));
    }
}
