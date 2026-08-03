<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domicile_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nama');
            $table->string('nik');
            $table->string('no_kk')->nullable();
            $table->string('desa_asal');
            $table->string('desa_tujuan');
            $table->string('alamat')->nullable();
            $table->string('status_pemohon')->nullable(); // Mandiri, Kepala Keluarga, dll
            $table->text('alasan');
            $table->enum('tipe', ['keluar', 'masuk'])->default('keluar');
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domicile_transfers');
    }
};
