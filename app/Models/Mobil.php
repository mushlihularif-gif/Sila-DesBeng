<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mobil extends Model
{
    protected $table = 'mobils';

    protected $fillable = [
        'nama_mobil',
        'deskripsi',
        'harga_sewa',
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
        'satuan',
        'region_id',
        'harga_dalam_desa',
        'batas_km_dalam_desa',
        'harga_luar_desa',
        'batas_km_luar_desa',
        'harga_luar_kota',
        'bbm_ditanggung',
        'opsi_supir',
        'bbm_ditanggung_borongan',
        'opsi_supir_borongan',
        'is_harian_active',
        'is_borongan_active',
        'opsi_lepas_kunci',
        'tipe_tarif_borongan',
        'tarif_borongan_wilayah',
    ];

    protected $casts = [
        'harga_sewa' => 'decimal:2',
        'harga_dalam_desa' => 'decimal:2',
        'harga_luar_desa' => 'decimal:2',
        'harga_luar_kota' => 'decimal:2',
        'is_harian_active' => 'boolean',
        'is_borongan_active' => 'boolean',
        'opsi_lepas_kunci' => 'boolean',
    ];

    public function getTarifBoronganWilayahParsedAttribute()
    {
        if (empty($this->tarif_borongan_wilayah)) {
            return [];
        }
        return is_array($this->tarif_borongan_wilayah)
            ? $this->tarif_borongan_wilayah
            : (json_decode($this->tarif_borongan_wilayah, true) ?? []);
    }

    public function getHargaDalamDesaWilayahAttribute()
    {
        return $this->tarif_borongan_wilayah_parsed['harga_dalam_desa'] ?? null;
    }

    public function getHargaLuarDesaWilayahAttribute()
    {
        return $this->tarif_borongan_wilayah_parsed['harga_luar_desa'] ?? null;
    }

    public function getTipeLuarKecamatanWilayahAttribute()
    {
        return $this->tarif_borongan_wilayah_parsed['tipe_luar_kecamatan'] ?? 'pukul_rata';
    }

    public function getHargaLuarKecamatanWilayahAttribute()
    {
        return $this->tarif_borongan_wilayah_parsed['harga_luar_kecamatan'] ?? null;
    }

    public function getHargaKecamatanKhususAttribute()
    {
        return $this->tarif_borongan_wilayah_parsed['harga_kecamatan_khusus'] ?? [];
    }

    public function getIsPublicServiceAttribute()
    {
        return in_array($this->kategori, ['ambulans', 'kendaraan_operasional']);
    }

    public function getHargaMulaiAttribute()
    {
        if ($this->is_public_service) {
            return 0;
        }

        $prices = [];

        // Tarif Harian
        if ($this->is_harian_active && (float)$this->harga_sewa > 0) {
            $prices[] = (float)$this->harga_sewa;
        }

        // Tarif Borongan
        if ($this->is_borongan_active) {
            if ($this->tipe_tarif_borongan === 'wilayah') {
                $w = $this->tarif_borongan_wilayah_parsed;
                if (!empty($w['harga_dalam_desa']) && (float)$w['harga_dalam_desa'] > 0) {
                    $prices[] = (float)$w['harga_dalam_desa'];
                }
                if (!empty($w['harga_luar_desa']) && (float)$w['harga_luar_desa'] > 0) {
                    $prices[] = (float)$w['harga_luar_desa'];
                }
                if (!empty($w['harga_luar_kecamatan']) && (float)$w['harga_luar_kecamatan'] > 0) {
                    $prices[] = (float)$w['harga_luar_kecamatan'];
                }
                if (!empty($w['harga_kecamatan_khusus']) && is_array($w['harga_kecamatan_khusus'])) {
                    foreach ($w['harga_kecamatan_khusus'] as $khusus) {
                        if ((float)$khusus > 0) {
                            $prices[] = (float)$khusus;
                        }
                    }
                }
            } else {
                if ((float)$this->harga_dalam_desa > 0) {
                    $prices[] = (float)$this->harga_dalam_desa;
                }
                if ((float)$this->harga_luar_desa > 0) {
                    $prices[] = (float)$this->harga_luar_desa;
                }
                if ((float)$this->harga_luar_kota > 0) {
                    $prices[] = (float)$this->harga_luar_kota;
                }
            }
        }

        // Fallback jika flag active belum diset atau prices kosong namun harga_sewa ada
        if (empty($prices) && (float)$this->harga_sewa > 0) {
            $prices[] = (float)$this->harga_sewa;
        }

        return !empty($prices) ? min($prices) : 0;
    }

    public function supirs()
    {
        return $this->belongsToMany(Supir::class, 'mobil_supir', 'mobil_id', 'supir_id');
    }

    public function bookings()
    {
        return $this->hasMany(MobilBooking::class, 'mobil_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function hasStock($quantity)
    {
        return $this->stok >= $quantity;
    }

    public function decreaseStock($quantity)
    {
        if (!$this->hasStock($quantity)) {
            throw new \Exception('Stok tidak mencukupi.');
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