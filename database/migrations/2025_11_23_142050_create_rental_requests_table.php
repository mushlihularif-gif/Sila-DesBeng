<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRentalRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('rental_requests', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // No. Pesanan
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('item_name'); // Tenda Komplit
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->time('pickup_time')->nullable();
            $table->time('return_time')->nullable();
            $table->string('delivery_method'); // Antar / Jemput
            $table->string('payment_method'); // Transfer / Tunai
            $table->string('address');
            $table->string('full_name');
            $table->string('email');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, processed, completed
            $table->string('rejection_reason')->nullable();
            $table->string('proof_of_payment')->nullable(); // path to file
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rental_requests');
    }
}