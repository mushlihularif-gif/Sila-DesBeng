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
            'buyer_name' => 'required|string|max:255',
            'buyer_address' => 'required|string',
            'quantity' => 'required|integer|min:1|max:100',
            'payment_method' => 'required|in:tunai',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Get gas item
        $gas = Gas::findOrFail($validated['gas_id']);
        
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
            'payment_method' => ucfirst($validated['payment_method']),
            'address' => $validated['buyer_address'],
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
            'item_name' => $gas->jenis_gas,
            'quantity' => $validated['quantity'],
            'total_amount' => $totalAmount,
            'payment_method' => $validated['payment_method'],
        ]);

        // Create admin notification
        AdminNotification::create([
            'type' => 'gas_order',
            'reference_id' => $order->id,
            'title' => 'Pesanan Gas Baru',
            'message' => 'Pesanan ' . $gas->jenis_gas . ' dari ' . Auth::user()->name,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pembelian gas berhasil dibuat!',
            'order_id' => $order->id,
            'receipt_id' => $receipt->id,
            'receipt_number' => $receipt->receipt_number,
        ]);
    }
}
