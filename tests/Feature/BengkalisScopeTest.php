<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\{RegionManagementController, WilayahPlatformController};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class BengkalisScopeTest extends TestCase
{
    use \Tests\Support\EditorialDatabase { setUp as setupEditorial; }

    protected function setUp(): void
    {
        $this->setupEditorial();
        Schema::table('regions', function (Blueprint $t) { $t->text('profile_text')->nullable(); $t->timestamps(); });
        foreach (['2026_06_15_182007_create_services_table.php', '2026_06_15_182008_create_region_services_table.php', '2026_06_22_151200_add_is_exclusive_to_region_services_table.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        DB::table('regions')->insert([
            ['id' => 10, 'name' => 'Kabupaten Bengkalis', 'type' => 'kabupaten', 'parent_id' => null],
            ['id' => 11, 'name' => 'Kecamatan Bengkalis', 'type' => 'kecamatan', 'parent_id' => 10],
            ['id' => 12, 'name' => 'Kecamatan Lain', 'type' => 'kecamatan', 'parent_id' => 10],
        ]);
        DB::table('regions')->where('id', 1)->update(['parent_id' => 11]);
        DB::table('regions')->where('id', 5)->update(['parent_id' => 12]);
        $super = $this->user(4); $super->role = 'super_admin'; $this->actingAs($super);
    }

    private function denied(callable $action, int $status = 404): void
    {
        try { $action(); $this->fail('Outside operational boundary was accepted'); }
        catch (HttpException $e) { $this->assertSame($status, $e->getStatusCode()); }
    }

    public function test_super_admin_tree_and_parent_options_only_contain_bengkalis(): void
    {
        $data = app(WilayahPlatformController::class)->index()->getData();
        $this->assertSame([11], $data['pohon']->pluck('id')->all());
        $this->assertSame(5, $data['total']);
        $this->assertEmpty($data['yatim']);
        $this->assertEqualsCanonicalizing([11, 1, 2], $data['pilihanInduk']->pluck('id')->all());
        $page = app(RegionManagementController::class)->index()->getData();
        $this->assertSame(11, $page['parentRegion']->id);
        $this->assertSame([1], $page['childrenRegions']->pluck('id')->all());
        $this->assertSame('desa', $page['targetType']);
        Schema::create('admin_notifications', function (Blueprint $t) {
            $t->id(); $t->integer('region_id')->nullable(); $t->string('type'); $t->boolean('is_read')->default(false); $t->timestamps();
        });
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        $html = view('admin.super_sistem.wilayah', $data)->render();
        $this->assertStringNotContainsString('Kabupaten baru', $html);
        $this->assertStringNotContainsString('Kecamatan Lain', $html);
        if ($directory = getenv('SEGI_MAPS_TEST_EXPORT')) file_put_contents($directory.'/admin-bengkalis-scope.html', $html);
    }

    public function test_super_admin_cannot_bypass_district_boundary_or_change_fixed_root(): void
    {
        $controller = app(RegionManagementController::class);
        $this->denied(fn () => $controller->index(12));
        $this->denied(fn () => $controller->update(Request::create('/', 'PUT', ['name' => 'Changed']), 5));
        $this->denied(fn () => $controller->update(Request::create('/', 'PUT', ['name' => 'Changed']), 11));
        $this->denied(fn () => $controller->destroy(11));
        $this->denied(fn () => $controller->store(Request::create('/', 'POST', ['name' => 'New', 'type' => 'desa', 'parent_id' => 12])));
        $this->denied(fn () => app(WilayahPlatformController::class)->store(Request::create('/', 'POST', ['name' => 'New', 'type' => 'desa', 'parent_id' => 12])));
        $this->assertDatabaseHas('regions', ['id' => 5, 'name' => 'Desa B']);
    }

    public function test_local_kelurahan_can_be_added_without_creating_other_districts(): void
    {
        app(WilayahPlatformController::class)->store(Request::create('/', 'POST', ['name' => 'Uji', 'type' => 'kelurahan', 'parent_id' => 11]));
        $this->assertDatabaseHas('regions', ['name' => 'Kelurahan Uji', 'type' => 'kelurahan', 'parent_id' => 11]);
        $this->denied(fn () => app(RegionManagementController::class)->store(Request::create('/', 'POST', ['name' => 'New', 'type' => 'kabupaten'])), 422);
    }

    public function test_public_profiles_reject_other_districts_and_api_does_not_invent_officials(): void
    {
        Schema::create('bumdes_members', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('region_id')->nullable(); $t->string('name');
            $t->string('position'); $t->integer('level'); $t->integer('order'); $t->string('photo')->nullable(); $t->timestamps();
        });
        $this->denied(fn () => app(\App\Http\Controllers\User\BumdesUserController::class)->show(Request::create('/', 'GET', ['id' => 5])));
        $api = app(\App\Http\Controllers\Api\RegionApiController::class);
        $this->assertSame([], $api->getProfile(1)->getData(true)['data']['structure']);
        DB::table('bumdes_members')->insert(['region_id' => 11, 'name' => 'Aparatur lama', 'position' => 'Camat', 'level' => 1, 'order' => 1]);
        $this->assertSame([], $api->getProfile(11)->getData(true)['data']['structure']);
        $this->denied(fn () => $api->getProfile(5));
        foreach (['index', 'create'] as $method) {
            $this->denied(fn () => app(\App\Http\Controllers\Admin\BumdesController::class)->$method(Request::create('/')), 403);
        }
        $this->actingAs($this->user(4));
        DB::table('bumdes_members')->insert(['id' => 2, 'region_id' => 5, 'name' => 'Aparatur desa lain', 'position' => 'Kades', 'level' => 1, 'order' => 1]);
        try {
            app(\App\Http\Controllers\Admin\BumdesController::class)->destroy(2);
            $this->fail('Other village structure deleted');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertDatabaseHas('bumdes_members', ['id' => 2]);
        }
    }

    public function test_service_location_changes_cannot_affect_another_village(): void
    {
        Schema::create('lokasi_layanan', function (Blueprint $t) {
            $t->id(); $t->integer('region_id'); $t->string('nama'); $t->text('alamat')->nullable();
            $t->double('latitude')->nullable(); $t->double('longitude')->nullable(); $t->text('catatan')->nullable();
            $t->boolean('is_aktif')->default(true); $t->timestamps();
        });
        Schema::create('barang', function (Blueprint $t) { $t->id(); $t->integer('region_id'); $t->string('lokasi'); });
        foreach ([1, 5] as $id) {
            DB::table('lokasi_layanan')->insert(['id' => $id, 'region_id' => $id, 'nama' => 'Kantor Desa', 'latitude' => 1, 'longitude' => 102]);
            DB::table('barang')->insert(['region_id' => $id, 'lokasi' => 'Kantor Desa']);
        }
        $this->actingAs($this->user(4));
        $controller = app(\App\Http\Controllers\Admin\LokasiLayananController::class);
        $request = Request::create('/', 'PUT', ['nama' => 'Gudang', 'latitude' => 0, 'longitude' => 102.15]);
        $foreign = \App\Models\LokasiLayanan::findOrFail(5);
        $this->denied(fn () => $controller->update($request, $foreign), 403);
        $this->denied(fn () => $controller->destroy($foreign), 403);
        $controller->update($request, \App\Models\LokasiLayanan::findOrFail(1));
        $this->assertDatabaseHas('lokasi_layanan', ['id' => 1, 'latitude' => 0, 'longitude' => 102.15]);
        $this->assertDatabaseHas('lokasi_layanan', ['id' => 5, 'nama' => 'Kantor Desa', 'latitude' => 1]);
        $this->assertDatabaseHas('barang', ['region_id' => 1, 'lokasi' => 'Gudang']);
        $this->assertDatabaseHas('barang', ['region_id' => 5, 'lokasi' => 'Kantor Desa']);
    }
}
