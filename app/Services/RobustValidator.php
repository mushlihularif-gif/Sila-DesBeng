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

        // 2. Fallback: getimagesize pada file binary langsung (bekerja walau ekstensi fileinfo mati)
        $realPath = $value->getRealPath();
        if ($realPath && file_exists($realPath) && filesize($realPath) > 0) {
            $info = @getimagesize($realPath);
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

            // Cek SVG
            if (in_array('svg', $normalized, true)) {
                $content = @file_get_contents($realPath, false, null, 0, 1024);
                if ($content && stripos($content, '<svg') !== false) {
                    return true;
                }
            }
        }

        // 3. Fallback: Jika getimagesize membaca gambar valid dan ekstensi klien cocok
        $clientExt = strtolower($value->getClientOriginalExtension());
        if ($clientExt && in_array($clientExt, $normalized, true)) {
            if ($realPath && file_exists($realPath) && filesize($realPath) > 0) {
                if (@getimagesize($realPath) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}
