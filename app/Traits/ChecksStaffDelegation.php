<?php

namespace App\Traits;

use App\Models\StaffPermission;
use Illuminate\Http\Request;

trait ChecksStaffDelegation
{
    /**
     * Memeriksa apakah layanan ini dikelola oleh staf dan menampilkan splash screen jika perlu.
     *
     * @param Request $request
     * @param string $unitKey
     * @param string $serviceName
     * @return \Illuminate\Http\Response|null
     */
    protected function checkDelegation(Request $request, string $unitKey, string $serviceName)
    {
        // Hanya cek jika yang login adalah supervisor (bukan staf)
        if (!in_array(auth()->user()->role, ['super_admin', 'admin_kecamatan', 'admin_desa'])) {
            return null;
        }

        // Cek apakah ada bypass
        if ($request->has('bypass_delegation')) {
            return null;
        }

        // Cari apakah ada staf yang mengelola unit ini di wilayah ini
        $staffPermission = StaffPermission::where('unit_key', $unitKey)
            ->whereHas('user', function ($query) {
                $query->where('region_id', auth()->user()->region_id)
                      ->where('role', 'staff');
            })
            ->with('user')
            ->first();

        if ($staffPermission && $staffPermission->user) {
            // Tampilkan splash screen
            return response()->view('admin.components.delegation-splash', [
                'staff' => $staffPermission->user,
                'serviceName' => $serviceName,
                'bypassUrl' => $request->fullUrlWithQuery(['bypass_delegation' => 1])
            ]);
        }

        return null;
    }
}
