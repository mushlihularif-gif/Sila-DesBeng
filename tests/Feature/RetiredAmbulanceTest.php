<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\UnitKendaraanOperasionalController;
use App\Http\Controllers\Api\{FasilitasBookingApiController, MobilBookingApiController};
use App\Http\Controllers\User\{FasilitasUmumBookingController, FasilitasUmumUserController, MobilBookingController, MobilRentalUserController};
use App\Models\{FasilitasUmum, Mobil, Service, User};
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Route, Schema};
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RetiredAmbulanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('mobils', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('region_id'); $t->string('nama_mobil'); $t->string('kategori')->nullable();
            $t->string('status')->default('tersedia'); $t->integer('stok')->default(1);
            foreach (['plat_nomor', 'deskripsi', 'foto', 'foto_2', 'foto_3'] as $field) $t->string($field)->nullable();
            foreach (['harga_sewa', 'harga_dalam_desa', 'harga_luar_desa', 'harga_luar_kota'] as $field) $t->decimal($field)->default(0);
            $t->boolean('is_harian_active')->default(false); $t->boolean('is_borongan_active')->default(false); $t->timestamps();
        });
        Schema::create('fasilitas_umums', function (Blueprint $t) {
            $t->id(); $t->string('nama_fasilitas'); $t->string('status')->default('tersedia');
        });
        Schema::create('supirs', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('region_id'); $t->string('nama'); $t->string('tipe');
        });
        Schema::create('mobil_supir', function (Blueprint $t) { $t->unsignedBigInteger('mobil_id'); $t->unsignedBigInteger('supir_id'); });
        Schema::create('services', function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('slug'); });
        Schema::create('region_services', function (Blueprint $t) { $t->unsignedBigInteger('region_id'); $t->unsignedBigInteger('service_id'); $t->boolean('is_active'); $t->timestamps(); });
        Schema::create('regions', function (Blueprint $t) { $t->id(); $t->string('name'); });
        DB::table('regions')->insert(['id' => 12, 'name' => 'Desa sendiri']);
        DB::table('mobils')->insert([
            ['id' => 1, 'region_id' => 12, 'nama_mobil' => 'Ambulans lama', 'kategori' => 'ambulans'],
            ['id' => 2, 'region_id' => 12, 'nama_mobil' => 'Operasional desa', 'kategori' => 'kendaraan_operasional'],
            ['id' => 3, 'region_id' => 13, 'nama_mobil' => 'Operasional desa lain', 'kategori' => 'kendaraan_operasional'],
            ['id' => 4, 'region_id' => 12, 'nama_mobil' => 'Mobil sewa', 'kategori' => null],
        ]);
        DB::table('fasilitas_umums')->insert([['id' => 1, 'nama_fasilitas' => 'Ambulans desa'], ['id' => 2, 'nama_fasilitas' => 'Balai desa']]);
        DB::table('supirs')->insert([['id' => 1, 'region_id' => 12, 'nama' => 'Supir lokal', 'tipe' => 'supir'], ['id' => 2, 'region_id' => 13, 'nama' => 'Supir desa lain', 'tipe' => 'supir']]);
        DB::table('services')->insert([['name' => 'Layanan Ambulans', 'slug' => 'layanan-ambulans'], ['name' => 'Ambulans', 'slug' => 'ambulans'], ['name' => 'Fasilitas Umum', 'slug' => 'fasilitas-umum']]);
        $user = new User(); $user->id = 10; $user->region_id = 12; $user->role = 'admin_desa'; $user->verification_status = 'verified';
        $this->actingAs($user);
    }

    private function assertMissing(callable $action): void
    {
        try {
            $action();
            $this->fail('Retired or unrelated vehicle must not be accessible.');
        } catch (ModelNotFoundException $e) {
            $this->assertNotEmpty($e->getModel());
        }
    }

    public function test_old_routes_are_removed_and_operational_route_requires_unit_permission(): void
    {
        $this->assertFalse(Route::has('user.ambulans.index'));
        $this->assertFalse(Route::has('user.ambulans.show'));
        $this->assertFalse(Route::has('admin.unit.ambulans.store'));
        $legacyRoutes = array_filter(Route::getRoutes()->getRoutes(), fn ($route) => str_contains($route->uri(), 'ambulans'));
        $this->assertEmpty($legacyRoutes);
        $route = Route::getRoutes()->getByName('admin.unit.kendaraan_operasional.store');
        $this->assertContains('staff.permission:fasilitas_umum', $route->gatherMiddleware());
    }

    public function test_catalogs_exclude_ambulances_without_deleting_history(): void
    {
        $this->assertEqualsCanonicalizing([2, 3, 4], Mobil::inCatalog()->pluck('id')->all());
        $this->assertSame([2], FasilitasUmum::inCatalog()->pluck('id')->all());
        $this->assertSame(['fasilitas-umum'], Service::available()->pluck('slug')->all());
        $this->assertNotNull(Mobil::find(1));
        $this->assertNotNull(FasilitasUmum::find(1));
        $request = Request::create('/api/mobil');
        $cars = app(MobilBookingApiController::class)->index($request)->getData(true)['data'];
        $this->assertEqualsCanonicalizing([2, 3, 4], array_column($cars, 'id'));
        $facilities = app(FasilitasBookingApiController::class)->index($request)->getData(true)['data'];
        $this->assertSame([2], array_column($facilities, 'id'));
    }

    public function test_old_ids_cannot_open_detail_or_booking_forms(): void
    {
        foreach ([MobilRentalUserController::class, FasilitasUmumUserController::class] as $controller) {
            $this->assertMissing(fn () => app($controller)->show(1));
        }
        foreach ([MobilBookingController::class, FasilitasUmumBookingController::class] as $controller) {
            $this->assertMissing(fn () => app($controller)->create(1));
        }
        foreach ([MobilBookingApiController::class, FasilitasBookingApiController::class] as $controller) {
            $this->assertSame(404, app($controller)->show(1)->getStatusCode());
        }
    }

    public function test_direct_web_and_api_booking_requests_reject_ambulance_ids(): void
    {
        $input = ['mobil_id' => 1, 'fasilitas_id' => 1, 'jenis_sewa' => 'harian', 'quantity' => 1, 'start_date' => now()->addDay()->toDateString(), 'end_date' => now()->addDay()->toDateString(), 'payment_method' => 'tunai', 'recipient_name' => 'Warga', 'delivery_address' => 'Desa', 'rental_purpose' => 'Kegiatan warga', 'jenis_acara' => 'sosial'];
        foreach ([MobilBookingController::class, FasilitasUmumBookingController::class, MobilBookingApiController::class, FasilitasBookingApiController::class] as $controller) {
            $this->assertMissing(fn () => app($controller)->store(Request::create('/booking', 'POST', $input)));
        }
        $this->assertSame(4, Mobil::count());
    }

    public function test_operational_admin_cannot_access_ambulances_or_another_villages_vehicle(): void
    {
        $controller = app(UnitKendaraanOperasionalController::class);
        foreach ([1, 3] as $id) {
            $this->assertMissing(fn () => $controller->show($id));
            $this->assertMissing(fn () => $controller->edit($id));
            $this->assertMissing(fn () => $controller->update(Request::create('/', 'PUT'), $id));
            $this->assertMissing(fn () => $controller->destroy($id));
        }
        $this->assertSame(4, Mobil::count());
    }

    public function test_operational_vehicle_can_be_updated_and_assigned_a_local_driver(): void
    {
        $controller = app(UnitKendaraanOperasionalController::class);
        $response = $controller->update(Request::create('/', 'PUT', ['nama_mobil' => 'Truk desa', 'supir_ids' => [1]]), 2);
        $this->assertSame(route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan']), $response->getTargetUrl());
        $this->assertSame('Truk desa', Mobil::find(2)->nama_mobil);
        $this->assertSame('kendaraan_operasional', Mobil::find(2)->kategori);
        $this->assertSame([1], Mobil::find(2)->supirs->modelKeys());
        $controller->update(Request::create('/', 'PUT', ['nama_mobil' => 'Truk desa']), 2);
        $this->assertCount(0, Mobil::find(2)->supirs);
    }

    public function test_operational_form_rejects_retired_category_and_foreign_driver(): void
    {
        foreach ([['kategori' => 'ambulans'], ['supir_ids' => [2]]] as $input) {
            try {
                app(UnitKendaraanOperasionalController::class)->update(Request::create('/', 'PUT', ['nama_mobil' => 'Diubah'] + $input), 2);
                $this->fail('Expected validation failure.');
            } catch (ValidationException $e) {
                $this->assertNotEmpty($e->errors());
            }
        }
        $this->assertSame('Operasional desa', Mobil::find(2)->nama_mobil);
        $this->assertSame(0, DB::table('mobil_supir')->count());
    }

    public function test_new_operational_vehicle_activates_only_facilities_and_ignores_submitted_owner(): void
    {
        app(UnitKendaraanOperasionalController::class)->store(Request::create('/', 'POST', ['nama_mobil' => 'Operasional baru', 'region_id' => 13, 'supir_ids' => [1]]));
        $vehicle = Mobil::latest('id')->first();
        $this->assertSame('kendaraan_operasional', $vehicle->kategori);
        $this->assertSame(12, $vehicle->region_id);
        $this->assertSame([1], $vehicle->supirs->modelKeys());
        $this->assertSame([3], DB::table('region_services')->where('is_active', true)->pluck('service_id')->all());
    }

    public function test_legacy_service_cannot_be_reenabled_in_region_settings(): void
    {
        foreach ([\App\Http\Controllers\Admin\RegionSettingController::class, \App\Http\Controllers\Admin\SystemSettingController::class] as $controller) {
            try {
                app($controller)->update(Request::create('/', 'POST', ['services' => [1]]));
                $this->fail('Expected retired service validation failure.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('services.0', $e->errors());
            }
        }
    }
}
