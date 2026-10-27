<?php

namespace Tests\Feature;

use App\Services\{FirebaseNotificationService, FirebaseService, GoogleIdentityService};
use Firebase\JWT\{CachedKeySet, JWT, Key};
use Google\Auth\Cache\MemoryCacheItemPool;
use Google\Auth\HttpHandler\HttpClientCache;
use GuzzleHttp\{Client, HandlerStack, Middleware};
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\{HttpFactory, Response};
use Illuminate\Support\Facades\{Http, Log};
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;

class GoogleAuthenticationDependencyTest extends TestCase
{
    private string $privateKey = '';
    private array $publicKey;

    protected function setUp(): void
    {
        parent::setUp();
        $options = ['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048];
        $windowsConfig = dirname(PHP_BINARY).'/extras/ssl/openssl.cnf';
        if (is_file($windowsConfig)) {
            $options['config'] = $windowsConfig;
        }
        $key = openssl_pkey_new($options);
        openssl_pkey_export($key, $this->privateKey, null, $options);
        $this->publicKey = openssl_pkey_get_details($key);
        config(['services.google.client_id' => 'test-client.apps.googleusercontent.com']);
    }

    private function verifier(): GoogleIdentityService
    {
        $jwks = ['keys' => [[
            'kty' => 'RSA', 'kid' => 'test-key', 'alg' => 'RS256', 'use' => 'sig',
            'n' => JWT::urlsafeB64Encode($this->publicKey['rsa']['n']),
            'e' => JWT::urlsafeB64Encode($this->publicKey['rsa']['e']),
        ]]];
        $http = new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode($jwks)),
        ]))]);
        $keys = new CachedKeySet('https://www.googleapis.com/oauth2/v3/certs', $http, new HttpFactory(), new MemoryCacheItemPool());

        return new class($keys) extends GoogleIdentityService {
            public function __construct(private CachedKeySet $keys) {}
            protected function signingKeys(): CachedKeySet { return $this->keys; }
        };
    }

    private function claims(): array
    {
        return [
            'iss' => 'https://accounts.google.com', 'aud' => config('services.google.client_id'),
            'sub' => 'verified-subject', 'email' => 'test@example.com', 'email_verified' => true,
            'iat' => time() - 10, 'exp' => time() + 3600,
        ];
    }

    private function assertRejected(string $token): void
    {
        try {
            $this->verifier()->verify($token);
            $this->fail('Invalid identity token accepted');
        } catch (HttpExceptionInterface $e) {
            $this->assertSame(401, $e->getStatusCode());
        }
    }

    public function test_signed_google_identity_is_verified_without_the_full_api_client(): void
    {
        foreach (['https://accounts.google.com', 'accounts.google.com'] as $issuer) {
            $claims = array_replace($this->claims(), ['iss' => $issuer]);
            $token = JWT::encode($claims, $this->privateKey, 'RS256', 'test-key');
            $this->assertSame($claims, $this->verifier()->verify($token));
        }
    }

    public function test_invalid_identity_claims_are_rejected(): void
    {
        foreach ([
            ['aud' => 'other-client'], ['iss' => 'https://attacker.example'],
            ['email_verified' => false], ['email_verified' => 'true'],
            ['email' => 'invalid'], ['sub' => ''], ['sub' => ['not-a-subject']],
            ['exp' => time() - 60], ['exp' => null], ['nbf' => time() + 3600],
        ] as $override) {
            $this->assertRejected(JWT::encode(array_replace($this->claims(), $override), $this->privateKey, 'RS256', 'test-key'));
        }
    }

    public function test_forged_signatures_unknown_keys_and_algorithm_confusion_are_rejected(): void
    {
        $token = JWT::encode($this->claims(), $this->privateKey, 'RS256', 'test-key');
        [$header, $payload] = explode('.', $token);
        $this->assertRejected($header.'.'.$payload.'.'.JWT::urlsafeB64Encode(str_repeat('x', 256)));
        $this->assertRejected(JWT::encode($this->claims(), $this->privateKey, 'RS256', 'unknown-key'));
        $this->assertRejected(JWT::encode($this->claims(), str_repeat('secret', 12), 'HS256', 'test-key'));
        $this->assertRejected('malformed-token');
    }

    public function test_both_firebase_services_exchange_scoped_credentials_and_send_with_the_token(): void
    {
        $originalStorage = storage_path();
        $testStorage = sys_get_temp_dir().'/segidaerah-auth-test-'.bin2hex(random_bytes(8));
        mkdir($testStorage.'/app', 0700, true);
        $credentialFile = $testStorage.'/app/firebase-auth.json';
        file_put_contents($credentialFile, json_encode([
            'type' => 'service_account', 'project_id' => 'test-project',
            'client_email' => 'test@test-project.iam.gserviceaccount.com',
            'private_key' => $this->privateKey, 'private_key_id' => 'test-key',
        ]));
        $this->app->useStoragePath($testStorage);
        $requests = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], '{"access_token":"test-access-token","expires_in":3600,"token_type":"Bearer"}'),
            new Response(200, [], '{"access_token":"test-access-token","expires_in":3600,"token_type":"Bearer"}'),
        ]));
        $handler->push(Middleware::history($requests));
        HttpClientCache::setHttpClient(new Client(['handler' => $handler]));
        Http::preventStrayRequests();
        Http::fake(['https://fcm.googleapis.com/*' => Http::response(['name' => 'test-message'])]);
        Log::spy();

        try {
            foreach ([FirebaseService::class, FirebaseNotificationService::class] as $service) {
                $this->assertTrue((new $service())->sendPushNotification('test-device-token', 'Judul', 'Isi', ['type' => 'test']));
            }
            Http::assertSentCount(2);
            Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-access-token')
                && $request->url() === 'https://fcm.googleapis.com/v1/projects/test-project/messages:send'
                && $request['message']['token'] === 'test-device-token');
            $this->assertCount(2, $requests);
            foreach ($requests as $transaction) {
                $this->assertSame('https://oauth2.googleapis.com/token', (string) $transaction['request']->getUri());
                parse_str((string) $transaction['request']->getBody(), $form);
                $assertion = JWT::decode($form['assertion'], new Key($this->publicKey['key'], 'RS256'));
                $this->assertSame('https://www.googleapis.com/auth/firebase.messaging', $assertion->scope);
                $this->assertSame('test@test-project.iam.gserviceaccount.com', $assertion->iss);
            }
        } finally {
            HttpClientCache::setHttpClient(null);
            $this->app->useStoragePath($originalStorage);
            unlink($credentialFile);
            rmdir($testStorage.'/app');
            rmdir($testStorage);
        }
    }
}
