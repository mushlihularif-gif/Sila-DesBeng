<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitChatMessage extends Model
{
    use HasFactory;

    protected $table = 'unit_chat_messages';

    protected $fillable = [
        'session_id',
        'sender_type',
        'sender_id',
        'message',
        'attachment_url',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function session()
    {
        return $this->belongsTo(UnitChatSession::class, 'session_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
