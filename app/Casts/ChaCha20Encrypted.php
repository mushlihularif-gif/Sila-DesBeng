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

        try {
            $encryptionKey = $this->deriveKey();

            // Hapus prefix dan decode Base64
            $encoded = substr($value, strlen(self::ENCRYPTED_PREFIX));
            $decoded = base64_decode($encoded, true);

            if ($decoded === false) {
                Log::warning("ChaCha20 Decrypt: Base64 decode gagal untuk field [{$key}]");
                return $value;
            }

            // Pisahkan nonce (12 bytes pertama) dari ciphertext
            $nonceLength = SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES; // 12 bytes

            if (strlen($decoded) < $nonceLength) {
                Log::warning("ChaCha20 Decrypt: Data terlalu pendek untuk field [{$key}]");
                return $value;
            }

            $nonce = substr($decoded, 0, $nonceLength);
            $ciphertext = substr($decoded, $nonceLength);

            // Decrypt menggunakan ChaCha20-Poly1305 IETF
            $plaintext = sodium_crypto_aead_chacha20poly1305_ietf_decrypt(
                $ciphertext,
                '',     // Additional Authenticated Data (AAD) — kosong
                $nonce,
                $encryptionKey
            );

            if ($plaintext === false) {
                Log::error("ChaCha20 Decrypt: Dekripsi gagal untuk field [{$key}]. Data mungkin telah dimanipulasi (tampered).");
                return null;
            }

            // Bersihkan memori sensitif
            sodium_memzero($encryptionKey);

            return $plaintext;

        } catch (\SodiumException $e) {
            Log::error("ChaCha20 Decrypt Error [{$key}]: " . $e->getMessage());
            return null;
        }
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

        try {
            $encryptionKey = $this->deriveKey();

            // Generate nonce acak unik (12 bytes untuk IETF variant)
            $nonce = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES);

            // Encrypt menggunakan ChaCha20-Poly1305 IETF (AEAD)
            $ciphertext = sodium_crypto_aead_chacha20poly1305_ietf_encrypt(
                $value,
                '',     // Additional Authenticated Data (AAD) — kosong
                $nonce,
                $encryptionKey
            );

            // Bersihkan memori sensitif
            sodium_memzero($encryptionKey);

            // Format: prefix + Base64( nonce + ciphertext )
            return self::ENCRYPTED_PREFIX . base64_encode($nonce . $ciphertext);

        } catch (\SodiumException $e) {
            Log::error("ChaCha20 Encrypt Error [{$key}]: " . $e->getMessage());
            // Fallback: simpan sebagai plaintext daripada kehilangan data
            return $value;
        }
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

        $requiredLength = SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_KEYBYTES; // 32 bytes

        if (strlen($key) !== $requiredLength) {
            // Jika key bukan 32 bytes, derive menggunakan BLAKE2b hash
            $key = sodium_crypto_generichash($key, '', $requiredLength);
        }

        return $key;
    }
}
