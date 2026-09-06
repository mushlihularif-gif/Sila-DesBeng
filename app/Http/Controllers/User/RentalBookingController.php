<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\RentalBooking;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RentalBookingController extends Controller
{
    /**
     * Tampilkan formulir pemesanan
     */
    public function create($itemId)
    {
        // Ambil item penyewaan
        $item = Barang::findOrFail($itemId);

        // Validasi KYC: Pengguna harus sudah terverifikasi
        if (Auth::user()->verification_status !== 'verified') {
            return redirect()->back()->with('show_kyc_modal', true);
        }

        // Validasi: Warga hanya bisa memesan layanan di wilayahnya sendiri
        if (! in_array($item->region_id, \App\Models\Region::wilayahLayananTerlihat(Auth::user()->region_id, 'Penyewaan Alat'))) {
            return redirect()->back()->with('error', 'Layanan khusus warga lokal. Silakan sesuaikan wilayah Anda.');
        }
        
        // Rekening yang ditampilkan harus milik wilayah barangnya, bukan rekening
        // pusat. Sejak pemasukan dipegang tiap daerah, SystemSetting::first()
        // akan menampilkan rekening yang salah ke warga.
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($item->region_id);
        
        // Ambil SOP Penyewaan Alat
        $region = \App\Models\Region::find(Auth::user()->region_id);
        $paymentInfo = $region ? ($region->payment_info ?? []) : [];
        
        $activeSop = $paymentInfo['sop_penyewaan_active'] ?? 'ditanggung';
        
        $defaultSopDitanggung = "1. Penyewa wajib menjaga barang sewaan dengan baik.\n2. Jika terjadi KERUSAKAN atau KEHILANGAN barang selama masa penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki alat tersebut sesuai dengan nilai barang.\n3. Keterlambatan pengembalian dapat dikenakan denda sesuai ketentuan yang berlaku.";
        $defaultSopTidakDitanggung = "1. Penyewa wajib menjaga barang sewaan dengan baik.\n2. Jika terjadi kerusakan atau kehilangan barang selama masa penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna (penyewa) karena telah didukung oleh dana operasional/APBD.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan.";
        
        $sop_penyewaan_alat = $paymentInfo['sop_penyewaan_' . $activeSop] ?? ($activeSop == 'ditanggung' ? $defaultSopDitanggung : $defaultSopTidakDitanggung);
        
        // Ambil jumlah dari permintaan (dari halaman detail)
        $quantity = request()->get('quantity', 1);
        

        // Buku alamat warga, supaya alamat pengiriman tidak perlu diketik ulang
        // di setiap unit layanan.
        $alamatTersimpan = \App\Models\AlamatWarga::milik(auth()->id())
            ->with('region')
            ->orderByDesc('is_utama')
            ->orderBy('id')
            ->get();
        return view('users.rental-booking', compact('item', 'setting', 'quantity', 'sop_penyewaan_alat', 'alamatTersimpan'));
    }

    /**
     * Simpan pemesanan
     */
    public function store(Request $request)
    {
        // Validasi permintaan
        $validated = $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'delivery_method' => 'required|in:antar,jemput',
            'quantity' => 'required|integer|min:1|max:50',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            // 'transfer' = transfer manual ke rekening wilayah, dibuktikan lewat
            // unggahan yang ditinjau petugas. Sebelumnya terkunci 'tunai' saja,
            // sehingga rekening wilayah tidak pernah bisa dipakai di unit ini.
            'payment_method' => 'required|in:tunai,transfer',

            // Penerima & Alamat (Wajib untuk Antar & Jemput)
            'recipient_name' => 'required|string|max:255',
            'delivery_address' => 'required|string',

            // Bukti wajib kalau warga memilih transfer — tanpa itu petugas tidak
            // punya dasar untuk memverifikasi pembayarannya.
            'payment_proof' => 'required_if:payment_method,transfer|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            
            // Bidang Tujuan Baru
            'rental_purpose' => 'required|string|max:1000',
        ]);

        // Hitung jumlah hari
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $daysCount = $startDate->diffInDays($endDate) + 1;

        // Server-side price recalculation to prevent parameter tampering
        $item = Barang::findOrFail($validated['barang_id']);

        // Validate stock before proceeding
        if (!$item->hasStock($validated['quantity'])) {
            return response()->json([
                'success' => false,
                'message' => "Mohon maaf, stok tidak mencukupi. Sisa stok: {$item->stok}"
            ], 400);
        }

        $totalAmount = $item->harga_sewa * $validated['quantity'] * $daysCount;

        // Tangani unggahan bukti pembayaran
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        // Buat pemesanan
        $booking = RentalBooking::create([
            'user_id' => Auth::id(),
            'barang_id' => $validated['barang_id'],
            'delivery_method' => $validated['delivery_method'],
            'rental_purpose' => $validated['rental_purpose'],
            'quantity' => $validated['quantity'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days_count' => $daysCount,
            'recipient_name' => $validated['recipient_name'] ?? null,
            'delivery_address' => $validated['delivery_address'] ?? null,
            'payment_method' => $validated['payment_method'],
            'payment_proof' => $paymentProofPath,
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);

        // Catat pergerakan dana ke ledger wilayah. Sebelumnya tidak tercatat sama
        // sekali di sini — hanya GasBookingController yang menulis ke ledger —
        // sehingga saldo wilayah tidak pernah mencerminkan pemasukan sewa alat.
        \App\Models\WalletTransaction::catatPemasukan(
            regionId: $item->region_id,
            referenceType: 'rental',
            referenceId: $booking->id,
            amount: $totalAmount,
            paymentMethod: $validated['payment_method'],
            proofPath: $paymentProofPath,
        );

        // Buat bukti transaksi
        $receipt = \App\Models\TransactionReceipt::create([
            'booking_type' => 'rental',
            'booking_id' => $booking->id,
            'receipt_number' => \App\Models\TransactionReceipt::generateReceiptNumber('rental'),
            'user_id' => Auth::id(),
            'item_name' => $item->nama_barang,
            'quantity' => $validated['quantity'],
            'total_amount' => $totalAmount,
            'payment_method' => $validated['payment_method'],
        ]);

        // Buat notifikasi admin
        \App\Models\AdminNotification::create([
            'type' => 'rental_request',
            'reference_id' => $booking->id,
            'region_id' => $item->region_id,
            'title' => 'Permintaan Penyewaan Baru',
            'message' => 'Permintaan penyewaan ' . $item->nama_barang . ' dari ' . Auth::user()->name,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil dibuat!',
            'booking_id' => $booking->id,
            'receipt_id' => $booking->id, // Gunakan ID pesanan untuk rute bukti transaksi
            'receipt_number' => $receipt->receipt_number,
        ]);
    }
}

