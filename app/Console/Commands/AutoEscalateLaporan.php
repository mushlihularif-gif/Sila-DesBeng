<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Laporan;
use Illuminate\Support\Facades\Log;

class AutoEscalateLaporan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laporan:auto-escalate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otomatis me-eskalasi laporan warga yang tidak ditanggapi melewati batas SLA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai pengecekan SLA Laporan Warga (Zero-Bottleneck)...');

        // Cari semua laporan yang masih Pending atau Proses
        $laporans = Laporan::whereIn('status', ['Pending', 'Proses'])
            ->whereNotNull('escalation_level')
            ->get();

        $escalatedCount = 0;

        foreach ($laporans as $laporan) {
            // Cek apakah sudah melebihi batas waktu (SLA)
            if ($laporan->isOverdue() && $laporan->canBeEscalated()) {
                $oldLevel = $laporan->escalation_level;
                
                // Lakukan eskalasi otomatis (tanpa handler_id karena dilakukan oleh sistem)
                $laporan->escalateTo(null, "Eskalasi otomatis oleh sistem karena melewati batas SLA tingkat {$oldLevel}");
                
                $newLevel = $laporan->escalation_level;
                
                $this->line("Laporan #{$laporan->id} di-eskalasi: {$oldLevel} -> {$newLevel}");
                Log::info("Sistem me-eskalasi laporan #{$laporan->id} secara otomatis dari {$oldLevel} ke {$newLevel}");
                
                $escalatedCount++;
            }
        }

        $this->info("Pengecekan selesai. Total {$escalatedCount} laporan berhasil di-eskalasi secara otomatis.");
        return Command::SUCCESS;
    }
}
