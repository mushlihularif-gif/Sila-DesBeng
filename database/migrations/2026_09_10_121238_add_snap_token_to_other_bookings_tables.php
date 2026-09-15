<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['rental_bookings', 'mobil_bookings', 'fasilitas_umum_bookings'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('snap_token')->nullable()->after('status');
                $table->string('payment_channel')->nullable()->after('snap_token');
                $table->timestamp('payment_expiry_time')->nullable()->after('payment_channel');
                $table->string('payment_va_number')->nullable()->after('payment_expiry_time');
                $table->string('payment_qr_url')->nullable()->after('payment_va_number');
            });
        }
    }

    public function down(): void
    {
        $tables = ['rental_bookings', 'mobil_bookings', 'fasilitas_umum_bookings'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['snap_token', 'payment_channel', 'payment_expiry_time', 'payment_va_number', 'payment_qr_url']);
            });
        }
    }
};
