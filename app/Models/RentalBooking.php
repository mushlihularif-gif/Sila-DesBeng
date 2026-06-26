<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RentalBooking extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * KEAMANAN: Auto-generate UUID saat membuat record baru.
     * UUID digunakan sebagai public identifier di URL untuk mencegah ID Guessing / IDOR.
     */
    protected static function boot()
    {
        parent::boot();

        // Terapkan isolasi wilayah secara otomatis untuk Admin RT/RW
        static::addGlobalScope(new \App\Models\Scopes\RegionIsolationScope('user'));

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = [
        'user_id',
        'barang_id',
        'order_number',
        'delivery_method',
        'quantity',
        'start_date',
        'end_date',
        'days_count',
        'recipient_name',
        'delivery_address',
        'latitude',
        'longitude',
        'payment_method',
        'payment_proof',
        'delivery_proof_image',
        'total_amount',
        'status',
        'admin_notes',
        'confirmed_at',
        'delivery_time',
        'arrival_time',
        'return_time',
        'completion_time',
        'cancellation_reason',
        'cancellation_requested_at',
        'cancellation_status',
        'admin_cancellation_response',
        'admin_cancellation_response',
        'receipt_path',
        'rental_purpose',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'confirmed_at' => 'datetime',
        'delivery_time' => 'datetime',
        'arrival_time' => 'datetime',
        'return_time' => 'datetime',
        'completion_time' => 'datetime',
        'cancellation_requested_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Ambil pengguna yang membuat pemesanan
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ambil barang sewaan
     */
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    /**
     * Ambil total harga terformat
     */
    public function getFormattedTotalAttribute()
    {
        return 'Rp. ' . number_format($this->total_amount, 0, ',', '.');
    }

    /**
     * Ambil tanggal mulai terformat
     */
    public function getFormattedStartDateAttribute()
    {
        return $this->start_date->format('d/m/Y');
    }

    /**
     * Ambil tanggal selesai terformat
     */
    public function getFormattedEndDateAttribute()
    {
        return $this->end_date->format('d/m/Y');
    }

    /**
     * Periksa jika pemesanan adalah untuk diantar
     */
    public function isDelivery()
    {
        return $this->delivery_method === 'antar';
    }

    /**
     * Periksa jika pemesanan adalah untuk dijemput
     */
    public function isPickup()
    {
        return $this->delivery_method === 'jemput';
    }

    /**
     * Periksa jika pembayaran via transfer
     */
    public function isTransfer()
    {
        return $this->payment_method === 'transfer';
    }

    /**
     * Periksa jika pembayaran tunai
     */
    public function isCash()
    {
        return $this->payment_method === 'tunai';
    }

    /**
     * Buat nomor pesanan unik
     */
    public static function generateOrderNumber()
    {
        do {
            $orderNumber = strtoupper(substr(md5(uniqid(rand(), true)), 0, 13));
        } while (self::where('order_number', $orderNumber)->exists());
        
        return $orderNumber;
    }

    /**
     * Periksa jika ada permintaan pembatalan
     */
    public function hasCancellationRequest()
    {
        return $this->cancellation_status === 'pending';
    }

    /**
     * Periksa jika pesanan bisa dibatalkan
     */
    public function canBeCancelled()
    {
        return $this->status === 'pending' && !$this->hasCancellationRequest();
    }
    /**
     * Get the badge class for the status.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'approved' => 'primary',
            'being_prepared' => 'info',
            'in_delivery' => 'info',
            'arrived' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}
