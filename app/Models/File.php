<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasUuids;
    protected $fillable = ['alias', 'filename', 'path', 'mime_type', 'size'];
    public function fileable()
    {
        return $this->morphTo();
    }
    public function getFileStreamAttribute()
    {
        return route('files.action', ['id' => $this->id, 'action' =>
        'stream']);
    }
    public function getFileDownloadAttribute()
    {
        return route('files.action', ['id' => $this->id, 'action' =>
        'download']);
    }
    public function handleAction($action)
    {
        $disk = null;
        if (Storage::disk('local')->exists($this->path)) {
            $disk = 'local';
        } elseif (Storage::disk('public')->exists($this->path)) {
            $disk = 'public';
        } elseif (Storage::disk('private')->exists($this->path)) {
            $disk = 'private';
        } elseif (file_exists(storage_path('app/' . $this->path))) {
            $disk = 'legacy_app';
        } elseif (file_exists(storage_path('app/private/' . $this->path))) {
            $disk = 'legacy_private';
        } elseif (file_exists(storage_path('app/public/' . $this->path))) {
            $disk = 'legacy_public';
        }

        if (!$disk) {
            if ($action === 'stream' && str_starts_with($this->mime_type ?? '', 'image/')) {
                $fallbackPath = public_path('Admin/img/avatars/pria.png');
                if (file_exists($fallbackPath)) {
                    return response()->file($fallbackPath, [
                        'Content-Type' => 'image/png',
                    ]);
                }
            }
            abort(404, 'File tidak ditemukan');
        }

        $filePath = match ($disk) {
            'local' => Storage::disk('local')->path($this->path),
            'public' => Storage::disk('public')->path($this->path),
            'private' => Storage::disk('private')->path($this->path),
            'legacy_app' => storage_path('app/' . $this->path),
            'legacy_private' => storage_path('app/private/' . $this->path),
            'legacy_public' => storage_path('app/public/' . $this->path),
        };

        if ($action === 'stream') {
            return response()->file($filePath, [
                'Content-Type' => $this->mime_type ?: 'image/jpeg',
                'Cache-Control' => 'no-cache, private',
            ]);
        }
        if ($action === 'download') {
            return response()->download($filePath, $this->filename);
        }
        abort(400, 'Aksi tidak valid');
    }
}
