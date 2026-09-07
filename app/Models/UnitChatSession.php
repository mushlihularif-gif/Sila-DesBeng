<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

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
        return $this->hasMany(UnitChatMessage::class, 'session_id')->orderBy('created_at', 'asc');
    }

    public function scopeForService($query, $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }
}
