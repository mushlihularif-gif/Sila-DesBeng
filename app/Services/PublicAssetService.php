<?php

namespace App\Services;

class PublicAssetService
{
    public function serve(string $root, string $path, bool $trusted)
    {
        // Laravel sudah mendekode parameter URL. Jangan dekode lagi.
        abort_if(str_contains($path, '..') || str_contains($path, "\0")
            || str_contains($path, '\\') || str_contains($path, ':')
            || preg_match('~(^|/)\.~', $path), 403);
        if (!$trusted) {
            abort_if(preg_match('~^(kyc|verifications)(/|$)~i', $path), 403);
            app(SensitiveUploadAccess::class)->authorize($path);
        }
        $root = realpath($root);
        $file = $root ? realpath($root . DIRECTORY_SEPARATOR . $path) : false;
        abort_unless($root && $file && is_file($file)
            && str_starts_with($file, $root . DIRECTORY_SEPARATOR), 404);
        $types = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'webp' => 'image/webp', 'gif' => 'image/gif', 'bmp' => 'image/bmp',
            'ico' => 'image/x-icon', 'pdf' => 'application/pdf',
        ];
        if ($trusted) {
            $types += ['svg' => 'image/svg+xml', 'css' => 'text/css', 'js' => 'application/javascript',
                'woff' => 'font/woff', 'woff2' => 'font/woff2', 'ttf' => 'font/ttf', 'eot' => 'application/vnd.ms-fontobject'];
        }
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        abort_unless(isset($types[$ext]), 404);
        return response()->file($file, [
            'Content-Type' => $types[$ext],
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => $trusted ? 'public, max-age=604800' : 'private, no-cache',
            'Content-Security-Policy' => $trusted ? "default-src 'none'; style-src 'unsafe-inline'" : "sandbox; default-src 'none'",
        ]);
    }
}
