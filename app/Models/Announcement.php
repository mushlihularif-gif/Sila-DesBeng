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
        'is_active', 'workflow_state', 'is_citizen', 'draft_data', 'revision', 'editor_id', 'edit_token',
        'edit_expires_at', 'submitted_at', 'handled_at', 'rejected_at', 'published_at', 'scheduled_at',
        'ends_at', 'reminded_at', 'cleanup_notified_at', 'rejection_reason', 'merged_into_id', 'contribution_to', 'publisher_id', 'views_count', 'image_disk',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'is_active' => 'boolean',
        'is_citizen' => 'boolean', 'draft_data' => 'array', 'revision' => 'integer',
        'edit_expires_at' => 'datetime', 'submitted_at' => 'datetime', 'handled_at' => 'datetime',
        'rejected_at' => 'datetime', 'published_at' => 'datetime', 'scheduled_at' => 'datetime',
        'ends_at' => 'datetime', 'reminded_at' => 'datetime', 'cleanup_notified_at' => 'datetime',
    ];

    protected $hidden = ['edit_token', 'draft_data'];

    public function scopeVisibleTo($query, ?User $user = null)
    {
        return app(\App\Services\ContentAccess::class)->visible($query, $user);
    }

    public function mediaUrl(?int $imageId = null): string
    {
        return route('content.media', ['announcement' => $this->id, 'image' => $imageId ?? 0]);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->image_path ? $this->mediaUrl() : ($this->images->first()?->url);
    }

    public function getMediaUrlsAttribute(): array
    {
        return array_values(array_filter([$this->image_path ? $this->mediaUrl() : null, ...$this->images->map(fn ($i) => $i->url)->all()]));
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'publisher_id');
    }

    

    public function targetRegion()
    {
        return $this->belongsTo(Region::class, 'target_audience_id');
    }

    public function images()
    {
        return $this->hasMany(AnnouncementImage::class)->where('is_staged', false);
    }

    public function allImages()
    {
        return $this->hasMany(AnnouncementImage::class);
    }

    public function getCoverImageAttribute()
    {
        if ($this->image_path) {
            return $this->image_path;
        }
        $firstImage = $this->images()->first();
        if ($firstImage) {
            return $firstImage->image_path;
        }
        return null;
    }
}

