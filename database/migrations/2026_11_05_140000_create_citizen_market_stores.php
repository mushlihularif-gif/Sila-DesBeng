<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('market_stores', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $t->foreignId('region_id')->constrained()->restrictOnDelete();
            $t->string('name', 100); $t->text('description'); $t->string('category', 100);
            $t->text('address'); $t->decimal('latitude',10,7)->nullable(); $t->decimal('longitude',10,7)->nullable();
            $t->string('avatar')->nullable(); $t->string('cover')->nullable(); $t->unsignedTinyInteger('cover_position')->default(50);
            $t->string('status',20)->default('pending')->index(); $t->boolean('is_open')->default(true);
            $t->text('review_reason')->nullable(); $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('reviewed_at')->nullable(); $t->json('settings')->nullable(); $t->timestamps();
        });
        Schema::table('pasar_produks', fn (Blueprint $t) => $t->foreignId('store_id')->nullable()->constrained('market_stores')->restrictOnDelete());
        Schema::table('pasar_orders', function (Blueprint $t) {
            $t->foreignId('store_id')->nullable()->constrained('market_stores')->restrictOnDelete();
            $t->json('payment_settings_snapshot')->nullable();
        });
        Schema::create('market_payments', function (Blueprint $t) {
            $t->id(); $t->foreignId('pasar_order_id')->unique()->constrained()->restrictOnDelete();
            $t->foreignId('store_id')->constrained('market_stores')->restrictOnDelete();
            $t->decimal('amount',14,2); $t->string('method',30); $t->string('status',30)->default('pending');
            $t->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $t->text('note')->nullable(); $t->timestamps();
        });
        Schema::create('market_store_events', function (Blueprint $t) {
            $t->id(); $t->foreignId('store_id')->constrained('market_stores')->restrictOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action',50); $t->text('note')->nullable(); $t->timestamps();
        });
        Schema::create('market_store_messages', function (Blueprint $t) {
            $t->id(); $t->foreignId('store_id')->constrained('market_stores')->restrictOnDelete();
            $t->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $t->foreignId('sender_id')->constrained('users')->restrictOnDelete();
            $t->text('message'); $t->timestamps(); $t->index(['store_id','buyer_id','id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_store_messages'); Schema::dropIfExists('market_store_events'); Schema::dropIfExists('market_payments');
        Schema::table('pasar_orders', function (Blueprint $t) { $t->dropConstrainedForeignId('store_id'); $t->dropColumn('payment_settings_snapshot'); });
        Schema::table('pasar_produks', fn (Blueprint $t) => $t->dropConstrainedForeignId('store_id'));
        Schema::dropIfExists('market_stores');
    }
};
