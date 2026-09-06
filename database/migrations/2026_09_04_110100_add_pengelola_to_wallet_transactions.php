<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menandai baris ledger yang uangnya BUKAN milik wilayah.
 *
 * Sebelum ini semua pemasukan dianggap milik wilayah. Begitu ada unit yang
 * dikelola mitra, anggapan itu salah dan berbahaya: kepala desa akan melihat
 * saldo yang sebagian bukan haknya, lalu bisa mencairkannya lebih dulu.
 *
 * NULL  -> baris ini milik wilayah (perilaku lama, tetap berlaku).
 * terisi -> milik mitra tersebut; tidak ikut dihitung ke saldo wilayah.
 *
 * Satu transaksi berbagi hasil menghasilkan DUA baris yang menunjuk pesanan
 * yang sama: porsi wilayah (NULL) dan porsi mitra (user_id mitra). Memecahnya
 * jadi dua baris, bukan satu baris dengan dua kolom nominal, membuat
 * perhitungan saldo tetap satu rumus untuk semua pihak.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['pengelola_user_id']);
            $table->dropIndex(['pengelola_user_id', 'status']);
            $table->dropColumn('pengelola_user_id');
        });
    }
};
