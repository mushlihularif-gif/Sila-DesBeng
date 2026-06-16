<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FasilitasUmum extends Model
{
    protected $table = 'fasilitas_umums';

    protected $fillable = [
        'nama_fasilitas',
        'deskripsi',
        'stok',
        'status',
        'kategori',
        'foto',
        'foto_2',
        'foto_3',
        'lokasi',
        'region_id'
    ];

    public function bookings()
    {
        return $this->hasMany(FasilitasUmumBooking::class, 'fasilitas_id');
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
