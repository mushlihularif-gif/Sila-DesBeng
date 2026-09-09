<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sama alasannya dengan gas_orders: popup Snap sering tertutup sebelum warga
 * sempat membayar, dan tanpa tokennya tersimpan satu-satunya jalan kembali
 * adalah memesan ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasar_orders', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('payment_va_number');
        });
    }

    public function down(): void
    {
        Schema::table('pasar_orders', function (Blueprint $table) {
            $table->dropColumn('snap_token');
        });
    }
};
