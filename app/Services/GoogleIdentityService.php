<?php

namespace App\Services;

class GoogleIdentityService
{
    public function verify(string $token): array
    {
        $clientId = config('services.google.client_id');
        abort_unless(is_string($clientId) && $clientId !== '', 503, 'Login Google belum dikonfigurasi.');

        try {
            $payload = (new \Google\Client(['client_id' => $clientId]))->verifyIdToken($token);
        } catch (\Exception $e) {
            $payload = false;
        }

        abort_unless(
            is_array($payload)
            && ($payload['aud'] ?? null) === $clientId
            && in_array($payload['iss'] ?? null, ['accounts.google.com', 'https://accounts.google.com'], true)
            && ($payload['email_verified'] ?? false) === true
            && !empty($payload['sub'])
            && filter_var($payload['email'] ?? '', FILTER_VALIDATE_EMAIL),
            401,
            'Token Google tidak valid. Silakan login ulang.'
        );

        return $payload;
    }
}
