<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Schema, Storage};

class InvalidateExposedAuthentication extends Command
{
    protected $signature = 'security:invalidate-auth {--once : Lewati jika pemulihan 2026-10-20 sudah selesai}';
    protected $description = 'Cabut sesi, remember-me, dan token API lama setelah perbaikan autentikasi';

    public function handle(): int
    {
        $marker = 'security/auth-cutover-2026-10-20';
        $disk = Storage::disk('local');
        if ($this->option('once') && $disk->exists($marker)) {
            $this->info('Pemulihan autentikasi sudah dijalankan.');
            return self::SUCCESS;
        }
        $driver = config('session.driver');
        if (!in_array($driver, ['database', 'file', 'array'], true)) {
            $this->error('Driver sesi ini perlu pencabutan sesi melalui pengelola hosting: ' . $driver);
            return self::FAILURE;
        }
        DB::transaction(function () {
            DB::table('users')->update(['remember_token' => null]);
            if (Schema::hasTable('personal_access_tokens')) {
                DB::table('personal_access_tokens')->delete();
            }
        });
        if ($driver === 'database') {
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))->delete();
        }
        // Hapus hanya file ID sesi, termasuk sesi lama yang pernah terlacak Git.
        $directory = config('session.files');
        if ($directory && is_dir($directory)) {
            foreach (new \DirectoryIterator($directory) as $entry) {
                if ($entry->isFile() && !$entry->isLink() && preg_match('/\A[a-zA-Z0-9]{40}\z/', $entry->getFilename())) {
                    if (!unlink($entry->getPathname())) {
                        throw new \RuntimeException('Gagal mencabut file sesi.');
                    }
                }
            }
        }
        if (!$disk->put($marker, now()->toIso8601String())) {
            throw new \RuntimeException('Gagal menyimpan penanda pemulihan autentikasi.');
        }
        $this->info('Sesi dan token lama dicabut. Pengguna perlu login kembali.');
        return self::SUCCESS;
    }
}
