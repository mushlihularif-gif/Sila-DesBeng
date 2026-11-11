<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AutoCompletePasarOrders extends Command
{
    protected $signature = 'pasar:auto-complete';

    protected $description = 'Kompatibilitas cron lama setelah Pasar Daerah dihentikan';

    public function handle(): int
    {
        // The deployed cPanel wrapper still invokes this name before editorial and notifications.
        // Return success without changing orders so those remaining tasks can keep running.
        return self::SUCCESS;
    }
}
