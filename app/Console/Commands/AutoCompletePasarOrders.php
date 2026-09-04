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

        $orders = PasarOrder::where('status', 'delivered')
            ->whereNotNull('delivered_at')
            ->where('delivered_at', '<=', $cutoffTime)
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            // Ubah status jadi completed
            $order->status = 'completed';
            $order->completion_time = Carbon::now();
            $order->save();

            Log::info("PasarOrder ID {$order->id} otomatis selesai karena lebih dari 2 jam sejak delivered_at.");
            $count++;
        }

        $this->info("Berhasil memproses $count pesanan.");
    }
}
