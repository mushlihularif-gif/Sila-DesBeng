<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Terapkan filter wilayah untuk query admin (jika admin memiliki batasan wilayah).
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $userRelationName Nama relasi ke model User (default: 'user')
     * @param bool $strict Jika true, hanya mengambil data wilayah sendiri (tidak termasuk anak wilayah)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyRegionFilter($query, $userRelationName = 'user', $strict = false)
    {
        $currentUser = auth()->user();
        
        // Jika super_admin, bisa melihat semua data
        if ($currentUser && $currentUser->role === 'super_admin') {
            return $query;
        }

        if ($currentUser && $currentUser->region_id) {
            if ($strict) {
                // Mode ketat: Hanya melihat data dari region-nya sendiri (Privasi Keuangan)
                return $query->whereHas($userRelationName, function($q) use ($currentUser) {
                    $q->where('region_id', $currentUser->region_id);
                });
            }

            if (in_array($currentUser->role, ['admin_kecamatan', 'admin_desa', 'lurah', 'admin_rw', 'admin_rt', 'admin'])) {
                $allowedRegionIds = \App\Models\Region::getDescendantIds($currentUser->region_id);
                $allowedRegionIds[] = $currentUser->region_id;

                return $query->whereHas($userRelationName, function($q) use ($allowedRegionIds) {
                    $q->whereIn('region_id', $allowedRegionIds);
                });
            }
        }
        
        return $query;
    }
}
