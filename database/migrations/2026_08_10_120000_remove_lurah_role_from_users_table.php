<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migrasi Kritis: Penghapusan Total Role 'lurah' dari ENUM
 * 
 * Keputusan: Brain_SiladesBeng.md Bab 8.5 (10 Agustus 2026)
 * Alasan: Role lurah dianggap overengineering karena dinamika politik desa,
 *         Kades bukan operator IT, dan konflik akun ganda dengan akun warga.
 * Pengganti: Operator (Admin Desa / Staf BUMDes) + laporan PDF via WhatsApp.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Safety: Ubah semua user ber-role 'lurah' menjadi 'user' terlebih dahulu
        DB::table('users')->where('role', 'lurah')->update(['role' => 'user']);

        // Hapus 'lurah' dari ENUM role
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin_kecamatan','admin_desa','admin','user','admin_rt','admin_rw','staff') DEFAULT 'user'");
    }

    public function down(): void
    {
        // Kembalikan 'lurah' ke ENUM jika rollback diperlukan
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin_kecamatan','admin_desa','admin','lurah','user','admin_rt','admin_rw','staff') DEFAULT 'user'");
    }
};
