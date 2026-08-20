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
     * Detail produk
     */
    public function show($id)
    {
        $produk = PasarProduk::with('region')->findOrFail($id);

        // Pastikan produk berasal dari desa pengguna (Hiper-Lokal)
        if (Auth::check() && Auth::user()->region_id && $produk->region_id !== Auth::user()->region_id) {
            abort(403, 'Anda hanya dapat melihat produk dari desa Anda sendiri.');
        }

        return view('users.pasar-detail', compact('produk'));
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
            'quantity' => 'required|integer|min:1'
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

        if ($cart->exists) {
            $cart->quantity += $validated['quantity'];
        } else {
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
        
        return view('users.pasar-checkout', compact('carts', 'buyer', 'seller', 'ongkir'));
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
            'payment_method' => 'required|in:tunai,transfer_manual,bank_transfer_bca,bank_transfer_bri,bank_transfer_bni,bank_transfer_mandiri,gopay,qris',
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
        $order = PasarOrder::findOrFail($id);
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
}
