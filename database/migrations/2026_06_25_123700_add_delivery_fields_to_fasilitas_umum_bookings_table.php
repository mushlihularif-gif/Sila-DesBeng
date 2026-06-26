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
        Schema::table('fasilitas_umum_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('fasilitas_umum_bookings', 'delivery_method')) {
                $table->string('delivery_method')->nullable();
            }
            if (!Schema::hasColumn('fasilitas_umum_bookings', 'recipient_name')) {
                $table->string('recipient_name')->nullable();
            }
            if (!Schema::hasColumn('fasilitas_umum_bookings', 'delivery_address')) {
                $table->text('delivery_address')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fasilitas_umum_bookings', function (Blueprint $table) {
            $table->dropColumn(['delivery_method', 'recipient_name', 'delivery_address']);
        });
    }
};
