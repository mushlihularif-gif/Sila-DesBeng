<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FasilitasUmumBooking extends Model
{
    use SoftDeletes;

    protected $table = 'fasilitas_umum_bookings';


    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'fasilitas_id',
        'start_date',
        'end_date',
        'quantity',
        'rental_purpose',
        'status',
        'admin_notes',
        'cancellation_reason',
        'cancellation_requested_at',
        'cancellation_status',
        'admin_cancellation_response',
        'confirmed_at',
        'return_time',
        'completion_time',
        'region_id',
        'dengan_supir',
        'delivery_method'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'cancellation_requested_at' => 'datetime',
        'confirmed_at' => 'datetime',
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
                $model->order_number = 'FU-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fasilitas()
    {
        return $this->belongsTo(FasilitasUmum::class, 'fasilitas_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
