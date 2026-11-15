<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RegionSettingController;
use App\Http\Controllers\Admin\RequestController;
use App\Http\Controllers\Admin\SupirController;
use App\Http\Controllers\Api\WilayahAdminApiController;
use App\Http\Middleware\CheckRole;
use App\Mail\OtpMail;
use App\Models\FasilitasUmumBooking;
use App\Models\MobilBooking;
use App\Models\Region;
use App\Models\Supir;
use App\Models\User;
use App\Services\ServiceAssignment;
use App\Support\RegionalCitizens;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\Support\EditorialDatabase;
use Tests\TestCase;

class RoleOperationalAuditTest extends TestCase
{
    use EditorialDatabase { setUp as editorialSetUp; }

    protected function setUp(): void
    {
        $this->editorialSetUp();
        Mail::fake();
        Schema::table('users', function (Blueprint $t) {
            $t->string('password')->nullable();
            $t->string('remember_token')->nullable();
            $t->string('status')->default('aktif');
            $t->integer('created_by')->nullable();
            $t->boolean('staff_enabled')->default(false);
        });
        Schema::table('regions', function (Blueprint $t) {
            $t->json('payment_info')->nullable();
            $t->timestamps();
        });
        Schema::table('notifications', fn (Blueprint $t) => $t->integer('admin_id')->nullable());
        Schema::create('staff_permissions', function (Blueprint $t) {
            $t->id();
            $t->integer('user_id');
            $t->string('unit_key');
        });
        (require database_path('migrations/2026_07_13_212744_create_personal_access_tokens_table.php'))->up();
        Schema::create('activity_log', function (Blueprint $t) {
            $t->id();
            $t->integer('user_id');
            $t->string('action');
            $t->text('description');
            $t->string('ip_address')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        foreach (['mobils' => 'nama_mobil', 'fasilitas_umums' => 'nama_fasilitas'] as $table => $name) {
            Schema::create($table, function (Blueprint $t) use ($name) {
                $t->id();
                $t->integer('region_id');
                $t->string($name)->default('Unit');
                $t->string('status')->default('tersedia');
                $t->integer('stok')->default(2);
                $t->timestamps();
                $t->softDeletes();
            });
            DB::table($table)->insert(['id' => 1, 'region_id' => 1]);
        }
        foreach (['mobil_bookings' => 'mobil_id', 'fasilitas_umum_bookings' => 'fasilitas_id'] as $table => $fk) {
            Schema::create($table, function (Blueprint $t) use ($fk, $table) {
                $t->id();
                $t->integer($fk)->default(1);
                $t->integer('user_id')->default(1);
                $t->integer('region_id')->default(1);
                $t->string('status')->default('confirmed');
                $t->string('uuid')->nullable();
                $t->string('order_number')->nullable();
                $t->integer('quantity')->default(1);
                $t->integer('handled_by')->nullable();
                $t->integer('assigned_supir_id')->nullable();
                $t->string('cancellation_status')->nullable();
                $t->decimal('total_amount', 12, 2)->default(0);
                $t->timestamp('completion_time')->nullable();
                $t->timestamp('confirmed_at')->nullable();
                if ($table === 'mobil_bookings') {
                    $t->timestamp('delivery_time')->nullable();
                    $t->timestamp('arrival_time')->nullable();
                }
                $t->timestamps();
                $t->softDeletes();
            });
        }
        Schema::create('supirs', function (Blueprint $t) {
            $t->id();
            $t->integer('region_id')->default(1);
            $t->string('nama')->default('Petugas');
            $t->string('kontak')->nullable();
            $t->string('foto')->nullable();
            $t->string('layanan')->nullable();
            $t->integer('user_id')->nullable();
            $t->string('status')->default('Tersedia');
            $t->string('tipe')->default('supir');
            $t->boolean('is_sewa_mobil')->default(true);
            $t->boolean('is_fasilitas_umum')->default(false);
            $t->timestamps();
        });
        DB::table('users')->where('id', 4)->update(['password' => Hash::make('lama-password-123'), 'email' => 'admin@example.test']);
        $this->actingAs($this->user(4));
    }

    private function req(array $data = []): Request
    {
        $r = Request::create('/', 'POST', $data, server: ['HTTP_ACCEPT' => 'application/json']);
        $r->setLaravelSession(app('session.store'));

        return $r;
    }

    private function denied(callable $f, int $status = 403): void
    {
        try {
            $f();
            $this->fail('Unexpectedly allowed');
        } catch (HttpExceptionInterface $e) {
            $this->assertSame($status, $e->getStatusCode());
        }
    }

    private function staff(): User
    {
        DB::table('users')->where('id', 8)->update(['role' => 'staff', 'region_id' => 1, 'created_by' => 4, 'staff_enabled' => true]);
        DB::table('staff_permissions')->insert(['user_id' => 8, 'unit_key' => 'sewa_mobil']);

        return $this->user(8);
    }

    public function test_staff_order_buttons_reach_controller_but_still_check_unit_and_village(): void
    {
        $staff = $this->staff();
        $this->actingAs($staff);
        foreach (['admin.aktivitas.update-status', 'admin.aktivitas.delivery-proof', 'admin.aktivitas.cancellation'] as $name) {
            $r = $this->req();
            $route = clone Route::getRoutes()->getByName($name);
            $r->setRouteResolver(fn () => $route);
            $this->assertSame(200, app(CheckRole::class)->handle($r, fn () => response('ok'), 'admin')->getStatusCode());
        }
        $facility = FasilitasUmumBooking::create([]);
        $this->denied(fn () => app(RequestController::class)->updateStatus($this->req(['status' => 'arrived']), 'fasilitas', $facility->id));
        DB::table('mobils')->where('id', 1)->update(['region_id' => 5]);
        $car = MobilBooking::create([]);
        $this->denied(fn () => app(RequestController::class)->updateStatus($this->req(['status' => 'arrived']), 'mobil', $car->id), 404);
    }

    public function test_driver_from_other_village_wrong_service_or_busy_cannot_be_assigned(): void
    {
        $order = MobilBooking::create([]);
        foreach ([['region_id' => 5], ['is_sewa_mobil' => false], ['tipe' => 'pengurus_gedung'], ['status' => 'Sedang Bertugas']] as $attrs) {
            $driver = Supir::create($attrs);
            $expected = isset($attrs['status']) ? 409 : 422;
            $this->denied(fn () => app(RequestController::class)->updateStatus($this->req(['status' => 'in_delivery', 'assigned_supir_id' => $driver->id]), 'mobil', $order->id), $expected);
            $this->assertSame('confirmed', $order->fresh()->status);
        }
        $this->assertSame(0, DB::table('activity_log')->count());
    }

    public function test_assignment_is_exclusive_and_release_respects_other_bookings(): void
    {
        $driver = Supir::create(['region_id' => 1, 'is_sewa_mobil' => true, 'is_fasilitas_umum' => true, 'tipe' => 'supir', 'status' => 'Tersedia']);
        $a = MobilBooking::create([])->fresh();
        $b = FasilitasUmumBooking::create([])->fresh();
        DB::transaction(function () use ($a, $driver) {
            ServiceAssignment::assign($a, 'mobil', $driver->id);
            $a->save();
        });
        // Even an incorrect manual availability reset must not allow a second booking.
        $driver->update(['status' => 'Tersedia']);
        $this->denied(fn () => DB::transaction(fn () => ServiceAssignment::assign($b, 'fasilitas', $driver->id)), 409);
        $driver->update(['status' => 'Sedang Bertugas']);
        $b->update(['assigned_supir_id' => $driver->id]); // historical conflict
        DB::transaction(fn () => ServiceAssignment::release($a));
        $this->assertSame('Sedang Bertugas', $driver->fresh()->status);
        $b->update(['status' => 'completed']);
        DB::transaction(fn () => ServiceAssignment::release($a));
        $this->assertSame('Tersedia', $driver->fresh()->status);
    }

    public function test_facility_progress_does_not_write_missing_transport_columns_or_move_backwards(): void
    {
        $order = FasilitasUmumBooking::create([]);
        $c = app(RequestController::class);
        $this->assertSame(200, $c->updateStatus($this->req(['status' => 'arrived']), 'fasilitas', $order->id)->getStatusCode());
        $this->assertSame('arrived', $order->fresh()->status);
        $c->updateStatus($this->req(['status' => 'arrived']), 'fasilitas', $order->id);
        $this->assertSame(1, DB::table('activity_log')->count());
        $this->denied(fn () => $c->updateStatus($this->req(['status' => 'being_prepared']), 'fasilitas', $order->id), 409);
        $order->update(['cancellation_status' => 'pending']);
        $this->denied(fn () => $c->updateStatus($this->req(['status' => 'completed']), 'fasilitas', $order->id), 409);
        $this->assertSame('arrived', $order->fresh()->status);
    }

    public function test_otp_requires_current_password_request_and_stops_after_five_attempts(): void
    {
        $c = app(ProfileController::class);
        $this->denied(fn () => $c->resendOtp(), 422);
        $this->denied(fn () => $c->verifyOtp($this->req(['otp' => '1234'])), 422);
        $c->changePassword($this->req(['current_password' => 'lama-password-123', 'new_password' => 'baru-password-456', 'new_password_confirmation' => 'baru-password-456']));
        $otp = Mail::queued(OtpMail::class)->last()->otp;
        $wrong = $otp === '1111' ? '2222' : '1111';
        for ($i = 0; $i < 5; $i++) {
            $this->assertSame(422, $c->verifyOtp($this->req(['otp' => $wrong]))->getStatusCode());
        }
        $this->denied(fn () => $c->verifyOtp($this->req(['otp' => $otp])), 422);
        $this->assertTrue(Hash::check('lama-password-123', $this->user(4)->password));
    }

    public function test_successful_otp_changes_password_revokes_tokens_and_cannot_replay(): void
    {
        $this->user(4)->createToken('test');
        $c = app(ProfileController::class);
        $c->changePassword($this->req(['current_password' => 'lama-password-123', 'new_password' => 'baru-password-456', 'new_password_confirmation' => 'baru-password-456']));
        $otp = Mail::queued(OtpMail::class)->last()->otp;
        $this->assertSame(200, $c->verifyOtp($this->req(['otp' => $otp]))->getStatusCode());
        $this->assertTrue(Hash::check('baru-password-456', $this->user(4)->password));
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
        $this->denied(fn () => $c->verifyOtp($this->req(['otp' => $otp])), 422);
    }

    public function test_region_settings_deny_non_village_roles_without_mutating_region(): void
    {
        foreach ([1, 2, 3] as $id) {
            $this->actingAs($this->user($id));
            $this->denied(fn () => app(RegionSettingController::class)->index());
        }
        $super = $this->user(4);
        $super->role = 'super_admin';
        $super->region_id = null;
        $this->actingAs($super);
        $this->denied(fn () => app(RegionSettingController::class)->index());
        $this->assertSame(7, DB::table('regions')->count());
        $this->assertNull($super->region_id);
    }

    public function test_delivery_switch_cannot_overwrite_bank_fields_and_preserves_other_settings(): void
    {
        DB::table('regions')->where('id', 1)->update(['payment_info' => json_encode(['bank_name' => 'Bank Desa'])]);
        $c = app(RegionSettingController::class);
        try {
            $c->toggleDelivery($this->req(['field' => 'bank_name', 'value' => true]));
            $this->fail('Unknown key accepted');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('field', $e->errors());
        }
        $c->toggleDelivery($this->req(['field' => 'mobil_sewa_delivery_antar_active', 'value' => true]));
        $info = Region::find(1)->payment_info;
        $this->assertSame('Bank Desa', $info['bank_name']);
        $this->assertTrue($info['mobil_delivery_antar_active']);
    }

    public function test_rw_dashboard_counts_descendant_citizens_without_legacy_rt_rw_fields(): void
    {
        DB::table('users')->where('id', 8)->update(['region_id' => 4]);
        foreach ([2 => [1], 3 => [1, 8], 5 => [8]] as $admin => $ids) {
            $this->actingAs($this->user($admin));
            $this->assertSame($ids, RegionalCitizens::query($this->user($admin))->orderBy('id')->pluck('id')->all());
            $r = $this->req();
            $r->setUserResolver(fn () => $this->user($admin));
            $response = app(WilayahAdminApiController::class)->getDashboardStats($r)->getData(true);
            $this->assertSame(count($ids), $response['data']['statistik']['total_warga']);
            $this->assertSame('Desa A', $response['data']['pengurus']['desa']);
            $this->assertNull($response['data']['pengurus']['rt']);
        }
        $this->denied(fn () => RegionalCitizens::query($this->user(1)));
    }

    public function test_facility_staff_can_add_facility_personnel_but_not_grant_transport_assignment(): void
    {
        $staff = $this->staff();
        DB::table('staff_permissions')->where('user_id', $staff->id)->update(['unit_key' => 'fasilitas_umum']);
        $this->actingAs($staff);
        $r = $this->req(['nama' => 'Pengurus Gedung', 'tipe' => 'pengurus_gedung', 'status' => 'Tersedia']);
        $route = clone Route::getRoutes()->getByName('supir.store');
        $route->bind($r);
        $r->setRouteResolver(fn () => $route);
        $this->assertSame(200, app(CheckRole::class)->handle($r, fn () => response('ok'), 'admin')->getStatusCode());
        $c = app(SupirController::class);
        $this->assertSame(200, $c->store($r)->getStatusCode());
        $this->assertDatabaseHas('supirs', ['region_id' => 1, 'is_fasilitas_umum' => true, 'is_sewa_mobil' => false]);
        $this->denied(fn () => $c->store($this->req(['nama' => 'Sopir', 'tipe' => 'supir', 'status' => 'Tersedia', 'is_sewa_mobil' => 1])));
        $this->assertSame(1, Supir::count());
    }

    public function test_personnel_in_use_cannot_be_deleted_or_manually_reset_available(): void
    {
        $driver = Supir::create(['status' => 'Sedang Bertugas', 'is_sewa_mobil' => true]);
        $order = MobilBooking::create(['assigned_supir_id' => $driver->id]);
        $c = app(SupirController::class);
        $this->denied(fn () => $c->update($this->req(['nama' => 'Sopir', 'status' => 'Tersedia']), $driver), 409);
        $this->denied(fn () => $c->destroy($driver), 409);
        $order->update(['status' => 'completed']);
        $this->denied(fn () => $c->destroy($driver), 409);
        $this->assertNotNull($driver->fresh());
    }

    public function test_password_otp_is_bound_to_requesting_account_and_expires(): void
    {
        $c = app(ProfileController::class);
        $c->changePassword($this->req(['current_password' => 'lama-password-123', 'new_password' => 'baru-password-456', 'new_password_confirmation' => 'baru-password-456']));
        $otp = Mail::queued(OtpMail::class)->last()->otp;
        $pending = session('admin_password_change');
        $this->actingAs($this->user(7));
        $this->denied(fn () => $c->verifyOtp($this->req(['otp' => $otp])), 422);
        $this->actingAs($this->user(4));
        session(['admin_password_change' => $pending]);
        $this->travel(6)->minutes();
        $this->denied(fn () => $c->verifyOtp($this->req(['otp' => $otp])),422);
        $this->assertTrue(Hash::check('lama-password-123',$this->user(4)->password));
    }
}
