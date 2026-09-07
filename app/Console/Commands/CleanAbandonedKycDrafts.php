<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanAbandonedKycDrafts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kyc:clean-drafts {--hours=2 : Usia minimum draft yang dihapus (jam)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus draf verifikasi KYC yang tidak diselesaikan oleh warga dan membersihkan file KTP terkait dari disk';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $threshold = now()->subHours($hours);

        $abandonedDrafts = \App\Models\KycVerification::where('status', 'draft')
            ->where('created_at', '<', $threshold)
            ->get();

        $count = 0;
        foreach ($abandonedDrafts as $draft) {
            // Hapus file fisik KTP
            if ($draft->ktp_image_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($draft->ktp_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($draft->ktp_image_path);
            }
            // Hapus file fisik Wajah jika ada
            if ($draft->face_image_path && \Illuminate\Support\Facades\Storage::disk('private')->exists($draft->face_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('private')->delete($draft->face_image_path);
            }
            // Hapus notifikasi terkait jika ada
            \App\Models\AdminNotification::where('type', 'kyc')->where('reference_id', $draft->id)->delete();

            $draft->delete();
            $count++;
        }

        $this->info("Berhasil membersihkan {$count} draf verifikasi identitas yang kedaluwarsa.");
        return 0;
    }
}
