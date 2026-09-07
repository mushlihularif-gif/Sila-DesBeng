<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'parent_id', 'profile_text', 'contact_phone', 'contact_email', 'payment_info', 'settings'
    ];

    protected $casts = [
        'payment_info' => 'array',
        'settings' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Region::class, 'parent_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'region_services')
                    ->withPivot('is_active', 'is_exclusive')
                    ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public static function getDescendantIds($regionId)
    {
        $ids = [];
        $children = self::where('parent_id', $regionId)->pluck('id')->toArray();
        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, self::getDescendantIds($childId));
        }
        return $ids;
    }

    public static function getAncestorIds($regionId)
    {
        $ids = [];
        $region = self::find($regionId);
        if ($region && $region->parent_id) {
            $ids[] = $region->parent_id;
            $ids = array_merge($ids, self::getAncestorIds($region->parent_id));
        }
        return $ids;
    }

    /**
     * Garis wilayah seorang warga: wilayahnya sendiri + seluruh induk di atasnya.
     *
     * Warga Desa Pematang Duku Timur bernaung di Kecamatan Bengkalis dan
     * Kabupaten Bengkalis, jadi ketiganya ada di garisnya. Desa lain dan
     * kecamatan lain TIDAK. Ini kebalikan getDescendantIds() yang dipakai sisi
     * admin (melihat ke bawah untuk mengawasi).
     *
     * @return array<int> selalu memuat $regionId sendiri
     */
    public static function garisLayananUntukWarga(?int $regionId): array
    {
        if (! $regionId) {
            return [];
        }

        return array_values(array_unique(
            array_merge([$regionId], self::getAncestorIds($regionId))
        ));
    }

    /**
     * Wilayah mana saja yang layanannya boleh DILIHAT warga ini.
     *
     * Batasan wilayah TIDAK berlaku otomatis. Tiap wilayah punya sakelar
     * "Eksklusif Warga Lokal" per layanan (region_services.is_exclusive):
     *
     *   tidak eksklusif -> terbuka untuk warga mana pun
     *   eksklusif       -> hanya warga wilayah itu dan wilayah di bawahnya
     *
     * Aturan yang sama sudah lebih dulu dipakai middleware CheckRegionService;
     * fungsi ini membawanya ke tingkat DAFTAR supaya warga tidak lagi melihat
     * barang yang nanti ditolak saat dipesan.
     *
     * @return array<int> region_id yang layanannya aktif dan boleh dilihat
     */
    public static function wilayahLayananTerlihat(?int $userRegionId, string $namaLayanan): array
    {
        $layanan = Service::where('name', $namaLayanan)->first();

        if (! $layanan) {
            return [];
        }

        $garis = self::garisLayananUntukWarga($userRegionId);

        return RegionService::where('service_id', $layanan->id)
            ->where('is_active', true)
            ->where(function ($q) use ($garis) {
                $q->where('is_exclusive', false)
                  ->orWhereIn('region_id', $garis);
            })
            ->pluck('region_id')
            ->all();
    }

    public function getFullPathAttribute()
    {
        $path = $this->name;
        $parent = $this->parent;
        while ($parent) {
            $path .= ', ' . $parent->name;
            $parent = $parent->parent;
        }
        return $path;
    }

    public function getPublicNameAttribute()
    {
        $current = $this;
        // Skip microscopic regions to protect privacy for public view
        while ($current && in_array(strtolower($current->type), ['rt', 'rw', 'dusun', 'lingkungan'])) {
            $current = $current->parent;
        }
        
        if ($current) {
            return $current->name;
        }
        
        return $this->name;
    }
}

