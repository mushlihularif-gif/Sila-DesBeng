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
        
        // Dapatkan region_id. Jika admin/super_admin tidak punya region_id, fallback ke Region pertama (Kabupaten)
        $regionId = $currentUser->region_id;
        if (!$regionId && in_array($currentUser->role, ['super_admin', 'admin'])) {
            $region = \App\Models\Region::first();
            if ($region) {
                $regionId = $region->id;
            }
        }

        // Jika super_admin dan TIDAK strict, mungkin bisa melihat semua data (opsional, tapi karena user minta privasi ketat, kita berlakukan ketat jika strict = true)
        if ($currentUser->role === 'super_admin' && !$strict) {
            return $query;
        }

        if ($regionId) {
            if ($strict) {
                // Mode ketat: Hanya melihat data dari region-nya sendiri (Privasi Keuangan)
                return $query->whereHas($userRelationName, function($q) use ($regionId) {
                    $q->where('region_id', $regionId);
                });
            }

            if (in_array($currentUser->role, ['admin_kecamatan', 'admin_desa', 'lurah', 'admin_rw', 'admin_rt', 'admin', 'super_admin'])) {
                $allowedRegionIds = \App\Models\Region::getDescendantIds($regionId);
                $allowedRegionIds[] = $regionId;

                return $query->whereHas($userRelationName, function($q) use ($allowedRegionIds) {
                    $q->whereIn('region_id', $allowedRegionIds);
                });
            }
        }
        
        return $query;
    }

    /**
     * Mendapatkan daftar nama layanan yang aktif untuk region admin saat ini.
     * Jika super_admin, kembalikan semua layanan.
     * 
     * @return array
     */
    protected function getActivatedServices()
    {
        $currentUser = auth()->user();

        if ($currentUser && in_array($currentUser->role, ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa'])) {
            $region = \App\Models\Region::with('services')->find($currentUser->region_id);
            if (!$region && in_array($currentUser->role, ['super_admin', 'admin'])) {
                $region = \App\Models\Region::first(); // Fallback untuk admin kabupaten & super_admin
            }
            if ($region) {
                return $region->services->pluck('name')->toArray();
            }
        }

        return [];
    }
}
