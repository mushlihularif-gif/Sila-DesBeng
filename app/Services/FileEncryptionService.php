<?php

namespace App\Services;

use App\Casts\ChaCha20Encrypted;
use App\Models\User;

class FileEncryptionService
{
    // Format yang sama (nonce + ciphertext + tag); OpenSSL mendukung hosting tanpa sodium.
    public static function encrypt(string $fileContent): string
    {
        return (new ChaCha20Encrypted())->set(new User(), 'document', $fileContent, []);
    }

    public static function decrypt(string $encryptedContent): ?string
    {
        try {
            return (new ChaCha20Encrypted())->get(new User(), 'document', $encryptedContent, []);
        } catch (\RuntimeException $e) {
            \Log::warning('Dekripsi dokumen gagal. Periksa kunci dan dukungan cipher.');
            return null;
        }
    }
}
