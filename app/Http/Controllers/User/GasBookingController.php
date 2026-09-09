<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Gas;
use App\Models\GasOrder;
use App\Models\SystemSetting;
use App\Models\TransactionReceipt;
use App\Models\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GasBookingController extends Controller
{
    /**
     * Store gas order
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'gas_id' => 'required|exists:gas,id',
            'delivery_method' => 'required|in:antar,jemput',
            'buyer_name' => 'required|string|max:255',
            'buyer_address' => 'required|string',
            // Titik antar hanya terisi bila warga memilih diantar; kolomnya
            // memang boleh kosong untuk pesanan yang diambil sendiri.
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'nomor_kk' => 'required|string|size:16',
            'quantity' => 'required|integer|min:1|max:100',
            // 'transfer' dan 'ewallet' adalah pembayaran manual ke rekening/dompet
            // wilayah. 'transfer' sempat tidak ada di daftar ini padahal tombolnya
            // dirender dan sisa kode di bawah sudah menanganinya, jadi setiap warga
            // yang memilih Transfer Bank ditolak validasi tanpa penjelasan.
            'payment_method' => 'required|in:tunai,transfer,ewallet,bank_transfer_bca,bank_transfer_bri,bank_transfer_bni,bank_transfer_mandiri,bank_transfer_bsi,gopay,qris',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Get gas item
        $gas = Gas::findOrFail($validated['gas_id']);
        
        // Validate stock before proceeding
        if (!$gas->hasStock($validated['quantity'])) {
            return response()->json([
                'success' => false,
                'message' => "Mohon maaf, stok tidak mencukupi. Sisa stok: {$gas->stok}"
            ], 400);
        }

        // --- VALIDASI KUOTA KK (HANYA SAAT MODE KRISIS) ---
        // Baca status Crisis Mode dari region
        $region = \App\Models\Region::find($gas->region_id);
        $settings = $region ? $region->settings : [];
        $isCrisisMode = isset($settings['crisis_mode_gas']) && $settings['crisis_mode_gas'] == true;

        if ($isCrisisMode) {
            $quotaLimit = $settings['gas_quota_limit'] ?? 1;
            $quotaDays = $settings['gas_quota_days'] ?? 7;

            // Hitung total pembelian KK ini dalam kurun waktu quota_days (yang tidak ditolak/dibatalkan)
            $pastDate = now()->subDays($quotaDays);
            
            // Blind Indexing Search
            $kkHash = hash_hmac('sha256', $validated['nomor_kk'], config('app.key'));
            
            $pastOrders = GasOrder::where('nomor_kk_hash', $kkHash)
                ->where('gas_id', $gas->id)
                ->where('created_at', '>=', $pastDate)
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->sum('quantity');

            if (($pastOrders + $validated['quantity']) > $quotaLimit) {
                $sisa = max(0, $quotaLimit - $pastOrders);
                return response()->json([
                    'success' => false,
                    'message' => "Mohon maaf, Mode Krisis sedang aktif. Maksimal pembelian adalah {$quotaLimit} tabung per {$quotaDays} hari. Sisa kuota Anda saat ini: {$sisa} tabung."
                ], 400);
            }
        }
        // --- END VALIDASI KUOTA KK ---

        // Calculate total
        $totalAmount = $gas->harga_satuan * $validated['quantity'];

        // Handle payment proof upload
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        // Generate order number
        $orderNumber = 'GAS-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        // Create gas order
        $order = GasOrder::create([
            'order_number' => $orderNumber,
            'user_id' => Auth::id(),
            'gas_id' => $validated['gas_id'], // Add gas_id for relationship
            'item_name' => $gas->jenis_gas,
            'quantity' => $validated['quantity'],
            'price' => $gas->harga_satuan,
            'order_date' => now(),
            'delivery_method' => $validated['delivery_method'],
            'payment_method' => ucfirst($validated['payment_method']),
            'address' => $validated['buyer_address'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'full_name' => $validated['buyer_name'],
            'email' => Auth::user()->email,
            'nomor_kk' => $validated['nomor_kk'],
            'status' => 'pending',
            'proof_of_payment' => $paymentProofPath,
        ]);

        // Create transaction receipt
        $receipt = TransactionReceipt::create([
            'booking_type' => 'gas',
            'booking_id' => $order->id,
            'receipt_number' => TransactionReceipt::generateReceiptNumber('gas'),
            'user_id' => Auth::id(),
            'region_id' => $gas->region_id,
            'item_name' => $gas->jenis_gas,
            'quantity' => $validated['quantity'],
            'total_amount' => $totalAmount,
            'payment_method' => $validated['payment_method'],
        ]);

        // Catat pergerakan dana ke ledger wilayah. Pembayaran gateway ditahan dulu
        // (escrow) sampai pesanan dikonfirmasi selesai; transfer manual/tunai
        // langsung tercatat "masuk" karena dananya tidak pernah singgah di Midtrans.
        \App\Models\WalletTransaction::catatPemasukan(
            regionId: $gas->region_id,
            referenceType: 'gas',
            referenceId: $order->id,
            amount: $totalAmount,
            paymentMethod: $validated['payment_method'],
            proofPath: $paymentProofPath,
        );

        // Create admin notification
        AdminNotification::create([
            'type' => 'gas_order',
            'reference_id' => $order->id,
            'region_id' => $gas->region_id,
            'title' => 'Pesanan Gas Baru',
            'message' => 'Pesanan ' . $gas->jenis_gas . ' dari ' . Auth::user()->name,
            'is_read' => false,
        ]);

        // Notifikasi ke pembeli
        \App\Services\NotificationService::notifyOrderCreated('gas', $order, $gas->jenis_gas . ' (' . $validated['quantity'] . ' tabung)');

        $response = [
            'success' => true,
            'message' => 'Pembelian gas berhasil dibuat!',
            'order_id' => $order->id,
            'receipt_id' => $receipt->id,
            'receipt_number' => $receipt->receipt_number,
        ];

        // Gateway hanya dijalankan untuk metode otomatis. 'tunai' dan 'transfer'
        // (transfer manual ke rekening wilayah, dibuktikan lewat unggahan) tidak
        // melewati gateway sama sekali.
        $lewatGateway = ! in_array($validated['payment_method'], ['tunai', 'transfer', 'ewallet'], true);

        if ($lewatGateway) {
            // Kredensial WILAYAH, bukan kredensial platform. Kalau wilayah ini
            // belum siap, gateway TIDAK dijalankan — lebih baik pesanan menunggu
            // pembayaran manual daripada uang warga masuk ke rekening yang salah.
            $siap = \App\Support\PenyediaPembayaran::terapkanMidtransWilayah($gas->region_id);

            if (! $siap) {
                \Illuminate\Support\Facades\Log::warning('Gateway dilewati: wilayah belum siap', [
                    'region_id' => $gas->region_id,
                    'alasan'    => \App\Support\PenyediaPembayaran::kesiapanWilayah($gas->region_id)['alasan'],
                ]);

                $lewatGateway = false;
            }
        }

        if ($lewatGateway) {
            $paymentMethod = $validated['payment_method'];

            // Snap, bukan Core API. Akun sandbox ini hanya punya Snap; Core API
            // menolak SEMUA kanal dengan 402 "Payment channel is not activated",
            // termasuk saat dipanggil langsung dengan cURL di luar aplikasi.
            //
            // Kanal dibatasi sesuai pilihan warga di halaman ini, supaya mereka
            // tidak perlu memilih bank dua kali. Metode yang belum ada di peta
            // membiarkan Snap menampilkan seluruh kanal aktif.
            $params = [
                'transaction_details' => [
                    'order_id' => $orderNumber,
                    'gross_amount' => $totalAmount,
                ],
                'customer_details' => [
                    'first_name' => $validated['buyer_name'],
                    'email' => Auth::user()->email,
                    'phone' => Auth::user()->phone ?? '081234567890',
                ],
                'item_details' => [
                    [
                        'id' => $gas->id,
                        'price' => $gas->harga_satuan,
                        'quantity' => $validated['quantity'],
                        'name' => $gas->jenis_gas,
                    ],
                ],
            ];

            $kanal = \App\Support\PenyediaPembayaran::kanalSnap($paymentMethod);
            if ($kanal) {
                $params['enabled_payments'] = [$kanal];
            }

            try {
                $snap = \Midtrans\Snap::createTransaction($params);

                $order->payment_channel = $paymentMethod;
                $order->snap_token = $snap->token;
                $order->payment_expiry_time = now()->addDay();
                $order->save();

                $response['snap_token'] = $snap->token;
            } catch (\Exception $e) {
                // TIDAK ADA nomor VA palsu di sini.
                //
                // Versi sebelumnya menangkap kegagalan Midtrans lalu mengarang
                // nomor VA acak 11 digit supaya demo tidak pernah terlihat
                // error. Akibatnya kegagalan gateway tersembunyi berhari-hari,
                // dan kalau sampai produksi, warga akan mentransfer ke nomor
                // yang tidak dikenal bank mana pun.
                \Illuminate\Support\Facades\Log::error('Midtrans Snap gagal membuat transaksi', [
                    'order_number' => $orderNumber,
                    'metode'       => $paymentMethod,
                    'pesan'        => $e->getMessage(),
                ]);

                $order->payment_channel = $paymentMethod;
                $order->save();

                $response['snap_token'] = null;
                $response['gateway_gagal'] = true;
                $response['message'] = 'Pesanan tersimpan, tetapi pembayaran otomatis '
                    . 'sedang tidak dapat diproses. Silakan hubungi petugas desa atau '
                    . 'ganti metode pembayaran dari halaman Aktivitas.';
            }
        }


        // Create notification for user
        \App\Models\Notification::create([
            'user_id' => $order->user_id,
            'type' => 'status_berubah',
            'title' => 'Menunggu Pembayaran',
            'message' => 'Pesanan gas Anda (Order ID: ' . $order->order_number . ') berhasil dibuat. Silakan selesaikan pembayaran sebelum batas waktu habis.',
            'is_read' => false,
            'link' => route('user.activity'),
            'icon' => 'fas fa-clock text-yellow-500'
        ]);

        return response()->json($response);
    }

    /**
     * Tanyakan instrumen bayar (QR / nomor VA) langsung ke Midtrans.
     *
     * Snap menerbitkannya saat warga memilih kanal di dalam popup, dan halaman
     * kita baru tahu lewat notifikasi 'pending'. Untuk QRIS notifikasi itu
     * sering TIDAK dikirim sampai ada pembayaran, sehingga menunggu saja
     * membuat halaman memuat ulang tanpa henti tanpa hasil.
     *
     * Jadi di sini kita menarik, bukan menunggu.
     */
    public function sinkronPembayaran($id)
    {
        $order = \App\Models\GasOrder::findOrFail($id);

        if ((int) $order->user_id !== (int) \Illuminate\Support\Facades\Auth::id()
            && \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
            abort(403);
        }

        // Sudah ada isinya, tidak perlu menembak Midtrans lagi.
        if ($order->payment_qr_url || $order->payment_va_number) {
            return response()->json([
                'siap'      => true,
                'qr_url'    => $order->payment_qr_url,
                'va_number' => $order->payment_va_number,
                'status'    => $order->status,
            ]);
        }

        $gas = \App\Models\Gas::find($order->gas_id);

        if (! $gas || ! \App\Support\PenyediaPembayaran::terapkanMidtransWilayah($gas->region_id)) {
            return response()->json(['siap' => false, 'alasan' => 'gateway_belum_siap']);
        }

        try {
            $detail = \Midtrans\Transaction::status($order->order_number);
            $berubah = false;

            if (isset($detail->va_numbers[0]->va_number)) {
                $order->payment_va_number = $detail->va_numbers[0]->va_number;
                $berubah = true;
            } elseif (isset($detail->biller_code, $detail->bill_key)) {
                $order->payment_va_number = $detail->biller_code . '-' . $detail->bill_key;
                $berubah = true;
            }

            if (isset($detail->actions)) {
                foreach ($detail->actions as $aksi) {
                    if (($aksi->name ?? null) === 'generate-qr-code') {
                        $order->payment_qr_url = $aksi->url;
                        $berubah = true;
                        break;
                    }
                }
            }

            if ($berubah) {
                $order->save();
            }

            return response()->json([
                'siap'      => $berubah,
                'qr_url'    => $order->payment_qr_url,
                'va_number' => $order->payment_va_number,
                'status'    => $order->status,
            ]);
        } catch (\Throwable $e) {
            // Wajar selama warga belum memilih kanal di popup: bagi Midtrans
            // transaksinya memang belum ada. Bukan error yang perlu ditampilkan.
            return response()->json([
                'siap'   => false,
                'alasan' => 'belum_ada_transaksi',
            ]);
        }
    }

    public function payment($id)
    {
        $order = \App\Models\GasOrder::findOrFail($id);
        
        // Ensure the user owns this order
        if ((int)$order->user_id !== (int)\Illuminate\Support\Facades\Auth::id() && \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        return view('users.gas-payment', compact('order'));
    }

    public function paymentPending($id)
    {
        $order = \App\Models\GasOrder::findOrFail($id);
        
        if ((int)$order->user_id !== (int)\Illuminate\Support\Facades\Auth::id() && \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        if ($order->status !== 'pending' || $order->payment_method === 'Tunai') {
            return redirect()->route('user.activity');
        }

        // Redirect to the beautiful payment instructions page instead of the ugly pending page
        return redirect()->route('user.gas.payment', $order->id);
    }

    public function simulatePayment($id)
    {
        $order = GasOrder::findOrFail($id);
        
        // Ensure the user owns this order
        if ((int)$order->user_id !== (int)Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }
        
        $order->status = 'confirmed'; // Tandai sebagai dibayar/dikonfirmasi
        $order->save();
        
        return redirect()->route('user.gas.payment', $order->id)->with('success', 'Simulasi pembayaran berhasil! Pesanan telah lunas.');
    }

    public function cancelPayment($id)
    {
        $order = GasOrder::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($order->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak dapat dibatalkan.'], 400);
        }

        $order->status = 'cancelled';
        $order->save();

        // Create user notification
        \App\Models\Notification::create([
            'user_id' => $order->user_id,
            'type' => 'gas_payment_expired',
            'title' => 'Pembayaran Gas Dibatalkan',
            'message' => 'Pesanan gas Anda (Order ID: ' . $order->order_number . ') telah dibatalkan oleh sistem karena batas waktu pembayaran telah habis.',
            'is_read' => false,
            'link' => route('user.activity'),
            'icon' => 'fas fa-times-circle text-red-500'
        ]);

        return response()->json(['success' => true, 'message' => 'Pesanan berhasil dibatalkan.']);
    }

    public function changePaymentMethod(Request $request, $id)
    {
        $order = GasOrder::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        if ($order->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pesanan berstatus pending yang dapat diubah metode pembayarannya.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:bank_transfer_bca,bank_transfer_bri,bank_transfer_bni,bank_transfer_mandiri,bank_transfer_bsi,gopay,qris',
        ]);

        $newMethod = $validated['payment_method'];

        if ($newMethod === strtolower($order->payment_method) || $newMethod === $order->payment_channel) {
            return redirect()->back()->with('info', 'Metode pembayaran tidak berubah.');
        }

        // Calculate total
        $gas = $order->gas;
        $totalAmount = $gas->harga_satuan * $order->quantity;

        // Generate a new unique order number for Midtrans by appending a suffix
        // We do not change the local $order->order_number because it's used for display
        $midtransOrderId = $order->order_number . '-' . time();

        // Kunci milik wilayah gasnya, bukan kunci platform. Kalau wilayahnya belum
        // siap, jangan diteruskan: SDK akan memakai kunci sisa di Config dan uang
        // warga mendarat di rekening wilayah lain.
        if (! \App\Support\PenyediaPembayaran::terapkanMidtransWilayah($gas->region_id)) {
            \Log::warning('Ganti metode bayar dilewati: wilayah belum siap', [
                'order_number' => $order->order_number,
                'region_id'    => $gas->region_id,
            ]);

            return redirect()->back()->with('error',
                'Pembayaran otomatis untuk wilayah ini sedang tidak tersedia. '
                . 'Silakan pilih pembayaran tunai atau transfer manual.');
        }

        $paymentType = '';
        if (str_starts_with($newMethod, 'bank_transfer_')) {
            $bank = str_replace('bank_transfer_', '', $newMethod);
            if ($bank === 'mandiri') {
                $paymentType = 'echannel';
            } else {
                $paymentType = 'bank_transfer';
            }
        } else if ($newMethod === 'gopay') {
            $paymentType = 'gopay';
        } else if ($newMethod === 'qris') {
            $paymentType = 'qris';
        }

        $params = [
            'payment_type' => $paymentType,
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => $totalAmount,
            ],
            'customer_details' => [
                'first_name' => $order->full_name,
                'email' => $order->email,
                'phone' => Auth::user()->phone ?? '081234567890',
            ],
            'item_details' => [
                [
                    'id' => $gas->id,
                    'price' => $gas->harga_satuan,
                    'quantity' => $order->quantity,
                    'name' => 'Gas ' . $gas->jenis_gas,
                ]
            ],
        ];

        // Midtrans custom expiry (calculate remaining time from original expiry time)
        // Midtrans expects expiry in minutes.
        $expiryTime = \Carbon\Carbon::parse($order->payment_expiry_time);
        $now = \Carbon\Carbon::now();
        $diffInMinutes = $now->diffInMinutes($expiryTime, false);
        
        if ($diffInMinutes <= 0) {
            return redirect()->back()->with('error', 'Waktu pembayaran sudah habis.');
        }

        $params['custom_expiry'] = [
            'order_time' => date('Y-m-d H:i:s O'),
            'expiry_duration' => $diffInMinutes,
            'unit' => 'minute'
        ];

        // Popup dibatasi ke kanal baru yang dipilih warga, supaya mereka tidak
        // perlu memilih ulang di dalam popup.
        $kanal = \App\Support\PenyediaPembayaran::kanalSnap($newMethod);
        if ($kanal) {
            $params['enabled_payments'] = [$kanal];
        }

        try {
            $snap = \Midtrans\Snap::createTransaction($params);

            // payment_expiry_time SENGAJA tidak diperbarui: batas waktunya
            // mengikuti pesanan asli, bukan direset tiap ganti metode.
            $order->payment_channel = $newMethod;
            $order->snap_token = $snap->token;

            if ($newMethod == 'gopay' || $newMethod == 'qris') {
                $order->payment_method = ucfirst($newMethod);
            } else {
                $order->payment_method = 'Bank Transfer ' . strtoupper(str_replace('bank_transfer_', '', $newMethod));
            }

            // Nomor VA lama dikosongkan: yang berlaku sekarang adalah tagihan
            // baru di dalam popup Snap, dan menyisakan nomor lama di halaman
            // hanya membuat warga membayar ke tagihan yang sudah ditinggalkan.
            $order->payment_va_number = null;
            $order->payment_qr_url = null;

            $order->save();

            // Update receipt payment method
            $receipt = \App\Models\TransactionReceipt::where('booking_type', 'gas')->where('booking_id', $order->id)->first();
            if ($receipt) {
                $receipt->payment_method = $order->payment_method;
                $receipt->save();
            }
            return redirect()->route('user.gas.payment', $order->id);
        } catch (\Exception $e) {
            // TIDAK mengarang nomor VA maupun QR palsu di sini.
            //
            // Versi sebelumnya menyimpan rand(10000,99999).rand(100000,999999)
            // sebagai nomor VA dan 'DUMMY_QR_CODE' sebagai QR. Warga melihat
            // halaman pembayaran yang tampak sah, mentransfer ke nomor yang tidak
            // dikenal bank mana pun, dan uangnya hilang tanpa jejak di Midtrans.
            //
            // Metode lama sengaja DIBIARKAN UTUH: lebih baik pesanan tetap pada
            // cara bayar yang sudah berhasil daripada berpindah ke cara yang
            // baru saja gagal dibuatkan tagihannya.
            \Illuminate\Support\Facades\Log::error('Gagal mengganti metode pembayaran gas', [
                'order_id' => $order->id,
                'metode_baru' => $newMethod,
                'pesan' => $e->getMessage(),
            ]);

            return redirect()->route('user.gas.payment', $order->id)
                ->with('error', 'Metode pembayaran gagal diubah karena layanan pembayaran '
                    . 'sedang tidak dapat dihubungi. Metode sebelumnya masih berlaku. '
                    . 'Silakan coba lagi nanti atau hubungi petugas desa.');
        }
    }
}
