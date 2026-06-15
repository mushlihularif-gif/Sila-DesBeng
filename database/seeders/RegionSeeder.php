<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Services
        $servicePenyewaan = \App\Models\Service::firstOrCreate(['slug' => 'penyewaan-alat'], ['name' => 'Penyewaan Alat']);
        $serviceGas = \App\Models\Service::firstOrCreate(['slug' => 'penjualan-gas'], ['name' => 'Penjualan Gas']);
        $serviceLaporan = \App\Models\Service::firstOrCreate(['slug' => 'pelaporan-warga'], ['name' => 'Pelaporan Warga']);

        // 2. Create Kabupaten Bengkalis
        $kabupaten = \App\Models\Region::firstOrCreate(
            ['type' => 'kabupaten', 'name' => 'Kabupaten Bengkalis'],
            ['profile_text' => 'Pemerintah Kabupaten Bengkalis']
        );

        // 3. Create Kecamatan
        $kecamatanNames = ['Bengkalis', 'Bantan', 'Bukit Batu', 'Mandau', 'Rupat', 'Siak Kecil', 'Pinggir', 'Bandar Laksamana', 'Talang Muandau', 'Bathin Solapan'];
        $kecamatans = [];
        foreach ($kecamatanNames as $name) {
            $kecamatans[$name] = \App\Models\Region::firstOrCreate(
                ['type' => 'kecamatan', 'name' => 'Kecamatan ' . $name, 'parent_id' => $kabupaten->id],
                ['profile_text' => 'Pemerintah Kecamatan ' . $name]
            );
        }

        // 4. Create Desa Pematang Duku Timur (under Kecamatan Bengkalis)
        $desaPDT = \App\Models\Region::firstOrCreate(
            ['type' => 'desa', 'name' => 'Desa Pematang Duku Timur', 'parent_id' => $kecamatans['Bengkalis']->id],
            ['profile_text' => 'Pemerintah Desa Pematang Duku Timur']
        );

        // 5. Attach services to Desa
        $desaPDT->services()->syncWithoutDetaching([
            $servicePenyewaan->id => ['is_active' => true],
            $serviceGas->id => ['is_active' => true],
            $serviceLaporan->id => ['is_active' => true],
        ]);

        // 6. Migrate existing users and data to Desa Pematang Duku Timur
        // First, handle super admin
        $superAdmin = \App\Models\User::where('email', 'admin@isewa.com')->first();
        if ($superAdmin) {
            $superAdmin->update(['region_id' => $kabupaten->id, 'role' => 'super_admin']);
        }

        // Update all other admins to be admin_desa for Desa Pematang Duku Timur
        \App\Models\User::where('role', 'admin')->where('id', '!=', $superAdmin?->id)->update([
            'role' => 'admin_desa',
            'region_id' => $desaPDT->id
        ]);
        
        // Update lurah to admin_desa (or keep as lurah if preferred, but linked to desa)
        \App\Models\User::where('role', 'lurah')->update([
            'role' => 'admin_desa',
            'region_id' => $desaPDT->id
        ]);

        // Link normal users to Desa Pematang Duku Timur for now (or leave null if they are just customers, but if they are citizens, link them)
        \App\Models\User::where('role', 'user')->whereNull('region_id')->update(['region_id' => $desaPDT->id]);

        // 7. Migrate all data to Desa Pematang Duku Timur
        $tables = ['barang', 'gas', 'laporans', 'rental_bookings', 'gas_orders', 'bumdes_members', 'manual_reports'];
        foreach ($tables as $table) {
            \Illuminate\Support\Facades\DB::table($table)->whereNull('region_id')->update(['region_id' => $desaPDT->id]);
        }
    }
}
