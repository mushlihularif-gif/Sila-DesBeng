<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengajuan penarikan saldo wilayah.
 *
 * Sejak Midtrans dipusatkan di Diskominfotik, uang warga dari semua desa
 * mendarat di satu rekening. Yang dimiliki wilayah adalah SALDO — catatan di
 * wallet_transactions — dan tabel inilah jalan untuk mengubah saldo itu
 * menjadi uang di rekening wilayahnya sendiri.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penarikan_saldo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->foreignId('diajukan_oleh')->constrained('users');

            $table->decimal('jumlah', 15, 2);

            // Rekening tujuan disalin apa adanya saat pengajuan dibuat, bukan
            // dibaca ulang dari wilayah saat dicairkan. Kalau admin daerah
            // mengganti rekeningnya di tengah antrean, uang tetap mengalir ke
            // rekening yang tercantum saat pengajuan disetujui.
            $table->string('nama_bank', 100);
            $table->text('no_rekening');
            $table->string('nama_pemilik', 150);

            $table->enum('status', ['pending', 'diproses', 'selesai', 'ditolak'])->default('pending');
            $table->text('catatan_admin')->nullable();

            $table->foreignId('diproses_oleh')->nullable()->constrained('users');
            $table->timestamp('diajukan_pada')->nullable();
            $table->timestamp('diselesaikan_pada')->nullable();

            // Jalur pencairan otomatis (Midtrans Iris / disbursement) belum
            // dipakai. Kolomnya disiapkan sekarang supaya menyambungkannya nanti
            // tinggal mengisi, tanpa memigrasi ulang tabel yang sudah berisi
            // riwayat keuangan.
            $table->string('payout_id')->nullable();
            $table->string('payout_status', 50)->nullable();

            $table->timestamps();

            $table->index(['region_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penarikan_saldo');
    }
};
