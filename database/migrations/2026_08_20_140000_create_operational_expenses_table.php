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
        Schema::create('operational_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->enum('category', ['domain', 'hosting', 'ssl', 'api_service', 'lainnya'])->default('lainnya');
            $table->decimal('amount', 15, 2);
            $table->enum('billing_cycle', ['bulanan', 'tahunan', 'sekali_bayar'])->default('tahunan');
            $table->date('due_date');
            $table->enum('status', ['lunas', 'jatuh_tempo', 'terlambat'])->default('jatuh_tempo');
            $table->string('proof_path')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->timestamps();

            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operational_expenses');
    }
};
