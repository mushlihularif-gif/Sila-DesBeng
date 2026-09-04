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
use App\Models\PasarChatSession;
use App\Models\PasarChatMessage;
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

        $avgRating = $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : 0;

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

            // Admin Notification
            \App\Models\AdminNotification::create([
                'type' => 'pasar_order',
                'reference_id' => $order->id,
                'region_id' => $user->region_id,
                'title' => 'Pesanan Pasar Daerah Baru (Via Mobile)',
                'message' => 'Pesanan Rp ' . number_format($totalAmount, 0, ',', '.') . ' dari ' . $user->name,
                'is_read' => false,
            ]);

            // User Notification
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'type' => 'status_berubah',
                'title' => 'Pesanan Berhasil Dibuat',
                'message' => 'Pesanan Pasar Daerah (Order ID: ' . $order->order_number . ') berhasil dibuat.',
                'is_read' => false,
                'link' => '/pasar-daerah/riwayat',
                'icon' => 'fas fa-shopping-bag text-blue-500'
            ]);

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

        if ($order->status === 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan sudah selesai. Hak komplain telah hangus.'
            ], 400);
        }

        if ($order->delivered_at) {
            $hoursPassed = now()->diffInHours($order->delivered_at);
            if ($hoursPassed >= 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Batas waktu pengajuan komplain (2 Jam setelah barang diterima) telah habis.'
                ], 400);
            }
        }

        $request->validate([
            'reason' => 'required|string',
            'solution_requested' => 'required|in:refund,replacement',
            'description' => 'nullable|string|max:1000',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:100',
            'evidence_1' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'evidence_2' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'evidence_3' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'evidence_4' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'evidence_5' => 'required|image|mimes:jpeg,png,jpg|max:10240',
            'evidence_video' => 'required|mimes:mp4,mov,ogg,qt|max:30720',
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
        $evidenceKeys = ['evidence_1', 'evidence_2', 'evidence_3', 'evidence_4', 'evidence_5', 'evidence_video'];
        foreach ($evidenceKeys as $evKey) {
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
            'evidence_4' => $evidencePaths['evidence_4'] ?? null,
            'evidence_5' => $evidencePaths['evidence_5'] ?? null,
            'evidence_video' => $evidencePaths['evidence_video'] ?? null,
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
                'evidence_4' => $complaint->evidence_4 ? url('storage/' . $complaint->evidence_4) : null,
                'evidence_5' => $complaint->evidence_5 ? url('storage/' . $complaint->evidence_5) : null,
                'evidence_video' => $complaint->evidence_video ? url('storage/' . $complaint->evidence_video) : null,
                'status' => $complaint->status,
                'admin_response' => $complaint->admin_response,
                'handler_name' => $complaint->handler ? $complaint->handler->name : null,
                'resolved_at' => $complaint->resolved_at ? $complaint->resolved_at->format('d M Y, H:i') : null,
                'created_at' => $complaint->created_at->format('d M Y, H:i'),
            ]
        ]);
    }

    /**
     * Konfirmasi Pesanan Diterima oleh User (+ Upload Foto Bukti Sampai)
     */
    public function confirmReceived(Request $request, $orderId)
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

        if ($order->status === 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Pesanan ini sudah selesai.'
            ], 400);
        }

        $request->validate([
            'delivery_proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // Support both field names
        $photoField = $request->hasFile('delivery_proof_image') ? 'delivery_proof_image' : ($request->hasFile('proof_image') ? 'proof_image' : null);

        if ($photoField) {
            $path = $request->file($photoField)->store('pasar_orders/proofs', 'public');
            $order->delivery_proof_image = $path;
        }

        $order->status = 'completed';
        $order->completion_time = now();
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan berhasil dikonfirmasi selesai. Terima kasih!',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'delivery_proof_image' => $order->delivery_proof_image ? url('storage/' . $order->delivery_proof_image) : null,
                'completion_time' => $order->completion_time ? $order->completion_time->format('Y-m-d H:i:s') : null,
            ]
        ]);
    }

    /**
     * Get Chat History for API
     */
    public function getChatHistory(Request $request, $region_id)
    {
        $user = $request->user();
        $sessionToken = $request->header('X-Chat-Session-Token') ?: $request->get('session_token');

        $query = PasarChatSession::where('region_id', $region_id);

        if ($user) {
            $query->where(function($q) use ($user, $sessionToken) {
                $q->where('user_id', $user->id);
                if ($sessionToken) {
                    $q->orWhere('session_token', $sessionToken);
                }
            });
        } else {
            if (!$sessionToken) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'session' => null,
                        'messages' => [],
                    ]
                ]);
            }
            $query->where('session_token', $sessionToken);
        }

        $session = $query->with(['messages'])->latest()->first();

        if ($session && $user && !$session->user_id) {
            $session->update([
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]);
        }

        if ($session) {
            $session->update(['unread_user_count' => 0]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'session' => $session,
                'messages' => $session ? $session->messages : [],
            ]
        ]);
    }

    /**
     * Send Chat Message for API
     */
    public function sendChatMessage(Request $request, $region_id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_token' => 'nullable|string|max:64',
        ]);

        $user = $request->user();
        $sessionToken = $request->session_token ?: ($request->header('X-Chat-Session-Token') ?: \Illuminate\Support\Str::random(32));
        $region = Region::findOrFail($region_id);

        $session = null;
        if ($user) {
            $session = PasarChatSession::where('region_id', $region_id)
                ->where(function($q) use ($user, $sessionToken) {
                    $q->where('user_id', $user->id)
                      ->orWhere('session_token', $sessionToken);
                })
                ->whereIn('status', ['bot', 'escalated'])
                ->latest()
                ->first();
        } else {
            $session = PasarChatSession::where('region_id', $region_id)
                ->where('session_token', $sessionToken)
                ->whereIn('status', ['bot', 'escalated'])
                ->latest()
                ->first();
        }

        if (!$session) {
            $session = PasarChatSession::create([
                'region_id' => $region_id,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : 'Pengguna Aplikasi',
                'session_token' => $sessionToken,
                'status' => 'bot',
                'last_message' => $request->message,
                'last_message_at' => now(),
                'unread_admin_count' => 0,
                'unread_user_count' => 0,
            ]);
        }

        // Simpan pesan user
        $userMsg = PasarChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'user',
            'sender_id' => $user ? $user->id : null,
            'message' => $request->message,
            'is_read' => true,
        ]);

        $botReply = null;
        $isEscalated = ($session->status === 'escalated');

        if (!$isEscalated) {
            $q = strtolower($request->message);
            $cleanRegionName = str_ireplace(['desa ', 'kelurahan '], '', $region->name);

            if (str_contains($q, 'menanyakan tentang produk') || str_contains($q, 'produk:')) {
                // Ekstraksi nama produk dari pesan
                $targetProduct = null;
                $extractedName = null;

                if (preg_match('/\[PRODUK\|.*?\|(.*?)\|.*?\]/', $request->message, $m)) {
                    $extractedName = trim($m[1]);
                } elseif (preg_match('/menanyakan tentang produk:\s*(.*?)\.\s*Apakah/i', $request->message, $m)) {
                    $extractedName = trim($m[1]);
                } elseif (preg_match('/produk:\s*(.*?)(?:\.|$)/i', $request->message, $m)) {
                    $extractedName = trim($m[1]);
                }

                if ($extractedName) {
                    $targetProduct = PasarProduk::where('region_id', $region->id)
                        ->where('nama_produk', 'like', "%{$extractedName}%")
                        ->first();
                }

                if ($targetProduct) {
                    $isHabis = ($targetProduct->stok <= 0 || strtolower($targetProduct->status) === 'habis');
                    if ($isHabis) {
                        // Cari produk sejenis yang ready di kategori yang sama
                        $similarProducts = PasarProduk::where('region_id', $region->id)
                            ->where('id', '!=', $targetProduct->id)
                            ->where('kategori', $targetProduct->kategori)
                            ->where('stok', '>', 0)
                            ->where(function($sq) {
                                $sq->whereNull('status')->orWhere('status', '!=', 'habis');
                            })
                            ->take(2)
                            ->get();

                        // Jika tidak ada di kategori yang sama, cari produk lain yang ready di toko yang sama
                        if ($similarProducts->isEmpty()) {
                            $similarProducts = PasarProduk::where('region_id', $region->id)
                                ->where('id', '!=', $targetProduct->id)
                                ->where('stok', '>', 0)
                                ->where(function($sq) {
                                    $sq->whereNull('status')->orWhere('status', '!=', 'habis');
                                })
                                ->take(2)
                                ->get();
                        }

                        $botReply = "Mohon maaf Kak, saat ini stok untuk produk *{$targetProduct->nama_produk}* sedang habis di Toko BUMDes {$cleanRegionName}.";

                        if ($similarProducts->isNotEmpty()) {
                            $botReply .= "\n\nNamun jangan khawatir, kami merekomendasikan produk sejenis lainnya yang saat ini ready dan bisa langsung Kakak pesan:";
                            foreach ($similarProducts as $sim) {
                                $simImg = $sim->foto ? asset('storage/' . $sim->foto) : '';
                                $simPrice = 'Rp ' . number_format($sim->harga, 0, ',', '.');
                                $botReply .= "\n\n[PRODUK|{$simImg}|{$sim->nama_produk}|{$simPrice}]\n(Stok Ready: {$sim->stok} {$sim->satuan})";
                            }
                            $botReply .= "\n\nKakak juga bisa klik tombol 'Chat Pengelola Toko' untuk menanyakan estimasi jadwal restock barang ini.";
                        } else {
                            $botReply .= "\n\nSilakan klik tombol 'Chat Pengelola Toko' di bawah untuk menanyakan langsung jadwal restock barang ini ke pengelola kami.";
                        }
                    } else {
                        $botReply = "Tentu Kak! Produk *{$targetProduct->nama_produk}* saat ini tercatat ready (stok: {$targetProduct->stok} {$targetProduct->satuan}) di etalase Toko BUMDes {$cleanRegionName} dan siap dipesan. Kakak bisa langsung menambahkannya ke keranjang belanja!";
                    }
                } else {
                    $botReply = "Tentu Kak! Produk tersebut saat ini tercatat ready di etalase Toko BUMDes {$cleanRegionName} dan siap dipesan. Kakak bisa langsung menambahkannya ke keranjang belanja atau tanyakan jika butuh informasi spesifikasi lainnya.";
                }
            } elseif (str_contains($q, 'stok') || str_contains($q, 'ready') || str_contains($q, 'ada')) {
                $botReply = "Stok produk di Toko BUMDes {$cleanRegionName} selalu terpantau ready dan siap segera dikemas.";
            } elseif (str_contains($q, 'kirim') || str_contains($q, 'antar') || str_contains($q, 'desa') || str_contains($q, 'kecamatan')) {
                $botReply = "Tentu bisa! Kami melayani pengiriman kurir lokal antar-desa dan antar-kecamatan se-Kabupaten Bengkalis.";
            } elseif (str_contains($q, 'ongkir') || str_contains($q, 'biaya') || str_contains($q, 'tarif')) {
                $botReply = "Ongkir dalam satu desa flat Rp 5.000 (bahkan gratis promo tertentu). Pengiriman antar-desa sekitar Rp 10.000.";
            } elseif (str_contains($q, 'cod') || str_contains($q, 'bayar') || str_contains($q, 'transfer') || str_contains($q, 'qris')) {
                $botReply = "Bisa bayar COD tunai saat kurir tiba, atau lewat QRIS dan Transfer Bank Virtual Account saat checkout.";
            } elseif (str_contains($q, 'retur') || str_contains($q, 'rusak') || str_contains($q, 'garansi') || str_contains($q, 'komplain')) {
                $botReply = "Jika produk tidak sesuai atau terdapat kerusakan saat diterima, Kakak bisa langsung mengajukan komplain & retur di menu riwayat transaksi. Kami menjamin penggantian barang baru atau pengembalian dana 100%.";
            } elseif (str_contains($q, 'lokasi') || str_contains($q, 'alamat') || str_contains($q, 'ambil')) {
                $botReply = "Kantor Toko BUMDes kami berlokasi di Desa {$cleanRegionName}. Kakak juga bisa memilih opsi 'Ambil Sendiri' saat checkout gratis ongkir.";
            } else {
                $botReply = "Maaf Kak, asisten otomatis kami belum memahami pertanyaan tersebut. Silakan klik tombol 'Chat Pengelola Toko' di atas agar pesan Kakak langsung diteruskan ke petugas Pengelola Toko BUMDes.";
            }

            $botMsg = PasarChatMessage::create([
                'session_id' => $session->id,
                'sender_type' => 'bot',
                'sender_id' => null,
                'message' => $botReply,
                'is_read' => true,
            ]);

            $session->update([
                'last_message' => $botReply,
                'last_message_at' => now(),
            ]);
        } else {
            $session->update([
                'last_message' => $request->message,
                'last_message_at' => now(),
                'unread_admin_count' => $session->unread_admin_count + 1,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'session_token' => $sessionToken,
                'session' => $session->fresh(),
                'user_message' => $userMsg,
                'bot_message' => isset($botMsg) ? $botMsg : null,
                'is_escalated' => $isEscalated,
            ]
        ]);
    }

    /**
     * Escalate to Admin for API
     */
    public function escalateToAdmin(Request $request, $region_id)
    {
        $user = $request->user();
        $sessionToken = $request->session_token ?: $request->header('X-Chat-Session-Token');
        $region = Region::findOrFail($region_id);

        $session = null;
        if ($user) {
            $session = PasarChatSession::where('region_id', $region_id)
                ->where(function($q) use ($user, $sessionToken) {
                    $q->where('user_id', $user->id)
                      ->orWhere('session_token', $sessionToken);
                })
                ->latest()
                ->first();
        } elseif ($sessionToken) {
            $session = PasarChatSession::where('region_id', $region_id)
                ->where('session_token', $sessionToken)
                ->latest()
                ->first();
        }

        if (!$session) {
            $sessionToken = $sessionToken ?: \Illuminate\Support\Str::random(32);
            $session = PasarChatSession::create([
                'region_id' => $region_id,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : 'Pengguna Aplikasi',
                'session_token' => $sessionToken,
                'status' => 'escalated',
                'last_message' => 'Meminta bantuan Pengelola Toko',
                'last_message_at' => now(),
                'unread_admin_count' => 1,
                'unread_user_count' => 0,
            ]);
        } else {
            $session->update([
                'status' => 'escalated',
                'unread_admin_count' => $session->unread_admin_count + 1,
                'last_message_at' => now(),
            ]);
        }

        $cleanRegionName = str_ireplace(['desa ', 'kelurahan '], '', $region->name);
        $escalateNotice = "Percakapan Kakak telah dialihkan ke Pengelola Toko BUMDes {$cleanRegionName}. Pengelola toko akan segera membaca dan membalas pesan Kakak di sini.";

        $botMsg = PasarChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'bot',
            'sender_id' => null,
            'message' => $escalateNotice,
            'is_read' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'session_token' => $session->session_token,
                'session' => $session->fresh(),
                'system_message' => $botMsg,
            ]
        ]);
    }
}
