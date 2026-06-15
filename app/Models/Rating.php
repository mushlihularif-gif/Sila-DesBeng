<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'laporan_id',
        'user_id',
        'rating',
        'feedback',
        'is_published'
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function laporan()
    {
        return $this->belongsTo(Laporan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    public function canBeDeleted()
    {
        // Bisa dihapus jika:
        // 1. Status masih Pending
        // 2. Belum lebih dari 1 hari sejak dibuat
        return $this->status === 'Pending' &&
            $this->created_at->diffInHours(now()) < 24;
    }
}
