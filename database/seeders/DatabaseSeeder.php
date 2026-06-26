<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::firstOrCreate(
            ['email' => 'admin@isewa.com'],
            [
                'username' => 'admin',
                'name' => 'Administrator',
                'phone' => '081234567890',
                'address' => 'Kantor BUMDes Desa Pematang Duku Timur',
                'gender' => 'laki-laki',
                'role' => 'admin',
                'status' => 'aktif',
                'email_verified_at' => now(),
                'password' => Hash::make('admin123'),
            ]
        );

        // Create Test User
        User::firstOrCreate(
            ['email' => 'user@test.com'],
            [
                'username' => 'testuser',
                'name' => 'Test User',
                'phone' => '085678901234',
                'address' => 'Jl. Test No. 123',
                'gender' => 'perempuan',
                'role' => 'user',
                'status' => 'aktif',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
            ]
        );

        // Create Admin Kecamatan User
        $regionKecamatan = \App\Models\Region::where('type', 'kecamatan')->first();
        User::firstOrCreate(
            ['email' => 'adminkecamatan@isewa.com'],
            [
                'username' => 'adminkecamatan',
                'name' => 'Admin Kecamatan',
                'phone' => '087654321098',
                'address' => 'Kantor Camat',
                'gender' => 'laki-laki',
                'role' => 'admin_kecamatan',
                'status' => 'aktif',
                'region_id' => $regionKecamatan ? $regionKecamatan->id : null,
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
            ]
        );

        // Create Admin Desa User
        $regionDesa = \App\Models\Region::where('type', 'desa')->first();
        User::firstOrCreate(
            ['email' => 'admindesa@isewa.com'],
            [
                'username' => 'admindesa',
                'name' => 'Admin Desa',
                'phone' => '089876543210',
                'address' => 'Kantor Kepala Desa',
                'gender' => 'laki-laki',
                'role' => 'admin_desa',
                'status' => 'aktif',
                'region_id' => $regionDesa ? $regionDesa->id : null,
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
            ]
        );

        echo "\n✅ Admin created: admin@isewa.com / admin123";
        echo "\n✅ Admin Kecamatan created: adminkecamatan@isewa.com / password123";
        echo "\n✅ Admin Desa created: admindesa@isewa.com / password123";
        echo "\n✅ User created: user@test.com / password123\n\n";
    }
}