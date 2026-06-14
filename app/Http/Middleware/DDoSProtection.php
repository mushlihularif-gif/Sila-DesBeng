<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Skenario 32: Advanced DDoS Protection Middleware
 * 
 * Pertahanan berlapis terhadap serangan DDoS (Distributed Denial of Service):
 * - Layer 1: Rate limiting per IP (maks 100 request/menit)
 * - Layer 2: Auto-ban IP yang melebihi batas (blokir 30 menit)
 * - Layer 3: Deteksi pola serangan (request terlalu cepat)
 * - Layer 4: Challenge untuk request mencurigakan
 * 
 * Mengapa: Serangan DDoS membanjiri server dengan jutaan request palsu
 * hingga server kehabisan RAM/CPU dan web mati total.
 */
class DDoSProtection
{
    /**
     * Konfigurasi DDoS Protection
     */
    private const MAX_REQUESTS_PER_MINUTE = 100;    // Maks request per menit per IP
    private const MAX_REQUESTS_PER_SECOND = 10;     // Maks request per detik per IP  
    private const BAN_DURATION_MINUTES = 30;         // Durasi ban dalam menit
    private const STRIKE_THRESHOLD = 3;              // Jumlah pelanggaran sebelum ban
    private const SUSPICIOUS_THRESHOLD = 60;         // Request per menit dianggap mencurigakan

    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        
        // Skip untuk localhost/development
        if (in_array($ip, ['127.0.0.1', '::1']) && app()->environment('local')) {
            return $next($request);
        }

        // Layer 1: Cek apakah IP sudah di-ban
        if ($this->isIpBanned($ip)) {
            Log::warning('DDOS_PROTECTION: Blocked banned IP', [
                'ip' => $ip,
                'path' => $request->path(),
            ]);
            
            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'IP Anda telah diblokir sementara karena aktivitas mencurigakan.',
                'retry_after' => self::BAN_DURATION_MINUTES . ' menit',
            ], 429);
        }

        // Layer 2: Cek rate limit per detik (deteksi burst/flood)
        $perSecondKey = 'ddos_sec:' . $ip;
        $perSecondCount = Cache::get($perSecondKey, 0);
        
        if ($perSecondCount >= self::MAX_REQUESTS_PER_SECOND) {
            $this->addStrike($ip, $request);
            
            return response()->json([
                'error' => 'Rate Limit Exceeded',
                'message' => 'Terlalu banyak request per detik. Silakan coba lagi.',
            ], 429);
        }
        
        Cache::put($perSecondKey, $perSecondCount + 1, now()->addSecond());

        // Layer 3: Cek rate limit per menit
        $perMinuteKey = 'ddos_min:' . $ip;
        $perMinuteCount = Cache::get($perMinuteKey, 0);
        
        if ($perMinuteCount >= self::MAX_REQUESTS_PER_MINUTE) {
            $this->addStrike($ip, $request);
            
            return response()->json([
                'error' => 'Rate Limit Exceeded', 
                'message' => 'Terlalu banyak request. Silakan tunggu 1 menit.',
            ], 429);
        }
        
        Cache::put($perMinuteKey, $perMinuteCount + 1, now()->addMinute());

        // Layer 4: Log jika mendekati batas (early warning)
        if ($perMinuteCount >= self::SUSPICIOUS_THRESHOLD) {
            Log::notice('DDOS_PROTECTION: Suspicious activity detected', [
                'ip' => $ip,
                'requests_per_minute' => $perMinuteCount,
                'path' => $request->path(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Layer 5: Deteksi User-Agent kosong atau bot
        $userAgent = $request->userAgent();
        if (empty($userAgent) || $this->isMaliciousBot($userAgent)) {
            Log::warning('DDOS_PROTECTION: Suspicious User-Agent blocked', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'path' => $request->path(),
            ]);
            
            // Jangan langsung blokir, tapi tambahkan delay
            usleep(500000); // 500ms delay untuk memperlambat bot
        }

        $response = $next($request);

        // Tambahkan rate limit headers ke response
        $response->headers->set('X-RateLimit-Limit', (string) self::MAX_REQUESTS_PER_MINUTE);
        $response->headers->set('X-RateLimit-Remaining', (string) max(0, self::MAX_REQUESTS_PER_MINUTE - $perMinuteCount));

        return $response;
    }

    /**
     * Cek apakah IP sudah di-ban
     */
    private function isIpBanned(string $ip): bool
    {
        return Cache::has('ddos_ban:' . $ip);
    }

    /**
     * Tambahkan strike pada IP dan ban jika melebihi threshold
     */
    private function addStrike(string $ip, Request $request): void
    {
        $strikeKey = 'ddos_strike:' . $ip;
        $strikes = Cache::get($strikeKey, 0) + 1;
        Cache::put($strikeKey, $strikes, now()->addHour());

        if ($strikes >= self::STRIKE_THRESHOLD) {
            // BAN IP
            Cache::put('ddos_ban:' . $ip, true, now()->addMinutes(self::BAN_DURATION_MINUTES));
            
            Log::critical('DDOS_PROTECTION: IP BANNED', [
                'ip' => $ip,
                'strikes' => $strikes,
                'ban_duration' => self::BAN_DURATION_MINUTES . ' minutes',
                'path' => $request->path(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    }

    /**
     * Deteksi User-Agent dari tool hacking/DDoS
     */
    private function isMaliciousBot(string $userAgent): bool
    {
        $maliciousBots = [
            'nikto', 'sqlmap', 'nmap', 'masscan', 'dirbuster', 'gobuster',
            'wfuzz', 'hydra', 'medusa', 'burpsuite', 'zap', 'acunetix',
            'nessus', 'openvas', 'w3af', 'skipfish', 'arachni',
            'goldeneye', 'hulk', 'slowloris', 'torshammer', 'loic',
            'hoic', 'xerxes', 'slowhttptest', 'hping', 'siege',
            'ab', 'wrk', 'bombardier', 'hey', 'vegeta',
        ];

        $lowerAgent = strtolower($userAgent);
        foreach ($maliciousBots as $bot) {
            if (str_contains($lowerAgent, $bot)) {
                return true;
            }
        }

        return false;
    }
}
