<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\IdentityDatabase;
use Tests\TestCase;

class DeploymentAuditTest extends TestCase
{
    use IdentityDatabase;

    public function test_audit_is_read_only_reports_missing_points_and_detects_unsupported_roles(): void
    {
        DB::table('regions')->insert(['id' => 10, 'name' => 'Kecamatan Bengkalis', 'type' => 'kecamatan']);
        DB::table('regions')->whereIn('id', [7, 8])->update(['parent_id' => 10]);
        foreach (['barang', 'gas', 'mobils', 'fasilitas_umums', 'lokasi_layanan'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id(); $t->integer('region_id'); $t->string('lokasi')->nullable(); $t->string('nama')->nullable();
                $t->double('latitude')->nullable(); $t->double('longitude')->nullable();
            });
        }
        DB::table('barang')->insert(['region_id' => 7, 'lokasi' => 'Gudang']);
        $this->assertSame(0, Artisan::call('segidaerah:audit'));
        $output = Artisan::output();
        $this->assertStringContainsString('LOKASI BELUM LENGKAP | barang: 1', $output);
        $this->assertStringNotContainsString(config('app.key'), $output);
        $this->assertStringNotContainsString('Pemilik Sintetis', $output);
        $this->assertSame(2, DB::table('users')->count());
        DB::table('users')->where('id', 2)->update(['role' => 'admin_kecamatan']);
        $this->assertSame(1, Artisan::call('segidaerah:audit'));
        $this->assertStringContainsString('1 akun dengan role tidak didukung', Artisan::output());
        $this->assertDatabaseHas('users', ['id' => 2, 'role' => 'admin_kecamatan']);
    }
}
