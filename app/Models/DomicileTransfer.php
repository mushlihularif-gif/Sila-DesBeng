<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomicileTransfer extends Model
{
    protected $fillable = [
        'user_id', 'nama', 'nik', 'no_kk', 'desa_asal', 'desa_tujuan',
        'alamat', 'status_pemohon', 'alasan', 'tipe', 'status',
        'admin_notes', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
