<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ktp_image_path',
        'face_scan_data',
        'nik_from_ocr',
        'name_from_ocr',
        'address_from_ocr',
        'rt_from_ocr',
        'rw_from_ocr',
        'kecamatan_from_ocr',
        'desa_from_ocr',
        'gender_from_ocr',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'face_scan_data' => 'array',
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
