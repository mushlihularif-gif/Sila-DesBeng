<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_receipts', function (Blueprint $table) {
            $table->id();
            $table->enum('booking_type', ['rental', 'gas']);
            $table->unsignedBigInteger('booking_id');
            $table->string('receipt_number')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Item details
            $table->string('item_name');
            $table->integer('quantity');
            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_method', ['transfer', 'tunai']);
            
            // Receipt file (optional PDF)
            $table->string('receipt_file_path')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('booking_type');
            $table->index('booking_id');
            $table->index('receipt_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_receipts');
    }
};
