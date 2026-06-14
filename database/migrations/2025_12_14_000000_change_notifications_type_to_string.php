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
        // Cek apakah kolom type masih ENUM, baru ubah ke STRING
        $columnType = DB::select("
            SELECT DATA_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'notifications' 
            AND COLUMN_NAME = 'type'
        ");

        // Hanya jalankan ALTER jika kolom masih ENUM (belum STRING)
        if (!empty($columnType) && $columnType[0]->DATA_TYPE === 'enum') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type VARCHAR(255) NOT NULL DEFAULT 'pesan_admin'");
            echo "✅ Kolom 'type' berhasil diubah dari ENUM ke VARCHAR(255)\n";
        } else {
            echo "ℹ️  Kolom 'type' sudah VARCHAR, skip migration ini\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum jika rollback (hati-hati, data yang tidak sesuai enum akan error)
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM(
            'permintaan_baru',
            'pembayaran_masuk',
            'bukti_diupload',
            'status_berubah',
            'pengajuan_selesai',
            'lokasi_dikirim',
            'stok_menipis',
            'pesan_admin'
        ) NOT NULL DEFAULT 'pesan_admin'");
    }
};
