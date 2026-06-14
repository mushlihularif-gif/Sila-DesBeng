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
            $table->string('receipt_path')->nullable()->after('admin_cancellation_response');
        });

        Schema::table('gas_orders', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('admin_cancellation_response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->dropColumn('receipt_path');
        });

        Schema::table('gas_orders', function (Blueprint $table) {
            $table->dropColumn('receipt_path');
        });
    }
};
