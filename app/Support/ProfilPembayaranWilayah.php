<?php

namespace App\Support;

use App\Models\Region;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;

/**
 * Profil pembayaran milik SATU WILAYAH.
 *
 * Latar belakangnya: pemasukan tiap desa/kecamatan menjadi tanggung jawab
 * daerahnya masing-masing, bukan lagi ditampung rekening pusat. Tapi halaman
 * pemesanan warga selama ini membaca SystemSetting — singleton platform —
 * sehingga warga Desa A melihat rekening pusat, bukan rekening desanya,
 * padahal kepala desa sudah mengisinya di Pengaturan Pembayaran Wilayah.
 *
 * Kelas ini menjembatani keduanya. Ia berpura-pura menjadi objek $setting yang
 * sudah dipakai view, tapi nilainya diambil dari `regions.payment_info` lebih
 * dulu, dan baru jatuh ke SystemSetting kalau wilayahnya belum mengisi. Dengan
 * begitu view tidak perlu diubah sama sekali, dan wilayah yang belum sempat
 * mengatur rekening tidak langsung kehilangan instruksi pembayaran.
 *
 * Nama kunci di payment_info berbeda dengan nama kolom SystemSetting
 * (account_number vs bank_account_number), jadi pemetaannya ditulis eksplisit
 * di $petaKunci di bawah.
 */
class ProfilPembayaranWilayah
{
    /**
     * Properti yang dibaca view => kunci padanannya di regions.payment_info.
     * Properti di luar daftar ini langsung diteruskan ke SystemSetting.
     */
    private const PETA_KUNCI = [
        'bank_name'            => 'bank_name',
        'bank_account_number'  => 'account_number',
        'bank_account_holder'  => 'account_name',
        'ewallet_name'         => 'ewallet_name',
        'ewallet_number'       => 'ewallet_number',
        'ewallet_account_holder' => 'ewallet_account_name',
    ];

    private function __construct(
        private ?Region $region,
        private ?SystemSetting $pusat,
    ) {
    }

    public static function untuk(?int $regionId): self
    {
        return new self(
            ($regionId && Schema::hasTable('regions')) ? Region::find($regionId) : null,
            Schema::hasTable('system_settings') ? SystemSetting::query()->first() : null,
        );
    }

    /**
     * View memanggil $setting->bank_name, $setting->payment_info, dan seterusnya.
     * Semua diarahkan ke sini.
     */
    public function __get(string $nama)
    {
        $info = $this->region?->payment_info ?? [];

        // payment_info memang milik wilayah; SystemSetting tidak punya kolom ini.
        // Sebelumnya view membacanya dari SystemSetting dan selalu dapat null,
        // membuat sakelar pengiriman diam-diam memakai nilai bawaan.
        if ($nama === 'payment_info') {
            return $info;
        }

        // Metode pembayaran diturunkan dari data wilayah, bukan disetel manual.
        // Transfer menyala sendiri begitu bank dan nomor rekening terisi —
        // admin desa tidak perlu mencentang apa pun lagi.
        if ($nama === 'payment_methods') {
            return $this->metodeTersedia();
        }

        $kunci = self::PETA_KUNCI[$nama] ?? null;

        if ($kunci !== null && filled($info[$kunci] ?? null)) {
            return $info[$kunci];
        }

        return $this->pusat?->{$nama};
    }

    public function __isset(string $nama): bool
    {
        return $this->__get($nama) !== null;
    }

    /**
     * Metode pembayaran yang benar-benar bisa dipakai warga di wilayah ini.
     *
     * Urutannya disengaja: transfer ke rekening wilayah diletakkan pertama
     * supaya menjadi pilihan teratas di halaman pemesanan.
     *
     * @return array<int, string>
     */
    public function metodeTersedia(): array
    {
        $info = $this->region?->payment_info ?? [];
        $metode = [];

        // Transfer manual: aktif bila sakelar bank_active menyala dan bank + nomor rekening terisi.
        $bankAktif = (bool) ($info['bank_active'] ?? true);

        if ($bankAktif && $this->punyaRekeningSendiri()) {
            $metode[] = 'transfer';
        }

        // E-Wallet manual: aktif bila sakelar ewallet_active menyala dan nama + nomor e-wallet terisi.
        $ewalletAktif = (bool) ($info['ewallet_active'] ?? false);

        if ($ewalletAktif && $this->punyaEwalletSendiri()) {
            $metode[] = 'ewallet';
        }

        // Pembayaran Tunai (Cash):
        // - Mengikuti sakelar cash_only_active dari Pengaturan Pembayaran Wilayah jika sudah disimpan.
        // - Untuk wilayah baru yang belum pernah mengatur pembayaran (belum ada bank/e-wallet),
        //   tunai tetap menjadi pilihan bawaan agar halaman pemesanan tidak buntu.
        $tunaiAktif = array_key_exists('cash_only_active', $info)
            ? (bool) $info['cash_only_active']
            : empty($metode);

        if ($tunaiAktif) {
            $metode[] = 'tunai';
        }

        return array_values(array_unique($metode));
    }

    /** Wilayah ini sudah mengisi rekeningnya sendiri? */
    public function punyaRekeningSendiri(): bool
    {
        $info = $this->region?->payment_info ?? [];

        return filled($info['bank_name'] ?? null) && filled($info['account_number'] ?? null);
    }

    /** Wilayah ini sudah mengisi e-wallet-nya sendiri? */
    public function punyaEwalletSendiri(): bool
    {
        $info = $this->region?->payment_info ?? [];

        return filled($info['ewallet_name'] ?? null) && filled($info['ewallet_number'] ?? null);
    }

    /** Wilayah ini menyalakan pembayaran otomatis? */
    public function gatewayAktif(): bool
    {
        return (bool) ($this->region?->payment_info['payment_gateway_active'] ?? false);
    }

    public function region(): ?Region
    {
        return $this->region;
    }

    /**
     * Informasi rekening dan e-wallet publik wilayah untuk instruksi pembayaran API mobile.
     *
     * @return array<string, mixed>
     */
    public function publik(): array
    {
        $metode = $this->metodeTersedia();
        $bankAktif = in_array('transfer', $metode, true);
        $ewalletAktif = in_array('ewallet', $metode, true);

        return [
            'bank_name' => $bankAktif ? $this->bank_name : null,
            'account_number' => $bankAktif ? $this->bank_account_number : null,
            'account_name' => $bankAktif ? $this->bank_account_holder : null,
            'ewallet_name' => $ewalletAktif ? $this->ewallet_name : null,
            'ewallet_number' => $ewalletAktif ? $this->ewallet_number : null,
            'ewallet_account_name' => $ewalletAktif ? $this->ewallet_account_holder : null,
            'payment_methods' => $metode,
        ];
    }
}
