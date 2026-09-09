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
        // 1. Daftarkan Layanan Ambulans jika belum ada di tabel services
        $ambulanceService = DB::table('services')->where('slug', 'layanan-ambulans')->first();
        if (!$ambulanceService) {
            $serviceId = DB::table('services')->insertGetId([
                'name' => 'Layanan Ambulans',
                'slug' => 'layanan-ambulans',
                'icon' => 'bx-plus-medical',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $serviceId = $ambulanceService->id;
        }

        // 2. Aktifkan Layanan Ambulans di region_services untuk seluruh wilayah
        $regionIds = DB::table('regions')->pluck('id');
        foreach ($regionIds as $regId) {
            DB::table('region_services')->updateOrInsert(
                ['region_id' => $regId, 'service_id' => $serviceId],
                [
                    'is_active' => true,
                    'is_exclusive' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 3. Perbarui region_id yang null pada tabel barang (default: desa id 12 jika belum terisi)
        $defaultRegionId = DB::table('regions')->where('id', 12)->exists() ? 12 : (DB::table('regions')->first()->id ?? null);
        if ($defaultRegionId) {
            DB::table('barang')->whereNull('region_id')->update(['region_id' => $defaultRegionId]);
            DB::table('mobils')->whereNull('region_id')->update(['region_id' => $defaultRegionId]);

            // Sinkronisasi region_id pada pesanan yang null
            if (Schema::hasColumn('rental_bookings', 'region_id')) {
                DB::table('rental_bookings')
                    ->whereNull('region_id')
                    ->update(['region_id' => $defaultRegionId]);
            }

            if (Schema::hasColumn('mobil_bookings', 'region_id')) {
                DB::table('mobil_bookings')
                    ->whereNull('region_id')
                    ->update(['region_id' => $defaultRegionId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $service = DB::table('services')->where('slug', 'layanan-ambulans')->first();
        if ($service) {
            DB::table('region_services')->where('service_id', $service->id)->delete();
            DB::table('services')->where('id', $service->id)->delete();
        }
    }
};
