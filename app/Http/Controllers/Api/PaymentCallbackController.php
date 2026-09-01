<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GasOrder;
use App\Models\RentalBooking;
use App\Models\PasarOrder;
use App\Models\TransactionReceipt;
use App\Models\WalletTransaction;
use Midtrans\Notification;
use App\Support\PenyediaPembayaran;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function handleNotification(Request $request)
    {
        try {
            // order_id dibaca dari badan permintaan lebih dulu, TANPA kunci apa pun.
            // Urutan ini wajib: tiap wilayah punya akun Midtrans sendiri, dan
            // Notification tidak memverifikasi tanda tangan secara lokal - ia
            // menanyakan ulang status pesanan ke API Midtrans memakai
            // Midtrans\Config::$serverKey. Kalau kunci platform yang terpasang,
            // pertanyaan itu dikirim ke akun yang tidak memiliki pesanan ini dan
            // callback dari semua wilayah gagal diproses.
            $orderId = (string) $request->input('order_id');

            [$order, $orderType, $regionId] = $this->kenaliPesanan($orderId);

            if (! $order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            if (! PenyediaPembayaran::terapkanMidtransWilayah($regionId)) {
                // Jangan diteruskan memakai kunci sisa milik wilayah lain:
                // statusnya akan salah baca. 503 supaya Midtrans mencoba lagi
                // setelah kunci wilayahnya dibetulkan.
                Log::warning('Callback Midtrans ditolak: kunci wilayah belum siap', [
                    'order_id'  => $orderId,
                    'jenis'     => $orderType,
                    'region_id' => $regionId,
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Kredensial wilayah untuk pesanan ini belum siap.',
                ], 503);
            }

            $notification = new Notification();

            $transaction = $notification->transaction_status;
            $type = $notification->payment_type;
            $fraud = $notification->fraud_status;

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
    /**
     * Kenali pesanan dari order_id, sekalian wilayah pemiliknya.
     *
     * Wilayah inilah yang menentukan kunci Midtrans mana yang dipakai untuk
     * memverifikasi callback ini.
     *
     * @return array{0: mixed, 1: string, 2: int|null} [pesanan, jenis, region_id]
     */
    private function kenaliPesanan(string $orderId): array
    {
        if (str_starts_with($orderId, 'GAS-')) {
            $order = GasOrder::with('gas')->where('order_number', $orderId)->first();

            // Ganti metode bayar menerbitkan ulang tagihan dengan order_id
            // bersuffix waktu (GAS-XX-123-1787762109) supaya Midtrans menganggapnya
            // transaksi baru. Nomor itu tidak ada di tabel, jadi suffix-nya
            // dilepas dulu - tanpa ini pesanan yang metodenya pernah diganti
            // tidak akan pernah terkonfirmasi.
            if (! $order && preg_match('/^(.*)-\d{10,}$/', $orderId, $cocok)) {
                $order = GasOrder::with('gas')->where('order_number', $cocok[1])->first();
            }

            return [$order, 'gas', $order?->gas?->region_id];
        }
        if (str_starts_with($orderId, 'PSR-')) {
            // Pasar Daerah sebelumnya tidak dikenali di sini sama sekali, jadi
            // setiap callback-nya dijawab 404 dan pesanannya tidak pernah
            // terkonfirmasi meski warganya sudah membayar.
            $order = PasarOrder::where('order_number', $orderId)->first();

            return [$order, 'pasar', $order?->region_id];
        }

        // Penyewaan alat memakai nomor tanpa awalan. Cabang lama mencari kolom
        // 'booking_number' yang tidak ada di tabelnya.
        $order = RentalBooking::with('barang')->where('order_number', $orderId)->first();

        return [$order, 'rental', $order?->barang?->region_id];
    }
}