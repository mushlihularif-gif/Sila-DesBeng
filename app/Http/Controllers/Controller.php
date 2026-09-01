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

            // 'staff' wajib ada di daftar ini. Tanpa itu query staf lolos tanpa
            // filter sama sekali dan mereka melihat pesanan seluruh wilayah,
            // bukan hanya wilayah tempat mereka ditugaskan.
            if (in_array($currentUser->role, ['admin_kecamatan', 'admin_desa', 'admin_rw', 'admin_rt', 'admin', 'super_admin', 'staff'])) {
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

        if (! $currentUser) {
            return [];
        }

        // 'staff' harus ikut di sini. Sebelumnya perannya tidak terdaftar
        // sehingga daftarnya selalu kosong dan setiap tab layanan disembunyikan
        // dengan pesan "Layanan Belum Di Aktifkan", padahal wilayahnya sudah
        // mengaktifkan layanan itu.
        $peranBerwilayah = ['super_admin', 'admin', 'admin_kecamatan', 'admin_desa', 'staff'];

        if (! in_array($currentUser->role, $peranBerwilayah)) {
            return [];
        }

        $region = \App\Models\Region::with('services')->find($currentUser->region_id);
        if (! $region && in_array($currentUser->role, ['super_admin', 'admin'])) {
            $region = \App\Models\Region::first(); // Fallback untuk admin kabupaten & super_admin
        }

        if (! $region) {
            return [];
        }

        $namaLayanan = $region->services->pluck('name')->toArray();

        if ($currentUser->role !== 'staff') {
            return $namaLayanan;
        }

        // Staf hanya melihat layanan yang memang dipercayakan kepadanya.
        // Kalau tidak dipersempit, staf gas ikut melihat tab sewa alat dan
        // mobil yang bukan urusannya.
        $petaIzin = [
            'gas'            => 'gas',
            'sewa_alat'      => 'alat',
            'sewa_mobil'     => 'mobil',
            'fasilitas_umum' => 'fasilitas',
            'pasar_daerah'   => 'pasar',
            'pelaporan_warga' => 'pelapor',
        ];

        $izinStaf = $currentUser->staffPermissions()->pluck('unit_key')->all();
        $kataKunci = [];
        foreach ($izinStaf as $izin) {
            if (isset($petaIzin[$izin])) {
                $kataKunci[] = $petaIzin[$izin];
            }
        }

        if (! $kataKunci) {
            return [];
        }

        return array_values(array_filter($namaLayanan, function ($nama) use ($kataKunci) {
            foreach ($kataKunci as $kata) {
                if (str_contains(strtolower($nama), $kata)) {
                    return true;
                }
            }

            return false;
        }));
    }
}
