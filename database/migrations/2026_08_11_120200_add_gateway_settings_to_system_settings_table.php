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
            $table->string('gateway_provider')->nullable()->after('payment_methods');
            $table->text('gateway_secret_key')->nullable()->after('gateway_provider');
            $table->text('gateway_public_key')->nullable()->after('gateway_secret_key');
            $table->boolean('gateway_is_production')->default(false)->after('gateway_public_key');
            $table->decimal('platform_fee_percentage', 5, 2)->default(0)->after('gateway_is_production');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'gateway_provider',
                'gateway_secret_key',
                'gateway_public_key',
                'gateway_is_production',
                'platform_fee_percentage',
            ]);
        });
    }
};
