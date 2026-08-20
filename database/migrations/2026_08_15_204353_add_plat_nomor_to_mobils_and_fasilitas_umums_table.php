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
            $table->string('plat_nomor')->nullable()->after('kategori');
        });
        
        Schema::table('fasilitas_umums', function (Blueprint $table) {
            $table->string('plat_nomor')->nullable()->after('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobils', function (Blueprint $table) {
            $table->dropColumn('plat_nomor');
        });
        
        Schema::table('fasilitas_umums', function (Blueprint $table) {
            $table->dropColumn('plat_nomor');
        });
    }
};
