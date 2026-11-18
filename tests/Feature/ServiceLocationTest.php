<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\LokasiLayanan;
use App\Support\ServiceLocation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ServiceLocationTest extends TestCase
{
    use \Tests\Support\EditorialDatabase;

    private function createLocationTable(): void
    {
        Schema::create('lokasi_layanan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('region_id');
            $table->string('nama');
            $table->text('alamat');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->timestamps();
        });
    }

    public function test_current_service_location_is_used_without_crossing_village_boundaries(): void
    {
        $this->createLocationTable();
        $foreign = LokasiLayanan::create(['region_id'=>5, 'nama'=>'Gudang', 'alamat'=>'Desa lain', 'latitude'=>3, 'longitude'=>104]);
        $own = LokasiLayanan::create(['region_id'=>1, 'nama'=>'Gudang', 'alamat'=>'Alamat baru desa sendiri', 'latitude'=>0, 'longitude'=>102]);
        $item = new Barang(['region_id'=>1, 'lokasi'=>'Gudang', 'latitude'=>1, 'longitude'=>101]);

        $resolved = ServiceLocation::forItem($item);
        $this->assertSame($own->id, $resolved->id);
        $this->assertSame('Alamat baru desa sendiri', $resolved->alamat);
        $this->assertSame(0.0, $resolved->latitude);
        $own->update(['longitude'=>103]);
        $this->assertSame(103.0, ServiceLocation::forItem($item)->longitude);

        $item->region_id = 99;
        $resolved = ServiceLocation::forItem($item);
        $this->assertFalse($resolved->exists);
        $this->assertSame(1.0, $resolved->latitude);
        $this->assertNotSame($foreign->alamat, $resolved->alamat);
    }

    public function test_legacy_product_coordinates_work_without_a_location_record(): void
    {
        $item = new Barang(['region_id'=>1, 'lokasi'=>'Kantor desa', 'latitude'=>0, 'longitude'=>102]);
        $location = ServiceLocation::forItem($item);
        $this->assertSame('Kantor desa', $location->alamat);
        $this->assertSame(0.0, $location->latitude);
        $this->assertSame(102.0, $location->longitude);
    }

    public function test_admin_can_save_a_point_but_another_village_cannot_change_it(): void
    {
        $this->createLocationTable();
        $this->actingAs($this->user(4));
        $controller = app(\App\Http\Controllers\Admin\LokasiLayananController::class);
        $request = \Illuminate\Http\Request::create('/lokasi', 'POST', [
            'nama'=>'Kantor layanan', 'alamat'=>'Alamat sintetis', 'latitude'=>0, 'longitude'=>102,
        ]);
        $response = $controller->simpanCepat($request);
        $this->assertTrue($response->getData(true)['success']);
        $location = LokasiLayanan::firstOrFail();
        $this->assertSame(1, (int) $location->region_id);
        $this->assertSame(0.0, $location->latitude);
        $request->merge(['longitude'=>103]);
        $controller->update($request, $location);
        $this->assertSame(103.0, $location->fresh()->longitude);

        $this->actingAs($this->user(7));
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $controller->update($request, $location);
    }
}
