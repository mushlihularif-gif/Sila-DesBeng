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
        if ($item->region_id && Auth::user()->region_id && ! in_array($item->region_id, \App\Models\Region::wilayahLayananTerlihat(Auth::user()->region_id, 'Penyewaan Alat'))) {
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

        // Cek apakah wilayah ini sudah siap gateway Midtrans
        $adaGateway = \App\Support\PenyediaPembayaran::terapkanMidtransWilayah($item->region_id);

        return view('users.rental-booking', compact('item', 'setting', 'quantity', 'sop_penyewaan_alat', 'alamatTersimpan', 'adaGateway'));
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
            'payment_method' => 'required|in:tunai,transfer,ewallet,bank_transfer_bca,bank_transfer_bri,bank_transfer_bni,bank_transfer_mandiri,bank_transfer_bsi,gopay,qris',

            // Penerima & Alamat (Wajib untuk Antar & Jemput)
            'recipient_name' => 'required|string|max:255',
            'delivery_address' => 'required|string',

            // Bukti wajib kalau warga memilih transfer — tanpa itu petugas tidak
            // punya dasar untuk memverifikasi pembayarannya.
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            
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
            'order_number' => RentalBooking::generateOrderNumber(),
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
            'region_id' => $item->region_id ?? Auth::user()->region_id,
        ]);

        // Catat pergerakan dana ke ledger wilayah
        $lewatGatewayCheck = ! in_array($validated['payment_method'], ['tunai', 'transfer', 'ewallet'], true);
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

        // Notifikasi ke penyewa
        \App\Services\NotificationService::notifyOrderCreated('rental', $booking, $item->nama_barang);

        $response = [
            'success' => true,
            'message' => 'Pemesanan berhasil dibuat!',
            'booking_id' => $booking->id,
            'receipt_id' => $booking->id, // Gunakan ID pesanan untuk rute bukti transaksi
            'receipt_number' => $receipt->receipt_number,
        ];

        // Gateway logic
        $lewatGateway = ! in_array($validated['payment_method'], ['tunai', 'transfer', 'ewallet'], true);

        if ($lewatGateway) {
            $siap = \App\Support\PenyediaPembayaran::terapkanMidtransWilayah($item->region_id);
            if (! $siap) {
                \Illuminate\Support\Facades\Log::warning('Gateway dilewati: wilayah belum siap', [
                    'region_id' => $item->region_id,
                ]);
                $lewatGateway = false;
            }
        }

        if ($lewatGateway) {
            $paymentMethod = $validated['payment_method'];

            $params = [
                'transaction_details' => [
                    'order_id' => $booking->order_number,
                    'gross_amount' => $totalAmount,
                ],
                'customer_details' => [
                    'first_name' => $validated['recipient_name'] ?? Auth::user()->name,
                    'email' => Auth::user()->email,
                    'phone' => Auth::user()->phone ?? '081234567890',
                ],
                'item_details' => [
                    [
                        'id' => $item->id,
                        'price' => $item->harga_sewa,
                        'quantity' => $validated['quantity'] * $daysCount, // Assuming price is per unit per day
                        'name' => $item->nama_barang,
                    ],
                ],
            ];

            $kanal = \App\Support\PenyediaPembayaran::kanalSnap($paymentMethod);
            if ($kanal) {
                $params['enabled_payments'] = [$kanal];
            }

            try {
                $snap = \Midtrans\Snap::createTransaction($params);

                $booking->payment_channel = $paymentMethod;
                $booking->snap_token = $snap->token;
                $booking->payment_expiry_time = now()->addDay();
                $booking->save();

                $response['snap_token'] = $snap->token;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Midtrans Snap gagal membuat transaksi', [
                    'order_number' => $booking->order_number,
                    'pesan'        => $e->getMessage(),
                ]);

                $booking->payment_channel = $paymentMethod;
                $booking->save();

                $response['snap_token'] = null;
                $response['gateway_gagal'] = true;
                $response['message'] = 'Pesanan tersimpan, tetapi pembayaran otomatis sedang tidak dapat diproses.';
            }
        }

        return response()->json($response);
    }
}

