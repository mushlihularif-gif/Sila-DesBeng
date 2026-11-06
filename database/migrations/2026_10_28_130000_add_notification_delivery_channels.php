<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $t->json('categories')->nullable(); $t->boolean('push_enabled')->default(true);
            $t->timestamp('consented_at')->nullable(); $t->string('verified_phone_hash',64)->nullable();
            $t->string('otp_hash')->nullable(); $t->string('otp_phone_hash',64)->nullable();
            $t->timestamp('otp_expires_at')->nullable(); $t->unsignedTinyInteger('otp_attempts')->default(0); $t->timestamps();
        });
        Schema::create('notification_deliveries', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('source_kind',12); $t->unsignedBigInteger('source_id');
            $t->string('category',24); $t->string('channel',12); $t->string('state',24)->default('pending');
            $t->string('content_hash',64)->nullable();
            $t->unsignedBigInteger('recipient_region_id')->nullable();
            $t->unsignedTinyInteger('attempts')->default(0); $t->timestamp('available_at');
            $t->timestamp('claimed_at')->nullable(); $t->string('claim_token',64)->nullable();
            $t->string('provider_id')->nullable()->index(); $t->string('error_code')->nullable();
            $t->timestamp('delivered_at')->nullable(); $t->timestamps();
            $t->unique(['source_kind','source_id','user_id','channel'],'notification_delivery_unique');
            $t->index(['state','available_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries'); Schema::dropIfExists('notification_settings');
    }
};
