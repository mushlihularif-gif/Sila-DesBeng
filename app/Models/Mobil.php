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
        'bbm_ditanggung_borongan',
        'is_harian_active',
        'is_borongan_active'
    ];

    protected $casts = [
        'harga_sewa' => 'decimal:2',
        'harga_dalam_desa' => 'decimal:2',
        'harga_luar_desa' => 'decimal:2',
        'harga_luar_kota' => 'decimal:2',
        'is_harian_active' => 'boolean',
        'is_borongan_active' => 'boolean',
    ];

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
