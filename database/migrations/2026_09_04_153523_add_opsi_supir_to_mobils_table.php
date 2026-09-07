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
            if (!Schema::hasColumn('mobils', 'opsi_supir')) {
                $table->string('opsi_supir')->default('Lepas Kunci')->after('bbm_ditanggung');
            }
            if (!Schema::hasColumn('mobils', 'opsi_supir_borongan')) {
                $table->string('opsi_supir_borongan')->default('Lepas Kunci')->after('bbm_ditanggung_borongan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobils', function (Blueprint $table) {
            if (Schema::hasColumn('mobils', 'opsi_supir')) {
                $table->dropColumn('opsi_supir');
            }
            if (Schema::hasColumn('mobils', 'opsi_supir_borongan')) {
                $table->dropColumn('opsi_supir_borongan');
            }
        });
    }
};
