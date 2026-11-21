<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_chat_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('unit_chat_sessions', 'user_cleared_before_id')) {
                $table->unsignedBigInteger('user_cleared_before_id')->nullable()->after('unread_user_count');
            }
            if (!Schema::hasColumn('unit_chat_sessions', 'admin_cleared_before_id')) {
                $table->unsignedBigInteger('admin_cleared_before_id')->nullable()->after('user_cleared_before_id');
            }
            if (!Schema::hasColumn('unit_chat_sessions', 'cleared_by_admins')) {
                $table->json('cleared_by_admins')->nullable()->after('admin_cleared_before_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('unit_chat_sessions', function (Blueprint $table) {
            $cols = array_values(array_filter(
                ['user_cleared_before_id', 'admin_cleared_before_id', 'cleared_by_admins'],
                fn ($col) => Schema::hasColumn('unit_chat_sessions', $col)
            ));
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
