<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Satu-satunya tempat yang tahu cara mengambil instrumen bayar dari Midtrans.
 *
 * Sebelumnya logika ini tersebar di tiga tempat — halaman warga, callback
 * notifikasi, dan API mobile — dan itu justru pola yang berulang kali menjadi
 * sumber bug di proyek ini: satu tempat diperbaiki, dua lainnya tertinggal.
 *
 * Yang membuatnya tidak sesederhana "baca satu field":
 *
 *  - Untuk Virtual Account, respons /status memuat `va_numbers`.
 *  - Untuk Mandiri Bill Payment, yang ada `biller_code` + `bill_key`.
 *  - Untuk QRIS/GoPay, respons /status TIDAK memuat QR sama sekali — tidak ada
 *    `actions` maupun `qr_string`, hanya `transaction_id`. Gambarnya disajikan
 *    Midtrans di alamat tetap /v2/qris/{transaction_id}/qr-code, dan itu pula
 *    isi actions[].url pada respons charge. Jadi alamatnya disusun sendiri.
 */
class InstrumenPembayaran
{
    /**
     * Ambil instrumen bayar dari Midtrans lalu simpan ke pesanan.
     *
     * Pemanggil WAJIB sudah menjalankan PenyediaPembayaran::terapkanMidtransWilayah()
     * lebih dulu — tanpa itu SDK memakai kunci sisa milik wilayah lain.
     *
     * @return bool true kalau ada yang berubah dan tersimpan.
     */
    public static function sinkronkan($order, string $orderId): bool
    {
        try {
            $detail = \Midtrans\Transaction::status($orderId);
        } catch (\Throwable $e) {
            // Wajar selama warga belum memilih kanal di popup Snap: bagi
            // Midtrans transaksinya memang belum ada.
            Log::info('Sinkron instrumen: transaksi belum ada di Midtrans', [
                'order_id' => $orderId,
                'pesan'    => $e->getMessage(),
            ]);

            return false;
        }

        $atribut = $order->getAttributes();
        $berubah = false;

        if (array_key_exists('payment_va_number', $atribut) && ! $order->payment_va_number) {
            if (isset($detail->va_numbers[0]->va_number)) {
                $order->payment_va_number = $detail->va_numbers[0]->va_number;
                $berubah = true;
            } elseif (isset($detail->biller_code, $detail->bill_key)) {
                $order->payment_va_number = $detail->biller_code . '-' . $detail->bill_key;
                $berubah = true;
            }
        }

        if (array_key_exists('payment_qr_url', $atribut) && ! $order->payment_qr_url) {
            // Jalur normal: sebagian kanal memang menyertakan actions.
            if (isset($detail->actions)) {
                foreach ($detail->actions as $aksi) {
                    if (($aksi->name ?? null) === 'generate-qr-code') {
                        $order->payment_qr_url = $aksi->url;
                        $berubah = true;
                        break;
                    }
                }
            }

            // QRIS/GoPay: disusun sendiri dari transaction_id.
            if (! $order->payment_qr_url
                && isset($detail->transaction_id)
                && in_array($detail->payment_type ?? '', ['qris', 'gopay'], true)) {
                $order->payment_qr_url = \Midtrans\Config::getBaseUrl()
                    . '/v2/qris/' . $detail->transaction_id . '/qr-code';
                $berubah = true;
            }
        }

        if ($berubah) {
            $order->save();

            return true;
        }

        Log::info('Sinkron instrumen: tidak ada yang bisa diambil', [
            'order_id' => $orderId,
            'jawaban'  => json_encode($detail),
        ]);

        return false;
    }

    /**
     * Ambilkan gambar QR dari Midtrans.
     *
     * Alamat QR-nya menuntut autentikasi Basic dengan SERVER KEY — kunci yang
     * tidak boleh menyentuh browser maupun aplikasi warga. Jadi server yang
     * mengambilnya, lalu meneruskan gambarnya.
     *
     * @return array{isi:string, tipe:string}|null null kalau gagal.
     */
    public static function ambilGambarQr(string $alamat): ?array
    {
        try {
            $jawab = \Illuminate\Support\Facades\Http::withBasicAuth(
                \Midtrans\Config::$serverKey,
                ''
            )->timeout(10)->get($alamat);

            if (! $jawab->successful()) {
                Log::warning('Gagal mengambil gambar QRIS', [
                    'alamat' => $alamat,
                    'kode'   => $jawab->status(),
                ]);

                return null;
            }

            return [
                'isi'  => $jawab->body(),
                'tipe' => $jawab->header('Content-Type') ?: 'image/png',
            ];
        } catch (\Throwable $e) {
            Log::warning('Gagal mengambil gambar QRIS', [
                'alamat' => $alamat,
                'pesan'  => $e->getMessage(),
            ]);

            return null;
        }
    }
}
