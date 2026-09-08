<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Batalkan pembagian hasil ke mitra — kembali ke SATU KAS DAERAH.
 *
 * Rancangan sebelumnya memecah tiap pemasukan jadi dua baris: porsi wilayah dan
 * porsi mitra pengelola unit, lalu mitra mengajukan pencairan kepada wilayah.
 * Rancangan itu dibatalkan: seluruh pemasukan tetap menjadi satu kas wilayah,
 * dan pembayaran kepada pihak ketiga diurus bendahara desa lewat transfer manual
 * di luar sistem.
 *
 * Urutannya penting. Kepemilikan dikembalikan ke wilayah LEBIH DULU, baru
 * kolomnya dibuang — kalau kolomnya langsung di-drop, uang yang terlanjur
 * tercatat sebagai porsi mitra akan hilang dari pembukuan tanpa jejak.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Kembalikan pemasukan porsi mitra menjadi hak wilayah penuh.
        if (Schema::hasColumn('wallet_transactions', 'pengelola_user_id')) {
            $jumlah = DB::table('wallet_transactions')->whereNotNull('pengelola_user_id')->count();

            DB::table('wallet_transactions')
                ->whereNotNull('pengelola_user_id')
                ->update([
                    'pengelola_user_id' => null,
                    'notes' => DB::raw("CONCAT(COALESCE(notes, ''), ' (dikembalikan ke kas wilayah)')"),
                ]);

            logger()->info("Pembagian mitra dibatalkan: {$jumlah} baris pemasukan dikembalikan ke kas wilayah.");
        }

        // 2. Pencairan yang ditujukan ke mitra tidak lagi punya arti.
        if (Schema::hasColumn('penarikan_saldo', 'pengelola_user_id')) {
            DB::table('penarikan_saldo')->whereNotNull('pengelola_user_id')->delete();
        }

        // 3. Buang kolomnya. Kunci asing dilepas lebih dulu; namanya mengikuti
        //    pola bawaan Laravel <tabel>_<kolom>_foreign.
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transactions', 'pengelola_user_id')) {
                try { $table->dropForeign('wallet_transactions_pengelola_user_id_foreign'); } catch (\Throwable $e) {}
                $table->dropColumn('pengelola_user_id');
            }
        });

        Schema::table('penarikan_saldo', function (Blueprint $table) {
            if (Schema::hasColumn('penarikan_saldo', 'pengelola_user_id')) {
                try { $table->dropForeign('penarikan_saldo_pengelola_user_id_foreign'); } catch (\Throwable $e) {}
                $table->dropColumn('pengelola_user_id');
            }
        });

        // 4. Daftar pengelola unit tidak dipakai lagi.
        Schema::dropIfExists('pengelola_unit');
    }

    public function down(): void
    {
        // Strukturnya bisa dikembalikan, tetapi kepemilikan porsi mitra yang
        // sudah dilebur ke kas wilayah TIDAK dapat dipulihkan — informasinya
        // memang sudah tidak ada lagi.
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('wallet_transactions', 'pengelola_user_id')) {
                $table->foreignId('pengelola_user_id')->nullable()->after('region_id')
                      ->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('penarikan_saldo', function (Blueprint $table) {
            if (! Schema::hasColumn('penarikan_saldo', 'pengelola_user_id')) {
                $table->foreignId('pengelola_user_id')->nullable()->after('region_id')
                      ->constrained('users')->nullOnDelete();
            }
        });

        if (! Schema::hasTable('pengelola_unit')) {
            Schema::create('pengelola_unit', function (Blueprint $table) {
                $table->id();
                $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('unit_key');
                $table->boolean('is_mitra')->default(false);
                $table->decimal('bagi_hasil_persen', 5, 2)->default(0);
                $table->boolean('auto_setujui')->default(false);
                $table->timestamps();
                $table->unique(['region_id', 'unit_key']);
            });
        }
    }
};
