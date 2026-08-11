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
        $tables = [
            'gas_orders',
            'mobil_bookings',
            'fasilitas_umum_bookings',
            'pasar_orders',
            'rental_bookings'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'handled_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('handled_by')->nullable()->after('status');
                    $table->foreign('handled_by')->references('id')->on('users')->onDelete('set null');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'gas_orders',
            'mobil_bookings',
            'fasilitas_umum_bookings',
            'pasar_orders',
            'rental_bookings'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'handled_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['handled_by']);
                    $table->dropColumn('handled_by');
                });
            }
        }
    }
};
