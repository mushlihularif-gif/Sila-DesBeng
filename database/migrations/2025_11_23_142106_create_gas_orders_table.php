<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGasOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('gas_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->nullable(); // No. Pesanan
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('gas_id')->nullable()->constrained('gas')->onDelete('cascade');
            $table->string('item_name'); // Gas LPG 3 Kg
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->date('order_date');
            $table->string('delivery_method')->nullable(); // Antar / Jemput
            $table->string('payment_method'); // Transfer / Tunai
            $table->string('address');
            $table->string('full_name');
            $table->string('email');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, processed, completed
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivery_time')->nullable();
            $table->timestamp('arrival_time')->nullable();
            $table->timestamp('completion_time')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->string('proof_of_payment')->nullable(); // path to file
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('gas_orders');
    }
}