<?php

namespace App\Console\Commands;

use App\Models\AdminNotification;
use App\Models\KycVerification;
use App\Services\IdentityFileCleanup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanAbandonedKycDrafts extends Command
{
    protected $signature = 'kyc:clean-drafts {--hours=2 : Usia minimum draft yang dihapus (jam)}';
    protected $description = 'Menghapus draf kedaluwarsa dan mencatat berkas untuk pembersihan yang dapat diulang';

    public function handle(): int
    {
        if (! ctype_digit((string) $this->option('hours')) || (int) $this->option('hours') < 1) {
            $this->error('Usia draf minimal satu jam.');
            return self::FAILURE;
        }
        $threshold = now()->subHours((int) $this->option('hours'));
        $count = 0;
        KycVerification::where('status', 'draft')->where('updated_at', '<', $threshold)
            ->chunkById(100, function ($drafts) use ($threshold, &$count) {
                foreach ($drafts as $draft) {
                    DB::transaction(function () use ($draft, $threshold, &$count) {
                        $draft = KycVerification::lockForUpdate()->find($draft->id);
                        if (! $draft || $draft->status !== 'draft' || $draft->updated_at >= $threshold) {
                            return;
                        }
                        foreach (['ktp_image_path', 'face_image_path'] as $column) {
                            if ($path = $draft->$column) {
                                app(IdentityFileCleanup::class)->enqueue($path);
                                DB::afterCommit(fn () => app(IdentityFileCleanup::class)->attempt($path));
                            }
                        }
                        AdminNotification::where('type', 'kyc')->where('reference_id', $draft->id)->delete();
                        $draft->delete();
                        $count++;
                    });
                }
            });
        $this->info("Berhasil membersihkan {$count} draf; penghapusan berkas yang tertunda akan dicoba ulang.");
        return self::SUCCESS;
    }
}
