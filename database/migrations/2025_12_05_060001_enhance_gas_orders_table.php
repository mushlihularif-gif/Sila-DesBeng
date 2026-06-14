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
        Schema::table('gas_orders', function (Blueprint $table) {
            // Ensure gas_id foreign key exists
            if (!Schema::hasColumn('gas_orders', 'gas_id')) {
                $table->foreignId('gas_id')->nullable()->after('user_id')->constrained('gas')->onDelete('cascade');
            }
            
            // Timestamps for order tracking - cek dulu apakah sudah ada
            if (!Schema::hasColumn('gas_orders', 'delivery_time')) {
                $table->timestamp('delivery_time')->nullable()->after('order_date');
            }
            if (!Schema::hasColumn('gas_orders', 'arrival_time')) {
                $table->timestamp('arrival_time')->nullable()->after('delivery_time');
            }
            if (!Schema::hasColumn('gas_orders', 'completion_time')) {
                $table->timestamp('completion_time')->nullable()->after('arrival_time');
            }
            
            // Delivery proof
            if (!Schema::hasColumn('gas_orders', 'delivery_proof_image')) {
                $table->string('delivery_proof_image')->nullable()->after('proof_of_payment');
            }
            
            // Cancellation request
            if (!Schema::hasColumn('gas_orders', 'cancellation_reason_user')) {
                $table->text('cancellation_reason_user')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('gas_orders', 'cancellation_requested_at')) {
                $table->timestamp('cancellation_requested_at')->nullable()->after('cancellation_reason_user');
            }
            if (!Schema::hasColumn('gas_orders', 'cancellation_status')) {
                $table->enum('cancellation_status', ['pending', 'approved', 'rejected'])->nullable()->after('cancellation_requested_at');
            }
            if (!Schema::hasColumn('gas_orders', 'admin_cancellation_response')) {
                $table->text('admin_cancellation_response')->nullable()->after('cancellation_status');
            }
        });
        
        // Update existing status enum
        DB::statement("ALTER TABLE gas_orders MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gas_orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_time',
                'arrival_time',
                'completion_time',
                'delivery_proof_image',
                'cancellation_reason_user',
                'cancellation_requested_at',
                'cancellation_status',
                'admin_cancellation_response',
            ]);
        });
    }
};
