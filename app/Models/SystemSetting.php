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
        'gateway_provider',
        'gateway_secret_key',
        'gateway_public_key',
        'gateway_is_production',
        'platform_fee_percentage',
    ];

    protected $casts = [
        'payment_methods' => 'array',
        'bank_account_number' => 'encrypted',
        'bank_account_holder' => 'encrypted',
        'ewallet_number' => 'encrypted',
        'ewallet_account_holder' => 'encrypted',
        'gateway_secret_key' => 'encrypted',
        'gateway_public_key' => 'encrypted',
        'gateway_is_production' => 'boolean',
        'platform_fee_percentage' => 'decimal:2',
    ];
}