<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::updateOrCreate(
    ['email' => 'kabupaten@admin.com'],
    [
        'name' => 'Admin Kabupaten',
        'username' => 'adminkab',
        'password' => Hash::make('password'),
        'role' => 'super_admin' // Assuming super_admin acts as kabupaten
    ]
);

User::updateOrCreate(
    ['email' => 'kecamatan@admin.com'],
    [
        'name' => 'Admin Kecamatan',
        'username' => 'adminkec',
        'password' => Hash::make('password'),
        'role' => 'admin_kecamatan'
    ]
);

User::updateOrCreate(
    ['email' => 'desa@admin.com'],
    [
        'name' => 'Admin Desa',
        'username' => 'admindesa',
        'password' => Hash::make('password'),
        'role' => 'admin_desa'
    ]
);

echo "Users created successfully!\n";
