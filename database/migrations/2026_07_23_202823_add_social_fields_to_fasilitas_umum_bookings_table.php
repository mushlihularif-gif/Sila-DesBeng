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
        Schema::table('fasilitas_umum_bookings', function (Blueprint $table) {
            $table->string('surat_pengantar')->nullable()->after('jenis_acara');
            $table->boolean('butuh_gudang')->default(false)->after('surat_pengantar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fasilitas_umum_bookings', function (Blueprint $table) {
            $table->dropColumn(['surat_pengantar', 'butuh_gudang']);
        });
    }
};
