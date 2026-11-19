<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class PartnerDocumentService
{
    public function store(UploadedFile $file): string
    {
        $path = $file->store('partner_applications', 'local');
        if (! $path) {
            throw new RuntimeException('Dokumen kemitraan gagal disimpan.');
        }
        return 'private:'.$path;
    }

    public function path(string $reference): string
    {
        $private = str_starts_with($reference, 'private:');
        $path = $private ? substr($reference, 8) : $reference;
        // Only server-generated basenames in this directory can be served.
        abort_unless(preg_match('~^partner_applications/[A-Za-z0-9_-]+\.(pdf|jpg|jpeg|png)$~iD', $path), 404);
        $disk = Storage::disk($private ? 'local' : 'public');
        abort_unless($disk->exists($path), 404);
        return $disk->path($path);
    }

    public function privatize(string $reference): string
    {
        if (str_starts_with($reference, 'private:')) {
            $this->path($reference);
            return $reference;
        }
        $source = $this->path($reference);
        $bytes = file_get_contents($source);
        $private = Storage::disk('local');
        if (! $private->put($reference, $bytes)
            || ! hash_equals(hash('sha256', $bytes), hash_file('sha256', $private->path($reference)))) {
            throw new RuntimeException('Salinan dokumen privat belum terverifikasi.');
        }
        return 'private:'.$reference;
    }
}
