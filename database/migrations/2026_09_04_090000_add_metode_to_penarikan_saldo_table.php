<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tujuan pencairan: rekening bank atau e-wallet.
 *
 * Kolom nama_bank/no_rekening sebenarnya sudah cukup umum untuk menampung
 * keduanya ("DANA" + nomor HP juga muat di sana), tetapi petugas Diskominfotik
 * yang mentransfer perlu tahu ini masuk ke m-banking atau ke aplikasi dompet
 * digital - dua alur transfer yang berbeda. Menyimpannya eksplisit lebih jujur
 * daripada menebak dari nama banknya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penarikan_saldo', function (Blueprint $table) {
            $table->enum('metode', ['bank', 'ewallet'])->default('bank')->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('penarikan_saldo', function (Blueprint $table) {
            $table->dropColumn('metode');
        });
    }
};
