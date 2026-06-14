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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul notifikasi
            $table->text('message'); // Isi notifikasi
            $table->enum('type', [
                'permintaan_baru',
                'pembayaran_masuk',
                'bukti_diupload',
                'status_berubah',
                'pengajuan_selesai',
                'lokasi_dikirim',
                'stok_menipis',
                'pesan_admin' // Tipe untuk notifikasi dari admin ke user
            ])->default('pesan_admin'); // Default ke pesan admin
            $table->unsignedBigInteger('user_id')->nullable(); // ID user yang terkait (jika ada)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null'); // Relasi ke user
            $table->unsignedBigInteger('admin_id')->nullable(); // ID admin yang membuat (jika dari admin)
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null'); // Asumsi admin juga di tabel users
            $table->boolean('is_read')->default(false); // Status sudah dibaca
            $table->timestamp('read_at')->nullable(); // Waktu dibaca
            $table->timestamp('sent_at')->nullable(); // Waktu dikirim (jika dari admin)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};