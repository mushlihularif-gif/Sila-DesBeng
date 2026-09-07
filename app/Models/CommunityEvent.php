<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityEvent extends Model
{
    protected $fillable = [
        'user_id', 'judul', 'tipe', 'target_scope', 'rw', 'rt',
        'koordinator', 'jadwal', 'lokasi', 'catatan', 'peralatan',
        'poster_path', 'status', 'jumlah_peserta',
    ];

    protected $casts = [
        'peralatan' => 'array',
    ];

    protected $appends = ['poster_url'];

    public function getPosterUrlAttribute()
    {
        return $this->poster_path ? url('storage/' . $this->poster_path) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function participants()
    {
        return $this->hasMany(EventParticipant::class, 'event_id');
    }
}
