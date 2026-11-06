<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Services\{FonnteService, NotificationDeliveryService};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\{DB, Hash, Http, Schema};
use Tests\TestCase;

class NotificationChannelsTest extends TestCase
{
    use \Tests\Support\EditorialDatabase { setUp as setUpEditorial; }

    protected function setUp(): void
    {
        $this->setUpEditorial();
        Schema::table('users',function (Blueprint $t) { $t->string('status')->default('aktif'); $t->string('fcm_token')->nullable(); });
        (require database_path('migrations/2026_10_28_130000_add_notification_delivery_channels.php'))->up();
        DB::table('users')->where('id',1)->update(['phone'=>'08123456789']);
        Schema::create('pasar_produks', function (Blueprint $t) {
            $t->unsignedBigInteger('store_id')->nullable();
            $t->id(); $t->unsignedBigInteger('region_id'); $t->string('nama_produk'); $t->string('status'); $t->integer('stok'); $t->decimal('harga'); $t->timestamps();
        });
        Schema::create('market_stores',function (Blueprint $t) { $t->id(); $t->string('name'); $t->string('status'); });
        Schema::create('market_payments',function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('pasar_order_id'); $t->string('status'); });
        DB::table('pasar_produks')->insert(['id'=>1,'region_id'=>1,'nama_produk'=>'Kerupuk warga','status'=>'tersedia','stok'=>20,'harga'=>10000]);
        config(['fonnte.token'=>'test-token','fonnte.base_url'=>'https://api.fonnte.com','notification_channels.enabled'=>true]);
    }

    private function consent(string $mode = 'instant', string $category = 'orders'): void
    {
        DB::table('notification_settings')->updateOrInsert(['user_id'=>1],[
            'categories'=>json_encode([$category=>$mode]), 'consented_at'=>now(), 'push_enabled'=>false,
            'verified_phone_hash'=>app(NotificationDeliveryService::class)->phoneHash($this->user(1)->phone),
            'created_at'=>now(),'updated_at'=>now(),
        ]);
    }

    private function notice(string $type = 'order_created'): Notification
    {
        return Notification::create(['user_id'=>1,'type'=>$type,'title'=>'Pesanan diperbarui','message'=>'Data pribadi 1234567890123456','link'=>$type === 'produk_baru' ? route('pasar.show',1) : route('user.activity'),'is_read'=>false]);
    }

    public function test_citizen_cannot_open_an_admin_notification_link(): void
    {
        $this->actingAs($this->user(1))->getJson('/buka-web/notifikasi/admin/1')->assertNotFound();
    }

    public function test_queued_notice_is_cancelled_when_article_changes_or_recipient_moves(): void
    {
        $this->consent('instant','news'); $item=$this->content(1,'Berita','published');
        Notification::create(['user_id'=>1,'type'=>'berita','title'=>'Kabar baru','message'=>'Ringkasan lama','link'=>route('announcements.show',$item)]);
        $job=DB::table('notification_deliveries')->first(); $service=app(NotificationDeliveryService::class);
        $this->assertNotNull($service->source($job,$this->user(1)));
        $item->update(['title'=>'Judul yang telah diperbaiki']);
        $this->assertNull($service->source($job,$this->user(1)));
        $item->update(['title'=>'Gotong royong warga']);
        DB::table('users')->where('id',1)->update(['region_id'=>7]);
        $this->assertNull($service->source($job,$this->user(1)));
    }

    public function test_no_whatsapp_without_verified_number_and_explicit_category_consent(): void
    {
        $this->notice(); $this->assertSame(0,DB::table('notification_deliveries')->count());
        $this->consent(); $notification=$this->notice();
        app(NotificationDeliveryService::class)->enqueue($notification);
        $this->assertSame(1,DB::table('notification_deliveries')->count());
        $this->notice('berita'); $this->assertSame(1,DB::table('notification_deliveries')->count());
    }

    public function test_delivery_contains_two_links_without_copying_private_details(): void
    {
        $this->consent(); $notification=$this->notice();
        Http::fake(['api.fonnte.com/*'=>Http::response(['status'=>true,'id'=>['provider-1']])]);
        $this->artisan('notifications:deliver')->assertSuccessful();
        Http::assertSent(function ($request) use ($notification) {
            $body=$request['message'];
            return str_contains($body,'/buka-web/notifikasi/user/'.$notification->id)
                && str_contains($body,'/buka-aplikasi/notifikasi/user/'.$notification->id)
                && !str_contains($body,'1234567890123456');
        });
        $this->assertSame('accepted',DB::table('notification_deliveries')->value('state'));
        $this->artisan('notifications:deliver')->assertSuccessful(); Http::assertSentCount(1);
    }

    public function test_opt_out_or_phone_change_cancels_pending_delivery(): void
    {
        $this->consent(); $this->notice();
        DB::table('notification_settings')->where('user_id',1)->update(['consented_at'=>null]);
        $this->artisan('notifications:deliver')->assertSuccessful();
        $this->assertSame('cancelled',DB::table('notification_deliveries')->value('state')); Http::assertNothingSent();
        $this->consent(); $this->notice();
        DB::table('users')->where('id',1)->update(['phone'=>'08999999999']);
        $this->artisan('notifications:deliver')->assertSuccessful();
        $this->assertSame(2,DB::table('notification_deliveries')->where('state','cancelled')->count()); Http::assertNothingSent();
    }

    public function test_timeout_is_not_blindly_retried(): void
    {
        $this->consent(); $this->notice();
        Http::fake(fn () => throw new ConnectionException('simulated timeout'));
        $this->artisan('notifications:deliver')->assertSuccessful();
        $this->assertSame('unknown',DB::table('notification_deliveries')->value('state'));
        $this->travel(20)->minutes(); $this->artisan('notifications:deliver')->assertSuccessful();
        $this->assertSame(1,DB::table('notification_deliveries')->value('attempts'));
    }

    public function test_daily_market_digest_groups_messages(): void
    {
        $this->travelTo(now()->setTime(10, 0));
        $this->consent('daily','market'); $this->notice('produk_baru'); $this->notice('produk_baru');
        Http::fake(['api.fonnte.com/*'=>Http::response(['status'=>true,'id'=>['digest-1']])]);
        $this->artisan('notifications:deliver')->assertSuccessful(); Http::assertNothingSent();
        $this->travelTo(now()->addDay()->setTime(7, 59));
        $this->artisan('notifications:deliver')->assertSuccessful(); Http::assertNothingSent();
        $this->travel(1)->minutes(); $this->artisan('notifications:deliver')->assertSuccessful();
        Http::assertSentCount(1);
        $this->assertSame(2,DB::table('notification_deliveries')->where('state','accepted')->count());
    }

    public function test_forwarded_link_does_not_open_another_users_notification(): void
    {
        $notification=$this->notice();
        $url=route('notification-link.web',['kind'=>'user','notification'=>$notification->id]);
        $this->actingAs($this->user(6))->getJson($url)->assertNotFound();
        $this->actingAs($this->user(1))->getJson($url)->assertOk()->assertJsonPath('destination',route('user.activity'));
    }

    public function test_admin_notification_link_accepts_admin_kind_but_checks_role_and_region(): void
    {
        Schema::create('admin_notifications', function (Blueprint $t) {
            $t->id(); $t->integer('region_id')->nullable(); $t->integer('reference_id')->nullable();
            foreach (['type', 'title', 'message'] as $field) $t->string($field)->nullable();
        });
        DB::table('admin_notifications')->insert([
            'id'=>1, 'region_id'=>1, 'type'=>'info', 'title'=>'Informasi desa', 'message'=>'Informasi pengelola',
        ]);
        $url=route('notification-link.web',['kind'=>'admin','notification'=>1]);
        $this->actingAs($this->user(4))->getJson($url)->assertOk()
            ->assertJsonPath('destination',route('admin.notifications.index'));
        $this->actingAs($this->user(7))->getJson($url)->assertNotFound();
        $this->actingAs($this->user(1))->getJson($url)->assertNotFound();
    }

    public function test_webhook_requires_secret_and_does_not_downgrade_delivered_status(): void
    {
        $this->consent(); $this->notice(); config(['notification_channels.webhook_secret'=>'test-secret']);
        DB::table('notification_deliveries')->update(['state'=>'accepted','provider_id'=>'wa-1']);
        $this->postJson('/api/webhooks/fonnte-status',['id'=>'wa-1','state'=>'delivered'])->assertForbidden();
        $this->withHeader('X-Webhook-Token','test-secret')->postJson('/api/webhooks/fonnte-status',['id'=>'wa-1','state'=>'delivered'])->assertOk();
        $this->withHeader('X-Webhook-Token','test-secret')->postJson('/api/webhooks/fonnte-status',['id'=>'wa-1','status'=>'sent'])->assertOk();
        $this->assertSame('delivered',DB::table('notification_deliveries')->value('state'));
    }

    public function test_otp_wrong_attempts_persist_and_code_cannot_be_reused(): void
    {
        DB::table('notification_settings')->insert(['user_id'=>1,'otp_hash'=>Hash::make('123456'),'otp_phone_hash'=>app(NotificationDeliveryService::class)->phoneHash($this->user(1)->phone),'otp_expires_at'=>now()->addMinutes(5)]);
        $this->actingAs($this->user(1))->postJson('/pengaturan-notifikasi/verifikasi',['code'=>'000000'])->assertStatus(422);
        $this->assertSame(1,DB::table('notification_settings')->value('otp_attempts'));
        $this->postJson('/pengaturan-notifikasi/verifikasi',['code'=>'123456'])->assertOk();
        $this->postJson('/pengaturan-notifikasi/verifikasi',['code'=>'123456'])->assertStatus(422);
    }

    public function test_product_withdrawal_or_price_change_cancels_queued_public_preview(): void
    {
        $this->consent('instant','market'); $this->notice('produk_baru');
        $job=DB::table('notification_deliveries')->first(); $service=app(NotificationDeliveryService::class);
        $this->assertNotNull($service->source($job,$this->user(1)));
        DB::table('pasar_produks')->where('id',1)->update(['harga'=>12000]);
        $this->assertNull($service->source($job,$this->user(1)));
        DB::table('pasar_produks')->where('id',1)->update(['harga'=>10000,'status'=>'nonaktif']);
        $this->assertNull($service->source($job,$this->user(1)));
        $this->artisan('notifications:deliver')->assertSuccessful(); Http::assertNothingSent();
    }

    public function test_order_link_resolves_exact_owned_order_and_rejects_foreign_or_deleted_order(): void
    {
        Schema::create('pasar_orders',function (Blueprint $t) { $t->unsignedBigInteger('store_id')->nullable(); $t->id(); $t->unsignedBigInteger('user_id'); $t->softDeletes(); });
        DB::table('pasar_orders')->insert([['id'=>9,'user_id'=>1],['id'=>10,'user_id'=>6]]);
        $order=\App\Models\PasarOrder::findOrFail(9);
        $link=\App\Services\NotificationService::orderLink($order);
        $this->assertStringContainsString('tab=pasar&order=9',$link);
        $notice=Notification::create(['user_id'=>1,'type'=>'status_berubah','title'=>'Pembaruan pesanan','link'=>$link]);
        $url=route('notification-link.web',['kind'=>'user','notification'=>$notice->id]);
        $this->actingAs($this->user(1))->getJson($url)->assertOk()->assertJsonPath('destination',$link);
        $notice->update(['link'=>\App\Services\NotificationService::orderLink(\App\Models\PasarOrder::findOrFail(10))]);
        $this->getJson($url)->assertNotFound();
        $notice->update(['link'=>$link]); DB::table('pasar_orders')->where('id',9)->delete();
        $this->getJson($url)->assertNotFound();
        $this->assertSame('orders',app(NotificationDeliveryService::class)->category('status_berubah'));
    }

    public function test_market_status_update_notifies_once_and_keeps_foreign_village_out(): void
    {
        Schema::create('pasar_complaints', function (Blueprint $t) { $t->id(); $t->integer('pasar_order_id'); $t->string('status'); $t->softDeletes(); });
        Schema::create('pasar_orders',function (Blueprint $t) { $t->unsignedBigInteger('store_id')->nullable();
            $t->id(); $t->unsignedBigInteger('user_id'); $t->unsignedBigInteger('region_id'); $t->string('order_number');
            $t->string('status'); $t->unsignedBigInteger('handled_by')->nullable(); $t->timestamp('delivered_at')->nullable(); $t->timestamp('confirmed_at')->nullable(); $t->timestamps(); $t->softDeletes();
        });
        DB::table('pasar_orders')->insert(['id'=>9,'user_id'=>1,'region_id'=>1,'order_number'=>'TEST-9','status'=>'paid']);
        $this->consent(); $this->actingAs($this->user(4));
        $controller=app(\App\Http\Controllers\Admin\UnitPasarDaerahController::class);
        foreach ([1,2] as $attempt) $controller->pesananUpdate(\Illuminate\Http\Request::create('/','PUT',['status'=>'confirmed']),9);
        $this->assertSame(1,Notification::count());
        $this->assertSame(1,DB::table('notification_deliveries')->count());
        $this->assertStringContainsString('tab=pasar&order=9',Notification::first()->link);
        $this->actingAs($this->user(7));
        try {
            $controller->pesananUpdate(\Illuminate\Http\Request::create('/','PUT',['status'=>'cancelled']),9);
            $this->fail('A different village must not update the order.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertSame('confirmed',DB::table('pasar_orders')->value('status'));
        }
        $this->assertSame(1,Notification::count());
    }

    public function test_activity_deep_link_can_open_an_older_order_beyond_the_first_page(): void
    {
        Schema::create('system_settings',fn (Blueprint $t)=>$t->id());
        foreach (['rental_bookings','gas_orders','mobil_bookings','fasilitas_umum_bookings','pasar_orders'] as $table) {
            Schema::create($table,function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('user_id'); $t->timestamps(); $t->softDeletes(); });
        }
        Schema::create('pasar_order_items',function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('pasar_order_id'); });
        foreach (range(1,10) as $id) DB::table('pasar_orders')->insert(['id'=>$id,'user_id'=>$id === 10 ? 6 : 1,'created_at'=>now()->subDays(11-$id)]);
        $this->actingAs($this->user(1));
        $view=app(\App\Http\Controllers\User\ActivityController::class)->index(\Illuminate\Http\Request::create('/aktivitas?tab=pasar&order=1'));
        $this->assertSame([1],$view->getData()['pasarOrders']->pluck('id')->all());
        $this->assertSame(1,$view->getData()['focusedOrder']);
        $this->getJson('/aktivitas?tab=pasar&order=10')->assertNotFound();
    }

    public function test_missing_provider_token_is_a_failure_not_simulated_success(): void
    {
        config(['fonnte.token'=>'']);
        $result=app(FonnteService::class)->sendOtp('08123456789','654321');
        $this->assertFalse($result['status']); Http::assertNothingSent();
    }
}
