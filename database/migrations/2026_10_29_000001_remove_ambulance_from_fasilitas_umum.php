<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bersihkan unit ambulans yang mungkin masih tersisa di tabel fasilitas_umums
        DB::table('fasilitas_umums')
            ->where('nama_fasilitas', 'like', '%ambulan%')
            ->orWhere('kategori', 'like', '%ambulan%')
            ->delete();

        // 2. Bersihkan kendaraan berkategori ambulans di tabel mobils
        DB::table('mobils')
            ->where('kategori', 'ambulans')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
