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
            'quantity' => 'required|integer|min:1|max:100',
            // 'transfer' dan 'ewallet' adalah pembayaran manual ke rekening/dompet
            // wilayah. 'transfer' sempat tidak ada di daftar ini padahal tombolnya
            // dirender dan sisa kode di bawah sudah menanganinya, jadi setiap warga
            // yang memilih Transfer Bank ditolak validasi tanpa penjelasan.
            'payment_method' => 'required|in:tunai,transfer,ewallet',
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
        // langsung tercatat "masuk" karena pembayarannya dilakukan manual.
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
        abort_unless(app()->environment(['local', 'testing']), 404);

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

}
