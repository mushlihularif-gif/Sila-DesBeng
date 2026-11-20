<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/** Durable deletion intent; enqueue in the same transaction that retires the reference. */
class IdentityFileCleanup
{
    public function enqueue(string $path): void
    {
        if ($path === '' || str_contains($path, '..') || str_contains($path, '\\')
            || str_contains($path, "\0") || str_starts_with($path, '/')) {
            throw new \InvalidArgumentException('Invalid identity document path.');
        }
        DB::table('identity_cleanup_queue')->insertOrIgnore(['path' => $path, 'created_at' => now()]);
    }

    public function attempt(string $path): bool
    {
        // Never delete an unqueued path supplied by a caller.
        if (! DB::table('identity_cleanup_queue')->where('path', $path)->exists()) {
            return false;
        }
        try {
            $disk = Storage::disk('private');
            if ($disk->exists($path) && ! $disk->delete($path)) {
                throw new \RuntimeException('Identity deletion failed.');
            }
            DB::table('identity_cleanup_queue')->where('path', $path)->delete();

            return true;
        } catch (\Throwable $e) {
            DB::table('identity_cleanup_queue')->where('path', $path)->increment('attempts', 1, ['last_attempt_at' => now()]);
            Log::warning('Penghapusan berkas identitas tertunda; scheduler akan mencoba kembali.');

            return false;
        }
    }
}
