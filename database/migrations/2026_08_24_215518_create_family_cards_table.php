<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('family_cards', function (Blueprint $table) {
            $table->id();
            $table->string('no_kk_hash')->unique();
            $table->string('no_kk_masked')->nullable();
            $table->string('kepala_keluarga_masked')->nullable();
            $table->string('kk_image_path')->nullable(); // Sementara untuk proses review
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('family_cards');
    }
};
