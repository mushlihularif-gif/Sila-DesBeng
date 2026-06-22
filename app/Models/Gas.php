<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gas extends Model
{
    use HasFactory;

    protected $table = 'gas';

    protected $fillable = [
        'jenis_gas',
        'deskripsi',
        'harga_satuan',
        'stok',
        'status',
        'kategori', // Tambahkan jika ingin seperti penyewaan
        'foto',
        'foto_2',
        'foto_3',
        'lokasi',
        'satuan', // Tambahkan jika ingin seperti penyewaan
        'region_id',
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
    ];

    public function gasOrders()
    {
        return $this->hasMany(GasOrder::class);
    }

    /**
     * Check if stock is sufficient
     */
    public function hasStock($quantity)
    {
        return $this->stok >= $quantity;
    }

    /**
     * Decrease stock with validation
     */
    public function decreaseStock($quantity)
    {
        if (!$this->hasStock($quantity)) {
            throw new \Exception("Stok tidak mencukupi. Tersedia: {$this->stok}, diminta: {$quantity}");
        }

        $this->stok -= $quantity;
        $this->save();

        return $this;
    }

    /**
     * Increase stock (for manual adjustment if needed)
     */
    public function increaseStock($quantity)
    {
        $this->stok += $quantity;
        $this->save();

        return $this;
    }
}