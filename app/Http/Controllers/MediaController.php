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
        
        // Cek semua kemungkinan path penyimpanan dari berbagai sistem (KYC & Manual)
        $possiblePaths = [
            'kyc/ktp/' . $filename,
            'kyc/face/' . $filename,
            'verifications/ktp/' . $filename,
            'verifications/face/' . $filename,
            $filename
        ];

        $fullPath = null;
        foreach ($possiblePaths as $p) {
            if (\Illuminate\Support\Facades\Storage::disk('private')->exists($p)) {
                $fullPath = storage_path('app/private/' . $p);
                break;
            }
        }

        if (!$fullPath) abort(404, 'File gambar tidak ditemukan.');

        // Membaca file gambar dari storage private
        $fileContent = file_get_contents($fullPath);
        
        // Coba dekripsi jika dienkripsi dengan ChaCha20
        $decryptedContent = \App\Services\FileEncryptionService::decrypt($fileContent);
        
        // Jika hasil dekripsi kosong, asumsikan ini file lama yang belum dienkripsi
        $imageContent = $decryptedContent ?: $fileContent;

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

        // Jika gambar gagal diproses (bukan gambar valid)
        return response($imageContent)->header('Content-Type', 'image/jpeg');
    }
}
