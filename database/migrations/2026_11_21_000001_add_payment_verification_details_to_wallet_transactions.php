<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wallet_transactions')) {
            return;
        }

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('wallet_transactions', 'verified_amount')) {
                $table->decimal('verified_amount', 15, 2)->nullable()->after('amount');
            }
            if (! Schema::hasColumn('wallet_transactions', 'detected_proof_amount')) {
                $table->decimal('detected_proof_amount', 15, 2)->nullable()->after('verified_amount');
            }
            if (! Schema::hasColumn('wallet_transactions', 'proof_scan_status')) {
                $table->string('proof_scan_status', 32)->nullable()->after('detected_proof_amount');
            }
            if (! Schema::hasColumn('wallet_transactions', 'proof_scan_meta')) {
                $table->json('proof_scan_meta')->nullable()->after('proof_scan_status');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('wallet_transactions')) {
            return;
        }

        Schema::table('wallet_transactions', function (Blueprint $table) {
            foreach (['proof_scan_meta', 'proof_scan_status', 'detected_proof_amount', 'verified_amount'] as $column) {
                if (Schema::hasColumn('wallet_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
