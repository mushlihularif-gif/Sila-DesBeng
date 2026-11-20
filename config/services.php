<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'mobile_client_id' => env('GOOGLE_MOBILE_CLIENT_ID'),
        'allowed_client_ids' => env('GOOGLE_ALLOWED_CLIENT_IDS'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL') ?: rtrim(env('APP_URL', 'http://localhost'), '/') . '/auth/google/callback',
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
        'map_id' => env('GOOGLE_MAPS_MAP_ID', 'DEMO_MAP_ID'),
    ],

    // Kotak masuk Gmail via IMAP (baca-saja) untuk panel kanan dashboard admin.
    // Nilai email & app_password normalnya diisi lewat panel Super Admin dan
    // menimpa nilai .env ini; lihat config/api_providers.php kategori gmail_imap.
    'gmail_inbox' => [
        'email'        => env('GMAIL_INBOX_EMAIL'),
        'app_password' => env('GMAIL_INBOX_APP_PASSWORD'),
        'host'         => env('GMAIL_INBOX_HOST', 'imap.gmail.com'),
        'port'         => env('GMAIL_INBOX_PORT', 993),
    ],

    'ocr_space' => [
        'api_key' => env('OCR_SPACE_API_KEY', 'helloworld'),
    ],

    // Gemini: dipakai chatbot warga dan sebagai cadangan pembacaan KTP
    // kalau OCR.space gagal. Lihat OcrService::extractUsingGemini().
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model'   => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    ],
];
