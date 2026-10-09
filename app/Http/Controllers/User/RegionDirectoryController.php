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
            'user.fasilitas-umum.equipment' => 'fasilitas-umum',
            'pelaporan.landing' => 'pelaporan-warga',
            'announcements.index' => 'pengumuman-dan-event',
        ];
        
        return $routeMap[$routeName] ?? null;
    }

    private function kecamatanFokus(): Region
    {
        return Region::where('type', 'kecamatan')
            ->where('name', 'Kecamatan Bengkalis')
            ->firstOrFail();
    }

    /** Halaman layanan langsung menampilkan desa di Kecamatan Bengkalis. */
    public function index(Request $request)
    {
        return $this->showDesa($request, $this->kecamatanFokus()->id);
    }

    /**
     * Tampilkan desa hanya di Kecamatan Bengkalis; pilihan kecamatan lain tidak
     * menjadi bagian dari cakupan layanan ini.
     */
    public function showDesa(Request $request, $id)
    {
        $kecamatanFokus = $this->kecamatanFokus();

        if ((int) $id !== (int) $kecamatanFokus->id) {
            return redirect()->route('bumdes.profil', $request->query());
        }

        $kecamatan = Region::where('type', 'kecamatan')
            ->with(['children', 'services' => function($q) {
                $q->where('is_active', true);
            }])
            ->findOrFail($kecamatanFokus->id);
            
        $desas = $kecamatan->children()
            ->whereIn('type', ['desa', 'kelurahan'])
            ->orderBy('name')
            ->get();

        if ($kecamatan->contact_phone) {
            $whatsappNumber = $kecamatan->contact_phone;
        } else {
            $settings = \App\Models\SystemSetting::first();
            $whatsappNumber = $settings->whatsapp_number ?? '+6281234567890';
        }
        
        $cleanNumber = preg_replace('/[^0-9+]/', '', $whatsappNumber);
        $whatsappLink = 'https://wa.me/' . ltrim($cleanNumber, '+');
        
        $members = \App\Models\BumdesMember::where('region_id', $kecamatan->id)->orderBy('level', 'asc')->orderBy('order', 'asc')->get();
        
        $region = $kecamatan;
        $activeServices = $region->services->pluck('name')->toArray();
        $villageServices = Region::where('parent_id', $kecamatan->id)
            ->whereIn('type', ['desa', 'kelurahan'])
            ->with(['services' => fn ($query) => $query->where('is_active', true)])
            ->get()
            ->flatMap(fn ($desa) => $desa->services->pluck('name'))
            ->all();
        $activeServices = array_values(array_unique(array_merge($activeServices, $villageServices)));
        $isWhatsappActive = $region && isset($region->payment_info['whatsapp_active']) ? $region->payment_info['whatsapp_active'] : false;

        return view('users.region-directory-desa', compact('kecamatan', 'desas', 'whatsappLink', 'members', 'region', 'activeServices', 'isWhatsappActive'));
    }
}
