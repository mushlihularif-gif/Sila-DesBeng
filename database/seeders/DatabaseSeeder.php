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
        User::create([
            'username' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@isewa.com',
            'phone' => '081234567890',
            'address' => 'Kantor BUMDes Desa Pematang Duku Timur',
            'gender' => 'laki-laki',
            'role' => 'admin',
            'status' => 'aktif',
            'email_verified_at' => now(),
            'password' => Hash::make('admin123'),
        ]);

        // Create Test User
        User::create([
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'user@test.com',
            'phone' => '085678901234',
            'address' => 'Jl. Test No. 123',
            'gender' => 'perempuan',
            'role' => 'user',
            'status' => 'aktif',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        echo "\n✅ Admin created: admin@isewa.com / admin123";
        echo "\n✅ User created: user@test.com / password123\n\n";
    }
}