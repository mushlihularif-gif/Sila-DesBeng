<?php

namespace App\Services;

use App\Casts\ChaCha20Encrypted;
use App\Models\User;

class FileEncryptionService
{
    // Format yang sama (nonce + ciphertext + tag); OpenSSL mendukung hosting tanpa sodium.
    public static function encrypt(string $fileContent, ?string $additionalData = null): string
    {
        return (new ChaCha20Encrypted($additionalData))->set(new User(), 'document', $fileContent, []);
    }

    public static function decrypt(string $encryptedContent, ?string $additionalData = null, int $expectedVersion = 1): ?string
    {
        try {
            // Version is trusted DB metadata, not inferred from attacker-controlled bytes.
            if ($expectedVersion === 2) {
                if (!$additionalData || !str_starts_with($encryptedContent, '$chacha20:v2$')) {
                    throw new \RuntimeException('Dokumen wajib menggunakan enkripsi versi 2.');
                }
                return (new ChaCha20Encrypted($additionalData))->get(new User(), 'document', $encryptedContent, []);
            }
            if ($expectedVersion !== 1 || str_starts_with($encryptedContent, '$chacha20:')) {
                throw new \RuntimeException('Versi dokumen tidak didukung.');
            }
            // Legacy records only: preserve existing encrypted/plain image compatibility.
            return (new ChaCha20Encrypted())->get(new User(), 'document', $encryptedContent, []);
        } catch (\RuntimeException $e) {
            \Log::warning('Dekripsi dokumen gagal. Periksa kunci dan dukungan cipher.');
            return null;
        }
    }
}
