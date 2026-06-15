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
        $tables = ['users', 'barang', 'gas', 'laporans', 'rental_bookings', 'gas_orders', 'bumdes_members', 'manual_reports'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            });
        }

        // Alter role enum in users table
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt', 'lurah', 'user', 'admin') DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['users', 'barang', 'gas', 'laporans', 'rental_bookings', 'gas_orders', 'bumdes_members', 'manual_reports'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['region_id']);
                $t->dropColumn('region_id');
            });
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'lurah', 'user') DEFAULT 'user'");
    }
};
