<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['rental_request', 'gas_order', 'payment_uploaded']);
            $table->unsignedBigInteger('reference_id'); // rental_booking_id or gas_order_id
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index('type');
            $table->index('reference_id');
            $table->index('is_read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
