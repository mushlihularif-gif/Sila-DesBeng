<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('role', 'admin_kecamatan')->first();
if ($user) {
    echo "User: " . $user->name . "\n";
    echo "Region: " . $user->region_id . "\n";
    $services = $user->region->services->pluck('name')->toArray();
    print_r($services);
}

$userAdmin = \App\Models\User::where('role', 'admin')->first();
if ($userAdmin) {
    echo "Admin User: " . $userAdmin->name . "\n";
    echo "Region: " . $userAdmin->region_id . "\n";
    $servicesAdmin = $userAdmin->region->services->pluck('name')->toArray();
    print_r($servicesAdmin);
}
