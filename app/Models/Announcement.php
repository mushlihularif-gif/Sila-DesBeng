<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'admin_id',
        'laporan_id',
        'post_category',
        'target_audience_type',
        'target_audience_id',
        'title',
        'description',
        'type',
        'event_date',
        'location',
        'image_path',
        'is_active'
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function laporan()
    {
        return $this->belongsTo(Laporan::class);
    }

    public function targetRegion()
    {
        return $this->belongsTo(Region::class, 'target_audience_id');
    }

    public function images()
    {
        return $this->hasMany(AnnouncementImage::class);
    }
}
