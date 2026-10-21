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

    public function secureKtpImage($filename)
    {
        return $this->identityImage($filename, 'ktp');
    }

    public function secureFaceImage($filename)
    {
        return $this->identityImage($filename, 'face');
    }

    private function identityImage(string $filename, string $type)
    {
        abort_unless(preg_match('/\\A[a-zA-Z0-9_.-]+\\z/', $filename)
            && !str_contains($filename, '..'), 404);
        $paths = ["kyc/{$type}/{$filename}", "verifications/{$type}/{$filename}"];
        $column = $type . '_image_path';
        $kyc = \App\Models\KycVerification::whereIn($column, $paths)->first();
        $media = app(\App\Services\IdentityMediaService::class);
        if ($kyc) {
            abort_unless($kyc->status === 'pending' && $kyc->user, 404);
            $media->authorize($kyc->user);
            return $media->render($kyc->$column, $type);
        }
        $column = $type . '_photo_path';
        $owner = \App\Models\User::whereIn($column, $paths)->firstOrFail();
        abort_unless($owner->verification_status === 'pending', 404);
        $media->authorize($owner, true);
        return $media->render($owner->$column, $type);
    }
}
