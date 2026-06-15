<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Laporan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama',
        'kategori',
        'lokasi',
        'rw',
        'rt',
        'rw_number',
        'rt_number',
        'deskripsi',
        'bukti',
        'status',
        'escalation_level',
        'rt_handler_id',
        'rw_handler_id',
        'catatan_rt',
        'catatan_rw',
        'escalated_to_rw_at',
        'catatan_admin',
        'admin_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'escalated_to_rw_at' => 'datetime',
    ];

    // Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    /**
     * Cek apakah laporan bisa dihapus
     * 
     * Syarat:
     * 1. Status masih "Pending" (belum disentuh RW/Admin)
     * 2. Belum lebih dari 24 jam sejak dibuat
     */
   public function canBeDeletedBy(?int $userId): bool
{
    if (! $userId) {
        return false;
    }

    if ($this->user_id !== $userId) {
        return false;
    }

    if (strtolower($this->status) !== 'pending') {
        return false;
    }

    if ($this->created_at->diffInHours(now()) >= 24) {
        return false;
    }

    return true;
}




}