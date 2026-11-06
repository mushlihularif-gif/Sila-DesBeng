<?php
namespace App\Services;

use App\Models\{PasarProduk, PasarCart};
use Illuminate\Validation\ValidationException;

class MarketBasket
{
    public function select($query, ?string $key)
    {
        if (!$key) return $query;
        if (!preg_match('/^(warga|desa):([1-9][0-9]*)$/', $key, $parts)) {
            throw ValidationException::withMessages(['store_key'=>'Pilih toko dari keranjang Anda.']);
        }
        return $query->whereHas('produk', fn ($q) => $parts[1] === 'warga'
            ? $q->where('store_id',(int)$parts[2]) : $q->whereNull('store_id')->where('region_id',(int)$parts[2]));
    }

    public function ensureAvailable(PasarProduk $product): void
    {
        if ($product->store_id) {
            abort_unless($product->store && $product->store->status === 'approved' && $product->store->is_open, 422, 'Toko sedang tutup atau belum dapat menerima pesanan.');
        }
    }
}
