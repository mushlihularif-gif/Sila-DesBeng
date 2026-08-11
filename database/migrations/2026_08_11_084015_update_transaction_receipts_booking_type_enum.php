<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \DB::statement("ALTER TABLE transaction_receipts MODIFY COLUMN booking_type ENUM('rental', 'gas', 'pasar') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting this might drop data if 'pasar' rows exist, so it's safer to just leave it or revert carefully
        \DB::statement("ALTER TABLE transaction_receipts MODIFY COLUMN booking_type ENUM('rental', 'gas') NOT NULL");
    }
};
