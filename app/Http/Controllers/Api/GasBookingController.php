<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gas;
use App\Models\GasOrder;
use App\Models\TransactionReceipt;
use App\Models\AdminNotification;
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
            'quantity' => 'required|integer|min:1|max:100',
            'payment_method' => 'required|in:tunai,bank_transfer_bca,bank_transfer_bri,bank_transfer_bni,bank_transfer_mandiri,gopay,qris',
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
            'full_name' => $validated['buyer_name'],
            'email' => $user->email,
            'status' => 'pending',
            'proof_of_payment' => $paymentProofPath,
        ]);

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
            \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
            \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $paymentMethod = $validated['payment_method'];
            $paymentType = '';
            $bank = '';
            
            if (str_starts_with($paymentMethod, 'bank_transfer_')) {
                $bank = str_replace('bank_transfer_', '', $paymentMethod);
                if ($bank === 'mandiri') {
                    $paymentType = 'echannel';
                } else {
                    $paymentType = 'bank_transfer';
                }
            } else if ($paymentMethod === 'gopay') {
                $paymentType = 'gopay';
            } else if ($paymentMethod === 'qris') {
                $paymentType = 'qris';
            }

            $params = [
                'payment_type' => $paymentType,
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
                        'name' => $gas->jenis_gas
                    ]
                ]
            ];

            if ($paymentType === 'bank_transfer') {
                $params['bank_transfer'] = [
                    'bank' => $bank
                ];
            } else if ($paymentType === 'echannel') {
                $params['echannel'] = [
                    'bill_info1' => 'Pembayaran:',
                    'bill_info2' => 'Gas ' . $gas->jenis_gas
                ];
            }

            try {
                $coreResponse = \Midtrans\CoreApi::charge($params);
                
                $order->payment_channel = $paymentMethod;
                $order->payment_expiry_time = now()->addDay();
                
                if (isset($coreResponse->va_numbers[0]->va_number)) {
                    $order->payment_va_number = $coreResponse->va_numbers[0]->va_number;
                } else if (isset($coreResponse->biller_code) && isset($coreResponse->bill_key)) {
                    $order->payment_va_number = $coreResponse->biller_code . '-' . $coreResponse->bill_key;
                } else if (isset($coreResponse->actions)) {
                    foreach ($coreResponse->actions as $action) {
                        if ($action->name === 'generate-qr-code') {
                            $order->payment_qr_url = $action->url;
                        }
                    }
                }
                
                $order->save();
            } catch (\Exception $e) {
                \Log::warning('Midtrans Error (Mobile): ' . $e->getMessage());
                
                // MOCK RESPONSE
                $order->payment_channel = $paymentMethod;
                $order->payment_expiry_time = now()->addDay();
                
                if ($paymentType === 'bank_transfer' || $paymentType === 'echannel') {
                    $order->payment_va_number = rand(10000, 99999) . rand(100000, 999999);
                } else if ($paymentType === 'qris' || $paymentType === 'gopay') {
                    $order->payment_qr_url = 'DUMMY_QR_CODE';
                }
                
                $order->save();
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
