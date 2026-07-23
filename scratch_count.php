<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Services Count: " . \App\Models\Service::count() . "\n";
echo "RegionServices Count: " . \App\Models\RegionService::count() . "\n";
