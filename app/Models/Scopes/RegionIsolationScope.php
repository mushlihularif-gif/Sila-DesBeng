<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Models\Region;

class RegionIsolationScope implements Scope
{
    protected $userRelationName;

    /**
     * @param string $userRelationName The relation name pointing to the User model.
     */
    public function __construct($userRelationName = 'user')
    {
        $this->userRelationName = $userRelationName;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Jika sedang menjalankan di console (artisan) atau belum login, skip
        if (app()->runningInConsole() || !Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Terapkan isolasi wilayah hanya untuk admin_rt dan admin_rw
        if ($user->region_id && in_array($user->role, ['admin_rt', 'admin_rw'])) {
            $allowedRegionIds = Region::getDescendantIds($user->region_id);
            $allowedRegionIds[] = $user->region_id;

            // Jika relasinya adalah dirinya sendiri (Model User)
            if ($model instanceof \App\Models\User) {
                $builder->whereIn($model->getTable() . '.region_id', $allowedRegionIds);
            } 
            // Jika relasinya via foreign key
            else if ($this->userRelationName) {
                $builder->whereHas($this->userRelationName, function($q) use ($allowedRegionIds) {
                    $q->whereIn('region_id', $allowedRegionIds);
                });
            }
        }
    }
}
