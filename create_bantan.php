<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Region;
use Illuminate\Support\Facades\Hash;

$kecBantan = Region::where('name', 'Bantan')->where('type', 'kecamatan')->first();

if ($kecBantan) {
    $userBantan = User::firstOrCreate(
        ['email' => 'adminbantan@isewa.com'],
        [
            'username' => 'adminbantan',
            'name' => 'Admin Kecamatan Bantan',
            'phone' => '087654321099',
            'address' => 'Kantor Camat Bantan',
            'gender' => 'laki-laki',
            'role' => 'admin_kecamatan',
            'status' => 'aktif',
            'region_id' => $kecBantan->id,
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]
    );
    echo "Kecamatan Bantan Admin created/found!\n";
    echo "Email: adminbantan@isewa.com\n";
    echo "Password: password123\n\n";
} else {
    echo "Kecamatan Bantan not found!\n\n";
}

$adminKecamatan = User::where('email', 'adminkecamatan@isewa.com')->with('region')->first();
if ($adminKecamatan) {
    echo "Kecamatan 1 (adminkecamatan@isewa.com) region: " . ($adminKecamatan->region ? $adminKecamatan->region->name : 'None') . "\n";
}

$adminDesa = User::where('email', 'admindesa@isewa.com')->with('region')->first();
if ($adminDesa) {
    echo "Desa 1 (admindesa@isewa.com) region: " . ($adminDesa->region ? $adminDesa->region->name : 'None') . "\n";
}
