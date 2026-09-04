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
        $produks = PasarProduk::where('region_id', $admin->region_id)->latest()->get();
        
        $region = Region::find($admin->region_id);
        $settings = $region ? $region->settings : [];

        // --- Data Pesanan ---
        $status = $request->get('status', 'all');
        $queryPesanan = \App\Models\PasarOrder::where('region_id', $admin->region_id)->with('items.produk', 'user')->latest();
        if ($status !== 'all') {
            $queryPesanan->where('status', $status);
        }
        $pesanans = $queryPesanan->get();

        // --- Data Laporan ---
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $queryLaporan = \App\Models\PasarOrder::where('region_id', $admin->region_id)
            ->whereIn('status', ['completed'])
            ->with('items.produk', 'user')
            ->latest();
            
        if ($startDate && $endDate) {
            $queryLaporan->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        
        $laporans = $queryLaporan->get();
        $totalPendapatan = $laporans->sum('grand_total');

        $tab = $request->get('tab', 'produk');

        // Data Kecamatan
        $semuaKecamatan = Region::where('type', 'kecamatan')->orderBy('name', 'asc')->get();

        // Data Ulasan
        $reviews = \App\Models\PasarReview::whereHas('produk', function($query) use ($admin) {
                $query->where('region_id', $admin->region_id);
            })
            ->with(['produk', 'user'])
            ->latest()
            ->get();

        // Data Komplain & Retur
        $complaints = \App\Models\PasarComplaint::where('region_id', $admin->region_id)
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
            'produks', 'settings', 'pesanans', 'status', 'laporans', 'startDate', 'endDate', 'totalPendapatan', 'tab', 'semuaKecamatan', 'admin', 'reviews', 'complaints', 'chats', 'totalUnreadChats'
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

        PasarProduk::create($validated);

        return redirect()->route('admin.unit.pasar_daerah.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $produk = PasarProduk::where('id', $id)->where('region_id', Auth::user()->region_id)->firstOrFail();
        return view('admin.unit.pasar_daerah.show', compact('produk')); // Optional if they have a show view
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $produk = PasarProduk::where('id', $id)->where('region_id', Auth::user()->region_id)->firstOrFail();
        
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
        $produk = PasarProduk::where('id', $id)->where('region_id', Auth::user()->region_id)->firstOrFail();

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

        $produk->update($validated);

        return redirect()->route('admin.unit.pasar_daerah.index')->with('success', 'Produk berhasil diubah.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $produk = PasarProduk::where('id', $id)->where('region_id', Auth::user()->region_id)->firstOrFail();
        
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
        $request->validate([
            'sop_pasar' => 'nullable|string',
            'ongkir_dalam_desa' => 'required|numeric|min:0',
            'ongkir_luar_desa' => 'required|numeric|min:0',
            'tipe_ongkir_luar_kecamatan' => 'required|in:pukul_rata,per_kecamatan',
            'ongkir_luar_kecamatan' => 'nullable|numeric|min:0',
            'ongkir_kecamatan_khusus' => 'nullable|array',
            'ongkir_kecamatan_khusus.*' => 'nullable|numeric|min:0',
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
        $settings['ongkir_dalam_desa'] = $request->input('ongkir_dalam_desa');
        $settings['ongkir_luar_desa'] = $request->input('ongkir_luar_desa');
        
        $tipe = $request->input('tipe_ongkir_luar_kecamatan');
        $settings['tipe_ongkir_luar_kecamatan'] = $tipe;
        
        if ($tipe == 'pukul_rata') {
            $settings['ongkir_luar_kecamatan'] = $request->input('ongkir_luar_kecamatan') ?? 25000;
        } else {
            $khusus = [];
            if ($request->has('ongkir_kecamatan_khusus')) {
                foreach ($request->input('ongkir_kecamatan_khusus') as $kec_id => $harga) {
                    if ($harga !== null && $harga !== '') {
                        $khusus[$kec_id] = $harga;
                    }
                }
            }
            $settings['ongkir_kecamatan_khusus'] = $khusus;
        }

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
        $admin = Auth::user();
        
        $status = $request->get('status', 'all');
        $query = \App\Models\PasarOrder::where('region_id', $admin->region_id)->with('items.produk', 'user')->latest();
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        $pesanans = $query->get();
        return view('admin.unit.pasar_daerah.pesanan', compact('pesanans', 'status'));
    }

    /**
     * Display order detail.
     */
    public function pesananShow($id)
    {
        $admin = Auth::user();
        $pesanan = \App\Models\PasarOrder::where('id', $id)
            ->where('region_id', $admin->region_id)
            ->with(['items.produk', 'user', 'complaint'])
            ->firstOrFail();
        
        return view('admin.unit.pasar_daerah.pesanan_show', compact('pesanan'));
    }

    /**
     * Update order status.
     */
    public function pesananUpdate(Request $request, $id)
    {
        $admin = Auth::user();
        $pesanan = \App\Models\PasarOrder::where('id', $id)->where('region_id', $admin->region_id)->firstOrFail();
        
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,confirmed,in_delivery,delivered,completed,cancelled,rejected',
        ]);
        
        $pesanan->status = $validated['status'];
        if ($validated['status'] === 'delivered' && empty($pesanan->delivered_at)) {
            $pesanan->delivered_at = now();
        }
        $pesanan->handled_by = auth()->id();
        $pesanan->save();
        
        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    /**
     * Display transaction reports (Laporan).
     */
    public function laporan(Request $request)
    {
        $admin = Auth::user();
        
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = \App\Models\PasarOrder::where('region_id', $admin->region_id)
            ->whereIn('status', ['completed'])
            ->with('items.produk', 'user')
            ->latest();
            
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        
        $laporans = $query->get();
        
        $totalPendapatan = $laporans->sum('grand_total');
        
        return view('admin.unit.pasar_daerah.laporan', compact('laporans', 'startDate', 'endDate', 'totalPendapatan'));
    }
    /**
     * Tampilkan Halaman Profil Toko
     */
    public function profile()
    {
        $user = Auth::user();
        return view('admin.unit.pasar_daerah.profile', compact('user'));
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
        $admin = Auth::user();
        
        $reviews = \App\Models\PasarReview::whereHas('produk', function($query) use ($admin) {
                $query->where('region_id', $admin->region_id);
            })
            ->with(['produk', 'user'])
            ->latest()
            ->get();
            
        return view('admin.unit.pasar_daerah.reviews', compact('reviews'));
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
            $query->where('region_id', $admin->region_id);
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
        $admin = Auth::user();
        
        $request->validate([
            'status' => 'required|in:approved_replacement,approved_refund,rejected',
            'admin_response' => 'required|string|max:1000',
        ]);

        $complaint = \App\Models\PasarComplaint::where('region_id', $admin->region_id)->findOrFail($id);
        
        $complaint->status = $request->status;
        $complaint->admin_response = $request->admin_response;
        $complaint->handled_by = $admin->id;
        $complaint->resolved_at = now();
        $complaint->save();

        // Update status order jika relevan
        if ($complaint->order) {
            if ($request->status === 'approved_refund') {
                $complaint->order->status = 'cancelled';
                $complaint->order->admin_cancellation_response = 'Komplain disetujui untuk pengembalian dana: ' . $request->admin_response;
            } elseif ($request->status === 'approved_replacement') {
                $complaint->order->status = 'processing';
            }
            $complaint->order->save();
        }

        return redirect()->back()->with('success', 'Tindakan komplain berhasil diproses dan dikirim ke pembeli.');
    }

    /**
     * Detail Pesan Chat untuk Admin
     */
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
