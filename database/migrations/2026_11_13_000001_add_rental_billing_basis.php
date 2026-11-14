<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::table('barang', fn (Blueprint $t) => $t->string('billing_basis', 20)->nullable());
        // Preserve the agreed single-use tariff for existing catalog items only.
        DB::table('barang')->update(['billing_basis' => 'per_activity']);
        Schema::table('rental_bookings', fn (Blueprint $t) => $t->string('billing_basis', 20)->nullable());
    }

    public function down(): void
    {
        Schema::table('rental_bookings', fn (Blueprint $t) => $t->dropColumn('billing_basis'));
        Schema::table('barang', fn (Blueprint $t) => $t->dropColumn('billing_basis'));
    }
};
