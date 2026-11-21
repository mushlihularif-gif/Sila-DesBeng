<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\Announcement;
use App\Models\Notification;
use App\Models\Region;
use App\Models\Service;
use App\Models\StaffPermission;
use App\Models\UnitChatSession;
use App\Models\User;
use App\Services\EditorialService;
use App\Services\NotificationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ComprehensiveNotificationTest extends TestCase
{
    private Region $kab;
    private Region $kec;
    private Region $desa;
    private Region $rw;
    private Region $rt;
    private User $superAdmin;
    private User $adminDesa;
    private User $staffGas;
    private User $staffAlat;
    private User $staffKabar;
    private User $adminRw;
    private User $adminRt;
    private User $warga;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array',
            'session.driver' => 'array',
            'notification_channels.enabled' => false,
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('regions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('type')->nullable();
            $t->unsignedBigInteger('parent_id')->nullable();
            $t->timestamps();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('uuid')->nullable();
            $t->string('name');
            $t->string('name_hash')->nullable();
            $t->string('username')->nullable();
            $t->string('email')->unique();
            $t->string('password');
            $t->string('avatar')->nullable();
            $t->string('role')->default('user');
            $t->string('status')->default('aktif');
            $t->unsignedBigInteger('region_id')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->boolean('staff_enabled')->default(false);
            $t->string('verification_status')->default('verified');
            $t->timestamps();
        });
        Schema::create('files', function (Blueprint $t) {
            $t->id();
            $t->string('fileable_type')->nullable();
            $t->unsignedBigInteger('fileable_id')->nullable();
            $t->string('path')->nullable();
            $t->timestamps();
        });
        Schema::create('staff_permissions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->string('unit_key');
            $t->timestamps();
        });
        Schema::create('services', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });
        Schema::create('region_services', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('region_id');
            $t->unsignedBigInteger('service_id');
            $t->boolean('is_active')->default(true);
            $t->boolean('is_exclusive')->default(false);
            $t->timestamps();
        });
        Schema::create('notifications', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('laporan_id')->nullable();
            $t->string('type')->nullable();
            $t->string('title');
            $t->text('message');
            $t->string('link')->nullable();
            $t->string('icon')->nullable();
            $t->string('image')->nullable();
            $t->unsignedBigInteger('admin_id')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->boolean('is_read')->default(false);
            $t->timestamp('read_at')->nullable();
            $t->timestamps();
        });
        Schema::create('admin_notifications', function (Blueprint $t) {
            $t->id();
            $t->string('type');
            $t->unsignedBigInteger('reference_id')->nullable();
            $t->unsignedBigInteger('region_id')->nullable();
            $t->string('title');
            $t->text('message');
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });
        Schema::create('unit_chat_sessions', function (Blueprint $t) {
            $t->id();
            $t->string('service_type', 32);
            $t->unsignedBigInteger('region_id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('session_token', 64)->nullable();
            $t->string('user_name')->nullable();
            $t->string('status')->default('bot');
            $t->string('item_reference')->nullable();
            $t->text('last_message')->nullable();
            $t->timestamp('last_message_at')->nullable();
            $t->unsignedInteger('unread_admin_count')->default(0);
            $t->unsignedInteger('unread_user_count')->default(0);
            $t->unsignedBigInteger('user_cleared_before_id')->nullable();
            $t->unsignedBigInteger('admin_cleared_before_id')->nullable();
            $t->json('cleared_by_admins')->nullable();
            $t->timestamps();
        });
        Schema::create('unit_chat_messages', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('session_id');
            $t->string('sender_type');
            $t->unsignedBigInteger('sender_id')->nullable();
            $t->text('message');
            $t->json('item_data')->nullable();
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });
        Schema::create('announcements', function (Blueprint $t) {
            $t->id();
            $t->string('title')->nullable();
            $t->text('description')->nullable();
            $t->string('post_category')->default('Berita');
            $t->unsignedBigInteger('region_id')->nullable();
            $t->unsignedBigInteger('target_audience_id')->nullable();
            $t->unsignedBigInteger('admin_id')->nullable();
            $t->unsignedBigInteger('publisher_id')->nullable();
            $t->string('workflow_state')->default('draft');
            $t->boolean('is_active')->default(false);
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
        });
        Schema::create('announcement_sources', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('announcement_id');
            $t->unsignedBigInteger('contributor_id')->nullable();
            $t->boolean('uses_text')->default(false);
            $t->boolean('is_staged')->default(false);
            $t->timestamps();
        });
        Schema::create('announcement_images', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('announcement_id');
            $t->unsignedBigInteger('contributor_id')->nullable();
            $t->boolean('pending_removal')->default(false);
            $t->boolean('is_staged')->default(false);
            $t->timestamps();
        });

        $this->kab = Region::create(['name' => 'Kabupaten Bengkalis', 'type' => 'kabupaten']);
        $this->kec = Region::create(['name' => 'Kecamatan Bengkalis', 'type' => 'kecamatan', 'parent_id' => $this->kab->id]);
        $this->desa = Region::create(['name' => 'Desa Pematang Duku Timur', 'type' => 'desa', 'parent_id' => $this->kec->id]);
        $this->rw = Region::create(['name' => 'RW 01', 'type' => 'rw', 'parent_id' => $this->desa->id]);
        $this->rt = Region::create(['name' => 'RT 01', 'type' => 'rt', 'parent_id' => $this->rw->id]);

        $this->superAdmin = $this->makeUser('Super Admin', 'super@test.local', 'super_admin', null);
        $this->adminDesa = $this->makeUser('Admin Desa', 'desa@test.local', 'admin_desa', $this->desa->id);
        $this->staffGas = $this->makeUser('Staf Gas', 'stafgas@test.local', 'staff', $this->desa->id, ['created_by' => $this->adminDesa->id, 'staff_enabled' => true]);
        $this->staffAlat = $this->makeUser('Staf Alat', 'stafalat@test.local', 'staff', $this->desa->id, ['created_by' => $this->adminDesa->id, 'staff_enabled' => true]);
        $this->staffKabar = $this->makeUser('Staf Kabar', 'stafkabar@test.local', 'staff', $this->desa->id, ['created_by' => $this->adminDesa->id, 'staff_enabled' => true]);
        StaffPermission::create(['user_id' => $this->staffGas->id, 'unit_key' => 'gas']);
        StaffPermission::create(['user_id' => $this->staffAlat->id, 'unit_key' => 'sewa_alat']);
        StaffPermission::create(['user_id' => $this->staffKabar->id, 'unit_key' => 'kabar_informasi']);

        $this->adminRw = $this->makeUser('Ketua RW', 'rw@test.local', 'admin_rw', $this->rw->id);
        $this->adminRt = $this->makeUser('Ketua RT', 'rt@test.local', 'admin_rt', $this->rt->id);
        $this->warga = $this->makeUser('Warga Uji', 'warga@test.local', 'user', $this->rt->id);
    }

    private function makeUser(string $name, string $email, string $role, ?int $regionId, array $extra = []): User
    {
        $u = new User();
        $u->uuid = Str::uuid()->toString();
        $u->name = $name;
        $u->username = Str::slug($name, '_');
        $u->email = $email;
        $u->password = Hash::make('PasswordKuat123!');
        $u->role = $role;
        $u->status = 'aktif';
        $u->region_id = $regionId;
        $u->verification_status = 'verified';
        foreach ($extra as $k => $v) {
            $u->{$k} = $v;
        }
        $u->save();

        return $u;
    }

    public function test_unit_chat_notifies_admin_desa_and_assigned_staff_and_reply_notifies_citizen(): void
    {
        // 1. Warga mengirim chat Gas -> harus masuk AdminNotification (terlihat Admin Desa & Staf Gas, BUKAN Staf Alat/Super Admin)
        $this->actingAs($this->warga)
            ->postJson('/unit-chat/gas/send', [
                'region_id' => $this->desa->id,
                'message' => 'Halo kak, apakah stok gas 5,5 kg ready hari ini?',
            ])
            ->assertOk();

        $chatNotifs = AdminNotification::where('type', 'unit_chat_gas')->get();
        $this->assertCount(1, $chatNotifs);
        $notif = $chatNotifs->first();
        $this->assertSame($this->desa->id, (int) $notif->region_id);
        $this->assertFalse($notif->is_read);

        $this->assertSame([$notif->id], AdminNotification::untukPengguna($this->adminDesa)->pluck('id')->all());
        $this->assertSame([$notif->id], AdminNotification::untukPengguna($this->staffGas)->pluck('id')->all());
        $this->assertSame([], AdminNotification::untukPengguna($this->staffAlat)->pluck('id')->all());
        $this->assertSame([], AdminNotification::untukPengguna($this->superAdmin)->pluck('id')->all());

        // Presentasi tautan tujuan langsung ke tab chat unit terkait
        $meta = $notif->resolvePresentation($this->staffGas);
        $this->assertSame('chat', $meta['category']);
        $this->assertStringContainsString('tab=chat', $meta['url']);

        // 2. Pesan kedua berturut-turut dalam 5 menit memperbarui notifikasi unread yang sama (anti-spam)
        $this->actingAs($this->warga)
            ->postJson('/unit-chat/gas/send', [
                'region_id' => $this->desa->id,
                'message' => 'Bisa diantar sekarang?',
            ])
            ->assertOk();
        $this->assertSame(1, AdminNotification::where('type', 'unit_chat_gas')->count());
        $this->assertStringContainsString('Bisa diantar sekarang?', $notif->fresh()->message);

        // 3. Saat Staf Gas membuka obrolan, notifikasi chat otomatis ditandai dibaca
        $session = UnitChatSession::firstOrFail();
        $this->actingAs($this->staffGas)
            ->getJson("/admin/unit/chat-service/gas/{$session->id}/messages")
            ->assertOk();
        $this->assertTrue($notif->fresh()->is_read);

        // 4. Saat Staf Gas membalas chat, warga langsung menerima Notification bertipe chat_reply
        $this->actingAs($this->staffGas)
            ->postJson("/admin/unit/chat-service/gas/{$session->id}/reply", [
                'message' => 'Ready kak, silakan langsung pesan di halaman gas ya.',
            ])
            ->assertOk();

        $userNotif = Notification::where('user_id', $this->warga->id)->where('type', 'chat_reply')->first();
        $this->assertNotNull($userNotif);
        $this->assertStringContainsString('Balasan Chat Layanan Gas', $userNotif->title);
        $this->assertStringContainsString('Ready kak', $userNotif->message);
    }

    public function test_broadcasts_reach_warga_admin_rt_and_admin_rw_and_service_activation_notifies_super_admin(): void
    {
        // Produk baru -> Warga, Admin RT, dan Admin RW semuanya menerima
        NotificationService::broadcastNewProduct('Gas LPG', 'Bright Gas 5,5 Kg', $this->desa->id, route('gas.sales'));
        foreach ([$this->warga, $this->adminRt, $this->adminRw] as $recipient) {
            $this->assertTrue(
                Notification::where('user_id', $recipient->id)->where('type', 'produk_baru')->exists(),
                "Role {$recipient->role} seharusnya menerima notifikasi produk baru"
            );
        }

        // Layanan baru diaktifkan -> Warga, RT, RW + Super Admin (platform) menerima
        $svc = Service::create(['name' => 'Penyewaan Mobil', 'slug' => 'penyewaan-mobil']);
        NotificationService::broadcastServiceActivated($this->desa, $svc);

        foreach ([$this->warga, $this->adminRt, $this->adminRw] as $recipient) {
            $this->assertTrue(
                Notification::where('user_id', $recipient->id)->where('type', 'layanan_baru')->exists(),
                "Role {$recipient->role} seharusnya menerima notifikasi layanan baru"
            );
        }
        $superNotif = AdminNotification::untukPengguna($this->superAdmin)->where('type', 'layanan_baru')->first();
        $this->assertNotNull($superNotif);
        $this->assertNull($superNotif->region_id);

        // Pengumuman / Berita terbit -> Warga, Admin RT, dan Admin RW semuanya menerima
        $ann = new Announcement();
        $ann->title = 'Kerja Bakti Desa Minggu Pagi';
        $ann->description = 'Diharapkan seluruh warga hadir membersihkan parit bersama di lapangan utama desa.';
        $ann->post_category = 'Berita';
        $ann->region_id = $this->desa->id;
        $ann->admin_id = $this->adminDesa->id;
        $ann->workflow_state = 'published';
        $ann->is_active = true;
        $ann->save();

        NotificationService::broadcastAnnouncement($ann);
        foreach ([$this->warga, $this->adminRt, $this->adminRw] as $recipient) {
            $this->assertTrue(
                Notification::where('user_id', $recipient->id)->where('type', 'berita')->exists(),
                "Role {$recipient->role} seharusnya menerima notifikasi berita desa"
            );
        }
    }

    public function test_kabar_submission_cancellation_and_stock_alerts_route_to_right_staff_and_admins(): void
    {
        // 1. Kiriman kabar masuk -> muncul di lonceng Admin Desa & Staf Kabar, BUKAN Staf Gas
        $draft = new Announcement();
        $draft->title = 'Dokumentasi Panen Raya RT 01';
        $draft->description = 'Kegiatan panen raya bersama masyarakat RT 01 berjalan lancar dan meriah.';
        $draft->post_category = 'Berita';
        $draft->region_id = $this->rt->id;
        $draft->admin_id = $this->warga->id;
        $draft->workflow_state = 'pending';
        $draft->is_active = false;
        $draft->save();

        app(EditorialService::class)->notifyReviewers($draft, 'Kiriman baru menunggu peninjauan', 'Ada kiriman baru.');

        $kabarAdminNotif = AdminNotification::where('type', 'announcement_submission')->first();
        $this->assertNotNull($kabarAdminNotif);
        $this->assertTrue(AdminNotification::untukPengguna($this->adminDesa)->whereKey($kabarAdminNotif->id)->exists());
        $this->assertTrue(AdminNotification::untukPengguna($this->staffKabar)->whereKey($kabarAdminNotif->id)->exists());
        $this->assertFalse(AdminNotification::untukPengguna($this->staffGas)->whereKey($kabarAdminNotif->id)->exists());

        // 2. Pembatalan gas & stok gas menipis -> muncul di Admin Desa & Staf Gas, BUKAN Staf Alat
        $cancelGas = AdminNotification::create([
            'type' => 'cancellation_gas',
            'reference_id' => 99,
            'region_id' => $this->desa->id,
            'title' => 'Permintaan Pembatalan Gas LPG',
            'message' => 'Warga membatalkan pesanan.',
            'is_read' => false,
        ]);
        $lowGas = AdminNotification::create([
            'type' => 'stock_low_gas',
            'reference_id' => 5,
            'region_id' => $this->desa->id,
            'title' => 'Stok Menipis',
            'message' => 'Stok Gas 3 Kg sisa 2 tabung.',
            'is_read' => false,
        ]);

        $gasStaffIds = AdminNotification::untukPengguna($this->staffGas)->pluck('id')->all();
        $alatStaffIds = AdminNotification::untukPengguna($this->staffAlat)->pluck('id')->all();
        $this->assertContains($cancelGas->id, $gasStaffIds);
        $this->assertContains($lowGas->id, $gasStaffIds);
        $this->assertNotContains($cancelGas->id, $alatStaffIds);
        $this->assertNotContains($lowGas->id, $alatStaffIds);
        $this->assertStringContainsString('tab=gas', $cancelGas->resolvePresentation($this->staffGas)['url']);
    }
}
