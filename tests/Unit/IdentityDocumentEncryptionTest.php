<?php

namespace Tests\Unit;

use App\Models\KycVerification;
use App\Services\{FileEncryptionService, IdentityDocumentContext};
use Tests\TestCase;

class IdentityDocumentEncryptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
    }

    private function context(int $id = 25, int $owner = 103, string $type = 'ktp', string $path = 'kyc/ktp/test.enc'): string
    {
        $record = new KycVerification();
        $record->setRawAttributes(['id' => $id, 'user_id' => $owner]);
        $record->exists = true;
        return IdentityDocumentContext::aad($record, $type, $path);
    }

    public function test_bound_document_roundtrip_and_backend_compatibility(): void
    {
        $aad = $this->context();
        $plain = "SYNTHETIC IMAGE BYTES\0\xff";
        $cipher = FileEncryptionService::encrypt($plain, $aad);
        $this->assertStringStartsWith('$chacha20:v2$', $cipher);
        $this->assertSame($plain, FileEncryptionService::decrypt($cipher, $aad, 2));
        $this->assertNotSame($cipher, FileEncryptionService::encrypt($plain, $aad));
        $raw = base64_decode(substr($cipher, strlen('$chacha20:v2$')), true);
        $nonce = substr($raw, 0, 12);
        $this->assertSame(strlen($plain) + 28, strlen($raw));
        $this->assertSame($plain, openssl_decrypt(substr($raw, 12, -16), 'chacha20-poly1305', str_repeat('k', 32), OPENSSL_RAW_DATA, $nonce, substr($raw, -16), $aad));
        if (function_exists('sodium_crypto_aead_chacha20poly1305_ietf_decrypt')) {
            $this->assertSame($plain, sodium_crypto_aead_chacha20poly1305_ietf_decrypt(substr($raw, 12), $aad, $nonce, str_repeat('k', 32)));
        }
        // Independently create an OpenSSL envelope and consume it through the app.
        $nonce = random_bytes(12);
        $tag = '';
        $bytes = openssl_encrypt($plain, 'chacha20-poly1305', str_repeat('k', 32), OPENSSL_RAW_DATA, $nonce, $tag, $aad, 16);
        $this->assertSame($plain, FileEncryptionService::decrypt('$chacha20:v2$'.base64_encode($nonce.$bytes.$tag), $aad, 2));
    }

    public function test_different_submission_owner_type_or_path_is_rejected(): void
    {
        $cipher = FileEncryptionService::encrypt('DATA UJI', $this->context());
        foreach ([$this->context(id: 26), $this->context(owner: 104), $this->context(type: 'face'), $this->context(path: 'kyc/ktp/replacement.enc')] as $wrong) {
            $this->assertNull(FileEncryptionService::decrypt($cipher, $wrong, 2));
        }
    }

    public function test_corruption_wrong_key_and_missing_context_are_rejected(): void
    {
        $aad = $this->context();
        $cipher = FileEncryptionService::encrypt('DATA UJI', $aad);
        $raw = base64_decode(substr($cipher, strlen('$chacha20:v2$')));
        foreach ([0, 12, strlen($raw) - 1] as $offset) {
            $bad = $raw;
            $bad[$offset] = chr(ord($bad[$offset]) ^ 1);
            $this->assertNull(FileEncryptionService::decrypt('$chacha20:v2$'.base64_encode($bad), $aad, 2));
        }
        $this->assertNull(FileEncryptionService::decrypt('$chacha20:v2$'.base64_encode(substr($raw, 0, 20)), $aad, 2));
        $this->assertNull(FileEncryptionService::decrypt('$chacha20:v2$%%%invalid', $aad, 2));
        $this->assertNull(FileEncryptionService::decrypt($cipher, null, 2));
        config(['app.key' => 'base64:'.base64_encode(str_repeat('x', 32))]);
        $this->assertNull(FileEncryptionService::decrypt($cipher, $aad, 2));
    }

    public function test_legacy_compatibility_never_downgrades_a_v2_record(): void
    {
        $aad = $this->context();
        $old = FileEncryptionService::encrypt('OLD IMAGE');
        $this->assertSame('OLD IMAGE', FileEncryptionService::decrypt($old));
        $this->assertSame('OLD PLAIN IMAGE', FileEncryptionService::decrypt('OLD PLAIN IMAGE'));
        foreach ([$old, 'OLD PLAIN IMAGE', '', '$chacha20:v3$unknown'] as $replacement) {
            $this->assertNull(FileEncryptionService::decrypt($replacement, $aad, 2));
        }
        $new = FileEncryptionService::encrypt('NEW IMAGE', $aad);
        $this->assertNull(FileEncryptionService::decrypt($new));
        $this->assertNull(FileEncryptionService::decrypt($new, $aad, 99));
        $this->assertNull(FileEncryptionService::decrypt(str_replace('$chacha20:v2$', '$chacha20$', $new), $aad, 2));
        // Even the old reader cannot authenticate an AAD-bound payload with a changed header.
        $this->assertNull(FileEncryptionService::decrypt(str_replace('$chacha20:v2$', '$chacha20$', $new)));
    }
}
