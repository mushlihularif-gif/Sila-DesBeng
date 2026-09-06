<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alamat tersimpan milik warga — beberapa alamat per akun, satu ditandai utama.
 *
 * Polanya mengikuti buku alamat pembeli di toko daring: warga menyimpan alamat
 * sekali, lalu tinggal memilihnya saat memesan, tanpa mengetik ulang setiap kali.
 * Sebelum ini setiap formulir pemesanan (gas, sewa alat, mobil, fasilitas)
 * menyediakan kolom alamat kosong yang harus diisi dari nol berulang-ulang.
 *
 * Bedanya dengan buku alamat toko daring pada umumnya: wilayahnya tidak diketik
 * bebas, melainkan menunjuk baris `regions`, karena seluruh sistem ini sudah
 * memakai pohon wilayah yang sama untuk KYC, laporan, dan pembukuan saldo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alamat_warga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Desa/kelurahan tempat alamat ini berada. Boleh kosong untuk alamat
            // di luar wilayah yang terdaftar di sistem.
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();

            $table->string('label')->nullable();          // "Rumah", "Kantor", "Kebun"
            $table->string('nama_penerima');

            // Kolom terenkripsi dibuat panjang: cipherteks ChaCha20 jauh lebih
            // panjang daripada teks aslinya. Kolom users.nik dulu varchar(16)
            // untuk nilai terenkripsi ~70 karakter, dan setiap penyimpanan gagal
            // dengan "Data too long for column".
            $table->string('no_telepon', 255);
            $table->text('detail_alamat');

            $table->string('rt', 10)->nullable();
            $table->string('rw', 10)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('patokan')->nullable();        // "seberang masjid", "pagar hijau"

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->boolean('is_utama')->default(false);
            $table->timestamps();

            // Dipakai untuk mengambil alamat utama seorang warga.
            $table->index(['user_id', 'is_utama']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alamat_warga');
    }
};
