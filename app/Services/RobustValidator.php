<?php

namespace App\Services;

use Illuminate\Validation\Validator as BaseValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RobustValidator extends BaseValidator
{
    /**
     * Validasi apakah berkas merupakan gambar valid.
     */
    public function validateImage($attribute, $value, $parameters = [])
    {
        $mimes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];
        return $this->validateMimes($attribute, $value, $mimes);
    }

    /**
     * Validasi MIME type berkas dengan fallback ganda getimagesize dan ekstensi berkas.
     */
    public function validateMimes($attribute, $value, $parameters)
    {
        if (! $this->isValidFileInstance($value)) {
            return false;
        }

        if ($this->shouldBlockPhpUpload($value, $parameters)) {
            return false;
        }

        // Normalisasi parameter ekstensi yang diizinkan (misal jpg <=> jpeg)
        $normalized = [];
        foreach ($parameters as $p) {
            $p = strtolower(trim($p));
            $normalized[] = $p;
            if ($p === 'jpg') $normalized[] = 'jpeg';
            if ($p === 'jpeg') $normalized[] = 'jpg';
        }
        $normalized = array_unique($normalized);

        // 1. Coba deteksi standar Symfony / Laravel guessExtension()
        $guessed = $value->guessExtension();
        if ($guessed && in_array(strtolower($guessed), $normalized, true)) {
            return true;
        }

        // Ambil path berkas sementara (pathname dari $_FILES['tmp_name'] paling akurat)
        $path = $value->getPathname() ?: $value->getRealPath();

        // 2. Deteksi langsung via Magic Bytes biner (tidak tergantung ekstensi PHP fileinfo / GD)
        if ($path && file_exists($path) && filesize($path) > 0) {
            $binaryMime = ImageFallbackMimeTypeGuesser::detectMimeFromBinary($path);
            if ($binaryMime) {
                $mimeMap = [
                    'image/jpeg' => ['jpg', 'jpeg'],
                    'image/png' => ['png'],
                    'image/gif' => ['gif'],
                    'image/webp' => ['webp'],
                    'image/bmp' => ['bmp'],
                    'image/svg+xml' => ['svg'],
                    'application/pdf' => ['pdf'],
                ];
                $possibleExts = $mimeMap[$binaryMime] ?? [];
                foreach ($possibleExts as $pe) {
                    if (in_array($pe, $normalized, true)) {
                        return true;
                    }
                }
            }

            // Fallback getimagesize jika format belum tertangkap magic bytes
            $info = @getimagesize($path);
            if ($info && !empty($info['mime'])) {
                $mimeMap = [
                    'image/jpeg' => ['jpg', 'jpeg'],
                    'image/pjpeg' => ['jpg', 'jpeg'],
                    'image/png' => ['png'],
                    'image/x-png' => ['png'],
                    'image/gif' => ['gif'],
                    'image/webp' => ['webp'],
                    'image/bmp' => ['bmp'],
                    'image/x-ms-bmp' => ['bmp'],
                    'image/svg+xml' => ['svg'],
                    'application/pdf' => ['pdf'],
                ];
                $possibleExts = $mimeMap[$info['mime']] ?? [];
                foreach ($possibleExts as $pe) {
                    if (in_array($pe, $normalized, true)) {
                        return true;
                    }
                }
            }
        }

        // 3. Fallback: Ekstensi klien yang diunggah cocok dengan whitelist parameter
        // Berguna saat server shared hosting membatasi akses parser biner atau MIME detection
        $clientExt = strtolower($value->getClientOriginalExtension());
        if ($clientExt && in_array($clientExt, $normalized, true)) {
            $dangerousExts = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'exe', 'sh', 'bat', 'cmd', 'cgi', 'pl', 'py', 'js', 'html', 'htm'];
            if (!in_array($clientExt, $dangerousExts, true)) {
                // Periksa keamanan isi file: tidak boleh mengandung tag script PHP
                if ($path && file_exists($path) && filesize($path) > 0) {
                    $firstChunk = @file_get_contents($path, false, null, 0, 2048);
                    if ($firstChunk && (stripos($firstChunk, '<?php') !== false || stripos($firstChunk, '<?=') !== false)) {
                        return false;
                    }
                }
                return true;
            }
        }

        return false;
    }
}
