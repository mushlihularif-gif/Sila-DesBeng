<?php
namespace Tests\Feature;

use App\Http\Controllers\ContentWorkspaceController;
use App\Http\Controllers\User\ActivityController;
use App\Services\NotificationDeliveryService;
use App\Support\ServiceTerms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;

class CitizenExperienceTest extends TestCase
{
    use \Tests\Support\EditorialDatabase;

    private function deletion($item, int $user, ?int $revision = null)
    {
        $request = Request::create('/', 'DELETE', ['revision'=>$revision ?? $item->fresh()->revision]);
        $request->setUserResolver(fn () => $this->user($user));
        return app(ContentWorkspaceController::class)->destroy($request, $item);
    }

    public function test_only_owner_can_delete_an_unchanged_unsent_draft(): void
    {
        $draft = $this->content(1, 'Berita', 'draft');
        foreach ([[6,0,404], [1,99,409]] as [$user,$revision,$status]) {
            try { $this->deletion($draft,$user,$revision); $this->fail('Deletion should fail'); }
            catch (HttpExceptionInterface $e) { $this->assertSame($status,$e->getStatusCode()); }
            $this->assertDatabaseHas('announcements',['id'=>$draft->id]);
        }
        $this->deletion($draft,1);
        $this->assertDatabaseMissing('announcements',['id'=>$draft->id]);
        foreach (['pending','review','published','merged','rejected'] as $state) {
            $item=$this->content(1,'Berita',$state);
            try { $this->deletion($item,1); $this->fail('Submitted article deleted'); }
            catch (HttpExceptionInterface $e) { $this->assertSame(422,$e->getStatusCode()); }
        }
    }

    public function test_news_activity_includes_own_submissions_and_published_credits_only(): void
    {
        $own=$this->content(); $foreign=$this->content(6); $credited=$this->content(4,'Berita','published');
        $private=$this->content(4,'Berita','draft'); $announcement=$this->content(1,'Pengumuman','published');
        foreach ([$credited,$private] as $item) DB::table('announcement_sources')->insert([
            'announcement_id'=>$item->id,'contributor_id'=>1,'contributor_name'=>'Akun 1','uses_text'=>true,'is_staged'=>false,
        ]);
        $ids=app(ActivityController::class)->newsFor($this->user(1))->pluck('id')->all();
        $this->assertContains($own->id,$ids); $this->assertContains($credited->id,$ids);
        foreach ([$foreign,$private,$announcement] as $item) $this->assertNotContains($item->id,$ids);
    }

    public function test_whatsapp_proof_is_reused_only_for_the_same_number_without_automatic_consent(): void
    {
        (require database_path('migrations/2026_10_28_130000_add_notification_delivery_channels.php'))->up();
        \Illuminate\Support\Facades\Schema::table('users', fn ($t) => $t->string('phone_hash')->nullable());
        $user=$this->user(1); $user->phone='08123456789'; $user->save();
        $service=app(NotificationDeliveryService::class);
        $this->assertFalse($service->preferences($user)['verified']);
        $service->recordVerifiedPhone($user,'08999999999');
        $this->assertFalse($service->preferences($user)['verified']);
        $service->recordVerifiedPhone($user,'+628123456789');
        $this->assertTrue($service->preferences($user)['verified']);
        $this->assertSame('off',$service->mode($user,'orders','whatsapp'));
        $user->phone='08999999999'; $user->save();
        $this->assertFalse($service->preferences($user)['verified']);
    }

    public function test_terms_render_as_numbered_items_and_separate_explanation_without_html_execution(): void
    {
        $text="1. Jaga alat.\n2. Laporkan kerusakan.\nSegera hubungi petugas.\n\nKeterangan <script>alert(1)</script>";
        $blocks=ServiceTerms::blocks($text);
        $this->assertCount(2,$blocks); $this->assertCount(2,$blocks[0]['items']);
        $html=\Illuminate\Support\Facades\Blade::render('<x-service-terms :text="$text" />',compact('text'));
        $this->assertStringContainsString('<ol>',$html);
        $this->assertStringContainsString('Laporkan kerusakan. Segera hubungi petugas.',$html);
        $this->assertStringNotContainsString('<script>',$html);
    }

    public function test_activity_and_admin_terms_render(): void
    {
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        $this->actingAs($this->user(1));
        $this->content(1,'Berita','review');
        $data=['newsItems'=>app(ActivityController::class)->newsFor($this->user(1))->paginate(5),'setting'=>null,'focusedTab'=>'berita'];
        foreach (['rentalBookings','gasOrders','mobilBookings','fasilitasBookings'] as $key) $data[$key]=new \Illuminate\Pagination\LengthAwarePaginator([],0,5);
        $html=view('users.activity',$data)->render();
        $this->assertStringContainsString('Berita Daerah',$html);
        $this->assertStringContainsString('Sedang diperiksa admin',$html);
        if ($dir=getenv('SEGI_MAPS_TEST_EXPORT')) file_put_contents($dir.'/activity.html',$html);
        \Illuminate\Support\Facades\Schema::create('services', function ($t) { $t->id(); $t->string('name'); $t->string('slug'); });
        \Illuminate\Support\Facades\Schema::create('region_services', function ($t) { $t->integer('region_id'); $t->integer('service_id'); $t->boolean('is_active'); $t->boolean('is_exclusive'); $t->timestamps(); });
        \Illuminate\Support\Facades\Schema::create('admin_notifications', function ($t) { $t->id(); $t->integer('region_id'); $t->boolean('is_read'); $t->timestamps(); });
        $this->actingAs($this->user(4));
        foreach (['penyewaan','mobil','fasilitas_umum'] as $unit) {
            $html=view('admin.unit.'.$unit.'.sop',['sop_active'=>'ditanggung','sop_ditanggung'=>ServiceTerms::RENTAL_LIABILITY,'sop_tidak_ditanggung'=>'Keterangan uji','default_ditanggung'=>ServiceTerms::RENTAL_LIABILITY,'default_tidak_ditanggung'=>'Bawaan'])->render();
            $this->assertStringContainsString('data-terms-editor',$html);
            if ($dir=getenv('SEGI_MAPS_TEST_EXPORT')) file_put_contents($dir.'/terms-'.$unit.'.html',$html);
        }
    }

    public function test_editorial_pages_render_for_supported_roles(): void
    {
        view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        foreach (['user','admin_rt','admin_rw','admin_desa','super_admin','staff'] as $role) {
            $user=$this->user(match($role){'admin_rt'=>2,'admin_rw'=>3,default=>4});
            $user->role=$role; $this->actingAs($user);
            $request=Request::create('/kelola-kabar'); $request->setUserResolver(fn ()=>$user);
            $html=app(ContentWorkspaceController::class)->index($request)->render();
            $this->assertStringContainsString('segi-back',$html);
            if ($role==='staff') $this->assertStringNotContainsString('Tulis Berita Daerah</button>',$html);
            if ($role==='user') {
                $item=$this->content($user->id,'Berita','draft');
                $html=app(ContentWorkspaceController::class)->show($request,$item)->render();
                $this->assertStringContainsString('id="ai-progress"',$html);
                $this->assertStringNotContainsString('lg:grid-cols-3',$html);
                if ($dir=getenv('SEGI_MAPS_TEST_EXPORT')) file_put_contents($dir.'/content.show.html',$html);
            }
        }
    }
}
