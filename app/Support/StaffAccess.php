<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

class StaffAccess
{
    public const UNITS = [
        'gas' => 'Gas Daerah',
        'sewa_alat' => 'Sewa Alat',
        'sewa_mobil' => 'Sewa Transportasi',
        'fasilitas_umum' => 'Fasilitas Umum',
        'kabar_informasi' => 'Kabar & Informasi Daerah',
    ];

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
            'admin.profile.change-password', 'admin.profile.verify-otp', 'admin.profile.resend-otp',
            'auth.logout'], true)
            || str_starts_with($name, 'notification-settings.')
            || str_starts_with($name, 'notification-link.')) {
            return true;
        }
        if ($request->is('api/*')) {
            if ($request->is('api/user', 'api/logout', 'api/fcm-token', 'api/profile/update', 'api/profile/password',
                'api/notification-settings', 'api/notification-settings/*', 'api/notification-destination/*')) {
                return true;
            }
            if ($request->is('api/content', 'api/content/*')) {
                return $user->hasUnitPermission('kabar_informasi');
            }
            return false;
        }
        if (str_starts_with($name, 'admin.announcements.') || str_starts_with($name, 'content.')) {
            return $user->hasUnitPermission('kabar_informasi');
        }
        if (str_starts_with($name, 'supir.')) return $user->hasUnitPermission('sewa_mobil') || $user->hasUnitPermission('fasilitas_umum');
        foreach (['admin.unit.penjualan_gas.' => 'gas', 'admin.gas.kk.' => 'gas', 'admin.unit.penyewaan.' => 'sewa_alat',
            'admin.unit.mobil.' => 'sewa_mobil', 'admin.unit.fasilitas_umum.' => 'fasilitas_umum',
            'admin.unit.kendaraan_operasional.' => 'fasilitas_umum',
            'receipt.gas.' => 'gas', 'receipt.rental.' => 'sewa_alat',
            'receipt.mobil.' => 'sewa_mobil', 'receipt.fasilitas.' => 'fasilitas_umum'] as $prefix => $unit) {
            if (str_starts_with($name, $prefix)) {
                return $user->hasUnitPermission($unit);
            }
        }
        if ($name === 'admin.categories.store') {
            $unitByType = ['barang' => 'sewa_alat', 'mobil' => 'sewa_mobil', 'gas' => 'gas', 'fasilitas' => 'fasilitas_umum'];
            $requestedType = (string) $request->input('type', '');
            return isset($unitByType[$requestedType]) && $user->hasUnitPermission($unitByType[$requestedType]);
        }
        if (str_starts_with($name, 'admin.unit.chat.')) {
            $unit = ['gas' => 'gas', 'penyewaan' => 'sewa_alat', 'mobil' => 'sewa_mobil', 'fasilitas_umum' => 'fasilitas_umum'][$request->route('service')] ?? '';

            return $user->hasUnitPermission($unit);
        }

        // These controllers additionally check the order type, ownership and granted unit.
        return in_array($name, ['admin.aktivitas.update-status', 'admin.aktivitas.delivery-proof', 'admin.aktivitas.cancellation'], true)
            || str_starts_with($name, 'admin.aktivitas.permintaan-pengajuan.')
            || str_starts_with($name, 'admin.aktivitas.bukti-transaksi.');
    }
}
