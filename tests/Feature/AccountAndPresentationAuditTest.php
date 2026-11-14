<?php
namespace Tests\Feature;

use App\Http\Controllers\User\ProfileController;
use App\Models\{Barang, Region, UnitChatSession};
use App\Support\ServiceTerms;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema};
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;

class AccountAndPresentationAuditTest extends TestCase
{
    use \Tests\Support\EditorialDatabase { setUp as editorialSetUp; }
    protected function setUp(): void
    {
        $this->editorialSetUp();
        Schema::table('users', function (Blueprint $t) {
            foreach (['username','address','rt','rw','gender','phone_hash'] as $field) $t->string($field)->nullable();
        });
        $this->actingAs($this->user(8));
    }

    public function test_settings_change_contact_address_but_never_account_authority_or_identity(): void
    {
        $request = Request::create('/', 'PUT', ['address'=>'Jalan Desa 1','rt'=>'01','rw'=>'02','region_id'=>5,'role'=>'super_admin','name'=>'Forged','verification_status'=>'verified']);
        $request->setUserResolver(fn () => $this->user(8));
        app(ProfileController::class)->updateSettings($request);
        $user = $this->user(8);
        $this->assertSame('Jalan Desa 1',$user->address);
        $this->assertSame('01',$user->rt);
        $this->assertEquals(3,$user->region_id);
        $this->assertSame('user',$user->role);
        $this->assertSame('unverified',$user->verification_status);
        $this->assertSame('Akun 8',$user->name);
        app(ProfileController::class)->update(Request::create('/', 'PUT', ['name'=>'Nama Profil','username'=>'warga8','address'=>'Changed','rt'=>'99','region_id'=>5]));
        $this->assertSame('Nama Profil',$user->fresh()->name);
        $this->assertSame('Jalan Desa 1',$user->fresh()->address);
    }

    public function test_rt_rw_update_rejects_foreign_village_with_403_and_preserves_account(): void
    {
        try { app(ProfileController::class)->updateRtRw(Request::create('/', 'POST', ['rw_id'=>6,'region_id'=>7])); $this->fail('Foreign region accepted'); }
        catch (HttpExceptionInterface $e) { $this->assertSame(403,$e->getStatusCode()); }
        $this->assertNull($this->user(8)->rt);
        $response = app(ProfileController::class)->updateRtRw(Request::create('/', 'POST', ['rw_id'=>2,'region_id'=>3]));
        $this->assertSame(200,$response->getStatusCode());
    }

    public function test_profile_and_settings_forms_render_separate_fields_inside_their_forms(): void
    {
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        foreach ([false, true] as $settings) {
            $html = view('users.profile', ['isSettings'=>$settings,'user'=>$this->user(8),'kecamatan_name'=>null,'desa_name'=>'Desa A','rw_name'=>'02','rt_name'=>'01','kecamatans'=>collect(),'currentDesaId'=>1])->render();
            $dom = new \DOMDocument(); @$dom->loadHTML($html); $xpath = new \DOMXPath($dom);
            $route = route($settings ? 'user.settings.update' : 'profile.update');
            $form = '//form[@action="'.$route.'"]';
            $this->assertSame(1,$xpath->query($form)->length);
            $this->assertSame($settings ? 0 : 1,$xpath->query($form.'//input[@name="name"]')->length);
            $this->assertSame($settings ? 1 : 0,$xpath->query($form.'//textarea[@name="address"]')->length);
            $this->assertSame(1,$xpath->query($form.'//button[@type="submit"]')->length);
        }
    }

    public function test_service_location_distinguishes_exact_point_from_search_and_missing_location(): void
    {
        $item = new Barang(['latitude'=>0,'longitude'=>0,'lokasi'=>'Balai Desa']); $item->setRelation('region',new Region(['name'=>'Desa A']));
        $html = \Illuminate\Support\Facades\Blade::render('<x-service-location :item="$item" />',['item'=>$item]);
        $this->assertStringContainsString('Buka Titik Lokasi di Peta',$html);
        $this->assertStringContainsString('q=0%2C0',$html);
        $item->latitude = 999;
        $html = \Illuminate\Support\Facades\Blade::render('<x-service-location :item="$item" />',['item'=>$item]);
        $this->assertStringContainsString('Cari Alamat di Peta',$html);
        $this->assertStringNotContainsString('q=999',$html);
        $item->lokasi = null;
        $html = \Illuminate\Support\Facades\Blade::render('<x-service-location :item="$item" />',['item'=>$item]);
        $this->assertStringNotContainsString('google.com/maps',$html);
    }

    public function test_chat_metadata_and_provider_terms_use_readable_values(): void
    {
        foreach (['{"name":"Sound System","id":12}'=>'Sound System','{bad json'=>'Produk layanan','Sound System'=>'Sound System'] as $raw=>$expected) {
            $chat = new UnitChatSession(['item_reference'=>$raw]); $this->assertSame($expected,$chat->item_label);
        }
        $region = new Region(['payment_info'=>['sop_penyewaan_active'=>'ditanggung','sop_penyewaan_ditanggung'=>'Ketentuan khusus desa']]);
        $this->assertSame('Ketentuan khusus desa',ServiceTerms::forRegion($region,'rental'));
        $this->assertSame(ServiceTerms::FACILITY_LIABILITY,ServiceTerms::forRegion(null,'facility'));
    }
    public function test_all_blade_templates_compile_without_php_syntax_errors(): void
    {
        foreach (\Illuminate\Support\Facades\File::allFiles(resource_path('views')) as $file) {
            if (!str_ends_with($file->getFilename(), '.blade.php')) continue;
            $compiled = app('blade.compiler')->compileString(file_get_contents($file->getPathname()));
            try { $this->assertIsArray(token_get_all($compiled, TOKEN_PARSE), $file->getRelativePathname()); }
            catch (\ParseError $e) { $this->fail($file->getRelativePathname().': '.$e->getMessage()); }
        }
    }

    public function test_public_directory_includes_joined_and_unjoined_villages_in_bengkalis_only(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->string('status')->default('aktif'));
        foreach (['2026_06_15_182007_create_services_table.php','2026_06_15_182008_create_region_services_table.php','2026_06_22_151200_add_is_exclusive_to_region_services_table.php'] as $file) (require database_path('migrations/'.$file))->up();
        DB::table('regions')->insert(['id'=>10,'type'=>'kecamatan','name'=>'Kecamatan Bengkalis','parent_id'=>null]);
        DB::table('regions')->whereIn('id',[1,5])->update(['parent_id'=>10]);
        DB::table('regions')->insert(['id'=>11,'type'=>'desa','name'=>'Desa di luar Bengkalis','parent_id'=>null]);
        DB::table('users')->where('id',7)->update(['status'=>'non_aktif']);
        auth()->logout();
        $data = app(\App\Http\Controllers\PartnerApplicationController::class)->create()->getData();
        $villages = $data['kecamatans']->first()->children;
        $this->assertSame([1,5],$villages->pluck('id')->all());
        $this->assertCount(1,$villages->firstWhere('id',1)->users);
        $this->assertCount(0,$villages->firstWhere('id',5)->users);
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        $html = view('pages.kemitraan.create',$data)->render();
        $this->assertStringContainsString('Sudah Bergabung',$html);
        $this->assertStringContainsString('Belum Bergabung',$html);
    }

}
