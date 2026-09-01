<?php

namespace App\Services;

use App\Models\Region;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Xendit xenPlatform.
 *
 * Model yang dipakai SiLaDesBeng: kredensial INDUK dipegang Diskominfotik,
 * sedangkan tiap desa/kecamatan menjadi SUB-AKUN dengan saldo dan rekening
 * sendiri. Saat membuat transaksi, ID sub-akun dikirim lewat header
 * `for-user-id`, sehingga dananya langsung menjadi saldo wilayah itu dan
 * tidak pernah singgah di saldo induk.
 *
 * Konsekuensinya perangkat desa tidak pernah menyentuh kunci API apa pun —
 * mereka cukup menyediakan nomor rekening di halaman Pembayaran Wilayah.
 *
 * Catatan penting dari dokumentasi Xendit:
 *
 *  - Tipe sub-akun OWNED dibatasi untuk akun Indonesia, jadi yang dipakai MANAGED.
 *  - Transaksi BARU boleh dibuat setelah callback `account.created` diterima;
 *    membuat transaksi sebelum itu akan gagal.
 *  - Callback diverifikasi lewat header X-CALLBACK-TOKEN, yang BERBEDA dari
 *    Secret Key dan diambil dari menu Webhook di dashboard.
 */
class XenditService
{
    private const BASE_URL = 'https://api.xendit.co';

    /** Kunci penyimpan ID sub-akun di regions.payment_info. */
    public const KUNCI_SUB_AKUN = 'xendit_account_id';

    public function siap(): bool
    {
        return filled(config('services.xendit.secret_key'));
    }

    /**
     * Permintaan dasar. Xendit memakai Basic Auth dengan Secret Key sebagai
     * username dan password dikosongkan.
     */
    private function permintaan(?string $forUserId = null): PendingRequest
    {
        $req = Http::withBasicAuth(trim((string) config('services.xendit.secret_key')), '')
            ->acceptJson()
            ->timeout(20)
            ->connectTimeout(8);

        // Inilah yang membuat dana mendarat di saldo wilayah, bukan saldo induk.
        return $forUserId ? $req->withHeaders(['for-user-id' => $forUserId]) : $req;
    }

    /**
     * Uji Secret Key ke server Xendit, dipakai saat kredensial disimpan.
     *
     * @return array{status:string, pesan:string}
     */
    public function ujiKredensial(): array
    {
        if (! $this->siap()) {
            return ['status' => 'belum_diatur', 'pesan' => 'Secret Key Xendit belum diisi.'];
        }

        try {
            $respon = $this->permintaan()->get(self::BASE_URL . '/balance');
        } catch (\Throwable $e) {
            return [
                'status' => 'tidak_terhubung',
                'pesan'  => 'Tidak bisa menghubungi server Xendit, jadi kunci belum bisa dipastikan benar.',
            ];
        }

        if ($respon->successful()) {
            return ['status' => 'valid', 'pesan' => 'Secret Key diterima Xendit.'];
        }

        if (in_array($respon->status(), [401, 403], true)) {
            return [
                'status' => 'ditolak',
                'pesan'  => 'Xendit MENOLAK Secret Key ini. Salin ulang dari dashboard, dan pastikan kuncinya '
                    . 'milik lingkungan yang sama dengan sakelar Mode Production.',
            ];
        }

        return [
            'status' => 'tidak_pasti',
            'pesan'  => 'Xendit menjawab dengan kode ' . $respon->status() . ', kunci belum bisa dipastikan benar.',
        ];
    }

    /**
     * Daftarkan satu wilayah sebagai sub-akun.
     *
     * Entitas yang didaftarkan adalah badan usaha milik wilayah tersebut
     * (BUM Desa untuk desa, BUM Desa Bersama untuk kecamatan) — bukan
     * pemerintah desanya, karena Xendit hanya menerima badan usaha.
     *
     * @return array{status:string, pesan:string, account_id?:string}
     */
    public function buatSubAkun(Region $region, string $email, string $namaBadanUsaha): array
    {
        if (! $this->siap()) {
            return ['status' => 'belum_diatur', 'pesan' => 'Secret Key Xendit belum diisi di panel Super Admin.'];
        }

        if ($this->idSubAkun($region)) {
            return ['status' => 'sudah_ada', 'pesan' => 'Wilayah ini sudah punya sub-akun Xendit.'];
        }

        try {
            $respon = $this->permintaan()->post(self::BASE_URL . '/v2/accounts', [
                'email' => $email,
                // OWNED dibatasi untuk akun Indonesia, jadi MANAGED.
                'type' => 'MANAGED',
                'public_profile' => ['business_name' => $namaBadanUsaha],
                // Memudahkan mencocokkan kembali kalau ID sempat hilang dari sisi kita.
                'reference_id' => 'siladesbeng-region-' . $region->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Xendit: gagal membuat sub-akun — ' . $e->getMessage());

            return ['status' => 'tidak_terhubung', 'pesan' => 'Tidak bisa menghubungi server Xendit.'];
        }

        if (! $respon->successful()) {
            $pesan = $respon->json('message') ?? ('Kode ' . $respon->status());
            Log::warning("Xendit: pembuatan sub-akun wilayah {$region->id} ditolak — " . $pesan);

            return ['status' => 'ditolak', 'pesan' => 'Xendit menolak pendaftaran: ' . $pesan];
        }

        $id = $respon->json('id');

        if (! $id) {
            return ['status' => 'tidak_pasti', 'pesan' => 'Xendit menjawab tanpa ID sub-akun.'];
        }

        $this->simpanIdSubAkun($region, $id);

        return [
            'status' => 'dibuat',
            'account_id' => $id,
            'pesan' => 'Sub-akun dibuat. Transaksi baru bisa dijalankan setelah Xendit '
                . 'mengirim callback account.created dan verifikasinya selesai.',
        ];
    }

    /**
     * Buat Virtual Account untuk satu pesanan.
     *
     * `$forUserId` diisi ID sub-akun wilayah supaya dananya mendarat di saldo
     * wilayah itu. Selama xenPlatform belum aktif, dibiarkan null dan dana
     * masuk ke akun induk — alur pembayarannya tetap bisa diuji penuh.
     *
     * @return array{status:string, pesan?:string, data?:array}
     */
    public function buatVirtualAccount(
        string $externalId,
        string $kodeBank,
        string $namaPenerima,
        int $jumlah,
        ?string $forUserId = null,
    ): array {
        return $this->kirim('POST', '/callback_virtual_accounts', [
            'external_id'     => $externalId,
            'bank_code'       => strtoupper($kodeBank),
            // Xendit menampilkan nama ini di aplikasi bank warga, jadi dipangkas
            // agar tidak ditolak karena kepanjangan.
            'name'            => mb_substr($namaPenerima, 0, 50),
            'expected_amount' => $jumlah,
            'is_closed'       => true,      // nominal terkunci, warga tak bisa salah transfer
            'is_single_use'   => true,      // sekali bayar lalu VA-nya mati
        ], $forUserId);
    }

    /**
     * Buat QRIS dinamis untuk satu pesanan.
     *
     * Endpoint QR menuntut header api-version; tanpa itu Xendit menolak.
     */
    public function buatQris(string $referenceId, int $jumlah, ?string $forUserId = null): array
    {
        return $this->kirim('POST', '/qr_codes', [
            'reference_id' => $referenceId,
            'type'         => 'DYNAMIC',
            'currency'     => 'IDR',
            'amount'       => $jumlah,
        ], $forUserId, ['api-version' => '2022-07-31']);
    }

    /**
     * Pembungkus permintaan: satu tempat untuk penanganan galat dan pencatatan,
     * supaya tiap metode di atas tidak mengulang blok try/catch yang sama.
     */
    private function kirim(string $metode, string $jalur, array $isi, ?string $forUserId, array $headerTambahan = []): array
    {
        if (! $this->siap()) {
            return ['status' => 'belum_diatur', 'pesan' => 'Secret Key Xendit belum diisi di panel Super Admin.'];
        }

        try {
            $req = $this->permintaan($forUserId);

            if ($headerTambahan) {
                $req = $req->withHeaders($headerTambahan);
            }

            $respon = $req->send($metode, self::BASE_URL . $jalur, ['json' => $isi]);
        } catch (\Throwable $e) {
            Log::warning("Xendit: {$metode} {$jalur} gagal terkirim — " . $e->getMessage());

            return ['status' => 'tidak_terhubung', 'pesan' => 'Tidak bisa menghubungi server Xendit.'];
        }

        if ($respon->successful()) {
            return ['status' => 'ok', 'data' => $respon->json()];
        }

        $pesan = $respon->json('message');
        $pesan = is_array($pesan) ? json_encode($pesan) : ($pesan ?? 'Kode ' . $respon->status());

        Log::warning("Xendit: {$metode} {$jalur} ditolak ({$respon->status()}) — " . $pesan);

        return ['status' => 'ditolak', 'pesan' => $pesan];
    }

    public function idSubAkun(Region $region): ?string
    {
        $id = $region->payment_info[self::KUNCI_SUB_AKUN] ?? null;

        return filled($id) ? (string) $id : null;
    }

    private function simpanIdSubAkun(Region $region, string $id): void
    {
        $info = $region->payment_info ?? [];
        $info[self::KUNCI_SUB_AKUN] = $id;

        $region->payment_info = $info;
        $region->save();
    }

    /**
     * Verifikasi bahwa callback benar-benar dari Xendit.
     *
     * Dibandingkan dengan hash_equals supaya tidak bocor lewat selisih waktu
     * pembandingan string.
     */
    public function callbackSah(?string $tokenDikirim): bool
    {
        $tokenAsli = (string) config('services.xendit.callback_token');

        if ($tokenAsli === '' || $tokenDikirim === null) {
            return false;
        }

        return hash_equals($tokenAsli, $tokenDikirim);
    }
}
