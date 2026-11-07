<?php

namespace Tests\Unit;

use App\Http\Middleware\DDoSProtection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DdosProtectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-01-01 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
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

    public function test_one_burst_does_not_ban_a_reader_but_repeated_bursts_do(): void
    {
        config(['ddos.per_detik' => 1, 'ddos.strike' => 2, 'ddos.ban_menit' => 10, 'ddos.whitelist' => []]);
        Cache::setDefaultDriver('array');
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
}
