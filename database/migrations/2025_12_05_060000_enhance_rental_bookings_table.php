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
        Schema::table('rental_bookings', function (Blueprint $table) {
            // Order number
            $table->string('order_number')->unique()->nullable()->after('id');
            
            // Timestamps for order tracking
            $table->timestamp('delivery_time')->nullable()->after('confirmed_at');
            $table->timestamp('arrival_time')->nullable()->after('delivery_time');
            $table->timestamp('return_time')->nullable()->after('arrival_time');
            $table->timestamp('completion_time')->nullable()->after('return_time');
            
            // Delivery proof
            $table->string('delivery_proof_image')->nullable()->after('payment_proof');
            
            // Cancellation request
            $table->text('cancellation_reason')->nullable()->after('admin_notes');
            $table->timestamp('cancellation_requested_at')->nullable()->after('cancellation_reason');
            $table->enum('cancellation_status', ['pending', 'approved', 'rejected'])->nullable()->after('cancellation_requested_at');
            $table->text('admin_cancellation_response')->nullable()->after('cancellation_status');
        });
        
        // Update existing status enum
        DB::statement("ALTER TABLE rental_bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'being_prepared', 'in_delivery', 'arrived', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'order_number',
                'delivery_time',
                'arrival_time',
                'return_time',
                'completion_time',
                'delivery_proof_image',
                'cancellation_reason',
                'cancellation_requested_at',
                'cancellation_status',
                'admin_cancellation_response',
            ]);
        });
        
        // Revert status enum
        DB::statement("ALTER TABLE rental_bookings MODIFY COLUMN status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending'");
    }
};
