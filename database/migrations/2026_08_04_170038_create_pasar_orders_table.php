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
        Schema::create('pasar_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete(); // Desa penjual
            
            // Financials
            $table->decimal('total_amount', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            
            // Methods
            $table->string('delivery_method'); // 'antar', 'jemput'
            $table->string('payment_method'); // 'tunai', 'transfer_manual', 'bank_transfer_bca', etc
            $table->string('payment_channel')->nullable();
            $table->string('payment_va_number')->nullable();
            $table->string('payment_qr_url')->nullable();
            $table->timestamp('payment_expiry_time')->nullable();
            $table->string('proof_of_payment')->nullable();
            
            // Customer & Delivery Info
            $table->text('delivery_address')->nullable();
            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            
            // Status Tracking
            $table->string('status')->default('pending'); // pending, confirmed, processing, ready, completed, cancelled, rejected
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completion_time')->nullable();
            $table->string('rejection_reason')->nullable();
            
            // Cancellations
            $table->text('cancellation_reason_user')->nullable();
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->enum('cancellation_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->text('admin_cancellation_response')->nullable();
            
            // Proofs
            $table->string('delivery_proof_image')->nullable();
            $table->string('receipt_path')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasar_orders');
    }
};
