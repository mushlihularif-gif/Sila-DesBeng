<?php

return [
    'enabled' => env('NOTIFICATION_CHANNELS_ENABLED', true),
    'webhook_secret' => env('FONNTE_WEBHOOK_SECRET', ''),
    'android_package' => env('ANDROID_APP_PACKAGE', ''),
    'android_fingerprints' => array_filter(explode(',', env('ANDROID_APP_SHA256_FINGERPRINTS', ''))),
    'categories' => [
        'account'=>'Akun dan verifikasi', 'announcements'=>'Pengumuman wilayah', 'news'=>'Berita Daerah',
        'market'=>'Ketersediaan alat dan layanan', 'orders'=>'Pesanan dan transaksi',
        'review'=>'Peninjauan kiriman', 'other'=>'Pemberitahuan layanan lainnya',
    ],
];
