<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FileEncryptionService
{
    private const ENCRYPTED_PREFIX = '$chacha20$';

    /**
     * Encrypt raw file content
     *
     * @param string $fileContent
     * @return string
     */
    public static function encrypt(string $fileContent): string
    {
        if (empty($fileContent)) {
            return $fileContent;
        }

        // Cegah double-encryption
        if (str_starts_with($fileContent, self::ENCRYPTED_PREFIX)) {
            return $fileContent;
        }

        try {
            $encryptionKey = self::deriveKey();
            $nonce = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES);
            $ciphertext = sodium_crypto_aead_chacha20poly1305_ietf_encrypt(
                $fileContent,
                '',     
                $nonce,
                $encryptionKey
            );
            sodium_memzero($encryptionKey);
            return self::ENCRYPTED_PREFIX . base64_encode($nonce . $ciphertext);
        } catch (\SodiumException $e) {
            Log::error("FileEncryptionService Encrypt Error: " . $e->getMessage());
            // Jika error, return asli (fallback agar data tak hilang, meski idealnya throw exception)
            return $fileContent;
        }
    }

    /**
     * Decrypt raw file content
     *
     * @param string $encryptedContent
     * @return string|null
     */
    public static function decrypt(string $encryptedContent): ?string
    {
        if (empty($encryptedContent)) {
            return $encryptedContent;
        }

        if (!str_starts_with($encryptedContent, self::ENCRYPTED_PREFIX)) {
            // Berarti file ini belum dienkripsi (legacy file)
            return $encryptedContent;
        }

        try {
            $encryptionKey = self::deriveKey();
            $encoded = substr($encryptedContent, strlen(self::ENCRYPTED_PREFIX));
            $decoded = base64_decode($encoded, true);

            if ($decoded === false) {
                return null;
            }

            $nonceLength = SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES;
            if (strlen($decoded) < $nonceLength) {
                return null;
            }

            $nonce = substr($decoded, 0, $nonceLength);
            $ciphertext = substr($decoded, $nonceLength);

            $plaintext = sodium_crypto_aead_chacha20poly1305_ietf_decrypt(
                $ciphertext,
                '',
                $nonce,
                $encryptionKey
            );

            sodium_memzero($encryptionKey);
            return $plaintext !== false ? $plaintext : null;
        } catch (\SodiumException $e) {
            Log::error("FileEncryptionService Decrypt Error: " . $e->getMessage());
            return null;
        }
    }

    private static function deriveKey(): string
    {
        $appKey = config('app.key');
        if (empty($appKey)) {
            throw new \RuntimeException('APP_KEY belum di-set.');
        }

        if (str_starts_with($appKey, 'base64:')) {
            $key = base64_decode(substr($appKey, 7));
        } else {
            $key = $appKey;
        }

        $requiredLength = SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_KEYBYTES;
        if (strlen($key) !== $requiredLength) {
            $key = sodium_crypto_generichash($key, '', $requiredLength);
        }

        return $key;
    }
}
