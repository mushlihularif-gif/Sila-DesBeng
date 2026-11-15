<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class FinancialProof
{
    public static function response(?string $path)
    {
        abort_unless($path && ! str_contains($path, '..') && ! str_starts_with($path, '/'), 404);
        // Read old public evidence during migration, but write all new finance evidence privately.
        $disk = Storage::disk('private')->exists($path) ? 'private' : 'public';
        abort_unless(Storage::disk($disk)->exists($path), 404);

        return Storage::disk($disk)->response($path, null, ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
}
