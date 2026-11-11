<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('chat:clean-old --days=3')->daily(); 
Schedule::command('kyc:clean-drafts')->hourly(); 
Schedule::command('content:maintain')->everyMinute()->withoutOverlapping();
Schedule::command('content:maintain --clean-media')->dailyAt('02:30')->withoutOverlapping();
Schedule::command('notifications:deliver --limit=30')->everyMinute()->withoutOverlapping();
 
