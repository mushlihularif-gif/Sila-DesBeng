<?php

namespace App\Services;

use App\Models\FasilitasUmumBooking;
use App\Models\MobilBooking;
use App\Models\Supir;
use Illuminate\Database\Eloquent\Model;

class ServiceAssignment
{
    public static function eligible(string $type, int $region)
    {
        return Supir::where('region_id', $region)->where($type === 'mobil' ? 'is_sewa_mobil' : 'is_fasilitas_umum', true)
            ->when($type === 'mobil', fn ($q) => $q->where('tipe', 'supir'));
    }

    public static function hasOtherBooking(int $driver, ?Model $except = null): bool
    {
        foreach ([MobilBooking::class, FasilitasUmumBooking::class] as $model) {
            $query = $model::withoutGlobalScopes()->where('assigned_supir_id', $driver)
                ->whereNotIn('status', ['completed', 'returned', 'resolved', 'cancelled', 'rejected']);
            if ($except instanceof $model) {
                $query->where('id', '!=', $except->id);
            }
            if ($query->exists()) {
                return true;
            }
        }

        return false;
    }

    // Caller holds the booking lock and transaction. The personnel row serializes
    // assignments even when two different orders are handled simultaneously.
    public static function assign(Model $order, string $type, int $driver): void
    {
        $region = (int) ($type === 'mobil' ? $order->mobil?->region_id : $order->fasilitas?->region_id);
        $person = self::eligible($type, $region)->lockForUpdate()->find($driver);
        abort_unless($person, 422, 'Petugas harus berasal dari wilayah dan layanan pesanan ini.');
        abort_if(self::hasOtherBooking($driver, $order), 409, 'Petugas sedang menangani pesanan lain. Pilih petugas lain.');
        abort_unless($person->status === 'Tersedia' || ((int) $order->assigned_supir_id === $driver && $person->status === 'Sedang Bertugas'),
            409, 'Petugas sedang bertugas atau tidak aktif.');
        if ($order->assigned_supir_id && (int) $order->assigned_supir_id !== $driver) {
            self::release($order);
        }
        $person->update(['status' => 'Sedang Bertugas']);
        $order->assigned_supir_id = $driver;
    }

    public static function release(Model $order): void
    {
        if (! $order->assigned_supir_id) {
            return;
        }
        $region = (int) ($order instanceof MobilBooking ? $order->mobil?->region_id : $order->fasilitas?->region_id);
        $person = Supir::where('region_id', $region)->lockForUpdate()->find($order->assigned_supir_id);
        if ($person && $person->status === 'Sedang Bertugas' && ! self::hasOtherBooking($person->id, $order)) {
            $person->update(['status' => 'Tersedia']);
        }
    }
}
