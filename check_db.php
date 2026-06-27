<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$regions = \App\Models\Region::where('type', 'kabupaten')->get();
foreach($regions as $r) {
    echo $r->id . ' - ' . $r->name . PHP_EOL;
}
