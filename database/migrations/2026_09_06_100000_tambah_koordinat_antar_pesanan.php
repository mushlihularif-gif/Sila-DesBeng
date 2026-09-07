<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Titik antar pesanan untuk unit yang belum punya.
 *
 * rental_bookings, mobil_bookings, dan pasar_orders sudah lama menyimpan
 * koordinat pengantaran; gas_orders dan fasilitas_umum_bookings belum, sehingga
 * petugas hanya menerima alamat berupa teks. Untuk pengantaran gas ke rumah
 * warga di desa yang jalannya tak bernama, teks saja sering tidak cukup.
 *
 * Ketelitian 7 angka desimal (~1 cm) menyamai kolom yang sudah ada di tabel
 * lain, supaya tidak ada dua tingkat ketelitian yang berbeda dalam satu sistem.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gas_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('gas_orders', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('address');
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });

        Schema::table('fasilitas_umum_bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('fasilitas_umum_bookings', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('delivery_address');
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('gas_orders', function (Blueprint $table) {
            if (Schema::hasColumn('gas_orders', 'latitude')) {
                $table->dropColumn(['latitude', 'longitude']);
            }
        });

        Schema::table('fasilitas_umum_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('fasilitas_umum_bookings', 'latitude')) {
                $table->dropColumn(['latitude', 'longitude']);
            }
        });
    }
};
