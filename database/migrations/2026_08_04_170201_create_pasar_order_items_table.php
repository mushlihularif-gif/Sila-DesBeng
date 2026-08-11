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
        Schema::create('pasar_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasar_order_id')->constrained('pasar_orders')->cascadeOnDelete();
            $table->foreignId('pasar_produk_id')->nullable()->constrained('pasar_produks')->nullOnDelete();
            $table->string('product_name');
            $table->decimal('product_price', 12, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasar_order_items');
    }
};
