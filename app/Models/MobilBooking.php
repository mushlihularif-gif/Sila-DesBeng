<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobilBooking extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $table = 'mobil_bookings';


    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'mobil_id',
        'jenis_sewa',
        'delivery_method',
        'quantity',
        'start_date',
        'end_date',
        'distance_km',
        'recipient_name',
        'delivery_address',
        'rental_purpose',
        'latitude',
        'longitude',
        'payment_method',
        'payment_proof',
        'delivery_proof_image',
        'total_amount',
        'status',
        'assigned_supir_id',
        'admin_notes',
        'cancellation_reason',
        'cancellation_requested_at',
        'cancellation_status',
        'admin_cancellation_response',
        'receipt_path',
        'confirmed_at',
        'delivery_time',
        'arrival_time',
        'return_time',
        'completion_time',
        'region_id',
        'dengan_supir'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_amount' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'cancellation_requested_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'delivery_time' => 'datetime',
        'arrival_time' => 'datetime',
        'return_time' => 'datetime',
        'completion_time' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Terapkan isolasi wilayah secara otomatis untuk Admin RT/RW
        static::addGlobalScope(new \App\Models\Scopes\RegionIsolationScope('user'));

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
            if (empty($model->order_number)) {
                $model->order_number = 'MB-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mobil()
    {
        return $this->belongsTo(Mobil::class, 'mobil_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function transactionReceipt()
    {
        return $this->hasOne(TransactionReceipt::class, 'booking_id')->where('booking_type', 'mobil');
    }
    /**
     * Ambil admin/staf yang memproses pesanan
     */
    public function handler()
    {
        return $this->belongsTo(\App\Models\User::class, 'handled_by');
    }

    public function supir()
    {
        return $this->belongsTo(Supir::class, 'assigned_supir_id');
    }
}
