<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentVerificationGuard
{
    /**
     * Parse input angka Indonesia/internasional (misal "200.000", "Rp 20.000", "200000", "200000.50").
     */
    public static function parseAmount(mixed $raw): ?float
    {
        if ($raw === null) {
            return null;
        }

        if (is_int($raw) || is_float($raw)) {
            return round((float) $raw, 2);
        }

        $text = trim((string) $raw);
        if ($text === '') {
            return null;
        }

        // Hapus prefix Rp / IDR dan spasi
        $clean = preg_replace('/^(?:rp\.?|idr)\s*/i', '', $text);
        $clean = preg_replace('/\s+/', '', (string) $clean);

        // Hanya izinkan digit, titik, koma, tanda minus
        if (! preg_match('/^-?[0-9.,]+$/', (string) $clean)) {
            return null;
        }

        // Format Indonesia: "200.000" atau "200.000,50"
        if (str_contains($clean, '.') && str_contains($clean, ',')) {
            if (strrpos($clean, ',') > strrpos($clean, '.')) {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } else {
                $clean = str_replace(',', '', $clean);
            }
        } elseif (str_contains($clean, '.')) {
            // Jika bagian setelah titik terakhir 3 digit (misal 200.000 atau 1.250.000), itu pemisah ribuan!
            if (preg_match('/^\d{1,3}(\.\d{3})+$/', $clean)) {
                $clean = str_replace('.', '', $clean);
            }
        } elseif (str_contains($clean, ',')) {
            if (preg_match('/^\d{1,3}(,\d{3})+$/', $clean)) {
                $clean = str_replace(',', '', $clean);
            } else {
                $clean = str_replace(',', '.', $clean);
            }
        }

        return is_numeric($clean) ? round((float) $clean, 2) : null;
    }

    /**
     * Pastikan nominal yang diketik petugas saat klik "Konfirmasi Uang Diterima"
     * benar-benar cocok dengan subtotal tagihan (mencegah pengesahan transfer kurang bayar).
     *
     * Catatan kompatibilitas:
     * - Di antarmuka Admin/Staf, input `confirmed_amount` bersifat WAJIB diisi.
     * - Jika panggilan lama/otomatis tidak mengirimkan `confirmed_amount`, tetap mengembalikan
     *   `$expectedSubtotal` agar alur lama tetap kompatibel.
     * - Begitu `confirmed_amount` dikirimkan dan TIDAK sesuai dengan subtotal tagihan (misal
     *   transfer Rp 20.000 untuk tagihan Rp 200.000), server PASTI menolak dengan HTTP 422.
     */
    public static function validateConfirmedAmount(
        Request $request,
        float $expectedSubtotal,
        float $alreadyPaid = 0.0
    ): float {
        $raw = $request->input('confirmed_amount');
        if ($raw === null || trim((string) $raw) === '') {
            return round($expectedSubtotal, 2);
        }

        $parsed = self::parseAmount($raw);
        if ($parsed === null || $parsed < 0) {
            throw ValidationException::withMessages([
                'confirmed_amount' => 'Masukkan nominal uang diterima dalam angka yang valid.',
            ]);
        }

        $mode = strtolower((string) $request->input('amount_mode', 'total'));
        $totalReceived = $mode === 'delta'
            ? round($alreadyPaid + $parsed, 2)
            : round($parsed, 2);

        $expected = round($expectedSubtotal, 2);

        if ($totalReceived < $expected - 0.5) {
            $shortfall = max(0, $expected - $totalReceived);
            throw ValidationException::withMessages([
                'confirmed_amount' => sprintf(
                    'Nominal yang diterima (Rp %s) kurang Rp %s dari subtotal tagihan (Rp %s). Gunakan aksi "Catat Kurang Bayar & Minta Pelunasan" atau "Tolak Bukti" agar pendapatan desa tidak salah tercatat.',
                    number_format($totalReceived, 0, ',', '.'),
                    number_format($shortfall, 0, ',', '.'),
                    number_format($expected, 0, ',', '.')
                ),
            ]);
        }

        if ($totalReceived > $expected + 0.5) {
            $excess = max(0, $totalReceived - $expected);
            throw ValidationException::withMessages([
                'confirmed_amount' => sprintf(
                    'Nominal yang Anda ketik (Rp %s) melebihi subtotal tagihan (Rp %s) sebesar Rp %s. Periksa kembali angka atau selesaikan kelebihan transfer dengan warga.',
                    number_format($totalReceived, 0, ',', '.'),
                    number_format($expected, 0, ',', '.'),
                    number_format($excess, 0, ',', '.')
                ),
            ]);
        }

        return $expected;
    }

    /**
     * Validasi nominal diterima parsial (kurang bayar) saat menolak bukti transfer untuk minta pelunasan.
     */
    public static function parsePartialReceivedAmount(Request $request, float $expectedSubtotal): ?float
    {
        $raw = $request->input('received_amount');
        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        $parsed = self::parseAmount($raw);
        if ($parsed === null || $parsed < 0) {
            throw ValidationException::withMessages([
                'received_amount' => 'Nominal yang sudah diterima harus berupa angka 0 atau lebih.',
            ]);
        }

        if ($parsed >= round($expectedSubtotal, 2) - 0.5) {
            throw ValidationException::withMessages([
                'received_amount' => sprintf(
                    'Nominal diterima (Rp %s) sudah memenuhi subtotal (Rp %s). Gunakan "Konfirmasi Uang Diterima" untuk melunasi pesanan.',
                    number_format($parsed, 0, ',', '.'),
                    number_format($expectedSubtotal, 0, ',', '.')
                ),
            ]);
        }

        return round($parsed, 2);
    }
}
