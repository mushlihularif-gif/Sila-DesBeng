<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('services') || !Schema::hasTable('region_services')) return;
        $ids = DB::table('services')->whereIn('slug', ['ambulans','layanan-ambulans','pelaporan-warga'])->pluck('id');
        DB::table('region_services')->whereIn('service_id', $ids)->update(['is_active'=>false]);
    }

    public function down(): void
    {
        // Do not reactivate retired services or erase their historical records on rollback.
    }
};
