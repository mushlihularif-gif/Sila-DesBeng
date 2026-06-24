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
        'foto',
        'foto_2',
        'foto_3',
        'lokasi',
        'satuan',
        'region_id',
        'harga_dalam_desa',
        'batas_km_dalam_desa',
        'harga_luar_desa',
        'batas_km_luar_desa',
        'harga_luar_kota',
        'bbm_ditanggung',
        'opsi_supir'
    ];

    protected $casts = [
        'harga_sewa' => 'decimal:2',
        'harga_dalam_desa' => 'decimal:2',
        'harga_luar_desa' => 'decimal:2',
        'harga_luar_kota' => 'decimal:2',
    ];

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
