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
                'rules'       => ['starts_with:AIza'],
                'placeholder' => 'AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
                'hint'        => 'Selalu diawali AIza, panjangnya umumnya 39 karakter',
                'config'      => 'services.google_maps.api_key',
                'env'         => 'GOOGLE_MAPS_API_KEY',
            ],
        ],
    ],

    'midtrans' => [
        'label'       => 'Payment Gateway — Midtrans',
        'icon'        => 'bx-credit-card',
        'description' => 'Kredensial pembayaran yang dipakai seluruh transaksi dari semua desa/kecamatan.',
        'console_url' => 'https://dashboard.midtrans.com/settings/config_info',
        'notes'       => [
            'Kunci Sandbox dan Production berbeda. Pastikan sakelar Mode Production di bawah sesuai dengan kunci yang dimasukkan.',
            'URL notifikasi pembayaran di dashboard Midtrans: {APP_URL}/api/payment/callback',
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
                'placeholder' => 'SB-Mid-server-xxxxxxxxxxxxxxxxxxxx',
                'hint'        => 'Sandbox diawali SB-Mid-server-, production diawali Mid-server-',
                'config'      => 'services.midtrans.server_key',
                'env'         => 'MIDTRANS_SERVER_KEY',
            ],
            'client_key' => [
                'label'       => 'Client Key',
                'type'        => 'secret',
                'min'         => 20,
                'max'         => 255,
                'placeholder' => 'SB-Mid-client-xxxxxxxxxxxxxxxxxxxx',
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
