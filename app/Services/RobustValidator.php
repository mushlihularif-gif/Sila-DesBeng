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
        $mimes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        if (!$this->validateMimes($attribute, $value, $mimes)) return false;
        $size = @getimagesize($value->getPathname());
        return $size && $size[0] > 0 && $size[1] > 0
            && $size[0] * $size[1] <= 25000000;
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

        // Nama/ekstensi kiriman klien bukan bukti tipe berkas.
        return false;
    }
}
