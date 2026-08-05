<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasarOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pasar_order_id',
        'pasar_produk_id',
        'product_name',
        'product_price',
        'quantity',
        'subtotal',
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(PasarOrder::class, 'pasar_order_id');
    }

    public function produk()
    {
        return $this->belongsTo(PasarProduk::class, 'pasar_produk_id');
    }
}
