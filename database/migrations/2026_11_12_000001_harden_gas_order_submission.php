<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gas_orders', function (Blueprint $table) {
            $table->uuid('idempotency_key')->nullable();
            $table->string('submission_hash', 64)->nullable();
            $table->unique(['user_id', 'idempotency_key']);
        });
        Schema::table('family_cards', function (Blueprint $table) {
            $table->string('no_kk_hash')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('gas_orders', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'idempotency_key']);
            $table->dropColumn(['idempotency_key', 'submission_hash']);
        });
        // Pending KK submissions legitimately have no number hash yet.
    }
};
