<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'icon'];

    public function regions()
    {
        return $this->belongsToMany(Region::class, 'region_services')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }
}
