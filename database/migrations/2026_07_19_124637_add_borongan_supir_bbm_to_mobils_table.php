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
            $table->string('opsi_supir_borongan')->nullable()->after('bbm_ditanggung');
            $table->string('nama_supir_borongan')->nullable()->after('opsi_supir_borongan');
            $table->string('kontak_supir_borongan')->nullable()->after('nama_supir_borongan');
            $table->string('bbm_ditanggung_borongan')->nullable()->after('kontak_supir_borongan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobils', function (Blueprint $table) {
            $table->dropColumn([
                'opsi_supir_borongan',
                'nama_supir_borongan',
                'kontak_supir_borongan',
                'bbm_ditanggung_borongan'
            ]);
        });
    }
};
