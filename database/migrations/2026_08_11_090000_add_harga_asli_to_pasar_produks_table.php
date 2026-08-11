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
        Schema::table('pasar_produks', function (Blueprint $table) {
            $table->decimal('harga_asli', 12, 2)->nullable()->after('harga');
        });

        \DB::table('pasar_produks')->where('harga', 800000)->update(['harga_asli' => 890000]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasar_produks', function (Blueprint $table) {
            $table->dropColumn('harga_asli');
        });
    }
};
