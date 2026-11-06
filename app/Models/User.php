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

    public const ROLES = ['user', 'admin_rt', 'admin_rw', 'admin_desa', 'super_admin'];
    public const REGION_ROLES = ['desa' => 'admin_desa', 'kelurahan' => 'admin_desa', 'rw' => 'admin_rw', 'rt' => 'admin_rt'];

    public function hasSupportedRole(): bool
    {
        return in_array($this->role, self::ROLES, true);
    }


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

        // BLIND INDEXING: Buat hash dari data sensitif sebelum disimpan
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

        // MENCEGAH BUG HANTU DATA: Hapus relasi foto saat User dihapus
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
        'latitude',
        'longitude',
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
        'store_description',
        'store_banner',
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
        'ktp_photo_path',
        'face_photo_path',
        'nik_hash',
        'name_hash',
        'phone_hash',
        'fcm_token',
        'google_id',
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
            // Waktu KYC disetujui. Tanpa cast ini nilainya kembali sebagai string
            // mentah, sehingga pemanggilan ->format()/->translatedFormat() di view
            // gagal dengan "Call to a member function ... on string" — dan hanya
            // pada akun yang memang sudah terverifikasi, karena akun yang belum
            // bernilai null dan diam saja.
            'verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'reset_token_expires_at' => 'datetime',
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

    public function mobilTransactions()
    {
        return $this->hasMany(MobilBooking::class, 'user_id');
    }

    public function fasilitasTransactions()
    {
        return $this->hasMany(FasilitasUmumBooking::class, 'user_id');
    }

    public function pasarTransactions()
    {
        return $this->hasMany(PasarOrder::class, 'user_id');
    }

    // ===================================
    // RELASI DARI I_VILAGGE
    // ===================================
    
    

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    

    public function isAdmin()
    {
        return in_array($this->role, ['super_admin', 'admin_desa', 'admin_rw', 'admin_rt'], true);
    }

    public function isStaff()
    {
        return false; // Akun staf lama tidak lagi memiliki akses.
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function staffPermissions()
    {
        return $this->hasMany(StaffPermission::class, 'user_id');
    }

    public function hasUnitPermission($unitKey)
    {
        return in_array($this->role, ['super_admin', 'admin_desa'], true)
            && in_array($unitKey, self::IZIN_UNIT, true);
    }

    /** Kompatibilitas pemanggil lama; akun staf tidak lagi diaktifkan. */
    public static function getStaffForUnit($unitKey, $regionId = null)
    {
        return collect();
    }

    /** Daftar modul Sistem Platform, dikelompokkan mengikuti sidebar. */
    public const IZIN_PLATFORM_GRUP = [
        'Sistem Platform' => [
            'platform_integrasi'  => ['Integrasi Payment Gateway', 'bx-plug'],
            'platform_penarikan'  => ['Persetujuan Penarikan Saldo', 'bx-money-withdraw'],
            'platform_monitoring' => ['Monitoring Transaksi', 'bx-line-chart'],
            'platform_keamanan'   => ['Log Keamanan dan Audit', 'bx-shield-quarter'],
            'platform_biaya'      => ['Biaya Server dan Domain', 'bx-server'],
        ],
        'Manajemen' => [
            'platform_banner' => ['Banner', 'bx-image'],
        ],
        'Data & Laporan' => [
            'platform_aktivitas' => ['Log Aktivitas', 'bx-history'],
        ],
        'Dashboard' => [
            'platform_inbox' => ['Kotak Masuk Email Instansi', 'bx-envelope'],
        ],
    ];

    /**
     * Bentuk datar 'kunci' => 'label', diturunkan dari IZIN_PLATFORM_GRUP
     * supaya definisinya tidak perlu ditulis dua kali.
     */
    public static function izinPlatform(): array
    {
        $hasil = [];

        foreach (self::IZIN_PLATFORM_GRUP as $izinGrup) {
            foreach ($izinGrup as $kunci => [$label, $ikon]) {
                $hasil[$kunci] = $label;
            }
        }

        return $hasil;
    }

    /**
     * Kunci izin milik satu tab, mis. kunciIzinGrup('Manajemen').
     */
    public static function kunciIzinGrup(string $namaGrup): array
    {
        return array_keys(self::IZIN_PLATFORM_GRUP[$namaGrup] ?? []);
    }

    /** Modul platform hanya dapat diakses Super Admin. */
    public function hasPlatformPermission(string $permissionKey): bool
    {
        return $this->isSuperAdmin() && array_key_exists($permissionKey, self::izinPlatform());
    }

    /**
     * True kalau akun ini berhak membuka area Sistem Platform sama sekali.
     * Dipakai untuk menentukan beranda dan menampilkan menu.
     */
    public function bolehAksesPlatform(): bool
    {
        return $this->isSuperAdmin();
    }

    /** Kompatibilitas pemanggil lama; akun staf tidak lagi diaktifkan. */
    public function hanyaPlatform(): bool
    {
        return false;
    }

    /** Kunci izin untuk staf UNIT LAYANAN, lawan dari IZIN_PLATFORM_GRUP. */
    public const IZIN_UNIT = [
        'gas', 'sewa_alat', 'sewa_mobil', 'fasilitas_umum',
        'pasar_daerah', 'kabar_informasi',
    ];

    /** Nama layanan untuk masing-masing kunci izin unit. */
    public const NAMA_UNIT = [
        'gas'             => 'Penjualan Gas',
        'sewa_alat'       => 'Penyewaan Alat',
        'sewa_mobil'      => 'Penyewaan Transportasi',
        'fasilitas_umum'  => 'Fasilitas Umum',
        'pasar_daerah'    => 'Pasar Daerah',
        'kabar_informasi' => 'Kabar dan Informasi Daerah',
            ];

    /** Kompatibilitas pemanggil lama; akun staf tidak lagi diaktifkan. */
    public function layananDipegang(): array
    {
        return [];
    }

    /** Kompatibilitas pemanggil lama; akun staf tidak lagi diaktifkan. */
    public function punyaIzinUnit(): bool
    {
        return false;
    }

    /**
     * True kalau punya minimal satu dari sekumpulan izin platform.
     * Dipakai memutuskan apakah sebuah GRUP menu perlu ditampilkan.
     */
    public function bolehSalahSatu(array $keys): bool
    {
        foreach ($keys as $k) {
            if ($this->hasPlatformPermission($k)) {
                return true;
            }
        }

        return false;
    }

    /** Periksa peran yang didukung untuk menu bersama. */
    public function bolehMenu(array $rolesBiasa, string $izinPlatform): bool
    {
        return $this->hasSupportedRole() && in_array($this->role, $rolesBiasa, true);
    }

    /**
     * Sebutan role untuk ditampilkan di antarmuka.
     */
    public function labelRole(): string
    {
        return ['user' => 'Warga', 'admin_rt' => 'Admin RT', 'admin_rw' => 'Admin RW',
            'admin_desa' => 'Admin Desa', 'super_admin' => 'Super Admin'][$this->role] ?? 'Peran tidak didukung';
    }

    public function getUnitPermissions()
    {
        return in_array($this->role, ['super_admin', 'admin_desa'], true) ? collect(self::IZIN_UNIT) : collect();
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /** Akun admin/staf yang membuat akun ini (dipakai Kelola Staf). */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Kaitan ke anggota Kartu Keluarga, dicocokkan lewat blind index NIK. */
    public function familyMember()
    {
        return $this->hasOne(FamilyMember::class, 'nik_hash', 'nik_hash');
    }

    public function kycVerification()
    {
        return $this->hasOne(KycVerification::class)->latest();
    }

    /**
     * URL Foto Profil Pengguna
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (!empty($this->avatar)) {
            if (\Illuminate\Support\Str::startsWith($this->avatar, ['http://', 'https://'])) {
                return $this->avatar;
            }
            return route('media.avatar', ['filename' => basename($this->avatar)]);
        }
        if ($this->relationLoaded('file') ? $this->file : $this->file()->first()) {
            return route('media.avatar', ['filename' => basename($this->file->path)]);
        }
        return null;
    }
}
