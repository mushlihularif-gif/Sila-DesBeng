<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$services = \App\Models\Service::where('name', 'Fasilitas Umum')->get();
if($services->count() > 1) {
    $services->last()->delete();
    echo "Deleted duplicate service.\n";
} else {
    echo "No duplicates found.\n";
}
