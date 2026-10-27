<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    public const RETIRED_SLUGS = ['ambulans', 'layanan-ambulans'];

    public function scopeAvailable($query)
    {
        return $query->whereNotIn('slug', self::RETIRED_SLUGS);
    }

    protected $fillable = ['name', 'slug', 'icon'];

    public function regions()
    {
        return $this->belongsToMany(Region::class, 'region_services')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }
}
