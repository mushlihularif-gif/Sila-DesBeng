<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnouncementImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'announcement_id',
        'image_path',
        'disk', 'contributor_id', 'contributor_name', 'is_staged', 'pending_removal',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }

    public function getUrlAttribute(): string
    {
        return route('content.media', ['announcement' => $this->announcement_id, 'image' => $this->id]);
    }
}
