<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobils', function (Blueprint $table) {
            $table->decimal('harga_dalam_desa', 12, 2)->default(100000);
            $table->integer('batas_km_dalam_desa')->default(5);
            
            $table->decimal('harga_luar_desa', 12, 2)->default(200000);
            $table->integer('batas_km_luar_desa')->default(50);
            
            $table->decimal('harga_luar_kota', 12, 2)->default(400000);
            
            $table->string('bbm_ditanggung')->default('Penyewa'); // 'Pemerintah Desa', 'Penyewa'
        });
    }

    public function down(): void
    {
        Schema::table('mobils', function (Blueprint $table) {
            $table->dropColumn([
                'harga_dalam_desa',
                'batas_km_dalam_desa',
                'harga_luar_desa',
                'batas_km_luar_desa',
                'harga_luar_kota',
                'bbm_ditanggung'
            ]);
        });
    }
};
