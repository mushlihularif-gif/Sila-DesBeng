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
        if (!$currentUser || !$currentUser->isAdmin()) {
            return $query->whereRaw('1 = 0');
        }
        
        $regionId = $currentUser->region_id;

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

                // Pengurus hanya melihat pengguna dalam wilayah tugasnya.
            if (in_array($currentUser->role, ['admin_desa', 'admin_rw', 'admin_rt', 'super_admin', 'staff'])) {
                $allowedRegionIds = \App\Models\Region::getDescendantIds($regionId);
                $allowedRegionIds[] = $regionId;

                return $query->whereHas($userRelationName, function($q) use ($allowedRegionIds) {
                    $q->whereIn('region_id', $allowedRegionIds);
                });
            }
        }
        
        // Akun operasional tanpa penempatan wilayah tidak boleh melihat semua data.
        return $query->whereRaw('1 = 0');
    }

    /**
     * Mendapatkan daftar nama layanan yang aktif untuk region admin saat ini.
     * Super admin mengelola layanan platform; admin desa mengikuti wilayahnya.
     * 
     * @return array
     */
    protected function getActivatedServices()
    {
        $currentUser = auth()->user();

        if (! $currentUser) {
            return [];
        }

        $peranBerwilayah = ['super_admin', 'admin_desa', 'staff'];

        if (! in_array($currentUser->role, $peranBerwilayah)) {
            return [];
        }

        // Super Admin mengelola seluruh layanan di sistem
        if (in_array($currentUser->role, ['super_admin'])) {
            return \App\Models\Service::available()->pluck('name')->toArray();
        }

        $region = \App\Models\Region::with('services')->find($currentUser->region_id);

        if (! $region) {
            return [];
        }

        $services = $region->services;
        if ($currentUser->isStaff()) {
            $units = ['penjualan-gas'=>'gas', 'penyewaan-alat'=>'sewa_alat', 'penyewaan-mobil'=>'sewa_mobil', 'fasilitas-umum'=>'fasilitas_umum'];
            $services = $services->filter(fn ($service) => isset($units[$service->slug]) && $currentUser->hasUnitPermission($units[$service->slug]));
        }
        return $services->pluck('name')->values()->all();
    }
}
