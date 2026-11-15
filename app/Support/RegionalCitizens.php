<?php

namespace App\Support;

use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class RegionalCitizens
{
    public static function query(User $admin): Builder
    {
        abort_unless(in_array($admin->role, ['admin_rt', 'admin_rw'], true), 403);
        abort_unless($admin->region_id && (User::REGION_ROLES[$admin->region?->type] ?? null) === $admin->role, 403, 'Wilayah tugas akun belum sesuai.');

        return User::where('role', 'user')->whereIn('region_id', [$admin->region_id, ...Region::getDescendantIds($admin->region_id)]);
    }
}
