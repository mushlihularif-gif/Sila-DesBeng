<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kecs = \App\Models\Region::where('type', 'kecamatan')->where('name', 'LIKE', '%Bengkalis%')->get();
foreach($kecs as $kec) { 
    echo $kec->id . ' - ' . $kec->name . ' (Children: ' . $kec->children()->count() . ")\n"; 
}
