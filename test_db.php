<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$region = \App\Models\Region::where('type', 'desa')->first();
if ($region) {
    echo "Region: " . $region->name . "\n";
    $services = $region->services()->get();
    foreach($services as $s) {
        echo "- " . $s->name . " (Active: " . $s->pivot->is_active . ")\n";
    }
} else {
    echo "No desa found\n";
}
