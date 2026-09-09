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
        'is_fasilitas_umum',
        'tipe',
    ];

    public function scopeHanyaSupir($query)
    {
        return $query->where('tipe', 'supir');
    }

    public function scopeHanyaPengurusGedung($query)
    {
        return $query->where('tipe', 'pengurus_gedung');
    }

    public function isPengurusGedung(): bool
    {
        return $this->tipe === 'pengurus_gedung';
    }

    public function isSupir(): bool
    {
        return $this->tipe === 'supir';
    }

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

    // Khusus untuk Gedung & Ruang Publik yang menggunakan tabel pivot fasilitas_pengurus
    public function fasilitas()
    {
        return $this->belongsToMany(FasilitasUmum::class, 'fasilitas_pengurus', 'pengurus_id', 'fasilitas_id');
    }
}
