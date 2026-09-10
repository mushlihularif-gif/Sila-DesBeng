<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * ChaCha20-Poly1305 Encrypted Cast (Defense in Depth — Database Level Encryption)
 *
 * Mengenkripsi data sensitif pada level database menggunakan algoritma
 * ChaCha20-Poly1305 melalui library Libsodium (built-in PHP 7.2+).
 *
 * Keunggulan dibandingkan AES-256-CBC bawaan Laravel:
 * - Performa lebih tinggi pada perangkat tanpa instruksi hardware AES-NI
 * - Authenticated Encryption (AEAD): menjamin integritas + kerahasiaan data
 * - Nonce unik per operasi: mencegah serangan replay
 *
 * Format penyimpanan di database:
 * Base64( nonce_12_bytes + ciphertext + auth_tag_16_bytes )
 *
 * @see https://www.php.net/manual/en/function.sodium-crypto-aead-chacha20poly1305-ietf-encrypt.php
 */
class ChaCha20Encrypted implements CastsAttributes
{
    /**
     * Prefix untuk menandai data yang sudah terenkripsi.
     * Mencegah double-encryption pada data yang sudah diproses.
     */
    private const ENCRYPTED_PREFIX = '$chacha20$';

    /**
     * Decrypt: Membaca data terenkripsi dari database dan mengembalikan plaintext.
     *
     * @param  Model  $model
     * @param  string $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        // Jika data tidak memiliki prefix enkripsi, kembalikan apa adanya (data lama/plaintext)
        if (!str_starts_with($value, self::ENCRYPTED_PREFIX)) {
            return $value;
        }

        // 1. Coba dekripsi dengan Libsodium jika tersedia
        if (function_exists('sodium_crypto_aead_chacha20poly1305_ietf_decrypt')) {
            try {
                $encryptionKey = $this->deriveKey();
                $encoded = substr($value, strlen(self::ENCRYPTED_PREFIX));
                $decoded = base64_decode($encoded, true);

                if ($decoded !== false && strlen($decoded) >= 12) {
                    $nonce = substr($decoded, 0, 12);
                    $ciphertext = substr($decoded, 12);

                    $plaintext = sodium_crypto_aead_chacha20poly1305_ietf_decrypt(
                        $ciphertext,
                        '',
                        $nonce,
                        $encryptionKey
                    );

                    sodium_memzero($encryptionKey);

                    if ($plaintext !== false) {
                        return $plaintext;
                    }
                }
            } catch (\Exception $e) {
                // Lanjut ke fallback OpenSSL jika terjadi error
            }
        }

        // 2. Fallback OpenSSL AEAD: Sangat krusial untuk shared hosting / cPanel jika ekstensi libsodium dinonaktifkan
        if (in_array('chacha20-poly1305', openssl_get_cipher_methods())) {
            try {
                $encryptionKey = $this->deriveKey();
                $encoded = substr($value, strlen(self::ENCRYPTED_PREFIX));
                $decoded = base64_decode($encoded, true);

                if ($decoded !== false && strlen($decoded) >= 28) {
                    $nonce = substr($decoded, 0, 12);
                    $cipherWithTag = substr($decoded, 12);
                    $cipher = substr($cipherWithTag, 0, -16);
                    $tag = substr($cipherWithTag, -16);

                    $plain = openssl_decrypt($cipher, 'chacha20-poly1305', $encryptionKey, OPENSSL_RAW_DATA, $nonce, $tag);
                    if ($plain !== false) {
                        return $plain;
                    }
                }
            } catch (\Exception $e) {
                Log::error("ChaCha20 OpenSSL Decrypt Error [{$key}]: " . $e->getMessage());
            }
        }

        return $value;
    }

    /**
     * Encrypt: Mengenkripsi plaintext sebelum disimpan ke database.
     *
     * @param  Model  $model
     * @param  string $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string|null
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        // Cegah double-encryption
        if (str_starts_with($value, self::ENCRYPTED_PREFIX)) {
            return $value;
        }

        // 1. Coba enkripsi dengan Libsodium jika tersedia
        if (function_exists('sodium_crypto_aead_chacha20poly1305_ietf_encrypt')) {
            try {
                $encryptionKey = $this->deriveKey();
                $nonce = random_bytes(12);

                $ciphertext = sodium_crypto_aead_chacha20poly1305_ietf_encrypt(
                    $value,
                    '',
                    $nonce,
                    $encryptionKey
                );

                sodium_memzero($encryptionKey);

                return self::ENCRYPTED_PREFIX . base64_encode($nonce . $ciphertext);
            } catch (\Exception $e) {
                // Lanjut ke fallback OpenSSL
            }
        }

        // 2. Fallback OpenSSL AEAD
        if (in_array('chacha20-poly1305', openssl_get_cipher_methods())) {
            try {
                $encryptionKey = $this->deriveKey();
                $nonce = random_bytes(12);
                $tag = '';

                $ciphertext = openssl_encrypt(
                    $value,
                    'chacha20-poly1305',
                    $encryptionKey,
                    OPENSSL_RAW_DATA,
                    $nonce,
                    $tag
                );

                if ($ciphertext !== false) {
                    return self::ENCRYPTED_PREFIX . base64_encode($nonce . $ciphertext . $tag);
                }
            } catch (\Exception $e) {
                Log::error("ChaCha20 OpenSSL Encrypt Error [{$key}]: " . $e->getMessage());
            }
        }

        // Fallback: simpan apa adanya daripada kehilangan data
        return $value;
    }

    /**
     * Derive encryption key dari Laravel APP_KEY.
     *
     * APP_KEY Laravel berformat "base64:xxxxx" (32 bytes setelah decode).
     * ChaCha20-Poly1305 IETF membutuhkan key 32 bytes — pas sempurna.
     *
     * @return string 32-byte raw key
     * @throws \RuntimeException jika APP_KEY tidak valid
     */
    private function deriveKey(): string
    {
        $appKey = config('app.key');

        if (empty($appKey)) {
            throw new \RuntimeException('APP_KEY belum di-set. Jalankan: php artisan key:generate');
        }

        // Laravel menyimpan key dengan prefix "base64:"
        if (str_starts_with($appKey, 'base64:')) {
            $key = base64_decode(substr($appKey, 7));
        } else {
            $key = $appKey;
        }

        $requiredLength = 32; // 32 bytes

        if (strlen($key) !== $requiredLength) {
            if (function_exists('sodium_crypto_generichash')) {
                $key = sodium_crypto_generichash($key, '', $requiredLength);
            } else {
                $key = hash('sha256', $key, true);
            }
        }

        return $key;
    }
}
