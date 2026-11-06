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
        return null; // Unit layanan dikelola langsung oleh admin desa.
    }
}
