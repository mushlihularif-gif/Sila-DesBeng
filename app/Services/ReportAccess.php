<?php

namespace App\Services;

use App\Models\Laporan;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ReportAccess
{
    public function administrativeQuery(?User $user): Builder
    {
        $query = Laporan::query();
        if (!$user || !$user->isAdmin()) {
            return $query->whereRaw('1 = 0');
        }
        if ($user->role === 'super_admin') {
            return $query;
        }
        if (!$user->region_id || ($user->role === 'staff' && !$user->hasUnitPermission('pelaporan_warga'))) {
            return $query->whereRaw('1 = 0');
        }
        $regions = array_merge([$user->region_id], Region::getDescendantIds($user->region_id));
        $query->whereIn('region_id', $regions);
        if (in_array($user->role, ['admin_rt', 'admin_rw'], true)) {
            // Akun RT/RW lama ditempatkan pada desa yang sama; nomor RW/RT tetap wajib.
            if (!$user->rw || ($user->role === 'admin_rt' && !$user->rt)) {
                return $query->whereRaw('1 = 0');
            }
            $query->where('rw', $user->rw);
            if ($user->role === 'admin_rt') {
                $query->where('rt', $user->rt);
            }
        }
        return $query;
    }

    public function canView(Laporan $report, ?User $user): bool
    {
        return $user && ((int) $report->user_id === (int) $user->id
            || $this->administrativeQuery($user)->whereKey($report->id)->exists());
    }
}
