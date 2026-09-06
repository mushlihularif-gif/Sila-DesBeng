<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Status 'dibatalkan': pengajuan yang ditarik kembali oleh wilayahnya sendiri.
 *
 * Sengaja dibedakan dari 'ditolak' (kini bermakna "tidak bisa diproses" oleh
 * Diskominfotik). Keduanya sama-sama mengembalikan saldo, tapi riwayatnya harus
 * jujur menyebut siapa yang menghentikan: wilayah menarik diri sendiri, atau
 * Diskominfotik terkendala. Menggabungkan keduanya akan membuat kelalaian
 * penampung dana terlihat seperti keputusan daerah.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE penarikan_saldo MODIFY COLUMN status
            ENUM('pending', 'diproses', 'selesai', 'ditolak', 'dibatalkan')
            NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Baris yang terlanjur 'dibatalkan' dikembalikan ke 'ditolak' supaya
        // tidak ada nilai di luar enum lama saat kolomnya dipersempit.
        DB::table('penarikan_saldo')->where('status', 'dibatalkan')->update(['status' => 'ditolak']);

        DB::statement("ALTER TABLE penarikan_saldo MODIFY COLUMN status
            ENUM('pending', 'diproses', 'selesai', 'ditolak')
            NOT NULL DEFAULT 'pending'");
    }
};
