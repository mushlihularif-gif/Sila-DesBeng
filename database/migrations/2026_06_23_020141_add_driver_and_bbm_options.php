<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah opsi_supir di tabel mobils
        Schema::table('mobils', function (Blueprint $table) {
            if (!Schema::hasColumn('mobils', 'opsi_supir')) {
                $table->string('opsi_supir')->default('Lepas Kunci'); // Lepas Kunci, Dengan Supir, Bebas Pilih
            }
        });

        // 2. Tambah opsi_supir dan bbm_ditanggung di tabel fasilitas_umums
        Schema::table('fasilitas_umums', function (Blueprint $table) {
            if (!Schema::hasColumn('fasilitas_umums', 'opsi_supir')) {
                $table->string('opsi_supir')->nullable(); // Untuk tipe kendaraan (Ambulan)
            }
            if (!Schema::hasColumn('fasilitas_umums', 'bbm_ditanggung')) {
                $table->string('bbm_ditanggung')->nullable(); // Pemerintah Desa, Penyewa
            }
        });

        // 3. Tambah dengan_supir di mobil_bookings
        Schema::table('mobil_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('mobil_bookings', 'dengan_supir')) {
                $table->boolean('dengan_supir')->default(false);
            }
        });

        // 4. Tambah dengan_supir dan delivery_method di fasilitas_umum_bookings
        Schema::table('fasilitas_umum_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('fasilitas_umum_bookings', 'dengan_supir')) {
                $table->boolean('dengan_supir')->default(false);
            }
            if (!Schema::hasColumn('fasilitas_umum_bookings', 'delivery_method')) {
                $table->string('delivery_method')->default('jemput');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobils', function (Blueprint $table) {
            if (Schema::hasColumn('mobils', 'opsi_supir')) {
                $table->dropColumn('opsi_supir');
            }
        });

        Schema::table('fasilitas_umums', function (Blueprint $table) {
            if (Schema::hasColumn('fasilitas_umums', 'opsi_supir')) {
                $table->dropColumn('opsi_supir');
            }
            if (Schema::hasColumn('fasilitas_umums', 'bbm_ditanggung')) {
                $table->dropColumn('bbm_ditanggung');
            }
        });

        Schema::table('mobil_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('mobil_bookings', 'dengan_supir')) {
                $table->dropColumn('dengan_supir');
            }
        });

        Schema::table('fasilitas_umum_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('fasilitas_umum_bookings', 'dengan_supir')) {
                $table->dropColumn('dengan_supir');
            }
            if (Schema::hasColumn('fasilitas_umum_bookings', 'delivery_method')) {
                $table->dropColumn('delivery_method');
            }
        });
    }
};
