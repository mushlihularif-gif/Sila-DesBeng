<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionReceipt extends Model
{
    protected $fillable = [
        'booking_type',
        'booking_id',
        'receipt_number',
        'user_id',
        'item_name',
        'quantity',
        'total_amount',
        'payment_method',
        'receipt_file_path',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the receipt
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the rental booking (if type is rental)
     */
    public function rentalBooking()
    {
        return $this->belongsTo(RentalBooking::class, 'booking_id');
    }

    /**
     * Get the gas order (if type is gas)
     */
    public function gasOrder()
    {
        return $this->belongsTo(GasOrder::class, 'booking_id');
    }

    /**
     * Generate unique receipt number
     */
    public static function generateReceiptNumber($type)
    {
        $prefix = $type === 'rental' ? 'RNT' : 'GAS';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        
        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute()
    {
        return 'Rp. ' . number_format($this->total_amount, 0, ',', '.');
    }
}
