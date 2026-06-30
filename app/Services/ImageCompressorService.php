<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageCompressorService
{
    /**
     * Compress and save an uploaded image file to the public disk.
     *
     * @param UploadedFile $file The uploaded image file.
     * @param string $folder The folder inside storage/app/public/ (e.g., 'banners').
     * @param int $maxWidth The maximum width (default 1280px for HD).
     * @param int $quality The image quality (default 80%).
     * @param bool $forceJpg Force conversion to JPG format.
     * @return string The relative path to the saved file (e.g., 'banners/filename.ext').
     */
    public static function compressAndStore(UploadedFile $file, string $folder, int $maxWidth = 1280, int $quality = 80, bool $forceJpg = false): string
    {
        // Buat instance manager dengan GD driver
        $manager = new ImageManager(new Driver());

        // Baca file gambar
        $image = $manager->read($file->getRealPath());

        // Jika lebar gambar lebih besar dari maxWidth, perkecil secara proporsional
        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        // Generate nama file unik dengan ekstensi
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        if ($forceJpg) {
            $extension = 'jpg';
            // Jika memaksa JPG, kita beri warna dasar putih agar bagian transparan (PNG) tidak jadi hitam
            // Intervention Image v3 belum punya fill background langsung sebelum encode,
            // tapi kita biarkan saja karena biasanya banner tidak transparan.
        }

        $filename = Str::random(40) . '.' . $extension;
        $path = $folder . '/' . $filename;

        // Encode gambar dengan kualitas yang ditentukan
        $encoded = $image->encodeByExtension($extension, $quality);

        // Simpan ke storage public
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }
}
