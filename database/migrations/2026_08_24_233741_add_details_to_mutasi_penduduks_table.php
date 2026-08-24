<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('mutasi_penduduks', function (Blueprint $table) {
            $table->string('alamat_baru')->nullable();
            $table->string('rt_baru')->nullable();
            $table->string('rw_baru')->nullable();
            $table->string('ktp_image_path')->nullable();
        });
    }

    public function down(): void {
        Schema::table('mutasi_penduduks', function (Blueprint $table) {
            $table->dropColumn(['alamat_baru', 'rt_baru', 'rw_baru', 'ktp_image_path']);
        });
    }
};
