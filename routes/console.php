<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('pasar:auto-complete')->everyMinute();
Schedule::command('chat:clean-old --days=3')->daily(); 
Schedule::command('kyc:clean-drafts')->hourly(); 
 