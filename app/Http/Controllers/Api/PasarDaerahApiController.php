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

use App\Models\PasarReview;
use App\Models\PasarComplaint;
use App\Models\User;
use App\Models\Region;
use Illuminate\Support\Facades\Storage;

class PasarDaerahApiController extends Controller
{
    /**
     * Get all active products
     */
    public function getProducts(Request $request)
    {
        $query = PasarProduk::with('region')->where('status', 'tersedia');

        // Filter by category
        if ($request->has('category') && $request->category !== 'Semua') {
            $query->where('kategori', $request->category);
        }

        // Search by name
        if ($request->has('search') && $request->search != '') {
            $query->where('nama_produk', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sort = $request->get('sort', 'latest');
        if ($sort === 'price_asc') {
            $query->orderBy('harga', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('harga', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $products = $query->get();

        // Format image URLs and seller info
        $products->transform(function ($product) {
            $product->image_url = $product->foto ? url('storage/' . $product->foto) : null;
            $product->images = array_values(array_filter([
                $product->foto ? url('storage/' . $product->foto) : null,
                $product->foto_2 ? url('storage/' . $product->foto_2) : null,
                $product->foto_3 ? url('storage/' . $product->foto_3) : null,
            ]));
            return $product;
        });

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    /**
     * Get product detail by ID (including seller info and reviews)
     */
    public function getProductDetail($id)
    {
        $product = PasarProduk::with('region')->find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $images = array_values(array_filter([
            $product->foto ? url('storage/' . $product->foto) : null,
            $product->foto_2 ? url('storage/' . $product->foto_2) : null,
            $product->foto_3 ? url('storage/' . $product->foto_3) : null,
        ]));

        $product->image_url = !empty($images) ? $images[0] : null;
        $product->images = $images;

        // Seller / Admin Desa info
        $seller = User::where('region_id', $product->region_id)
            ->whereIn('role', ['admin_desa', 'admin'])
            ->first();

        $sellerData = null;
        if ($seller) {
            $sellerData = [
                'id' => $seller->id,
                'name' => $seller->name,
                'store_name' => 'Pasar ' . ($product->region ? $product->region->name : 'Desa'),
                'avatar' => $seller->avatar ? url('storage/' . $seller->avatar) : null,
                'store_banner' => $seller->store_banner ? url('storage/' . $seller->store_banner) : null,
                'store_description' => $seller->store_description,
                'region_name' => $product->region ? $product->region->name : null,
            ];
        }

        // Reviews
        $reviews = PasarReview::where('pasar_produk_id', $product->id)
            ->with('user')
            ->latest()
            ->get()
            ->map(function ($rev) {
                return [
                    'id' => $rev->id,
                    'user_name' => $rev->user ? $rev->user->name : 'Anonim',
                    'user_avatar' => $rev->user && $rev->user->avatar ? url('storage/' . $rev->user->avatar) : null,
                    'rating' => $rev->rating,
                    'comment' => $rev->comment,
                    'reply' => $rev->reply,
                    'replied_at' => $rev->replied_at ? $rev->replied_at->format('d M Y, H:i') : null,
                    'created_at' => $rev->created_at ? $rev->created_at->format('d M Y, H:i') : null,
                ];
            });

        $avgRating = $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : 5.0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'product' => $product,
                'seller' => $sellerData,
                'rating_summary' => [
                    'average' => $avgRating,
                    'total_reviews' => $reviews->count(),
                ],
                'reviews' => $reviews,
            ]
        ]);
    }

    /**
     * Get reviews for a product
     */
    public function getProductReviews($id)
    {
        $reviews = PasarReview::where('pasar_produk_id', $id)
            ->with('user')
            ->latest()
            ->get()
            ->map(function ($rev) {
                return [
                    'id' => $rev->id,
                    'user_name' => $rev->user ? $rev->user->name : 'Anonim',
                    'user_avatar' => $rev->user && $rev->user->avatar ? url('storage/' . $rev->user->avatar) : null,
                    'rating' => $rev->rating,
                    'comment' => $rev->comment,
                    'reply' => $rev->reply,
                    'replied_at' => $rev->replied_at ? $rev->replied_at->format('d M Y, H:i') : null,
                    'created_at' => $rev->created_at ? $rev->created_at->format('d M Y, H:i') : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $reviews,
        ]);
    }

    /**
     * Add review for a product (Auth required)
     */
    public function addReview(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $product = PasarProduk::findOrFail($id);
        $user = $request->user();

        $review = PasarReview::create([
            'pasar_produk_id' => $product->id,
            'user_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ulasan berhasil dikirim',
            'data' => $review
        ]);
    }

    /**
     * Get Seller / Admin Desa Store Profile
     */
    public function getSellerProfile($region_id)
    {
        $region = Region::find($region_id);
        $seller = User::where('region_id', $region_id)
            ->whereIn('role', ['admin_desa', 'admin'])
            ->first();

        $products = PasarProduk::where('region_id', $region_id)
            ->where('status', 'tersedia')
            ->latest()
            ->get()
            ->map(function ($product) {
                $product->image_url = $product->foto ? url('storage/' . $product->foto) : null;
                return $product;
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'store_name' => 'Pasar ' . ($region ? $region->name : 'Desa'),
                'region_name' => $region ? $region->name : null,
                'avatar' => ($seller && $seller->avatar) ? url('storage/' . $seller->avatar) : null,
                'store_banner' => ($seller && $seller->store_banner) ? url('storage/' . $seller->store_banner) : null,
                'store_description' => $seller ? $seller->store_description : null,
                'products' => $products,
                'total_products' => $products->count(),
            ]
        ]);
    }

    /**
     * Get unique categories
     */
    public function getCategories()
    {
        $defaultCategories = [
            'Semua',
            'Hasil Tani & Bumi',
            'Pangan & Olahan',
            'Material & Bangunan',
            'Kerajinan & Kesenian',
            'Lainnya',
        ];

        $dbCategories = PasarProduk::where('status', 'tersedia')
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->select('kategori')
            ->distinct()
            ->pluck('kategori')
            ->toArray();

        $merged = array_values(array_unique(array_merge($defaultCategories, $dbCategories)));

        return response()->json([
            'status' => 'success',
            'data' => $merged
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

    /**
     * Ajukan Komplain / Retur Barang Rusak atau Tidak Sesuai
     */
    public function submitComplaint(Request $request, $orderId)
    {
        $user = $request->user();
        
        $order = PasarOrder::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        $request->validate([
            'reason' => 'required|string',
            'solution_requested' => 'required|in:refund,replacement',
            'description' => 'nullable|string|max:1000',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:100',
            'evidence_1' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'evidence_2' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'evidence_3' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // Cek jika sudah pernah komplain yang masih pending
        $existing = PasarComplaint::where('pasar_order_id', $order->id)->first();
        if ($existing && $existing->status === 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah mengajukan komplain untuk pesanan ini yang sedang diproses.'
            ], 400);
        }

        $evidencePaths = [];
        foreach (['evidence_1', 'evidence_2', 'evidence_3'] as $evKey) {
            if ($request->hasFile($evKey)) {
                $evidencePaths[$evKey] = $request->file($evKey)->store('pasar_complaints', 'public');
            }
        }

        $complaint = PasarComplaint::create([
            'pasar_order_id' => $order->id,
            'user_id' => $user->id,
            'region_id' => $order->region_id,
            'reason' => $request->reason,
            'solution_requested' => $request->solution_requested,
            'description' => $request->description,
            'evidence_1' => $evidencePaths['evidence_1'] ?? null,
            'evidence_2' => $evidencePaths['evidence_2'] ?? null,
            'evidence_3' => $evidencePaths['evidence_3'] ?? null,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Komplain berhasil diajukan dan sedang ditinjau oleh Admin Desa.',
            'data' => $complaint
        ]);
    }

    /**
     * Get detail komplain pesanan
     */
    public function getComplaintDetail(Request $request, $orderId)
    {
        $user = $request->user();

        $complaint = PasarComplaint::where('pasar_order_id', $orderId)
            ->where('user_id', $user->id)
            ->with('handler')
            ->first();

        if (!$complaint) {
            return response()->json([
                'status' => 'error',
                'message' => 'Belum ada komplain untuk pesanan ini'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $complaint->id,
                'order_id' => $complaint->pasar_order_id,
                'reason' => $complaint->reason,
                'solution_requested' => $complaint->solution_requested,
                'description' => $complaint->description,
                'evidence_1' => $complaint->evidence_1 ? url('storage/' . $complaint->evidence_1) : null,
                'evidence_2' => $complaint->evidence_2 ? url('storage/' . $complaint->evidence_2) : null,
                'evidence_3' => $complaint->evidence_3 ? url('storage/' . $complaint->evidence_3) : null,
                'status' => $complaint->status,
                'admin_response' => $complaint->admin_response,
                'handler_name' => $complaint->handler ? $complaint->handler->name : null,
                'resolved_at' => $complaint->resolved_at ? $complaint->resolved_at->format('d M Y, H:i') : null,
                'created_at' => $complaint->created_at->format('d M Y, H:i'),
            ]
        ]);
    }
}
