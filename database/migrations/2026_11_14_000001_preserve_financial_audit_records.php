<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('manual_reports', fn (Blueprint $t) => $t->softDeletes());
        Schema::table('penarikan_saldo', fn (Blueprint $t) => $t->string('proof_path')->nullable());
        Schema::table('wallet_transactions', function (Blueprint $t) {
            $t->string('refund_status', 20)->nullable()->index();
            $t->string('refund_proof')->nullable();
            $t->timestamp('refunded_at')->nullable();
            $t->unsignedBigInteger('refunded_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('manual_reports', fn (Blueprint $t) => $t->dropSoftDeletes());
        Schema::table('penarikan_saldo', fn (Blueprint $t) => $t->dropColumn('proof_path'));
        Schema::table('wallet_transactions', fn (Blueprint $t) => $t->dropColumn(['refund_status', 'refund_proof', 'refunded_at', 'refunded_by']));
    }
};
