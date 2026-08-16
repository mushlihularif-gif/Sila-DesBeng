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

        return view('admin.unit.pasar_daerah.index', compact(
            'produks', 'settings', 'pesanans', 'status', 'laporans', 'startDate', 'endDate', 'totalPendapatan', 'tab', 'semuaKecamatan'
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
        
        $region->settings = $settings;
        $region->save();

        return redirect()->back()->with('success', 'Pengaturan Toko Pasar Daerah berhasil diperbarui.');
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
        $pesanan = \App\Models\PasarOrder::where('id', $id)->where('region_id', $admin->region_id)->with('items.produk', 'user')->firstOrFail();
        
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
            'status' => 'required|in:pending,paid,confirmed,in_delivery,completed,cancelled,rejected',
        ]);
        
        $pesanan->status = $validated['status'];
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
}
