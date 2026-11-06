<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PasarProduk;
use App\Models\PasarCart;
use App\Models\PasarOrder;
use App\Models\PasarOrderItem;
use App\Models\Region;
use App\Models\TransactionReceipt;
use App\Models\AdminNotification;
use App\Models\Notification;
use App\Models\PasarChatSession;
use App\Models\PasarChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PasarDaerahController extends Controller
{
    private function desaIdsKecamatanBengkalis(): array
    {
        $kecamatanId = Region::where('type', 'kecamatan')
            ->where('name', 'Kecamatan Bengkalis')
            ->value('id');
        abort_if(! $kecamatanId, 404, 'Kecamatan Bengkalis belum terdaftar.');

        return Region::where('parent_id', $kecamatanId)
            ->whereIn('type', ['desa', 'kelurahan'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function pembeliMemilikiWilayah(): bool
    {
        return app(\App\Services\MarketShippingService::class)->village(Auth::user()?->region_id) !== null;
    }

    /**
     * Hitung jarak dengan algoritma Haversine
     */
    private function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) return 0;

        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    /** Menampilkan katalog produk dari desa-desa di Kecamatan Bengkalis. */
    public function index(Request $request)
    {
        $desaIds = $this->desaIdsKecamatanBengkalis();
        $kecamatanFokus = Region::where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis')->firstOrFail();
        $desas = Region::whereIn('id', $desaIds)
            ->whereIn('type', ['desa', 'kelurahan'])
            ->orderBy('name')
            ->get();

        $query = PasarProduk::visibleStore()->where('status', 'tersedia')->whereIn('region_id', $desaIds);

        // Filter berdasarkan kategori
        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        // Filter pencarian nama
        if ($request->filled('search')) {
            $query->where('nama_produk', 'like', '%' . $request->search . '%');
        }

        // Filter wilayah hanya menerima desa dalam kecamatan cakupan.
        $desaDiminta = $request->input('desa_id', $request->input('region_id'));
        if ($desaDiminta && $desaDiminta !== 'all') {
            if (in_array((int) $desaDiminta, array_map('intval', $desaIds), true)) {
                $query->where('region_id', (int) $desaDiminta);
            }
        }

        if ($request->filled('sort')) {
            if ($request->sort == 'termurah') {
                $query->orderBy('harga', 'asc');
            } elseif ($request->sort == 'termahal') {
                $query->orderBy('harga', 'desc');
            } else {
                $query->latest();
            }
        } else {
            $query->latest();
        }

        $produks = $query->with(['region.parent','store'])->paginate(12)->withQueryString();

        return view('users.pasar-katalog', compact('produks', 'desas', 'kecamatanFokus'));
    }

    /**
     * Halaman Profil Toko / BUMDes
     */
    public function toko($id, Request $request)
    {
        $region = Region::findOrFail($id);
        abort_unless(
            in_array($region->type, ['desa', 'kelurahan'], true)
                && (int) $region->parent_id === (int) Region::where('type', 'kecamatan')->where('name', 'Kecamatan Bengkalis')->value('id'),
            404
        );

        $seller = \App\Models\User::where('region_id', $id)
            ->whereIn('role', ['admin_desa'])
            ->first();

        $query = PasarProduk::whereNull('store_id')->where('region_id', $id)->where('status', 'tersedia');

        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('search')) {
            $query->where('nama_produk', 'like', '%' . $request->search . '%');
        }

        if ($request->get('sort') === 'termurah') {
            $query->orderBy('harga', 'asc');
        } elseif ($request->get('sort') === 'termahal') {
            $query->orderBy('harga', 'desc');
        } else {
            $query->latest();
        }

        $produks = $query->paginate(12)->withQueryString();

        // Ulasan untuk seluruh produk dari toko desa ini
        $reviews = \App\Models\PasarReview::whereHas('produk', function($q) use ($id) {
            $q->whereNull('store_id')->where('region_id', $id);
        })->with(['produk', 'user'])->latest()->get();

        $averageRating = $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : 0;

        $totalSales = \App\Models\PasarOrderItem::whereHas('produk', function($q) use ($id) {
            $q->whereNull('store_id')->where('region_id', $id);
        })->sum('quantity');

        $totalProducts = PasarProduk::whereNull('store_id')->where('region_id', $id)->where('status', 'tersedia')->count();

        $categories = PasarProduk::whereNull('store_id')->where('region_id', $id)
            ->where('status', 'tersedia')
            ->pluck('kategori')
            ->unique()
            ->filter();

        return view('users.pasar-toko', compact(
            'region', 'seller', 'produks', 'reviews', 'averageRating', 'totalSales', 'totalProducts', 'categories'
        ));
    }

    /**
     * Detail produk
     */
    public function show($id)
    {
        $desaIds = $this->desaIdsKecamatanBengkalis();
        $produk = PasarProduk::visibleStore()->with(['region','store.owner:id,name'])->whereIn('region_id', $desaIds)->whereIn('status', ['tersedia', 'habis'])->findOrFail($id);

        if ($produk->store_id) {
            $store=$produk->store;
            $canReview=app(\App\Services\MarketAfterSalesService::class)->canReview(Auth::user(),$produk->id);
            $reviews=$produk->reviews()->with('user:id,name')->latest()->paginate(10);
            return view('market.product',compact('produk','store','canReview','reviews'));
        }

        // Seller / Admin Desa info
        $seller = \App\Models\User::where('region_id', $produk->region_id)
            ->whereIn('role', ['admin_desa'])
            ->first();

        // Reviews
        $reviews = \App\Models\PasarReview::where('pasar_produk_id', $produk->id)
            ->with('user')
            ->latest()
            ->get();

        $averageRating = $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : 0;

        $soldCount = \App\Models\PasarOrderItem::where('pasar_produk_id', $produk->id)->whereHas('order', fn ($q) => $q->where('status', 'completed'))->sum('quantity');

        // Produk Terpopuler / Rekomendasi Lainnya
        $popularProducts = PasarProduk::visibleStore()->with('region')
            ->whereIn('region_id', $desaIds)
            ->where('id', '!=', $produk->id)
            ->where('status', 'tersedia')
            ->orderByDesc('id')
            ->take(4)
            ->get();

        $canReview = app(\App\Services\MarketAfterSalesService::class)->canReview(Auth::user(), $produk->id);
        return view('users.pasar-detail', compact('produk', 'seller', 'reviews', 'averageRating', 'soldCount', 'popularProducts', 'canReview'));
    }

    /**
     * Simpan Ulasan Produk
     */
    public function storeReview(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $produk = PasarProduk::whereIn('region_id', $this->desaIdsKecamatanBengkalis())->findOrFail($id);

        app(\App\Services\MarketAfterSalesService::class)->review(Auth::user(), $produk->id, ['rating' => $request->rating, 'comment' => $request->comment]);

        return back()->with('success', 'Ulasan Anda berhasil dikirim!');
    }

    /**
     * Lihat Keranjang
     */
    public function getCartItemsApi()
    {
        abort_unless($this->pembeliMemilikiWilayah(), 403, 'Lengkapi desa atau kelurahan pada akun sebelum berbelanja.');
        $user_id = Auth::id();
        $desaIds = $this->desaIdsKecamatanBengkalis();

        $cartItems = PasarCart::where('user_id', $user_id)
            ->with(['produk' => function ($query) {
                $query->select('id', 'nama_produk', 'harga', 'foto', 'stok');
            }])
            ->whereHas('produk', function ($query) use ($desaIds) {
                $query->whereIn('region_id', $desaIds);
            })
            ->get();

        $subtotal = $cartItems->sum(function ($item) {
            return $item->produk->harga * $item->quantity;
        });

        $itemsFormatted = $cartItems->map(function ($item) {
            return [
                'id' => $item->id,
                'pasar_produk_id' => $item->pasar_produk_id,
                'quantity' => $item->quantity,
                'nama_produk' => $item->produk->nama_produk,
                'harga' => $item->produk->harga,
                'foto_url' => $item->produk->foto ? \Illuminate\Support\Facades\Storage::url($item->produk->foto) : null,
                'stok' => $item->produk->stok
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $itemsFormatted,
            'subtotal' => $subtotal,
            'total_items' => $cartItems->sum('quantity')
        ]);
    }

    public function cart()
    {
        abort_unless($this->pembeliMemilikiWilayah(), 403, 'Lengkapi desa atau kelurahan pada akun sebelum berbelanja.');
        // Keranjang hanya dapat berisi produk dari Kecamatan Bengkalis.
        $carts = PasarCart::whereHas('produk', function($query) {
            $query->whereIn('region_id', $this->desaIdsKecamatanBengkalis());
        })->with(['produk.region','produk.store'])->where('user_id', Auth::id())->get();

        return view('market.cart', compact('carts'));
    }

    /**
     * Tambah item ke keranjang
     */
    public function addToCart(Request $request)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            \App\Models\User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
            abort_unless($this->pembeliMemilikiWilayah(), 403, 'Lengkapi desa atau kelurahan pada akun sebelum berbelanja.');
            $validated = $request->validate([
                'pasar_produk_id' => 'required|exists:pasar_produks,id',
                'quantity' => 'required|integer|min:1',
                'is_direct_buy' => 'nullable|boolean'
            ]);

            $produk = PasarProduk::findOrFail($validated['pasar_produk_id']);

            $desaIds = $this->desaIdsKecamatanBengkalis();
            if (! in_array((int) $produk->region_id, $desaIds, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk hanya tersedia dari desa-desa di Kecamatan Bengkalis.'
                ], 403);
            }

            if ($produk->status !== 'tersedia' || !$produk->hasStock($validated['quantity'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok hanya tersisa {$produk->stok}"
                ], 400);
            }

            app(\App\Services\MarketBasket::class)->ensureAvailable($produk);

            $cart = PasarCart::firstOrNew([
                'user_id' => Auth::id(),
                'pasar_produk_id' => $validated['pasar_produk_id']
            ]);

            $isDirectBuy = $request->boolean('is_direct_buy');

            if ($cart->exists && !$isDirectBuy) {
                $cart->quantity += $validated['quantity'];
            } else {
                // Jika Beli Langsung (Direct Buy) atau item baru, set sesuai kuantitas yang dipilih pembeli saat ini
                $cart->quantity = $validated['quantity'];
            }

            if (!$produk->hasStock($cart->quantity)) {
                return response()->json([
                    'success' => false,
                    'message' => "Total pesanan melebihi stok yang ada."
                ], 400);
            }

            $cart->save();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil ditambahkan ke keranjang', 'checkout_url'=>route('pasar.checkout',['store_key'=>$produk->storeKey()])
            ]);
        });
    }

    /**
     * Update quantity keranjang
     */
    public function updateCart(Request $request)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            \App\Models\User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
            abort_unless($this->pembeliMemilikiWilayah(), 403, 'Lengkapi desa atau kelurahan pada akun sebelum berbelanja.');
            $validated = $request->validate([
                'cart_id' => 'required|exists:pasar_carts,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $cart = PasarCart::where('id', $validated['cart_id'])->where('user_id', Auth::id())->firstOrFail();

            abort_unless(in_array((int) $cart->produk->region_id, $this->desaIdsKecamatanBengkalis(), true), 404);
            if ($cart->produk->status !== 'tersedia' || !$cart->produk->hasStock($validated['quantity'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok tidak mencukupi"
                ], 400);
            }

            $cart->quantity = $validated['quantity'];
            $cart->save();

            return response()->json(['success' => true]);
        });
    }

    /**
     * Hapus dari keranjang
     */
    public function removeFromCart($id)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            \App\Models\User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
            PasarCart::where('id', $id)->where('user_id', Auth::id())->delete();
            return response()->json(['success' => true]);
        });
    }

    /**
     * Halaman Checkout (dari keranjang)
     */
    public function checkout(Request $request)
    {
        abort_unless($this->pembeliMemilikiWilayah(), 403, 'Lengkapi desa atau kelurahan pada akun sebelum berbelanja.');

        // Hanya izinkan checkout untuk produk dari desa-desa Kecamatan Bengkalis.
        $carts = PasarCart::whereHas('produk', function($query) {
            $query->whereIn('region_id', $this->desaIdsKecamatanBengkalis());
        })->with(['produk.region','produk.store'])->where('user_id', Auth::id());
        $carts=app(\App\Services\MarketBasket::class)->select($carts,$request->input('store_key'))->get();

        if ($carts->isEmpty()) {
            return redirect()->route('pasar.cart')->with('error', 'Keranjang belanja Anda kosong.');
        }

        if ($carts->map(fn($cart)=>$cart->produk->storeKey())->unique()->count() !== 1) return redirect()->route('pasar.cart')->with('success','Pilih toko yang ingin dipesan. Barang toko lain tetap tersimpan.');
        $storeKey=$carts->first()->produk->storeKey();
        $marketStore=$carts->first()->produk->store;
        app(\App\Services\MarketBasket::class)->ensureAvailable($carts->first()->produk);

        $buyer = app(\App\Services\MarketCheckoutService::class)->village(Auth::user());
        $seller = $marketStore ? $marketStore->shippingRegion() : Region::findOrFail($carts->first()->produk->region_id);
        $shippingConfig = app(\App\Services\MarketShippingService::class)->configuration($seller);
        $shippingRegions = Region::whereIn('type', ['desa', 'kelurahan'])->whereHas('parent', fn ($q) => $q->where('type', 'kecamatan'))
            ->with('parent:id,name')->orderBy('name')->get(['id','name','parent_id','type']);

        $region = $seller;
        $alamatTersimpan = \App\Models\AlamatWarga::milik(Auth::id())
            ->with('region')
            ->orderByDesc('is_utama')
            ->orderBy('id')
            ->get();
        return view('users.pasar-checkout', compact('carts', 'buyer', 'seller', 'region', 'shippingConfig', 'shippingRegions', 'alamatTersimpan','storeKey','marketStore'));
    }

    /**
     * Proses Pemesanan (Checkout Submit)
     */
    public function placeOrder(Request $request)
    {
        $validated = $request->validate([
            'delivery_method' => 'required|in:antar,jemput',
            'store_key' => 'nullable|string|max:50',
            'payment_method' => 'required|in:tunai,bank_transfer,transfer_manual,qris,COD',
            'full_name' => 'required|string|max:255', 'phone' => 'required|string|max:30',
            'delivery_address' => 'required_if:delivery_method,antar|nullable|string|max:2000',
            'delivery_region_id' => ['required_if:delivery_method,antar', 'nullable', 'integer', \Illuminate\Validation\Rule::exists('regions','id')->whereIn('type',['desa','kelurahan'])],
            'quoted_shipping_cost' => 'nullable|integer|min:0|max:10000000',
            'delivery_latitude' => 'nullable|required_with:delivery_longitude|numeric|between:-90,90',
            'delivery_longitude' => 'nullable|required_with:delivery_latitude|numeric|between:-180,180',
            'notes' => 'nullable|string|max:2000', 'proof_of_payment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
        $order = app(\App\Services\MarketCheckoutService::class)->place(Auth::user(), $validated, $request->file('proof_of_payment'));
        return response()->json(['success' => true, 'message' => 'Pesanan berhasil dibuat!', 'order_id' => $order->id]);
    }

    public function payment($id)
    {
        $order = PasarOrder::with('region', 'items.produk')->findOrFail($id);
        if ($order->store_id) $order->region->settings = $order->payment_settings_snapshot ?? [];
        abort_unless(app(\App\Services\BookingAccess::class)->canView($order, 'pasar', Auth::user()), 403);
        return view('users.pasar-payment', compact('order'));
    }

    public function simulatePayment($id)
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $order = PasarOrder::findOrFail($id);
        abort_unless(app(\App\Services\BookingAccess::class)->canView($order, 'pasar', Auth::user()), 403);

        $order->status = 'confirmed';
        $order->save();

        return redirect()->route('pasar.payment', $order->id)->with('success', 'Simulasi pembayaran berhasil!');
    }

    /**
     * Konfirmasi Pesanan Diterima oleh User Web
     */
    public function confirmReceived(Request $request, $id)
    {
        $request->validate(['proof_image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120']);
        app(\App\Services\MarketCheckoutService::class)->received(Auth::user(), (int) $id, $request->file('proof_image'));
        return back()->with('success', 'Pesanan berhasil dikonfirmasi telah diterima.');
    }

    /**
     * Ajukan Komplain / Retur Pesanan oleh User Web
     */
    public function storeComplaint(Request $request, $id)
    {
        app(\App\Services\MarketAfterSalesService::class)->submit(Auth::user(), (int) $id, $request);
        return back()->with('success', 'Komplain berhasil diajukan dan sedang ditinjau pengelola toko.');
    }

    /**
     * Mengambil riwayat percakapan chat toko
     */
    public function getChatHistory(Request $request, $regionId)
    {
        $user = Auth::user();
        $sessionToken = \App\Services\ChatSessionToken::read($request);

        $query = PasarChatSession::where('region_id', $regionId);

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
     * Mengirim pesan chat dari user
     */
    public function sendChatMessage(Request $request, $regionId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_token' => 'nullable|string|max:64',
        ]);

        $user = Auth::user();
        $sessionToken = \App\Services\ChatSessionToken::read($request);
        $region = Region::findOrFail($regionId);

        $session = null;
        if ($user) {
            $session = PasarChatSession::where('region_id', $regionId)
                ->where('user_id', $user->id)
                ->whereIn('status', ['bot', 'escalated'])
                ->latest()
                ->first();
        } else {
            $session = PasarChatSession::where('region_id', $regionId)
                ->whereNull('user_id')->where('session_token', $sessionToken)
                ->whereIn('status', ['bot', 'escalated'])
                ->latest()
                ->first();
        }

        if (!$session) {
            $sessionToken = \Illuminate\Support\Str::random(64);
            $session = PasarChatSession::create([
                'region_id' => $regionId,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : 'Pengunjung Web',
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
                            $botReply .= "\n\nKakak juga bisa klik tombol 'Chat Pengelola Toko' di bawah untuk menanyakan estimasi jadwal restock barang ini.";
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
                $botReply = "Maaf Kak, asisten otomatis kami belum memahami pertanyaan tersebut. Silakan klik tombol 'Chat Pengelola Toko' di bawah agar pesan Kakak langsung diteruskan ke petugas Pengelola Toko BUMDes.";
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
     * Meneruskan chat ke Pengelola Toko (Eskalasi)
     */
    public function escalateToAdmin(Request $request, $regionId)
    {
        $user = Auth::user();
        $sessionToken = \App\Services\ChatSessionToken::read($request);
        $region = Region::findOrFail($regionId);

        $session = null;
        if ($user) {
            $session = PasarChatSession::where('region_id', $regionId)
                ->where('user_id', $user->id)
                ->latest()
                ->first();
        } elseif ($sessionToken) {
            $session = PasarChatSession::where('region_id', $regionId)
                ->whereNull('user_id')->where('session_token', $sessionToken)
                ->latest()
                ->first();
        }

        if (!$session) {
            $sessionToken = \Illuminate\Support\Str::random(64);
            $session = PasarChatSession::create([
                'region_id' => $regionId,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : 'Pengunjung Web',
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
