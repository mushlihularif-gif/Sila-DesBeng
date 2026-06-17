<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo \App\Models\Region::where('name', 'like', '%Bandar%')->count();
echo "\n";
echo \App\Models\Region::where('name', 'like', '%Api-Api%')->count();
