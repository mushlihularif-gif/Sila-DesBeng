<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Terapkan filter wilayah untuk query admin (jika admin memiliki batasan wilayah).
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $userRelationName Nama relasi ke model User (default: 'user')
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyRegionFilter($query, $userRelationName = 'user')
    {
        $currentUser = auth()->user();
        if ($currentUser && $currentUser->region_id && in_array($currentUser->role, ['admin_kecamatan', 'admin_desa', 'lurah', 'admin_rw', 'admin_rt'])) {
            $allowedRegionIds = \App\Models\Region::getDescendantIds($currentUser->region_id);
            $allowedRegionIds[] = $currentUser->region_id;

            return $query->whereHas($userRelationName, function($q) use ($allowedRegionIds) {
                $q->whereIn('region_id', $allowedRegionIds);
            });
        }
        
        return $query;
    }
}
