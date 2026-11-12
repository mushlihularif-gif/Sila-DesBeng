<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Category;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageCompressorService;

class UnitPenyewaanController extends Controller
{
    use \App\Traits\ChecksStaffDelegation;

    private function scopedBarang()
    {
        $user = auth()->user();
        abort_unless($user && $user->isAdmin() && $user->region_id && $user->hasUnitPermission('sewa_alat'), 403);
        return Barang::where('region_id', $user->region_id);
    }

    // Default SOP Texts
    private $defaultSopDitanggung = "1. Penyewa wajib menjaga barang sewaan dengan baik.\n2. Jika terjadi KERUSAKAN atau KEHILANGAN barang selama masa penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki alat tersebut sesuai dengan nilai barang.\n3. Keterlambatan pengembalian dapat dikenakan denda sesuai ketentuan yang berlaku.";
    private $defaultSopTidakDitanggung = "1. Penyewa wajib menjaga barang sewaan dengan baik.\n2. Jika terjadi kerusakan atau kehilangan barang selama masa penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna (penyewa) karena telah didukung oleh dana operasional.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan.";

    /**
     * Menampilkan daftar barang.
     */
    public function index(Request $request)
    {
        if ($splash = $this->checkDelegation($request, 'sewa_alat', 'Penyewaan Alat/Tenda')) {
            return $splash;
        }

        $user = auth()->user();
        $region = Region::find($user->region_id);
        $paymentInfo = $region ? ($region->payment_info ?? []) : [];
        
        $activeSop = $paymentInfo['sop_penyewaan_active'] ?? 'ditanggung';
        $sop_penyewaan_alat = $paymentInfo['sop_penyewaan_' . $activeSop] ?? $this->{'defaultSop' . ucfirst(\Illuminate\Support\Str::camel($activeSop))} ?? '';

        $search = $request->get('search');
        
        $barangs = $this->scopedBarang()
            ->when($search, function ($query, $search) {
                return $query->searchWhereLike(['nama_barang', 'kategori'], $search);
            })
            ->paginate(6)
            ->appends(['search' => $search]);
        
        $tab = $request->get('tab', 'katalog');
        $chats = collect();
        $totalUnreadChats = 0;

        if (\Illuminate\Support\Facades\Schema::hasTable('unit_chat_sessions')) {
            try {
                $chats = \App\Models\UnitChatSession::where('region_id', $user ? $user->region_id : null)
                    ->where('service_type', 'penyewaan')
                    ->with('user')
                    ->orderBy('last_message_at', 'desc')
                    ->get();
                $totalUnreadChats = $chats->sum('unread_admin_count');
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('UnitChatSession penyewaan query skipped: ' . $e->getMessage());
            }
        }
        
        return view('admin.unit.penyewaan.index', compact('barangs', 'search', 'sop_penyewaan_alat', 'chats', 'totalUnreadChats', 'tab'));
    }

    /**
     * Menampilkan halaman Ketentuan SOP.
     */
    public function sop()
    {
        $this->scopedBarang();
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
        $this->scopedBarang();
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
        $this->scopedBarang();
        // Lokasi diambil dari daftar milik WILAYAH, bukan lagi SELECT DISTINCT
        // pada tabel produk unit ini. Query lama tidak menyaring region_id sama
        // sekali, sehingga admin satu desa ikut melihat nama lokasi desa lain;
        // selain itu lokasinya lenyap begitu produk terakhir yang memakainya
        // dihapus, dan koordinatnya harus diketik ulang tiap kali.
        $savedLocations = \App\Models\LokasiLayanan::untukWilayah(auth()->user()->region_id);
            
        $categories = Category::where('region_id', auth()->user()->region_id)
            ->where(function($q) {
                $q->where('type', 'barang')->orWhereNull('type');
            })->orderBy('name')->get();

        return view('admin.unit.penyewaan.create', compact('savedLocations', 'categories'));
    }

    /**
     * Menyimpan barang baru ke dalam database.
     */
    public function store(Request $request)
    {
        $this->scopedBarang();
        // Validasi input
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_sewa' => ['required','string','regex:/^(?:[0-9]+|[0-9]{1,3}(?:\.[0-9]{3})+)$/'], // string (agar titik/koma diperbolehkan)
            'stok' => 'required|integer|min:0|max:1000000',
            'status' => 'required|in:tersedia,disewa,rusak',
            'kategori' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'satuan' => 'required|string|max:255',
            'foto_utama' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
        ]);

        //  Bersihkan semua karakter non-angka (titik, koma, spasi, dll), lalu ubah ke integer
        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_sewa);
        if ($hargaBersih <= 0 || $hargaBersih > 99999999) {
            return back()->withErrors(['harga_sewa' => 'Harga sewa harus antara Rp 1 dan Rp 99.999.999.'])->withInput();
        }

        $data = [
            'nama_barang' => $request->nama_barang,
            'deskripsi' => $request->deskripsi,
            'harga_sewa' => $hargaBersih,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'satuan' => $request->satuan,
            'region_id' => auth()->user()->region_id,
        ];

        $newPaths = [];
        try {
            foreach (['foto'=>'foto_utama','foto_2'=>'foto_2','foto_3'=>'foto_3'] as $field=>$input) {
                if ($request->hasFile($input)) $newPaths[] = $data[$field] = ImageCompressorService::compressAndStore($request->file($input), 'barang');
            }
            $barang = \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
                $barang = Barang::create($data);
                $serviceAlat = \App\Models\Service::where('slug', 'penyewaan-alat')->first();
                if ($serviceAlat) \App\Models\RegionService::updateOrInsert(
                    ['region_id'=>$barang->region_id,'service_id'=>$serviceAlat->id], ['is_active'=>true,'updated_at'=>now()]
                );
                return $barang;
            });
        } catch (\Throwable $e) {
            foreach ($newPaths as $path) if ($path) Storage::disk('public')->delete($path);
            throw $e;
        }

        // Broadcast produk baru ke warga
        \App\Services\NotificationService::broadcastNewProduct('Penyewaan Alat', $barang->nama_barang, $barang->region_id ?? (auth()->user()->region_id ?? null), route('rental.equipment.show', $barang->id));

        return redirect()->route('admin.unit.penyewaan.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail barang.
     */
    public function show($id)
    {
        $barang = $this->scopedBarang()->findOrFail($id);
        return view('admin.unit.penyewaan.show', compact('barang'));
    }

    /**
     * Menampilkan form untuk mengedit barang.
     */
    public function edit($id)
    {
        $barang = $this->scopedBarang()->findOrFail($id);
        // Lokasi diambil dari daftar milik WILAYAH, bukan lagi SELECT DISTINCT
        // pada tabel produk unit ini. Query lama tidak menyaring region_id sama
        // sekali, sehingga admin satu desa ikut melihat nama lokasi desa lain;
        // selain itu lokasinya lenyap begitu produk terakhir yang memakainya
        // dihapus, dan koordinatnya harus diketik ulang tiap kali.
        $savedLocations = \App\Models\LokasiLayanan::untukWilayah(auth()->user()->region_id);
            
        $categories = Category::where('region_id', auth()->user()->region_id)
            ->where(function($q) {
                $q->where('type', 'barang')->orWhereNull('type');
            })->orderBy('name')->get();

        return view('admin.unit.penyewaan.edit', compact('barang', 'savedLocations', 'categories'));
    }

    /**
     * Menghapus barang dari database.
     */
    public function destroy($id)
    {
        $barang = $this->scopedBarang()->findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            $barang = $this->scopedBarang()->lockForUpdate()->findOrFail($id);
            abort_if($barang->rentalBookings()->withTrashed()->exists(), 409, 'Alat memiliki riwayat sewa. Ubah status menjadi Tidak Tersedia agar bukti transaksi tetap terjaga.');
            $barang->delete();
            \Illuminate\Support\Facades\DB::afterCommit(function () use ($barang) {
                foreach ([$barang->foto,$barang->foto_2,$barang->foto_3] as $path) if ($path) Storage::disk('public')->delete($path);
            });
        });

        return redirect()->route('admin.unit.penyewaan.index')->with('success', 'Barang berhasil dihapus.');
    }

    /**
     * Memperbarui barang yang ada di database.
     */
    public function update(Request $request, $id)
    {
        $this->scopedBarang();
        // Validasi input — SAMA seperti store()
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga_sewa' => ['required','string','regex:/^(?:[0-9]+|[0-9]{1,3}(?:\.[0-9]{3})+)$/'], // Terima sebagai string (untuk format Rupiah)
            'stok' => 'required|integer|min:0|max:1000000',
            'original_stock'=>'required|integer|min:0',
            'status' => 'required|in:tersedia,disewa,rusak',
            'kategori' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'satuan' => 'required|string|max:255',
            'foto_utama' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:8192',
        ]);

        // Bersihkan harga (sama seperti store)
        $hargaBersih = (int) preg_replace('/[^0-9]/', '', $request->harga_sewa);
        if ($hargaBersih <= 0 || $hargaBersih > 99999999) {
            return back()->withErrors(['harga_sewa' => 'Harga sewa harus antara Rp 1 dan Rp 99.999.999.'])->withInput();
        }

        // Cari barang
        $barang = $this->scopedBarang()->findOrFail($id);

        // Siapkan data update
        $data = [
            'nama_barang' => $request->nama_barang,
            'deskripsi' => $request->deskripsi,
            'harga_sewa' => $hargaBersih,
            'stok' => $request->stok,
            'status' => $request->status,
            'kategori' => $request->kategori,
            'lokasi' => $request->lokasi,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'satuan' => $request->satuan,
            'region_id' => $barang->region_id ?? (auth()->user()->region_id ?? null),
        ];

        $newPaths = []; $oldPaths = [];
        try {
            foreach (['foto'=>'foto_utama','foto_2'=>'foto_2','foto_3'=>'foto_3'] as $field=>$input) {
                if ($request->hasFile($input)) $newPaths[] = $data[$field] = ImageCompressorService::compressAndStore($request->file($input), 'barang');
                elseif ($request->input('delete_'.$field) == '1') $data[$field] = null;
            }
            \Illuminate\Support\Facades\DB::transaction(function () use ($id, $data, $request, &$oldPaths) {
                $barang = $this->scopedBarang()->lockForUpdate()->findOrFail($id);
                if ((int)$barang->stok !== $request->integer('original_stock')) throw \Illuminate\Validation\ValidationException::withMessages(['stok'=>'Stok telah berubah. Muat ulang halaman sebelum menyimpan.']);
                foreach (['foto','foto_2','foto_3'] as $field) if (array_key_exists($field,$data) && $barang->$field) $oldPaths[] = $barang->$field;
                $barang->update($data);
            });
        } catch (\Throwable $e) {
            foreach ($newPaths as $path) if ($path) Storage::disk('public')->delete($path);
            throw $e;
        }
        foreach ($oldPaths as $path) Storage::disk('public')->delete($path);

        return redirect()->route('admin.unit.penyewaan.index')
                         ->with('success', 'Barang berhasil diperbarui.');
    }
}