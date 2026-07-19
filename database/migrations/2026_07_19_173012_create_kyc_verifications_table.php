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
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ktp_image_path')->nullable();
            $table->json('face_scan_data')->nullable(); // Capture frames
            $table->string('nik_from_ocr')->nullable();
            $table->string('name_from_ocr')->nullable();
            $table->string('address_from_ocr')->nullable();
            $table->string('rt_from_ocr')->nullable();
            $table->string('rw_from_ocr')->nullable();
            $table->string('kecamatan_from_ocr')->nullable();
            $table->string('desa_from_ocr')->nullable();
            $table->string('gender_from_ocr')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
