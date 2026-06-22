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
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('ewallet_name')->nullable()->after('bank_account_holder');
            $table->text('ewallet_number')->nullable()->after('ewallet_name');
            $table->text('ewallet_account_holder')->nullable()->after('ewallet_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['ewallet_name', 'ewallet_number', 'ewallet_account_holder']);
        });
    }
};
