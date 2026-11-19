<?php

namespace Tests\Feature;

use App\Models\{Barang, Region, FasilitasUmum};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{DB, Schema};
use Tests\TestCase;

class MobilePageRenderTest extends TestCase
{
    use \Tests\Support\EditorialDatabase;

    public function test_catalog_region_and_admin_detail_pages_render_for_mobile_review(): void
    {
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        Schema::table('users', fn (Blueprint $t) => $t->string('status')->default('aktif'));
        Schema::create('admin_notifications', function (Blueprint $t) { $t->id(); $t->integer('region_id'); $t->boolean('is_read'); $t->timestamps(); });
        foreach (['2026_06_15_182007_create_services_table.php','2026_06_15_182008_create_region_services_table.php','2026_06_22_151200_add_is_exclusive_to_region_services_table.php'] as $file) (require database_path('migrations/'.$file))->up();
        $item = new Barang;
        $item->forceFill(['id'=>1,'uuid'=>'sample-item','nama_barang'=>'Peralatan kegiatan warga','nama_mobil'=>'Kendaraan layanan desa',
            'jenis_gas'=>'Gas layanan warga','nama_fasilitas'=>'Gedung kegiatan warga','foto'=>'synthetic-image.webp',
            'harga_sewa'=>700000,'harga_satuan'=>25000,'harga_sewa_per_hari'=>700000,'stok'=>5,'billing_basis'=>'per_activity',
            'kategori'=>'Layanan kegiatan','status'=>'tersedia','deskripsi'=>'Keterangan contoh layanan untuk pengujian tampilan.',
            'latitude'=>1.4854,'longitude'=>102.1512,'lokasi'=>'Alamat layanan uji','region_id'=>1]);
        $item->setRelation('region', Region::find(1));
        $facility = new FasilitasUmum;
        $facility->forceFill($item->getAttributes()+['nama'=>'Gedung kegiatan warga']);
        $facility->setRelation('region', Region::find(1));
        $facility->setRelation('pengurus', collect());
        $this->actingAs($this->user(1));
        foreach (['users.rental-detail','users.gas-detail','users.mobil-rental-detail','users.fasilitas-umum-detail'] as $name) {
            $this->export($name, view($name,['item'=>str_contains($name,'fasilitas') ? $facility : $item])->render());
        }
        foreach ([2,3] as $id) {
            $actor = $this->user($id); $this->actingAs($actor);
            $this->export('warga-'.$actor->role, view('user.wilayah.warga', [
                'wargas'=>new LengthAwarePaginator(collect([$this->user(1)]),1,15),'search'=>'',
            ])->render());
            $this->export('berita-'.$actor->role, view('user.wilayah.berita',['beritas'=>collect()])->render());
            $this->export('pengumuman-'.$actor->role, view('user.wilayah.pengumuman',['pengumumans'=>collect(),'region'=>$actor->region])->render());
        }
        $this->actingAs($this->user(4));
        foreach (['penyewaan'=>['barang'=>$item], 'penjualan_gas'=>['gas'=>$item], 'fasilitas_umum'=>['fasilitas'=>$facility]] as $unit=>$data) {
            $this->export('admin-detail-'.$unit, view('admin.unit.'.$unit.'.show',$data)->render());
        }
    }

    private function export(string $name, string $html): void
    {
        $this->assertStringContainsString('segidaerah-responsive.js', $html, $name);
        if ($directory = getenv('SEGI_MAPS_TEST_EXPORT')) file_put_contents($directory.'/'.$name.'.html', $html);
    }
}
