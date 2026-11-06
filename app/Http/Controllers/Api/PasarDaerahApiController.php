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
    public function getOrderPayment(Request $request, int $id)
    {
        abort_unless($request->user(), 401);
        $order = PasarOrder::where('user_id', $request->user()->id)->with('region')->findOrFail($id);
        $settings = $order->store_id ? ($order->payment_settings_snapshot ?? []) : ($order->region?->settings ?? []);
        // Only expose the store's payment instructions, never its entire settings.
        $payment = $order->status === 'pending' ? [
            'bank_name' => $settings['rekening_bank'] ?? null,
            'account_number' => $settings['rekening_nomor'] ?? null,
            'account_name' => $settings['rekening_nama'] ?? null,
            'ewallet_number' => $settings['qris_ewallet_number'] ?? null,
            'qris_image_url' => !empty($settings['qris_image'])
                ? Storage::disk('public')->url($settings['qris_image']) : null,
        ] : null;

        return response()->json(['status' => 'success', 'data' => [
            'order_id' => $order->id, 'order_number' => $order->order_number, 'status' => $order->status,
            'payment_method' => $order->payment_method, 'total_amount' => $order->total_amount,
            'shipping_cost' => $order->shipping_cost, 'grand_total' => $order->grand_total,
            'has_payment_proof' => filled($order->proof_of_payment), 'payment_instructions' => $payment,
            'payment_url' => route('pasar.payment', $order->id),
        ]])->header('Cache-Control', 'private, no-store');
    }

    private function desaIdsKecamatanBengkalis(): array
    {
        $kecamatanId = Region::where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis')->value('id');
        abort_if(! $kecamatanId, 404, 'Kecamatan Bengkalis belum terdaftar.');

        return Region::where('parent_id', $kecamatanId)
            ->whereIn('type', ['desa', 'kelurahan'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function pembeliMemilikiWilayah(?User $user): bool
    {
        return app(\App\Services\MarketShippingService::class)->village($user?->region_id) !== null;
    }

    /**
     * Get all active products
     */
    public function getProducts(Request $request)
    {
        $query = PasarProduk::visibleStore()->with(['region:id,name,type,parent_id','store:id,user_id,name,status,is_open,region_id'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 'tersedia')
            ->whereIn('region_id', $this->desaIdsKecamatanBengkalis());

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
            $product->rating = $product->reviews_avg_rating ? round($product->reviews_avg_rating, 1) : null;
            $product->reviews_count = (int) ($product->reviews_count ?? 0);
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
        $product = PasarProduk::visibleStore()->with(['region:id,name,type,parent_id','store:id,user_id,name,status,is_open,region_id'])
            ->whereIn('region_id', $this->desaIdsKecamatanBengkalis())
            ->whereIn('status', ['tersedia', 'habis'])->find($id);

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
            ->whereIn('role', ['admin_desa'])
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

        if ($product->store_id) {
            $store = \App\Models\MarketStore::with('owner:id,name')->findOrFail($product->store_id);
            $sellerData = ['id'=>$store->user_id,'store_id'=>$store->id,'name'=>$store->owner->name,
                'store_name'=>$store->name,'avatar'=>$store->avatar?Storage::url($store->avatar):null,
                'store_banner'=>$store->cover?Storage::url($store->cover):null,'store_description'=>$store->description,
                'region_name'=>$product->region?->name,'is_open'=>$store->is_open,'store_url'=>route('market.store',$store->id)];
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
        PasarProduk::visibleStore()->whereIn('region_id', $this->desaIdsKecamatanBengkalis())
            ->whereIn('status', ['tersedia', 'habis'])->findOrFail($id);
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

        $product = PasarProduk::visibleStore()->whereIn('region_id', $this->desaIdsKecamatanBengkalis())->findOrFail($id);
        $user = $request->user();

        $review = app(\App\Services\MarketAfterSalesService::class)->review($user, $product->id, ['rating' => $request->rating, 'comment' => $request->comment]);

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
        abort_unless(in_array((int) $region_id, $this->desaIdsKecamatanBengkalis(), true), 404);
        $region = Region::findOrFail($region_id);
        $shipping = app(\App\Services\MarketShippingService::class)->configuration($region);
        $seller = User::where('region_id', $region_id)
            ->whereIn('role', ['admin_desa'])
            ->first();

        $products = PasarProduk::whereNull('store_id')->where('region_id', $region_id)
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
                'delivery_options' => ['pickup' => $shipping['pickup'], 'delivery' => $shipping['delivery']],
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

        $dbCategories = PasarProduk::visibleStore()->where('status', 'tersedia')
            ->whereIn('region_id', $this->desaIdsKecamatanBengkalis())
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
        if (! $this->pembeliMemilikiWilayah($user)) {
            return response()->json(['status' => 'error', 'message' => 'Lengkapi desa atau kelurahan pada akun sebelum berbelanja.'], 403);
        }
        
        $cartItems = PasarCart::with('produk')
            ->where('user_id', $user->id)
            ->whereHas('produk', fn ($query) => $query->whereIn('region_id', $this->desaIdsKecamatanBengkalis()))
            ->get();

        $formattedCart = $cartItems->map(function ($item) {
            $produk = $item->produk;
            return [
                'id' => $item->id, // Cart item ID
                'pasar_produk_id' => $item->pasar_produk_id,
                'store_key' => $produk?->storeKey(),
                'store_name' => $produk?->store?->name ?? $produk?->region?->name,
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
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            \App\Models\User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $request->validate([
                'pasar_produk_id' => 'required|exists:pasar_produks,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $user = $request->user();
            if (! $this->pembeliMemilikiWilayah($user)) {
                return response()->json(['status' => 'error', 'message' => 'Lengkapi desa atau kelurahan pada akun sebelum berbelanja.'], 403);
            }
            $produk = PasarProduk::visibleStore()->whereIn('region_id', $this->desaIdsKecamatanBengkalis())->find($request->pasar_produk_id);
            if (! $produk) {
                return response()->json(['status' => 'error', 'message' => 'Produk hanya tersedia dari desa-desa di Kecamatan Bengkalis.'], 404);
            }

            app(\App\Services\MarketBasket::class)->ensureAvailable($produk);

            if ($produk->status !== 'tersedia' || $produk->stok < $request->quantity) {
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
        });
    }

    /**
     * Update cart item quantity
     */
    public function updateCart(Request $request)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            \App\Models\User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $request->validate([
                'cart_item_id' => 'required|exists:pasar_carts,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $user = $request->user();
            if (! $this->pembeliMemilikiWilayah($user)) {
                return response()->json(['status' => 'error', 'message' => 'Lengkapi desa atau kelurahan pada akun sebelum berbelanja.'], 403);
            }
            $cartItem = PasarCart::where('id', $request->cart_item_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$cartItem) {
                return response()->json(['status' => 'error', 'message' => 'Item not found in cart'], 404);
            }

            $produk = $cartItem->produk;
            abort_unless($produk && in_array((int) $produk->region_id, $this->desaIdsKecamatanBengkalis(), true), 404);
            if ($produk->status !== 'tersedia' || $produk->stok < $request->quantity) {
                return response()->json(['status' => 'error', 'message' => 'Stok tidak mencukupi'], 400);
            }

            $cartItem->update(['quantity' => $request->quantity]);

            return response()->json(['status' => 'success', 'message' => 'Keranjang diperbarui']);
        });
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(Request $request)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            \App\Models\User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $request->validate([
                'cart_item_id' => 'required|exists:pasar_carts,id'
            ]);

            $user = $request->user();
            PasarCart::where('id', $request->cart_item_id)
                ->where('user_id', $user->id)
                ->delete();

            return response()->json(['status' => 'success', 'message' => 'Barang dihapus dari keranjang']);
        });
    }

    /**
     * Checkout process
     */
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'delivery_method' => 'required|in:Ambil Sendiri,Diantar,antar,jemput',
            'store_key' => 'nullable|string|max:50',
            'payment_method' => 'nullable|in:tunai,bank_transfer,transfer_manual,qris,COD',
            'delivery_address' => 'required_if:delivery_method,Diantar,antar|nullable|string|max:2000',
            'delivery_region_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('regions','id')->whereIn('type',['desa','kelurahan'])],
            'quoted_shipping_cost' => 'nullable|integer|min:0|max:10000000',
            'delivery_latitude' => 'nullable|numeric|between:-90,90',
            'delivery_longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string|max:2000', 'proof_of_payment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        $data['delivery_method'] = in_array($data['delivery_method'], ['Diantar', 'antar'], true) ? 'antar' : 'jemput';
        $data['payment_method'] = $data['payment_method'] ?? 'tunai';
        $data['full_name'] = $request->user()->name;
        $data['phone'] = $request->user()->phone;
        $order = app(\App\Services\MarketCheckoutService::class)->place($request->user(), $data, $request->file('proof_of_payment'));
        return response()->json(['status' => 'success', 'message' => 'Pesanan berhasil dibuat', 'order' => $order]);
    }

    /**
     * Ajukan Komplain / Retur Barang Rusak atau Tidak Sesuai
     */
    public function submitComplaint(Request $request, $orderId)
    {
        $complaint = app(\App\Services\MarketAfterSalesService::class)->submit($request->user(), (int) $orderId, $request);
        return response()->json(['status' => 'success', 'message' => 'Komplain sedang ditinjau pengelola toko.', 'data' => $complaint]);
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
        $request->validate([
            'delivery_proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);
        $order = app(\App\Services\MarketCheckoutService::class)->received($request->user(), (int) $orderId,
            $request->file('delivery_proof_image') ?? $request->file('proof_image'));
        return response()->json(['status' => 'success', 'message' => 'Pesanan dikonfirmasi diterima.', 'data' => [
            'id' => $order->id, 'order_number' => $order->order_number, 'status' => $order->status,
            'delivery_proof_image' => $order->delivery_proof_image ? url('storage/'.$order->delivery_proof_image) : null,
            'completion_time' => $order->completion_time?->format('Y-m-d H:i:s'),
        ]]);
    }

    /**
     * Get Chat History for API
     */
    public function getChatHistory(Request $request, $region_id)
    {
        abort_unless(in_array((int) $region_id, $this->desaIdsKecamatanBengkalis(), true), 404);
        $user = \Illuminate\Support\Facades\Auth::guard('sanctum')->user();
        $sessionToken = \App\Services\ChatSessionToken::read($request);

        $query = PasarChatSession::where('region_id', $region_id);

        if ($user) {
            $query->where('user_id', $user->id);
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
            $query->whereNull('user_id')->where('session_token', $sessionToken);
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

        $user = \Illuminate\Support\Facades\Auth::guard('sanctum')->user();
        abort_unless(in_array((int) $region_id, $this->desaIdsKecamatanBengkalis(), true), 404);
        $sessionToken = \App\Services\ChatSessionToken::read($request);
        $region = Region::findOrFail($region_id);

        $session = null;
        if ($user) {
            $session = PasarChatSession::where('region_id', $region_id)
                ->where('user_id', $user->id)
                ->whereIn('status', ['bot', 'escalated'])
                ->latest()
                ->first();
        } else {
            $session = PasarChatSession::where('region_id', $region_id)
                ->whereNull('user_id')->where('session_token', $sessionToken)
                ->whereIn('status', ['bot', 'escalated'])
                ->latest()
                ->first();
        }

        if (!$session) {
            $sessionToken = \Illuminate\Support\Str::random(64);
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
                    $targetProduct = PasarProduk::whereNull('store_id')->where('region_id', $region->id)
                        ->where('nama_produk', 'like', "%{$extractedName}%")
                        ->first();
                }

                if ($targetProduct) {
                    $isHabis = ($targetProduct->stok <= 0 || strtolower($targetProduct->status) === 'habis');
                    if ($isHabis) {
                        // Cari produk sejenis yang ready di kategori yang sama
                        $similarProducts = PasarProduk::whereNull('store_id')->where('region_id', $region->id)
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
                            $similarProducts = PasarProduk::whereNull('store_id')->where('region_id', $region->id)
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
                $botReply = "Tentu bisa! Kami melayani pengiriman kurir lokal antardesa di Kecamatan Bengkalis, sesuai jangkauan toko.";
            } elseif (str_contains($q, 'ongkir') || str_contains($q, 'biaya') || str_contains($q, 'tarif')) {
                $botReply = "Ongkir dalam satu desa flat Rp 5.000 (bahkan gratis promo tertentu). Pengiriman antar-desa sekitar Rp 10.000.";
            } elseif (str_contains($q, 'cod') || str_contains($q, 'bayar') || str_contains($q, 'transfer') || str_contains($q, 'qris')) {
                $botReply = "Pembayaran tersedia secara tunai, transfer manual, atau QRIS toko. Petugas akan memeriksa pembayaran manual yang dilaporkan.";
            } elseif (str_contains($q, 'retur') || str_contains($q, 'rusak') || str_contains($q, 'garansi') || str_contains($q, 'komplain')) {
                $botReply = "Jika produk tidak sesuai atau terdapat kerusakan saat diterima, Kakak bisa langsung mengajukan komplain dan retur di menu riwayat transaksi. Kami menjamin penggantian barang baru atau pengembalian dana 100%.";
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
                'session_token' => $session->session_token,
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
        abort_unless(in_array((int) $region_id, $this->desaIdsKecamatanBengkalis(), true), 404);
        $user = \Illuminate\Support\Facades\Auth::guard('sanctum')->user();
        $sessionToken = \App\Services\ChatSessionToken::read($request);
        $region = Region::findOrFail($region_id);

        $session = null;
        if ($user) {
            $session = PasarChatSession::where('region_id', $region_id)
                ->where('user_id', $user->id)
                ->latest()
                ->first();
        } elseif ($sessionToken) {
            $session = PasarChatSession::where('region_id', $region_id)
                ->whereNull('user_id')->where('session_token', $sessionToken)
                ->latest()
                ->first();
        }

        if (!$session) {
            $sessionToken = \Illuminate\Support\Str::random(64);
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
