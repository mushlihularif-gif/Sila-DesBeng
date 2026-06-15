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
        Schema::create('mobils', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mobil');
            $table->text('deskripsi');
            $table->decimal('harga_sewa', 12, 2); // Harga per km
            $table->integer('stok')->default(1);
            $table->enum('status', ['tersedia', 'disewa', 'rusak'])->default('tersedia');
            $table->string('kategori')->default('Mobil');
            $table->string('foto')->nullable();
            $table->string('foto_2')->nullable();
            $table->string('foto_3')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('satuan')->default('km');
            $table->unsignedBigInteger('region_id')->nullable();
            $table->timestamps();

            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobils');
    }
};
