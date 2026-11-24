<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Memperluas kolom status_biaya dari ENUM('gratis', 'berbayar') menjadi
     * VARCHAR(30) agar mendukung 3 skema pembiayaan fasilitas umum:
     * - gratis        : Gratis sepenuhnya (acara sosial & pribadi gratis)
     * - berbayar      : Sosial gratis, komersial / pribadi berbayar
     * - berbayar_semua: Semua acara berbayar (sosial & pribadi berbayar)
     */
    public function up(): void
    {
        if (! Schema::hasTable('fasilitas_umums') || ! Schema::hasColumn('fasilitas_umums', 'status_biaya')) {
            return;
        }

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE fasilitas_umums MODIFY COLUMN status_biaya VARCHAR(30) NOT NULL DEFAULT 'gratis'");
        } else {
            Schema::table('fasilitas_umums', function (Blueprint $table) {
                $table->string('status_biaya', 30)->default('gratis')->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('fasilitas_umums') || ! Schema::hasColumn('fasilitas_umums', 'status_biaya')) {
            return;
        }

        DB::table('fasilitas_umums')
            ->where('status_biaya', 'berbayar_semua')
            ->update(['status_biaya' => 'berbayar']);

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE fasilitas_umums MODIFY COLUMN status_biaya ENUM('gratis', 'berbayar') NOT NULL DEFAULT 'gratis'");
        }
    }
};
