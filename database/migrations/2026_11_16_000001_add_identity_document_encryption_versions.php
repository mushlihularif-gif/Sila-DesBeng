<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->unsignedTinyInteger('ktp_encryption_version')->default(1);
            $table->unsignedTinyInteger('face_encryption_version')->default(1);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('ktp_photo_encryption_version')->default(1);
            $table->unsignedTinyInteger('face_photo_encryption_version')->default(1);
        });
    }

    public function down(): void
    {
        // Never discard the trusted minimum version while bound files still exist.
        if (DB::table('kyc_verifications')->where('ktp_encryption_version', '>', 1)->orWhere('face_encryption_version', '>', 1)->exists()
            || DB::table('users')->where('ktp_photo_encryption_version', '>', 1)->orWhere('face_photo_encryption_version', '>', 1)->exists()) {
            throw new \RuntimeException('Dokumen versi 2 masih tercatat. Pulihkan kode dan data bersama cadangan yang sesuai.');
        }
        Schema::table('kyc_verifications', fn (Blueprint $table) => $table->dropColumn(['ktp_encryption_version', 'face_encryption_version']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['ktp_photo_encryption_version', 'face_photo_encryption_version']));
    }
};
