<?php

namespace Tests\Feature;

use App\Http\Controllers\{MediaController};
use App\Http\Controllers\Api\{AuthController, UnitChatApiController};
use App\Models\{User, Laporan};
use App\Services\{BookingAccess, GoogleIdentityService, PublicAssetService, ReportAccess};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\{Request, UploadedFile};
use Illuminate\Support\Facades\{Auth, DB, Schema, Storage, Validator};
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;

class SecurityAuditRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('regions', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('type'); $t->integer('parent_id')->nullable();
        });
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->uuid('uuid')->nullable();
            foreach (['name', 'email', 'password', 'google_id', 'name_hash', 'nik_hash', 'phone_hash', 'phone', 'avatar',
                'remember_token', 'ktp_photo_path', 'face_photo_path', 'rt', 'rw', 'nik', 'address'] as $column) $t->string($column)->nullable();
            $t->string('role')->default('user'); $t->string('status')->default('aktif');
            $t->integer('region_id')->nullable(); $t->string('verification_status')->default('pending'); $t->timestamps();
        });
        Schema::create('personal_access_tokens', function (Blueprint $t) {
            $t->id(); $t->morphs('tokenable'); $t->string('name'); $t->string('token', 64)->unique();
            $t->text('abilities')->nullable(); $t->timestamp('expires_at')->nullable(); $t->timestamp('last_used_at')->nullable(); $t->timestamps();
        });
        Schema::create('staff_permissions', function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->string('unit_key'); $t->timestamps();
        });
        Schema::create('kyc_verifications', function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->string('status');
            $t->string('ktp_image_path')->nullable(); $t->string('face_image_path')->nullable(); $t->timestamps();
        });
        Schema::create('laporans', function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->integer('region_id'); $t->string('rt'); $t->string('rw');
            $t->text('bukti')->nullable(); $t->string('status')->default('Pending'); $t->timestamps();
        });
        foreach (['unit', 'pasar'] as $kind) {
            Schema::create($kind . '_chat_sessions', function (Blueprint $t) {
                $t->id(); $t->integer('user_id')->nullable(); $t->integer('region_id');
                $t->string('service_type')->nullable(); $t->string('session_token')->unique();
                $t->string('user_name')->nullable(); $t->string('status')->default('bot');
                $t->integer('unread_admin_count')->default(0); $t->integer('unread_user_count')->default(0);
                $t->text('last_message')->nullable(); $t->timestamp('last_message_at')->nullable(); $t->text('item_reference')->nullable(); $t->timestamps();
            });
            Schema::create($kind . '_chat_messages', function (Blueprint $t) {
                $t->id(); $t->integer('session_id'); $t->integer('sender_id')->nullable();
                $t->string('sender_type'); $t->text('message'); $t->boolean('is_read')->default(false);
                $t->text('item_data')->nullable(); $t->timestamps();
            });
        }
        DB::table('regions')->insert([
            ['id' => 1, 'name' => 'Kecamatan Bengkalis', 'type' => 'kecamatan', 'parent_id' => null],
            ['id' => 7, 'name' => 'Desa Uji', 'type' => 'desa', 'parent_id' => 1],
            ['id' => 8, 'name' => 'Desa Lain', 'type' => 'desa', 'parent_id' => 1],
        ]);
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Pemilik Uji', 'email' => 'owner@gmail.com', 'region_id' => 7],
            ['id' => 2, 'name' => 'Pengguna Lain', 'email' => 'other@gmail.com', 'region_id' => 8],
        ]);
        Storage::fake('private');
        Storage::fake('public');
        Auth::setUser(User::find(1));
    }

    private function request(array $data = []): Request
    {
        $r = Request::create('/test', 'POST', $data);
        $r->setUserResolver(fn () => Auth::user());
        return $r;
    }

    private function assertDenied(callable $action, int $status): void
    {
        try {
            $action();
            $this->fail('Akses seharusnya ditolak.');
        } catch (HttpExceptionInterface $e) {
            $this->assertSame($status, $e->getStatusCode());
        }
    }

    public function test_warga_api_is_scoped_to_rt_and_cannot_review_kyc(): void
    {
        DB::table('users')->where('id', 1)->update(['rt' => '01', 'rw' => '02']);
        DB::table('users')->where('id', 2)->update(['rt' => '01', 'rw' => '02']);
        $admin = new User(); $admin->id = 99; $admin->role = 'admin_rt'; $admin->region_id = 7; $admin->rt = '01'; $admin->rw = '02';
        Auth::setUser($admin);
        $controller = app(\App\Http\Controllers\Api\AdminWargaApiController::class);
        $this->assertSame([1], array_column($controller->index($this->request())->getData()->data, 'id'));
        try { $controller->show(2); $this->fail('Cross-region citizen exposed'); }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) { $this->assertTrue(true); }
        $this->assertDenied(fn () => $controller->approveKyc($this->request(), 1), 403);
        $this->assertDenied(fn () => $controller->rejectKyc($this->request(), 1), 403);
        $admin->rt = '03';
        $this->assertCount(0, $controller->index($this->request())->getData()->data);
    }

    public function test_auth_cutover_revokes_tokens_once_without_deleting_unrelated_files(): void
    {
        Storage::fake('local');
        $disk = Storage::disk('local');
        $disk->makeDirectory('test-sessions');
        config(['session.driver' => 'file', 'session.files' => $disk->path('test-sessions')]);
        $disk->put('test-sessions/' . str_repeat('a', 40), 'test session');
        $disk->put('test-sessions/.gitignore', '*');
        User::find(1)->createToken('before');
        DB::table('users')->update(['remember_token' => 'old']);
        $this->artisan('security:invalidate-auth --once')->assertExitCode(0);
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
        $this->assertNull(DB::table('users')->value('remember_token'));
        $disk->assertMissing('test-sessions/' . str_repeat('a', 40));
        $disk->assertExists('test-sessions/.gitignore');
        User::find(1)->createToken('after');
        $this->artisan('security:invalidate-auth --once')->assertExitCode(0);
        $this->assertSame(1, DB::table('personal_access_tokens')->count());
    }

    public function test_protected_payment_files_require_the_booking_owner(): void
    {
        foreach (['rental_bookings', 'gas_orders', 'mobil_bookings', 'fasilitas_umum_bookings', 'pasar_orders'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id(); $t->integer('user_id'); $t->integer('region_id');
                foreach (['receipt_path', 'payment_proof', 'proof_of_payment', 'delivery_proof_image', 'surat_pengantar'] as $column) $t->string($column)->nullable();
                $t->timestamps(); $t->softDeletes();
            });
        }
        foreach (['rental_bookings', 'gas_orders', 'mobil_bookings', 'fasilitas_umum_bookings', 'pasar_orders'] as $table) {
            $path = 'payment_proofs/' . $table . '.jpg';
            $column = in_array($table, ['gas_orders', 'pasar_orders']) ? 'proof_of_payment' : 'payment_proof';
            DB::table($table)->insert(['user_id' => 1, 'region_id' => 7, $column => $path]);
            Auth::setUser(User::find(1));
            app(\App\Services\SensitiveUploadAccess::class)->authorize($path);
            $this->assertTrue(true);
            Auth::setUser(User::find(2));
            $this->assertDenied(fn () => app(\App\Services\SensitiveUploadAccess::class)->authorize($path), 403);
        }
    }

    public function test_disabled_user_cannot_use_existing_authenticated_session(): void
    {
        $user = User::find(1); $user->status = 'non_aktif'; Auth::setUser($user);
        $this->assertDenied(fn () => app(\App\Http\Middleware\CheckRole::class)->handle($this->request(), fn () => response('secret')), 403);
    }

    private function identityFixture(): void
    {
        DB::table('kyc_verifications')->insert(['user_id' => 1, 'status' => 'pending', 'ktp_image_path' => 'kyc/ktp/test.enc']);
        $image = UploadedFile::fake()->image('test.jpg', 30, 20)->get();
        Storage::disk('private')->put('kyc/ktp/test.enc', \App\Services\FileEncryptionService::encrypt($image));
    }

    public function test_identity_owner_can_view_but_other_citizen_cannot(): void
    {
        $this->identityFixture();
        $response = app(MediaController::class)->secureKtpImage('test.enc');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        Auth::setUser(User::find(2));
        $this->assertDenied(fn () => app(MediaController::class)->secureKtpImage('test.enc'), 403);
    }

    public function test_identity_review_requires_correct_role_and_region(): void
    {
        $this->identityFixture();
        $admin = User::find(2); $admin->role = 'admin_desa'; Auth::setUser($admin);
        $this->assertDenied(fn () => app(MediaController::class)->secureKtpImage('test.enc'), 403);
        $admin->region_id = 7;
        $this->assertSame(200, app(MediaController::class)->secureKtpImage('test.enc')->getStatusCode());
        $admin->role = 'staff';
        $this->assertDenied(fn () => app(MediaController::class)->secureKtpImage('test.enc'), 403);
    }

    public function test_finalized_or_corrupt_identity_is_never_returned_raw(): void
    {
        $this->identityFixture();
        Storage::disk('private')->put('kyc/ktp/test.enc', '<script>private document</script>');
        $this->assertDenied(fn () => app(MediaController::class)->secureKtpImage('test.enc'), 404);
        DB::table('kyc_verifications')->update(['status' => 'approved']);
        $this->assertDenied(fn () => app(MediaController::class)->secureKtpImage('test.enc'), 404);
    }

    public function test_login_google_rejects_unsigned_identity_fields(): void
    {
        $this->postJson('/api/login/google', ['email' => 'owner@gmail.com', 'name' => 'Owner', 'google_id' => 'fake'])
            ->assertUnprocessable()->assertJsonValidationErrors('id_token');
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_google_verified_subject_overrides_client_identity(): void
    {
        DB::table('users')->where('id', 1)->update(['google_id' => 'trusted-subject']);
        $this->mock(GoogleIdentityService::class)->shouldReceive('verify')->once()->with('verified-test-token')
            ->andReturn(['sub' => 'trusted-subject', 'email' => 'owner@gmail.com', 'name' => 'Owner']);
        $response = app(AuthController::class)->loginGoogle($this->request([
            'id_token' => 'verified-test-token', 'email' => 'other@gmail.com', 'google_id' => 'attacker',
        ]));
        $this->assertSame(1, $response->getData()->data->user->id);
        $this->assertSame(1, (int) DB::table('personal_access_tokens')->value('tokenable_id'));
    }

    public function test_chat_token_cannot_read_another_accounts_messages(): void
    {
        $token = str_repeat('a', 64);
        DB::table('unit_chat_sessions')->insert(['id' => 1, 'user_id' => 2, 'region_id' => 7, 'service_type' => 'gas', 'session_token' => $token]);
        DB::table('unit_chat_messages')->insert(['session_id' => 1, 'sender_type' => 'user', 'message' => 'PRIVATE']);
        $response = app(UnitChatApiController::class)->getChatHistory($this->request(['region_id' => 7, 'session_token' => $token]), 'gas');
        $this->assertStringNotContainsString('PRIVATE', $response->getContent());
        $this->assertSame(1, $response->getData()->data->session->user_id);
    }

    public function test_guest_cannot_use_account_chat_token_and_receives_server_generated_token(): void
    {
        Auth::forgetGuards();
        $token = str_repeat('b', 64);
        DB::table('unit_chat_sessions')->insert(['id' => 1, 'user_id' => 2, 'region_id' => 7, 'service_type' => 'gas', 'session_token' => $token]);
        $response = app(UnitChatApiController::class)->getChatHistory($this->request(['region_id' => 7, 'session_token' => $token]), 'gas');
        $session = $response->getData()->data->session;
        $this->assertNotSame($token, $session->session_token);
        $this->assertSame(64, strlen($session->session_token));
        $next = app(UnitChatApiController::class)->getChatHistory($this->request(['region_id' => 7, 'session_token' => $session->session_token]), 'gas');
        $this->assertSame($session->id, $next->getData()->data->session->id);
    }

    public function test_market_chat_does_not_accept_another_accounts_token(): void
    {
        $token = str_repeat('c', 64);
        DB::table('pasar_chat_sessions')->insert(['id' => 1, 'user_id' => 2, 'region_id' => 7, 'session_token' => $token]);
        foreach ([\App\Http\Controllers\User\PasarDaerahController::class, \App\Http\Controllers\Api\PasarDaerahApiController::class] as $controller) {
            $response = app($controller)->getChatHistory($this->request(['session_token' => $token]), 7);
            $this->assertNull($response->getData()->data->session);
        }
    }

    public function test_citizen_cannot_list_or_update_administrative_reports(): void
    {
        $controller = app(\App\Http\Controllers\Api\LaporanController::class);
        $this->assertDenied(fn () => $controller->getAdminReports($this->request()), 403);
        $this->assertDenied(fn () => $controller->forwardReport($this->request(['action' => 'resolve']), 1), 403);
    }

    public function test_report_admin_is_scoped_by_region_and_rt_rw(): void
    {
        DB::table('laporans')->insert([
            ['id' => 1, 'user_id' => 2, 'region_id' => 7, 'rt' => '01', 'rw' => '02'],
            ['id' => 2, 'user_id' => 2, 'region_id' => 7, 'rt' => '02', 'rw' => '02'],
            ['id' => 3, 'user_id' => 2, 'region_id' => 8, 'rt' => '01', 'rw' => '02'],
        ]);
        $admin = Auth::user(); $admin->role = 'admin_rt'; $admin->rt = '01'; $admin->rw = '02';
        $this->assertSame([1], app(ReportAccess::class)->administrativeQuery($admin)->pluck('id')->all());
        $admin->role = 'staff';
        $this->assertSame(0, app(ReportAccess::class)->administrativeQuery($admin)->count());
        $admin->role = 'admin_desa'; $admin->region_id = null;
        $this->assertSame(0, app(ReportAccess::class)->administrativeQuery($admin)->count());
    }

    public function test_receipt_requires_owner_or_region_unit_permission(): void
    {
        $booking = new \App\Models\PasarOrder(['user_id' => 2, 'region_id' => 8]);
        $viewer = Auth::user(); $viewer->role = 'admin_desa';
        $this->assertFalse(app(BookingAccess::class)->canView($booking, 'pasar', $viewer));
        $viewer->region_id = 8;
        $this->assertTrue(app(BookingAccess::class)->canView($booking, 'pasar', $viewer));
        $viewer->role = 'staff';
        $this->assertFalse(app(BookingAccess::class)->canView($booking, 'pasar', $viewer));
        $this->assertTrue(app(BookingAccess::class)->canView($booking, 'pasar', User::find(2)));
    }

    public function test_public_storage_report_image_requires_ownership(): void
    {
        DB::table('laporans')->insert(['user_id' => 2, 'region_id' => 8, 'rt' => '01', 'rw' => '01', 'bukti' => 'laporan/test.jpg']);
        Storage::disk('public')->put('laporan/test.jpg', UploadedFile::fake()->image('test.jpg')->get());
        $asset = fn () => app(PublicAssetService::class)->serve(Storage::disk('public')->path(''), 'laporan/test.jpg', false);
        $this->assertDenied($asset, 403);
        Auth::setUser(User::find(2));
        $this->assertSame(200, $asset()->getStatusCode());
    }

    public function test_assets_reject_traversal_and_executable_files(): void
    {
        $service = app(PublicAssetService::class);
        $this->assertDenied(fn () => $service->serve(public_path(), '../.env', true), 403);
        $this->assertDenied(fn () => $service->serve(public_path(), 'index.php', true), 404);
        $this->assertDenied(fn () => $service->serve(public_path(), '%2e%2e/.env', true), 403);
    }

    public function test_upload_validation_rejects_disguised_html_svg_and_fake_magic_bytes(): void
    {
        foreach (['<html>not an image</html>', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>', "\x89PNG\r\n\x1a\ninvalid"] as $payload) {
            $file = UploadedFile::fake()->createWithContent('photo.png', $payload);
            $this->assertFalse(Validator::make(['photo' => $file], ['photo' => 'image'])->passes());
        }
        $this->assertTrue(Validator::make(['photo' => UploadedFile::fake()->image('photo.png')], ['photo' => 'image'])->passes());
    }

    public function test_api_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) $this->postJson('/api/login', [])->assertUnprocessable();
        $this->postJson('/api/login', [])->assertStatus(429);
    }
}
