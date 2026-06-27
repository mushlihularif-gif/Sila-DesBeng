<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRegionIdToBumdesMembersTable extends Migration
{
    public function up()
    {
        Schema::table('bumdes_members', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('id')->constrained('regions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('bumdes_members', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');
        });
    }
}
