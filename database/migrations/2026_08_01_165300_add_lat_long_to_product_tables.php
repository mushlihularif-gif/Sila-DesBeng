<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom latitude & longitude (opsional) ke tabel produk
     * agar setiap produk bisa memiliki titik lokasi map sendiri.
     */
    public function up(): void
    {
        // Tabel Barang (Penyewaan Alat)
        Schema::table('barang', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('lokasi');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        // Tabel Mobils (Penyewaan Mobil)
        Schema::table('mobils', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('lokasi');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        // Tabel Gas (Penjualan Gas)
        Schema::table('gas', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('lokasi');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        // Tabel Fasilitas Umums (Fasilitas Umum)
        Schema::table('fasilitas_umums', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('lokasi');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('mobils', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('gas', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('fasilitas_umums', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
