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
        Schema::create('pasar_complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasar_order_id')->constrained('pasar_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            
            $table->string('reason'); // 'Barang rusak / busuk / basi', 'Jumlah barang kurang / tidak lengkap', dll
            $table->string('solution_requested')->default('refund'); // 'refund', 'replacement'
            $table->text('description')->nullable();
            
            // Foto bukti kerusakan / unboxing
            $table->string('evidence_1')->nullable();
            $table->string('evidence_2')->nullable();
            $table->string('evidence_3')->nullable();
            
            // Info rekening pembeli jika minta refund
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            
            // Status & Respon Admin Desa
            $table->enum('status', ['pending', 'approved_replacement', 'approved_refund', 'rejected'])->default('pending');
            $table->text('admin_response')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasar_complaints');
    }
};
