<?php

return [
    // Only show social destinations that the operator has configured.
    'social' => [
        'tiktok' => env('SOCIAL_TIKTOK_URL'),
        'facebook' => env('SOCIAL_FACEBOOK_URL'),
        'youtube' => env('SOCIAL_YOUTUBE_URL'),
    ],
];
