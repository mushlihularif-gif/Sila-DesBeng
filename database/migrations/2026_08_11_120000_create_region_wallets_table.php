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
        Schema::create('region_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('region_id')->unique();
            $table->decimal('saldo_tertahan', 15, 2)->default(0);
            $table->decimal('saldo_tersedia', 15, 2)->default(0);
            $table->decimal('total_dicairkan', 15, 2)->default(0);
            $table->string('gateway_beneficiary_id')->nullable();
            $table->timestamps();

            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('region_wallets');
    }
};
