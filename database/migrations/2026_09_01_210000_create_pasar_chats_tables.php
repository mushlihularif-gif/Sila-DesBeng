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
        Schema::create('pasar_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('session_token', 64)->nullable()->index();
            $table->string('user_name')->nullable();
            $table->enum('status', ['bot', 'escalated', 'resolved', 'closed'])->default('bot');
            $table->text('last_message')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->unsignedInteger('unread_admin_count')->default(0);
            $table->unsignedInteger('unread_user_count')->default(0);
            $table->timestamps();
        });

        Schema::create('pasar_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('pasar_chat_sessions')->onDelete('cascade');
            $table->enum('sender_type', ['user', 'bot', 'admin']);
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasar_chat_messages');
        Schema::dropIfExists('pasar_chat_sessions');
    }
};
