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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('location_name')->nullable(); // Nama lokasi (opsional)
            $table->decimal('latitude', 10, 7)->nullable(); // Latitude
            $table->decimal('longitude', 10, 7)->nullable(); // Longitude
            $table->text('address')->nullable(); // Alamat lengkap (opsional)
            $table->string('bank_name')->nullable(); // Nama Bank
            $table->string('bank_account_number')->nullable(); // Nomor Rekening
            $table->string('bank_account_holder')->nullable(); // Atas Nama
            $table->json('payment_methods')->nullable(); // Metode Pembayaran Aktif (Transfer, Tunai)
            $table->string('whatsapp_number')->nullable(); // Nomor WhatsApp
            $table->text('office_address')->nullable(); // Alamat Kantor
            $table->string('operating_hours')->nullable(); // Jam Operasional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};