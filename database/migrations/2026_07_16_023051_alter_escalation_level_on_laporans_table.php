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
        // Mengubah dari ENUM('rt', 'rw', 'admin') menjadi VARCHAR
        // Menggunakan raw statement karena Laravel tidak mendukung modify() pada kolom ENUM tanpa package doctrine/dbal
        DB::statement("ALTER TABLE laporans MODIFY escalation_level VARCHAR(50) DEFAULT 'rt'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke ENUM (Data desa/kecamatan/kabupaten akan menjadi invalid)
        DB::statement("ALTER TABLE laporans MODIFY escalation_level ENUM('rt', 'rw', 'admin') DEFAULT 'rt'");
    }
};
