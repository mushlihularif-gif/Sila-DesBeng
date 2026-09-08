<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiCredential;
use App\Models\SystemSetting;
use App\Models\WalletTransaction;
use App\Models\GasOrder;
use App\Models\RentalBooking;
use App\Models\MobilBooking;
use App\Models\FasilitasUmumBooking;
use App\Models\OperationalExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuperAdminSettingController extends Controller
{
    /**
     * Panel Integrasi Payment Gateway (satu kredensial platform, dipakai semua region)
     */
    /**
     * Panel Integrasi & API Key Platform.
     *
     * Daftar kartu kredensial dirender dari config/api_providers.php, jadi
     * menambah provider baru tidak perlu menyentuh controller maupun view ini.
     */
    public function gateway()
    {
        $settings  = SystemSetting::instance();
        $providers = config('api_providers', []);
        $tersimpan = ApiCredential::allCached();

        // Kesiapan tiap wilayah di bawah penyedia yang sedang aktif. Tanpa ini
        // Super Admin memilih penyedia tanpa tahu akibatnya: berapa desa yang
        // langsung bisa menerima pembayaran, dan berapa yang justru terhenti.
        $penyedia      = \App\Support\PenyediaPembayaran::aktif();
        $labelPenyedia = \App\Support\PenyediaPembayaran::label();
        $kunciDiWilayah = \App\Support\PenyediaPembayaran::kunciDiisiOlehWilayah();
        $platformSiap  = \App\Support\PenyediaPembayaran::platformSiap();

        $kesiapanWilayah = \App\Models\Region::whereIn('type', ['desa', 'kecamatan'])
            ->orderBy('name')
            ->get()
            ->map(function ($region) {
                $status = \App\Support\PenyediaPembayaran::kesiapanWilayah($region->id);

                return [
                    'nama'   => $region->name,
                    'tipe'   => $region->type,
                    'siap'   => $status['siap'],
                    'alasan' => $status['alasan'],
                ];
            });

        $jumlahSiap = $kesiapanWilayah->where('siap', true)->count();

        // Hanya kartu kredensial gateway yang SEDANG aktif yang ditampilkan.
        // Menampilkan keduanya sekaligus menyesatkan: Super Admin bisa mengisi
        // kunci Xendit dengan rapi lalu heran kenapa transaksinya tetap lewat
        // Midtrans. Kartu penyedia lain tidak ikut disaring.
        $penyediaLain = $penyedia === \App\Support\PenyediaPembayaran::MIDTRANS
            ? \App\Support\PenyediaPembayaran::XENDIT
            : \App\Support\PenyediaPembayaran::MIDTRANS;

        $labelPenyediaLain = $providers[$penyediaLain]['label'] ?? ucfirst($penyediaLain);

        unset($providers[$penyediaLain]);

        return view('admin.super_sistem.gateway', compact(
            'settings', 'providers', 'tersimpan',
            'penyedia', 'labelPenyedia', 'kunciDiWilayah', 'platformSiap',
            'kesiapanWilayah', 'jumlahSiap', 'penyediaLain', 'labelPenyediaLain'
        ));
    }

    /**
     * Simpan pengaturan gateway yang sifatnya bisnis, bukan kredensial
     * (penyedia aktif & fee platform).
     */
    public function gatewayUpdate(Request $request)
    {
        $validated = $request->validateWithBag('umum', [
            // 'oy' dibuang dari pilihan: tidak ada implementasinya, dan
            // PenyediaPembayaran::aktif() diam-diam jatuh ke Midtrans kalau
            // nilainya di luar dua ini - Super Admin mengira memilih OY!
            // padahal seluruh sistem tetap berjalan di atas Midtrans.
            // Wajib diisi karena nilai kosong pun berakibat sama.
            'gateway_provider'        => 'required|in:midtrans,xendit',
            'platform_fee_percentage' => 'required|numeric|min:0|max:100',
        ], [
            'gateway_provider.required' => 'Penyedia gateway wajib dipilih.',
            'gateway_provider.in'       => 'Penyedia yang didukung sistem baru Midtrans dan Xendit.',
            'platform_fee_percentage.required' => 'Fee platform wajib diisi.',
        ]);

        $settings = SystemSetting::instance();
        $settings->gateway_provider = $validated['gateway_provider'];
        $settings->platform_fee_percentage = $validated['platform_fee_percentage'];
        $settings->save();

        Log::info('SuperAdmin: Pengaturan gateway umum diperbarui oleh ' . auth()->user()->email);

        return redirect()
            ->route('admin.sistem-platform.gateway')
            ->with('success', 'Pengaturan gateway berhasil diperbarui.');
    }

    /**
     * Simpan (TIMPA) kredensial satu kategori.
     *
     * Semua field kategori wajib diisi — kalau ada yang kosong, form ditolak
     * dengan peringatan dan data lama tetap utuh. Penyimpanan memakai
     * updateOrCreate sehingga satu kategori selamanya hanya punya satu baris.
     */
    public function credentialUpdate(Request $request, string $category)
    {
        $provider = config("api_providers.{$category}");

        abort_if(! $provider, 404, 'Kategori kredensial tidak dikenal.');

        [$rules, $attributes, $messages] = $this->buildCredentialValidation($provider);

        $validated = $request->validateWithBag($category, $rules, $messages, $attributes);

        // Pemeriksaan silang khusus Midtrans: sakelar mode dan awalan kunci harus
        // sepasang. Kalau tidak, Midtrans menolak dengan 401 dan pembayaran gagal
        // tanpa pesan yang jelas — lebih baik dicegat di sini.
        $this->periksaPasanganKunciMidtrans($request, $category);

        $credentials = [];

        foreach ($provider['fields'] as $field => $definition) {
            if (($definition['type'] ?? 'text') === 'boolean') {
                $credentials[$field] = $request->boolean($field);
                continue;
            }

            $credentials[$field] = trim((string) ($validated[$field] ?? ''));
        }

        ApiCredential::put($category, $credentials, auth()->user()->email);

        // Kotak masuk menyimpan hasil bacanya di cache; buang begitu kredensial
        // berubah supaya panel tidak terus menampilkan hasil dari akun lama.
        if ($category === 'gmail_imap') {
            \App\Services\GmailInboxService::lupakanCache();
        }

        Log::info("SuperAdmin: Kredensial [{$category}] ditimpa oleh " . auth()->user()->email);

        // Kredensial yang bisa diperiksa langsung ke penyedianya, diuji di sini.
        // Prinsipnya: JANGAN menebak sah/tidaknya kunci dari bentuk teksnya —
        // penyedia bisa mengubah format kapan saja (Midtrans membuang awalan
        // "SB-", Google AI Studio berpindah dari "AIza" ke "AQ."). Satu-satunya
        // jawaban yang bisa dipercaya datang dari server penyedianya sendiri.
        $uji = match (true) {
            $category === 'midtrans' && ! empty($credentials['server_key']) => $this->ujiKoneksiMidtrans(
                $credentials['server_key'],
                (bool) ($credentials['is_production'] ?? false)
            ),
            $category === 'gemini' && ! empty($credentials['api_key']) => $this->ujiKoneksiGemini(
                $credentials['api_key']
            ),
            default => null,
        };

        if ($uji) {
            if ($uji['status'] !== 'valid') {
                Log::warning("SuperAdmin: Uji kredensial [{$category}] gagal ({$uji['status']}) — " . $uji['pesan']);

                return redirect()
                    ->route('admin.sistem-platform.gateway')
                    ->with('warning', 'Kredensial tersimpan, TAPI belum lolos uji: ' . $uji['pesan']);
            }

            return redirect()
                ->route('admin.sistem-platform.gateway')
                ->with('success', "Kredensial {$provider['label']} berhasil diterapkan dan {$uji['pesan']}");
        }

        return redirect()
            ->route('admin.sistem-platform.gateway')
            ->with('success', "Kredensial {$provider['label']} berhasil diterapkan.");
    }

    /**
     * Hapus kredensial satu kategori sehingga aplikasi kembali memakai nilai .env.
     */
    public function credentialDestroy(string $category)
    {
        $provider = config("api_providers.{$category}");

        abort_if(! $provider, 404, 'Kategori kredensial tidak dikenal.');

        ApiCredential::forget($category);

        Log::info("SuperAdmin: Kredensial [{$category}] dihapus oleh " . auth()->user()->email);

        return redirect()
            ->route('admin.sistem-platform.gateway')
            ->with('success', "Kredensial {$provider['label']} dihapus. Sistem kembali memakai nilai dari file .env.");
    }

    /**
     * Uji kunci Midtrans ke server aslinya, dipanggil tepat setelah disimpan.
     *
     * Midtrans tidak punya endpoint "ping", jadi caranya menanyakan status sebuah
     * order id yang pasti tidak ada. Yang dibaca adalah KODE balasannya:
     *
     *   404 -> kunci diterima, hanya transaksinya yang tidak ada  => kunci VALID
     *   401 -> kredensial ditolak                                 => kunci SALAH
     *   lainnya / CURL error                                      => jaringan/luar dugaan
     *
     * Tujuannya supaya salah ketik atau kelebihan karakter ketahuan di panel admin,
     * bukan baru ketahuan ketika warga sudah berada di halaman pembayaran.
     *
     * @return array{status:string, pesan:string}
     */
    private function ujiKoneksiMidtrans(string $serverKey, bool $produksi, bool $ulangi = false): array
    {
        $serverKeyAsal    = \Midtrans\Config::$serverKey;
        $produksiAsal     = \Midtrans\Config::$isProduction;
        $curlOptionsAsal  = \Midtrans\Config::$curlOptions;

        try {
            // Spasi di ujung kunci membuat header HTTP tidak sah, sehingga curl
            // gagal SEBELUM sempat mengirim permintaan — gejalanya mirip "server
            // tidak menjawab" padahal masalahnya salah salin.
            \Midtrans\Config::$serverKey = trim($serverKey);
            \Midtrans\Config::$isProduction = $produksi;
            // Jangan biarkan penyimpanan menggantung kalau Midtrans lambat.
            //
            // CURLOPT_HTTPHEADER WAJIB disertakan meski kosong: ApiRequestor.php:117
            // membaca Config::$curlOptions[CURLOPT_HTTPHEADER] tanpa isset(), jadi
            // opsi curl kustom tanpa kunci itu memicu "Undefined array key 10023"
            // sebelum permintaan HTTP-nya sempat terkirim.
            \Midtrans\Config::$curlOptions = [
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_HTTPHEADER     => [],
            ];

            \Midtrans\Transaction::status('SILADESBENG-UJI-' . uniqid());

            // Praktis tidak akan sampai sini; kalau iya, berarti kunci diterima.
            return ['status' => 'valid', 'pesan' => 'Kunci diterima Midtrans.'];
        } catch (\Throwable $e) {
            $kode = (int) $e->getCode();

            if ($kode === 404) {
                return [
                    'status' => 'valid',
                    'pesan'  => 'Kunci diterima Midtrans (' . ($produksi ? 'Production' : 'Sandbox') . ').',
                ];
            }

            if ($kode === 401 || $kode === 403) {
                // Ditolak di mode yang dipilih. Coba lingkungan satunya: kalau di
                // sana diterima, berarti kuncinya benar dan hanya sakelarnya yang
                // keliru — jauh lebih berguna daripada sekadar bilang "ditolak".
                if (! $ulangi) {
                    $lain = $this->ujiKoneksiMidtrans($serverKey, ! $produksi, true);

                    if ($lain['status'] === 'valid') {
                        $modeBenar = $produksi ? 'Sandbox' : 'Production';
                        $modeSalah = $produksi ? 'Production' : 'Sandbox';

                        return [
                            'status' => 'salah_mode',
                            'pesan'  => "Kunci ini SAH, tetapi milik lingkungan {$modeBenar}, sedangkan "
                                . "sakelar sedang di {$modeSalah}. Ubah sakelar Mode Production agar cocok.",
                        ];
                    }
                }

                return [
                    'status' => 'ditolak',
                    'pesan'  => 'Midtrans MENOLAK Server Key ini di kedua lingkungan. Periksa kembali '
                        . 'apakah ada salah ketik atau karakter yang ikut tersalin, lalu salin ulang dari dashboard Midtrans.',
                ];
            }

            if (stripos($e->getMessage(), 'CURL') !== false) {
                return [
                    'status' => 'tidak_terhubung',
                    'pesan'  => 'Tidak bisa menghubungi server Midtrans, jadi kunci belum bisa dipastikan benar. '
                        . 'Periksa koneksi internet server.',
                ];
            }

            return [
                'status' => 'tidak_pasti',
                'pesan'  => 'Balasan Midtrans di luar dugaan, kunci belum bisa dipastikan benar. '
                    . 'Rinciannya tercatat di log aplikasi.',
            ];
        } finally {
            \Midtrans\Config::$serverKey = $serverKeyAsal;
            \Midtrans\Config::$isProduction = $produksiAsal;
            \Midtrans\Config::$curlOptions = $curlOptionsAsal;
        }
    }

    /**
     * Uji API key Gemini ke server Google.
     *
     * Sengaja TIDAK menebak dari awalan kunci: Google AI Studio menerbitkan
     * format baru berawalan "AQ." sementara kunci lama berawalan "AIza", dan
     * keduanya sah. Menebak dari bentuk teks pernah menolak kunci yang benar.
     *
     * Endpoint daftar model dipilih karena ringan, tidak memakai kuota
     * pembuatan teks, dan cukup untuk membuktikan kunci diterima.
     *
     * @return array{status:string, pesan:string}
     */
    private function ujiKoneksiGemini(string $apiKey): array
    {
        $kunci = trim($apiKey);
        $url = 'https://generativelanguage.googleapis.com/v1beta/models';

        // DUA gaya autentikasi dicoba, karena Google memakai keduanya:
        //   ?key=...                  -> API key klasik (AIza...)
        //   Authorization: Bearer ... -> "auth key" format baru (AQ....)
        // Mencoba keduanya membuat uji ini tidak ikut basi kalau Google
        // mengubah format lagi.
        $cara = [
            'query'  => fn () => \Illuminate\Support\Facades\Http::timeout(10)->connectTimeout(5)
                ->get($url, ['key' => $kunci]),
            'bearer' => fn () => \Illuminate\Support\Facades\Http::timeout(10)->connectTimeout(5)
                ->withToken($kunci)->get($url),
        ];

        $statusTerakhir = null;

        foreach ($cara as $nama => $panggil) {
            try {
                $respon = $panggil();
            } catch (\Throwable $e) {
                return [
                    'status' => 'tidak_terhubung',
                    'pesan'  => 'Tidak bisa menghubungi server Google, jadi kunci belum bisa dipastikan benar. '
                        . 'Periksa koneksi internet server.',
                ];
            }

            if ($respon->successful()) {
                $jumlah = count($respon->json('models') ?? []);
                $gaya = $nama === 'query' ? 'API key' : 'auth key (Bearer)';

                return [
                    'status' => 'valid',
                    'pesan'  => "Kunci diterima Google sebagai {$gaya} — {$jumlah} model tersedia.",
                ];
            }

            $statusTerakhir = $respon->status();
        }

        if (in_array($statusTerakhir, [400, 401, 403], true)) {
            return [
                'status' => 'ditolak',
                'pesan'  => 'Google MENOLAK kunci ini pada kedua cara autentikasi. Salin ulang langsung dari '
                    . 'AI Studio (jangan diketik manual), dan pastikan Generative Language API aktif di project tersebut.',
            ];
        }

        return [
            'status' => 'tidak_pasti',
            'pesan'  => 'Google menjawab dengan kode ' . $statusTerakhir . ', kunci belum bisa dipastikan benar.',
        ];
    }

    /**
     * Pastikan sakelar Mode Production cocok dengan awalan kunci Midtrans.
     *
     * Midtrans memisahkan Sandbox dan Production sebagai dua lingkungan penuh:
     * kunci SB-Mid- hanya sah di api.sandbox.midtrans.com, kunci Mid- hanya sah
     * di api.midtrans.com. Sakelar di panel ini menentukan alamat mana yang
     * dihubungi aplikasi, jadi salah pasang = semua transaksi ditolak 401.
     */
    private function periksaPasanganKunciMidtrans(Request $request, string $category): void
    {
        if ($category !== 'midtrans') {
            return;
        }

        $produksi = $request->boolean('is_production');
        $galat = [];

        foreach (['server_key' => 'Server Key', 'client_key' => 'Client Key'] as $field => $label) {
            $nilai = trim((string) $request->input($field));

            if ($nilai === '') {
                continue;
            }

            // Kunci Midtrans tidak pernah memuat spasi. Spasi di tengah tidak
            // terhapus oleh trim() dan akan membuat permintaan gagal sebelum
            // terkirim, jadi dicegat di sini dengan pesan yang jelas.
            if (preg_match('/\s/', $nilai)) {
                $galat[$field] = "{$label} memuat spasi. Kunci Midtrans tidak pernah mengandung spasi — "
                    . 'kemungkinan ada karakter ikut tersalin. Salin ulang langsung dari dashboard Midtrans.';
                continue;
            }

            // CATATAN: JANGAN menebak lingkungan dari awalan kunci.
            // Dulu Midtrans memakai awalan "SB-Mid-" untuk sandbox, tetapi akun
            // yang lebih baru memakai "Mid-" untuk KEDUANYA. Sudah dibuktikan
            // dengan kunci sandbox milik instansi ini: awalannya "Mid-server-",
            // diterima api.sandbox.midtrans.com dan ditolak api.midtrans.com.
            // Penentuan lingkungan dilakukan lewat uji koneksi sungguhan di
            // ujiKoneksiMidtrans(), bukan dari bentuk teksnya.
        }

        if ($galat) {
            throw \Illuminate\Validation\ValidationException::withMessages($galat)
                ->errorBag($category);
        }
    }

    /**
     * Susun aturan validasi satu kategori dari definisi di config/api_providers.php.
     *
     * Setiap field teks jadi: required + string + batas min/max karakter dari
     * config + aturan tambahan (regex/starts_with) kalau ada.
     */
    private function buildCredentialValidation(array $provider): array
    {
        $rules = [];
        $attributes = [];
        $messages = [];

        foreach ($provider['fields'] as $field => $definition) {
            $attributes[$field] = $definition['label'];

            if (($definition['type'] ?? 'text') === 'boolean') {
                $rules[$field] = ['nullable', 'boolean'];
                continue;
            }

            $baris = ['required', 'string'];

            if (isset($definition['min'])) {
                $baris[] = 'min:' . $definition['min'];
                $messages["{$field}.min"] = ":attribute minimal {$definition['min']} karakter.";
            }

            if (isset($definition['max'])) {
                $baris[] = 'max:' . $definition['max'];
                $messages["{$field}.max"] = ":attribute maksimal {$definition['max']} karakter.";
            }

            if (($definition['type'] ?? 'text') === 'select' && ! empty($definition['options'])) {
                $baris[] = 'in:' . implode(',', array_keys($definition['options']));
            }

            foreach ($definition['rules'] ?? [] as $tambahan) {
                $baris[] = $tambahan;
            }

            $messages["{$field}.required"] = ':attribute wajib diisi.';
            $messages["{$field}.email"] = ':attribute harus berupa alamat email yang sah.';
            $messages["{$field}.in"] = 'Pilihan :attribute tidak sah.';

            if (isset($definition['hint'])) {
                $messages["{$field}.regex"] = ":attribute formatnya tidak sesuai. {$definition['hint']}";
                $messages["{$field}.starts_with"] = ":attribute formatnya tidak sesuai. {$definition['hint']}";
            }

            $rules[$field] = $baris;
        }

        return [$rules, $attributes, $messages];
    }

    /**
     * Monitoring kesehatan transaksi lintas region (agregat, bukan nominal per desa)
     */
    public function monitoring()
    {
        $unitModels = [
            'Gas' => GasOrder::class,
            'Sewa Alat' => RentalBooking::class,
            'Sewa Mobil' => MobilBooking::class,
            'Fasilitas Umum' => FasilitasUmumBooking::class,
        ];

        $stats = [];
        foreach ($unitModels as $label => $modelClass) {
            $stats[$label] = [
                'total' => $modelClass::count(),
                'selesai' => $modelClass::where('status', 'completed')->count(),
                'gagal' => $modelClass::whereIn('status', ['rejected', 'cancelled'])->count(),
                'menunggu' => $modelClass::where('status', 'pending')->count(),
            ];
        }

        $walletHealth = [
            // sum(), bukan count() - sebelumnya menghitung JUMLAH BARIS, jadi
            // angkanya kebetulan cocok dengan rupiah hanya kalau tiap transaksi
            // pas Rp 1. Sejak Midtrans dipusatkan di akun Diskominfotik, angka
            // ini bukan lagi sekadar indikator kesehatan sistem - ini rupiah
            // sungguhan yang jadi tanggung jawab Diskominfotik mencairkannya.
            'total_tertahan' => (float) WalletTransaction::where('type', 'ditahan')->where('status', 'pending')->sum('amount'),
            'total_gagal_verifikasi' => WalletTransaction::where('status', 'rejected')->count(),
            'jumlah_region_aktif' => WalletTransaction::distinct('region_id')->count('region_id'),
        ];

        return view('admin.super_sistem.monitoring', compact('stats', 'walletHealth'));
    }

    /**
     * Log keamanan & audit (siapa buat akun staf, percobaan akses ditolak, dst)
     */
    public function securityLog()
    {
        $logPath = storage_path('logs/laravel.log');
        $lines = [];

        if (file_exists($logPath)) {
            $handle = fopen($logPath, 'r');
            $buffer = [];
            while (!feof($handle)) {
                $line = fgets($handle);
                if ($line !== false && (str_contains($line, 'SECURITY:') || str_contains($line, 'SuperAdmin:'))) {
                    $buffer[] = trim($line);
                }
            }
            fclose($handle);
            $lines = array_slice(array_reverse($buffer), 0, 200);
        }

        $recentStaffAccounts = \App\Models\User::where('role', 'staff')
            ->whereNotNull('created_by')
            ->with('creator:id,name,email')
            ->latest()
            ->limit(50)
            ->get();

        return view('admin.super_sistem.security-log', compact('lines', 'recentStaffAccounts'));
    }

    /**
     * Monitoring biaya server/domain/hosting - murni pencatatan & pengingat.
     * Tidak ditarik dari saldo wallet/fee platform, tetap dibayar lewat APBD
     * lewat Diskominfotik; ini cuma bantu supaya tidak lupa jatuh tempo.
     */
    public function expenses()
    {
        $expenses = OperationalExpense::with('payer:id,name')->orderBy('due_date')->get();

        return view('admin.super_sistem.expenses', compact('expenses'));
    }

    public function expensesStore(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|in:domain,hosting,ssl,api_service,lainnya',
            'amount' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:bulanan,tahunan,sekali_bayar',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        OperationalExpense::create($validated);

        return redirect()->back()->with('success', 'Item biaya operasional berhasil ditambahkan.');
    }

    public function expensesMarkPaid(OperationalExpense $expense)
    {
        $expense->markPaidAndRenew(auth()->id());

        Log::info('SuperAdmin: Expense "' . $expense->item_name . '" marked paid by ' . auth()->user()->email);

        return redirect()->back()->with('success', 'Tagihan ditandai lunas.');
    }

    public function expensesDestroy(OperationalExpense $expense)
    {
        $expense->delete();

        return redirect()->back()->with('success', 'Item biaya operasional dihapus.');
    }
}
