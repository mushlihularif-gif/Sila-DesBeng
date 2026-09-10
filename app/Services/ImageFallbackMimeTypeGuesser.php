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
     * Deteksi MIME type langsung dari magic bytes biner berkas.
     * Tidak membutuhkan ekstensi fileinfo, GD, maupun shell_exec.
     */
    public static function detectMimeFromBinary(string $path): ?string
    {
        if (!file_exists($path) || !is_readable($path) || filesize($path) === 0) {
            return null;
        }

        $handle = @fopen($path, 'rb');
        if (!$handle) {
            return null;
        }

        $bytes = @fread($handle, 16);
        @fclose($handle);

        if (!$bytes || strlen($bytes) < 4) {
            return null;
        }

        // JPEG: FF D8 FF
        if (str_starts_with($bytes, "\xFF\xD8\xFF")) {
            return 'image/jpeg';
        }

        // PNG: \x89PNG\r\n\x1a\n
        if (str_starts_with($bytes, "\x89PNG\r\n\x1a\n") || str_starts_with($bytes, "\x89PNG")) {
            return 'image/png';
        }

        // GIF: GIF87a or GIF89a
        if (str_starts_with($bytes, 'GIF87a') || str_starts_with($bytes, 'GIF89a')) {
            return 'image/gif';
        }

        // WebP: RIFF....WEBP
        if (str_starts_with($bytes, 'RIFF') && substr($bytes, 8, 4) === 'WEBP') {
            return 'image/webp';
        }

        // BMP: BM
        if (str_starts_with($bytes, 'BM')) {
            return 'image/bmp';
        }

        // SVG: XML or <svg
        $content = @file_get_contents($path, false, null, 0, 1024);
        if ($content && (stripos($content, '<svg') !== false || (stripos($content, '<?xml') !== false && stripos($content, '<svg') !== false))) {
            return 'image/svg+xml';
        }

        // PDF: %PDF-
        if (str_starts_with($bytes, '%PDF-')) {
            return 'application/pdf';
        }

        return null;
    }

    /**
     * Menebak MIME type file secara aman menggunakan deteksi magic bytes biner & getimagesize.
     * Sangat penting untuk shared hosting (cPanel) ketika ekstensi PHP fileinfo dinonaktifkan.
     */
    public function guessMimeType(string $path): ?string
    {
        if (!is_file($path) || !is_readable($path) || filesize($path) === 0) {
            return null;
        }

        // 1. Deteksi cepat via Magic Bytes (100% independen tanpa modul eksternal)
        $binaryMime = self::detectMimeFromBinary($path);
        if ($binaryMime) {
            return $binaryMime;
        }

        // 2. Deteksi gambar via getimagesize native PHP
        $info = @getimagesize($path);
        if ($info && !empty($info['mime']) && str_starts_with($info['mime'], 'image/')) {
            return $info['mime'];
        }

        return null;
    }
}
