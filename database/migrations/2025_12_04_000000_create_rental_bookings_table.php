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
        Schema::dropIfExists('rental_bookings');
        
        Schema::create('rental_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('barang_id')->constrained('barang')->onDelete('cascade');
            
            // Delivery information
            $table->enum('delivery_method', ['antar', 'jemput'])->default('antar');
            $table->integer('quantity')->default(1);
            
            // Rental period
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days_count')->default(1);
            
            // Delivery address (for 'antar' method)
            $table->string('recipient_name')->nullable();
            $table->text('delivery_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            // Payment information
            $table->enum('payment_method', ['transfer', 'tunai'])->default('transfer');
            $table->string('payment_proof')->nullable(); // File path for transfer proof
            $table->decimal('total_amount', 12, 2)->default(0);
            
            // Status tracking
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending');
            
            // Admin notes
            $table->text('admin_notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('user_id');
            $table->index('barang_id');
            $table->index('status');
            $table->index('delivery_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_bookings');
    }
};
