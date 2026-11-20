<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identity_cleanup_queue', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::table('identity_cleanup_queue')->exists()) {
            throw new RuntimeException('Selesaikan antrean penghapusan identitas sebelum rollback.');
        }
        Schema::dropIfExists('identity_cleanup_queue');
    }
};
