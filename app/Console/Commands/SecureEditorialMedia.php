<?php

namespace App\Console\Commands;

use App\Models\{Announcement, AnnouncementImage};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Storage};

class SecureEditorialMedia extends Command
{
    protected $signature = 'content:secure-media';
    protected $description = 'Pindahkan foto kabar lama ke penyimpanan privat tanpa mengganti referensi atau menghilangkan kredit';

    public function handle(): int
    {
        $paths = Announcement::whereNotNull('image_path')->pluck('image_path')
            ->merge(AnnouncementImage::pluck('image_path'))->unique();
        $failed = 0;
        foreach ($paths as $path) {
            if (str_starts_with($path,'editorial/') && !str_contains($path,'..')) continue;
            if (!str_starts_with($path,'announcements/') || str_contains($path,'..')) { $failed++; continue; }
            if (!Storage::disk('public')->exists($path)) {
                if (Storage::disk('private')->exists($path)) {
                    DB::transaction(function () use ($path) {
                        Announcement::where('image_path',$path)->update(['image_disk'=>'private']);
                        AnnouncementImage::where('image_path',$path)->update(['disk'=>'private']);
                    });
                    continue;
                }
                $this->warn('Referensi foto lama tidak ditemukan; periksa galeri terkait.'); $failed++; continue;
            }
            $stream = Storage::disk('public')->readStream($path);
            try { $copied = $stream && Storage::disk('private')->put($path,$stream); }
            finally { if (is_resource($stream)) fclose($stream); }
            if (!$copied || Storage::disk('private')->size($path) !== Storage::disk('public')->size($path)) { $failed++; continue; }
            DB::transaction(function () use ($path) {
                Announcement::where('image_path',$path)->update(['image_disk'=>'private']);
                AnnouncementImage::where('image_path',$path)->update(['disk'=>'private']);
            });
            if (!Storage::disk('public')->delete($path)) $failed++;
        }
        $this->info('Pemindahan foto selesai. Referensi gagal: '.$failed);
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
