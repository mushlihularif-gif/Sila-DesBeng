<?php

/*
|--------------------------------------------------------------------------
| Registry Provider API Pihak Ketiga
|--------------------------------------------------------------------------
|
| SATU-SATUNYA file yang perlu diubah kalau mau menambah layanan pihak ketiga
| baru (Xendit, OY!, Fonnte/Wablas untuk WA, Firebase, dsb). Tidak perlu
| migration, tidak perlu tambah kolom, tidak perlu edit form: halaman
| "Integrasi & API Key Platform" merender dirinya sendiri dari file ini.
|
| Tiap key di array ini = satu KATEGORI = tepat satu baris di tabel
| `api_credentials` (kolom `category` unik). Menyimpan lewat form memakai
| updateOrCreate, jadi data lama ditimpa dan tidak akan pernah bertumpuk.
|
| Struktur satu kategori:
|   label       : judul kartu di halaman admin
|   icon        : nama ikon boxicons (tanpa prefix "bx-")
|   description : keterangan singkat di bawah judul
|   console_url : link ke dashboard penyedia (opsional)
|   notes       : array catatan yang ditampilkan di kartu. Token {APP_URL}
|                 otomatis diganti dengan APP_URL aplikasi.
|   fields      : daftar field kredensial.
|
| Struktur satu field:
|   label       : label input
|   type        : text | secret | boolean | select
|   min / max   : batas jumlah karakter (dipakai untuk validasi server DAN
|                 atribut minlength/maxlength di browser). Abaikan untuk boolean.
|   rules       : aturan validasi TAMBAHAN di luar required/string/min/max
|   options     : hanya untuk type "select", berupa [nilai => label]
|   placeholder : contoh format nilai
|   hint        : keterangan kecil di bawah input
|   config      : kunci config() yang ditimpa nilai ini saat runtime, mis.
|                 "services.google.client_id". Kalau kategori belum diisi,
|                 nilai .env yang lama tetap dipakai (fallback otomatis).
|   env         : nama variabel .env yang digantikan (hanya untuk ditampilkan)
|
*/

return [

    'google_oauth' => [
        'label'       => 'Login Google (OAuth 2.0)',
        'icon'        => 'bxl-google',
        'description' => 'Dipakai tombol "Masuk dengan Google" di halaman depan. Ganti akun Google Cloud Console cukup dengan menimpa dua field di bawah.',
        'console_url' => 'https://console.cloud.google.com/apis/credentials',
        'notes'       => [
            'Authorized redirect URI di Google Cloud Console harus PERSIS sama dengan: {APP_URL}/auth/google/callback',
            'Authorized JavaScript origins boleh dikosongkan — aplikasi ini memakai alur server-side (Laravel Socialite), bukan Google Identity Services berbasis JavaScript.',
            'Kalau OAuth consent screen masih berstatus "Testing", email penguji wajib didaftarkan di daftar Test users.',
        ],
        'fields' => [
            'client_id' => [
                'label'       => 'Client ID',
                'type'        => 'text',
                'min'         => 30,
                'max'         => 255,
                'rules'       => ['regex:/\.apps\.googleusercontent\.com$/'],
                'placeholder' => '1234567890-abcdefghijk.apps.googleusercontent.com',
                'hint'        => 'Selalu berakhiran .apps.googleusercontent.com',
                'config'      => 'services.google.client_id',
                'env'         => 'GOOGLE_CLIENT_ID',
            ],
            'client_secret' => [
                'label'       => 'Client Secret',
                'type'        => 'secret',
                'min'         => 16,
                'max'         => 255,
                'placeholder' => 'GOCSPX-xxxxxxxxxxxxxxxxxxxxxxxx',
                'hint'        => 'Biasanya diawali GOCSPX-',
                'config'      => 'services.google.client_secret',
                'env'         => 'GOOGLE_CLIENT_SECRET',
            ],
        ],
    ],

    'google_maps' => [
        'label'       => 'Google Maps',
        'icon'        => 'bx-map-alt',
        'description' => 'Peta lokasi pada form Laporan Warga dan pemesanan gas.',
        'console_url' => 'https://console.cloud.google.com/google/maps-apis/credentials',
        'notes'       => [
            'Ini API key biasa, bukan OAuth client — dibuat lewat menu "Create credentials > API key".',
            'Batasi key lewat Application restrictions supaya tidak bisa dipakai domain lain.',
        ],
        'fields' => [
            'api_key' => [
                'label'       => 'Maps API Key',
                'type'        => 'secret',
                'min'         => 30,
                'max'         => 255,
                'placeholder' => 'AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
                'hint'        => 'Umumnya diawali AIza. Awalan tidak dipaksakan karena Google bisa mengubah formatnya.',
                'config'      => 'services.google_maps.api_key',
                'env'         => 'GOOGLE_MAPS_API_KEY',
            ],
        ],
    ],

    'gmail_imap' => [
        'label'       => 'Kotak Masuk Gmail (IMAP)',
        'icon'        => 'bx-envelope',
        'description' => 'Menampilkan email yang masuk ke alamat resmi platform pada panel kanan dashboard. Bersifat baca-saja.',
        'console_url' => 'https://myaccount.google.com/apppasswords',
        'notes'       => [
            'Memakai App Password, BUKAN password Gmail biasa. Verifikasi 2 Langkah wajib aktif dulu di akun Google tersebut, kalau tidak menu App passwords tidak akan muncul.',
            'Buat di: Akun Google > Keamanan > Verifikasi 2 Langkah > App passwords. Hasilnya 16 huruf, boleh ditempel apa adanya (spasi akan dibuang otomatis).',
            'Sengaja tidak memakai Gmail API karena scope bacanya tergolong restricted — butuh verifikasi Google berbayar, dan tautannya putus tiap 7 hari selama belum terverifikasi.',
            'App Password hanya memberi akses ke kotak surat ini saja dan bisa dicabut kapan saja dari halaman yang sama tanpa mengganggu login Google.',
        ],
        'fields' => [
            'email' => [
                'label'       => 'Alamat Gmail',
                'type'        => 'text',
                'min'         => 6,
                'max'         => 255,
                'rules'       => ['email'],
                'placeholder' => 'kominfo@gmail.com',
                'hint'        => 'Alamat kotak surat yang isinya akan ditampilkan.',
                'config'      => 'services.gmail_inbox.email',
                'env'         => 'GMAIL_INBOX_EMAIL',
            ],
            'app_password' => [
                'label'       => 'App Password',
                'type'        => 'secret',
                'min'         => 16,
                'max'         => 19,
                'placeholder' => 'abcd efgh ijkl mnop',
                'hint'        => '16 huruf dari Google. Boleh dengan atau tanpa spasi.',
                'config'      => 'services.gmail_inbox.app_password',
                'env'         => 'GMAIL_INBOX_APP_PASSWORD',
            ],
        ],
    ],

    'ocr_space' => [
        'label'       => 'OCR KTP — OCR.space',
        'icon'        => 'bx-id-card',
        'description' => 'Membaca NIK dan nama dari foto KTP pada proses verifikasi identitas (KYC).',
        'console_url' => 'https://ocr.space/ocrapi/freekey',
        'notes'       => [
            'Tanpa key sendiri, aplikasi memakai key contoh "helloworld" milik OCR.space yang dibatasi ketat dan tidak layak produksi.',
            'Key gratis dikirim ke email setelah mendaftar di halaman OCR.space API. Simpan di sini, bukan di file .env.',
            'Kalau OCR.space gagal membaca, aplikasi otomatis mencoba ulang memakai Gemini (lihat kartu Gemini AI di bawah).',
        ],
        'fields' => [
            'api_key' => [
                'label'       => 'OCR.space API Key',
                'type'        => 'secret',
                'min'         => 8,
                'max'         => 255,
                'placeholder' => 'K1234567890123',
                'hint'        => 'Key gratis biasanya diawali huruf K.',
                'config'      => 'services.ocr_space.api_key',
                'env'         => 'OCR_SPACE_API_KEY',
            ],
        ],
    ],

    'fonnte_wa' => [
        'label'       => 'WhatsApp OTP — Fonnte',
        'icon'        => 'bxl-whatsapp',
        'description' => 'Mengirim kode OTP dan notifikasi lewat WhatsApp ke nomor warga.',
        'console_url' => 'https://md.fonnte.com/new/index.php',
        'notes'       => [
            'Token diambil dari dashboard Fonnte pada perangkat WhatsApp yang sudah tersambung.',
            'Satu token terikat pada satu perangkat. Kalau perangkatnya di-scan ulang atau terputus, token lama berhenti bekerja dan harus diperbarui di sini.',
        ],
        'fields' => [
            'token' => [
                'label'       => 'Fonnte Token',
                'type'        => 'secret',
                'min'         => 8,
                'max'         => 255,
                'placeholder' => 'xxxxxxxxxxxxxxxxxxxx',
                'hint'        => 'Terlihat di dashboard Fonnte bagian perangkat.',
                'config'      => 'fonnte.token',
                'env'         => 'FONNTE_TOKEN',
            ],
        ],
    ],

    'gemini' => [
        'label'       => 'Gemini AI — Google',
        'icon'        => 'bx-bot',
        'description' => 'Chatbot bantuan warga, sekaligus cadangan pembacaan KTP kalau OCR.space gagal.',
        'console_url' => 'https://aistudio.google.com/app/apikey',
        'notes'       => [
            'Key dibuat di Google AI Studio, BUKAN di Google Cloud Console tempat OAuth dan Maps tadi.',
            'Key ini terpisah dari Login Google dan Google Maps meski sama-sama milik Google — jangan tertukar.',
            'Kalau dikosongkan, chatbot menolak melayani dan cadangan OCR tidak aktif; verifikasi KTP tetap jalan lewat OCR.space saja.',
        ],
        'fields' => [
            'api_key' => [
                'label'       => 'Gemini API Key',
                'type'        => 'secret',
                'min'         => 30,
                'max'         => 255,
                'placeholder' => 'AQ.AbXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
                'hint'        => 'Awalannya bisa AQ. (format baru) atau AIza (format lama) — keduanya sah. Kebenarannya diuji langsung ke Google saat disimpan.',
                'config'      => 'services.gemini.api_key',
                'env'         => 'GEMINI_API_KEY',
            ],
            'model' => [
                'label'       => 'Model',
                'type'        => 'text',
                'min'         => 5,
                'max'         => 60,
                'placeholder' => 'gemini-2.5-flash',
                'hint'        => 'Kosongkan bagian ini hanya kalau tahu persis nama model penggantinya.',
                'config'      => 'services.gemini.model',
                'env'         => 'GEMINI_MODEL',
            ],
        ],
    ],

    'xendit' => [
        'label'       => 'Payment Gateway — Xendit (xenPlatform)',
        'icon'        => 'bx-transfer-alt',
        'description' => 'Kredensial induk milik Diskominfotik. Tiap desa/kecamatan menjadi sub-akun dengan saldo dan rekening sendiri, tanpa perlu menyentuh kunci API.',
        'console_url' => 'https://dashboard.xendit.co/settings/developers#api-keys',
        'notes'       => [
            'Dipakai untuk model "pemasukan dipegang daerah masing-masing": pembayaran dikirim dengan header for-user-id berisi ID sub-akun wilayah, sehingga dana langsung masuk ke saldo wilayah itu, bukan ke saldo induk.',
            'Perangkat desa TIDAK pernah memasukkan kunci apa pun. Mereka hanya mengisi nomor rekening di halaman Pembayaran Wilayah, dan verifikasi sub-akunnya bisa diurus Diskominfotik atas nama desa.',
            'Callback Xendit diverifikasi lewat header X-CALLBACK-TOKEN. Token ini berbeda dari Secret Key dan diambil dari menu Webhook di dashboard.',
            'Kunci lingkungan uji dan produksi berbeda. Pastikan sakelar Mode Production di bawah sesuai dengan kunci yang dimasukkan.',
            'URL callback yang didaftarkan di dashboard Xendit: {APP_URL}/api/payment/callback/xendit',
        ],
        'mode_field' => 'is_production',
        'tautan' => [
            [
                'label'          => 'Buka Dashboard Xendit',
                'ikon'           => 'bx-link-external',
                'url_sandbox'    => 'https://dashboard.xendit.co/',
                'url_production' => 'https://dashboard.xendit.co/',
                'catatan'        => 'Tempat menyalin Secret Key dan Callback Token, serta mengelola sub-akun tiap wilayah.',
            ],
        ],
        'fields' => [
            'secret_key' => [
                'label'       => 'Secret Key (API Key induk)',
                'type'        => 'secret',
                'min'         => 20,
                'max'         => 255,
                'placeholder' => 'xnd_...',
                'hint'        => 'Awalan tidak dipaksakan — Xendit dapat mengubah formatnya. Keabsahannya diuji langsung ke Xendit saat disimpan.',
                'config'      => 'services.xendit.secret_key',
                'env'         => 'XENDIT_SECRET_KEY',
            ],
            'callback_token' => [
                'label'       => 'Callback Token (X-CALLBACK-TOKEN)',
                'type'        => 'secret',
                'min'         => 10,
                'max'         => 255,
                'placeholder' => 'diambil dari menu Webhook di dashboard',
                'hint'        => 'Dipakai memverifikasi bahwa callback benar-benar dari Xendit. BUKAN Secret Key.',
                'config'      => 'services.xendit.callback_token',
                'env'         => 'XENDIT_CALLBACK_TOKEN',
            ],
            'is_production' => [
                'label'  => 'Aktifkan Mode Production',
                'type'   => 'boolean',
                'hint'   => 'Matikan selama masih memakai kunci lingkungan uji.',
                'config' => 'services.xendit.is_production',
                'env'    => 'XENDIT_IS_PRODUCTION',
            ],
        ],
    ],

    'midtrans' => [
        'label'       => 'Payment Gateway — Midtrans',
        'icon'        => 'bx-credit-card',
        'description' => 'Kredensial pembayaran yang dipakai seluruh transaksi dari semua desa/kecamatan.',
        'console_url' => 'https://dashboard.midtrans.com/settings/config_info',
        'notes'       => [
            'Sakelar Mode Production di bawah adalah pengaturan APLIKASI INI, bukan pengaturan di akun Midtrans. Ia menentukan aplikasi menghubungi api.sandbox.midtrans.com atau api.midtrans.com.',
            'Sakelar dan kunci harus sepasang. JANGAN menebak dari awalan kunci: akun Midtrans yang lebih baru memakai awalan "Mid-" untuk Sandbox MAUPUN Production. Panel ini menguji kuncinya langsung ke server Midtrans saat disimpan, lalu memberi tahu lingkungan mana yang menerimanya.',
            'URL notifikasi pembayaran yang didaftarkan di dashboard Midtrans: {APP_URL}/api/payment/callback',
        ],

        // Tautan keluar yang ditampilkan di kartu. Dipilih otomatis mengikuti
        // nilai field 'is_production' yang tersimpan.
        'mode_field' => 'is_production',
        'tautan' => [
            [
                'label'          => 'Buka Dashboard Midtrans',
                'ikon'           => 'bx-link-external',
                'url_sandbox'    => 'https://dashboard.sandbox.midtrans.com/',
                'url_production' => 'https://dashboard.midtrans.com/',
                'catatan'        => 'Tempat menyalin Merchant ID dan kunci. Perlu login akun Midtrans milik instansi.',
            ],
            [
                'label'        => 'Simulator Pembayaran',
                'ikon'         => 'bx-test-tube',
                'url_sandbox'  => 'https://simulator.sandbox.midtrans.com/',
                'hanya_sandbox' => true,
                'catatan'      => 'Alat publik Midtrans untuk menandai transaksi Sandbox sebagai lunas. Tanpa login, dan tidak bisa menyentuh transaksi Production.',
            ],
        ],
        'fields' => [
            'merchant_id' => [
                'label'       => 'Merchant ID',
                'type'        => 'text',
                'min'         => 5,
                'max'         => 50,
                'placeholder' => 'M225547813',
                'config'      => 'services.midtrans.merchant_id',
                'env'         => 'MIDTRANS_MERCHANT_ID',
            ],
            'server_key' => [
                'label'       => 'Server Key (rahasia)',
                'type'        => 'secret',
                'min'         => 20,
                'max'         => 255,
                'placeholder' => 'Mid-server-xxxxxxxxxxxxxxxxxxxx',
                'hint'        => 'Awalan tidak menentukan lingkungan (akun baru memakai Mid- untuk keduanya). Kebenarannya diuji langsung ke Midtrans saat disimpan.',
                'config'      => 'services.midtrans.server_key',
                'env'         => 'MIDTRANS_SERVER_KEY',
            ],
            'client_key' => [
                'label'       => 'Client Key',
                'type'        => 'secret',
                'min'         => 20,
                'max'         => 255,
                'placeholder' => 'Mid-client-xxxxxxxxxxxxxxxxxxxx',
                'config'      => 'services.midtrans.client_key',
                'env'         => 'MIDTRANS_CLIENT_KEY',
            ],
            'is_production' => [
                'label'  => 'Aktifkan Mode Production',
                'type'   => 'boolean',
                'hint'   => 'Matikan selama masih memakai kunci Sandbox.',
                'config' => 'services.midtrans.is_production',
                'env'    => 'MIDTRANS_IS_PRODUCTION',
            ],
        ],
    ],

];
