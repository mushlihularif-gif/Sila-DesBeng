<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep the original assignment, without copying passwords or identity data.
        if (! Schema::hasTable('retired_account_assignments')) {
            Schema::create('retired_account_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->string('original_role')->nullable();
                $table->string('original_status')->nullable();
                $table->unsignedBigInteger('original_region_id')->nullable();
                $table->unsignedBigInteger('original_created_by')->nullable();
                $table->boolean('original_staff_enabled')->default(false);
                $table->string('reason');
                $table->timestamp('retired_at');
            });
        }

        DB::transaction(function () {
            $users = DB::table('users')->select('id', 'role', 'status', 'region_id', 'created_by', 'staff_enabled')->lockForUpdate()->get()->keyBy('id');
            $regions = DB::table('regions')->select('id', 'type')->get()->keyBy('id');
            $roles = ['user', 'admin_desa', 'admin_rw', 'admin_rt', 'staff', 'super_admin'];
            foreach ($users as $user) {
                $legacy = in_array($user->role, ['admin', 'admin_kecamatan', null, ''], true);
                if (! $legacy && ! in_array($user->role, $roles, true)) {
                    throw new RuntimeException('Peran tidak dikenal pada akun #'.$user->id.'. Tinjau penugasan sebelum migrasi.');
                }
                $manager = $users->get($user->created_by);
                $region = $regions->get($user->region_id);
                $invalidStaff = $user->role === 'staff' && (! $region
                    || ! in_array($region->type, ['desa', 'kelurahan'], true)
                    || ! $manager || $manager->role !== 'admin_desa'
                    || (int) $manager->region_id !== (int) $user->region_id);
                if (! $legacy && ! $invalidStaff) {
                    continue;
                }
                DB::table('retired_account_assignments')->insertOrIgnore([
                    'user_id' => $user->id, 'original_role' => $user->role,
                    'original_status' => $user->status, 'original_region_id' => $user->region_id,
                    'original_created_by' => $user->created_by,
                    'original_staff_enabled' => $user->staff_enabled,
                    'reason' => $legacy ? 'unsupported_role' : 'invalid_staff_assignment',
                    'retired_at' => now(),
                ]);
                DB::table('users')->where('id', $user->id)->update([
                    'role' => $legacy ? 'user' : 'staff', 'status' => 'non_aktif',
                    'staff_enabled' => false, 'remember_token' => null,
                ]);
                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')->where('user_id', $user->id)->delete();
                }
                if (Schema::hasTable('personal_access_tokens')) {
                    DB::table('personal_access_tokens')->whereIn('tokenable_type', ['App\\Models\\User', 'user'])
                        ->where('tokenable_id', $user->id)->delete();
                }
            }
        });

        // DDL deliberately follows the committed data conversion (MySQL implicit commit).
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin_desa','user','admin_rt','admin_rw','staff') NOT NULL DEFAULT 'user'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin','admin_kecamatan','admin_desa','admin','user','admin_rt','admin_rw','staff') NULL DEFAULT 'user'");
        }
        // Rollback must not silently restore retired privileges or erase the audit trail.
    }
};
