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
        Schema::table('mobils', function (Blueprint $table) {
            $table->string('tipe_tarif_borongan')->default('jarak')->after('is_borongan_active');
            $table->json('tarif_borongan_wilayah')->nullable()->after('tipe_tarif_borongan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobils', function (Blueprint $table) {
            $table->dropColumn('tipe_tarif_borongan');
            $table->dropColumn('tarif_borongan_wilayah');
        });
    }
};
