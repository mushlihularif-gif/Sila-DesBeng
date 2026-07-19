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
        // Set invalid roles to 'user' to prevent truncation error
        DB::statement("UPDATE users SET role = 'user' WHERE role NOT IN ('super_admin','admin_kecamatan','admin_desa','admin','lurah','user','rt','rw')");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin_kecamatan','admin_desa','admin','lurah','user','rt','rw') DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin_kecamatan','admin_desa','admin','lurah','user') DEFAULT 'user'");
    }
};
