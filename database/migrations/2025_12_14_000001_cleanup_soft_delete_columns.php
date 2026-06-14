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
        // Hapus kolom deleted_at dari rental_bookings jika ada (dari migration soft delete yang lama)
        if (Schema::hasColumn('rental_bookings', 'deleted_at')) {
            Schema::table('rental_bookings', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
            echo "✅ Kolom 'deleted_at' dihapus dari tabel 'rental_bookings'\n";
        } else {
            echo "ℹ️  Tabel 'rental_bookings' tidak memiliki kolom 'deleted_at', skip\n";
        }

        // Hapus kolom deleted_at dari gas_orders jika ada (dari migration soft delete yang lama)
        if (Schema::hasColumn('gas_orders', 'deleted_at')) {
            Schema::table('gas_orders', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
            echo "✅ Kolom 'deleted_at' dihapus dari tabel 'gas_orders'\n";
        } else {
            echo "ℹ️  Tabel 'gas_orders' tidak memiliki kolom 'deleted_at', skip\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tambahkan kembali jika rollback (tidak disarankan)
        Schema::table('rental_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('rental_bookings', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('gas_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('gas_orders', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }
};
