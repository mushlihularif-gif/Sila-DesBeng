<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

class StaffAccess
{
    public const UNITS = ['gas' => 'Gas Daerah', 'sewa_alat' => 'Sewa Alat', 'sewa_mobil' => 'Sewa Transportasi', 'fasilitas_umum' => 'Fasilitas Umum'];

    public static function allows(User $user, Request $request): bool
    {
        if (! $user->hasSupportedRole()) {
            return false;
        }
        $name = $request->route()?->getName() ?? '';
        if (str_ends_with($name, '.sop.update') || $name === 'admin.unit.penjualan_gas.crisis_mode') {
            return false;
        }
        if (in_array($name, ['admin.dashboard', 'admin.profile', 'admin.profile.update', 'admin.profile.delete-avatar',
            'admin.notifications.index', 'admin.notifications.mark-as-read', 'admin.notifications.mark-all-read',
            'admin.profile.change-password', 'admin.profile.verify-otp', 'admin.profile.resend-otp'], true)) {
            return true;
        }
        foreach (['admin.unit.penjualan_gas.' => 'gas', 'admin.unit.penyewaan.' => 'sewa_alat',
            'admin.unit.mobil.' => 'sewa_mobil', 'supir.' => 'sewa_mobil', 'admin.unit.fasilitas_umum.' => 'fasilitas_umum',
            'admin.unit.kendaraan_operasional.' => 'fasilitas_umum'] as $prefix => $unit) {
            if (str_starts_with($name, $prefix)) {
                return $user->hasUnitPermission($unit);
            }
        }
        if (str_starts_with($name, 'admin.unit.chat.')) {
            $unit = ['gas' => 'gas', 'penyewaan' => 'sewa_alat', 'mobil' => 'sewa_mobil', 'fasilitas_umum' => 'fasilitas_umum'][$request->route('service')] ?? '';

            return $user->hasUnitPermission($unit);
        }

        // These controllers additionally check the order type, ownership and granted unit.
        return str_starts_with($name, 'admin.aktivitas.permintaan-pengajuan.')
            || str_starts_with($name, 'admin.aktivitas.bukti-transaksi.');
    }
}
