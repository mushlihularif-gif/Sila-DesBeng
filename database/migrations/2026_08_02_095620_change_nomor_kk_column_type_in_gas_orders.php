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
            $table->text('nomor_kk')->nullable()->change();
            if (Schema::hasColumn('gas_orders', 'nomor_kk_hash')) {
                $table->text('nomor_kk_hash')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gas_orders', function (Blueprint $table) {
            $table->string('nomor_kk', 16)->nullable()->change();
            if (Schema::hasColumn('gas_orders', 'nomor_kk_hash')) {
                $table->string('nomor_kk_hash', 64)->nullable()->change();
            }
        });
    }
};
