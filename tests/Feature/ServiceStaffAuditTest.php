<?php
namespace Tests\Feature;

use App\Http\Controllers\Admin\ServiceStaffController;
use App\Http\Middleware\CheckRole;
use App\Models\{AdminNotification, User};
use App\Support\StaffAccess;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Route, Schema};
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Tests\TestCase;

class ServiceStaffAuditTest extends TestCase
{
    use \Tests\Support\EditorialDatabase { setUp as editorialSetUp; }

    protected function setUp(): void
    {
        $this->editorialSetUp();
        Schema::table('users', function (Blueprint $t) {
            foreach (['password', 'username', 'uuid', 'phone_hash', 'remember_token'] as $name) $t->string($name)->nullable();
            $t->string('status')->default('aktif');
            $t->unsignedBigInteger('created_by')->nullable();
            $t->string('verification_status')->default('unverified')->change();
        });
        (require database_path('migrations/2026_11_13_000002_authorize_service_staff.php'))->up();
        Schema::create('staff_permissions', function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->string('unit_key'); $t->timestamps();
        });
        Schema::create('activity_log', function (Blueprint $t) {
            $t->id(); $t->integer('user_id'); $t->string('action'); $t->text('description'); $t->string('ip_address'); $t->timestamps(); $t->softDeletes();
        });
        Schema::create('sessions', function (Blueprint $t) { $t->string('id'); $t->integer('user_id'); });
        Schema::create('admin_notifications', function (Blueprint $t) {
            $t->id(); $t->integer('region_id')->nullable(); $t->string('type');
        });
    }

    private function createStaff(array $overrides = []): User
    {
        $this->actingAs($this->user(4));
        app(ServiceStaffController::class)->store(Request::create('/', 'POST', $overrides + [
            'name'=>'Petugas Alat', 'username'=>'petugas_alat', 'email'=>'petugas@example.test',
            'password'=>'uji-password-123', 'password_confirmation'=>'uji-password-123', 'units'=>['sewa_alat'],
        ]));
        return User::where('username', $overrides['username'] ?? 'petugas_alat')->firstOrFail();
    }

    private function routeRequest(string $name, array $params = []): Request
    {
        $route = clone Route::getRoutes()->getByName($name);
        $request = Request::create(route($name, $params), $route->methods()[0], server: ['HTTP_ACCEPT'=>'application/json']);
        $route->bind($request); $request->setRouteResolver(fn () => $route);
        return $request;
    }

    public function test_staff_is_created_in_managers_village_without_trusting_submitted_role_or_region(): void
    {
        $staff = $this->createStaff(['region_id'=>5, 'role'=>'super_admin', 'staff_enabled'=>false]);
        $this->assertSame('staff', $staff->role);
        $this->assertEquals(1, $staff->region_id);
        $this->assertTrue($staff->hasSupportedRole());
        $this->assertTrue(Hash::check('uji-password-123', $staff->password));
        $this->assertTrue($staff->hasUnitPermission('sewa_alat'));
        $this->assertFalse($staff->hasUnitPermission('gas'));
        $this->assertFalse($staff->hasPlatformPermission('platform_keamanan'));
        $this->assertSame(1, DB::table('activity_log')->count());
    }

    public function test_staff_can_only_reach_assigned_unit_routes_and_cannot_edit_policy_or_accounts(): void
    {
        $staff = $this->createStaff(); $this->actingAs($staff);
        foreach (['admin.dashboard','admin.unit.penyewaan.index','admin.unit.penyewaan.create','admin.notifications.index'] as $name) {
            $this->assertSame(200, app(CheckRole::class)->handle($this->routeRequest($name), fn () => response('ok'), 'admin')->getStatusCode(), $name);
        }
        foreach (['admin.staff.store','admin.unit.penjualan_gas.index','admin.unit.mobil.index','admin.unit.fasilitas_umum.index','admin.system-settings.update','admin.unit.penyewaan.sop.update'] as $name) {
            $request = $this->routeRequest($name);
            $this->assertFalse(StaffAccess::allows($staff, $request), $name);
            try { app(CheckRole::class)->handle($request, fn () => response('ok'), 'admin'); $this->fail($name); }
            catch (HttpExceptionInterface $e) { $this->assertSame(403, $e->getStatusCode()); }
        }
        $this->assertTrue(StaffAccess::allows($staff, $this->routeRequest('admin.unit.chat.messages', ['service'=>'penyewaan','id'=>1])));
        $this->assertFalse(StaffAccess::allows($staff, $this->routeRequest('admin.unit.chat.messages', ['service'=>'gas','id'=>1])));
    }

    public function test_disabling_staff_without_permissions_revokes_sessions_and_legacy_staff_stays_disabled(): void
    {
        $staff = $this->createStaff();
        DB::table('sessions')->insert(['id'=>'staff-session','user_id'=>$staff->id]);
        app(ServiceStaffController::class)->update(Request::create('/', 'PUT', ['name'=>'Petugas','enabled'=>0]), $staff->id);
        $this->assertFalse($staff->fresh()->hasSupportedRole());
        $this->assertSame(0, DB::table('sessions')->count());
        $this->assertSame(0, $staff->staffPermissions()->count());
        DB::table('users')->where('id',$staff->id)->update(['status'=>'aktif']);
        $this->assertFalse($staff->fresh()->hasSupportedRole());
    }

    public function test_foreign_manager_cannot_change_staff_and_manager_reassignment_revokes_access(): void
    {
        $staff = $this->createStaff(); $this->actingAs($this->user(7));
        try {
            app(ServiceStaffController::class)->update(Request::create('/', 'PUT', ['name'=>'Changed','enabled'=>1,'units'=>['gas']]), $staff->id);
            $this->fail('Foreign manager changed staff');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) { $this->assertTrue(true); }
        $this->assertTrue($staff->fresh()->hasUnitPermission('sewa_alat'));
        DB::table('users')->where('id',4)->update(['region_id'=>5]);
        $this->assertFalse($staff->fresh()->hasSupportedRole());
    }

    public function test_staff_notification_scope_excludes_other_services_villages_and_platform(): void
    {
        $staff = $this->createStaff();
        DB::table('admin_notifications')->insert([
            ['id'=>1,'region_id'=>1,'type'=>'rental_request'], ['id'=>2,'region_id'=>5,'type'=>'rental_request'],
            ['id'=>3,'region_id'=>1,'type'=>'gas_order'], ['id'=>4,'region_id'=>null,'type'=>'platform'],
        ]);
        $this->assertSame([1], AdminNotification::untukPengguna($staff)->pluck('id')->all());
        $this->assertSame([1,3], AdminNotification::untukPengguna($this->user(4))->pluck('id')->all());
    }

    public function test_invalid_permissions_and_non_village_roles_cannot_create_staff(): void
    {
        try { $this->createStaff(['units'=>['platform_keamanan']]); $this->fail('Platform permission accepted'); }
        catch (ValidationException $e) { $this->assertArrayHasKey('units.0', $e->errors()); }
        foreach ([1,2,3] as $id) {
            $this->actingAs($this->user($id));
            try { app(ServiceStaffController::class)->store(Request::create('/', 'POST')); $this->fail('Unauthorized creator'); }
            catch (HttpExceptionInterface $e) { $this->assertSame(403,$e->getStatusCode()); }
        }
        $this->assertSame(0, User::where('role','staff')->count());
    }
    public function test_staff_sidebar_shows_only_assigned_service_and_own_account(): void
    {
        $staff = $this->createStaff(); $this->actingAs($staff);
        $html = view('admin.staff.menu')->render();
        $this->assertStringContainsString(route('admin.unit.penyewaan.index'),$html);
        $this->assertStringNotContainsString(route('admin.unit.penjualan_gas.index'),$html);
        $this->assertStringNotContainsString(route('admin.staff.index'),$html);
        $this->assertStringContainsString(route('admin.profile'),$html);
        $this->assertStringNotContainsString('@endif',$html);
    }

}
