<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('supirs', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('status');
            $table->unsignedBigInteger('user_id')->nullable()->after('foto');
            $table->boolean('is_sewa_mobil')->default(false)->after('user_id');
            $table->boolean('is_fasilitas_umum')->default(false)->after('is_sewa_mobil');
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('mobils', function (Blueprint $table) {
            $table->dropForeign(['supir_id']);
            $table->dropForeign(['supir_borongan_id']);
            $table->dropColumn(['supir_id', 'supir_borongan_id', 'opsi_supir', 'nama_supir', 'kontak_supir', 'opsi_supir_borongan', 'nama_supir_borongan', 'kontak_supir_borongan']);
        });

        if (Schema::hasColumn('fasilitas_umums', 'supir_id')) {
            Schema::table('fasilitas_umums', function (Blueprint $table) {
                $table->dropForeign(['supir_id']);
                $table->dropColumn('supir_id');
            });
        }

        Schema::create('mobil_supir', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mobil_id');
            $table->unsignedBigInteger('supir_id');
            $table->timestamps();

            $table->foreign('mobil_id')->references('id')->on('mobils')->onDelete('cascade');
            $table->foreign('supir_id')->references('id')->on('supirs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mobil_supir');

        Schema::table('supirs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['foto', 'user_id', 'is_sewa_mobil', 'is_fasilitas_umum']);
        });

        Schema::table('mobils', function (Blueprint $table) {
            $table->unsignedBigInteger('supir_id')->nullable();
            $table->unsignedBigInteger('supir_borongan_id')->nullable();
            $table->string('opsi_supir')->nullable();
            $table->string('nama_supir')->nullable();
            $table->string('kontak_supir')->nullable();
            $table->string('opsi_supir_borongan')->nullable();
            $table->string('nama_supir_borongan')->nullable();
            $table->string('kontak_supir_borongan')->nullable();
            
            $table->foreign('supir_id')->references('id')->on('supirs')->onDelete('set null');
            $table->foreign('supir_borongan_id')->references('id')->on('supirs')->onDelete('set null');
        });
        
        if (!Schema::hasColumn('fasilitas_umums', 'supir_id')) {
            Schema::table('fasilitas_umums', function (Blueprint $table) {
                $table->unsignedBigInteger('supir_id')->nullable();
                $table->foreign('supir_id')->references('id')->on('supirs')->onDelete('set null');
            });
        }
    }
};
