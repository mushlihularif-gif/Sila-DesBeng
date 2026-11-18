<?php

namespace Tests\Feature;

use App\Models\AlamatWarga;
use App\Models\Barang;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class GoogleMapsPagesTest extends TestCase
{
    use \Tests\Support\EditorialDatabase;

    public function test_map_pages_render_with_synthetic_locations(): void
    {
        $this->actingAs($this->user(1));
        config(['services.google_maps.api_key'=>'test-key']);
        view()->share('errors', new ViewErrorBag());
        $item = new Barang;
        $item->forceFill(['id'=>1,'nama_barang'=>'Alat uji','nama_mobil'=>'Mobil uji','jenis_gas'=>'Gas uji','foto'=>'', 'kategori'=>'Layanan uji',
            'harga_sewa'=>10000,'harga_satuan'=>10000,'harga_sewa_per_hari'=>10000,'stok'=>5,'billing_basis'=>'per_activity',
            'latitude'=>1.4854,'longitude'=>102.1512,'lokasi'=>'Alamat uji','region_id'=>1]);
        $item->setRelation('region', \App\Models\Region::find(1));
        $address = new AlamatWarga(['nama_penerima'=>'Penerima uji','no_telepon'=>'0800000000','detail_alamat'=>'Alamat sintetis','latitude'=>1.4854,'longitude'=>102.1512,'is_utama'=>true]);
        $address->id = 1;
        $address->setRelation('region', null);
        $location = (object)['nama'=>'Lokasi uji','alamat'=>'Alamat layanan sintetis','latitude'=>1.4854,'longitude'=>102.1512];
        $data = ['item'=>$item,'quantity'=>1,'setting'=>new \App\Models\SystemSetting,'alamatTersimpan'=>collect([$address]),'lokasiLayanan'=>$location,
            'sop_penyewaan_alat'=>'Ketentuan uji','sop_mobil'=>'Ketentuan uji','tarifWilayah'=>collect(),'isWilayah'=>false,
            'alamat'=>collect([$address]),'desa'=>collect()];
        foreach (['users.saldo.index','users.gas-booking','users.rental-booking','users.mobil-rental-booking'] as $name) {
            $html = view($name, $data)->render();
            $this->assertSame(1, substr_count($html,'data-google-maps-key='), $name);
            $this->assertStringNotContainsString('leaflet@', $html);
            $this->export($name, $html);
        }
        $html = \Illuminate\Support\Facades\Blade::render("@include('admin.lokasi_layanan._modal') @stack('modals') @stack('scripts')");
        $this->assertStringContainsString('petaLokasi', $html);
        $this->assertSame(1, substr_count($html,'data-google-maps-key='));
        $this->export('admin.lokasi-layanan', $html);

        // Exercise the actual delivery-map fragment with the valid latitude 0.
        $source = file_get_contents(resource_path('views/admin/aktivitas/request-detail.blade.php'));
        $start = strrpos(substr($source, 0, strpos($source, '$tLat =')), '@php');
        $end = strpos($source, '@if($request->notes)', $start);
        $fragment = substr($source, $start, $end - $start);
        $fragment .= substr($source, strrpos($source, "@push('scripts')"));
        $order = new \App\Models\RentalBooking;
        $order->forceFill(['latitude'=>0,'longitude'=>102.15,'delivery_method'=>'antar']);
        $html = \Illuminate\Support\Facades\Blade::render($fragment." @stack('scripts')", ['request'=>$order]);
        $this->assertStringContainsString('id="peta-titik-pesanan"', $html);
        $this->export('admin.titik-antar', $html);
        $html = \Illuminate\Support\Facades\Blade::render('<x-service-location :item="$item" />', ['item'=>$item]);
        $this->assertStringContainsString('https://www.google.com/maps?q=', $html);
    }

    private function export(string $name, string $html): void
    {
        if ($directory = getenv('SEGI_MAPS_TEST_EXPORT')) {
            file_put_contents($directory.'/'.$name.'.html', $html);
        }
    }
}


