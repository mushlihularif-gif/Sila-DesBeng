<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->string('kategori');
            $table->string('lokasi');
            $table->string('rw')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw_number')->nullable();
            $table->string('rt_number')->nullable();
            $table->text('deskripsi');
            $table->string('bukti')->nullable();
            $table->enum('status', ['Pending', 'Proses', 'Dilanjutkan', 'Selesai', 'Ditolak'])->default('Pending');
            $table->enum('escalation_level', ['rt', 'rw', 'admin'])->default('rt');
            $table->foreignId('rt_handler_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rw_handler_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('catatan_rt')->nullable();
            $table->text('catatan_rw')->nullable();
            $table->timestamp('escalated_to_rw_at')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('rating');
            $table->text('feedback')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('laporan_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('link')->nullable();
            $table->string('icon')->nullable();
        });

        Schema::create('help_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->string('category');
            $table->text('description');
            $table->string('screenshot')->nullable();
            $table->string('priority')->default('normal');
            $table->string('status')->default('baru');
            $table->text('admin_response')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('kritik_saran', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('whatsapp')->nullable();
            $table->enum('jenis', ['Kritik', 'Saran', 'Keluhan', 'Pujian']);
            $table->text('pesan');
            $table->string('owner')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kritik_saran');
        Schema::dropIfExists('help_tickets');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('laporans');
    }
};
