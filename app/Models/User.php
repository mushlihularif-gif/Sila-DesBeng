<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * KEAMANAN: Auto-generate UUID saat membuat record baru.
     * UUID digunakan sebagai public identifier untuk mencegah ID Guessing.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });

        // ✅ BLIND INDEXING: Buat hash dari data sensitif sebelum disimpan
        // Ini memungkinkan pencarian (seperti saat login atau cek data) meskipun data dienkripsi
        static::saving(function ($model) {
            // Hashing Phone
            if ($model->isDirty('phone') && !empty($model->phone)) {
                $plainPhone = $model->phone;
                if (!str_starts_with($plainPhone, '$chacha20$')) {
                    $model->phone_hash = hash_hmac('sha256', $plainPhone, config('app.key'));
                }
            }

            // Hashing NIK
            if ($model->isDirty('nik') && !empty($model->nik)) {
                $plainNik = $model->nik;
                if (!str_starts_with($plainNik, '$chacha20$')) {
                    $model->nik_hash = hash_hmac('sha256', $plainNik, config('app.key'));
                }
            }

            // Hashing Name
            if ($model->isDirty('name') && !empty($model->name)) {
                $plainName = $model->name;
                if (!str_starts_with($plainName, '$chacha20$')) {
                    $model->name_hash = hash_hmac('sha256', $plainName, config('app.key'));
                }
            }
        });

        // ✅ MENCEGAH BUG HANTU DATA: Hapus relasi foto saat User dihapus
        static::deleting(function ($model) {
            if ($model->file) {
                // Hapus file dari penyimpanan server
                if (\Illuminate\Support\Facades\Storage::exists($model->file->path)) {
                    \Illuminate\Support\Facades\Storage::delete($model->file->path);
                }
                // Hapus data dari database (tabel files)
                $model->file()->delete();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nik',
        'username',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'rt',
        'rw',
        'gender',
        'avatar',
        'position',
        'role',
        'region_id',
        'google_id',
        'verification_status',
        'verified_at',
        'ktp_photo_path',
        'face_photo_path',
        'ktp_rejection_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
        'reset_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',    // ✅ TAMBAH INI
            'reset_token_expires_at' => 'datetime', // ✅ TAMBAH INI
            'password' => 'hashed',
            'status' => 'string',
            // Defense in Depth: ChaCha20-Poly1305 Database-Level Encryption (PII)
            'phone' => \App\Casts\ChaCha20Encrypted::class,
            'address' => \App\Casts\ChaCha20Encrypted::class,
            'nik' => \App\Casts\ChaCha20Encrypted::class,
            'name' => \App\Casts\ChaCha20Encrypted::class,
        ];
    }

    /**
     * Polymorphic relation to files (untuk avatar)
     */
    public function file()
    {
        return $this->morphOne(File::class, 'fileable');
    }
    
    // Relasi ke transaksi penyewaan
    public function rentalTransactions()
    {
        return $this->hasMany(RentalBooking::class, 'user_id');
    }

    // Relasi ke transaksi gas
    public function gasTransactions()
    {
        return $this->hasMany(GasOrder::class, 'user_id');
    }

    // ===================================
    // RELASI DARI I_VILAGGE
    // ===================================
    
    public function laporans()
    {
        return $this->hasMany(Laporan::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function handledLaporans()
    {
        return $this->hasMany(Laporan::class, 'admin_id');
    }

    public function isAdmin()
    {
        return in_array($this->role, ['super_admin', 'admin_kecamatan', 'admin_desa', 'admin', 'lurah', 'admin_rw', 'admin_rt']);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
