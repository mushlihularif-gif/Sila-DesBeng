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
                    ->available()
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
    /**
     * Apakah tabel dan pengaturan region_services untuk layanan ini sudah terisi
     * (true di produksi dan di pengujian yang mengatur region_services; false hanya
     * pada fixture pengujian terisolasi yang tidak membuat/mengisi region_services).
     */
    public static function adaPengaturanLayanan(string $namaLayanan): bool
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('regions')
            || ! \Illuminate\Support\Facades\Schema::hasTable('services')
            || ! \Illuminate\Support\Facades\Schema::hasTable('region_services')) {
            return false;
        }

        $slug = \Illuminate\Support\Str::slug($namaLayanan);
        $layananId = Service::where('name', $namaLayanan)
            ->orWhere('slug', $slug)
            ->orWhere('slug', str_replace('peminjaman-', '', $slug))
            ->value('id');

        if (! $layananId) {
            return false;
        }

        return RegionService::where('service_id', $layananId)->exists();
    }

    public static function wilayahLayananTerlihat(?int $userRegionId, string $namaLayanan, bool $isGuest = false): array
    {
        $slug = \Illuminate\Support\Str::slug($namaLayanan);
        $layanan = Service::where('name', $namaLayanan)
            ->orWhere('slug', $slug)
            ->orWhere('slug', str_replace('peminjaman-', '', $slug))
            ->first();

        // Pengunjung tamu (guest belum login) belum jelas domisili desanya, sehingga
        // boleh melihat etalase produk dari seluruh desa yang layanannya aktif (is_active = true).
        // Layanan yang dinonaktifkan (is_active = false) tetap disembunyikan, dan begitu user login
        // aturan Khusus Warga Desa (is_exclusive = true) diterapkan secara ketat.
        if ($isGuest) {
            if (! $layanan) {
                return [];
            }

            return RegionService::where('service_id', $layanan->id)
                ->where('is_active', true)
                ->pluck('region_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        $garis = self::garisLayananUntukWarga($userRegionId);

        $allowed = [];
        if ($layanan) {
            $allowed = RegionService::where('service_id', $layanan->id)
                ->where('is_active', true)
                ->where(function ($q) use ($garis) {
                    $q->where('is_exclusive', false)
                      ->orWhereIn('region_id', $garis);
                })
                ->pluck('region_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            // Wilayah milik warga sendiri hanya diikutkan secara otomatis bila belum
            // punya baris region_services untuk layanan ini (kompatibilitas wilayah lama).
            // Jika admin wilayah menonaktifkan layanan (is_active = false), tetap disembunyikan.
            if (! empty($garis)) {
                $configuredOwnIds = RegionService::where('service_id', $layanan->id)
                    ->whereIn('region_id', $garis)
                    ->pluck('region_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                foreach ($garis as $gId) {
                    $gInt = (int) $gId;
                    if (! in_array($gInt, $configuredOwnIds, true)) {
                        $allowed[] = $gInt;
                    }
                }
            }
        } elseif (! empty($garis)) {
            $allowed = array_map('intval', $garis);
        }

        return array_values(array_unique(array_map('intval', $allowed)));
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

    /**
     * Normalisasi region_id warga/produk (misalnya RT, RW, Dusun) naik ke tingkat
     * desa/kelurahan pemilik layanan agar chat dan layanan selalu terhubung ke
     * Admin Desa & Staf unit yang tepat.
     */
    public static function resolveServiceRegionId(?int $regionId): ?int
    {
        if (! $regionId) {
            return null;
        }

        $current = self::find($regionId);
        $depth = 0;

        while ($current && $current->parent_id && $depth++ < 20
            && in_array(strtolower((string) $current->type), ['rt', 'rw', 'dusun', 'lingkungan'], true)) {
            $parent = self::find($current->parent_id);
            if (! $parent) {
                break;
            }
            $current = $parent;
        }

        return $current ? (int) $current->id : null;
    }

    /**
     * Wilayah desa/kelurahan bawaan ketika konteks wilayah belum dipilih secara eksplisit.
     */
    public static function defaultServiceVillageId(): int
    {
        $village = self::whereIn('type', ['desa', 'kelurahan'])
            ->orderBy('id')
            ->first();

        if ($village) {
            return (int) $village->id;
        }

        $first = self::orderBy('id')->first();

        return $first ? (int) $first->id : 1;
    }
}

