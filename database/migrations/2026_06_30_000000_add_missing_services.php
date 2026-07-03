<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Service;
use App\Models\Region;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $serviceMobil = Service::firstOrCreate(
            ['slug' => 'penyewaan-mobil'],
            ['name' => 'Penyewaan Mobil']
        );

        $serviceFasilitas = Service::firstOrCreate(
            ['slug' => 'fasilitas-umum'],
            ['name' => 'Fasilitas Umum']
        );

        // Optionally, attach to all villages (desa) so they can use it immediately if desired
        $regions = Region::where('type', 'desa')->get();
        foreach ($regions as $region) {
            $region->services()->syncWithoutDetaching([
                $serviceMobil->id => ['is_active' => true],
                $serviceFasilitas->id => ['is_active' => true],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Service::whereIn('slug', ['penyewaan-mobil', 'fasilitas-umum'])->delete();
    }
};
