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
        'harga_asli',
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
        'region_id', 'store_id'
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'harga_asli' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function store() { return $this->belongsTo(MarketStore::class, 'store_id'); }
    public function storeKey(): string { return $this->store_id ? 'warga:'.$this->store_id : 'desa:'.$this->region_id; }
    public function scopeVisibleStore($query)
    {
        return $query->where(fn ($q) => $q->whereNull('store_id')->orWhereHas('store', fn ($s) => $s->where('status','approved')));
    }

    public function carts()
    {
        return $this->hasMany(PasarCart::class, 'pasar_produk_id');
    }

    public function orderItems()
    {
        return $this->hasMany(PasarOrderItem::class, 'pasar_produk_id');
    }

    public function reviews()
    {
        return $this->hasMany(PasarReview::class, 'pasar_produk_id');
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
