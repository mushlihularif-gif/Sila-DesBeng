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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique()->nullable(); // Dari form, nullable untuk Google Login
            $table->string('name');               // Nama Lengkap
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable(); // Nullable untuk Google Login
            $table->string('phone')->nullable();  // No Telepon
            $table->text('address')->nullable();  // Alamat
            $table->string('rt')->nullable();     // Tambahan dari i_vilagge
            $table->string('rw')->nullable();     // Tambahan dari i_vilagge
            $table->enum('gender', ['laki-laki', 'perempuan'])->nullable(); // Jenis Kelamin
            $table->enum('status', ['aktif', 'non_aktif'])->default('aktif'); // Status akun
            $table->enum('role', ['admin', 'lurah', 'user'])->default('user'); // Tambah lurah
            $table->string('otp_code', 4)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};