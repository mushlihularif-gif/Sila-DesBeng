<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supir extends Model
{
    protected $fillable = [
        'region_id',
        'layanan',
        'nama',
        'kontak',
        'status',
        'foto',
        'user_id',
        'is_sewa_mobil',
        'is_fasilitas_umum'
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Hanya untuk Ambulans yang menggunakan tabel pivot mobil_supir
    public function ambulans()
    {
        return $this->belongsToMany(Mobil::class, 'mobil_supir', 'supir_id', 'mobil_id');
    }
}
