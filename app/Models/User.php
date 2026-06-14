<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
        'username',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'gender',
        'avatar',
        'position',
        'google_id',
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
}
