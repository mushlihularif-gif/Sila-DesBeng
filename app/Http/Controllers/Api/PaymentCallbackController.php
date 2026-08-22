<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GasOrder;
use App\Models\RentalBooking;
use App\Models\TransactionReceipt;
use App\Models\WalletTransaction;
use App\Models\SystemSetting;
use Midtrans\Notification;

class PaymentCallbackController extends Controller
{
    public function __construct()
    {
        SystemSetting::applyMidtransConfig();
    }

    public function handleNotification(Request $request)
    {
        try {
            $notification = new Notification();
            
            $transaction = $notification->transaction_status;
            $type = $notification->payment_type;
            $orderId = $notification->order_id;
            $fraud = $notification->fraud_status;

            // Resolve Model
            // Since we have multiple booking types, we check by prefix or just query both
            $order = null;
            $orderType = null;

            if (strpos($orderId, 'GAS-') === 0) {
                $order = GasOrder::where('order_number', $orderId)->first();
                $orderType = 'gas';
            } elseif (strpos($orderId, 'RNTL-') === 0) {
                $order = RentalBooking::where('booking_number', $orderId)->first();
                $orderType = 'rental';
            }

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            if ($transaction == 'capture') {
                if ($type == 'credit_card') {
                    if ($fraud == 'challenge') {
                        $order->status = 'pending';
                    } else {
                        $order->status = 'confirmed';
                    }
                }
            } else if ($transaction == 'settlement') {
                $order->status = 'confirmed';
            } else if ($transaction == 'pending') {
                $order->status = 'pending';
            } else if ($transaction == 'deny') {
                $order->status = 'cancelled';
            } else if ($transaction == 'expire') {
                $order->status = 'cancelled';
            } else if ($transaction == 'cancel') {
                $order->status = 'cancelled';
            }

            // Optional: update receipt status if needed, but we don't track status in receipt directly
            // Update confirmed_at if confirmed
            if ($order->status == 'confirmed' && !$order->confirmed_at) {
                $order->confirmed_at = now();
            }

            $order->save();

            // Sinkronkan status dana tertahan di ledger dengan hasil notifikasi Midtrans.
            // Dana tetap berstatus "ditahan" (belum dicairkan ke BUM Desa) sampai
            // pesanan dikonfirmasi selesai/diterima - webhook ini hanya memastikan
            // uangnya benar-benar sudah dibayar (verified) atau batal (rejected).
            $walletTx = WalletTransaction::where('reference_type', $orderType)
                ->where('reference_id', $order->id)
                ->where('source', 'gateway')
                ->latest()
                ->first();

            if ($walletTx) {
                if (in_array($order->status, ['confirmed'])) {
                    $walletTx->status = 'verified';
                } elseif (in_array($order->status, ['cancelled'])) {
                    $walletTx->status = 'rejected';
                }
                $walletTx->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Notification processed successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Midtrans Callback Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
