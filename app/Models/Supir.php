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
        'status'
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
