<?php

namespace App\Support;

use App\Models\Region;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/** Public directory status only; never use this status to grant an account access. */
final class VillageParticipation
{
    public static function query(Builder|Relation $query): Builder|Relation
    {
        return $query->withExists([
            'users as has_active_village_admin' => fn (Builder $users) => $users
                ->where('role', 'admin_desa')->where('status', 'aktif'),
            'services as has_active_village_service' => fn (Builder $services) => $services
                ->where('region_services.is_active', true),
        ]);
    }

    public static function joined(Region $village): bool
    {
        return (bool) $village->has_active_village_admin || (bool) $village->has_active_village_service;
    }
}
