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

    /**
     * Menampilkan katalog Pasar Daerah (lintas desa)
     */
    public function index(Request $request)
    {
        $query = PasarProduk::where('status', 'tersedia');

        // Filter berdasarkan kategori
        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        // Filter pencarian nama
        if ($request->filled('search')) {
            $query->where('nama_produk', 'like', '%' . $request->search . '%');
        }

        // Filter produk (Publik, bisa dilihat siapa saja)
        if ($request->filled('region_id') && $request->region_id !== 'all') {
            $query->where('region_id', $request->region_id);
        } else {
            // Default: Prioritaskan produk dari desa pengguna (jika login), jika tidak, tampilkan semua
            // atau jika public, biarkan kosong agar menampilkan semua.
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

        $produks = $query->with('region')->paginate(12)->withQueryString();
        
        $kecamatans = Region::where('type', 'kecamatan')->get();
        $desas = Region::where('type', 'desa')->get(); // Di frontend nanti difilter via JS

        return view('users.pasar-katalog', compact('produks', 'kecamatans', 'desas'));
    }

    /**
     * Halaman Profil Toko / BUMDes
     */
    public function toko($id, Request $request)
    {
        $region = Region::findOrFail($id);

        $seller = \App\Models\User::where('region_id', $id)
            ->whereIn('role', ['admin_desa', 'admin'])
            ->first();

        $query = PasarProduk::where('region_id', $id)->where('status', 'tersedia');

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
            $q->where('region_id', $id);
        })->with(['produk', 'user'])->latest()->get();

        $averageRating = $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : 0;

        $totalSales = \App\Models\PasarOrderItem::whereHas('produk', function($q) use ($id) {
            $q->where('region_id', $id);
        })->sum('quantity');

        $totalProducts = PasarProduk::where('region_id', $id)->where('status', 'tersedia')->count();

        $categories = PasarProduk::where('region_id', $id)
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
        $produk = PasarProduk::with('region')->findOrFail($id);

        // Seller / Admin Desa info
        $seller = \App\Models\User::where('region_id', $produk->region_id)
            ->whereIn('role', ['admin_desa', 'admin'])
            ->first();

        // Reviews
        $reviews = \App\Models\PasarReview::where('pasar_produk_id', $produk->id)
            ->with('user')
            ->latest()
            ->get();

        $averageRating = $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : 0;

        $soldCount = \App\Models\PasarOrderItem::where('pasar_produk_id', $produk->id)->sum('quantity');

        // Produk Terpopuler / Rekomendasi Lainnya
        $popularProducts = PasarProduk::with('region')
            ->where('id', '!=', $produk->id)
            ->where('status', 'aktif')
            ->orderByDesc('id')
            ->take(4)
            ->get();

        return view('users.pasar-detail', compact('produk', 'seller', 'reviews', 'averageRating', 'soldCount', 'popularProducts'));
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

        $produk = PasarProduk::findOrFail($id);

        \App\Models\PasarReview::create([
            'pasar_produk_id' => $produk->id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Ulasan Anda berhasil dikirim!');
    }

    /**
     * Lihat Keranjang
     */
    public function getCartItemsApi()
    {
        $user_id = Auth::id();
        $user_region_id = Auth::user()->region_id;

        $cartItems = PasarCart::where('user_id', $user_id)
            ->with(['produk' => function ($query) {
                $query->select('id', 'nama_produk', 'harga', 'foto', 'stok');
            }])
            ->whereHas('produk', function ($query) use ($user_region_id) {
                $query->where('region_id', $user_region_id);
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
        // Hanya tampilkan keranjang yang produknya berasal dari desa pengguna
        $carts = PasarCart::whereHas('produk', function($query) {
            $query->where('region_id', Auth::user()->region_id);
        })->with('produk.region')->where('user_id', Auth::id())->get();

        return view('users.pasar-keranjang', compact('carts'));
    }

    /**
     * Tambah item ke keranjang
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'pasar_produk_id' => 'required|exists:pasar_produks,id',
            'quantity' => 'required|integer|min:1',
            'is_direct_buy' => 'nullable|boolean'
        ]);

        $produk = PasarProduk::findOrFail($validated['pasar_produk_id']);

        if ($produk->region_id !== Auth::user()->region_id) {
            return response()->json([
                'success' => false,
                'message' => "Pasar Daerah ini eksklusif. Anda hanya dapat membeli barang dari desa Anda sendiri."
            ], 403);
        }

        if (!$produk->hasStock($validated['quantity'])) {
            return response()->json([
                'success' => false,
                'message' => "Stok hanya tersisa {$produk->stok}"
            ], 400);
        }

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
            'message' => 'Berhasil ditambahkan ke keranjang'
        ]);
    }

    /**
     * Update quantity keranjang
     */
    public function updateCart(Request $request)
    {
        $validated = $request->validate([
            'cart_id' => 'required|exists:pasar_carts,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $cart = PasarCart::where('id', $validated['cart_id'])->where('user_id', Auth::id())->firstOrFail();
        
        if (!$cart->produk->hasStock($validated['quantity'])) {
            return response()->json([
                'success' => false,
                'message' => "Stok tidak mencukupi"
            ], 400);
        }

        $cart->quantity = $validated['quantity'];
        $cart->save();

        return response()->json(['success' => true]);
    }

    /**
     * Hapus dari keranjang
     */
    public function removeFromCart($id)
    {
        PasarCart::where('id', $id)->where('user_id', Auth::id())->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Halaman Checkout (dari keranjang)
     */
    public function checkout()
    {
        // Hanya izinkan checkout untuk produk dari desa pengguna
        $carts = PasarCart::whereHas('produk', function($query) {
            $query->where('region_id', Auth::user()->region_id);
        })->with('produk.region')->where('user_id', Auth::id())->get();

        if ($carts->isEmpty()) {
            return redirect()->route('pasar.cart')->with('error', 'Keranjang belanja Anda kosong.');
        }

        $buyer = Region::find(Auth::user()->region_id);
        $seller = Region::find($carts->first()->produk->region_id);
        $settings = $seller ? $seller->settings : [];
        
        // Hitung Ongkir Wilayah
        $ongkir = 0;
        if ($buyer && $seller) {
            if ($buyer->id === $seller->id) {
                $ongkir = $settings['ongkir_dalam_desa'] ?? 0;
            } elseif ($buyer->parent_id === $seller->parent_id) {
                $ongkir = $settings['ongkir_luar_desa'] ?? 10000;
            } else {
                // Luar Kecamatan
                $tipe = $settings['tipe_ongkir_luar_kecamatan'] ?? 'pukul_rata';
                if ($tipe == 'pukul_rata') {
                    $ongkir = $settings['ongkir_luar_kecamatan'] ?? 25000;
                } else {
                    $khusus = $settings['ongkir_kecamatan_khusus'] ?? [];
                    if (isset($khusus[$buyer->parent_id])) {
                        $ongkir = $khusus[$buyer->parent_id];
                    } else {
                        $ongkir = -1; // Flag: Tidak melayani kecamatan ini
                    }
                }
            }
        }

        $region = $seller;
        return view('users.pasar-checkout', compact('carts', 'buyer', 'seller', 'region', 'ongkir'));
    }

    /**
     * Proses Pemesanan (Checkout Submit)
     */
    public function placeOrder(Request $request)
    {
        $carts = PasarCart::with('produk')->where('user_id', Auth::id())->get();
        if ($carts->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Keranjang kosong'], 400);
        }

        $region_id = $carts->first()->produk->region_id;
        $region = Region::find($region_id);
        $settings = $region ? $region->settings : [];

        $validated = $request->validate([
            'delivery_method' => 'required|in:antar,jemput',
            'payment_method' => 'required|in:tunai,bank_transfer,transfer_manual,bank_transfer_bca,bank_transfer_bri,bank_transfer_bni,bank_transfer_mandiri,gopay,qris,COD,virtual_account',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string',
            'delivery_address' => 'required_if:delivery_method,antar|string|nullable',
            'delivery_latitude' => 'required_if:delivery_method,antar|numeric|nullable',
            'delivery_longitude' => 'required_if:delivery_method,antar|numeric|nullable',
            'notes' => 'nullable|string',
            'proof_of_payment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Hitung total dan verifikasi stok
        $totalAmount = 0;
        foreach ($carts as $cart) {
            if (!$cart->produk->hasStock($cart->quantity)) {
                return response()->json(['success' => false, 'message' => 'Stok ' . $cart->produk->nama_produk . ' tidak mencukupi'], 400);
            }
            $totalAmount += ($cart->produk->harga * $cart->quantity);
        }

        // Hitung Ongkir
        $shippingCost = 0;
        $distanceKm = 0; // Jarak dihapus, diset 0
        if ($validated['delivery_method'] === 'antar') {
            $buyer = Region::find(Auth::user()->region_id);
            $seller = Region::find($carts->first()->produk->region_id);
            $settings = $seller ? $seller->settings : [];
            
            if ($buyer && $seller) {
                if ($buyer->id === $seller->id) {
                    $shippingCost = $settings['ongkir_dalam_desa'] ?? 0;
                } elseif ($buyer->parent_id === $seller->parent_id) {
                    $shippingCost = $settings['ongkir_luar_desa'] ?? 10000;
                } else {
                    // Luar Kecamatan
                    $tipe = $settings['tipe_ongkir_luar_kecamatan'] ?? 'pukul_rata';
                    if ($tipe == 'pukul_rata') {
                        $shippingCost = $settings['ongkir_luar_kecamatan'] ?? 25000;
                    } else {
                        $khusus = $settings['ongkir_kecamatan_khusus'] ?? [];
                        if (isset($khusus[$buyer->parent_id])) {
                            $shippingCost = $khusus[$buyer->parent_id];
                        } else {
                            return response()->json(['success' => false, 'message' => 'Toko tidak melayani pengiriman ke kecamatan Anda.'], 400);
                        }
                    }
                }
            }
        }

        $grandTotal = $totalAmount + $shippingCost;

        $paymentProofPath = null;
        if ($request->hasFile('proof_of_payment')) {
            $paymentProofPath = $request->file('proof_of_payment')->store('payment_proofs/pasar', 'public');
        }

        $orderNumber = PasarOrder::generateOrderNumber();

        \DB::beginTransaction();
        try {
            // Update GPS user jika mereka memesan dengan diantar
            if ($validated['delivery_method'] === 'antar' && $validated['delivery_latitude'] && $validated['delivery_longitude']) {
                $user = Auth::user();
                $user->latitude = $validated['delivery_latitude'];
                $user->longitude = $validated['delivery_longitude'];
                if ($validated['delivery_address']) {
                    $user->address = $validated['delivery_address'];
                }
                $user->save();
            }

            // Create Order
            $order = PasarOrder::create([
                'order_number' => $orderNumber,
                'user_id' => Auth::id(),
                'region_id' => $region_id,
                'total_amount' => $totalAmount,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'delivery_method' => $validated['delivery_method'],
                'payment_method' => $validated['payment_method'] === 'transfer_manual' ? 'Transfer Manual' : ucfirst($validated['payment_method']),
                'proof_of_payment' => $paymentProofPath,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'delivery_latitude' => $validated['delivery_latitude'] ?? null,
                'delivery_longitude' => $validated['delivery_longitude'] ?? null,
                'distance_km' => $distanceKm,
                'full_name' => $validated['full_name'],
                'phone' => $validated['phone'],
                'notes' => $validated['notes'],
                'status' => 'pending',
            ]);

            // Create Order Items & Reduce Stock
            foreach ($carts as $cart) {
                PasarOrderItem::create([
                    'pasar_order_id' => $order->id,
                    'pasar_produk_id' => $cart->pasar_produk_id,
                    'product_name' => $cart->produk->nama_produk,
                    'product_price' => $cart->produk->harga,
                    'quantity' => $cart->quantity,
                    'subtotal' => $cart->produk->harga * $cart->quantity
                ]);
                
                $cart->produk->decreaseStock($cart->quantity);
            }

            // Create Receipt
            $receipt = TransactionReceipt::create([
                'booking_type' => 'pasar',
                'booking_id' => $order->id,
                'receipt_number' => TransactionReceipt::generateReceiptNumber('pasar'),
                'user_id' => Auth::id(),
                'item_name' => count($carts) . ' Produk (' . $orderNumber . ')',
                'quantity' => 1,
                'total_amount' => $grandTotal,
                'payment_method' => $order->payment_method,
            ]);

            // Clear Cart
            PasarCart::where('user_id', Auth::id())->delete();

            // Admin Notification
            AdminNotification::create([
                'type' => 'pasar_order',
                'reference_id' => $order->id,
                'region_id' => $region_id,
                'title' => 'Pesanan Pasar Daerah Baru',
                'message' => 'Pesanan Rp ' . number_format($grandTotal, 0, ',', '.') . ' dari ' . Auth::user()->name,
                'is_read' => false,
            ]);

            // User Notification
            Notification::create([
                'user_id' => Auth::id(),
                'type' => 'status_berubah',
                'title' => 'Pesanan Dibuat',
                'message' => 'Pesanan Pasar Daerah (Order ID: ' . $order->order_number . ') berhasil dibuat.',
                'is_read' => false,
                'link' => route('user.activity'),
                'icon' => 'fas fa-shopping-bag text-blue-500'
            ]);

            // MIDTRANS INTEGRATION
            if (!in_array($validated['payment_method'], ['tunai', 'transfer_manual'])) {
                $this->processMidtrans($order, $validated['payment_method'], $grandTotal);
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat!',
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processMidtrans($order, $paymentMethod, $totalAmount)
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $paymentType = '';
        $bank = '';
        
        if (str_starts_with($paymentMethod, 'bank_transfer_')) {
            $bank = str_replace('bank_transfer_', '', $paymentMethod);
            $paymentType = $bank === 'mandiri' ? 'echannel' : 'bank_transfer';
        } else if ($paymentMethod === 'gopay') {
            $paymentType = 'gopay';
        } else if ($paymentMethod === 'qris') {
            $paymentType = 'qris';
        }

        $params = [
            'payment_type' => $paymentType,
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $totalAmount,
            ],
            'customer_details' => [
                'first_name' => $order->full_name,
                'email' => Auth::user()->email,
                'phone' => $order->phone ?? '081234567890',
            ],
            'item_details' => [
                [
                    'id' => 'PASAR-TOTAL',
                    'price' => $totalAmount,
                    'quantity' => 1,
                    'name' => 'Pesanan Pasar ' . $order->order_number
                ]
            ]
        ];

        if ($paymentType === 'bank_transfer') {
            $params['bank_transfer'] = ['bank' => $bank];
        } else if ($paymentType === 'echannel') {
            $params['echannel'] = ['bill_info1' => 'Pembayaran:', 'bill_info2' => 'Pasar Daerah'];
        }

        try {
            $coreResponse = \Midtrans\CoreApi::charge($params);
            
            $order->payment_channel = $paymentMethod;
            $order->payment_expiry_time = now()->addDay();
            
            if (isset($coreResponse->va_numbers[0]->va_number)) {
                $order->payment_va_number = $coreResponse->va_numbers[0]->va_number;
            } else if (isset($coreResponse->biller_code) && isset($coreResponse->bill_key)) {
                $order->payment_va_number = $coreResponse->biller_code . '-' . $coreResponse->bill_key;
            } else if (isset($coreResponse->actions)) {
                foreach ($coreResponse->actions as $action) {
                    if ($action->name === 'generate-qr-code') {
                        $order->payment_qr_url = $action->url;
                    }
                }
            }
            $order->save();
        } catch (\Exception $e) {
            \Log::warning('Midtrans Error: ' . $e->getMessage());
            // Mock response
            $order->payment_channel = $paymentMethod;
            $order->payment_expiry_time = now()->addDay();
            if ($paymentType === 'bank_transfer' || $paymentType === 'echannel') {
                $order->payment_va_number = rand(10000, 99999) . rand(100000, 999999);
            } else if ($paymentType === 'qris' || $paymentType === 'gopay') {
                $order->payment_qr_url = 'DUMMY_QR_CODE';
            }
            $order->save();
        }
    }

    public function payment($id)
    {
        $order = PasarOrder::with('region', 'items.produk')->findOrFail($id);
        if ($order->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403);
        }
        return view('users.pasar-payment', compact('order'));
    }

    public function simulatePayment($id)
    {
        $order = PasarOrder::findOrFail($id);
        if ($order->user_id !== Auth::id() && !Auth::user()->is_admin) abort(403);
        
        $order->status = 'confirmed';
        $order->save();
        
        return redirect()->route('pasar.payment', $order->id)->with('success', 'Simulasi pembayaran berhasil!');
    }

    /**
     * Konfirmasi Pesanan Diterima oleh User Web
     */
    public function confirmReceived(Request $request, $id)
    {
        $order = PasarOrder::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'proof_image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('proof_image')) {
            $path = $request->file('proof_image')->store('pasar_orders/proofs', 'public');
            $order->delivery_proof_image = $path;
        }

        $order->status = 'completed';
        $order->completion_time = now();
        $order->save();

        return redirect()->back()->with('success', 'Pesanan berhasil dikonfirmasi telah diterima. Terima kasih telah berbelanja!');
    }

    /**
     * Ajukan Komplain / Retur Pesanan oleh User Web
     */
    public function storeComplaint(Request $request, $id)
    {
        $order = PasarOrder::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'reason' => 'required|string|max:255',
            'solution_requested' => 'required|in:refund,replacement',
            'description' => 'nullable|string|max:1000',
            'evidence_1' => 'nullable|image|max:5120',
            'evidence_2' => 'nullable|image|max:5120',
            'evidence_3' => 'nullable|image|max:5120',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:100',
        ]);

        // Cek jika sudah ada komplain
        $existing = \App\Models\PasarComplaint::where('pasar_order_id', $order->id)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Komplain untuk pesanan ini sudah pernah diajukan.');
        }

        $ev1 = $request->hasFile('evidence_1') ? $request->file('evidence_1')->store('pasar/complaints', 'public') : null;
        $ev2 = $request->hasFile('evidence_2') ? $request->file('evidence_2')->store('pasar/complaints', 'public') : null;
        $ev3 = $request->hasFile('evidence_3') ? $request->file('evidence_3')->store('pasar/complaints', 'public') : null;

        \App\Models\PasarComplaint::create([
            'pasar_order_id' => $order->id,
            'user_id' => Auth::id(),
            'region_id' => $order->region_id,
            'reason' => $request->reason,
            'solution_requested' => $request->solution_requested,
            'description' => $request->description,
            'evidence_1' => $ev1,
            'evidence_2' => $ev2,
            'evidence_3' => $ev3,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Komplain berhasil diajukan dan sedang ditinjau oleh pihak BUMDes.');
    }

    /**
     * Mengambil riwayat percakapan chat toko
     */
    public function getChatHistory(Request $request, $regionId)
    {
        $user = Auth::user();
        $sessionToken = $request->header('X-Chat-Session-Token') ?: $request->get('session_token');

        $query = PasarChatSession::where('region_id', $regionId);

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
     * Mengirim pesan chat dari user
     */
    public function sendChatMessage(Request $request, $regionId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_token' => 'nullable|string|max:64',
        ]);

        $user = Auth::user();
        $sessionToken = $request->session_token ?: ($request->header('X-Chat-Session-Token') ?: \Illuminate\Support\Str::random(32));
        $region = Region::findOrFail($regionId);

        $session = null;
        if ($user) {
            $session = PasarChatSession::where('region_id', $regionId)
                ->where(function($q) use ($user, $sessionToken) {
                    $q->where('user_id', $user->id)
                      ->orWhere('session_token', $sessionToken);
                })
                ->whereIn('status', ['bot', 'escalated'])
                ->latest()
                ->first();
        } else {
            $session = PasarChatSession::where('region_id', $regionId)
                ->where('session_token', $sessionToken)
                ->whereIn('status', ['bot', 'escalated'])
                ->latest()
                ->first();
        }

        if (!$session) {
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
                'session_token' => $sessionToken,
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
        $sessionToken = $request->session_token ?: $request->header('X-Chat-Session-Token');
        $region = Region::findOrFail($regionId);

        $session = null;
        if ($user) {
            $session = PasarChatSession::where('region_id', $regionId)
                ->where(function($q) use ($user, $sessionToken) {
                    $q->where('user_id', $user->id)
                      ->orWhere('session_token', $sessionToken);
                })
                ->latest()
                ->first();
        } elseif ($sessionToken) {
            $session = PasarChatSession::where('region_id', $regionId)
                ->where('session_token', $sessionToken)
                ->latest()
                ->first();
        }

        if (!$session) {
            $sessionToken = $sessionToken ?: \Illuminate\Support\Str::random(32);
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
