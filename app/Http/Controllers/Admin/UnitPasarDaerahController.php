<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasarProduk;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UnitPasarDaerahController extends Controller
{
    use \App\Traits\ChecksStaffDelegation;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($splash = $this->checkDelegation($request, 'pasar_daerah', 'Pasar Daerah')) {
            return $splash;
        }

        $admin = Auth::user();
        $produks = PasarProduk::whereNull('store_id')->where('region_id', $admin->region_id)->latest()->get();
        
        $region = Region::find($admin->region_id);
        $settings = $region ? $region->settings : [];
        $shippingConfig = $region ? app(\App\Services\MarketShippingService::class)->configuration($region) : [];
        $shippingRegions = Region::whereIn('type', ['desa', 'kelurahan', 'kecamatan'])->with('parent:id,name')->orderBy('name')->get(['id', 'name', 'type', 'parent_id']);

        // --- Data Pesanan ---
        $status = $request->get('status', 'all');
        $queryPesanan = \App\Models\PasarOrder::whereNull('store_id')->where('region_id', $admin->region_id)->with('items.produk', 'user')->latest();
        if ($status !== 'all') {
            $queryPesanan->where('status', $status);
        }
        $pesanans = $queryPesanan->get();

        // --- Data Laporan ---
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $queryLaporan = \App\Models\PasarOrder::whereNull('store_id')->where('region_id', $admin->region_id)
            ->whereIn('status', ['completed'])
            ->whereHas('paymentTransactions', fn ($q) => $q->where('status', 'verified'))
            ->with('items.produk', 'user')
            ->latest();
            
        if ($startDate && $endDate) {
            $queryLaporan->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        
        $laporans = $queryLaporan->get();
        $totalPendapatan = $laporans->sum('grand_total');

        $tab = $request->get('tab', 'produk');

        // Data Ulasan
        $reviews = \App\Models\PasarReview::whereHas('produk', function($query) use ($admin) {
                $query->whereNull('store_id')->where('region_id', $admin->region_id);
            })
            ->with(['produk', 'user'])
            ->latest()
            ->get();

        // Data Komplain & Retur
        $complaints = \App\Models\PasarComplaint::whereHas('order',fn($q)=>$q->whereNull('store_id'))->where('region_id', $admin->region_id)
            ->with(['order.items.produk', 'user', 'handler'])
            ->latest()
            ->get();

        // Data Chat Pengelola Toko
        $chats = \App\Models\PasarChatSession::where('region_id', $admin->region_id)
            ->with(['user'])
            ->orderBy('last_message_at', 'desc')
            ->get();
        $totalUnreadChats = $chats->sum('unread_admin_count');

        return view('admin.unit.pasar_daerah.index', compact(
            'produks', 'settings', 'shippingConfig', 'shippingRegions', 'pesanans', 'status', 'laporans', 'startDate', 'endDate', 'totalPendapatan', 'tab', 'admin', 'reviews', 'complaints', 'chats', 'totalUnreadChats'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // 5 Kategori Fixed
        $kategoriList = [
            'Hasil Tani & Bumi',
            'Pangan & Olahan',
            'Material & Bangunan',
            'Kerajinan & Kesenian',
            'Lainnya'
        ];
        
        return view('admin.unit.pasar_daerah.create', compact('kategoriList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'nullable|string',
            'status' => 'required|in:tersedia,habis,nonaktif',
            'kategori' => 'nullable|string',
            'lokasi' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $validated['region_id'] = Auth::user()->region_id;

        // Handle File Uploads
        foreach (['foto', 'foto_2', 'foto_3'] as $fotoField) {
            if ($request->hasFile($fotoField)) {
                $validated[$fotoField] = $request->file($fotoField)->store('pasar_produks', 'public');
            }
        }

        $produk = PasarProduk::create($validated);
        if ($produk->status === 'tersedia') {
            \App\Services\NotificationService::broadcastNewProduct('Pasar Daerah', $produk->nama_produk, $produk->region_id, route('pasar.show', $produk->id));
        }

        return redirect()->route('admin.unit.pasar_daerah.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $produk = PasarProduk::whereNull('store_id')->where('id', $id)->where('region_id', Auth::user()->region_id)->firstOrFail();
        return redirect()->route('admin.unit.pasar_daerah.edit', $produk->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $produk = PasarProduk::whereNull('store_id')->where('id', $id)->where('region_id', Auth::user()->region_id)->firstOrFail();
        
        $kategoriList = [
            'Hasil Tani & Bumi',
            'Pangan & Olahan',
            'Material & Bangunan',
            'Kerajinan & Kesenian',
            'Lainnya'
        ];
        
        return view('admin.unit.pasar_daerah.edit', compact('produk', 'kategoriList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $produk = PasarProduk::whereNull('store_id')->where('id', $id)->where('region_id', Auth::user()->region_id)->firstOrFail();

        $validated = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'nullable|string',
            'status' => 'required|in:tersedia,habis,nonaktif',
            'kategori' => 'nullable|string',
            'lokasi' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        foreach (['foto', 'foto_2', 'foto_3'] as $fotoField) {
            if ($request->hasFile($fotoField)) {
                if ($produk->$fotoField) {
                    Storage::disk('public')->delete($produk->$fotoField);
                }
                $validated[$fotoField] = $request->file($fotoField)->store('pasar_produks', 'public');
            }
        }

        $previouslyAvailable = $produk->status === 'tersedia';
        $produk->update($validated);
        if (!$previouslyAvailable && $produk->status === 'tersedia') {
            \App\Services\NotificationService::broadcastNewProduct('Pasar Daerah', $produk->nama_produk, $produk->region_id, route('pasar.show', $produk->id));
        }

        return redirect()->route('admin.unit.pasar_daerah.index')->with('success', 'Produk berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $produk = PasarProduk::whereNull('store_id')->where('id', $id)->where('region_id', Auth::user()->region_id)->firstOrFail();
        
        if ($produk->orderItems()->exists()) {
            return back()->with('error', 'Produk memiliki riwayat pesanan. Ubah status menjadi nonaktif agar riwayat tetap tersedia.');
        }

        // Hapus file
        foreach (['foto', 'foto_2', 'foto_3'] as $fotoField) {
            if ($produk->$fotoField) {
                Storage::disk('public')->delete($produk->$fotoField);
            }
        }

        $produk->delete();

        return redirect()->route('admin.unit.pasar_daerah.index')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Update SOP and Ongkir settings
     */
    public function updateSop(Request $request)
    {
        abort_unless(Auth::user()?->hasUnitPermission('pasar_daerah') && Auth::user()->region_id, 403);
        $request->validate([
            'sop_pasar' => 'nullable|string',
            'shipping' => 'sometimes|array',
            'ongkir_dalam_desa' => 'required_without:shipping|nullable|integer|min:0|max:10000000',
            'ongkir_luar_desa' => 'required_without:shipping|nullable|integer|min:0|max:10000000',
            // Payment settings
            'enable_cod' => 'nullable',
            'enable_bank_transfer' => 'nullable',
            'rekening_bank' => 'nullable|string|max:100',
            'rekening_nomor' => 'nullable|string|max:50',
            'rekening_nama' => 'nullable|string|max:100',
            'enable_qris' => 'nullable',
            'qris_image' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
            'qris_ewallet_name' => 'nullable|string|max:100',
            'qris_ewallet_number' => 'nullable|string|max:50',
            'qris_ewallet_holder' => 'nullable|string|max:100',
        ]);

        $region = Region::findOrFail(Auth::user()->region_id);
        
        $settings = $region->settings ?? [];
        $settings['sop_pasar'] = $request->input('sop_pasar');
        if ($request->has('shipping')) {
            $settings['market_shipping'] = app(\App\Services\MarketShippingService::class)->validateSettings($request->input('shipping'));
            if (!$settings['market_shipping']['delivery'] && !$request->has('shipping.overrides')) {
                // Menutup layanan antar tidak menghapus tarif khusus yang disembunyikan formulir.
                $settings['market_shipping']['overrides'] = app(\App\Services\MarketShippingService::class)->configuration($region)['overrides'];
            }
        } else {
            $settings['ongkir_dalam_desa'] = $request->input('ongkir_dalam_desa');
            $settings['ongkir_luar_desa'] = $request->input('ongkir_luar_desa');
        }
        
        unset($settings['tipe_ongkir_luar_kecamatan'], $settings['ongkir_luar_kecamatan'], $settings['ongkir_kecamatan_khusus']);

        // Simpan Pengaturan Pembayaran Toko
        $settings['enable_cod'] = $request->has('enable_cod');
        $settings['enable_bank_transfer'] = $request->has('enable_bank_transfer');
        $settings['rekening_bank'] = $request->input('rekening_bank');
        $settings['rekening_nomor'] = $request->input('rekening_nomor');
        $settings['rekening_nama'] = $request->input('rekening_nama');
        
        $settings['enable_qris'] = $request->has('enable_qris');
        if ($request->hasFile('qris_image')) {
            if (!empty($settings['qris_image'])) {
                Storage::disk('public')->delete($settings['qris_image']);
            }
            $settings['qris_image'] = $request->file('qris_image')->store('store_qris', 'public');
        }
        $settings['qris_ewallet_name'] = $request->input('qris_ewallet_name');
        $settings['qris_ewallet_number'] = $request->input('qris_ewallet_number');
        $settings['qris_ewallet_holder'] = $request->input('qris_ewallet_holder');
        
        $region->settings = $settings;
        $region->save();

        return redirect()->back()->with('success', 'Pengaturan Toko & Metode Pembayaran berhasil diperbarui.');
    }

    /**
     * Display a listing of orders (Pesanan).
     */
    public function pesanan(Request $request)
    {
        return redirect()->route('admin.unit.pasar_daerah.index', ['tab' => 'pesanan']);
    }

    /**
     * Display order detail.
     */
    public function pesananShow($id)
    {
        $admin = Auth::user();
        $pesanan = \App\Models\PasarOrder::whereNull('store_id')->where('id', $id)
            ->where('region_id', $admin->region_id)
            ->with(['items.produk', 'user', 'complaint'])
            ->firstOrFail();
        
        return view('admin.unit.pasar_daerah.pesanan_show', compact('pesanan'));
    }

    /**
     * Review a cancellation within the operator's own market region.
     */
    public function handleCancellation(Request $request, $id, $action)
    {
        abort_unless(in_array($action, ['approve', 'reject']), 404);
        $admin = $request->user();
        abort_unless($admin && $admin->isAdmin() && $admin->hasUnitPermission('pasar_daerah') && $admin->region_id, 403);
        $data = $request->validate([
            'admin_response' => [$action === 'reject' ? 'required' : 'nullable', 'string', 'max:500'],
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id, $action, $admin, $data) {
            $order = \App\Models\PasarOrder::whereNull('store_id')->where('region_id', $admin->region_id)->lockForUpdate()->findOrFail($id);
            if ($order->cancellation_status !== 'pending' || in_array($order->status, ['cancelled', 'rejected', 'completed'])) {
                return response()->json(['success' => false, 'message' => 'Permintaan pembatalan sudah diproses atau pesanan sudah selesai.'], 409);
            }
            $order->cancellation_status = $action === 'approve' ? 'approved' : 'rejected';
            $order->admin_cancellation_response = $data['admin_response'] ?? null;
            if ($action === 'approve') {
                foreach ($order->items()->orderBy('pasar_produk_id')->get() as $item) {
                    $product = $item->produk()->lockForUpdate()->first();
                    if ($product) {
                        $product->increaseStock($item->quantity);
                    }
                }
                \App\Models\WalletTransaction::batalkanDanRefund('pasar', $order->id, 'Pembatalan pesanan disetujui pengelola.');
                $order->status = 'cancelled';
            }
            $order->save();
            $message = $action === 'approve' ? 'Permintaan pembatalan disetujui.' : 'Permintaan pembatalan ditolak.';
            \App\Models\Notification::create([
                'user_id' => $order->user_id,
                'admin_id' => $admin->id,
                'type' => 'status_berubah',
                'title' => $message,
                'message' => 'Pesanan #' . $order->order_number . ': ' . $message . ' ' . ($data['admin_response'] ?? ''),
                'link' => \App\Services\NotificationService::orderLink($order, 'pasar'),
            ]);
            \App\Models\ActivityLog::create([
                'user_id' => $admin->id, 'action' => 'Cancellation Review',
                'description' => $message . ' Pesanan pasar #' . $order->order_number,
                'ip_address' => $request->ip(),
            ]);
            return response()->json(['success' => true, 'message' => $message]);
        });
    }

    public function pesananUpdate(Request $request, $id)
    {
        $admin = Auth::user();
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,confirmed,in_delivery,delivered,completed,cancelled,rejected,payment_received',
        ]);
        \Illuminate\Support\Facades\DB::transaction(function () use ($id,$admin,$validated) {
            $pesanan = \App\Models\PasarOrder::whereNull('store_id')->where('region_id',$admin->region_id)->lockForUpdate()->findOrFail($id);
            if ($validated['status'] === 'payment_received') {
                abort_unless($pesanan->isCashPayment() && in_array($pesanan->status, ['confirmed', 'in_delivery', 'delivered', 'completed'], true), 409,
                    'Pembayaran tunai belum dapat dikonfirmasi untuk status pesanan ini.');
                abort_if($pesanan->complaint()->where('status', 'pending')->exists(), 409, 'Tangani komplain pesanan terlebih dahulu.');
                if ($pesanan->paymentIsVerified()) return;
                abort_unless($pesanan->paymentTransactions()->where('source', 'manual')->where('status', 'pending')->exists(), 409,
                    'Catatan pembayaran tidak tersedia untuk dikonfirmasi.');
                \App\Models\WalletTransaction::tandaiPembayaranManualDiterima('pasar', $pesanan->id, $admin->id);
                \App\Models\Notification::create([
                    'user_id' => $pesanan->user_id, 'type' => 'status_berubah', 'title' => 'Pembayaran Tunai Diterima',
                    'message' => 'Pembayaran pesanan #'.$pesanan->order_number.' telah dikonfirmasi pengelola.',
                    'link' => \App\Services\NotificationService::orderLink($pesanan, 'pasar'), 'is_read' => false,
                ]);
                return;
            }
            if ($pesanan->status === $validated['status']) return;
            $allowed = [
                'pending'=>$pesanan->isCashPayment() ? ['paid','confirmed','cancelled','rejected'] : ['paid','cancelled','rejected'],
                'paid'=>['confirmed','cancelled'],
                'confirmed'=>['in_delivery','delivered','cancelled'], 'in_delivery'=>['delivered'], 'delivered'=>['completed'],
            ];
            abort_unless(in_array($validated['status'], $allowed[$pesanan->status] ?? [], true), 409,
                'Status pesanan sudah berubah atau langkah ini tidak tersedia.');
            abort_if($pesanan->complaint()->where('status','pending')->exists(), 409, 'Tangani komplain pesanan terlebih dahulu.');
            if ($validated['status'] === 'paid') {
                \App\Models\WalletTransaction::tandaiPembayaranManualDiterima('pasar', $pesanan->id, $admin->id);
            }
            if (in_array($validated['status'], ['cancelled','rejected'], true)) {
                foreach ($pesanan->items()->orderBy('pasar_produk_id')->get() as $item) {
                    $product = $item->produk()->lockForUpdate()->first();
                    if ($product) $product->increaseStock($item->quantity);
                }
                \App\Models\WalletTransaction::batalkanDanRefund('pasar', $pesanan->id, 'Pesanan '.$validated['status'].' oleh pengelola.');
            }
            if ($validated['status'] === 'confirmed') $pesanan->confirmed_at = now();
            if ($validated['status'] === 'completed') $pesanan->completion_time = now();
            $pesanan->status = $validated['status'];
            if ($validated['status'] === 'delivered' && !$pesanan->delivered_at) $pesanan->delivered_at = now();
            $pesanan->handled_by = $admin->id;
            $pesanan->save();
            $labels = ['pending'=>'Menunggu pembayaran','paid'=>'Sudah dibayar','confirmed'=>'Dikonfirmasi',
                'in_delivery'=>'Dalam pengiriman','delivered'=>'Telah diantar','completed'=>'Selesai','cancelled'=>'Dibatalkan','rejected'=>'Ditolak'];
            \App\Models\Notification::create([
                'user_id'=>$pesanan->user_id, 'type'=>'status_berubah', 'title'=>'Pembaruan Pesanan Pasar Daerah',
                'message'=>'Pesanan #'.$pesanan->order_number.': '.$labels[$pesanan->status].'.',
                'link'=>\App\Services\NotificationService::orderLink($pesanan,'pasar'), 'is_read'=>false, 'sent_at'=>now(),
            ]);
        }, 3);

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    /**
     * Display transaction reports (Laporan).
     */
    public function laporan(Request $request)
    {
        return redirect()->route('admin.unit.pasar_daerah.index', ['tab' => 'laporan']);
    }
    /**
     * Tampilkan Halaman Profil Toko
     */
    public function profile()
    {
        return redirect()->route('admin.unit.pasar_daerah.index', ['tab' => 'profil']);
    }

    /**
     * Update Profil Toko
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'store_description' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'store_banner' => 'nullable|image|mimes:jpeg,png,jpg|max:3072',
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('store_banner')) {
            if ($user->store_banner) {
                Storage::disk('public')->delete($user->store_banner);
            }
            $user->store_banner = $request->file('store_banner')->store('store_banners', 'public');
        }

        $user->store_description = $validated['store_description'];
        $user->save();

        return redirect()->back()->with('success', 'Profil Toko berhasil diperbarui.');
    }

    /**
     * Tampilkan Halaman Ulasan
     */
    public function reviews()
    {
        return redirect()->route('admin.unit.pasar_daerah.index', ['tab' => 'ulasan']);
    }

    /**
     * Balas Ulasan
     */
    public function replyReview(Request $request, $id)
    {
        $admin = Auth::user();
        
        $request->validate([
            'reply' => 'required|string',
        ]);

        $review = \App\Models\PasarReview::whereHas('produk', function($query) use ($admin) {
            $query->whereNull('store_id')->where('region_id', $admin->region_id);
        })->findOrFail($id);

        $review->reply = $request->reply;
        $review->replied_at = now();
        $review->save();

        return redirect()->back()->with('success', 'Balasan ulasan berhasil dikirim.');
    }

    /**
     * Proses Tindakan Komplain / Retur Barang dari Pembeli
     */
    public function handleComplaint(Request $request, $id)
    {
        $data = $request->validate(['status' => 'required|in:approved_replacement,approved_refund,rejected', 'admin_response' => 'required|string|max:1000']);
        app(\App\Services\MarketAfterSalesService::class)->decide(Auth::user(), (int) $id, $data);
        return back()->with('success', 'Keputusan komplain berhasil dicatat.');
    }

    /**
     * Detail Pesan Chat untuk Admin
     */
    public function chats()
    {
        return redirect()->route('admin.unit.pasar_daerah.index', ['tab' => 'chat']);
    }

    public function chatMessages($id)
    {
        $admin = Auth::user();
        $session = \App\Models\PasarChatSession::where('region_id', $admin->region_id)->findOrFail($id);
        
        // Reset unread count for admin
        $session->update(['unread_admin_count' => 0]);
        
        $messages = $session->messages()->with('sender')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'session' => $session,
                'messages' => $messages,
            ]
        ]);
    }

    /**
     * Balas Chat dari Admin ke Pembeli
     */
    public function replyChat(Request $request, $id)
    {
        $admin = Auth::user();
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $session = \App\Models\PasarChatSession::where('region_id', $admin->region_id)->findOrFail($id);

        $msg = \App\Models\PasarChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'admin',
            'sender_id' => $admin->id,
            'message' => $request->message,
            'is_read' => false,
        ]);

        $session->update([
            'last_message' => $request->message,
            'last_message_at' => now(),
            'unread_user_count' => $session->unread_user_count + 1,
            'status' => 'escalated',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pesan balasan berhasil dikirim.',
            'data' => [
                'chat_message' => $msg,
            ]
        ]);
    }

    /**
     * Tandai Sesi Chat Selesai
     */
    public function resolveChat($id)
    {
        $admin = Auth::user();
        $session = \App\Models\PasarChatSession::where('region_id', $admin->region_id)->findOrFail($id);

        $session->update([
            'status' => 'resolved',
        ]);

        \App\Models\PasarChatMessage::create([
            'session_id' => $session->id,
            'sender_type' => 'bot',
            'sender_id' => null,
            'message' => 'Sesi obrolan ini telah ditandai selesai oleh Pengelola Toko BUMDes. Terima kasih sudah menghubungi kami!',
            'is_read' => true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi obrolan berhasil ditandai selesai.',
        ]);
    }
}
