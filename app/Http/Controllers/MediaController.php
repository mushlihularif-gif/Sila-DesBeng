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
        $resolvedPath = $this->resolveProfilePath($path);

        if ($resolvedPath && file_exists($resolvedPath)) {
            return response()->file($resolvedPath);
        }

        $fallback = public_path('Admin/img/avatars/pria.png');
        if (file_exists($fallback)) {
            return response()->file($fallback, ['Content-Type' => 'image/png']);
        }
        abort(404);
    }

    /**
     * Serve user profile picture from private storage.
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userProfile($filename)
    {
        // Sanitize filename to prevent path traversal
        $filename = basename($filename);
        if (empty($filename) || str_contains($filename, '..') || str_contains($filename, "\0")) {
            abort(404);
        }

        $path = 'profiles/' . $filename;
        $resolvedPath = $this->resolveProfilePath($path);

        if ($resolvedPath && file_exists($resolvedPath)) {
            return response()->file($resolvedPath);
        }

        $fallback = public_path('Admin/img/avatars/pria.png');
        if (file_exists($fallback)) {
            return response()->file($fallback, ['Content-Type' => 'image/png']);
        }
        abort(404);
    }

    /**
     * Resolves profile file path across local, public, and legacy storage paths.
     */
    private function resolveProfilePath(string $relativePath): ?string
    {
        if (Storage::disk('local')->exists($relativePath)) {
            return Storage::disk('local')->path($relativePath);
        }
        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->path($relativePath);
        }
        if (file_exists(storage_path('app/' . $relativePath))) {
            return storage_path('app/' . $relativePath);
        }
        if (file_exists(storage_path('app/private/' . $relativePath))) {
            return storage_path('app/private/' . $relativePath);
        }
        if (file_exists(storage_path('app/public/' . $relativePath))) {
            return storage_path('app/public/' . $relativePath);
        }
        return null;
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
                $fullPath = \Illuminate\Support\Facades\Storage::disk('private')->path($p);
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

        if ($imageContent) {
            try {
                $manager = \Intervention\Image\ImageManager::gd();
                $image = $manager->read($imageContent);

                // Cek apakah file ini adalah KTP (bukan face photo)
                // Deteksi dari nama path atau file. Asumsikan jika ada kata 'face', itu wajah.
                $isFace = str_contains($fullPath, 'face');

                if (!$isFace) {
                    $watermarkPath = public_path('Admin/img/watermarkprivasi/WatermarkPrivasiFIX.png');
                    if (file_exists($watermarkPath)) {
                        $watermark = $manager->read($watermarkPath);
                        $watermarkWidth = intval($image->width() * 0.9);
                        $watermark->scaleDown(width: $watermarkWidth);
                        $image->place($watermark, 'center');
                    }
                }

                $encoded = $image->toJpeg(85);
                $finalImage = $encoded->toString();

                // Bersihkan output buffer sebelumnya (mencegah karakter whitespace/error nyempil di gambar)
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }

                return response($finalImage)->header('Content-Type', 'image/jpeg');
            } catch (\Exception $e) {
                \Log::error('MediaController Image Error: ' . $e->getMessage());
                // Fallback to original decrypted content if manipulation fails
                return response($imageContent)->header('Content-Type', 'image/jpeg');
            }
        }

        // Jika gambar gagal diproses (bukan gambar valid)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        return response($imageContent)->header('Content-Type', 'image/jpeg');
    }

    public function secureFaceImage($filename)
    {
        return $this->secureKtpImage($filename);
    }
}
