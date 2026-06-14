<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePinjamanTable extends Migration
{
    public function up(): void
    {
        Schema::create('pinjaman', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Relasi ke user peminjam
            $table->string('nama_peminjam');
            $table->string('jenis_usaha')->nullable(); // Bidang usaha peminjam
            $table->decimal('jumlah_pinjaman', 15, 2);
            $table->decimal('bunga', 5, 2)->default(0.00); // Misalnya 2.5%
            $table->integer('lama_angsuran'); // Dalam bulan
            $table->decimal('angsuran_bulanan', 15, 2); // Jumlah angsuran per bulan
            $table->enum('status', ['diajukan', 'disetujui', 'ditolak', 'lunas', 'macet'])->default('diajukan');
            $table->date('tanggal_pengajuan');
            $table->date('tanggal_mulai')->nullable(); // Tanggal mulai angsuran
            $table->date('tanggal_lunas')->nullable(); // Tanggal pelunasan
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Jika user_id digunakan, tambahkan foreign key
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('pinjaman');
    }
}