<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'message',
        'image',
        'type',
        'user_id',
        'admin_id',
        'is_read',
        'read_at',
        'sent_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke user (untuk notifikasi yang terkait user tertentu)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke admin (untuk notifikasi yang dibuat admin)
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id'); // Asumsi admin juga dari model User
    }
}