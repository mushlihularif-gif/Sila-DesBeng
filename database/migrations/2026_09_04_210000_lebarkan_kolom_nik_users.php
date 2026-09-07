<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Melebarkan users.nik agar muat menampung cipherteks.
 *
 * Kolomnya dibuat varchar(16) waktu NIK masih disimpan apa adanya. Sejak
 * User memakai cast ChaCha20Encrypted, yang benar-benar ditulis ke basis data
 * adalah cipherteks sepanjang ~70 karakter, sehingga setiap penyimpanan gagal:
 *
 *   SQLSTATE[22001]: Data too long for column 'nik' at row 1
 *
 * Akibatnya persetujuan KYC SELALU gagal - galatnya tertelan try/catch di
 * KycReviewController::approve() dan hanya muncul sebagai "Terjadi kesalahan",
 * sementara status warga diam-diam tetap 'pending'.
 *
 * Tanpa Doctrine DBAL terpasang, ->change() tidak tersedia, jadi dipakai SQL
 * mentah. Kolomnya nullable dan tidak berindeks, jadi pelebaran ini aman.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `users` MODIFY `nik` VARCHAR(255) NULL');
    }

    public function down(): void
    {
        // Menyempitkan kembali akan memotong cipherteks yang sudah tersimpan,
        // jadi isinya dikosongkan lebih dulu daripada rusak separuh.
        DB::table('users')->whereNotNull('nik')->update(['nik' => null]);
        DB::statement('ALTER TABLE `users` MODIFY `nik` VARCHAR(16) NULL');
    }
};
