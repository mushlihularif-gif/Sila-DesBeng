<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class UnitChatSession extends Model
{
    use HasFactory;

    protected $table = 'unit_chat_sessions';

    protected $fillable = [
        'service_type',
        'region_id',
        'user_id',
        'session_token',
        'user_name',
        'status',
        'item_reference',
        'last_message',
        'last_message_at',
        'unread_admin_count',
        'unread_user_count',
        'user_cleared_before_id',
        'admin_cleared_before_id',
        'cleared_by_admins',
    ];

    protected $hidden = [
        'admin_cleared_before_id',
        'cleared_by_admins',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'user_cleared_before_id' => 'integer',
        'admin_cleared_before_id' => 'integer',
        'cleared_by_admins' => 'array',
    ];

    public function getItemLabelAttribute(): string
    {
        $reference = trim((string) $this->item_reference);
        if (str_starts_with($reference, '{') || str_starts_with($reference, '[')) {
            $data = json_decode($reference, true);
            return is_array($data) && is_string($data['name'] ?? null) ? $data['name'] : 'Produk layanan';
        }
        return $reference;
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(UnitChatMessage::class, 'session_id')->orderBy('created_at', 'asc')->orderBy('id', 'asc');
    }

    public function scopeForService($query, $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }

    public function scopeVisibleForAdmin($query, string $serviceType, ?int $regionId)
    {
        return $query->where('region_id', $regionId)
            ->where('service_type', $serviceType)
            ->where(function ($q) {
                $q->whereNotNull('last_message_at')
                    ->orWhere('status', 'escalated')
                    ->orWhere('unread_admin_count', '>', 0)
                    ->orWhereHas('messages', fn ($m) => $m->whereIn('sender_type', ['user', 'admin']));
            })
            ->orderByRaw('COALESCE(last_message_at, updated_at) DESC');
    }

    public function latestMessage()
    {
        return $this->hasOne(UnitChatMessage::class, 'session_id')->latestOfMany('id');
    }

    public function displayUserName(): string
    {
        $fromUser = trim((string) ($this->user?->name ?? ''));
        if ($fromUser !== '') {
            return $fromUser;
        }

        $stored = trim((string) ($this->user_name ?? ''));

        return $stored !== '' ? $stored : 'Warga';
    }

    public function userClearedBeforeId(): int
    {
        return (int) ($this->user_cleared_before_id ?? 0);
    }

    public function adminClearedBeforeId(int $adminUserId): int
    {
        $map = is_array($this->cleared_by_admins) ? $this->cleared_by_admins : [];
        if (isset($map[(string) $adminUserId])) {
            return (int) $map[(string) $adminUserId];
        }

        return (int) ($this->admin_cleared_before_id ?? 0);
    }

    /**
     * Bersihkan tampilan chat khusus sisi warga (riwayat di Admin Desa & Staf tetap utuh).
     */
    public function clearForUser(): int
    {
        $maxId = (int) ($this->messages()->max('id') ?? 0);
        $updates = ['unread_user_count' => 0];
        if (Schema::hasColumn($this->getTable(), 'user_cleared_before_id')) {
            $updates['user_cleared_before_id'] = $maxId;
        }
        $this->update($updates);

        return $maxId;
    }

    /**
     * Bersihkan tampilan chat khusus akun pengelola/staf ini (riwayat di warga & pengelola lain tetap utuh).
     */
    public function clearForAdmin(int $adminUserId): int
    {
        $maxId = (int) ($this->messages()->max('id') ?? 0);
        $updates = ['unread_admin_count' => 0];
        if (Schema::hasColumn($this->getTable(), 'cleared_by_admins')) {
            $map = is_array($this->cleared_by_admins) ? $this->cleared_by_admins : [];
            $map[(string) $adminUserId] = $maxId;
            $updates['cleared_by_admins'] = $map;
        } elseif (Schema::hasColumn($this->getTable(), 'admin_cleared_before_id')) {
            $updates['admin_cleared_before_id'] = $maxId;
        }
        $this->update($updates);

        return $maxId;
    }
}
