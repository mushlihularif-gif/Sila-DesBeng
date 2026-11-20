<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyRoleMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('regions', function (Blueprint $t) { $t->id(); $t->string('type'); });
        Schema::create('users', function (Blueprint $t) {
            $t->id(); $t->string('role')->nullable(); $t->string('status')->default('aktif');
            $t->integer('region_id')->nullable(); $t->integer('created_by')->nullable();
            $t->boolean('staff_enabled')->default(false); $t->string('remember_token')->nullable();
            $t->string('password')->default('synthetic-password-hash');
        });
        Schema::create('sessions', function (Blueprint $t) { $t->id(); $t->integer('user_id'); });
        Schema::create('personal_access_tokens', function (Blueprint $t) { $t->id(); $t->integer('tokenable_id'); $t->string('tokenable_type'); });
        DB::table('regions')->insert([['id' => 1, 'type' => 'kabupaten'], ['id' => 2, 'type' => 'kecamatan'], ['id' => 12, 'type' => 'desa']]);
        // Same assignment shapes as the hosting export; no real credentials or identities.
        foreach ([[5, 'admin', 12, null], [7, 'admin_kecamatan', 2, null], [8, 'admin_kecamatan', 2, null],
            [9, 'admin_desa', 12, null], [10, 'super_admin', null, null], [14, 'staff', null, 10],
            [38, 'staff', 2, 7], [90, 'admin', 1, null], [94, 'super_admin', null, null],
            [100, 'staff', 12, 9], [101, 'user', 12, null]] as [$id, $role, $region, $manager]) {
            DB::table('users')->insert(['id' => $id, 'role' => $role, 'region_id' => $region, 'created_by' => $manager,
                'staff_enabled' => $id === 100, 'remember_token' => 'synthetic-token']);
            DB::table('sessions')->insert(['user_id' => $id]);
            DB::table('personal_access_tokens')->insert(['tokenable_id' => $id, 'tokenable_type' => 'App\\Models\\User']);
        }
    }

    public function test_retirement_preserves_valid_accounts_records_and_audit_and_revokes_old_sessions(): void
    {
        $migration = require database_path('migrations/2026_11_20_000001_retire_legacy_account_roles.php');
        $migration->up();
        foreach ([5, 7, 8, 90] as $id) {
            $this->assertDatabaseHas('users', ['id' => $id, 'role' => 'user', 'status' => 'non_aktif', 'remember_token' => null, 'password' => 'synthetic-password-hash']);
        }
        foreach ([14, 38] as $id) {
            $this->assertDatabaseHas('users', ['id' => $id, 'role' => 'staff', 'status' => 'non_aktif', 'staff_enabled' => false]);
        }
        $this->assertSame(11, DB::table('users')->count());
        $this->assertSame(6, DB::table('retired_account_assignments')->count());
        $this->assertDatabaseHas('retired_account_assignments', ['user_id' => 5, 'original_role' => 'admin', 'original_status' => 'aktif']);
        $this->assertDatabaseHas('users', ['id' => 9, 'role' => 'admin_desa', 'status' => 'aktif', 'region_id' => 12]);
        foreach ([10, 94] as $id) $this->assertDatabaseHas('users', ['id' => $id, 'role' => 'super_admin', 'status' => 'aktif']);
        $this->assertDatabaseHas('users', ['id' => 100, 'role' => 'staff', 'staff_enabled' => true, 'status' => 'aktif']);
        $this->assertEqualsCanonicalizing([9, 10, 94, 100, 101], DB::table('sessions')->pluck('user_id')->all());
        $this->assertEqualsCanonicalizing([9, 10, 94, 100, 101], DB::table('personal_access_tokens')->pluck('tokenable_id')->all());
        $migration->up();
        $migration->down();
        $this->assertSame(6, DB::table('retired_account_assignments')->count());
        $this->assertDatabaseHas('users', ['id' => 5, 'role' => 'user', 'status' => 'non_aktif']);
    }

    public function test_unknown_role_aborts_conversion_without_partial_account_changes(): void
    {
        DB::table('users')->insert(['id' => 999, 'role' => 'unknown-role']);
        try {
            (require database_path('migrations/2026_11_20_000001_retire_legacy_account_roles.php'))->up();
            $this->fail('Unknown assignment should require review.');
        } catch (\RuntimeException $e) {
            $this->assertDatabaseHas('users', ['id' => 5, 'role' => 'admin', 'status' => 'aktif']);
            $this->assertSame(0, DB::table('retired_account_assignments')->count());
            $this->assertSame(11, DB::table('sessions')->count());
        }
    }
}
