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
        Schema::table('mobil_bookings', function (Blueprint $table) {
            $table->string('tujuan_wilayah')->nullable()->after('distance_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobil_bookings', function (Blueprint $table) {
            $table->dropColumn('tujuan_wilayah');
        });
    }
};
