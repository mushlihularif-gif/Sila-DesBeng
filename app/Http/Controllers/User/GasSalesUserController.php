<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Gas;
use App\Models\GasOrder;

class GasSalesUserController extends Controller
{
    public function index()
    {
        $kategori = request('kategori', '');
        $targetRegionId = request()->integer('region_id') ?: null;
        $filterRegionId = request()->integer('filter_region_id') ?: null;
        $ordering = app(\App\Services\GasOrdering::class);
        $query = $ordering->visibleQuery(auth()->user());
        $visibleRegionIds = (clone $query)->pluck('region_id')->unique()->all();
        $targetRegion = $targetRegionId ? \App\Models\Region::find($targetRegionId) : null;
        if ($targetRegionId) {
            $query->whereIn('region_id', array_merge([$targetRegionId], \App\Models\Region::getDescendantIds($targetRegionId)));
        }
        $isGasCrisis = (clone $query)->with('region')->get()->contains(fn ($gas) => $ordering->crisis($gas));
        $family = auth()->user() ? $ordering->household(auth()->user()) : null;
        $hasKk = $family !== null;
        $familyCardNumber = $family?->no_kk_masked;
        $pendingKk = auth()->check() && \App\Models\FamilyCard::where('submitted_by', auth()->id())->where('status', 'pending')->exists();

        $availableRegionIds = (clone $query)->whereNotNull('region_id')->distinct()->pluck('region_id')->map(fn ($id) => (int) $id)->all();

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        if ($filterRegionId) {
            $query->whereIn('region_id', \App\Support\RegionCatalogFilter::idsFor($filterRegionId, $visibleRegionIds));
        }

        $sort = request('sort', 'terbaru');
        if ($sort === 'terlama') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort === 'termurah') {
            $query->orderBy('harga_satuan', 'asc');
        } elseif ($sort === 'termahal') {
            $query->orderBy('harga_satuan', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $items = $query->get();
        $filterRegions = \App\Support\RegionCatalogFilter::options($availableRegionIds, $visibleRegionIds);

        // Statistik
        $stats = [
            'total_produk'   => Gas::where('status', '!=', 'rusak')->count(),
            'total_stok'     => Gas::where('status', '!=', 'rusak')->sum('stok'),
            'total_transaksi'=> GasOrder::count(),
            'selesai'        => GasOrder::where('status', 'completed')->orWhere('status', 'selesai')->count(),
        ];

        return view('users.gas-sales', compact('items', 'kategori', 'stats', 'isGasCrisis', 'hasKk', 'familyCardNumber', 'pendingKk', 'targetRegion', 'targetRegionId', 'filterRegions', 'filterRegionId'));
    }



    public function show($id)
    {
        // Ambil data produk gas spesifik
        $item = app(\App\Services\GasOrdering::class)->visibleQuery(auth()->user())->findOrFail($id);
        
        // Ambil pengaturan sistem untuk lokasi
        $setting = \App\Models\SystemSetting::first();
        
        return view('users.gas-detail', compact('item', 'setting'));
    }

    public function booking($id)
    {
        // Ambil data produk gas spesifik
        $item = app(\App\Services\GasOrdering::class)->visibleQuery(auth()->user())->findOrFail($id);
        $user = auth()->user();

        $quantity = max(1, min(100, (int) request()->query('quantity', 1)));
        $ordering = app(\App\Services\GasOrdering::class);
        try {
            $ordering->checkEligibility($user, $item, $quantity, $ordering->household($user));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('gas.sales', ['region_id' => $item->region_id])->with('error', collect($e->errors())->flatten()->first());
        }

        // Rekening & metode pembayaran milik WILAYAH gas ini, bukan rekening pusat.
        // Pemasukan tiap daerah menjadi tanggung jawab daerahnya sendiri.
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($item->region_id);

        // Lokasi layanan produk INI. Sebelumnya halaman pembelian hanya membaca
        // $setting->location_name / address / latitude / longitude, yang berasal
        // dari profil pembayaran wilayah maupun SystemSetting platform — bukan
        // dari produknya. Akibatnya kotak "Alamat Pusat Layanan" kosong meski
        // petugas sudah mengisi lokasi beserta titik petanya, dan tombol
        // "Lihat lokasi" jatuh ke tautan Google Maps yang dipaku di view.
        $lokasiLayanan = $item->lokasi
            ? \App\Models\LokasiLayanan::where('region_id', $item->region_id)
                ->where('nama', $item->lokasi)
                ->first()
            : null;

        // Produk lama bisa punya lokasi yang belum terdaftar di daftar wilayah;
        // koordinat pada barisnya sendiri tetap dipakai supaya petanya muncul.
        if (! $lokasiLayanan && $item->lokasi) {
            $lokasiLayanan = new \App\Models\LokasiLayanan([
                'nama'      => $item->lokasi,
                'latitude'  => $item->latitude,
                'longitude' => $item->longitude,
            ]);
        }


        // Buku alamat warga, supaya alamat pengiriman tidak perlu diketik ulang
        // di setiap unit layanan.
        $alamatTersimpan = \App\Models\AlamatWarga::milik(auth()->id())
            ->with('region')
            ->orderByDesc('is_utama')
            ->orderBy('id')
            ->get();
        return view('users.gas-booking', compact('item', 'quantity', 'setting', 'lokasiLayanan', 'alamatTersimpan'));
    }
}
