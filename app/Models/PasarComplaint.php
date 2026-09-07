<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PasarComplaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pasar_complaints';

    protected $fillable = [
        'pasar_order_id',
        'user_id',
        'region_id',
        'reason',
        'solution_requested',
        'description',
        'evidence_1',
        'evidence_2',
        'evidence_3',
        'evidence_4',
        'evidence_5',
        'evidence_video',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'status',
        'admin_response',
        'handled_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(PasarOrder::class, 'pasar_order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
