<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Selamatkan/Migrasi Data Lama
        // Jika ada user yang telanjur menggunakan role 'rt' atau 'rw', ubah menjadi 'admin_rt' atau 'admin_rw'
        DB::table('users')->where('role', 'rt')->update(['role' => 'admin_rt']);
        DB::table('users')->where('role', 'rw')->update(['role' => 'admin_rw']);

        // 2. Bersihkan Skema Database (Buang rt dan rw dari ENUM)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin_kecamatan','admin_desa','admin','lurah','user','admin_rt','admin_rw') DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
