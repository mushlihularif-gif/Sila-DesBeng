<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_card_id')->constrained('family_cards')->onDelete('cascade');
            $table->string('nik_hash')->unique(); // Ensures 1 NIK can only be in 1 KK
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('family_members');
    }
};
