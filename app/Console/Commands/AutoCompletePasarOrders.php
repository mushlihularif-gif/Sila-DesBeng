<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PasarOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoCompletePasarOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pasar:auto-complete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Selesaikan pesanan Pasar Daerah otomatis jika sudah lewat 2 jam dari waktu pengiriman (delivered_at)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Mencari pesanan Pasar Daerah yang telah dikirim (delivered) > 2 jam...');

        // Cari pesanan yang statusnya 'delivered' dan delivered_at lebih dari 2 jam lalu
        $cutoffTime = Carbon::now()->subHours(2);

        $count = 0;
        PasarOrder::where('status', 'delivered')->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', $cutoffTime)->select('id')->chunkById(100, function ($orders) use (&$count, $cutoffTime) {
                foreach ($orders as $candidate) {
                    $changed = \Illuminate\Support\Facades\DB::transaction(function () use ($candidate, $cutoffTime) {
                        $order = PasarOrder::lockForUpdate()->find($candidate->id);
                        if (!$order || $order->status !== 'delivered' || !$order->delivered_at
                            || $order->delivered_at->gt($cutoffTime) || $order->complaint()->where('status', 'pending')->exists()) return false;
                        $order->update(['status' => 'completed', 'completion_time' => now()]);
                        return true;
                    });
                    if ($changed) $count++;
                }
            });

        $this->info("Berhasil memproses $count pesanan.");
    }
}
