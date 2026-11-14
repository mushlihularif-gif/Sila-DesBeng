<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected static function booted(): void
    {
        static::created(fn (self $notification) => app(\App\Services\NotificationDeliveryService::class)->enqueueAdmin($notification));
    }
    protected $fillable = [
        'type',
        'reference_id',
        'region_id',
        'title',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Notifikasi platform untuk super admin; notifikasi wilayah untuk pengelola
     * wilayah tersebut. Staf hanya melihat jenis layanan yang ditugaskan.
     */
    public function scopeUntukPengguna($query, ?User $pengguna)
    {
        if (!$pengguna) return $query->whereRaw('1 = 0');
        if ($pengguna->bolehAksesPlatform()) {
            return $query->whereNull('region_id');
        }

        if ($pengguna->isStaff()) {
            $types = [];
            foreach (['gas'=>['gas_order'], 'sewa_alat'=>['rental_request'], 'sewa_mobil'=>['mobil_order'], 'fasilitas_umum'=>['fasilitas_order']] as $unit=>$list) {
                if ($pengguna->hasUnitPermission($unit)) $types = array_merge($types, $list);
            }
            return $query->where('region_id', $pengguna->region_id)->whereIn('type', $types);
        }
        $regionId = $pengguna->region_id;

        if (! $regionId) {
            return $query->whereRaw('1 = 0');
        }

        $wilayah = Region::getDescendantIds($regionId);
        $wilayah[] = $regionId;

        return $query->where(function ($q) use ($wilayah) {
            $q->whereIn('region_id', $wilayah);
        });
    }
}
