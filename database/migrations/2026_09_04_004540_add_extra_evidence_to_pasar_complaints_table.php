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
        Schema::table('pasar_complaints', function (Blueprint $table) {
            $table->string('evidence_4')->nullable()->after('evidence_3');
            $table->string('evidence_5')->nullable()->after('evidence_4');
            $table->string('evidence_video')->nullable()->after('evidence_5');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasar_complaints', function (Blueprint $table) {
            $table->dropColumn(['evidence_4', 'evidence_5', 'evidence_video']);
        });
    }
};
