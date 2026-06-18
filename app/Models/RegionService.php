<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionService extends Model
{
    protected $fillable = ['region_id', 'service_id', 'is_active'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
