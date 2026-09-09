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
        Schema::table('supirs', function (Blueprint $table) {
            if (!Schema::hasColumn('supirs', 'tipe')) {
                $table->string('tipe')->default('supir')->after('status');
            }
        });

        if (!Schema::hasTable('fasilitas_pengurus')) {
            Schema::create('fasilitas_pengurus', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('fasilitas_id');
                $table->unsignedBigInteger('pengurus_id');
                $table->timestamps();

                $table->foreign('fasilitas_id')->references('id')->on('fasilitas_umums')->onDelete('cascade');
                $table->foreign('pengurus_id')->references('id')->on('supirs')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fasilitas_pengurus');

        Schema::table('supirs', function (Blueprint $table) {
            if (Schema::hasColumn('supirs', 'tipe')) {
                $table->dropColumn('tipe');
            }
        });
    }
};
