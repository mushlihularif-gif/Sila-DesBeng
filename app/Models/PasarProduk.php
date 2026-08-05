<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasarProduk extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_produk',
        'deskripsi',
        'harga',
        'stok',
        'satuan',
        'status',
        'kategori',
        'foto',
        'foto_2',
        'foto_3',
        'lokasi',
        'latitude',
        'longitude',
        'region_id'
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function carts()
    {
        return $this->hasMany(PasarCart::class, 'pasar_produk_id');
    }

    public function orderItems()
    {
        return $this->hasMany(PasarOrderItem::class, 'pasar_produk_id');
    }

    /**
     * Check if product has enough stock
     */
    public function hasStock($quantity = 1)
    {
        return $this->stok >= $quantity;
    }

    /**
     * Decrease stock by quantity
     */
    public function decreaseStock($quantity = 1)
    {
        if (!$this->hasStock($quantity)) {
            throw new \Exception("Stok {$this->nama_produk} tidak mencukupi.");
        }
        
        $this->decrement('stok', $quantity);
        
        if ($this->stok === 0) {
            $this->update(['status' => 'habis']);
        }
        
        return $this;
    }

    /**
     * Increase stock by quantity
     */
    public function increaseStock($quantity = 1)
    {
        $this->increment('stok', $quantity);
        
        if ($this->stok > 0 && $this->status === 'habis') {
            $this->update(['status' => 'tersedia']);
        }
        
        return $this;
    }
}
