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
        Schema::create('mobil_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('order_number')->unique()->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mobil_id')->constrained('mobils')->cascadeOnDelete();
            $table->string('delivery_method')->default('jemput');
            $table->integer('quantity')->default(1);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('distance_km')->default(1);
            $table->string('recipient_name')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('rental_purpose')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('payment_method')->default('tunai');
            $table->string('payment_proof')->nullable();
            $table->string('delivery_proof_image')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['pending', 'confirmed', 'process', 'delivering', 'arrived', 'completed', 'cancelled', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->string('cancellation_status')->nullable();
            $table->text('admin_cancellation_response')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('delivery_time')->nullable();
            $table->timestamp('arrival_time')->nullable();
            $table->timestamp('return_time')->nullable();
            $table->timestamp('completion_time')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobil_bookings');
    }
};
