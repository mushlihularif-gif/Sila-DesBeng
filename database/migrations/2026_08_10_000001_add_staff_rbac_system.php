<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah enum role di tabel users untuk menambahkan 'staff'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin_kecamatan','admin_desa','admin','lurah','user','admin_rt','admin_rw','staff') DEFAULT 'user'");

        // 2. Tambah kolom created_by untuk mengetahui siapa yang membuat akun staf ini
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('id');
            // Menambahkan foreign key untuk integrity
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // 3. Buat tabel staff_permissions
        Schema::create('staff_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('unit_key'); // Contoh: 'gas', 'sewa_alat', dll
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_permissions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });

        // Kembalikan enum role tanpa 'staff'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin_kecamatan','admin_desa','admin','lurah','user','admin_rt','admin_rw') DEFAULT 'user'");
    }
};
