<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Melebarkan kolom laporans yang isinya bukan lagi teks apa adanya.
 *
 * `nama` dan `lokasi` disimpan terenkripsi ChaCha20-Poly1305, dan ciphertext-nya
 * jauh lebih panjang daripada teks aslinya: 100 karakter menjadi 182, 150 menjadi
 * 250, dan 172 menjadi 266. Kolomnya masih varchar(255) sementara aturan
 * validasinya mengizinkan 255 karakter, jadi alamat sepanjang wajar — misalnya
 * "Jalan Raya Pematang Duku Timur Nomor 123 RT 004 RW 002 dekat simpang warung
 * Pak Haji Ahmad sebelah masjid Al-Ikhlas..." (172 karakter) — membuat
 * penyimpanan gagal dengan SQLSTATE[22001] karena sql_mode memakai
 * STRICT_TRANS_TABLES. Laporannya hilang dan warga hanya melihat galat umum.
 *
 * `bukti` juga dilebarkan: isinya JSON array jalur foto, bukan satu jalur.
 * Tiga foto saat ini memang masih muat, tetapi batas itu hanya soal aturan
 * validasi yang sewaktu-waktu dinaikkan — dan kalau terpotong, seluruh JSON-nya
 * rusak, bukan cuma foto terakhirnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporans', function (Blueprint $table) {
            $table->text('nama')->change();
            $table->text('lokasi')->change();
            $table->text('bukti')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('laporans', function (Blueprint $table) {
            $table->string('nama', 255)->change();
            $table->string('lokasi', 255)->change();
            $table->string('bukti', 255)->nullable()->change();
        });
    }
};
