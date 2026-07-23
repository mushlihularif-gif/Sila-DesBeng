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
        Schema::table('fasilitas_umum_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('fasilitas_umum_bookings', 'jenis_acara')) {
                $table->string('jenis_acara')->nullable()->after('rental_purpose');
            }
            if (!Schema::hasColumn('fasilitas_umum_bookings', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('fasilitas_umum_bookings', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('fasilitas_umum_bookings', 'payment_status')) {
                $table->string('payment_status')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('fasilitas_umum_bookings', 'payment_proof')) {
                $table->string('payment_proof')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('fasilitas_umum_bookings', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('payment_proof');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fasilitas_umum_bookings', function (Blueprint $table) {
            $columns = ['jenis_acara', 'total_amount', 'payment_method', 'payment_status', 'payment_proof', 'receipt_path'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('fasilitas_umum_bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
