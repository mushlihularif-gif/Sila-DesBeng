<?php

namespace App\Console\Commands;

use App\Models\KycVerification;
use App\Models\User;
use App\Services\IdentityFileCleanup;
use App\Services\IdentityReviewService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanIdentityFiles extends Command
{
    protected $signature = 'kyc:clean-files {--limit=500 : Maksimum antrean yang dicoba per proses}';
    protected $description = 'Mencoba ulang penghapusan dokumen verifikasi yang sudah selesai atau diganti';

    public function handle(): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000]]);
        if ($limit === false) {
            $this->error('Limit harus 1 sampai 10000.');
            return self::FAILURE;
        }
        // Recover references left by older releases, or a process stopped after commit.
        foreach ([
            KycVerification::whereIn('status', ['approved', 'rejected'])->where(fn ($q) => $q->whereNotNull('ktp_image_path')->orWhereNotNull('face_image_path')),
            User::whereIn('verification_status', ['verified', 'rejected'])->where(fn ($q) => $q->whereNotNull('ktp_photo_path')->orWhereNotNull('face_photo_path')),
        ] as $query) {
            foreach ($query->orderBy('id')->limit($limit)->get() as $record) {
                app(IdentityReviewService::class)->cleanup($record);
            }
        }
        $paths = DB::table('identity_cleanup_queue')->orderBy('last_attempt_at')->orderBy('id')->limit($limit)->pluck('path');
        foreach ($paths as $path) {
            app(IdentityFileCleanup::class)->attempt($path);
        }
        $remaining = DB::table('identity_cleanup_queue')->count();
        $this->info("Antrean penghapusan identitas tersisa: {$remaining}.");

        return $remaining ? self::FAILURE : self::SUCCESS;
    }
}
