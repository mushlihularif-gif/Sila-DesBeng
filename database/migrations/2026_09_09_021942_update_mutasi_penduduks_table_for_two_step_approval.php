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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE mutasi_penduduks MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending'");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE mutasi_penduduks MODIFY COLUMN requested_by VARCHAR(50) NOT NULL DEFAULT 'user'");

        Schema::table('mutasi_penduduks', function (Blueprint $table) {
            $table->timestamp('approved_asal_at')->nullable()->after('rejection_reason');
            $table->timestamp('approved_tujuan_at')->nullable()->after('approved_asal_at');
            $table->string('rejected_by_role', 50)->nullable()->after('approved_tujuan_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_penduduks', function (Blueprint $table) {
            $table->dropColumn(['approved_asal_at', 'approved_tujuan_at', 'rejected_by_role']);
        });

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE mutasi_penduduks MODIFY COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE mutasi_penduduks MODIFY COLUMN requested_by ENUM('user','admin') NOT NULL DEFAULT 'user'");
    }
};
