<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Token Snap disimpan supaya pembayaran bisa DILANJUTKAN.
 *
 * Popup Snap sering tertutup — warga salah pencet, ponsel berpindah aplikasi,
 * atau sengaja menunda. Tanpa tokennya tersimpan, satu-satunya jalan kembali
 * adalah memesan ulang, dan pesanan lama menggantung sebagai sampah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gas_orders', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('payment_channel');
        });
    }

    public function down(): void
    {
        Schema::table('gas_orders', function (Blueprint $table) {
            $table->dropColumn('snap_token');
        });
    }
};
