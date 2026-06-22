<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_name',
        'latitude',
        'longitude',
        'address',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'ewallet_name',
        'ewallet_number',
        'ewallet_account_holder',
        'payment_methods',
        'card_background_image',
        'card_background_type',
        'card_gradient_style',
        'cash_payment_description',
        'whatsapp_number',
        'office_address',
        'operating_hours',
    ];

    protected $casts = [
        'payment_methods' => 'array',
        'bank_account_number' => 'encrypted',
        'bank_account_holder' => 'encrypted',
        'ewallet_number' => 'encrypted',
        'ewallet_account_holder' => 'encrypted',
    ];
}