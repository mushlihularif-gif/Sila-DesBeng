<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionService extends Model
{
    protected $fillable = ['region_id', 'service_id', 'is_active', 'is_exclusive'];

    /**
     * Aktifkan layanan untuk wilayah tertentu dengan default Khusus Warga Desa (is_exclusive = true)
     * tanpa menimpa pengaturan is_exclusive yang sudah pernah diatur admin wilayah.
     */
    public static function aktifkanUntukWilayah(int $regionId, int $serviceId, bool $defaultExclusive = true): void
    {
        $existing = static::where('region_id', $regionId)
            ->where('service_id', $serviceId)
            ->first();

        if ($existing) {
            if (! $existing->is_active) {
                $existing->update(['is_active' => true]);
            }
            return;
        }

        $attrs = [
            'region_id' => $regionId,
            'service_id' => $serviceId,
            'is_active' => true,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('region_services', 'is_exclusive')) {
            $attrs['is_exclusive'] = $defaultExclusive;
        }

        static::create($attrs);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
