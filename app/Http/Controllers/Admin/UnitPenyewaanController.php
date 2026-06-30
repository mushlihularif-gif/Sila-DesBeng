<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageCompressorService;

class UnitPenyewaanController extends Controller
{
    // Default SOP Texts
    private $defaultSopDitanggung = "1. Penyewa wajib menjaga barang sewaan dengan baik.\n2. Jika terjadi KERUSAKAN atau KEHILANGAN barang selama masa penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki alat tersebut sesuai dengan nilai barang.\n3. Keterlambatan pengembalian dapat dikenakan denda sesuai ketentuan yang berlaku.";
    private $defaultSopTidakDitanggung = "1. Penyewa wajib menjaga barang sewaan dengan baik.\n2. Jika terjadi kerusakan atau kehilangan barang selama masa penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna (penyewa) karena telah didukung oleh dana operasional.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan.";

    /**
     * Menampilkan daftar barang.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $region = Region::find($user->region_id);
        $paymentInfo = $region ? ($region->payment_info ?? []) : [];
        
        $activeSop = $paymentInfo['sop_penyewaan_active'] ?? 'ditanggung';
        $sop_penyewaan_alat = $paymentInfo['sop_penyewaan_' . $activeSop] ?? $this->{'defaultSop' . ucfirst(\Illuminate\Support\Str::camel($activeSop))} ?? '';

        $search = $request->get('search');
        
        $barangs = Barang::query()
            ->when($search, function ($query, $search) {
                return $query->searchWhereLike(['nama_barang', 'kategori'], $search);
            })
            ->paginate(6)
            ->appends(['search' => $search]);
        
        return view('admin.unit.penyewaan.index', compact('barangs', 'search', 'sop_penyewaan_alat'));
    }

    /**
     * Menampilkan halaman Ketentuan SOP.
     */
    public function sop()
    {
        $user = auth()->user();
        $region = Region::find($user->region_id);
        
        if (!$region) {
            return redirect()->back()->with('error', 'Region tidak ditemukan.');
        }

        $paymentInfo = $region->payment_info ?? [];
        
        $sop_active = $paymentInfo['sop_penyewaan_active'] ?? 'ditanggung';
        $sop_ditanggung = $paymentInfo['sop_penyewaan_ditanggung'] ?? $this->defaultSopDitanggung;
        $sop_tidak_ditanggung = $paymentInfo['sop_penyewaan_tidak_ditanggung'] ?? $this->defaultSopTidakDitanggung;
        
        $default_ditanggung = $this->defaultSopDitanggung;
        $default_tidak_ditanggung = $this->defaultSopTidakDitanggung;

        return view('admin.unit.penyewaan.sop', compact('sop_active', 'sop_ditanggung', 'sop_tidak_ditanggung', 'default_ditanggung', 'default_tidak_ditanggung'));
    }

    /**
     * Menyimpan Ketentuan SOP.
     */
    public function updateSop(Request $request)
    {
        $request->validate([
            'sop_penyewaan_active' => 'required|in:ditanggung,tidak_ditanggung',
            'sop_penyewaan_ditanggung' => 'nullable|string',
            'sop_penyewaan_tidak_ditanggung' => 'nullable|string',
        ]);

        $user = auth()->user();
        $region = Region::find($user->region_id);

        if (!$region) {
            return redirect()->back()->with('error', 'Region tidak ditemukan.');
        }

        $paymentInfo = $region->payment_info ?? [];
        $paymentInfo['sop_penyewaan_active'] = $request->sop_penyewaan_active;
        $paymentInfo['sop_penyewaan_ditanggung'] = $request->sop_penyewaan_ditanggung ?? $this->defaultSopDitanggung;
        $paymentInfo['sop_penyewaan_tidak_ditanggung'] = $request->sop_penyewaan_tidak_ditanggung ?? $this->defaultSopTidakDitanggung;

        $region->update([
            'payment_info' => $paymentInfo,
        ]);

        return redirect()->route('admin.unit.penyewaan.sop')->with('success', 'Ketentuan SOP berhasil diperbarui.');
    }

    /**
     * Menampilkan form untuk membuat barang baru.
     */
    public function create()
    {
        return view('admin.unit.penyewaan.create');
    }

    /**
     * Menyimpan barang baru ke dalam database.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_sewa' => 'required|string', // string (agar titik/koma diperbolehkan)
            'stok' => 'required|integer',
            'status' => 'required|in:tersedia,disewa,rusak',
            'kategori' => 'required|string',
            'lokasi' => 'required|string',
            'satuan' => 'required|string',
            'foto_utama' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:8192',
        ]);

        //  Bersihkan semua karakter non-angka (titik, koma, spasi, dll), lalu ubah ke integer
        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_sewa);
        if ($hargaBersih <= 0) {
            return back()->withErrors(['harga_sewa' => 'Harga sewa harus angka valid dan lebih dari 0.'])->withInput();
        }

        $data = [
            'nama_barang' => $request->nama_barang,
            'deskripsi' => $request->deskripsi,
            'harga_sewa' => $hargaBersih,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'satuan' => $request->satuan,
        ];

        // Upload gambar
        if ($request->hasFile('foto_utama')) { 
            $data['foto'] = ImageCompressorService::compressAndStore($request->file('foto_utama'), 'barang'); 
        }
        if ($request->hasFile('foto_2')) {
            $data['foto_2'] = ImageCompressorService::compressAndStore($request->file('foto_2'), 'barang');
        }
        if ($request->hasFile('foto_3')) {
            $data['foto_3'] = ImageCompressorService::compressAndStore($request->file('foto_3'), 'barang');
        }

        Barang::create($data);

        return redirect()->route('admin.unit.penyewaan.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail barang.
     */
    public function show($id)
    {
        $barang = Barang::findOrFail($id);
        return view('admin.unit.penyewaan.show', compact('barang'));
    }

    /**
     * Menampilkan form untuk mengedit barang.
     */
    public function edit($id)
    {
        $barang = Barang::findOrFail($id);
        return view('admin.unit.penyewaan.edit', compact('barang'));
    }

    /**
     * Menghapus barang dari database.
     */
    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);

        // Hapus file gambar
        if ($barang->foto) {
            Storage::disk('public')->delete($barang->foto);
        }
        if ($barang->foto_2) {
            Storage::disk('public')->delete($barang->foto_2);
        }
        if ($barang->foto_3) {
            Storage::disk('public')->delete($barang->foto_3);
        }

        $barang->delete();

        return redirect()->route('admin.unit.penyewaan.index')->with('success', 'Barang berhasil dihapus.');
    }

    /**
     * Memperbarui barang yang ada di database.
     */
    public function update(Request $request, $id)
    {
        // Validasi input — SAMA seperti store()
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_sewa' => 'required|string', // ✅ Terima sebagai string (untuk format Rupiah)
            'stok' => 'required|integer',
            'status' => 'required|in:tersedia,disewa,rusak',
            'kategori' => 'required|string',
            'lokasi' => 'required|string',
            'satuan' => 'required|string',
            'foto_utama' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Bersihkan harga (sama seperti store)
        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_sewa);
        if ($hargaBersih <= 0) {
            return back()->withErrors(['harga_sewa' => 'Harga sewa harus angka valid dan lebih dari 0.'])->withInput();
        }

        // Cari barang
        $barang = Barang::findOrFail($id);

        // Siapkan data update
        $data = [
            'nama_barang' => $request->nama_barang,
            'deskripsi' => $request->deskripsi,
            'harga_sewa' => $hargaBersih,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'satuan' => $request->satuan,
        ];

        // Update gambar utama
        if ($request->hasFile('foto_utama')) {
            if ($barang->foto && Storage::disk('public')->exists($barang->foto)) {
                Storage::disk('public')->delete($barang->foto);
            }
            $data['foto'] = ImageCompressorService::compressAndStore($request->file('foto_utama'), 'barang');
        }

        // Update foto_2
        if ($request->input('delete_foto_2') == '1') {
            if ($barang->foto_2 && Storage::disk('public')->exists($barang->foto_2)) Storage::disk('public')->delete($barang->foto_2);
            $data['foto_2'] = null;
        } elseif ($request->hasFile('foto_2')) {
            if ($barang->foto_2 && Storage::disk('public')->exists($barang->foto_2)) Storage::disk('public')->delete($barang->foto_2);
            $data['foto_2'] = ImageCompressorService::compressAndStore($request->file('foto_2'), 'barang');
        }

        // Update foto_3
        if ($request->input('delete_foto_3') == '1') {
            if ($barang->foto_3 && Storage::disk('public')->exists($barang->foto_3)) Storage::disk('public')->delete($barang->foto_3);
            $data['foto_3'] = null;
        } elseif ($request->hasFile('foto_3')) {
            if ($barang->foto_3 && Storage::disk('public')->exists($barang->foto_3)) Storage::disk('public')->delete($barang->foto_3);
            $data['foto_3'] = ImageCompressorService::compressAndStore($request->file('foto_3'), 'barang');
        }

        // Simpan perubahan
        $barang->update($data);

        return redirect()->route('admin.unit.penyewaan.index')
                         ->with('success', 'Barang berhasil diperbarui.');
    }
}