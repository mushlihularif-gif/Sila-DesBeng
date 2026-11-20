<?php

namespace Tests\Feature;

use App\Http\Controllers\PartnerApplicationController;
use App\Http\Controllers\Api\PartnerApplicationApiController;
use App\Models\{PartnerApplication, Region};
use App\Services\PartnerDocumentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{DB, Schema, Storage};
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class PartnershipAuditTest extends TestCase
{
    use \Tests\Support\EditorialDatabase { setUp as setupEditorial; }

    protected function setUp(): void
    {
        $this->setupEditorial();
        Schema::table('users', fn (Blueprint $t) => $t->string('status')->default('aktif'));
        Schema::table('regions', function (Blueprint $t) { $t->text('profile_text')->nullable(); $t->timestamps(); });
        foreach (['2026_06_15_182007_create_services_table.php', '2026_06_15_182008_create_region_services_table.php',
            '2026_06_22_151200_add_is_exclusive_to_region_services_table.php', '2026_06_15_182008_create_partner_applications_table.php',
            '2024_06_19_000000_add_document_path_to_partner_applications_table.php', '2026_06_15_183626_add_user_id_to_partner_applications_table.php',
            '2026_06_29_200048_add_position_to_partner_applications_table.php'] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        DB::table('regions')->insert([
            ['id'=>10, 'name'=>'Kecamatan Bengkalis','type'=>'kecamatan','parent_id'=>null],
            ['id'=>11, 'name'=>'Abadi','type'=>'desa','parent_id'=>10],
        ]);
        DB::table('regions')->whereIn('id',[1,5])->update(['parent_id'=>10]);
        DB::table('regions')->where('id',1)->update(['name'=>'Zamrud']);
        DB::table('users')->where('id',7)->update(['status'=>'non_aktif']);
        DB::table('users')->where('id',1)->update(['email'=>'warga@example.test']);
        DB::table('services')->insert(['id'=>1,'name'=>'Penjualan Gas','slug'=>'penjualan-gas']);
        DB::table('region_services')->insert(['region_id'=>5,'service_id'=>1,'is_active'=>true]);
        Storage::fake('local'); Storage::fake('public');
    }

    public function test_web_and_api_put_joined_villages_first_without_enabling_inactive_admins(): void
    {
        $this->actingAs($this->user(1));
        $page = app(PartnerApplicationController::class)->create();
        $this->assertSame([5,1,11], $page->getData()['kecamatans']->first()->children->pluck('id')->all());
        $response = app(PartnerApplicationApiController::class)->getRegions()->getData(true);
        $villages = $response['data'][0]['children'];
        $this->assertSame([5,1,11], array_column($villages, 'id'));
        $this->assertSame([true,true,false], array_column($villages, 'is_joined'));
        $this->assertFalse($villages[0]['has_admin']);
        $this->assertSame('non_aktif', $this->user(7)->status);
        view()->share('errors', new ViewErrorBag());
        $html = $page->render();
        $this->assertStringContainsString('Daftar sebagai apa?', $html);
        $this->assertStringContainsString('data-partner-role="rw"', $html);
        $this->assertStringContainsString('data-partner-role="rt"', $html);
        if ($directory = getenv('SEGI_MAPS_TEST_EXPORT')) file_put_contents($directory.'/partnership.html', $html);
    }

    public function test_retired_or_disabled_services_do_not_mark_a_village_joined(): void
    {
        DB::table('region_services')->where('region_id',5)->update(['is_active'=>false]);
        DB::table('services')->insert(['id'=>2,'name'=>'Pasar Daerah','slug'=>'pasar-daerah']);
        DB::table('region_services')->insert(['region_id'=>11,'service_id'=>2,'is_active'=>true]);
        $data = app(PartnerApplicationApiController::class)->getRegions()->getData(true)['data'][0]['children'];
        $this->assertSame([1,11,5], array_column($data,'id'));
        $this->assertSame([true,false,false], array_column($data,'is_joined'));
    }

    public function test_multiple_admins_and_services_count_the_village_once_and_directory_lists_providers(): void
    {
        DB::table('users')->where('id', 8)->update(['role' => 'admin_desa', 'region_id' => 1]);
        DB::table('region_services')->insert(['region_id' => 1, 'service_id' => 1, 'is_active' => true]);
        $data = app(PartnerApplicationApiController::class)->getRegions()->getData(true)['data'][0]['children'];
        $this->assertCount(2, array_filter($data, fn ($village) => $village['is_joined']));
        $this->assertCount(1, array_filter($data, fn ($village) => $village['id'] === 1));
        $this->actingAs($this->user(1));
        view()->share('errors', new ViewErrorBag());
        $page = app(\App\Http\Controllers\User\RegionDirectoryController::class)->index(Request::create('/'));
        $this->assertEqualsCanonicalizing([1, 5], $page->getData()['joinedVillages']->pluck('id')->all());
        $this->assertStringContainsString(route('bumdes.detail', ['slug' => 'zamrud', 'id' => 1]), $page->render());
        DB::table('users')->where('region_id', 1)->where('role', 'admin_desa')->update(['role' => 'super_admin']);
        $data = app(PartnerApplicationApiController::class)->getRegions()->getData(true)['data'][0]['children'];
        $this->assertCount(2, array_filter($data, fn ($village) => $village['is_joined']));
    }

    public function test_service_directory_renders_without_district_officials_or_contact_data(): void
    {
        // These fixtures deliberately contain no organization or system-settings tables.
        $this->actingAs($this->user(1));
        view()->share('errors', new ViewErrorBag());
        $page = app(\App\Http\Controllers\User\RegionDirectoryController::class)->index(Request::create('/'));
        $html = $page->render();
        $this->assertStringContainsString('Layanan di Kecamatan Bengkalis', $html);
        $this->assertStringNotContainsString('unit-carousel-container', $html);
        $this->assertStringNotContainsString('Lihat desa yang sudah dan belum bergabung', $html);
        $this->assertStringContainsString('data-village-search', $html);
        $this->assertStringContainsString('aria-controls="village-list"  hidden', $html);
        $this->assertStringNotContainsString('Struktur Organisasi dan Tata Kerja', $html);
        $this->assertStringNotContainsString('Pemerintah Kecamatan Bengkalis', $html);
        if ($directory = getenv('SEGI_MAPS_TEST_EXPORT')) file_put_contents($directory.'/service-directory.html', $html);
    }

    public function test_directory_limits_initial_rows_and_includes_only_joined_villages_and_kelurahan(): void
    {
        foreach (range(20, 25) as $id) {
            DB::table('regions')->insert(['id' => $id, 'name' => ($id === 25 ? 'Kelurahan' : 'Desa').' Uji '.$id,
                'type' => $id === 25 ? 'kelurahan' : 'desa', 'parent_id' => 10]);
            DB::table('region_services')->insert(['region_id' => $id, 'service_id' => 1, 'is_active' => true]);
        }
        view()->share('errors', new ViewErrorBag());
        $page = app(\App\Http\Controllers\User\RegionDirectoryController::class)->index(Request::create('/'));
        $this->assertCount(8, $page->getData()['joinedVillages']);
        $html = $page->render();
        preg_match_all('/<article[^>]*data-village-row[^>]*>/', $html, $rows);
        $this->assertCount(8, $rows[0]);
        $this->assertCount(3, array_filter($rows[0], fn ($row) => str_contains($row, ' hidden')));
        $this->assertStringContainsString(route('bumdes.detail', ['slug' => 'kelurahan-uji-25', 'id' => 25]), $html);
        $this->assertStringNotContainsString('data-village-name="Abadi"', $html);
        $this->assertStringNotContainsString('unit-carousel-container', $html);
        if ($directory = getenv('SEGI_MAPS_TEST_EXPORT')) file_put_contents($directory.'/service-directory-many.html', $html);
    }

    public function test_rt_rw_form_does_not_depend_on_an_active_village_admin(): void
    {
        // Desa B has an active service, but its village administrator is inactive.
        $this->actingAs($this->user(6));
        $page = app(PartnerApplicationController::class)->create();
        $this->assertFalse($page->getData()['isJoined']);
        $this->assertTrue($page->getData()['canApplyRtRw']);
        view()->share('errors', new ViewErrorBag());
        $html = $page->render();
        foreach (['desa', 'rw', 'rt'] as $role) {
            preg_match('/<button[^>]*data-partner-role="'.$role.'"[^>]*>/', $html, $button);
            $this->assertNotEmpty($button);
            $this->assertStringNotContainsString('disabled', $button[0]);
        }
        $this->assertStringContainsString('id="domicile_fields"', $html);
        $this->assertStringContainsString('id="region_selectors"', $html);
        if ($directory = getenv('SEGI_MAPS_TEST_EXPORT')) file_put_contents($directory.'/partnership-no-admin.html', $html);
    }

    public function test_missing_or_outside_domicile_explains_unavailable_rt_rw_roles(): void
    {
        foreach ([null, 10] as $regionId) {
            DB::table('users')->where('id',1)->update(['region_id'=>$regionId]);
            $this->actingAs($this->user(1));
            $page = app(PartnerApplicationController::class)->create();
            $this->assertFalse($page->getData()['canApplyRtRw']);
            view()->share('errors', new ViewErrorBag());
            $html = $page->render();
            foreach (['rw', 'rt'] as $role) {
                preg_match('/<button[^>]*data-partner-role="'.$role.'"[^>]*>/', $html, $button);
                $this->assertNotEmpty($button);
                $this->assertStringContainsString('disabled', $button[0]);
            }
            $this->assertStringContainsString('Lengkapi domisili', $html);
        }
    }

    public function test_web_rejects_rt_request_without_a_domicile(): void
    {
        DB::table('users')->where('id',1)->update(['region_id'=>null]);
        $this->actingAs($this->user(1));
        $response = app(PartnerApplicationController::class)->store(Request::create('/', 'POST', ['region_type'=>'rt','parent_region_id'=>6]));
        $this->assertTrue($response->getSession()->get('errors')->has('parent_region_id'));
        $this->assertSame(0, PartnerApplication::count());
    }

    private function payload(): array
    {
        return ['applicant_name'=>'Warga Uji','position'=>'Ketua RT','region_type'=>'rt','region_name'=>'RT 08',
            'parent_region_id'=>6,'contact_email'=>'warga@example.test','contact_phone'=>'0800000000','reason'=>'Pengajuan uji'];
    }

    public function test_api_cannot_apply_as_rt_in_another_village(): void
    {
        $actor = $this->user(1);
        $request = Request::create('/', 'POST', $this->payload(), [], ['document'=>UploadedFile::fake()->create('sk.pdf',1,'application/pdf')]);
        $request->setUserResolver(fn () => $actor);
        try { app(PartnerApplicationApiController::class)->store($request); $this->fail('Foreign village accepted'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(403,$e->getStatusCode()); }
        $this->assertSame(0, PartnerApplication::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_invalid_web_submission_does_not_create_a_manual_rw(): void
    {
        $this->actingAs($this->user(1));
        $before = Region::count();
        $request = Request::create('/', 'POST', $this->payload()+['parent_rw_name'=>'RW 99']);
        try { app(PartnerApplicationController::class)->store($request); $this->fail('Missing document accepted'); }
        catch (\Illuminate\Validation\ValidationException $e) { $this->assertArrayHasKey('document',$e->errors()); }
        $this->assertSame($before, Region::count());
    }

    public function test_valid_api_submission_uses_the_signed_in_owner_and_private_document(): void
    {
        $actor = $this->user(1);
        $payload = array_replace($this->payload(), ['parent_region_id'=>2,'contact_email'=>'someone-else@example.test','applicant_name'=>'Forged name','user_id'=>8]);
        $request = Request::create('/', 'POST', $payload, [], ['document'=>UploadedFile::fake()->create('sk.pdf',1,'application/pdf')]);
        $request->setUserResolver(fn () => $actor);
        $response = app(PartnerApplicationApiController::class)->store($request);
        $this->assertSame(201, $response->getStatusCode());
        $application = PartnerApplication::firstOrFail();
        $this->assertEquals(1, $application->user_id);
        $this->assertSame($actor->email, $application->contact_email);
        $this->assertSame($actor->name, $application->applicant_name);
        $this->assertStringStartsWith('private:', $application->document_path);
        $this->assertFileExists(app(PartnerDocumentService::class)->path($application->document_path));
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_new_documents_are_private_and_path_traversal_is_rejected(): void
    {
        $service = app(PartnerDocumentService::class);
        $reference = $service->store(UploadedFile::fake()->create('sk.pdf',1,'application/pdf'));
        $this->assertStringStartsWith('private:partner_applications/', $reference);
        $this->assertFileExists($service->path($reference));
        $this->assertSame([], Storage::disk('public')->allFiles());
        try { $service->path('partner_applications/../../.env'); $this->fail('Traversal accepted'); }
        catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) { $this->assertSame(404,$e->getStatusCode()); }
    }

    public function test_legacy_document_migration_checks_content_and_can_be_repeated(): void
    {
        $path = 'partner_applications/synthetic.pdf';
        Storage::disk('public')->put($path, 'synthetic-document');
        $application = PartnerApplication::create($this->payload()+['document_path'=>$path, 'user_id'=>1]);
        $this->artisan('kemitraan:protect-documents')->assertSuccessful();
        $this->assertSame('private:'.$path, $application->fresh()->document_path);
        $this->assertSame('synthetic-document', Storage::disk('local')->get($path));
        Storage::disk('public')->assertMissing($path);
        $this->artisan('kemitraan:protect-documents')->assertSuccessful();
    }
    public function test_approval_cannot_promote_an_email_belonging_to_another_citizen(): void
    {
        DB::table('users')->where('id',8)->update(['email'=>'victim@example.test']);
        $this->actingAs($this->user(4));
        $application = PartnerApplication::create(array_replace($this->payload(), ['parent_region_id'=>2,'contact_email'=>'victim@example.test','user_id'=>1]));
        $response = app(\App\Http\Controllers\Admin\PartnerApplicationController::class)->approve(Request::create('/', 'POST', ['reason'=>'Uji']), $application->id);
        $this->assertStringContainsString('tidak sesuai', $response->getSession()->get('error'));
        $this->assertSame('user', $this->user(8)->role);
        $this->assertSame('pending', $application->fresh()->status);
    }

    public function test_processed_application_cannot_be_approved_again(): void
    {
        $this->actingAs($this->user(4));
        $application = PartnerApplication::create(array_replace($this->payload(), ['parent_region_id'=>2,'status'=>'rejected','user_id'=>1]));
        $response = app(\App\Http\Controllers\Admin\PartnerApplicationController::class)->approve(Request::create('/', 'POST', ['reason'=>'Uji']), $application->id);
        $this->assertStringContainsString('sudah diproses', $response->getSession()->get('error'));
        $this->assertSame('rejected', $application->fresh()->status);
    }

}
