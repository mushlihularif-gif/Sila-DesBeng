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
            $table->string('cash_payment_description')->nullable()->after('payment_methods');
            $table->string('card_background_type')->default('gradient')->after('card_background_image');
            $table->string('card_gradient_style')->default('blue')->after('card_background_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['cash_payment_description', 'card_background_type', 'card_gradient_style']);
        });
    }
};
