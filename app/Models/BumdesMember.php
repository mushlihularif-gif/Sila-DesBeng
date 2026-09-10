<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BumdesMember extends Model
{
    protected $fillable = ['region_id', 'name', 'position', 'level', 'photo', 'order'];

    protected $casts = [
        'level' => 'integer',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function region()
    {
        return $this->belongsTo(\App\Models\Region::class, 'region_id');
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset('storage/' . $this->photo) : asset('Admin/img/avatars/pria.png');
    }

    public function getLevelLabelAttribute()
    {
        switch ($this->level) {
            case 1:
                return 'Tingkat 1 - Pimpinan Utama';
            case 2:
                return 'Tingkat 2 - Sekretaris / Wakil';
            case 3:
                return 'Tingkat 3 - Kepala Seksi / Unit';
            case 4:
                return 'Tingkat 4 - Staf Pelaksana';
            default:
                return 'Tingkat ' . ($this->level ?? 1);
        }
    }

    public function getLevelShortLabelAttribute()
    {
        switch ($this->level) {
            case 1:
                return 'Pimpinan';
            case 2:
                return 'Sekretaris';
            case 3:
                return 'Kasi / Unit';
            case 4:
                return 'Staf';
            default:
                return 'Tingkat ' . ($this->level ?? 1);
        }
    }
}