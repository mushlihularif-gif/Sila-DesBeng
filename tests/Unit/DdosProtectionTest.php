<?php

namespace Tests\Unit;

use App\Http\Middleware\DDoSProtection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DdosProtectionTest extends TestCase
{
    public function test_burst_limit_uses_atomic_counter_and_returns_429_after_limit(): void
    {
        config([
            'cache.default' => 'array',
            'ddos.per_detik' => 1,
            'ddos.per_menit' => 100,
            'ddos.strike' => 5,
            'ddos.whitelist' => [],
        ]);
        Cache::store('array')->flush();

        $middleware = new DDoSProtection();
        $request = Request::create('/contoh', 'GET', [], [], [], [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'SiladesBeng Security Test',
        ]);
        $next = fn () => response('ok');

        $this->assertSame(200, $middleware->handle($request, $next)->getStatusCode());
        $this->assertSame(429, $middleware->handle($request, $next)->getStatusCode());
    }
}
