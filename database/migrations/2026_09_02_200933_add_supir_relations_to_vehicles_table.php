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
        // Supir table changes (layanan becomes nullable since one supir can handle multiple)
        Schema::table('supirs', function (Blueprint $table) {
            $table->string('layanan')->nullable()->change();
        });

        // Mobils table changes
        Schema::table('mobils', function (Blueprint $table) {
            $table->foreignId('supir_id')->nullable()->after('opsi_supir')->constrained('supirs')->nullOnDelete();
            $table->foreignId('supir_borongan_id')->nullable()->after('opsi_supir_borongan')->constrained('supirs')->nullOnDelete();
        });

        // Fasilitas_umums table changes
        Schema::table('fasilitas_umums', function (Blueprint $table) {
            $table->foreignId('supir_id')->nullable()->after('opsi_supir')->constrained('supirs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobils', function (Blueprint $table) {
            $table->dropForeign(['supir_id']);
            $table->dropForeign(['supir_borongan_id']);
            $table->dropColumn(['supir_id', 'supir_borongan_id']);
        });

        Schema::table('fasilitas_umums', function (Blueprint $table) {
            $table->dropForeign(['supir_id']);
            $table->dropColumn(['supir_id']);
        });
        
        // Supirs table changes revert
        Schema::table('supirs', function (Blueprint $table) {
            $table->string('layanan')->nullable(false)->change();
        });
    }
};
