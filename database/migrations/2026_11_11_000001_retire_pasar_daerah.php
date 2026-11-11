<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('services') && Schema::hasTable('region_services')) {
            $ids = DB::table('services')->where('slug', 'pasar-daerah')->pluck('id');
            DB::table('region_services')->whereIn('service_id', $ids)->update(['is_active' => false]);
        }
    }

    public function down(): void
    {
        // Historical records are retained. A rollback must not reactivate this retired service.
    }
};
