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
        'nik_from_ocr' => \App\Casts\ChaCha20Encrypted::class,
        'name_from_ocr' => \App\Casts\ChaCha20Encrypted::class,
    ];

    protected static function boot()
    {
        parent::boot();

        // ✅ BLIND INDEXING: Buat hash dari data sensitif sebelum disimpan
        static::saving(function ($model) {
            // Hashing NIK
            if ($model->isDirty('nik_from_ocr') && !empty($model->nik_from_ocr)) {
                $plainNik = $model->nik_from_ocr;
                if (!str_starts_with($plainNik, '$chacha20$')) {
                    $model->nik_from_ocr_hash = hash_hmac('sha256', $plainNik, config('app.key'));
                }
            }

            // Hashing Name
            if ($model->isDirty('name_from_ocr') && !empty($model->name_from_ocr)) {
                $plainName = $model->name_from_ocr;
                if (!str_starts_with($plainName, '$chacha20$')) {
                    $model->name_from_ocr_hash = hash_hmac('sha256', $plainName, config('app.key'));
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
