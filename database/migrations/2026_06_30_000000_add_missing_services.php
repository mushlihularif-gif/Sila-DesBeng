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

        // Hanya pasangkan ke Desa Pematang Duku Timur (desa aktif percontohan)
        $desaPDT = Region::where('type', 'desa')->where('name', 'like', '%Pematang Duku Timur%')->first();
        if ($desaPDT) {
            $desaPDT->services()->syncWithoutDetaching([
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
