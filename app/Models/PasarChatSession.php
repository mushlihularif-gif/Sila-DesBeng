<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasarChatSession extends Model
{
    use HasFactory;

    protected $table = 'pasar_chat_sessions';

    protected $fillable = [
        'region_id',
        'user_id',
        'session_token',
        'user_name',
        'status',
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
        return $this->hasMany(PasarChatMessage::class, 'session_id')->orderBy('created_at', 'asc');
    }
}
