<?php

namespace App\Support;

use App\Models\SaldoWarga;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Log;

/**
 * Dompet warga: menghitung saldonya, dan mengembalikan uang saat pesanan yang
 * sudah dibayar batal.
 *
 * Aturan pemicunya satu dan berlaku di mana pun: begitu baris ledger gateway
 * yang SUDAH TERVERIFIKASI (artinya uangnya benar-benar diterima) ditandai
 * batal, uang itu kembali menjadi hak warga. Ditempatkan di satu tempat
 * seperti ini supaya tiap jalur pembatalan — ditolak admin, kedaluwarsa,
 * gagal dikirim — otomatis ikut, tanpa perlu diingat satu per satu.
 *
 * Baris yang masih 'pending' TIDAK direfund: itu tagihan yang terbit tapi
 * belum dibayar, jadi tidak ada uang yang perlu dikembalikan.
 */
class DompetWarga
{
    /** Saldo yang bisa dipakai belanja atau dicairkan sekarang. */
    public static function saldo(int $userId): float
    {
        $masuk = (float) SaldoWarga::where('user_id', $userId)
            ->where('type', SaldoWarga::REFUND)
            ->where('status', SaldoWarga::SELESAI)
            ->sum('amount');

        $belanja = (float) SaldoWarga::where('user_id', $userId)
            ->where('type', SaldoWarga::BELANJA)
            ->sum('amount');

        // Penarikan yang masih berjalan tetap memotong saldo — uangnya sudah
        // "dipesan" untuk dicairkan, tidak boleh dibelanjakan lagi.
        $ditarik = (float) SaldoWarga::where('user_id', $userId)
            ->where('type', SaldoWarga::PENARIKAN)
            ->whereIn('status', [SaldoWarga::MENUNGGU, SaldoWarga::DIPROSES, SaldoWarga::SELESAI])
            ->sum('amount');

        return max(0, $masuk - $belanja - $ditarik);
    }

    /**
     * Sebaran saldo warga: di mana saja uangnya berada sekarang.
     *
     * Sejajar dengan SaldoWilayah::ringkasan() di sisi wilayah. Tanpa rincian
     * ini, warga yang saldonya berkurang karena sedang diajukan penarikan akan
     * mengira uangnya hilang — angka "tersedia" saja tidak menjelaskan ke mana
     * sisanya pergi.
     *
     * @return array{tersedia:float, diajukan:float, diproses:float,
     *               total_masuk:float, sudah_cair:float, terpakai:float}
     */
    public static function ringkasan(int $userId): array
    {
        $jumlahPenarikan = fn (string $status) => (float) SaldoWarga::where('user_id', $userId)
            ->where('type', SaldoWarga::PENARIKAN)
            ->where('status', $status)
            ->sum('amount');

        return [
            'tersedia'    => self::saldo($userId),
            'diajukan'    => $jumlahPenarikan(SaldoWarga::MENUNGGU),
            'diproses'    => $jumlahPenarikan(SaldoWarga::DIPROSES),
            'sudah_cair'  => $jumlahPenarikan(SaldoWarga::SELESAI),
            'total_masuk' => self::totalRefund($userId),
            'terpakai'    => (float) SaldoWarga::where('user_id', $userId)
                ->where('type', SaldoWarga::BELANJA)
                ->sum('amount'),
        ];
    }

    /** Total yang pernah dikembalikan ke warga ini, sepanjang masa. */
    public static function totalRefund(int $userId): float
    {
        return (float) SaldoWarga::where('user_id', $userId)
            ->where('type', SaldoWarga::REFUND)
            ->where('status', SaldoWarga::SELESAI)
            ->sum('amount');
    }

    /**
     * Kembalikan uang pesanan yang batal ke dompet warganya.
     *
     * Nominalnya diambil dari JUMLAH SELURUH baris ledger gateway terverifikasi
     * milik pesanan itu — penting karena pesanan berbagi hasil punya dua baris
     * (porsi wilayah + porsi mitra), sedangkan warga membayar totalnya.
     *
     * Aman dipanggil berulang: kalau refund untuk pesanan yang sama sudah ada,
     * tidak dibuat dua kali.
     *
     * @return SaldoWarga|null baris refund yang dibuat, atau null bila tidak ada
     *                         uang yang perlu dikembalikan
     */
    public static function kembalikanUntukPesanan(
        string $referenceType,
        int $referenceId,
        ?int $userId,
        ?int $regionId,
        string $alasan,
    ): ?SaldoWarga {
        if (! $userId) {
            Log::warning('Refund dilewati: pemilik pesanan tidak diketahui', [
                'reference' => $referenceType . '#' . $referenceId,
            ]);
            return null;
        }

        $sudahAda = SaldoWarga::where('type', SaldoWarga::REFUND)
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->exists();

        if ($sudahAda) {
            return null;
        }

        // Hanya yang 'verified': itu uang yang benar-benar sudah diterima.
        $nominal = (float) WalletTransaction::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('source', 'gateway')
            ->where('status', 'verified')
            ->sum('amount');

        if ($nominal <= 0) {
            return null;
        }

        $refund = SaldoWarga::create([
            'user_id' => $userId,
            'region_id' => $regionId,
            'type' => SaldoWarga::REFUND,
            'amount' => $nominal,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'status' => SaldoWarga::SELESAI,
            'catatan' => $alasan,
        ]);

        Log::info('Refund masuk ke dompet warga', [
            'saldo_warga_id' => $refund->id,
            'user_id' => $userId,
            'reference' => $referenceType . '#' . $referenceId,
            'jumlah' => $nominal,
            'alasan' => $alasan,
        ]);

        return $refund;
    }
}
