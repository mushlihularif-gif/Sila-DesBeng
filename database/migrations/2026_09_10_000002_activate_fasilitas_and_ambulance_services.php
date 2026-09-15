<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Pastikan Service 'Fasilitas Umum' dan 'Layanan Ambulans' terdaftar di tabel services
        $services = [
            [
                'name' => 'Fasilitas Umum',
                'slug' => 'fasilitas-umum',
                'icon' => 'bx-building',
            ],
            [
                'name' => 'Layanan Ambulans',
                'slug' => 'layanan-ambulans',
                'icon' => 'bx-plus-medical',
            ],
            [
                'name' => 'Peminjaman Fasilitas Umum',
                'slug' => 'peminjaman-fasilitas-umum',
                'icon' => 'bx-building',
            ],
        ];

        $serviceIds = [];
        foreach ($services as $srv) {
            $existing = DB::table('services')->where('slug', $srv['slug'])->first();
            if (!$existing) {
                $id = DB::table('services')->insertGetId([
                    'name' => $srv['name'],
                    'slug' => $srv['slug'],
                    'icon' => $srv['icon'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $serviceIds[] = $id;
            } else {
                $serviceIds[] = $existing->id;
            }
        }

        // 2. Aktifkan layanan ini di region_services untuk semua region
        $regionIds = DB::table('regions')->pluck('id');
        foreach ($regionIds as $regId) {
            foreach ($serviceIds as $srvId) {
                DB::table('region_services')->updateOrInsert(
                    ['region_id' => $regId, 'service_id' => $srvId],
                    [
                        'is_active' => true,
                        'is_exclusive' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        // 3. Pastikan data fasilitas_umums dan mobils yang null region_id-nya diarahkan ke Desa Pematang Duku Timur (id 12)
        $defaultRegionId = DB::table('regions')->where('id', 12)->exists() ? 12 : (DB::table('regions')->first()->id ?? null);
        if ($defaultRegionId) {
            if (Schema::hasTable('fasilitas_umums')) {
                DB::table('fasilitas_umums')->whereNull('region_id')->update(['region_id' => $defaultRegionId]);
            }
            if (Schema::hasTable('mobils')) {
                DB::table('mobils')->whereNull('region_id')->update(['region_id' => $defaultRegionId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak menghapus data untuk keamanan operasional
    }
};
