<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'subject', 'category', 'description', 'screenshot',
        'priority', 'status', 'admin_response', 'handled_by', 'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getStatusBadgeAttribute()
    {
        return [
            'baru' => 'bg-blue-500/20 text-blue-400 border-blue-400',
            'diproses' => 'bg-yellow-500/20 text-yellow-400 border-yellow-400',
            'selesai' => 'bg-green-500/20 text-green-400 border-green-400',
            'ditutup' => 'bg-gray-500/20 text-gray-400 border-gray-400',
        ][$this->status] ?? 'bg-gray-500/20 text-gray-400 border-gray-400';
    }

    public function getCategoryIconAttribute()
    {
        return [
            'bug' => '🐛', 'fitur' => '✨', 'akun' => '👤',
            'laporan' => '📋', 'lainnya' => '❓',
        ][$this->category] ?? '❓';
    }
}