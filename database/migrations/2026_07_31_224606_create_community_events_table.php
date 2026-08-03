<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('judul');
            $table->enum('tipe', ['gotong_royong', 'rapat', 'kegiatan_sosial'])->default('gotong_royong');
            $table->string('target_scope')->nullable(); // Tingkat RT, RW, Dusun
            $table->string('rw')->nullable();
            $table->string('rt')->nullable();
            $table->string('koordinator')->nullable();
            $table->string('jadwal')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('catatan')->nullable();
            $table->json('peralatan')->nullable();
            $table->string('poster_path')->nullable();
            $table->enum('status', ['upcoming', 'ongoing', 'completed', 'cancelled'])->default('upcoming');
            $table->integer('jumlah_peserta')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_events');
    }
};
