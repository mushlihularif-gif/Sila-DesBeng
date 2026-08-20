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
        Schema::table('mobil_bookings', function (Blueprint $table) {
            $table->foreignId('assigned_supir_id')->nullable()->constrained('supirs')->nullOnDelete();
        });
        
        Schema::table('fasilitas_umum_bookings', function (Blueprint $table) {
            $table->foreignId('assigned_supir_id')->nullable()->constrained('supirs')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobil_bookings', function (Blueprint $table) {
            $table->dropForeign(['assigned_supir_id']);
            $table->dropColumn('assigned_supir_id');
        });
        
        Schema::table('fasilitas_umum_bookings', function (Blueprint $table) {
            $table->dropForeign(['assigned_supir_id']);
            $table->dropColumn('assigned_supir_id');
        });
    }
};
