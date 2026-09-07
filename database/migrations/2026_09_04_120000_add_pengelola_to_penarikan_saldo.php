<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Membedakan dua jenis pengajuan pencairan di tabel yang sama.
 *
 * NULL   -> WILAYAH mengajukan ke Diskominfotik (yang sudah berjalan).
 * terisi -> MITRA mengajukan ke wilayahnya.
 *
 * Dipakai bersama, bukan tabel terpisah, karena bentuk datanya identik:
 * jumlah, rekening tujuan, status, catatan, siapa memproses. Yang berbeda
 * hanya siapa mengajukan ke siapa. Memisahkannya jadi dua tabel akan
 * menggandakan seluruh model, notifikasi, dan tampilan riwayat tanpa alasan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penarikan_saldo', function (Blueprint $table) {
            $table->foreignId('pengelola_user_id')
                ->nullable()
                ->after('region_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['pengelola_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('penarikan_saldo', function (Blueprint $table) {
            $table->dropForeign(['pengelola_user_id']);
            $table->dropIndex(['pengelola_user_id', 'status']);
            $table->dropColumn('pengelola_user_id');
        });
    }
};
