<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Titik layanan milik wilayah — gudang, kantor desa, pangkalan gas, balai.
 *
 * Dipakai BERSAMA seluruh unit (gas, sewa alat, mobil, fasilitas umum, pasar),
 * karena satu gudang tetap gudang yang sama apa pun yang disimpan di dalamnya.
 */
class LokasiLayanan extends Model
{
    protected $table = 'lokasi_layanan';

    protected $fillable = [
        'region_id', 'nama', 'alamat', 'latitude', 'longitude', 'catatan', 'is_aktif',
    ];

    protected $casts = [
        'latitude'  => 'float',
        'longitude' => 'float',
        'is_aktif'  => 'boolean',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /** Lokasi milik satu wilayah saja. */
    public function scopeMilikWilayah($query, ?int $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    /** True kalau titik petanya sudah ditentukan. */
    public function punyaTitik(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Daftar untuk dropdown "Lokasi Tersimpan" di formulir unit.
     *
     * Disaring per wilayah. Query lama di tiap unit controller memakai
     * Gas::select('lokasi')->distinct() TANPA syarat wilayah, sehingga admin
     * satu desa ikut melihat nama lokasi milik desa lain.
     */
    public static function untukWilayah(?int $regionId)
    {
        if (! $regionId) {
            return collect();
        }

        return self::milikWilayah($regionId)->aktif()->orderBy('nama')->get();
    }
}
