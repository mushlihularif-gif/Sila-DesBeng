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
        Schema::create('pasar_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pasar_produk_id')->constrained('pasar_produks')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();

            // 1 user only has 1 unique entry per product in cart
            $table->unique(['user_id', 'pasar_produk_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasar_carts');
    }
};
