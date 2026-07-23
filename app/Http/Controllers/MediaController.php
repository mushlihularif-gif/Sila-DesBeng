<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Serve admin avatar from private storage.
     *
     * @param string $filename
     * @return StreamedResponse
     */
    public function adminAvatar($filename)
    {
        // Sanitize filename to prevent path traversal
        $filename = basename($filename);
        if (empty($filename) || str_contains($filename, '..') || str_contains($filename, "\0")) {
            abort(404);
        }

        $path = 'profiles/' . $filename;
        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }
        $fullPath = Storage::disk('local')->path($path);
        return response()->file($fullPath);
    }

    /**
     * Serve user profile picture from private storage.
     *
     * @param string $filename
     * @return StreamedResponse
     */
    public function userProfile($filename)
    {
        // Sanitize filename to prevent path traversal
        $filename = basename($filename);
        if (empty($filename) || str_contains($filename, '..') || str_contains($filename, "\0")) {
            abort(404);
        }

        $path = 'profiles/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('local')->path($path);

        return response()->file($fullPath);
    }

    /**
     * Defense in Depth: Serve KTP/Face image with Dynamic Watermark
     * Mencegah Admin melakukan screenshot data sensitif warga.
     *
     * @param string $filename
     * @return \Illuminate\Http\Response|StreamedResponse
     */
    public function secureKtpImage($filename)
    {
        $filename = basename($filename);
        if (empty($filename) || str_contains($filename, '..') || str_contains($filename, "\0")) {
            abort(404);
        }

        $path = $filename; // KycController saves them as ktp_123.jpg directly in private/
        
        // Wait, where exactly does KycController save them? 
        // Let's assume it's just in storage/app/private/
        // I will use Storage::disk('local')->path($filename);
        // Tapi kita akan cari aman dengan membaca dari lokasi yang benar
        $fullPath = storage_path('app/private/' . $filename);
        
        if (!file_exists($fullPath)) {
            // coba cek kyc folder
            $fullPath = storage_path('app/private/kyc/' . $filename);
            if (!file_exists($fullPath)) {
                // cek root private
                $fullPath = storage_path('app/' . $filename);
                if (!file_exists($fullPath)) abort(404);
            }
        }

        // Membaca file gambar (GD Library)
        $imageContent = file_get_contents($fullPath);
        $img = @imagecreatefromstring($imageContent);

        if ($img !== false) {
            $width = imagesx($img);
            $height = imagesy($img);

            $watermarkColor = imagecolorallocatealpha($img, 255, 0, 0, 75);
            $font = 5;
            
            $text1 = "RAHASIA SILADESBENG";
            $text2 = "VERIFIKASI BUMDES";
            $text3 = date('Y-m-d');
            
            $textWidth = imagefontwidth($font) * strlen($text1);
            
            $x = ($width - $textWidth) / 2;
            $y = ($height - (imagefontheight($font) * 3)) / 2;
            
            imagestring($img, $font, $x, $y, $text1, $watermarkColor);
            imagestring($img, $font, $x, $y + 15, $text2, $watermarkColor);
            imagestring($img, $font, $x, $y + 30, $text3, $watermarkColor);
            
            ob_start();
            imagejpeg($img, null, 85);
            $finalImage = ob_get_clean();
            imagedestroy($img);

            return response($finalImage)->header('Content-Type', 'image/jpeg');
        }

        return response()->file($fullPath);
    }
}
