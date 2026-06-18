<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

var_dump("Kecamatan: " . App\Models\Region::where("type", "kecamatan")->count());
var_dump("Desa: " . App\Models\Region::where("type", "desa")->count());
