<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * SKENARIO KEAMANAN: UUID (Universally Unique Identifier)
 * 
 * Menambahkan kolom UUID publik ke tabel-tabel transaksi utama.
 * UUID digunakan sebagai identifier di URL, menggantikan ID numerik
 * yang mudah ditebak (1, 2, 3...) untuk mencegah IDOR / ID Guessing.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan UUID ke tabel rental_bookings
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id')->nullable();
        });

        // Tambahkan UUID ke tabel gas_orders
        Schema::table('gas_orders', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id')->nullable();
        });

        // Tambahkan UUID ke tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id')->nullable();
        });

        // Generate UUID untuk data yang sudah ada
        foreach (\App\Models\RentalBooking::withTrashed()->whereNull('uuid')->cursor() as $booking) {
            $booking->update(['uuid' => Str::uuid()->toString()]);
        }
        foreach (\App\Models\GasOrder::withTrashed()->whereNull('uuid')->cursor() as $order) {
            $order->update(['uuid' => Str::uuid()->toString()]);
        }
        foreach (\App\Models\User::whereNull('uuid')->cursor() as $user) {
            $user->update(['uuid' => Str::uuid()->toString()]);
        }
    }

    public function down(): void
    {
        Schema::table('rental_bookings', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('gas_orders', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
