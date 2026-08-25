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
        return in_array($this->role, ['super_admin', 'admin_kecamatan', 'admin_desa', 'admin', 'admin_rw', 'admin_rt', 'staff']);
    }

    public function isStaff()
    {
        return $this->role === 'staff';
    }

    public function isSuperAdmin()
    {
        return in_array($this->role, ['super_admin', 'admin_kecamatan', 'admin_desa', 'admin']);
    }

    public function staffPermissions()
    {
        return $this->hasMany(StaffPermission::class, 'user_id');
    }

    public function hasUnitPermission($unitKey)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return $this->staffPermissions()->where('unit_key', $unitKey)->exists();
    }

    /**
     * Izin modul Sistem Platform, disimpan di tabel staff_permissions yang sama
     * dengan izin unit layanan, dibedakan lewat awalan "platform_".
     *
     * Dikelompokkan MENGIKUTI TAB DI SIDEBAR.
     *
     * Tiap izin sengaja setara satu SUB-MENU, bukan satu grup. Memberi izin
     * "Manajemen" secara utuh tidak bermakna — yang dibuka staf selalu halaman
     * tertentu, jadi pilihannya pun harus setingkat halaman.
     *
     * Bentuk: 'Nama Tab' => ['kunci_izin' => ['label', 'ikon boxicons']]
     */
    public const IZIN_PLATFORM_GRUP = [
        'Sistem Platform' => [
            'platform_integrasi'  => ['Integrasi Payment Gateway', 'bx-plug'],
            'platform_monitoring' => ['Monitoring Transaksi', 'bx-line-chart'],
            'platform_keamanan'   => ['Log Keamanan & Audit', 'bx-shield-quarter'],
            'platform_biaya'      => ['Biaya Server & Domain', 'bx-server'],
        ],
        'Manajemen' => [
            'platform_staf'   => ['Kelola Staf', 'bx-user-voice'],
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

    /**
     * Izin modul platform.
     *
     * BEDA dengan hasUnitPermission(): di sini HANYA super_admin yang otomatis
     * lolos. Admin kabupaten/kecamatan/desa TIDAK, karena modul ini memuat
     * kredensial API dan log keamanan seluruh platform.
     */
    public function hasPlatformPermission(string $permissionKey): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        if ($this->role !== 'staff') {
            return false;
        }

        return $this->staffPermissions()->where('unit_key', $permissionKey)->exists();
    }

    /**
     * True kalau akun ini berhak membuka area Sistem Platform sama sekali.
     * Dipakai untuk menentukan beranda dan menampilkan menu.
     */
    public function bolehAksesPlatform(): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        if ($this->role !== 'staff') {
            return false;
        }

        return $this->staffPermissions()
            ->whereIn('unit_key', array_keys(self::izinPlatform()))
            ->exists();
    }

    /**
     * True untuk akun staf yang MURNI bekerja di tingkat platform.
     *
     * Dipakai menyembunyikan menu operasional per wilayah (Manajemen,
     * Data & Laporan, Pengaturan). Tanpa ini mereka ikut melihat laporan
     * transaksi dan pendapatan desa, karena banyak menu disaring dengan
     * kondisi negatif `role !== 'super_admin'` yang dilewati role 'staff'.
     */
    public function hanyaPlatform(): bool
    {
        return $this->role === 'staff' && $this->bolehAksesPlatform();
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

    /**
     * Boleh melihat sebuah menu yang dibagi bersama admin wilayah.
     *
     * $rolesBiasa = role non-staf yang selama ini sudah berhak. Staf hanya lolos
     * kalau izin platform-nya dicentang. Bentuk ini dipakai supaya penambahan
     * akses untuk staf platform tidak mengubah sedikit pun hak admin wilayah.
     */
    public function bolehMenu(array $rolesBiasa, string $izinPlatform): bool
    {
        if ($this->role === 'staff') {
            return $this->hasPlatformPermission($izinPlatform);
        }

        return in_array($this->role, $rolesBiasa, true);
    }

    /**
     * Sebutan role untuk ditampilkan di antarmuka.
     */
    public function labelRole(): string
    {
        $peta = [
            'super_admin'     => 'Super Admin',
            'admin'           => 'Admin Pusat',
            'admin_kecamatan' => 'Admin Kecamatan',
            'admin_desa'      => 'Admin Desa',
            'admin_rw'        => 'Admin RW',
            'admin_rt'        => 'Admin RT',
            'user'            => 'Pengguna',
        ];

        if ($this->role === 'staff') {
            return $this->hanyaPlatform() ? 'Staf Platform' : 'Staf Unit';
        }

        return $peta[$this->role] ?? ucfirst($this->role);
    }

    public function getUnitPermissions()
    {
        if ($this->isSuperAdmin()) {
            return collect(['gas', 'sewa_alat', 'sewa_mobil', 'fasilitas_umum', 'pasar_daerah', 'kabar_informasi', 'pelaporan_warga']);
        }
        return $this->staffPermissions()->pluck('unit_key');
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
}
