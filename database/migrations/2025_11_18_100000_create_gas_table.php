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
        Schema::create('gas', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_gas');
            $table->text('deskripsi')->nullable();
            $table->decimal('harga_satuan', 12, 2);
            $table->integer('stok')->default(0);
            $table->enum('status', ['tersedia', 'dipesan', 'rusak'])->default('tersedia');
            $table->string('foto')->nullable();
            $table->string('foto_2')->nullable();
            $table->string('foto_3')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gas');
    }
};