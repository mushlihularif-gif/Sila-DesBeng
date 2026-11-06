<?php

namespace App\Services;

use App\Models\{AdminNotification, Notification, PasarCart, PasarOrder, PasarOrderItem, PasarProduk, Region, TransactionReceipt, User, WalletTransaction};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\ValidationException;

class MarketCheckoutService
{
    public function village(User $user): ?Region
    {
        return app(MarketShippingService::class)->village($user->region_id);
    }

    public function shipping(User $user, Region $seller): float
    {
        $quote = app(MarketShippingService::class)->quote($seller, $this->village($user), 'antar');
        abort_unless($quote['available'], 422, $quote['message']);
        return (float) $quote['cost'];
    }

    public function place(User $user, array $data, ?UploadedFile $proof = null): PasarOrder
    {
        abort_unless($user->verification_status === 'verified', 403, 'Verifikasi identitas diperlukan sebelum memesan.');
        $proofPath = null;
        try {
            return DB::transaction(function () use ($user, $data, $proof, &$proofPath) {
                // Satu pemilik keranjang diproses satu per satu, termasuk klik/kirim ulang.
                User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                $carts = app(MarketBasket::class)->select(PasarCart::where('user_id', $user->id), $data['store_key'] ?? null)->orderBy('id')->lockForUpdate()->get();
                if ($carts->isEmpty()) throw ValidationException::withMessages(['cart' => 'Keranjang kosong atau sudah diproses.']);
                $products = PasarProduk::whereIn('id', $carts->pluck('pasar_produk_id'))->get()->keyBy('id');
                $sellerIds = $products->map(fn ($p) => $p->storeKey())->unique();
                if ($sellerIds->count() !== 1 || $products->count() !== $carts->pluck('pasar_produk_id')->unique()->count()) {
                    throw ValidationException::withMessages(['cart' => 'Pilih produk yang tersedia dari satu toko.']);
                }
                $firstProduct = $products->first();
                $store = $firstProduct->store_id ? \App\Models\MarketStore::lockForUpdate()->findOrFail($firstProduct->store_id) : null;
                if ($store) {
                    abort_unless($store->status === 'approved' && $store->is_open,422,'Toko sedang tutup atau belum dapat menerima pesanan.');
                    abort_if((int)$store->user_id === (int)$user->id,422,'Anda tidak dapat memesan produk toko sendiri.');
                }
                // Seluruh perubahan profil/produk memakai urutan toko lalu produk.
                $products = PasarProduk::whereIn('id',$carts->pluck('pasar_produk_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                if($products->count() !== $carts->pluck('pasar_produk_id')->unique()->count()
                    || $products->contains(fn($p)=>$p->storeKey() !== $firstProduct->storeKey())) {
                    throw ValidationException::withMessages(['cart'=>'Isi toko berubah. Periksa kembali keranjang.']);
                }
                $seller = $store ? $store->shippingRegion() : Region::findOrFail($firstProduct->region_id);
                abort_unless($seller->parent?->type === 'kecamatan' && $seller->parent->name === 'Kecamatan Bengkalis', 422, 'Toko berada di luar cakupan Pasar Daerah.');
                $settings = $seller->settings ?? [];
                $payment = $data['payment_method'];
                $enabled = match ($payment) {
                    'COD', 'tunai' => $settings['enable_cod'] ?? true,
                    'bank_transfer', 'transfer_manual' => $settings['enable_bank_transfer'] ?? true,
                    'qris' => $settings['enable_qris'] ?? (!empty($settings['qris_image']) || !empty($settings['qris_ewallet_number'])),
                    default => false,
                };
                if (!$enabled) throw ValidationException::withMessages(['payment_method' => 'Metode pembayaran ini tidak tersedia. Pilih metode lain.']);
                $destination = isset($data['delivery_region_id']) ? Region::find($data['delivery_region_id']) : $this->village($user);
                $quote = app(MarketShippingService::class)->quote($seller, $destination, $data['delivery_method']);
                abort_unless($quote['available'], 422, $quote['message']);
                $shipping = $quote['cost'];
                if (isset($data['quoted_shipping_cost']) && (float) $data['quoted_shipping_cost'] !== (float) $shipping) {
                    throw ValidationException::withMessages(['quoted_shipping_cost' => 'Ongkir toko berubah. Periksa total terbaru sebelum memesan kembali.']);
                }
                $total = 0;
                foreach ($carts as $cart) {
                    $product = $products[$cart->pasar_produk_id];
                    if ($cart->quantity < 1 || $product->status !== 'tersedia' || $product->stok < $cart->quantity) {
                        throw ValidationException::withMessages(['cart' => 'Produk '.$product->nama_produk.' tidak tersedia atau stok tidak mencukupi.']);
                    }
                    $total += (float) $product->harga * $cart->quantity;
                }
                if ($proof) $proofPath = $proof->store('payment_proofs/pasar', 'public');
                $order = PasarOrder::create([
                    'order_number' => PasarOrder::generateOrderNumber(), 'user_id' => $user->id,
                    'region_id' => $seller->id, 'total_amount' => $total, 'shipping_cost' => $shipping,
                    'store_id' => $store?->id,
                    'payment_settings_snapshot' => $store ? array_intersect_key($settings, array_flip(['rekening_bank','rekening_nomor','rekening_nama','qris_image','qris_ewallet_number'])) : null,
                    'grand_total' => $total + $shipping, 'delivery_method' => $data['delivery_method'],
                    'payment_method' => $payment === 'transfer_manual' ? 'Transfer Manual' : ucfirst($payment),
                    'proof_of_payment' => $proofPath, 'status' => 'pending',
                    'full_name' => $data['full_name'], 'phone' => $data['phone'], 'notes' => $data['notes'] ?? null,
                    'delivery_address' => $data['delivery_method'] === 'antar' ? ($data['delivery_address'] ?? null) : null,
                    'delivery_region_id' => $data['delivery_method'] === 'antar' ? $destination?->id : null,
                    'delivery_latitude' => $data['delivery_method'] === 'antar' ? ($data['delivery_latitude'] ?? null) : null,
                    'delivery_longitude' => $data['delivery_method'] === 'antar' ? ($data['delivery_longitude'] ?? null) : null,
                ]);
                // Semua opsi ini pembayaran manual ke toko, bukan callback gateway.
                $ledgerMethod = match ($payment) {
                    'COD', 'tunai' => 'tunai', 'bank_transfer', 'transfer_manual' => 'transfer_manual', 'qris' => 'ewallet',
                };
                app(MarketPayments::class)->record($order,$ledgerMethod,$proofPath);
                foreach ($carts as $cart) {
                    $product = $products[$cart->pasar_produk_id];
                    PasarOrderItem::create([
                        'pasar_order_id' => $order->id, 'pasar_produk_id' => $product->id,
                        'product_name' => $product->nama_produk, 'product_price' => $product->harga,
                        'quantity' => $cart->quantity, 'subtotal' => $product->harga * $cart->quantity,
                    ]);
                    $product->decreaseStock($cart->quantity);
                }
                TransactionReceipt::create([
                    'booking_type' => 'pasar', 'booking_id' => $order->id,
                    'receipt_number' => TransactionReceipt::generateReceiptNumber('pasar'),
                    'user_id' => $user->id, 'region_id' => $seller->id,
                    'item_name' => $carts->count().' Produk ('.$order->order_number.')',
                    'quantity' => 1, 'total_amount' => $order->grand_total, 'payment_method' => $order->payment_method,
                ]);
                PasarCart::where('user_id', $user->id)->whereIn('id', $carts->pluck('id'))->delete();
                if ($store) Notification::create([
                    'user_id'=>$store->user_id,'type'=>'status_berubah','title'=>'Pesanan Baru di '.$store->name,
                    'message'=>'Pesanan '.$order->order_number.' menunggu tanggapan Anda.',
                    'link'=>route('market.seller.order',$order->id),'is_read'=>false,
                ]);
                else AdminNotification::create([
                    'type' => 'pasar_order', 'reference_id' => $order->id, 'region_id' => $seller->id,
                    'title' => 'Pesanan Pasar Daerah Baru', 'message' => 'Pesanan '.$order->order_number.' dari '.$user->name,
                    'is_read' => false,
                ]);
                Notification::create([
                    'user_id' => $user->id, 'type' => 'status_berubah', 'title' => 'Pesanan Dibuat',
                    'message' => 'Pesanan Pasar Daerah '.$order->order_number.' berhasil dibuat.',
                    'link' => NotificationService::orderLink($order, 'pasar'), 'is_read' => false,
                    'icon' => 'fas fa-shopping-bag text-blue-500',
                ]);
                return $order;
            });
        } catch (\Throwable $e) {
            if ($proofPath) Storage::disk('public')->delete($proofPath);
            throw $e;
        }
    }

    public function received(User $user, int $id, ?UploadedFile $proof = null): PasarOrder
    {
        $path = null;
        try {
            return DB::transaction(function () use ($user, $id, $proof, &$path) {
                $order = PasarOrder::where('user_id', $user->id)->lockForUpdate()->findOrFail($id);
                abort_unless(in_array($order->status, ['confirmed', 'in_delivery', 'delivered'], true), 409,
                    'Pesanan belum dapat dikonfirmasi diterima.');
                abort_if($order->complaint()->where('status', 'pending')->exists(), 409,
                    'Komplain pesanan ini masih ditangani.');
                if ($proof) $path = $proof->store('pasar_orders/proofs', 'public');
                if ($path) $order->delivery_proof_image = $path;
                $order->status = 'completed'; $order->completion_time = now(); $order->save();
                return $order;
            });
        } catch (\Throwable $e) {
            if ($path) Storage::disk('public')->delete($path);
            throw $e;
        }
    }
}
