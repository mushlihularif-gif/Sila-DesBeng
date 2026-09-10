<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddLevelToBumdesMembersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('bumdes_members', 'level')) {
            Schema::table('bumdes_members', function (Blueprint $table) {
                $table->unsignedTinyInteger('level')->default(1)->after('position');
            });
        }

        // Penataan awal data eksisting berdasarkan nama jabatan
        // Tingkat 1: Pimpinan Utama
        DB::table('bumdes_members')->where(function($q) {
            $q->where('position', 'LIKE', '%Kepala Desa%')
              ->orWhere('position', 'LIKE', '%Kades%')
              ->orWhere('position', 'LIKE', '%Lurah%')
              ->orWhere('position', 'LIKE', '%Camat%')
              ->orWhere('position', 'LIKE', '%Bupati%');
        })->where(function($q) {
            $q->where('position', 'NOT LIKE', '%Wakil%')
              ->where('position', 'NOT LIKE', '%Sekretaris%');
        })->update(['level' => 1]);

        // Tingkat 2: Pimpinan Kedua / Sekretaris / Wakil
        DB::table('bumdes_members')->where(function($q) {
            $q->where('position', 'LIKE', '%Sekretaris%')
              ->orWhere('position', 'LIKE', '%Sekdes%')
              ->orWhere('position', 'LIKE', '%Sekcam%')
              ->orWhere('position', 'LIKE', '%Sekda%')
              ->orWhere('position', 'LIKE', '%Wakil%');
        })->update(['level' => 2]);

        // Tingkat 3: Kepala Seksi / Kaur / Kepala Unit / Direktur
        DB::table('bumdes_members')->where(function($q) {
            $q->where('position', 'LIKE', '%Kasi%')
              ->orWhere('position', 'LIKE', '%Kaur%')
              ->orWhere('position', 'LIKE', '%Kepala Seksi%')
              ->orWhere('position', 'LIKE', '%Kepala Urusan%')
              ->orWhere('position', 'LIKE', '%Kepala Bagian%')
              ->orWhere('position', 'LIKE', '%Kabid%')
              ->orWhere('position', 'LIKE', '%Direktur%')
              ->orWhere('position', 'LIKE', '%Kepala Unit%')
              ->orWhere('position', 'LIKE', '%Ketua%');
        })->update(['level' => 3]);

        // Tingkat 4: Staf Pelaksana / Dusun / Anggota Lainnya
        DB::table('bumdes_members')->where(function($q) {
            $q->where('position', 'LIKE', '%Kadus%')
              ->orWhere('position', 'LIKE', '%Dusun%')
              ->orWhere('position', 'LIKE', '%Staf%')
              ->orWhere('position', 'LIKE', '%Anggota%')
              ->orWhere('position', 'LIKE', '%Operator%');
        })->update(['level' => 4]);
    }

    public function down()
    {
        if (Schema::hasColumn('bumdes_members', 'level')) {
            Schema::table('bumdes_members', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        }
    }
}
