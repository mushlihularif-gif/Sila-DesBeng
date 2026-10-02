<?php

namespace Tests\Unit;

use App\Casts\ChaCha20Encrypted;
use App\Models\User;
use RuntimeException;
use Tests\TestCase;

class ChaCha20EncryptedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.key' => 'base64:' . base64_encode(str_repeat('k', 32))]);
    }

    public function test_sensitive_value_round_trips_as_ciphertext(): void
    {
        $cast = new ChaCha20Encrypted();
        $model = new User();
        $plain = '1234567890123456';

        $stored = $cast->set($model, 'nik', $plain, []);

        $this->assertStringStartsWith('$chacha20$', $stored);
        $this->assertNotSame($plain, $stored);
        $this->assertSame($plain, $cast->get($model, 'nik', $stored, []));
    }

    public function test_legacy_plaintext_remains_readable_for_existing_records(): void
    {
        $this->assertSame('Data lama', (new ChaCha20Encrypted())->get(new User(), 'name', 'Data lama', []));
    }

    public function test_tampered_ciphertext_fails_closed(): void
    {
        $this->expectException(RuntimeException::class);
        (new ChaCha20Encrypted())->get(new User(), 'nik', '$chacha20$' . base64_encode(random_bytes(40)), []);
    }
}
