<?php

namespace App\Support;

use App\Models\Region;
use App\Models\SystemSetting;
use App\Services\XenditService;

/**
 * Satu-satunya tempat yang menjawab: penyedia pembayaran mana yang aktif,
 * dan apakah sebuah wilayah sudah siap menerima pembayaran otomatis.
 *
 * Sebelum kelas ini ada, `system_settings.gateway_provider` hanya tersimpan
 * tanpa pernah dibaca siapa pun — dropdown Midtrans/Xendit/OY! di panel Super
 * Admin tidak memengaruhi apa pun, dan sistem selalu memakai Midtrans karena
 * itu yang ditulis keras di controller.
 *
 * Perbedaan mendasar antara kedua penyedia menentukan di mana kuncinya disimpan:
 *
 *  MIDTRANS — satu akun merchant milik Diskominfotik untuk seluruh wilayah.
 *             Satu akun Midtrans memang hanya bisa punya satu rekening
 *             pencairan, dan itulah rekening Diskominfotik. Uang warga dari
 *             semua desa mendarat di sana, lalu dibukukan sebagai SALDO tiap
 *             wilayah di wallet_transactions. Wilayah mencairkannya lewat
 *             pengajuan penarikan yang disetujui Diskominfotik.
 *
 *             Transfer manual TIDAK lewat jalur ini: uangnya langsung masuk
 *             ke rekening wilayah, jadi tidak menambah saldo yang perlu
 *             dicairkan.
 *
 *  XENDIT   — kredensial induk milik Diskominfotik melayani semua wilayah lewat
 *             sub-akun. Kunci disimpan sekali di panel Super Admin; yang
 *             disimpan per wilayah hanya ID sub-akunnya.
 */
class PenyediaPembayaran
{
    public const MIDTRANS = 'midtrans';
    public const XENDIT   = 'xendit';

    /** Penyedia yang dinyalakan Super Admin. Midtrans dipakai kalau belum dipilih. */
    public static function aktif(): string
    {
        $pilihan = SystemSetting::query()->value('gateway_provider');

        return in_array($pilihan, [self::MIDTRANS, self::XENDIT], true)
            ? $pilihan
            : self::MIDTRANS;
    }

    public static function label(): string
    {
        return match (self::aktif()) {
            self::XENDIT => 'Xendit',
            default      => 'Midtrans',
        };
    }

    /**
     * Kunci untuk penyedia aktif diisi di mana?
     * Menentukan bentuk halaman Pembayaran Wilayah.
     */
    public static function kunciDiisiOlehWilayah(): bool
    {
        // Sejak Midtrans dipusatkan di Diskominfotik, tidak ada lagi penyedia
        // yang menuntut wilayah mengisi kunci API sendiri. Dipertahankan
        // sebagai satu tempat keputusan kalau nanti ada penyedia yang begitu.
        return false;
    }

    /**
     * Kredensial tingkat platform sudah terisi?
     * Untuk Midtrans ini hanya cadangan; untuk Xendit ini wajib.
     */
    public static function platformSiap(): bool
    {
        return match (self::aktif()) {
            self::XENDIT => filled(config('services.xendit.secret_key')),
            default      => filled(config('services.midtrans.server_key')),
        };
    }

    /**
     * Kesiapan satu wilayah menerima pembayaran otomatis.
     *
     * @return array{siap:bool, alasan:string, sakelar:bool}
     */
    public static function kesiapanWilayah(?int $regionId): array
    {
        $region = $regionId ? Region::find($regionId) : null;
        $info   = $region?->payment_info ?? [];
        $sakelar = (bool) ($info['payment_gateway_active'] ?? false);

        if (! $region) {
            return ['siap' => false, 'sakelar' => false, 'alasan' => 'Wilayah tidak ditemukan.'];
        }

        if (! $sakelar) {
            return [
                'siap' => false,
                'sakelar' => false,
                'alasan' => 'Pembayaran otomatis belum dinyalakan untuk wilayah ini.',
            ];
        }

        if (self::aktif() === self::XENDIT) {
            if (! self::platformSiap()) {
                return [
                    'siap' => false,
                    'sakelar' => true,
                    'alasan' => 'Kredensial Xendit induk belum diisi di panel Super Admin.',
                ];
            }

            if (! filled($info[XenditService::KUNCI_SUB_AKUN] ?? null)) {
                return [
                    'siap' => false,
                    'sakelar' => true,
                    'alasan' => 'Wilayah ini belum punya sub-akun Xendit. Daftarkan dari panel Super Admin.',
                ];
            }

            return ['siap' => true, 'sakelar' => true, 'alasan' => 'Siap menerima pembayaran lewat Xendit.'];
        }

        // Midtrans terpusat: yang perlu ada hanyalah kunci milik Diskominfotik.
        if (! self::platformSiap()) {
            return [
                'siap' => false,
                'sakelar' => true,
                'alasan' => 'Kredensial Midtrans induk belum diisi di panel Super Admin.',
            ];
        }

        return [
            'siap' => true,
            'sakelar' => true,
            'alasan' => 'Siap. Pemasukan dibukukan sebagai saldo wilayah.',
        ];
    }

    /**
     * Pasang kredensial MIDTRANS milik wilayah ini ke \Midtrans\Config.
     *
     * Mengembalikan false kalau wilayahnya belum siap. Pemanggil WAJIB
     * memeriksa nilai itu dan tidak melanjutkan ke gateway — kalau diteruskan,
     * SDK akan memakai kunci platform yang tersisa di Config, dan uang warga
     * mendarat di rekening yang salah tanpa ada yang menyadarinya.
     */
    public static function terapkanMidtransWilayah(?int $regionId): bool
    {
        $kredensial = self::kredensialWilayah($regionId);

        if (! $kredensial || $kredensial['penyedia'] !== self::MIDTRANS) {
            return false;
        }

        \Midtrans\Config::$serverKey    = $kredensial['server_key'];
        \Midtrans\Config::$clientKey    = $kredensial['client_key'];
        \Midtrans\Config::$isProduction = $kredensial['is_production'];
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;

        return true;
    }

    /**
     * Kredensial yang harus dipakai saat memproses pembayaran wilayah ini.
     *
     * Untuk Midtrans mengembalikan kunci milik wilayah; untuk Xendit
     * mengembalikan ID sub-akunnya, karena kuncinya milik platform.
     *
     * @return array{penyedia:string, server_key?:string, client_key?:string, for_user_id?:string}|null
     */
    public static function kredensialWilayah(?int $regionId): ?array
    {
        if (! self::kesiapanWilayah($regionId)['siap']) {
            return null;
        }

        $info = Region::find($regionId)?->payment_info ?? [];

        if (self::aktif() === self::XENDIT) {
            return [
                'penyedia'    => self::XENDIT,
                'for_user_id' => $info[XenditService::KUNCI_SUB_AKUN],
            ];
        }

        // Kunci Midtrans milik Diskominfotik, sama untuk semua wilayah.
        // region_id tetap dibawa karena dipakai membukukan pemasukan ke
        // saldo wilayah yang benar setelah pembayaran dikonfirmasi.
        return [
            'penyedia'      => self::MIDTRANS,
            'server_key'    => (string) config('services.midtrans.server_key'),
            'client_key'    => (string) config('services.midtrans.client_key'),
            'is_production' => (bool) config('services.midtrans.is_production'),
            'region_id'     => $regionId,
        ];
    }
}
