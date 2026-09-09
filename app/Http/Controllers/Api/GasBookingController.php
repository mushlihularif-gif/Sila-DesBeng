<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gas;
use App\Models\GasOrder;
use App\Models\TransactionReceipt;
use App\Models\AdminNotification;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class GasBookingController extends Controller
{
    /**
     * Store gas order from API
     */
    public function store(Request $request)
    {
        $user = $request->user();

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
            'quantity' => 'required|integer|min:1|max:100',
            'payment_method' => 'required|in:tunai,bank_transfer_bca,bank_transfer_bri,bank_transfer_bni,bank_transfer_mandiri,bank_transfer_bsi,gopay,qris',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Get gas item
        $gas = Gas::findOrFail($validated['gas_id']);
        
        // Region validation
        if ($user->region_id != $gas->region_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa memesan gas dari BUMDes desa/wilayah lain. Silakan pilih gas dari desa Anda sendiri.'
            ], 403);
        }

        // Validate stock before proceeding
        if (!$gas->hasStock($validated['quantity'])) {
            return response()->json([
                'success' => false,
                'message' => "Mohon maaf, stok tidak mencukupi. Sisa stok: {$gas->stok}"
            ], 400);
        }

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
            'user_id' => $user->id,
            'gas_id' => $validated['gas_id'],
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
            'email' => $user->email,
            'status' => 'pending',
            'proof_of_payment' => $paymentProofPath,
        ]);

        // Catat pergerakan dana ke ledger wilayah - endpoint API ini sebelumnya
        // tidak pernah menulis ke ledger sama sekali, terpisah dari endpoint
        // User\GasBookingController yang sudah dibenahi.
        \App\Models\WalletTransaction::catatPemasukan(
            regionId: $gas->region_id,
            referenceType: 'gas',
            referenceId: $order->id,
            amount: $totalAmount,
            paymentMethod: $validated['payment_method'],
            proofPath: $paymentProofPath,
        );

        // Create transaction receipt
        $receipt = TransactionReceipt::create([
            'booking_type' => 'gas',
            'booking_id' => $order->id,
            'receipt_number' => TransactionReceipt::generateReceiptNumber('gas'),
            'user_id' => $user->id,
            'item_name' => $gas->jenis_gas,
            'quantity' => $validated['quantity'],
            'total_amount' => $totalAmount,
            'payment_method' => $validated['payment_method'],
        ]);

        // Create admin notification
        AdminNotification::create([
            'type' => 'gas_order',
            'reference_id' => $order->id,
            'region_id' => $gas->region_id,
            'title' => 'Pesanan Gas Baru (Mobile)',
            'message' => 'Pesanan ' . $gas->jenis_gas . ' dari ' . $user->name,
            'is_read' => false,
        ]);

        $response = [
            'success' => true,
            'message' => 'Pembelian gas berhasil dibuat!',
            'order_id' => $order->id,
            'receipt_id' => $receipt->id,
            'receipt_number' => $receipt->receipt_number,
        ];

        // Midtrans Integration using Core API
        if ($validated['payment_method'] !== 'tunai') {
            // Kunci milik wilayah gasnya, bukan kunci platform - lihat catatan yang
            // sama di User\GasBookingController.
            if (! \App\Support\PenyediaPembayaran::terapkanMidtransWilayah($gas->region_id)) {
                \Log::warning('Gateway API dilewati: wilayah belum siap', [
                    'order_number' => $order->order_number,
                    'region_id'    => $gas->region_id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran otomatis untuk wilayah ini sedang tidak tersedia.',
                ], 422);
            }

            $paymentMethod = $validated['payment_method'];

            // Snap, bukan Core API. Akun ini hanya punya Snap; Core API menolak
            // seluruh kanal dengan 402 "Payment channel is not activated".
            //
            // Aplikasi Flutter tidak bisa membuka popup, jadi yang dikirim
            // balik adalah redirect_url - dibuka di webview. snap_token ikut
            // disertakan untuk berjaga kalau nanti dipasang SDK Snap mobile.
            $params = [
                'transaction_details' => [
                    'order_id' => $orderNumber,
                    'gross_amount' => $totalAmount,
                ],
                'customer_details' => [
                    'first_name' => $validated['buyer_name'],
                    'email' => $user->email,
                    'phone' => $user->phone ?? '081234567890',
                ],
                'item_details' => [
                    [
                        'id' => (string) $gas->id,
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
                $response['snap_redirect_url'] = $snap->redirect_url;
            } catch (\Exception $e) {
                // TIDAK ADA nomor VA palsu. Versi sebelumnya mengisi
                // rand(10000,99999).rand(100000,999999) sebagai nomor VA dan
                // 'DUMMY_QR_CODE' sebagai QR, sehingga aplikasi mobile
                // menampilkan tagihan yang tidak pernah ada di Midtrans.
                \Illuminate\Support\Facades\Log::error('Midtrans Snap gagal (mobile)', [
                    'order_number' => $orderNumber,
                    'metode'       => $paymentMethod,
                    'pesan'        => $e->getMessage(),
                ]);

                $order->payment_channel = $paymentMethod;
                $order->save();

                $response['snap_token'] = null;
                $response['snap_redirect_url'] = null;
                $response['gateway_gagal'] = true;
                $response['message'] = 'Pesanan tersimpan, tetapi pembayaran otomatis '
                    . 'sedang tidak dapat diproses. Silakan pilih tunai atau hubungi petugas desa.';
            }
        }

        // Create notification for user
        \App\Models\Notification::create([
            'user_id' => $order->user_id,
            'type' => 'status_berubah',
            'title' => 'Menunggu Pembayaran',
            'message' => 'Pesanan gas Anda (Order ID: ' . $order->order_number . ') berhasil dibuat. Silakan selesaikan pembayaran.',
            'is_read' => false,
            'link' => '/unit-penjualan-gas',
            'icon' => 'fas fa-clock text-yellow-500'
        ]);

        if ($validated['payment_method'] !== 'tunai') {
            $response['payment_data'] = [
                'va_number' => $order->payment_va_number,
                'qr_url' => $order->payment_qr_url,
                'channel' => $order->payment_channel,
                'expiry_time' => $order->payment_expiry_time ? $order->payment_expiry_time->toDateTimeString() : null,
                'total_amount' => $totalAmount
            ];
        }

        return response()->json($response);
    }
}
