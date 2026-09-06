<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus index unique lama pada kolom nik yang plain (karena nik kini terenkripsi ChaCha20)
            $table->dropUnique('users_nik_unique');

            // Perluas ukuran kolom nik menjadi 255 untuk menampung ciphertext ChaCha20
            $table->string('nik', 255)->nullable()->change();

            // Tambahkan index unique pada nik_hash untuk blind indexing & integritas data unik
            $table->unique('nik_hash', 'users_nik_hash_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_nik_hash_unique');
            $table->string('nik', 16)->nullable()->change();
            $table->unique('nik', 'users_nik_unique');
        });
    }
};
