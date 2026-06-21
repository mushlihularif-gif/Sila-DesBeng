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
        Schema::table('gas_orders', function (Blueprint $table) {
            $table->string('payment_channel')->nullable()->after('payment_method');
            $table->string('payment_va_number')->nullable()->after('payment_channel');
            $table->string('payment_qr_url')->nullable()->after('payment_va_number');
            $table->timestamp('payment_expiry_time')->nullable()->after('payment_qr_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gas_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_channel',
                'payment_va_number',
                'payment_qr_url',
                'payment_expiry_time'
            ]);
        });
    }
};
