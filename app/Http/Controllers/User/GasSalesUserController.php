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
        $query = Gas::where('status', '!=', 'rusak');
        
        $isGasCrisis = false;
        $hasKk = false;
        $familyCardNumber = null;
        $pendingKk = false;

        // Validasi: Warga hanya bisa melihat gas dari desa/wilayahnya sendiri
        if (auth()->check() && auth()->user()->role === 'user') {
            $user = auth()->user();
            $query->where('region_id', $user->region_id);
            
            // Cek mode krisis gas
            $region = \App\Models\Region::find($user->region_id);
            if ($region && $region->is_gas_crisis) {
                $isGasCrisis = true;
                
                // Cek apakah user punya KK terverifikasi
                if ($user->familyMember && $user->familyMember->familyCard) {
                    $hasKk = true;
                    $familyCardNumber = $user->familyMember->familyCard->no_kk_masked;
                } else {
                    // Cek apakah ada pengajuan KK yang pending
                    $pending = \App\Models\FamilyCard::where('submitted_by', $user->id)
                        ->where('status', 'pending')->first();
                    if ($pending) {
                        $pendingKk = true;
                    }
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

        // Validasi KYC: Pengguna harus sudah terverifikasi
        if ($user->verification_status !== 'verified') {
            return redirect()->back()->with('show_kyc_modal', true);
        }

        // Validasi: Warga hanya bisa memesan layanan di wilayahnya sendiri
        if ($user->region_id != $item->region_id) {
            return redirect()->back()->with('error', 'Layanan khusus warga lokal. Silakan sesuaikan wilayah Anda.');
        }

        // Validasi Krisis Gas
        $region = \App\Models\Region::find($user->region_id);
        if ($region && $region->is_gas_crisis) {
            if (!$user->nik) {
                return redirect()->route('user.gas.sales')->with('error', 'Ini adalah akun khusus pemerintahan (tanpa NIK). Silakan login menggunakan akun warga pribadi Anda yang terdaftar NIK untuk membeli gas subsidi.');
            }
            // Cek apakah punya KK terverifikasi
            if (!$user->familyMember || !$user->familyMember->familyCard) {
                return redirect()->route('user.gas.sales')->with('error', 'Desa sedang dalam mode krisis gas. Anda wajib memverifikasi Kartu Keluarga (KK) terlebih dahulu.');
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
        
        return view('users.gas-booking', compact('item', 'quantity', 'setting'));
    }
}
