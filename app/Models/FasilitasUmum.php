<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FasilitasUmum extends Model
{
    public const BIAYA_GRATIS = 'gratis';
    public const BIAYA_BERBAYAR_PRIBADI = 'berbayar';
    public const BIAYA_BERBAYAR_SEMUA = 'berbayar_semua';

    public static function allowedPricingSchemes(): array
    {
        return [
            self::BIAYA_GRATIS,
            self::BIAYA_BERBAYAR_PRIBADI,
            self::BIAYA_BERBAYAR_SEMUA,
        ];
    }

    public function effectivePricingScheme(): string
    {
        $scheme = (string) ($this->status_biaya ?? self::BIAYA_GRATIS);
        $hasPrice = (float) ($this->harga_sewa ?? 0) > 0;

        if ($scheme === self::BIAYA_BERBAYAR_SEMUA && $hasPrice) {
            return self::BIAYA_BERBAYAR_SEMUA;
        }

        if ($scheme === self::BIAYA_BERBAYAR_PRIBADI && $hasPrice) {
            return self::BIAYA_BERBAYAR_PRIBADI;
        }

        return self::BIAYA_GRATIS;
    }

    public function isPaidScheme(): bool
    {
        return $this->effectivePricingScheme() !== self::BIAYA_GRATIS;
    }

    public function isPaidForEvent(string $jenisAcara): bool
    {
        $normalized = $jenisAcara === 'pribadi' ? 'komersial' : $jenisAcara;
        $scheme = $this->effectivePricingScheme();

        if ($scheme === self::BIAYA_BERBAYAR_SEMUA) {
            return true;
        }

        if ($scheme === self::BIAYA_BERBAYAR_PRIBADI) {
            return $normalized === 'komersial';
        }

        return false;
    }

    public function pricingSchemeLabel(): string
    {
        return match ($this->effectivePricingScheme()) {
            self::BIAYA_BERBAYAR_SEMUA => 'Semua Acara Berbayar (Sosial & Pribadi)',
            self::BIAYA_BERBAYAR_PRIBADI => 'Sosial Gratis, Komersial / Pribadi Berbayar',
            default => 'Gratis Sepenuhnya (Sosial & Pribadi Gratis)',
        };
    }

    public function scopeInCatalog($query)
    {
        return $query->where('nama_fasilitas', 'not like', '%ambulan%')
            ->where(function($q) {
                $q->whereNull('kategori')
                  ->orWhere(function($sub) {
                      $sub->where('kategori', 'not like', '%kendaraan%')
                          ->where('kategori', 'not like', '%ambulan%');
                  });
            });
    }

    protected $table = 'fasilitas_umums';

    protected $fillable = [
        'nama_fasilitas',
        'deskripsi',
        'stok',
        'status',
        'kategori',
        'plat_nomor',
        'foto',
        'foto_2',
        'foto_3',
        'lokasi',
        'latitude',
        'longitude',
        'region_id',
        'opsi_supir',
        'bbm_ditanggung',
        'status_biaya',
        'harga_sewa',
    ];

    public function bookings()
    {
        return $this->hasMany(FasilitasUmumBooking::class, 'fasilitas_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function pengurus()
    {
        return $this->belongsToMany(Supir::class, 'fasilitas_pengurus', 'fasilitas_id', 'pengurus_id');
    }

    public function hasStock($quantity)
    {
        return $this->stok >= $quantity;
    }

    public function decreaseStock($quantity)
    {
        if (!$this->hasStock($quantity)) {
            throw new \Exception("Stok tidak mencukupi.");
        }
        $this->stok -= $quantity;
        $this->save();
    }

    public function increaseStock($quantity)
    {
        $this->stok += $quantity;
        $this->save();
    }
}
