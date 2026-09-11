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
        // 1. Ambil ID layanan Fasilitas Umum dan Peminjaman Fasilitas Umum
        $fasilitasServiceIds = DB::table('services')
            ->whereIn('slug', ['fasilitas-umum', 'peminjaman-fasilitas-umum'])
            ->pluck('id')
            ->toArray();

        // 2. Ambil ID layanan Ambulans
        $ambulanceServiceIds = DB::table('services')
            ->whereIn('slug', ['layanan-ambulans', 'ambulans'])
            ->pluck('id')
            ->toArray();

        // 3. Wilayah yang sah memiliki Fasilitas Umum:
        // - Wilayah yang tercatat di tabel fasilitas_umums
        // - Desa Pematang Duku Timur
        $fasilitasItemRegionIds = DB::table('fasilitas_umums')
            ->whereNotNull('region_id')
            ->pluck('region_id')
            ->toArray();

        $pematangDukuTimurIds = DB::table('regions')
            ->where('name', 'like', '%Pematang Duku Timur%')
            ->pluck('id')
            ->toArray();

        $validFasilitasRegionIds = array_unique(array_merge($fasilitasItemRegionIds, $pematangDukuTimurIds));

        // 4. Wilayah yang sah memiliki Armada Ambulans
        $ambulanceItemRegionIds = DB::table('mobils')
            ->where('kategori', 'ambulans')
            ->whereNotNull('region_id')
            ->pluck('region_id')
            ->toArray();

        $validAmbulanceRegionIds = array_unique(array_merge($ambulanceItemRegionIds, $pematangDukuTimurIds));

        // 5. Nonaktifkan Fasilitas Umum untuk seluruh wilayah selain wilayah yang sah
        if (!empty($fasilitasServiceIds)) {
            // Nonaktifkan untuk wilayah yang tidak memiliki fasilitas umum
            DB::table('region_services')
                ->whereIn('service_id', $fasilitasServiceIds)
                ->whereNotIn('region_id', $validFasilitasRegionIds)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            // Pastikan wilayah sah tetap aktif
            foreach ($validFasilitasRegionIds as $regId) {
                foreach ($fasilitasServiceIds as $srvId) {
                    DB::table('region_services')->updateOrInsert(
                        ['region_id' => $regId, 'service_id' => $srvId],
                        [
                            'is_active' => true,
                            'is_exclusive' => false,
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }

        // 6. Nonaktifkan Layanan Ambulans untuk wilayah yang tidak memiliki armada ambulans
        if (!empty($ambulanceServiceIds)) {
            DB::table('region_services')
                ->whereIn('service_id', $ambulanceServiceIds)
                ->whereNotIn('region_id', $validAmbulanceRegionIds)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            foreach ($validAmbulanceRegionIds as $regId) {
                foreach ($ambulanceServiceIds as $srvId) {
                    DB::table('region_services')->updateOrInsert(
                        ['region_id' => $regId, 'service_id' => $srvId],
                        [
                            'is_active' => true,
                            'is_exclusive' => false,
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu mengembalikan ke status aktif masal yang salah
    }
};
