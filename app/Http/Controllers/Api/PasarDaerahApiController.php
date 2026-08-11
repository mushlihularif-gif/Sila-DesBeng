<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PasarProduk;
use App\Models\PasarCart;
use App\Models\PasarOrder;
use App\Models\PasarOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PasarDaerahApiController extends Controller
{
    /**
     * Get all active products
     */
    public function getProducts(Request $request)
    {
        $query = PasarProduk::where('status', 'tersedia');

        // Filter by category
        if ($request->has('category') && $request->category !== 'Semua') {
            $query->where('kategori', $request->category);
        }

        // Search by name
        if ($request->has('search') && $request->search != '') {
            $query->where('nama_produk', 'like', '%' . $request->search . '%');
        }

        // Sort by newest
        $products = $query->orderBy('created_at', 'desc')->get();

        // Format image URLs
        $products->transform(function ($product) {
            $product->image_url = $product->foto ? url('storage/' . $product->foto) : null;
            return $product;
        });

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    /**
     * Get unique categories
     */
    public function getCategories()
    {
        $categories = PasarProduk::where('status', 'tersedia')
            ->select('kategori')
            ->distinct()
            ->pluck('kategori')
            ->toArray();
            
        // Always include 'Semua' at the beginning
        array_unshift($categories, 'Semua');

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * Get user's cart
     */
    public function getCart(Request $request)
    {
        $user = $request->user();
        
        $cartItems = PasarCart::with('produk')
            ->where('user_id', $user->id)
            ->get();

        $formattedCart = $cartItems->map(function ($item) {
            $produk = $item->produk;
            return [
                'id' => $item->id, // Cart item ID
                'pasar_produk_id' => $item->pasar_produk_id,
                'quantity' => $item->quantity,
                'name' => $produk ? $produk->nama_produk : 'Produk Tidak Ditemukan',
                'price' => $produk ? $produk->harga : 0,
                'stock' => $produk ? $produk->stok : 0,
                'image_url' => ($produk && $produk->foto) ? url('storage/' . $produk->foto) : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedCart
        ]);
    }

    /**
     * Add item to cart
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'pasar_produk_id' => 'required|exists:pasar_produks,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = $request->user();
        $produk = PasarProduk::find($request->pasar_produk_id);

        if ($produk->stok < $request->quantity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stok tidak mencukupi'
            ], 400);
        }

        $cartItem = PasarCart::where('user_id', $user->id)
            ->where('pasar_produk_id', $request->pasar_produk_id)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $request->quantity;
            if ($produk->stok < $newQuantity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Stok tidak mencukupi untuk jumlah ini'
                ], 400);
            }
            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            PasarCart::create([
                'user_id' => $user->id,
                'pasar_produk_id' => $request->pasar_produk_id,
                'quantity' => $request->quantity
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil ditambahkan ke keranjang'
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateCart(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:pasar_carts,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = $request->user();
        $cartItem = PasarCart::where('id', $request->cart_item_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$cartItem) {
            return response()->json(['status' => 'error', 'message' => 'Item not found in cart'], 404);
        }

        $produk = $cartItem->produk;
        if ($produk->stok < $request->quantity) {
            return response()->json(['status' => 'error', 'message' => 'Stok tidak mencukupi'], 400);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json(['status' => 'success', 'message' => 'Keranjang diperbarui']);
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:pasar_carts,id'
        ]);

        $user = $request->user();
        PasarCart::where('id', $request->cart_item_id)
            ->where('user_id', $user->id)
            ->delete();

        return response()->json(['status' => 'success', 'message' => 'Barang dihapus dari keranjang']);
    }

    /**
     * Checkout process
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'delivery_method' => 'required|string|in:Ambil Sendiri,Diantar',
            // Default to COD if not specified
            'payment_method' => 'nullable|string'
        ]);

        $user = $request->user();
        $cartItems = PasarCart::with('produk')->where('user_id', $user->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Keranjang kosong'], 400);
        }

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            
            // Validate stock and calculate total
            foreach ($cartItems as $item) {
                $produk = $item->produk;
                if (!$produk || $produk->stok < $item->quantity) {
                    throw new \Exception("Stok {$produk->nama_produk} tidak mencukupi.");
                }
                $totalAmount += ($produk->harga * $item->quantity);
            }

            // Create Order
            $order = PasarOrder::create([
                'user_id' => $user->id,
                'region_id' => $user->region_id,
                'total_amount' => $totalAmount,
                'shipping_cost' => 0, // Simplified for now
                'grand_total' => $totalAmount,
                'delivery_method' => $request->delivery_method,
                'payment_method' => $request->payment_method ?? 'COD',
                'delivery_address' => $request->delivery_method == 'Diantar' ? $request->delivery_address : null,
                'full_name' => $user->name,
                'phone' => $user->phone ?? '00000000',
                'notes' => $request->notes,
                'status' => 'Menunggu Pembayaran',
            ]);

            // Create Order Items and decrease stock
            foreach ($cartItems as $item) {
                $produk = $item->produk;
                
                PasarOrderItem::create([
                    'pasar_order_id' => $order->id,
                    'pasar_produk_id' => $produk->id,
                    'quantity' => $item->quantity,
                    'price' => $produk->harga,
                    'subtotal' => $produk->harga * $item->quantity
                ]);

                // Decrease stock
                $produk->decrement('stok', $item->quantity);
            }

            // Clear Cart
            PasarCart::where('user_id', $user->id)->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pesanan berhasil dibuat',
                'order' => $order
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }
}
