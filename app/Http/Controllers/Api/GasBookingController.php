<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gas;
use App\Models\GasOrder;
use App\Models\TransactionReceipt;
use App\Models\AdminNotification;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class GasBookingController extends Controller
{
    public function uploadPayment(Request $request, int $id)
    {
        abort_unless($request->user(), 401);
        // Check ownership before validating or storing an uploaded file.
        GasOrder::where('user_id', $request->user()->id)->findOrFail($id);
        $request->validate(['payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120']);
        $storedPath = null;

        try {
            DB::transaction(function () use ($request, $id, &$storedPath) {
                $order = GasOrder::where('user_id', $request->user()->id)->lockForUpdate()->findOrFail($id);
                abort_unless($order->status === 'pending' && $order->cancellation_status !== 'pending', 409,
                    'Bukti hanya dapat dikirim untuk pesanan yang masih menunggu pembayaran.');
                abort_unless(in_array(strtolower(trim($order->payment_method)), ['transfer', 'transfer_manual', 'transfer manual', 'bank_transfer', 'ewallet'], true), 422,
                    'Pesanan ini tidak menggunakan pembayaran transfer atau e-wallet manual.');
                abort_if($order->proof_of_payment, 409, 'Bukti sudah dikirim dan sedang menunggu pemeriksaan petugas.');
                $ledger = \App\Models\WalletTransaction::where('reference_type', 'gas')
                    ->where('reference_id', $order->id)->lockForUpdate()->get();
                abort_if($ledger->contains(fn ($entry) => $entry->status !== 'pending'), 409,
                    'Pembayaran sudah diproses petugas. Bukti tidak dapat diganti.');

                $storedPath = $request->file('payment_proof')->store('payment_proofs/gas', 'public');
                abort_unless($storedPath, 503, 'Bukti pembayaran belum berhasil disimpan.');
                $order->update(['proof_of_payment' => $storedPath]);
                // Uploading evidence never verifies payment or changes the amount/status.
                \App\Models\WalletTransaction::whereKey($ledger->modelKeys())->update(['proof_path' => $storedPath]);
                AdminNotification::create([
                    'type' => 'gas_order', 'reference_id' => $order->id, 'region_id' => $order->gas?->region_id,
                    'title' => 'Bukti Pembayaran Gas Dikirim',
                    'message' => 'Bukti pembayaran pesanan '.$order->order_number.' perlu diperiksa.', 'is_read' => false,
                ]);
            });
        } catch (\Throwable $e) {
            if ($storedPath) Storage::disk('public')->delete($storedPath);
            throw $e;
        }

        return response()->json(['success' => true, 'message' => 'Bukti pembayaran dikirim. Petugas akan memeriksanya.', 'order_id' => $id])
            ->header('Cache-Control', 'private, no-store');
    }

    public function getReceipt(Request $request, int $id)
    {
        abort_unless($request->user(), 401);
        $order = GasOrder::withTrashed()->where('user_id', $request->user()->id)->findOrFail($id);
        $receipt = TransactionReceipt::where('booking_type', 'gas')->where('booking_id', $order->id)
            ->where('user_id', $request->user()->id)->first();

        return response()->json(['success' => true, 'data' => [
            'order_id' => $order->id, 'order_number' => $order->order_number, 'status' => $order->status,
            'receipt_number' => $receipt?->receipt_number, 'item_name' => $order->item_name,
            'quantity' => $order->quantity, 'total_amount' => $receipt?->total_amount ?? (string) ($order->price * $order->quantity),
            'payment_method' => $order->payment_method,
            'receipt_url' => route('receipt.gas.view', $order->id),
        ]])->header('Cache-Control', 'private, no-store');
    }

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
            'payment_method' => 'required|in:tunai,transfer',
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

        // Create notification for user
        \App\Models\Notification::create([
            'user_id' => $order->user_id,
            'type' => 'status_berubah',
            'title' => 'Menunggu Pembayaran',
            'message' => 'Pesanan gas Anda (Order ID: ' . $order->order_number . ') berhasil dibuat. Silakan selesaikan pembayaran.',
            'is_read' => false,
            'link' => \App\Services\NotificationService::orderLink($order, 'gas'),
            'icon' => 'fas fa-clock text-yellow-500'
        ]);

        return response()->json($response);
    }
}
