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
        Schema::table('fasilitas_umums', function (Blueprint $table) {
            if (!Schema::hasColumn('fasilitas_umums', 'status_biaya')) {
                $table->enum('status_biaya', ['gratis', 'berbayar'])->default('gratis')->after('status');
            }
            if (!Schema::hasColumn('fasilitas_umums', 'harga_sewa')) {
                $table->decimal('harga_sewa', 12, 2)->nullable()->after('status_biaya');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fasilitas_umums', function (Blueprint $table) {
            if (Schema::hasColumn('fasilitas_umums', 'status_biaya')) {
                $table->dropColumn('status_biaya');
            }
            if (Schema::hasColumn('fasilitas_umums', 'harga_sewa')) {
                $table->dropColumn('harga_sewa');
            }
        });
    }
};
