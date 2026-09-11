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
        $targetRegionId = request('region_id');
        $query = Gas::where('status', '!=', 'rusak');
        
        $isGasCrisis = false;
        $hasKk = false;
        $familyCardNumber = null;
        $pendingKk = false;

        if ($targetRegionId) {
            $regionIds = array_merge([(int) $targetRegionId], \App\Models\Region::getDescendantIds($targetRegionId));
            $query->whereIn('region_id', $regionIds);

            $region = \App\Models\Region::find($targetRegionId);
            if ($region && $region->is_gas_crisis) {
                $isGasCrisis = true;
            }
        } elseif (auth()->check() && auth()->user()->role === 'user' && auth()->user()->region_id) {
            $user = auth()->user();
            $allowed = \App\Models\Region::wilayahLayananTerlihat($user->region_id, 'Penjualan Gas');
            $query->where(function($sub) use ($allowed) {
                $sub->whereIn('region_id', $allowed)
                    ->orWhereNull('region_id');
            });
            
            // Cek mode krisis gas
            $region = \App\Models\Region::find($user->region_id);
            if ($region && $region->is_gas_crisis) {
                $isGasCrisis = true;
            }
        }

        if (auth()->check() && $isGasCrisis) {
            $user = auth()->user();
            if ($user->familyMember && $user->familyMember->familyCard) {
                $hasKk = true;
                $familyCardNumber = $user->familyMember->familyCard->no_kk_masked;
            } else {
                $pending = \App\Models\FamilyCard::where('submitted_by', $user->id)
                    ->where('status', 'pending')->first();
                if ($pending) {
                    $pendingKk = true;
                }
            }
        }

        if ($kategori) {
            $query->where('kategori', $kategori);
        }

        $items = $query->orderBy('created_at', 'desc')->get();

        // Statistik
        $stats = [
            'total_produk'   => Gas::where('status', '!=', 'rusak')->count(),
            'total_stok'     => Gas::where('status', '!=', 'rusak')->sum('stok'),
            'total_transaksi'=> GasOrder::count(),
            'selesai'        => GasOrder::where('status', 'completed')->orWhere('status', 'selesai')->count(),
        ];

        return view('users.gas-sales', compact('items', 'kategori', 'stats', 'isGasCrisis', 'hasKk', 'familyCardNumber', 'pendingKk'));
    }



    public function show($id)
    {
        // Ambil data produk gas spesifik
        $item = Gas::findOrFail($id);
        
        // Ambil pengaturan sistem untuk lokasi
        $setting = \App\Models\SystemSetting::first();
        
        return view('users.gas-detail', compact('item', 'setting'));
    }

    public function booking($id)
    {
        // Ambil data produk gas spesifik
        $item = Gas::findOrFail($id);
        $user = auth()->user();

        // Validasi: Warga hanya bisa memesan layanan di wilayahnya sendiri
        if (! in_array($item->region_id, \App\Models\Region::wilayahLayananTerlihat($user->region_id, 'Penjualan Gas'))) {
            return redirect()->back()->with('error', 'Layanan khusus warga lokal. Silakan sesuaikan wilayah Anda.');
        }

        // Validasi Mode Krisis Gas (hanya jika mode krisis diaktifkan oleh admin)
        $region = \App\Models\Region::find($user->region_id);
        if ($region && $region->is_gas_crisis) {
            // Cek apakah punya KK terverifikasi
            if (!$user->familyMember || !$user->familyMember->familyCard) {
                return redirect()->route('gas.sales')->with('error', 'Desa sedang dalam mode krisis gas. Anda wajib memverifikasi Kartu Keluarga (KK) terlebih dahulu.');
            }
        }
        
        // Ambil jumlah dari parameter query, default ke 1
        $quantity = request()->query('quantity', 1);
        
        // Validasi jumlah
        if ($quantity < 1) {
            $quantity = 1;
        }
        if ($quantity > $item->stok) {
            $quantity = $item->stok;
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
