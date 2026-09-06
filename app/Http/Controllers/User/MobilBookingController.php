<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use App\Models\MobilBooking;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MobilBookingController extends Controller
{
    public function create($itemId)
    {
        // Validasi KYC: Pengguna harus sudah terverifikasi
        if (Auth::user()->verification_status !== 'verified') {
            return redirect()->back()->with('show_kyc_modal', true);
        }

        $item = Mobil::findOrFail($itemId);

        // Validasi: Warga hanya bisa memesan layanan di wilayahnya sendiri
        if (! in_array($item->region_id, \App\Models\Region::wilayahLayananTerlihat(Auth::user()->region_id, 'Penyewaan Mobil'))) {
            return redirect()->back()->with('error', 'Layanan khusus warga lokal. Silakan sesuaikan wilayah Anda.');
        }
        
        // Rekening & metode pembayaran milik WILAYAH layanan ini, bukan rekening
        // pusat. Pemasukan tiap daerah menjadi tanggung jawab daerahnya sendiri.
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($item->region_id);
        
        // Ambil SOP Penyewaan Mobil
        $region = \App\Models\Region::find(Auth::user()->region_id);
        $paymentInfo = $region ? ($region->payment_info ?? []) : [];
        
        $activeSop = $paymentInfo['sop_mobil_active'] ?? 'ditanggung';
        
        $defaultSopDitanggung = "1. Penyewa wajib menjaga mobil sewaan dengan baik.\n2. Jika terjadi KERUSAKAN atau KEHILANGAN mobil selama masa penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki mobil tersebut sesuai dengan kerusakan.\n3. Keterlambatan pengembalian dapat dikenakan denda sesuai ketentuan yang berlaku.";
        $defaultSopTidakDitanggung = "1. Penyewa wajib menjaga mobil sewaan dengan baik.\n2. Jika terjadi kerusakan atau kehilangan mobil selama masa penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna (penyewa) karena telah didukung oleh dana operasional/APBD.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan.";
        
        $sop_mobil = $paymentInfo['sop_mobil_' . $activeSop] ?? ($activeSop == 'ditanggung' ? $defaultSopDitanggung : $defaultSopTidakDitanggung);
        
        $quantity = request()->get('quantity', 1);

        // Tarif borongan per wilayah. View sudah lama memakai $isWilayah,
        // $tarifWilayah, dan $kecamatanKhusus, tetapi ketiganya tidak pernah
        // dikirim dari sini sehingga halaman ini melempar "Undefined variable"
        // begitu dibuka. Kolomnya juga tidak di-cast, jadi didekode di sini.
        $tarifWilayah = json_decode($item->tarif_borongan_wilayah ?? '', true);
        if (! is_array($tarifWilayah)) {
            $tarifWilayah = [];
        }

        $isWilayah = ($item->tipe_tarif_borongan ?? null) === 'wilayah';

        // Hanya kecamatan yang benar-benar punya tarif khusus yang ditawarkan,
        // supaya daftar pilihannya tidak berisi wilayah tanpa harga.
        $idKecamatanKhusus = array_keys($tarifWilayah['harga_kecamatan_khusus'] ?? []);
        $kecamatanKhusus = $idKecamatanKhusus
            ? \App\Models\Region::whereIn('id', $idKecamatanKhusus)->orderBy('name')->get()
            : collect();


        // Buku alamat warga, supaya alamat pengiriman tidak perlu diketik ulang
        // di setiap unit layanan.
        $alamatTersimpan = \App\Models\AlamatWarga::milik(auth()->id())
            ->with('region')
            ->orderByDesc('is_utama')
            ->orderBy('id')
            ->get();
        return view('users.mobil-rental-booking', compact(
            'item', 'setting', 'quantity', 'sop_mobil',
            'isWilayah', 'tarifWilayah', 'kecamatanKhusus', 'alamatTersimpan'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mobil_id' => 'required|exists:mobils,id',
            'jenis_sewa' => 'required|in:harian,borongan',
            'delivery_method' => 'required|in:antar,jemput',
            'quantity' => 'required|integer|min:1|max:50',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'distance_km' => 'nullable|integer|min:1',
            'tujuan_wilayah' => 'nullable|string',
            // 'transfer' = transfer manual ke rekening wilayah, dibuktikan lewat
            // unggahan yang ditinjau petugas. Sebelumnya terkunci 'tunai' saja,
            // sehingga rekening wilayah tidak pernah bisa dipakai di unit ini.
            'payment_method' => 'required|in:tunai,transfer',
            
            'recipient_name' => 'required|string|max:255',
            'delivery_address' => 'required|string',
            
            // Bukti wajib kalau warga memilih transfer - tanpa itu petugas tidak
            // punya dasar untuk memverifikasi pembayarannya.
            'payment_proof' => 'required_if:payment_method,transfer|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            
            'rental_purpose' => 'required|string|max:1000',
        ]);

        $item = Mobil::findOrFail($validated['mobil_id']);
        
        // Validate availability before proceeding
        if ($item->status !== 'tersedia') {
            return response()->json([
                'success' => false,
                'message' => "Mohon maaf, mobil sedang tidak tersedia saat ini."
            ], 400);
        }

        // Hitung totalAmount berdasarkan jenis sewa
        if ($validated['jenis_sewa'] === 'harian') {
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = \Carbon\Carbon::parse($validated['end_date'] ?? $validated['start_date']);
            $days = $startDate->diffInDays($endDate) + 1; // Minimal 1 hari
            $totalAmount = $item->harga_sewa * $validated['quantity'] * $days;
        } else {
            // Borongan
            if (($item->tipe_tarif_borongan ?? 'jarak') === 'wilayah') {
                $tarifWilayah = json_decode($item->tarif_borongan_wilayah, true) ?? [];
                $tujuan = $validated['tujuan_wilayah'] ?? 'dalam_desa';
                
                if ($tujuan === 'dalam_desa') {
                    $pricePerUnit = $tarifWilayah['harga_dalam_desa'] ?? 0;
                } elseif ($tujuan === 'luar_desa') {
                    $pricePerUnit = $tarifWilayah['harga_luar_desa'] ?? 0;
                } elseif (str_starts_with($tujuan, 'kec_')) {
                    $kecId = str_replace('kec_', '', $tujuan);
                    $pricePerUnit = $tarifWilayah['harga_kecamatan_khusus'][$kecId] ?? 0;
                } else {
                    $pricePerUnit = $tarifWilayah['harga_luar_kecamatan'] ?? 0;
                }
            } else {
                // Berdasarkan Jarak
                $dist = $validated['distance_km'] ?? 1;
                
                if ($item->batas_km_dalam_desa > 0 && $dist <= $item->batas_km_dalam_desa) {
                    $pricePerUnit = $item->harga_dalam_desa;
                } elseif ($item->batas_km_luar_desa > 0 && $dist <= $item->batas_km_luar_desa) {
                    $pricePerUnit = $item->harga_luar_desa;
                } else {
                    $pricePerUnit = $item->harga_luar_kota;
                }
            }
            
            $totalAmount = $pricePerUnit * $validated['quantity'];
            
            // Samakan end_date dengan start_date untuk borongan jika kosong
            if (empty($validated['end_date'])) {
                $validated['end_date'] = $validated['start_date'];
            }
        }

        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        $booking = MobilBooking::create([
            'user_id' => Auth::id(),
            'mobil_id' => $validated['mobil_id'],
            'jenis_sewa' => $validated['jenis_sewa'],
            'delivery_method' => $validated['delivery_method'],
            'rental_purpose' => $validated['rental_purpose'],
            'quantity' => $validated['quantity'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'distance_km' => $validated['distance_km'] ?? 1,
            'tujuan_wilayah' => $validated['tujuan_wilayah'] ?? null,
            'recipient_name' => $validated['recipient_name'] ?? null,
            'delivery_address' => $validated['delivery_address'] ?? null,
            'payment_method' => $validated['payment_method'],
            'payment_proof' => $paymentProofPath,
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);

        // Catat pergerakan dana ke ledger wilayah — sebelumnya tidak tercatat
        // sama sekali di sini, lihat catatan yang sama di RentalBookingController.
        \App\Models\WalletTransaction::catatPemasukan(
            regionId: $item->region_id,
            referenceType: 'mobil',
            referenceId: $booking->id,
            amount: $totalAmount,
            paymentMethod: $validated['payment_method'],
            proofPath: $paymentProofPath,
        );

        $receipt = \App\Models\TransactionReceipt::create([
            'booking_type' => 'mobil',
            'booking_id' => $booking->id,
            'receipt_number' => \App\Models\TransactionReceipt::generateReceiptNumber('mobil'),
            'user_id' => Auth::id(),
            'amount' => $totalAmount,
            'issued_at' => now(),
        ]);

        $booking->update(['receipt_path' => $receipt->receipt_number]);

        return redirect()->route('user.dashboard')
            ->with('success', 'Pemesanan Mobil berhasil dibuat! Menunggu konfirmasi admin.')
            ->with('show_receipt', $receipt->id);
    }
}
