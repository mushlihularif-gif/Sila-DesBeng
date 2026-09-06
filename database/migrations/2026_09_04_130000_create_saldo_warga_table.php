<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dompet warga: satu buku besar untuk seluruh riwayat saldo miliknya.
 *
 * Dibuat karena ada lubang nyata: saat pesanan yang SUDAH DIBAYAR ditolak,
 * baris ledger wilayah hanya diberi status 'rejected'. Itu mengeluarkannya dari
 * saldo wilayah tapi tidak memasukkannya ke siapa pun — uangnya benar-benar ada
 * di rekening Midtrans Diskominfotik, namun tidak muncul di layar mana pun.
 * Warga kehilangan uang dan sistem berpura-pura uang itu tidak pernah ada.
 *
 * Tiga jenis baris hidup berdampingan di sini supaya seluruh cerita satu dompet
 * terbaca dari satu tempat:
 *
 *   refund    -> uang MASUK, dikembalikan karena pesanan batal/gagal
 *   belanja   -> uang KELUAR, dipakai membayar pesanan berikutnya
 *   penarikan -> uang KELUAR, dicairkan ke rekening warga (punya alur status)
 *
 * Saldo = refund selesai - belanja - penarikan yang masih berjalan/selesai.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldo_warga', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Wilayah asal pesanannya — untuk penelusuran, bukan kepemilikan.
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();

            $table->enum('type', ['refund', 'belanja', 'penarikan']);
            $table->decimal('amount', 15, 2);

            // Pesanan yang memicu baris ini. Kosong untuk penarikan.
            $table->string('reference_type', 30)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            // refund & belanja langsung 'selesai'; penarikan melewati alurnya.
            $table->enum('status', ['selesai', 'pending', 'diproses', 'ditolak'])->default('selesai');

            // Rekening tujuan, hanya untuk penarikan. Disalin saat pengajuan
            // dibuat, seperti pola di penarikan_saldo.
            $table->string('nama_bank', 100)->nullable();
            $table->text('no_rekening')->nullable();
            $table->string('nama_pemilik', 150)->nullable();

            $table->text('catatan')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diselesaikan_pada')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'type', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_warga');
    }
};
