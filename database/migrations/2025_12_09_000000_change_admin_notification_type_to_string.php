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
        Schema::table('admin_notifications', function (Blueprint $table) {
            // Change enum to string to support more types like 'stock_low'
            $table->string('type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_notifications', function (Blueprint $table) {
            // Revert back to enum if needed (might lose data if types outside enum exist)
            // flagging as string 255 for safety in down since we can't easily revert to restricted enum without data loss risk
             $table->string('type')->change();
        });
    }
};
