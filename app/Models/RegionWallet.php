<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegionWallet extends Model
{
    protected $fillable = [
        'region_id',
        'saldo_tertahan',
        'saldo_tersedia',
        'total_dicairkan',
        'gateway_beneficiary_id',
    ];

    protected $casts = [
        'saldo_tertahan' => 'decimal:2',
        'saldo_tersedia' => 'decimal:2',
        'total_dicairkan' => 'decimal:2',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'region_id', 'region_id');
    }
}
