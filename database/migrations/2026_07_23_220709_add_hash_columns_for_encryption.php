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
            $table->string('nik_hash')->nullable()->after('nik');
            $table->string('name_hash')->nullable()->after('name');
        });

        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->string('nik_from_ocr_hash')->nullable()->after('nik_from_ocr');
            $table->string('name_from_ocr_hash')->nullable()->after('name_from_ocr');
        });

        Schema::table('gas_orders', function (Blueprint $table) {
            $table->string('nomor_kk_hash')->nullable()->after('nomor_kk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nik_hash', 'name_hash']);
        });

        Schema::table('kyc_verifications', function (Blueprint $table) {
            $table->dropColumn(['nik_from_ocr_hash', 'name_from_ocr_hash']);
        });

        Schema::table('gas_orders', function (Blueprint $table) {
            $table->dropColumn('nomor_kk_hash');
        });
    }
};
