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
        // Pastikan enum role di tabel users mencakup admin_rt, admin_rw, rt, dan rw agar tidak terjadi Data truncated error
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin_kecamatan','admin_desa','admin','lurah','user','admin_rt','admin_rw','rt','rw') DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin_kecamatan','admin_desa','admin','lurah','user','rt','rw') DEFAULT 'user'");
    }
};
