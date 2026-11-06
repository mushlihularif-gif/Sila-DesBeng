<?php

namespace Tests\Feature;

use App\Models\{Announcement, User};
use App\Services\{ContentAccess, EditorialService};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Http, Schema, Storage};
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EditorialWorkflowTest extends TestCase
{
    use \Tests\Support\EditorialDatabase;

    public function test_only_agreed_roles_can_manage_content_and_receive_review_requests(): void
    {
        $item=$this->content(); $access=app(ContentAccess::class);
        foreach (['admin_kecamatan','admin_kabupaten','admin','staff'] as $index=>$role) {
            $id=20+$index;
            DB::table('users')->insert(['id'=>$id,'name'=>'Akun peran lama','role'=>$role,'region_id'=>1,'verification_status'=>'verified']);
            $actor=$this->user($id);
            $this->assertFalse($access->manager($actor));
            $this->assertFalse($access->canInspect($actor,$item));
            $this->actingAs($actor)->postJson('/kelola-kabar',['category'=>'Berita'])->assertForbidden();
            $this->postJson('/kelola-kabar/ai',['mode'=>'compose','category'=>'Berita'])->assertForbidden();
        }
        DB::table('users')->insert(['id'=>30,'name'=>'Pengelola sistem','role'=>'super_admin','region_id'=>1,'verification_status'=>'verified']);
        $this->assertTrue($access->canManage($this->user(30),$item));
        $draft=$this->content(1,'Berita','draft');
        $this->service()->submit($draft,$this->user(1));
        $this->assertEqualsCanonicalizing([2,3,4],DB::table('notifications')->pluck('user_id')->all());
    }

    public function test_drafts_and_other_regions_are_inaccessible_through_public_api(): void
    {
        $draft = $this->content(1,'Berita','draft');
        $notice = $this->content(2,'Pengumuman','published');
        $news = $this->content(1,'Berita','published');
        $this->getJson('/api/news/'.$draft->id)->assertNotFound();
        $this->getJson('/api/news/'.$notice->id)->assertNotFound();
        $this->getJson('/api/news')->assertOk()->assertJsonCount(1,'data')->assertJsonPath('data.0.id',$news->id);
        $this->actingAs($this->user(6),'sanctum')->getJson('/api/news/'.$notice->id)->assertNotFound();
        $this->actingAs($this->user(1),'sanctum')->getJson('/api/news/'.$notice->id)->assertOk();
    }

    public function test_review_queue_is_shared_only_with_own_rt_rw_and_village(): void
    {
        $item = $this->content(); $access = app(ContentAccess::class);
        foreach ([2,3,4] as $id) $this->assertTrue($access->canManage($this->user($id),$item));
        foreach ([5,6,7] as $id) $this->assertFalse($access->canInspect($this->user($id),$item));
        $this->actingAs($this->user(5))->getJson('/kelola-kabar/'.$item->id.'/state')->assertNotFound();
        $this->actingAs($this->user(4))->getJson('/kelola-kabar/'.$item->id.'/state')->assertOk()->assertJsonMissingPath('edit_token');
    }

    public function test_only_one_session_can_claim_including_two_tabs_of_same_account(): void
    {
        $item = $this->content(); $lease = $this->service()->claim($this->user(2),$item->id);
        $this->assertSame(64,strlen($lease['token']));
        foreach ([2,3,4] as $id) {
            try { $this->service()->claim($this->user($id),$item->id); $this->fail('Two sessions obtained the lease'); }
            catch (HttpException $e) { $this->assertSame(409,$e->getStatusCode()); }
        }
        $this->assertSame(2,(int)$item->fresh()->editor_id);
    }

    public function test_expired_owner_cannot_overwrite_new_owner(): void
    {
        $item = $this->content(); $old = $this->service()->claim($this->user(2),$item->id);
        $this->travel(121)->seconds();
        $new = $this->service()->claim($this->user(3),$item->id);
        try {
            $this->service()->locked($this->user(2),$item->id,$old['token'],0, fn ($item) => $item->update(['title'=>'Stale overwrite']));
            $this->fail('Expired session wrote successfully');
        } catch (HttpException $e) { $this->assertSame(409,$e->getStatusCode()); }
        $this->assertSame('Gotong royong warga',$item->fresh()->title);
        $this->assertNotSame($old['token'],$new['token']);
    }

    public function test_revision_is_checked_and_draft_does_not_change_published_article(): void
    {
        $item = $this->content(1,'Berita','published'); $lease = $this->service()->claim($this->user(4),$item->id);
        $data = $item->only(['title','description','type','location','event_date']); $data['title']='Judul draf baru';
        $this->service()->locked($this->user(4),$item->id,$lease['token'],0,fn ($item) => $this->service()->save($item,$this->user(4),$data));
        $this->assertSame('Gotong royong warga',$item->fresh()->title);
        $this->assertSame('Judul draf baru',$item->fresh()->draft_data['title']);
        try { $this->service()->locked($this->user(4),$item->id,$lease['token'],0,fn ($i) => $i->update(['title'=>'Old'])); $this->fail('Stale version accepted'); }
        catch (HttpException $e) { $this->assertSame(409,$e->getStatusCode()); }
    }

    public function test_parent_rw_notice_requires_parent_approval_and_never_targets_other_rw(): void
    {
        $item = $this->content(2,'Pengumuman','draft');
        $data = $item->only(['title','description','type','location','event_date']); $data['target_audience_id']=2;
        $this->service()->save($item,$this->user(2),$data);
        $this->service()->submit($item,$this->user(2));
        $this->assertFalse($item->fresh()->is_active);
        $this->assertEqualsCanonicalizing([3,4],DB::table('notifications')->pluck('user_id')->all());
        $this->assertFalse(app(ContentAccess::class)->canManage($this->user(2),$item->fresh()));
        $this->assertTrue(app(ContentAccess::class)->canManage($this->user(3),$item->fresh()));
        $this->assertNotContains(6,app(ContentAccess::class)->targets($this->user(2)));
    }

    public function test_submitting_citizen_news_notifies_all_three_levels_immediately(): void
    {
        $item=$this->content(1,'Berita','draft');
        $this->service()->submit($item,$this->user(1));
        $this->assertEqualsCanonicalizing([2,3,4],DB::table('notifications')->pluck('user_id')->all());
        $this->assertSame('pending',$item->fresh()->workflow_state);
    }

    public function test_photos_require_same_access_as_their_unpublished_submission(): void
    {
        Storage::fake('private'); Storage::disk('private')->put('editorial/test.jpg','image');
        $item=$this->content(); $image=$item->images()->create(['image_path'=>'editorial/test.jpg','disk'=>'private']);
        $this->get($image->url)->assertNotFound();
        $this->actingAs($this->user(6))->get($image->url)->assertNotFound();
        $response = $this->actingAs($this->user(4))->get($image->url)->assertOk();
        $this->assertStringContainsString('no-store',$response->headers->get('Cache-Control'));
    }

    public function test_merge_rolls_back_every_source_if_any_is_locked(): void
    {
        $target=$this->content(4,'Berita','draft'); $source1=$this->content(); $source2=$this->content();
        $targetLease=$this->service()->claim($this->user(4),$target->id);
        $this->service()->claim($this->user(3),$source2->id);
        try {
            $this->service()->locked($this->user(4),$target->id,$targetLease['token'],0,fn ($item) => $this->service()->merge($item,$this->user(4),[
                ['id'=>$source1->id,'revision'=>0,'text'=>true],['id'=>$source2->id,'revision'=>0,'text'=>true],
            ])); $this->fail('Partial merge accepted');
        } catch (HttpException $e) { $this->assertSame(409,$e->getStatusCode()); }
        $this->assertSame('pending',$source1->fresh()->workflow_state);
        $this->assertSame(0,DB::table('announcement_sources')->count());
        $this->assertSame(0,DB::table('notifications')->count());
    }

    public function test_ai_input_is_isolated_and_does_not_mutate_content(): void
    {
        config(['services.gemini.api_key'=>'test-key']);
        Http::fake(['generativelanguage.googleapis.com/*'=>Http::response(['candidates'=>[['content'=>['parts'=>[['text'=>json_encode(['title'=>'Usulan','description'=>'Teks dari fakta','questions'=>[]])]]]]]])]);
        $this->actingAs($this->user(1))->postJson('/kelola-kabar/ai',['mode'=>'compose','category'=>'Berita','facts'=>'Membersihkan balai warga'])->assertOk()->assertJsonPath('title','Usulan');
        $this->assertSame(0,Announcement::count());
        Http::assertSent(fn ($request) => !isset($request->data()['history']) && $request->hasHeader('x-goog-api-key','test-key'));
    }

    public function test_unverified_citizen_cannot_submit_and_legacy_mutations_are_closed(): void
    {
        $this->actingAs($this->user(8))->post('/kelola-kabar',['category'=>'Berita'])->assertForbidden();
        $this->actingAs($this->user(2))->postJson('/wilayah/berita',['title'=>'Bypass'])->assertStatus(409);
    }

    public function test_documentation_merged_into_published_news_is_private_until_publication(): void
    {
        Storage::fake('private'); Storage::disk('private')->put('editorial/extra.jpg','photo');
        $target=$this->content(1,'Berita','published'); $source=$this->content();
        $source->update(['contribution_to'=>$target->id]);
        $photo=$source->images()->create(['image_path'=>'editorial/extra.jpg','disk'=>'private']);
        $lease=$this->service()->claim($this->user(4),$target->id);
        $this->service()->locked($this->user(4),$target->id,$lease['token'],0,fn ($item) => $this->service()->merge($item,$this->user(4),[
            ['id'=>$source->id,'revision'=>0,'text'=>true,'images'=>[$photo->id]],
        ]));
        $this->assertSame(0,$target->fresh()->images()->count());
        $staged=$target->allImages()->first();
        $this->get($staged->url)->assertNotFound();
        $this->assertTrue((bool)DB::table('announcement_sources')->value('is_staged'));
        $this->service()->locked($this->user(4),$target->id,$lease['token'],1,fn ($item) => $this->service()->publish($item,$this->user(4)));
        $this->get($staged->url)->assertOk();
        $this->assertSame(1,$target->fresh()->images()->count());
        $this->assertFalse((bool)DB::table('announcement_sources')->value('is_staged'));
    }

    public function test_retention_warns_then_removes_only_expired_unhandled_or_rejected_submissions(): void
    {
        $pending=$this->content(); $pending->update(['submitted_at'=>now()->subDays(16),'cleanup_notified_at'=>now()->subHours(2)]);
        $rejected=$this->content(1,'Berita','rejected'); $rejected->update(['rejected_at'=>now()->subDays(4),'cleanup_notified_at'=>now()->subHours(2)]);
        $handled=$this->content(1,'Berita','review'); $handled->update(['submitted_at'=>now()->subDays(20),'handled_at'=>now()->subDays(19)]);
        $warning=$this->content(); $warning->update(['submitted_at'=>now()->subDays(14)]);
        $this->artisan('content:maintain')->assertSuccessful();
        $this->assertNull($pending->fresh()); $this->assertNull($rejected->fresh());
        $this->assertNotNull($handled->fresh()); $this->assertNotNull($warning->fresh()->cleanup_notified_at);
    }

    public function test_scheduled_content_is_not_published_twice_by_repeated_maintenance(): void
    {
        $item=$this->content(4,'Pengumuman','scheduled'); $item->update(['scheduled_at'=>now()->subMinute()]);
        $this->artisan('content:maintain')->assertSuccessful();
        $count=DB::table('notifications')->count();
        $this->artisan('content:maintain')->assertSuccessful();
        $this->assertTrue($item->fresh()->is_active); $this->assertSame($count,DB::table('notifications')->count());
    }

    public function test_verification_or_region_change_revokes_access_to_edit_old_draft(): void
    {
        $item=$this->content(1,'Berita','draft'); $access=app(\App\Services\ContentAccess::class);
        $this->assertTrue($access->canWrite($this->user(1),$item));
        DB::table('users')->where('id',1)->update(['verification_status'=>'unverified']);
        $this->assertFalse($access->canWrite($this->user(1),$item));
        DB::table('users')->where('id',1)->update(['verification_status'=>'verified','region_id'=>7]);
        $this->assertFalse($access->canWrite($this->user(1),$item));
    }

    public function test_workspace_renders_for_citizen_and_reviewer(): void
    {
        $item=$this->content();
        $this->actingAs($this->user(1))->get('/kelola-kabar/'.$item->id)->assertOk()->assertSee('Cara menulis')->assertDontSee('Gabungkan Kiriman');
        $this->actingAs($this->user(4))->get('/kelola-kabar/'.$item->id)->assertOk()->assertSee('Gabungkan Kiriman')->assertSee('Riwayat Penanganan');
    }

    public function test_source_picker_can_search_authors_and_paginate_without_leaking_ineligible_sources(): void
    {
        $this->user(1)->update(['name'=>'Siti Aminah']);
        $this->assertStringStartsWith('$chacha20$', DB::table('users')->where('id',1)->value('name'));
        $target=$this->content(4,'Berita','draft');
        $eligible=[];
        for ($i=0;$i<13;$i++) $eligible[]=$this->content()->id;
        $this->content(6); // Different village.
        // A foreign resident must not even be decrypted by the author's search.
        DB::table('users')->where('id',6)->update(['name'=>'$chacha20$unreadable-foreign-record']);
        $this->content(1,'Berita','published');
        $this->content(1,'Berita','draft');
        $this->content(2,'Pengumuman','pending');
        $otherTarget=$this->content(1,'Berita','published');
        $this->content()->update(['contribution_to'=>$otherTarget->id]);
        $mine=$this->content(); $mine->update(['contribution_to'=>$target->id]); $eligible[]=$mine->id;
        $this->actingAs($this->user(4));
        $first=$this->getJson('/kelola-kabar/'.$target->id.'/sources?search=aminah')->assertOk()
            ->assertJsonPath('total',14)->assertJsonPath('current_page',1)->assertJsonPath('last_page',2)->assertJsonCount(12,'items');
        $second=$this->getJson('/kelola-kabar/'.$target->id.'/sources?search=aminah&page=2')->assertOk()->assertJsonCount(2,'items');
        $this->assertEqualsCanonicalizing($eligible,[...array_column($first->json('items'),'id'),...array_column($second->json('items'),'id')]);
        $this->getJson('/kelola-kabar/'.$target->id.'/sources?search=tidak-ada')->assertOk()->assertJsonPath('total',0);
        $this->getJson('/kelola-kabar/'.$target->id.'/sources?page=-1')->assertUnprocessable();
        $this->actingAs($this->user(7))->getJson('/kelola-kabar/'.$target->id.'/sources')->assertForbidden();
        $this->actingAs($this->user(1))->getJson('/kelola-kabar/'.$target->id.'/sources')->assertForbidden();
    }

    public function test_presence_displays_decrypted_names_only_for_recent_viewers_of_this_submission(): void
    {
        $this->user(2)->update(['name'=>'Ketua RT Sari']);
        $this->user(3)->update(['name'=>'Ketua RW Hasan']);
        $this->user(4)->update(['name'=>'Admin Desa Ayu']);
        $item=$this->content(); $other=$this->content();
        DB::table('announcement_presence')->insert([
            ['announcement_id'=>$item->id,'user_id'=>2,'seen_at'=>now()],
            ['announcement_id'=>$item->id,'user_id'=>3,'seen_at'=>now()->subMinute()],
            ['announcement_id'=>$other->id,'user_id'=>3,'seen_at'=>now()],
        ]);
        $response=$this->actingAs($this->user(4))->getJson('/kelola-kabar/'.$item->id.'/state')->assertOk();
        $this->assertEqualsCanonicalizing(['Ketua RT Sari','Admin Desa Ayu'],$response->json('presence'));
        $this->assertStringNotContainsString('$chacha20$', $response->getContent());
        $this->actingAs($this->user(1))->getJson('/kelola-kabar/'.$item->id.'/state')->assertOk()->assertJsonPath('presence',[]);
    }
}
