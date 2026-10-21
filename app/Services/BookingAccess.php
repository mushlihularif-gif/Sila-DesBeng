<?php

namespace App\Services;

use App\Models\User;

class BookingAccess
{
    public const TYPES = [
        'rental' => [\App\Models\RentalBooking::class, 'barang', 'sewa_alat'],
        'gas' => [\App\Models\GasOrder::class, 'gas', 'gas'],
        'mobil' => [\App\Models\MobilBooking::class, 'mobil', 'sewa_mobil'],
        'fasilitas' => [\App\Models\FasilitasUmumBooking::class, 'fasilitas', 'fasilitas_umum'],
        'pasar' => [\App\Models\PasarOrder::class, null, 'pasar_daerah'],
    ];

    public function canView($booking, string $type, ?User $viewer): bool
    {
        if (!$viewer) return false;
        if ((int) $booking->user_id === (int) $viewer->id) return true;
        if (!isset(self::TYPES[$type]) || !$viewer->isAdmin() || !$viewer->region_id) return false;
        [, $relation, $permission] = self::TYPES[$type];
        $region = $booking->region_id ?: ($relation ? $booking->$relation?->region_id : null);
        return $region && (int) $region === (int) $viewer->region_id && $viewer->hasUnitPermission($permission);
    }
}
