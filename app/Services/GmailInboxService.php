<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Exceptions\AuthFailedException;
use Webklex\PHPIMAP\IMAP;
use Webklex\PHPIMAP\Message;

/**
 * Pembaca kotak masuk Gmail lewat IMAP, khusus BACA-SAJA.
 *
 * Kredensialnya diambil dari config('services.gmail_inbox.*') yang ditimpa
 * ApiCredential dari panel Super Admin (kategori 'gmail_imap').
 *
 * Dua hal yang sengaja dijaga di kelas ini:
 *
 * 1. TIDAK MENGUBAH APA PUN di kotak surat. Opsi IMAP::FT_PEEK dan leaveUnread()
 *    memastikan membuka email di dashboard tidak menandainya sebagai sudah dibaca
 *    di Gmail milik instansi.
 * 2. TIDAK MENEMBAK SERVER IMAP TIAP KALI DASHBOARD DIBUKA. Hasilnya di-cache
 *    sebentar, termasuk hasil gagalnya, supaya koneksi yang bermasalah tidak
 *    membuat dashboard ikut lambat.
 */
class GmailInboxService
{
    private const CACHE_KEY = 'gmail_inbox.daftar';
    private const CACHE_TTL = 120;   // detik
    private const TIMEOUT   = 15;    // detik

    public function isConfigured(): bool
    {
        return filled(config('services.gmail_inbox.email'))
            && filled(config('services.gmail_inbox.app_password'));
    }

    public function alamat(): ?string
    {
        return config('services.gmail_inbox.email');
    }

    /**
     * Daftar email terbaru (hanya header, tanpa isi — supaya cepat).
     *
     * @return array{status:string, pesan?:string, diambil_pada?:string, messages:array}
     */
    public function latest(int $limit = 15, bool $segarkan = false): array
    {
        if (! $this->isConfigured()) {
            return [
                'status'   => 'belum_diatur',
                'pesan'    => 'Kotak masuk Gmail belum ditautkan.',
                'messages' => [],
            ];
        }

        if ($segarkan) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () use ($limit) {
            try {
                return [
                    'status'        => 'ok',
                    'alamat'        => $this->alamat(),
                    'diambil_pada'  => now()->toIso8601String(),
                    'messages'      => $this->ambilDaftar($limit),
                ];
            } catch (\Throwable $e) {
                Log::warning('GmailInbox: gagal mengambil daftar email — ' . $e->getMessage());

                return [
                    'status'   => 'gagal',
                    'pesan'    => $this->pesanRamah($e),
                    'messages' => [],
                ];
            }
        });
    }

    /**
     * Isi satu email. Tidak di-cache karena dibuka per permintaan.
     *
     * @return array{status:string, pesan?:string, message?:array}
     */
    public function message(int $uid): array
    {
        if (! $this->isConfigured()) {
            return ['status' => 'belum_diatur', 'pesan' => 'Kotak masuk Gmail belum ditautkan.'];
        }

        try {
            $client = $this->client();
            $client->connect();

            $pesan = $client->getFolder('INBOX')->query()->leaveUnread()->getMessageByUid($uid);

            if (! $pesan) {
                $client->disconnect();

                return ['status' => 'tidak_ada', 'pesan' => 'Email tidak ditemukan. Mungkin sudah dipindahkan atau dihapus.'];
            }

            $hasil = $this->ringkasHeader($pesan) + [
                'body'        => $this->isiAman($pesan),
                'lampiran'    => $this->namaLampiran($pesan),
            ];

            $client->disconnect();

            return ['status' => 'ok', 'message' => $hasil];
        } catch (\Throwable $e) {
            Log::warning("GmailInbox: gagal membuka email uid={$uid} — " . $e->getMessage());

            return ['status' => 'gagal', 'pesan' => $this->pesanRamah($e)];
        }
    }

    /**
     * Kosongkan cache, dipakai setelah kredensial diganti dari panel.
     */
    public static function lupakanCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // =====================================================
    // Internal
    // =====================================================

    private function ambilDaftar(int $limit): array
    {
        $client = $this->client();
        $client->connect();

        $daftar = $client->getFolder('INBOX')
            ->query()
            ->whereAll()               // kriteria IMAP "ALL"; namanya whereAll, bukan all
            ->leaveUnread()
            ->setFetchBody(false)      // header saja: jauh lebih cepat
            ->setFetchOrder('desc')
            ->limit($limit)
            ->get();

        $hasil = [];

        foreach ($daftar as $pesan) {
            $hasil[] = $this->ringkasHeader($pesan);
        }

        $client->disconnect();

        return $hasil;
    }

    private function ringkasHeader(Message $pesan): array
    {
        $pengirim = $pesan->getFrom()->first();

        $tanggal = $pesan->getDate()?->first();

        return [
            'uid'        => (int) $pesan->getUid(),
            'subjek'     => $this->rapikan((string) $pesan->getSubject()) ?: '(tanpa subjek)',
            'nama'       => $this->rapikan($pengirim->personal ?? '') ?: ($pengirim->mail ?? 'Tidak diketahui'),
            'email'      => $pengirim->mail ?? '',
            'tanggal'    => $tanggal ? $tanggal->toIso8601String() : null,
            'sudah_baca' => $this->sudahDibaca($pesan),
        ];
    }

    private function sudahDibaca(Message $pesan): bool
    {
        try {
            return in_array('seen', array_map('strtolower', $pesan->getFlags()->toArray()), true);
        } catch (\Throwable $e) {
            return true;
        }
    }

    /**
     * Ambil isi email sebagai TEKS BIASA.
     *
     * Isi email adalah data dari luar yang tidak boleh dipercaya. Menampilkan
     * HTML-nya apa adanya membuka celah XSS di dashboard admin, jadi HTML-nya
     * dilucuti di sini dan view hanya menampilkan teks yang sudah di-escape.
     */
    private function isiAman(Message $pesan): string
    {
        $teks = (string) $pesan->getTextBody();

        if (trim($teks) === '') {
            $html = (string) $pesan->getHTMLBody();

            // Buang <script>/<style> BESERTA isinya lebih dulu — strip_tags saja
            // akan menyisakan kode di dalamnya sebagai teks.
            $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html) ?? '';
            $html = preg_replace('#<br\s*/?>#i', "\n", $html) ?? '';
            $html = preg_replace('#</(p|div|tr|li|h[1-6])>#i', "\n", $html) ?? '';

            $teks = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        $teks = preg_replace("/\n{3,}/", "\n\n", str_replace("\r\n", "\n", $teks)) ?? $teks;

        return trim($teks);
    }

    private function namaLampiran(Message $pesan): array
    {
        try {
            return $pesan->getAttachments()
                ->map(fn ($l) => ['nama' => $l->getName(), 'ukuran' => $l->getSize()])
                ->values()
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function rapikan(string $nilai): string
    {
        return trim(preg_replace('/\s+/u', ' ', $nilai) ?? $nilai);
    }

    private function client()
    {
        $cm = new ClientManager([
            'options' => [
                // FT_PEEK = jangan set flag \Seen saat mengambil isi email.
                'fetch'       => IMAP::FT_PEEK,
                'fetch_body'  => false,
                'fetch_flags' => true,
            ],
        ]);

        return $cm->make([
            'host'          => config('services.gmail_inbox.host', 'imap.gmail.com'),
            'port'          => (int) config('services.gmail_inbox.port', 993),
            'protocol'      => 'imap',
            'encryption'    => 'ssl',
            'validate_cert' => true,
            'username'      => config('services.gmail_inbox.email'),
            'password'      => $this->appPassword(),
            'timeout'       => self::TIMEOUT,
        ]);
    }

    /**
     * Google menampilkan App Password sebagai 4 kelompok berspasi
     * ("abcd efgh ijkl mnop"), sedangkan IMAP menolak spasi tersebut.
     */
    private function appPassword(): string
    {
        return preg_replace('/\s+/', '', (string) config('services.gmail_inbox.app_password')) ?? '';
    }

    private function pesanRamah(\Throwable $e): string
    {
        // PENTING: webklex membungkus kegagalan login di dalam
        // ConnectionFailedException yang pesannya "connection setup failed",
        // sedangkan AuthFailedException di dalamnya justru berpesan kosong.
        // Menebak dari teks pesan terluar akan melaporkan App Password yang salah
        // sebagai gangguan jaringan, jadi rantai exception ditelusuri dan
        // KELAS-nya diperiksa lebih dulu sebelum teksnya.
        $rantai = [];
        for ($x = $e; $x !== null; $x = $x->getPrevious()) {
            $rantai[] = $x;
        }

        foreach ($rantai as $x) {
            if ($x instanceof AuthFailedException) {
                return 'Login ditolak Google. Periksa alamat Gmail dan App Password-nya. '
                     . 'Pastikan Verifikasi 2 Langkah aktif dan yang dipakai App Password 16 huruf, bukan password Gmail biasa.';
            }
        }

        $gabungan = implode(' | ', array_map(fn ($x) => $x->getMessage(), $rantai));

        if (stripos($gabungan, 'AUTHENTICATIONFAILED') !== false || stripos($gabungan, 'Invalid credentials') !== false) {
            return 'Login ditolak Google. Periksa alamat Gmail dan App Password-nya.';
        }

        if (stripos($gabungan, 'timed out') !== false || stripos($gabungan, 'timeout') !== false) {
            return 'Server tidak menjawab dalam ' . self::TIMEOUT . ' detik. Kemungkinan port 993 diblokir jaringan atau hosting.';
        }

        if (stripos($gabungan, 'refused') !== false || stripos($gabungan, 'resolve') !== false || stripos($gabungan, 'connection') !== false) {
            return 'Tidak bisa menghubungi imap.gmail.com:993. Periksa koneksi internet server atau aturan firewall.';
        }

        return 'Gagal membaca kotak masuk. Rinciannya tercatat di log aplikasi.';
    }
}
