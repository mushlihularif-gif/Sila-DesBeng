<?php

namespace Tests\Feature;

use App\Http\Controllers\User\AlamatWargaController;
use App\Models\AlamatWarga;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GoogleMapsAddressTest extends TestCase
{
    use \Tests\Support\EditorialDatabase;

    public function test_address_coordinates_persist_and_foreign_user_cannot_edit_them(): void
    {
        (require database_path('migrations/2026_09_05_170000_create_alamat_warga_table.php'))->up();
        $this->actingAs($this->user(1));
        $request = Request::create('/alamat', 'POST', ['nama_penerima'=>'Warga Uji', 'no_telepon'=>'0800000000', 'detail_alamat'=>'Alamat sintetis', 'latitude'=>0, 'longitude'=>102.15]);
        $request->headers->set('Accept', 'application/json');
        $response = app(AlamatWargaController::class)->store($request);
        $this->assertSame(201, $response->getStatusCode());
        $address = AlamatWarga::firstOrFail();
        $this->assertEquals(0, $address->latitude);
        $this->assertEquals(102.15, $address->longitude);
        $this->actingAs($this->user(6));
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        app(AlamatWargaController::class)->update($request, $address);
    }

    public function test_incomplete_or_out_of_range_coordinates_are_rejected(): void
    {
        (require database_path('migrations/2026_09_05_170000_create_alamat_warga_table.php'))->up();
        $this->actingAs($this->user(1));
        foreach ([['latitude'=>1], ['longitude'=>102], ['latitude'=>91,'longitude'=>102], ['latitude'=>1,'longitude'=>181]] as $point) {
            try {
                app(AlamatWargaController::class)->store(Request::create('/alamat','POST', $point + ['nama_penerima'=>'Uji','no_telepon'=>'0800000000','detail_alamat'=>'Alamat sintetis']));
                $this->fail('Invalid coordinates accepted');
            } catch (ValidationException $e) {
                $this->assertNotEmpty(array_intersect(['latitude','longitude'], array_keys($e->errors())));
            }
        }
        $this->assertSame(0, AlamatWarga::count());
    }
}
