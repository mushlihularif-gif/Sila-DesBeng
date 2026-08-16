<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'region_id',
        'type',
        'source',
        'amount',
        'reference_type',
        'reference_id',
        'status',
        'verified_by',
        'proof_path',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
