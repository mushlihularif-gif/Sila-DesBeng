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
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->dropForeign(['barang_id']);
        });
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable()->change();
            $table->foreign('barang_id')->references('id')->on('barang')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->dropForeign(['barang_id']);
        });
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable(false)->change();
            $table->foreign('barang_id')->references('id')->on('barang')->onDelete('cascade');
        });
    }
};
