<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah default bawaan region_services.is_exclusive menjadi TRUE (Khusus Warga Desa)
     * agar secara bawaan unit usaha desa hanya bisa diakses/dipesan oleh warga desanya sendiri,
     * serta kunci seluruh unit usaha operasional yang sudah ada (kecuali Kabar & Informasi / pengumuman).
     */
    public function up(): void
    {
        if (! Schema::hasTable('region_services') || ! Schema::hasColumn('region_services', 'is_exclusive')) {
            return;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE region_services MODIFY COLUMN is_exclusive TINYINT(1) NOT NULL DEFAULT 1');
        } else {
            Schema::table('region_services', function (Blueprint $table) {
                $table->boolean('is_exclusive')->default(true)->change();
            });
        }

        $publicServiceIds = Schema::hasTable('services')
            ? DB::table('services')->whereIn('slug', ['pengumuman'])->pluck('id')->all()
            : [];

        $query = DB::table('region_services');
        if (! empty($publicServiceIds)) {
            $query->whereNotIn('service_id', $publicServiceIds);
        }
        $query->update(['is_exclusive' => true]);

        if (! empty($publicServiceIds)) {
            DB::table('region_services')
                ->whereIn('service_id', $publicServiceIds)
                ->update(['is_exclusive' => false]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('region_services') || ! Schema::hasColumn('region_services', 'is_exclusive')) {
            return;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE region_services MODIFY COLUMN is_exclusive TINYINT(1) NOT NULL DEFAULT 0');
        } else {
            Schema::table('region_services', function (Blueprint $table) {
                $table->boolean('is_exclusive')->default(false)->change();
            });
        }
    }
};
