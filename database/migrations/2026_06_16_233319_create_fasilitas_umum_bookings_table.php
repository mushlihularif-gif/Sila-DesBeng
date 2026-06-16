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
        Schema::create('fasilitas_umum_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('fasilitas_id')->constrained('fasilitas_umums')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('quantity')->default(1);
            $table->text('rental_purpose')->nullable();
            
            $table->string('status')->default('pending'); // pending, approved, ongoing, completed, cancelled, rejected
            $table->text('admin_notes')->nullable();
            
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->string('cancellation_status')->nullable();
            $table->text('admin_cancellation_response')->nullable();
            
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('return_time')->nullable();
            $table->timestamp('completion_time')->nullable();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fasilitas_umum_bookings');
    }
};
