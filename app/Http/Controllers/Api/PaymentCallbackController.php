<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GasOrder;
use App\Models\RentalBooking;
use App\Models\MobilBooking;
use App\Models\FasilitasUmumBooking;
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

                // Snap menerbitkan instrumen bayarnya BARU SETELAH warga memilih
                // kanal di dalam popup, jadi saat pesanan dibuat kita belum punya
                // nomor VA maupun QR untuk ditampilkan. Notifikasi 'pending' ini
                // adalah kesempatan pertama mengambilnya — sehingga halaman
                // pembayaran kita bisa menampilkan QR/VA yang sama, dan warga yang
                // terlanjur menutup popup tidak perlu mengulang dari awal.
                $this->simpanInstrumenBayar($order, $orderId);
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
            // Menyentuh SELURUH baris pesanan ini, bukan latest()->first():
            // pesanan pada unit yang dikelola mitra punya dua baris (porsi
            // wilayah + porsi mitra), dan dulu hanya satu yang ikut berubah —
            // menyisakan separuh uang berstatus salah.
            if ($order->status === 'confirmed') {
                WalletTransaction::where('reference_type', $orderType)
                    ->where('reference_id', $order->id)
                    ->where('source', 'gateway')
                    ->where('status', 'pending')
                    ->update(['status' => 'verified']);
            } elseif ($order->status === 'cancelled') {
                // Kedaluwarsa/gagal di sisi Midtrans. Kalau uangnya terlanjur
                // terbayar, dikembalikan ke dompet warga — bukan dihilangkan.
                WalletTransaction::batalkanDanRefund(
                    $orderType,
                    $order->id,
                    'Pembayaran dibatalkan/kedaluwarsa di Midtrans.'
                );
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
    /**
     * Ambil nomor VA / URL QR dari Midtrans lalu simpan ke pesanan.
     *
     * Dipanggil saat notifikasi 'pending' datang. Kegagalannya sengaja tidak
     * dilempar: callback WAJIB tetap menjawab 200, kalau tidak Midtrans akan
     * mengirim ulang notifikasi yang sama berkali-kali.
     */
    private function simpanInstrumenBayar($order, string $orderId): void
    {
        try {
            $detail = \Midtrans\Transaction::status($orderId);
            $atribut = $order->getAttributes();
            $berubah = false;

            if (array_key_exists('payment_va_number', $atribut)) {
                if (isset($detail->va_numbers[0]->va_number)) {
                    $order->payment_va_number = $detail->va_numbers[0]->va_number;
                    $berubah = true;
                } elseif (isset($detail->biller_code, $detail->bill_key)) {
                    $order->payment_va_number = $detail->biller_code . '-' . $detail->bill_key;
                    $berubah = true;
                }
            }

            if (array_key_exists('payment_qr_url', $atribut) && isset($detail->actions)) {
                foreach ($detail->actions as $aksi) {
                    if (($aksi->name ?? null) === 'generate-qr-code') {
                        $order->payment_qr_url = $aksi->url;
                        $berubah = true;
                        break;
                    }
                }
            }

            // QRIS: respons /status tidak memuat actions maupun qr_string —
            // hanya transaction_id. Gambar QR-nya disajikan Midtrans di alamat
            // tetap berikut, yang juga persis isi actions[].url pada respons
            // charge. Jadi alamatnya disusun sendiri dari transaction_id.
            if (! $order->payment_qr_url
                && isset($detail->transaction_id)
                && in_array($detail->payment_type ?? '', ['qris', 'gopay'], true)) {
                $order->payment_qr_url = \Midtrans\Config::getBaseUrl()
                    . '/v2/qris/' . $detail->transaction_id . '/qr-code';
                $berubah = true;
            }

            if ($berubah) {
                $order->save();
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengambil instrumen bayar dari Midtrans', [
                'order_id' => $orderId,
                'pesan'    => $e->getMessage(),
            ]);
        }
    }

    private function kenaliPesanan(string $orderId): array
    {
        if (str_starts_with($orderId, 'GAS-')) {
            $order = GasOrder::with('gas')->where('order_number', $orderId)->first();

            if (! $order && preg_match('/^(.*)-\d{10,}$/', $orderId, $cocok)) {
                $order = GasOrder::with('gas')->where('order_number', $cocok[1])->first();
            }

            return [$order, 'gas', $order?->gas?->region_id];
        }

        if (str_starts_with($orderId, 'PSR-')) {
            $order = PasarOrder::where('order_number', $orderId)->first();
            return [$order, 'pasar', $order?->region_id];
        }

        if (str_starts_with($orderId, 'MB-')) {
            $order = MobilBooking::where('order_number', $orderId)->first();
            // Suffix suffix waktu (MB-XX-ABCDE-1787762109) — lepas suffix dulu
            if (! $order && preg_match('/^(.*)-\d{10,}$/', $orderId, $cocok)) {
                $order = MobilBooking::where('order_number', $cocok[1])->first();
            }
            return [$order, 'mobil', $order?->region_id];
        }

        if (str_starts_with($orderId, 'FU-')) {
            $order = FasilitasUmumBooking::where('order_number', $orderId)->first();
            if (! $order && preg_match('/^(.*)-\d{10,}$/', $orderId, $cocok)) {
                $order = FasilitasUmumBooking::where('order_number', $cocok[1])->first();
            }
            return [$order, 'fasilitas_umum', $order?->region_id];
        }

        if (str_starts_with($orderId, 'RNT-')) {
            $order = RentalBooking::with('barang')->where('order_number', $orderId)->first();
            if (! $order && preg_match('/^(.*)-\d{10,}$/', $orderId, $cocok)) {
                $order = RentalBooking::with('barang')->where('order_number', $cocok[1])->first();
            }
            return [$order, 'rental', $order?->barang?->region_id];
        }

        // Fallback: order lama tanpa awalan (sebelum penambahan prefix RNT-)
        $order = RentalBooking::with('barang')->where('order_number', $orderId)->first();
        return [$order, 'rental', $order?->barang?->region_id];
    }
}