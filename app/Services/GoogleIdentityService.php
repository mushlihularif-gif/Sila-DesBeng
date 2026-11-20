<?php

namespace App\Services;

use Firebase\JWT\CachedKeySet;
use Firebase\JWT\JWT;
use Google\Auth\Cache\MemoryCacheItemPool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

class GoogleIdentityService
{
    protected function signingKeys(): CachedKeySet
    {
        // Use Google's fixed JWKS endpoint, never a URL supplied by the token.
        return new CachedKeySet(
            'https://www.googleapis.com/oauth2/v3/certs',
            new Client(['timeout' => 10, 'connect_timeout' => 5]),
            new HttpFactory(),
            new MemoryCacheItemPool(),
            3600,
            true
        );
    }

    /**
     * Collect all trusted Google OAuth 2.0 client IDs (web and mobile).
     *
     * @return list<string>
     */
    protected function allowedClientIds(): array
    {
        $raw = [];
        foreach ([
            config('services.google.client_id'),
            config('services.google.mobile_client_id'),
        ] as $single) {
            if (is_string($single) && trim($single) !== '') {
                $raw[] = trim($single);
            }
        }

        $extra = config('services.google.allowed_client_ids');
        if (is_string($extra) && trim($extra) !== '') {
            foreach (explode(',', $extra) as $item) {
                if (trim($item) !== '') {
                    $raw[] = trim($item);
                }
            }
        } elseif (is_array($extra)) {
            foreach ($extra as $item) {
                if (is_string($item) && trim($item) !== '') {
                    $raw[] = trim($item);
                }
            }
        }

        return array_values(array_unique($raw));
    }

    public function verify(string $token): array
    {
        $allowedClientIds = $this->allowedClientIds();
        abort_unless($allowedClientIds !== [], 503, 'Login Google belum dikonfigurasi.');

        try {
            $payload = (array) JWT::decode($token, $this->signingKeys());
        } catch (\Exception $e) {
            $payload = false;
        }

        abort_unless(
            is_array($payload)
            && in_array($payload['aud'] ?? null, $allowedClientIds, true)
            && in_array($payload['iss'] ?? null, ['accounts.google.com', 'https://accounts.google.com'], true)
            && ($payload['email_verified'] ?? false) === true
            && is_string($payload['sub'] ?? null) && $payload['sub'] !== ''
            && is_int($payload['exp'] ?? null) && $payload['exp'] > time()
            && filter_var($payload['email'] ?? '', FILTER_VALIDATE_EMAIL),
            401,
            'Token Google tidak valid. Silakan login ulang.'
        );

        return $payload;
    }
}
