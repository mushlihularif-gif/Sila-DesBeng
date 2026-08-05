<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasarCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pasar_produk_id',
        'quantity'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produk()
    {
        return $this->belongsTo(PasarProduk::class, 'pasar_produk_id');
    }
}
