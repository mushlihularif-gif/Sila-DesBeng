<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UnitChatSession;
use App\Models\PasarChatSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanOldChats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:clean-old {--days=3 : Jumlah hari batas waktu chat}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghapus permanen sesi chat dan pesannya yang sudah tidak aktif (lebih lama dari X hari)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffTime = Carbon::now()->subDays($days);

        $this->info("Menghapus sesi chat yang tidak aktif sejak sebelum {$cutoffTime->toDateTimeString()}...");

        // Menghapus UnitChatSession
        $unitDeleted = UnitChatSession::where('updated_at', '<', $cutoffTime)->delete();
        
        // Menghapus PasarChatSession
        $pasarDeleted = PasarChatSession::where('updated_at', '<', $cutoffTime)->delete();

        $total = $unitDeleted + $pasarDeleted;

        if ($total > 0) {
            Log::info("CleanOldChats: Berhasil menghapus {$unitDeleted} UnitChatSession dan {$pasarDeleted} PasarChatSession yang usianya lebih dari {$days} hari.");
            $this->info("Berhasil menghapus total {$total} sesi chat.");
        } else {
            $this->info("Tidak ada sesi chat lama yang perlu dihapus.");
        }
    }
}
