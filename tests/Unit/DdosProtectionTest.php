<?php

namespace Tests\Unit;

use App\Http\Middleware\DDoSProtection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DdosProtectionTest extends TestCase
{
    private ?string $cacheDirectory = null;
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-01-01 12:00:00');
    }

    protected function tearDown(): void
    {
        if ($this->cacheDirectory) {
            $base = realpath(storage_path('framework/testing'));
            $target = realpath($this->cacheDirectory);
            if ($base && $target && str_starts_with($target, $base.DIRECTORY_SEPARATOR.'ddos-')) {
                (new \Illuminate\Filesystem\Filesystem())->deleteDirectory($target);
            }
        }
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_file_cache_expiration_during_increment_does_not_permanently_limit_readers(): void
    {
        $this->cacheDirectory = storage_path('framework/testing/ddos-'.bin2hex(random_bytes(8)));
        $store = new class(new \Illuminate\Filesystem\Filesystem(), $this->cacheDirectory) extends \Illuminate\Cache\FileStore {
            private bool $delayed = false;
            public function increment($key, $value = 1)
            {
                // Reproduce crossing the one-second TTL between add() and increment().
                if (!$this->delayed && str_starts_with($key, 'ddos_sec:')) {
                    $this->delayed = true;
                    Carbon::setTestNow(now()->addSeconds(2));
                }
                return parent::increment($key, $value);
            }
        };
        Cache::extend('ddos-test-file', fn () => Cache::repository($store));
        config(['cache.stores.ddos-test-file' => ['driver'=>'ddos-test-file'],
            'ddos.per_detik'=>1, 'ddos.per_menit'=>100, 'ddos.strike'=>5, 'ddos.whitelist'=>[]]);
        Cache::setDefaultDriver('ddos-test-file');
        $request = Request::create('/kelola-kabar', 'GET', [], [], [], [
            'REMOTE_ADDR'=>'203.0.113.12', 'HTTP_USER_AGENT'=>'Mozilla/5.0', 'HTTP_ACCEPT'=>'application/json',
        ]);
        $middleware = new DDoSProtection();
        $hit = fn () => $middleware->handle($request, fn () => response('ok'));
        $this->assertSame(200, $hit()->getStatusCode());
        $this->travel(2)->seconds();
        $this->assertSame(200, $hit()->getStatusCode(), 'An expired burst must not affect a later second.');
        $this->assertSame(429, $hit()->getStatusCode(), 'Real bursts must still be limited.');
        $this->travel(2)->seconds();
        $this->assertSame(200, $hit()->getStatusCode());
    }

    public function test_burst_limit_uses_atomic_counter_and_returns_429_after_limit(): void
    {
        config([
            'cache.default' => 'array',
            'ddos.per_detik' => 1,
            'ddos.per_menit' => 100,
            'ddos.strike' => 5,
            'ddos.whitelist' => [],
        ]);
        Cache::setDefaultDriver('array');
        Cache::store('array')->flush();

        $middleware = new DDoSProtection();
        $request = Request::create('/contoh', 'GET', [], [], [], [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) Chrome/120 Safari/537.36',
        ]);
        $next = fn () => response('ok');

        $this->assertSame(200, $middleware->handle($request, $next)->getStatusCode());
        $this->assertSame(429, $middleware->handle($request, $next)->getStatusCode());
    }

    public static function cacheDrivers(): array
    {
        return [['array'], ['file']];
    }

    private function useDriver(string $driver): void
    {
        if ($driver === 'file') {
            $this->cacheDirectory = storage_path('framework/testing/ddos-'.bin2hex(random_bytes(8)));
            config(['cache.stores.file.path'=>$this->cacheDirectory, 'cache.stores.file.lock_path'=>$this->cacheDirectory]);
        }
        Cache::setDefaultDriver($driver);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('cacheDrivers')]
    public function test_one_burst_does_not_ban_a_reader_but_repeated_bursts_do(string $driver): void
    {
        config(['ddos.per_detik' => 1, 'ddos.strike' => 2, 'ddos.ban_menit' => 10, 'ddos.whitelist' => []]);
        $this->useDriver($driver);
        $middleware = new DDoSProtection();
        $request = Request::create('/kelola-kabar', 'GET', [], [], [], [
            'REMOTE_ADDR' => '203.0.113.11', 'HTTP_USER_AGENT' => 'Browser', 'HTTP_ACCEPT' => 'text/html',
        ]);
        $hit = fn () => $middleware->handle($request, fn () => response('ok'));
        $this->assertSame(200, $hit()->getStatusCode());
        for ($i = 0; $i < 8; $i++) {
            $limited = $hit();
            $this->assertSame(429, $limited->getStatusCode());
            $this->assertSame('1', $limited->headers->get('Retry-After'));
        }
        $this->assertStringContainsString('<html', $limited->getContent());
        $this->travel(2)->seconds();
        $this->assertSame(200, $hit()->getStatusCode());
        $this->travel(60)->seconds();
        $this->assertSame(200, $hit()->getStatusCode());
        $request->headers->set('Accept', 'application/json');
        $this->assertSame('600', $hit()->headers->get('Retry-After'));
        $this->travel(10)->seconds();
        $banned = $hit();
        $this->assertSame('590', $banned->headers->get('Retry-After'));
        $this->assertSame('Too Many Requests', json_decode($banned->getContent(), true)['error']);
        $this->travel(591)->seconds();
        $this->assertSame(200, $hit()->getStatusCode());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('cacheDrivers')]
    public function test_minute_limit_recovers_and_expired_ban_does_not_block_forever(string $driver): void
    {
        $this->useDriver($driver);
        config(['ddos.per_detik'=>100, 'ddos.per_menit'=>2, 'ddos.strike'=>5, 'ddos.whitelist'=>[]]);
        Cache::forever('ddos_ban:203.0.113.13', now()->subMinute()->timestamp);
        $request = Request::create('/kelola-kabar', 'GET', [], [], [], [
            'REMOTE_ADDR'=>'203.0.113.13', 'HTTP_USER_AGENT'=>'Mozilla/5.0', 'HTTP_ACCEPT'=>'application/json',
        ]);
        $middleware = new DDoSProtection();
        $hit = fn () => $middleware->handle($request, fn () => response('ok'));
        $this->assertSame(200, $hit()->getStatusCode());
        $this->assertSame(200, $hit()->getStatusCode());
        $this->assertSame(429, $hit()->getStatusCode());
        $this->travel(61)->seconds();
        $this->assertSame(200, $hit()->getStatusCode());
    }
}
