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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('region_id');
            $table->enum('type', ['masuk', 'ditahan', 'dilepas', 'keluar', 'refund', 'koreksi']);
            $table->enum('source', ['gateway', 'manual']);
            $table->decimal('amount', 15, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->enum('status', ['pending', 'verified', 'completed', 'rejected'])->default('pending');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->string('proof_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
