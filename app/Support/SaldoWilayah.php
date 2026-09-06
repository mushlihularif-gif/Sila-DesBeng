<?php

namespace App\Support;

use App\Models\PenarikanSaldo;
use App\Models\WalletTransaction;

/**
 * Menghitung saldo satu wilayah dari wallet_transactions.
 *
 * Saldo TIDAK disimpan sebagai satu angka di kolom manapun — sengaja dihitung
 * ulang setiap kali dari baris ledger, supaya tidak pernah ada dua angka yang
 * bisa berbeda (angka tersimpan vs jumlah baris sungguhan). Sumber kebenaran
 * tunggal: wallet_transactions.
 *
 * Sejak Midtrans dipusatkan di akun Diskominfotik, uang gateway dari semua
 * wilayah mendarat di SATU rekening. "Saldo wilayah" di sini adalah bagian
 * uang itu yang menjadi hak satu wilayah tapi belum dicairkan ke rekening
 * banknya. Transfer manual TIDAK masuk hitungan ini - uangnya sudah langsung
 * di rekening wilayah sejak awal, tidak pernah singgah di Diskominfotik.
 */
class SaldoWilayah
{
    /**
     * Saldo yang bisa dicairkan sekarang: pemasukan gateway yang sudah
     * diverifikasi (uangnya sungguh-sungguh diterima), dikurangi penarikan
     * yang sudah selesai atau sedang berjalan.
     */
    public static function tersedia(int $regionId): float
    {
        $masuk = WalletTransaction::where('region_id', $regionId)
            ->where('source', 'gateway')
            ->where('status', 'verified')
            ->sum('amount');

        $sudahDitarik = PenarikanSaldo::where('region_id', $regionId)
            ->keKominfo()
            ->whereIn('status', [PenarikanSaldo::MENUNGGU, PenarikanSaldo::DIPROSES, PenarikanSaldo::SELESAI])
            ->sum('jumlah');

        return max(0, (float) $masuk - (float) $sudahDitarik);
    }

    /**
     * Dana gateway yang belum dikonfirmasi (escrow) — dibayar warga tapi
     * pesanannya belum selesai/diterima, jadi belum boleh dicairkan.
     */
    public static function tertahan(int $regionId): float
    {
        return (float) WalletTransaction::where('region_id', $regionId)
            ->where('source', 'gateway')
            ->where('status', 'pending')
            ->sum('amount');
    }

    /**
     * Sejak pembagian hasil ke mitra dibatalkan, seluruh saldo gateway adalah
     * hak wilayah — tidak ada lagi titipan pihak lain di dalamnya. Dipertahankan
     * sebagai alias supaya pemanggil lama tidak perlu diubah serentak.
     */
    public static function hakWilayah(int $regionId): float
    {
        return self::tersedia($regionId);
    }

    /**
     * Seluruh pemasukan gateway wilayah ini, tanpa dikurangi penarikan.
     * Angka "sepanjang masa" untuk halaman Keuangan.
     */
    public static function totalPemasukan(int $regionId): float
    {
        return (float) WalletTransaction::where('region_id', $regionId)
            ->where('source', 'gateway')
            ->where('status', 'verified')
            ->sum('amount');
    }

    /**
     * Banyaknya PESANAN gateway yang lunas.
     *
     * Dihitung dari pasangan reference unik, bukan jumlah baris. Sekarang satu
     * pesanan memang selalu satu baris, tetapi perhitungan ini dipertahankan
     * karena tetap benar dan melindungi dari baris ganda apa pun sebabnya.
     */
    public static function jumlahPesananSelesai(int $regionId): int
    {
        return WalletTransaction::where('region_id', $regionId)
            ->where('source', 'gateway')
            ->where('status', 'verified')
            ->distinct()
            ->count(\Illuminate\Support\Facades\DB::raw('CONCAT(reference_type, "-", reference_id)'));
    }

    /** Ringkasan untuk halaman Keuangan Wilayah. */
    public static function ringkasan(int $regionId): array
    {
        return [
            'tersedia' => self::tersedia($regionId),
            'tertahan' => self::tertahan($regionId),
            'hak_wilayah' => self::hakWilayah($regionId),
            'sedang_ditarik' => (float) PenarikanSaldo::where('region_id', $regionId)
                ->keKominfo()
                ->berjalan()
                ->sum('jumlah'),
        ];
    }

    /**
     * Posisi dana dari sudut pandang Diskominfotik sebagai PENAMPUNG.
     *
     * Uang gateway seluruh wilayah mendarat di satu rekening milik mereka, jadi
     * angka yang paling penting bukan "pendapatan" melainkan "berapa yang masih
     * kami pegang dan itu milik siapa" — utang kustodian, bukan kas sendiri.
     *
     * @return array{dikelola:float, total_masuk:float, sudah_dicairkan:float, tertahan:float, sedang_diajukan:float}
     */
    public static function ringkasanPlatform(): array
    {
        $totalMasuk = (float) WalletTransaction::where('source', 'gateway')
            ->where('status', 'verified')
            ->sum('amount');

        $sudahDicairkan = (float) PenarikanSaldo::keKominfo()->where('status', PenarikanSaldo::SELESAI)->sum('jumlah');

        $sedangDiajukan = (float) PenarikanSaldo::keKominfo()->berjalan()->sum('jumlah');

        $tertahan = (float) WalletTransaction::where('source', 'gateway')
            ->where('status', 'pending')
            ->sum('amount');

        return [
            // Yang masih dipegang dan menjadi hak wilayah — termasuk yang sedang
            // diajukan, karena selama belum ditransfer uangnya masih di sini.
            'dikelola'        => max(0, $totalMasuk - $sudahDicairkan),
            'total_masuk'     => $totalMasuk,
            'sudah_dicairkan' => $sudahDicairkan,
            'sedang_diajukan' => $sedangDiajukan,
            'tertahan'        => $tertahan,
        ];
    }

    /**
     * Rincian dana kelolaan per wilayah — siapa memiliki berapa.
     * Hanya wilayah yang pernah punya pemasukan gateway yang ditampilkan.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function rincianPerWilayah()
    {
        return WalletTransaction::query()
            ->where('source', 'gateway')
            ->where('status', 'verified')
            ->selectRaw('region_id, SUM(amount) as total_masuk')
            ->groupBy('region_id')
            ->with('region')
            ->get()
            ->map(function ($baris) {
                $dicairkan = (float) PenarikanSaldo::where('region_id', $baris->region_id)
                    ->keKominfo()
                    ->where('status', PenarikanSaldo::SELESAI)
                    ->sum('jumlah');

                $diajukan = (float) PenarikanSaldo::where('region_id', $baris->region_id)
                    ->keKominfo()
                    ->berjalan()
                    ->sum('jumlah');

                return [
                    'region_id'  => $baris->region_id,
                    'nama'       => $baris->region->name ?? 'Wilayah #' . $baris->region_id,
                    'tipe'       => $baris->region->type ?? '-',
                    'masuk'      => (float) $baris->total_masuk,
                    'dicairkan'  => $dicairkan,
                    'diajukan'   => $diajukan,
                    'dipegang'   => max(0, (float) $baris->total_masuk - $dicairkan),
                ];
            })
            ->sortByDesc('dipegang')
            ->values();
    }
}
