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
            $table->string('nama_supir')->nullable()->after('opsi_supir');
            $table->string('kontak_supir')->nullable()->after('nama_supir');
        });

        Schema::table('fasilitas_umums', function (Blueprint $table) {
            $table->string('nama_supir')->nullable()->after('opsi_supir');
            $table->string('kontak_supir')->nullable()->after('nama_supir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobils_and_fasilitas_umums_tables', function (Blueprint $table) {
            //
        });
    }
};
