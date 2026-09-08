<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Siapa yang mengelola satu unit layanan di satu wilayah, dan apakah
 * pemasukannya miliknya atau milik wilayah.
 *
 * Kepemilikan sengaja dicatat pada tingkat (wilayah + unit layanan), bukan per
 * barang: "gas di Desa A dikelola Pak Budi" — bukan tiap tabung punya pemilik
 * sendiri. Ini mengikuti struktur izin staf yang sudah ada (staff_permissions
 * menautkan user ke unit_key), jadi pengelola = staf yang memang sudah
 * memegang unit itu.
 *
 * tipe 'daerah'  -> unit dikelola perangkat desa sendiri; pemasukan milik wilayah.
 * tipe 'mitra'   -> unit dikelola pihak ketiga; pemasukannya HAK MITRA, wilayah
 *                   hanya menampung dan mengambil bagi hasil sesuai persentase.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengelola_unit', function (Blueprint $table) {
            $table->id();

            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();

            // Nilai mengikuti App\Models\User::IZIN_UNIT — 'gas', 'sewa_alat',
            // 'sewa_mobil', 'fasilitas_umum', 'pasar_daerah'.
            $table->string('unit_key', 50);

            // Staf/mitra yang memegang unit ini. Boleh kosong untuk unit yang
            // dikelola desa sendiri tanpa penanggung jawab khusus.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('tipe', ['daerah', 'mitra'])->default('daerah');

            // Berapa persen dari tiap transaksi yang menjadi hak WILAYAH.
            // Untuk tipe 'daerah' nilainya diabaikan (wilayah dapat 100%).
            $table->decimal('bagi_hasil_persen', 5, 2)->default(0);

            // Rekening tujuan pencairan milik mitra. Disalin ke pengajuan saat
            // dibuat, sama seperti pola di penarikan_saldo.
            $table->string('nama_bank', 100)->nullable();
            $table->text('no_rekening')->nullable();
            $table->string('nama_pemilik', 150)->nullable();

            // Wilayah boleh menyetujui pencairan mitra secara otomatis selama
            // saldonya cukup, supaya mitra tidak menunggu dua lapis proses manual.
            $table->boolean('auto_setujui')->default(false);

            $table->boolean('aktif')->default(true);
            $table->timestamps();

            // Satu unit layanan di satu wilayah hanya punya satu pengelola.
            $table->unique(['region_id', 'unit_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengelola_unit');
    }
};
