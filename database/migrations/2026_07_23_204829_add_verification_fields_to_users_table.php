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
            $table->string('ktp_photo_path')->nullable()->after('verification_status');
            $table->string('face_photo_path')->nullable()->after('ktp_photo_path');
            $table->text('ktp_rejection_reason')->nullable()->after('face_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['ktp_photo_path', 'face_photo_path', 'ktp_rejection_reason']);
        });
    }
};
