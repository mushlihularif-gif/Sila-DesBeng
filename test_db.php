<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
try {
    print_r(\App\Models\Region::where('parent_id', 2)->get()->toArray());
} catch (\Exception $e) {
    echo $e->getMessage();
}
