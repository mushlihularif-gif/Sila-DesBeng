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
        Schema::table('gas_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('gas_orders', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gas_orders', function (Blueprint $table) {
            if (Schema::hasColumn('gas_orders', 'confirmed_at')) {
                $table->dropColumn('confirmed_at');
            }
        });
    }
};
