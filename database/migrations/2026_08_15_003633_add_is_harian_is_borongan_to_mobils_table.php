<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mobils', function (Blueprint $table) {
            $table->boolean('is_harian_active')->default(true)->after('bbm_ditanggung');
            $table->boolean('is_borongan_active')->default(true)->after('bbm_ditanggung_borongan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobils', function (Blueprint $table) {
            $table->dropColumn(['is_harian_active', 'is_borongan_active']);
        });
    }
};
