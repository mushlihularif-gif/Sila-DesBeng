<?php

namespace Tests\Feature;

use App\Models\Region;
use App\Models\UnitChatSession;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class UnitChatEndToEndTest extends TestCase
{
    private Region $kabupaten;
    private Region $kecamatan;
    private Region $desaA;
    private Region $rwA;
    private Region $rtA;
    private Region $desaB;
    private User $citizenRtA;
    private User $adminDesaA;
    private User $adminDesaB;
    private User $staffGasA;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'cache.default' => 'array',
            'session.driver' => 'array',
            'ddos.whitelist' => ['127.0.0.1'],
        ]);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('regions', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('type')->nullable();
            $t->unsignedBigInteger('parent_id')->nullable();
            $t->text('profile_text')->nullable();
            $t->string('contact_phone')->nullable();
            $t->string('contact_email')->nullable();
            $t->json('payment_info')->nullable();
            $t->json('settings')->nullable();
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
            $t->string('verification_status')->default('unverified');
            $t->rememberToken();
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
            $t->timestamps();
        });

        Schema::create('unit_chat_messages', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('session_id');
            $t->string('sender_type');
            $t->unsignedBigInteger('sender_id')->nullable();
            $t->text('message');
            $t->json('item_data')->nullable();
            $t->string('attachment_url')->nullable();
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });

        (require database_path('migrations/2026_11_21_000001_add_clear_watermarks_to_unit_chat_sessions_table.php'))->up();

        $this->kabupaten = Region::create(['name' => 'Kabupaten Bengkalis', 'type' => 'kabupaten']);
        $this->kecamatan = Region::create(['name' => 'Kecamatan Bengkalis', 'type' => 'kecamatan', 'parent_id' => $this->kabupaten->id]);
        $this->desaA = Region::create(['name' => 'Desa Pematang Duku Timur', 'type' => 'desa', 'parent_id' => $this->kecamatan->id]);
        $this->rwA = Region::create(['name' => 'RW 01', 'type' => 'rw', 'parent_id' => $this->desaA->id]);
        $this->rtA = Region::create(['name' => 'RT 01', 'type' => 'rt', 'parent_id' => $this->rwA->id]);
        $this->desaB = Region::create(['name' => 'Desa Wonosari', 'type' => 'desa', 'parent_id' => $this->kecamatan->id]);

        $this->citizenRtA = $this->makeUser('Siti Warga', 'siti@test.local', 'user', $this->rtA->id);
        $this->adminDesaA = $this->makeUser('Admin Pematang', 'admin_a@test.local', 'admin_desa', $this->desaA->id);
        $this->adminDesaB = $this->makeUser('Admin Wonosari', 'admin_b@test.local', 'admin_desa', $this->desaB->id);
        $this->staffGasA = $this->makeUser('Budi Staf Gas', 'staf_gas@test.local', 'staff', $this->desaA->id, [
            'created_by' => $this->adminDesaA->id,
            'staff_enabled' => true,
        ]);
        DB::table('staff_permissions')->insert([
            'user_id' => $this->staffGasA->id,
            'unit_key' => 'gas',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeUser(string $name, string $email, string $role, ?int $regionId, array $extra = []): User
    {
        $user = new User();
        $user->uuid = Str::uuid()->toString();
        $user->name = $name;
        $user->email = $email;
        $user->username = Str::slug($name, '_') . '_' . rand(10, 999);
        $user->password = Hash::make('PasswordKuat123!');
        $user->role = $role;
        $user->status = 'aktif';
        $user->region_id = $regionId;
        foreach ($extra as $key => $value) {
            $user->{$key} = $value;
        }
        $user->save();

        return $user;
    }

    public function test_all_four_unit_chats_deliver_from_citizen_to_admin_desa_and_return_reply(): void
    {
        foreach (['gas', 'penyewaan', 'mobil', 'fasilitas_umum'] as $service) {
            // 1. Warga yang terdaftar di RT mengirim pesan lewat rute web /unit-chat/{service}/send
            $sendRes = $this->actingAs($this->citizenRtA)
                ->postJson("/unit-chat/{$service}/send", [
                    'message' => "Halo petugas {$service}, apakah tersedia?",
                ]);

            $sendRes->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('data.region_id', $this->desaA->id);

            $session = UnitChatSession::where('service_type', $service)
                ->where('user_id', $this->citizenRtA->id)
                ->first();

            $this->assertNotNull($session, "Session for {$service} should exist");
            $this->assertSame($this->desaA->id, (int) $session->region_id, "RT citizen should resolve to parent Desa for {$service}");
            $this->assertSame('Siti Warga', $session->user_name);
            $this->assertSame(1, (int) $session->unread_admin_count);

            // 2. Admin Desa melihat sesi chat warga lewat endpoint polling /sessions
            $sessionsRes = $this->actingAs($this->adminDesaA)
                ->getJson("/admin/unit/chat-service/{$service}/sessions");

            $sessionsRes->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('data.total_sessions', 1)
                ->assertJsonPath('data.total_unread', 1)
                ->assertJsonPath('data.sessions.0.user_name', 'Siti Warga')
                ->assertJsonPath('data.sessions.0.needs_reply', true);

            // 3. Admin Desa membuka isi pesan -> unread admin menjadi 0
            $messagesRes = $this->actingAs($this->adminDesaA)
                ->getJson("/admin/unit/chat-service/{$service}/{$session->id}/messages");

            $messagesRes->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('data.session.user_name', 'Siti Warga');
            $this->assertStringContainsString("Halo petugas {$service}", $messagesRes->getContent());

            // 4. Admin Desa membalas chat warga
            $replyRes = $this->actingAs($this->adminDesaA)
                ->postJson("/admin/unit/chat-service/{$service}/{$session->id}/reply", [
                    'message' => "Siap Bu Siti, layanan {$service} tersedia hari ini.",
                ]);

            $replyRes->assertOk()
                ->assertJsonPath('status', 'success')
                ->assertJsonPath('data.chat_message.sender_type', 'admin')
                ->assertJsonPath('data.chat_message.sender_name', 'Admin Pematang');

            // 5. Warga mendapat badge unread 1 lalu melihat balasan Admin Desa saat membuka riwayat
            $unreadRes = $this->actingAs($this->citizenRtA)
                ->getJson("/unit-chat/unread-counts?region_id={$this->desaA->id}");

            $unreadRes->assertOk()
                ->assertJsonPath("data.{$service}", 1);

            $historyRes = $this->actingAs($this->citizenRtA)
                ->getJson("/unit-chat/{$service}/history?region_id={$this->desaA->id}");

            $historyRes->assertOk()
                ->assertJsonPath('data.is_escalated', true);
            $this->assertStringContainsString("Siap Bu Siti, layanan {$service} tersedia hari ini.", $historyRes->getContent());

            // 6. Admin Desa menandai selesai, lalu warga kirim pesan baru -> status terbuka kembali
            $resolveRes = $this->actingAs($this->adminDesaA)
                ->postJson("/admin/unit/chat-service/{$service}/{$session->id}/resolve");
            $resolveRes->assertOk();
            $this->assertSame('resolved', $session->fresh()->status);

            $reopenRes = $this->actingAs($this->citizenRtA)
                ->postJson("/unit-chat/{$service}/send", [
                    'region_id' => $this->desaA->id,
                    'message' => 'Terima kasih, boleh saya pesan sekarang?',
                ]);
            $reopenRes->assertOk();
            $this->assertSame('escalated', $session->fresh()->status);
            $this->assertSame(1, (int) $session->fresh()->unread_admin_count);
        }
    }

    public function test_staff_can_read_and_reply_assigned_unit_only_and_village_scope_is_enforced(): void
    {
        // Warga kirim chat ke Gas & Penyewaan di Desa A
        $this->actingAs($this->citizenRtA)->postJson('/unit-chat/gas/send', [
            'region_id' => $this->desaA->id,
            'message' => 'Gas 5.5 kg ready?',
        ])->assertOk();

        $this->actingAs($this->citizenRtA)->postJson('/unit-chat/penyewaan/send', [
            'region_id' => $this->desaA->id,
            'message' => 'Tenda tersedia?',
        ])->assertOk();

        $gasSession = UnitChatSession::where('service_type', 'gas')->firstOrFail();
        $rentalSession = UnitChatSession::where('service_type', 'penyewaan')->firstOrFail();

        // Staf khusus Gas bisa melihat, membaca, dan membalas chat Gas (termasuk lewat rute alias)
        $this->actingAs($this->staffGasA)
            ->getJson('/admin/unit/chat-service/gas/sessions')
            ->assertOk()
            ->assertJsonPath('data.total_sessions', 1);

        $this->actingAs($this->staffGasA)
            ->getJson("/admin/chat-service/gas/{$gasSession->id}/messages")
            ->assertOk()
            ->assertJsonPath('data.session.user_name', 'Siti Warga');

        $this->actingAs($this->staffGasA)
            ->postJson("/admin/unit/chat-service/gas/{$gasSession->id}/reply", [
                'message' => 'Ready kak, silakan langsung pesan ya.',
            ])
            ->assertOk()
            ->assertJsonPath('data.chat_message.sender_name', 'Budi Staf Gas');

        // Staf khusus Gas DITOLAK (403) pada unit lain (penyewaan, mobil, fasilitas_umum)
        $this->actingAs($this->staffGasA)
            ->getJson('/admin/unit/chat-service/penyewaan/sessions')
            ->assertStatus(403);

        $this->actingAs($this->staffGasA)
            ->postJson("/admin/unit/chat-service/penyewaan/{$rentalSession->id}/reply", [
                'message' => 'Tidak boleh lolos',
            ])
            ->assertStatus(403);

        // Admin Desa lain (Desa B) tidak melihat sesi Desa A & mendapat 404 saat mengakses sesi Desa A
        $this->actingAs($this->adminDesaB)
            ->getJson('/admin/unit/chat-service/gas/sessions')
            ->assertOk()
            ->assertJsonPath('data.total_sessions', 0);

        $this->actingAs($this->adminDesaB)
            ->postJson("/admin/unit/chat-service/gas/{$gasSession->id}/reply", [
                'message' => 'Lintas desa tidak boleh',
            ])
            ->assertStatus(404);
    }

    public function test_escalate_auto_creates_session_and_long_item_reference_is_sanitized(): void
    {
        // Klik "Chat Petugas Layanan" sebelum membuka riwayat tidak lagi 404
        $escRes = $this->actingAs($this->citizenRtA)
            ->postJson('/unit-chat/gas/escalate', [
                'region_id' => $this->desaA->id,
            ]);

        $escRes->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.is_escalated', true);

        // Item reference JSON panjang (>260 char) disanitasi ke nama produk agar aman untuk VARCHAR(255)
        $longJson = json_encode([
            'name' => 'Gas LPG 5,5 Kg Pink',
            'price' => 'Rp 95.000',
            'image' => 'https://siladesbeng.inovasia.site/storage/gas/' . str_repeat('abcdef123456', 25) . '.jpg',
        ]);
        $this->assertGreaterThan(255, strlen($longJson));

        $this->actingAs($this->citizenRtA)
            ->postJson('/unit-chat/gas/send', [
                'region_id' => $this->desaA->id,
                'message' => 'Mau tanya stok produk ini',
                'item_reference' => $longJson,
                'item_data' => $longJson,
            ])
            ->assertOk();

        $session = UnitChatSession::where('service_type', 'gas')->firstOrFail();
        $this->assertSame('Gas LPG 5,5 Kg Pink', $session->item_reference);
        $this->assertLessThanOrEqual(240, strlen($session->item_reference));
    }

    public function test_catalog_and_detail_chat_widgets_render_exact_village_region_id_and_web_endpoint(): void
    {
        $html = view('users.partials.service_chat_widget', [
            'serviceType' => 'gas',
            'serviceTitle' => 'Layanan Gas',
            'targetRegionId' => $this->desaA->id,
            'targetRegion' => $this->desaA,
            'catalogItems' => collect(),
            'filterRegions' => collect(),
        ])->render();

        $this->assertStringContainsString('data-region-id="' . $this->desaA->id . '"', $html);
        $this->assertStringContainsString('Petugas Desa Pematang Duku Timur', $html);
        $this->assertStringContainsString('/unit-chat', $html);
        $this->assertStringNotContainsString('/api/unit-chat', $html);

        $detailHtml = view('components.unit-chat-widget', [
            'service' => 'mobil',
            'title' => 'Layanan Sewa Transportasi',
            'regionId' => $this->rtA->id, // RT harus otomatis dinormalisasi naik ke Desa A
            'regionName' => $this->desaA->name,
            'itemName' => 'Mobil Pick-Up Desa',
            'itemPrice' => 'Rp 250.000 / hari',
            'itemImage' => '',
        ])->render();

        $this->assertStringContainsString('var unitRegionId = ' . $this->desaA->id . ';', $detailHtml);
        $this->assertStringContainsString('/unit-chat', $detailHtml);
        $this->assertStringNotContainsString('/api/unit-chat', $detailHtml);
    }

    public function test_guest_citizen_chat_with_explicit_village_region_reaches_admin_desa_and_staff(): void
    {
        $sendRes = $this->postJson('/unit-chat/gas/send', [
            'region_id' => $this->desaA->id,
            'message' => 'Halo saya tamu mau tanya gas 3 kg',
        ]);

        $sendRes->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.region_id', $this->desaA->id);

        $token = $sendRes->json('data.session_token');
        $this->assertSame(64, strlen((string) $token));

        $sessionsRes = $this->actingAs($this->staffGasA)
            ->getJson('/admin/unit/chat-service/gas/sessions');
        $sessionsRes->assertOk()
            ->assertJsonPath('data.total_sessions', 1)
            ->assertJsonPath('data.sessions.0.user_name', 'Warga');

        $sessionId = $sessionsRes->json('data.sessions.0.id');
        $this->actingAs($this->staffGasA)
            ->postJson("/admin/unit/chat-service/gas/{$sessionId}/reply", [
                'message' => 'Halo, stok gas 3 kg tersedia.',
            ])
            ->assertOk();

        \Illuminate\Support\Facades\Auth::logout();
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $historyRes = $this->withHeaders(['X-Chat-Session-Token' => $token])
            ->getJson("/unit-chat/gas/history?region_id={$this->desaA->id}");
        $historyRes->assertOk();
        $this->assertStringContainsString('Halo, stok gas 3 kg tersedia.', $historyRes->getContent());
    }

    public function test_clear_chat_is_independent_for_citizen_staff_and_admin_desa_like_whatsapp(): void
    {
        // 1. Warga kirim pesan & Staf Gas membalas
        $this->actingAs($this->citizenRtA)->postJson('/unit-chat/gas/send', [
            'region_id' => $this->desaA->id,
            'message' => 'Pesan awal warga',
        ])->assertOk();

        $session = UnitChatSession::where('service_type', 'gas')->firstOrFail();

        $this->actingAs($this->staffGasA)->postJson("/admin/unit/chat-service/gas/{$session->id}/reply", [
            'message' => 'Balasan awal staf',
        ])->assertOk();

        // 2. Warga klik Bersihkan Chat -> hanya sisi warga yang bersih; Staf & Admin Desa tetap melihat lengkap
        $this->actingAs($this->citizenRtA)->postJson('/unit-chat/gas/clear', [
            'region_id' => $this->desaA->id,
        ])->assertOk()->assertJsonPath('status', 'success');

        $citizenHistoryAfterClear = $this->actingAs($this->citizenRtA)
            ->getJson("/unit-chat/gas/history?region_id={$this->desaA->id}")
            ->assertOk()
            ->getContent();
        $this->assertStringNotContainsString('Pesan awal warga', $citizenHistoryAfterClear);
        $this->assertStringNotContainsString('Balasan awal staf', $citizenHistoryAfterClear);
        $this->assertStringContainsString('sudah dibersihkan', $citizenHistoryAfterClear);

        $staffViewAfterUserClear = $this->actingAs($this->staffGasA)
            ->getJson("/admin/unit/chat-service/gas/{$session->id}/messages")
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('Pesan awal warga', $staffViewAfterUserClear);
        $this->assertStringContainsString('Balasan awal staf', $staffViewAfterUserClear);

        $adminDesaViewAfterUserClear = $this->actingAs($this->adminDesaA)
            ->getJson("/admin/unit/chat-service/gas/{$session->id}/messages")
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('Pesan awal warga', $adminDesaViewAfterUserClear);
        $this->assertStringContainsString('Balasan awal staf', $adminDesaViewAfterUserClear);

        // 3. Staf klik Bersihkan Chat -> sisi Staf bersih, namun Admin Desa tetap melihat riwayat penuh
        $this->actingAs($this->staffGasA)
            ->postJson("/admin/unit/chat-service/gas/{$session->id}/clear")
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->actingAs($this->staffGasA)
            ->getJson("/admin/unit/chat-service/gas/{$session->id}/messages")
            ->assertOk()
            ->assertJsonCount(0, 'data.messages');

        $this->actingAs($this->staffGasA)
            ->getJson('/admin/unit/chat-service/gas/sessions')
            ->assertOk()
            ->assertJsonPath('data.sessions.0.last_message', 'Obrolan dibersihkan (tampilan Anda)');

        $adminDesaStillSeesAll = $this->actingAs($this->adminDesaA)
            ->getJson("/admin/unit/chat-service/gas/{$session->id}/messages")
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('Pesan awal warga', $adminDesaStillSeesAll);
        $this->assertStringContainsString('Balasan awal staf', $adminDesaStillSeesAll);

        // 4. Warga mengirim pesan baru setelah masing-masing membersihkan -> pesan baru langsung muncul di semua sisi
        $this->actingAs($this->citizenRtA)->postJson('/unit-chat/gas/send', [
            'region_id' => $this->desaA->id,
            'message' => 'Pesan baru setelah bersih',
        ])->assertOk();

        $citizenAfterNew = $this->actingAs($this->citizenRtA)
            ->getJson("/unit-chat/gas/history?region_id={$this->desaA->id}")
            ->assertOk()
            ->getContent();
        $this->assertStringNotContainsString('Pesan awal warga', $citizenAfterNew);
        $this->assertStringContainsString('Pesan baru setelah bersih', $citizenAfterNew);

        $staffAfterNew = $this->actingAs($this->staffGasA)
            ->getJson("/admin/unit/chat-service/gas/{$session->id}/messages")
            ->assertOk()
            ->getContent();
        $this->assertStringNotContainsString('Pesan awal warga', $staffAfterNew);
        $this->assertStringContainsString('Pesan baru setelah bersih', $staffAfterNew);

        // 5. Staf tanpa izin unit penyewaan tetap ditolak (403) saat mencoba clear unit lain
        $this->actingAs($this->staffGasA)
            ->postJson("/admin/unit/chat-service/penyewaan/{$session->id}/clear")
            ->assertStatus(403);
    }
}
