<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'parent_id', 'profile_text', 'contact_phone', 'contact_email', 'payment_info'
    ];

    protected $casts = [
        'payment_info' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Region::class, 'parent_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'region_services')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public static function getDescendantIds($regionId)
    {
        $ids = [];
        $children = self::where('parent_id', $regionId)->pluck('id')->toArray();
        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, self::getDescendantIds($childId));
        }
        return $ids;
    }

    public static function getAncestorIds($regionId)
    {
        $ids = [];
        $region = self::find($regionId);
        if ($region && $region->parent_id) {
            $ids[] = $region->parent_id;
            $ids = array_merge($ids, self::getAncestorIds($region->parent_id));
        }
        return $ids;
    }
}
