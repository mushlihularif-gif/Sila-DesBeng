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
                abort_if($ledger->isEmpty() || $ledger->contains(fn ($entry) => $entry->status !== 'pending'), 409,
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

        if (!$request->expectsJson() && !$request->is('api/*')) return back()->with('success', 'Bukti pembayaran dikirim. Petugas akan memeriksanya.');
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
        return response()->json(app(\App\Services\GasOrdering::class)->store($request));
    }
}
