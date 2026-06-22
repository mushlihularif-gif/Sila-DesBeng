<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE transaction_receipts MODIFY COLUMN payment_method VARCHAR(255) NOT NULL DEFAULT 'tunai'");
        DB::statement("ALTER TABLE rental_bookings MODIFY COLUMN payment_method VARCHAR(255) NOT NULL DEFAULT 'tunai'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transaction_receipts MODIFY COLUMN payment_method ENUM('transfer', 'tunai') NOT NULL");
        DB::statement("ALTER TABLE rental_bookings MODIFY COLUMN payment_method ENUM('transfer', 'tunai') NOT NULL DEFAULT 'transfer'");
    }
};
