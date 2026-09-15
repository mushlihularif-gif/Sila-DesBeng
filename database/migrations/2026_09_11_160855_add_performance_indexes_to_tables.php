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
        // Add indexes to frequently queried columns to improve Beranda response time
        
        Schema::table('announcements', function (Blueprint $table) {
            $table->index('is_active', 'idx_announcements_is_active');
            $table->index('created_at', 'idx_announcements_created_at');
        });

        Schema::table('pasar_produks', function (Blueprint $table) {
            $table->index('status', 'idx_pasar_produks_status');
        });

        Schema::table('barang', function (Blueprint $table) {
            $table->index('status', 'idx_barang_status');
        });

        Schema::table('mobils', function (Blueprint $table) {
            $table->index('status', 'idx_mobils_status');
        });

        Schema::table('fasilitas_umums', function (Blueprint $table) {
            $table->index('status', 'idx_fasilitas_umums_status');
        });

        Schema::table('gas', function (Blueprint $table) {
            $table->index('stok', 'idx_gas_stok'); // Gas uses 'stok > 0' condition
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex('idx_announcements_is_active');
            $table->dropIndex('idx_announcements_created_at');
        });

        Schema::table('pasar_produks', function (Blueprint $table) {
            $table->dropIndex('idx_pasar_produks_status');
        });

        Schema::table('barang', function (Blueprint $table) {
            $table->dropIndex('idx_barang_status');
        });

        Schema::table('mobils', function (Blueprint $table) {
            $table->dropIndex('idx_mobils_status');
        });

        Schema::table('fasilitas_umums', function (Blueprint $table) {
            $table->dropIndex('idx_fasilitas_umums_status');
        });

        Schema::table('gas', function (Blueprint $table) {
            $table->dropIndex('idx_gas_stok');
        });
    }
};
