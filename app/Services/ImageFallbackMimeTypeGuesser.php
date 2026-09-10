<?php

namespace App\Services;

use Symfony\Component\Mime\MimeTypeGuesserInterface;

class ImageFallbackMimeTypeGuesser implements MimeTypeGuesserInterface
{
    /**
     * Memeriksa apakah guesser didukung di sistem saat ini.
     */
    public function isGuesserSupported(): bool
    {
        return true;
    }

    /**
     * Menebak MIME type file secara aman menggunakan getimagesize / binary header.
     * Sangat penting untuk shared hosting (cPanel) ketika ekstensi PHP fileinfo dinonaktifkan.
     */
    public function guessMimeType(string $path): ?string
    {
        if (!is_file($path) || !is_readable($path) || filesize($path) === 0) {
            return null;
        }

        // 1. Deteksi gambar biner menggunakan getimagesize (native PHP GD / core)
        $info = @getimagesize($path);
        if ($info && !empty($info['mime']) && str_starts_with($info['mime'], 'image/')) {
            return $info['mime'];
        }

        // 2. Deteksi file vektor SVG
        $header = @file_get_contents($path, false, null, 0, 1024);
        if ($header && (stripos($header, '<svg') !== false || stripos($header, '<?xml') !== false)) {
            if (stripos($header, '<svg') !== false) {
                return 'image/svg+xml';
            }
        }

        // 3. Deteksi PDF
        if ($header && str_starts_with($header, '%PDF-')) {
            return 'application/pdf';
        }

        return null;
    }
}
