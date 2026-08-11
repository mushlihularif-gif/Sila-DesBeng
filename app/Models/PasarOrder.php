<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PasarOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = [
        'order_number',
        'user_id',
        'region_id',
        'total_amount',
        'shipping_cost',
        'grand_total',
        'delivery_method',
        'payment_method',
        'payment_channel',
        'payment_va_number',
        'payment_qr_url',
        'payment_expiry_time',
        'proof_of_payment',
        'delivery_address',
        'delivery_latitude',
        'delivery_longitude',
        'distance_km',
        'full_name',
        'phone',
        'notes',
        'status',
        'confirmed_at',
        'completion_time',
        'rejection_reason',
        'cancellation_reason_user',
        'cancellation_requested_at',
        'cancellation_status',
        'admin_cancellation_response',
        'delivery_proof_image',
        'receipt_path',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'delivery_latitude' => 'decimal:7',
        'delivery_longitude' => 'decimal:7',
        'distance_km' => 'decimal:2',
        'payment_expiry_time' => 'datetime',
        'confirmed_at' => 'datetime',
        'completion_time' => 'datetime',
        'cancellation_requested_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function items()
    {
        return $this->hasMany(PasarOrderItem::class, 'pasar_order_id');
    }

    public static function generateOrderNumber()
    {
        do {
            $orderNumber = 'PSR-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        } while (self::where('order_number', $orderNumber)->exists());
        
        return $orderNumber;
    }

    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->grand_total, 0, ',', '.');
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'processing' => 'primary',
            'ready' => 'secondary',
            'completed' => 'success',
            'cancelled' => 'danger',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
    /**
     * Ambil admin/staf yang memproses pesanan
     */
    public function handler()
    {
        return $this->belongsTo(\App\Models\User::class, 'handled_by');
    }
}
