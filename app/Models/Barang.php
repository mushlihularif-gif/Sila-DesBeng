<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';

    protected $fillable = [
        'nama_barang',
        'deskripsi',
        'harga_sewa',
        'stok',
        'status',
        'kategori',
        'foto',
        'foto_2',
        'foto_3',
        'lokasi',
        'satuan', 
    ];

    protected $casts = [
        'harga_sewa' => 'decimal:2',
    ];

    /**
     * Check if stock is sufficient
     */
    public function hasStock($quantity)
    {
        return $this->stok >= $quantity;
    }

    /**
     * Decrease stock with validation (for rental)
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
     * Increase stock (when rental is returned)
     */
    public function increaseStock($quantity)
    {
        $this->stok += $quantity;
        $this->save();

        return $this;
    }
}