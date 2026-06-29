<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::with(['region', 'roles'])->get();
foreach($users as $user) {
    $role = $user->roles->first() ? $user->roles->first()->name : 'No Role';
    $regionName = $user->region ? $user->region->name : 'None';
    echo "Email: {$user->email} | Role: {$role} | Region: {$regionName}\n";
}
