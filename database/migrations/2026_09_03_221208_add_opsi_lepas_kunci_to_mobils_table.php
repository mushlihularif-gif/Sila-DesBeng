<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobils', function (Blueprint $table) {
            $table->string('opsi_lepas_kunci')->default('wajib_jemput')->after('is_borongan_active');
        });
    }

    public function down(): void
    {
        Schema::table('mobils', function (Blueprint $table) {
            $table->dropColumn('opsi_lepas_kunci');
        });
    }
};