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
        // 1. Pastikan Service 'Fasilitas Umum' terdaftar
        $fasilitasService = DB::table('services')->where('slug', 'fasilitas-umum')->first();
        if (!$fasilitasService) {
            $fasilitasId = DB::table('services')->insertGetId([
                'name' => 'Fasilitas Umum',
                'slug' => 'fasilitas-umum',
                'icon' => 'bx-building',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $fasilitasId = $fasilitasService->id;
        }

        // 2. Ambil ID layanan redundant (ambulans dan peminjaman fasilitas terpisah)
        $redundantServiceIds = DB::table('services')
            ->whereIn('slug', ['peminjaman-fasilitas-umum', 'layanan-ambulans'])
            ->pluck('id')
            ->toArray();

        // 3. Untuk wilayah yang memiliki layanan redundant aktif, pastikan Fasilitas Umum aktif
        if (!empty($redundantServiceIds)) {
            $activeRegionIds = DB::table('region_services')
                ->whereIn('service_id', $redundantServiceIds)
                ->where('is_active', true)
                ->pluck('region_id')
                ->unique();

            foreach ($activeRegionIds as $regId) {
                DB::table('region_services')->updateOrInsert(
                    ['region_id' => $regId, 'service_id' => $fasilitasId],
                    [
                        'is_active' => true,
                        'is_exclusive' => false,
                        'updated_at' => now(),
                    ]
                );
            }

            // 4. Nonaktifkan layanan redundant dari region_services
            DB::table('region_services')
                ->whereIn('service_id', $redundantServiceIds)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
