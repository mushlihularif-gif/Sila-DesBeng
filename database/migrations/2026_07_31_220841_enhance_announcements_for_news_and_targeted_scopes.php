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
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('post_category')->default('Pengumuman')->after('id'); // 'Berita' atau 'Pengumuman'
            $table->string('target_audience_type')->nullable()->after('post_category'); // 'kabupaten', 'kecamatan', 'desa', 'rw', 'rt'
            $table->unsignedBigInteger('target_audience_id')->nullable()->after('target_audience_type');
        });

        Schema::create('announcement_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->string('image_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_images');
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['post_category', 'target_audience_type', 'target_audience_id']);
        });
    }
};
