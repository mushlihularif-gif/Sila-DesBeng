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
            $table->string('google_client_id')->nullable()->after('platform_fee_percentage');
            $table->text('google_client_secret')->nullable()->after('google_client_id');
            $table->text('google_maps_api_key')->nullable()->after('google_client_secret');
            $table->string('midtrans_merchant_id')->nullable()->after('google_maps_api_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn(['google_client_id', 'google_client_secret', 'google_maps_api_key', 'midtrans_merchant_id']);
        });
    }
};
