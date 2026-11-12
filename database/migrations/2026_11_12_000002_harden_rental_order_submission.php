<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->unsignedInteger('returned_quantity')->nullable();
            $table->text('return_notes')->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->uuid('idempotency_key')->nullable();
            $table->string('submission_hash', 64)->nullable();
            $table->unique(['user_id','idempotency_key'], 'rental_user_submission_unique');
        });
    }
    public function down(): void
    {
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->dropUnique('rental_user_submission_unique');
            $table->dropColumn(['unit_price','idempotency_key','submission_hash','returned_quantity','return_notes']);
        });
    }
};
