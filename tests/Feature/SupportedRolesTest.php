<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\{RegionManagementController, RegionAdminManagementController};
use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\CheckRole;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Route, Schema};
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SupportedRolesTest extends TestCase
{
    use \Tests\Support\EditorialDatabase { setUp as setupEditorial; }

    protected function setUp(): void
    {
        $this->setupEditorial();
        Schema::table('users', function (Blueprint $t) {
            foreach (['password', 'username', 'uuid', 'phone_hash', 'remember_token'] as $field) $t->string($field)->nullable();
            $t->string('status')->default('aktif');
            $t->string('verification_status')->default('unverified')->change();
        });
        DB::table('users')->update(['password' => Hash::make('testing-password')]);
        DB::table('users')->where('id', 1)->update(['email' => 'warga@example.test']);
    }

    private function denied(callable $action, int $status = 403): void
    {
        try { $action(); $this->fail('Operation unexpectedly allowed'); }
        catch (HttpException $e) { $this->assertSame($status, $e->getStatusCode()); }
    }

    public function test_exact_five_role_permission_matrix_and_old_sessions_fail_closed(): void
    {
        $request = Request::create('/private', 'GET', server: ['HTTP_ACCEPT' => 'application/json']);
        foreach (array_merge(User::ROLES, ['admin', 'admin_kecamatan', 'admin_kabupaten', 'staff', 'warga']) as $role) {
            $user = $this->user(1); $user->role = $role;
            $user->region_id = ['admin_desa' => 1, 'admin_rw' => 2, 'admin_rt' => 3][$role] ?? 3;
            $this->actingAs($user);
            $supported = in_array($role, User::ROLES, true);
            $this->assertSame($supported, $user->hasSupportedRole());
            $this->assertSame($role === 'super_admin', $user->isSuperAdmin());
            $this->assertSame($role === 'super_admin', $user->hasPlatformPermission('platform_keamanan'));
            $this->assertFalse($user->hasPlatformPermission('platform_staf'));
            $this->assertSame(in_array($role, ['super_admin', 'admin_desa'], true), $user->hasUnitPermission('gas'));
            if (!$supported) {
                foreach ([[], ['admin'], ['user']] as $roles) {
                    $this->denied(fn () => app(CheckRole::class)->handle($request, fn () => response('ok'), ...$roles));
                }
                continue;
            }
            $this->assertSame(200, app(CheckRole::class)->handle($request, fn () => response('ok'))->getStatusCode());
            $this->assertSame(in_array($role, ['super_admin', 'admin_desa'], true) ? 200 : 403,
                app(CheckRole::class)->handle($request, fn () => response('ok'), 'admin')->getStatusCode());
        }
    }

    public function test_legacy_roles_cannot_get_mobile_tokens_or_use_existing_api_sessions(): void
    {
        foreach (['admin', 'admin_kecamatan', 'admin_kabupaten', 'staff'] as $role) {
            DB::table('users')->where('id', 1)->update(['role' => $role]);
            $this->denied(fn () => app(AuthController::class)->login(Request::create('/', 'POST', [
                'email' => 'warga@example.test', 'password' => 'testing-password',
            ])));
            \Laravel\Sanctum\Sanctum::actingAs($this->user(1));
            $this->denied(fn () => app(CheckRole::class)->handle(Request::create('/api/private'), fn () => response('ok'), 'sanctum'));
        }
    }

    public function test_village_cannot_view_create_accounts_or_delete_admins_in_other_regions(): void
    {
        $this->actingAs($this->user(4));
        $controller = app(RegionManagementController::class);
        $this->denied(fn () => $controller->index(5), 404);
        $this->denied(fn () => $controller->destroyAdmin(7), 404);
        $this->denied(fn () => $controller->generateAdmin(Request::create('/', 'POST', [
            'name' => 'Pengurus', 'email' => 'new@example.test', 'password' => 'testing-password',
        ]), 6), 404);
        $this->denied(fn () => $controller->store(Request::create('/', 'POST', [
            'name' => 'RW baru', 'type' => 'rw', 'parent_id' => 5,
        ])), 404);
        $this->assertSame(8, DB::table('users')->count());
        $this->assertSame(7, DB::table('regions')->count());
    }

    public function test_village_can_create_local_rw_admin_and_unsupported_region_cannot_get_admin(): void
    {
        $this->actingAs($this->user(4));
        $request = Request::create('/', 'POST', ['name' => 'Pengurus RW', 'email' => 'new@example.test', 'password' => 'testing-password']);
        app(RegionManagementController::class)->generateAdmin($request, 2);
        $this->assertDatabaseHas('users', ['email' => 'new@example.test', 'role' => 'admin_rw', 'region_id' => 2]);
        $super = $this->user(4); $super->role = 'super_admin'; $this->actingAs($super);
        $this->denied(fn () => app(RegionManagementController::class)->store(Request::create('/', 'POST', [
            'name' => 'Kabupaten Contoh', 'type' => 'kabupaten', 'admin_name' => 'Pengurus',
            'admin_email' => 'forbidden@example.test', 'admin_password' => 'testing-password',
        ])), 422);
        $this->assertDatabaseMissing('users', ['email' => 'forbidden@example.test']);
    }

    public function test_unassigned_village_admin_cannot_reach_management_and_staff_routes_are_retired(): void
    {
        $user = $this->user(4); $user->region_id = null; $this->actingAs($user);
        $this->denied(fn () => app(RegionManagementController::class)->index());
        $this->denied(fn () => app(RegionAdminManagementController::class)->index());
        $this->assertEmpty(collect(Route::getRoutes()->getRoutes())->filter(
            fn ($route) => str_starts_with($route->getName() ?? '', 'admin.staff.')
        ));
    }
}
