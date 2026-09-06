<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiPenduduk extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_region_id',
        'to_region_id',
        'status',
        'requested_by',
        'reason',
        'rejection_reason',
        'alamat_baru',
        'rt_baru',
        'rw_baru',
        'ktp_image_path',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromRegion()
    {
        return $this->belongsTo(Region::class, 'from_region_id');
    }

    public function toRegion()
    {
        return $this->belongsTo(Region::class, 'to_region_id');
    }
}
