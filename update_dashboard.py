import os

file_path = r"D:\laragon\www\SilaDesBeng\app\Http\Controllers\Admin\DashboardController.php"

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Update Base Queries Setup
old_base_setup = """    $baseRental = $this->applyRegionFilter(RentalBooking::withTrashed());
    $baseGas = $this->applyRegionFilter(GasOrder::withTrashed());
    $baseMobil = $this->applyRegionFilter(\App\Models\MobilBooking::withTrashed());

    // Filter by specific region if requested
    $selectedKecamatanId = request('kecamatan_id');
    $selectedDesaId = request('desa_id');
    
    if ($selectedDesaId && $selectedDesaId !== 'all') {
        $baseRental->whereHas('user', function($q) use ($selectedDesaId) {
            $q->where('region_id', $selectedDesaId);
        });
        $baseGas->whereHas('user', function($q) use ($selectedDesaId) {
            $q->where('region_id', $selectedDesaId);
        });
        $baseMobil->whereHas('user', function($q) use ($selectedDesaId) {
            $q->where('region_id', $selectedDesaId);
        });
    } elseif ($selectedKecamatanId && $selectedKecamatanId !== 'all') {
        // Find all desa IDs under this kecamatan
        $desaIdsUnderKecamatan = \App\Models\Region::where('parent_id', $selectedKecamatanId)->pluck('id')->toArray();
        $desaIdsUnderKecamatan[] = $selectedKecamatanId; // Include the kecamatan itself just in case
        
        $baseRental->whereHas('user', function($q) use ($desaIdsUnderKecamatan) {
            $q->whereIn('region_id', $desaIdsUnderKecamatan);
        });
        $baseGas->whereHas('user', function($q) use ($desaIdsUnderKecamatan) {
            $q->whereIn('region_id', $desaIdsUnderKecamatan);
        });
        $baseMobil->whereHas('user', function($q) use ($desaIdsUnderKecamatan) {
            $q->whereIn('region_id', $desaIdsUnderKecamatan);
        });
    }"""

new_base_setup = """    $baseRental = $this->applyRegionFilter(RentalBooking::withTrashed());
    $baseGas = $this->applyRegionFilter(GasOrder::withTrashed());
    $baseMobil = $this->applyRegionFilter(\App\Models\MobilBooking::withTrashed());
    $baseFasilitas = $this->applyRegionFilter(\App\Models\FasilitasUmumBooking::withTrashed());
    $basePasar = $this->applyRegionFilter(\App\Models\PasarOrder::withTrashed());
    $baseLaporan = $this->applyRegionFilter(\App\Models\Laporan::query(), 'user');

    // Filter by specific region if requested
    $selectedKecamatanId = request('kecamatan_id');
    $selectedDesaId = request('desa_id');
    
    if ($selectedDesaId && $selectedDesaId !== 'all') {
        $userFilter = function($q) use ($selectedDesaId) { $q->where('region_id', $selectedDesaId); };
        $baseRental->whereHas('user', $userFilter);
        $baseGas->whereHas('user', $userFilter);
        $baseMobil->whereHas('user', $userFilter);
        $baseFasilitas->whereHas('user', $userFilter);
        $basePasar->whereHas('user', $userFilter);
        $baseLaporan->whereHas('user', $userFilter);
    } elseif ($selectedKecamatanId && $selectedKecamatanId !== 'all') {
        $desaIdsUnderKecamatan = \App\Models\Region::where('parent_id', $selectedKecamatanId)->pluck('id')->toArray();
        $desaIdsUnderKecamatan[] = $selectedKecamatanId;
        $userFilter = function($q) use ($desaIdsUnderKecamatan) { $q->whereIn('region_id', $desaIdsUnderKecamatan); };
        
        $baseRental->whereHas('user', $userFilter);
        $baseGas->whereHas('user', $userFilter);
        $baseMobil->whereHas('user', $userFilter);
        $baseFasilitas->whereHas('user', $userFilter);
        $basePasar->whereHas('user', $userFilter);
        $baseLaporan->whereHas('user', $userFilter);
    }"""

content = content.replace(old_base_setup, new_base_setup)

# 2. Update Requests Fetching logic
old_requests_logic = """    $rentalRequests = collect();
    $gasRequests = collect();
    $latestRequests = collect();

    // Hanya admin tingkat desa ke bawah yang mengurus pesanan/permintaan
    if (in_array(auth()->user()->role, ['admin_desa', 'admin_rw', 'admin_rt'])) {
        $rentalRequests = $baseRental->clone()->with(['user', 'barang'])
            ->where(function($q) {
                $q->where('status', 'pending')
                  ->orWhere('cancellation_status', 'pending');
            })
            ->get()
            ->map(function ($item) {
                $item->type = 'rental';
                $item->item_name = $item->barang->nama_barang ?? 'Unknown Item';
                return $item;
            });

        // Ambil pesanan gas yang tertunda atau minta batal
        $gasRequests = $baseGas->clone()->with('user')
            ->where(function($q) {
                 $q->where('status', 'pending')
                   ->orWhere('cancellation_status', 'pending');
            })
            ->get()
            ->map(function ($item) {
                $item->type = 'gas';
                $item->item_name = $item->item_name ?? 'Gas Order'; 
                return $item;
            });

        // Gabungkan dan urutkan berdasarkan created_at desc
        $latestRequests = $rentalRequests->concat($gasRequests)->sortByDesc('created_at')->take(5);
    }"""

new_requests_logic = """    $latestRequests = collect();
    $totalPending = 0;

    if (in_array(auth()->user()->role, ['admin_desa', 'admin_rw', 'admin_rt'])) {
        $rentalRequests = $baseRental->clone()->with(['user', 'barang'])->where(function($q) { $q->where('status', 'pending')->orWhere('cancellation_status', 'pending'); })->get()->map(function ($i) { $i->type = 'rental'; $i->item_name = $i->barang->nama_barang ?? 'Alat'; return $i; });
        $gasRequests = $baseGas->clone()->with('user')->where(function($q) { $q->where('status', 'pending')->orWhere('cancellation_status', 'pending'); })->get()->map(function ($i) { $i->type = 'gas'; $i->item_name = $i->item_name ?? 'Tabung Gas'; return $i; });
        
        $mobilRequests = $baseMobil->clone()->with(['user', 'mobil' => function($q) { $q->withTrashed(); }])->where(function($q) { $q->where('status', 'pending')->orWhere('cancellation_status', 'pending'); })->get()->map(function ($i) { $i->type = 'mobil'; $i->item_name = $i->mobil->nama_mobil ?? 'Mobil'; return $i; });
        
        $fasilitasRequests = $baseFasilitas->clone()->with(['user', 'fasilitas' => function($q) { $q->withTrashed(); }])->where(function($q) { $q->where('status', 'pending')->orWhere('cancellation_status', 'pending'); })->get()->map(function ($i) { $i->type = 'fasilitas_umum'; $i->item_name = $i->fasilitas->nama_fasilitas ?? 'Fasilitas'; return $i; });
        
        $pasarRequests = $basePasar->clone()->with('user')->where(function($q) { $q->where('status', 'waiting')->orWhere('cancellation_status', 'pending'); })->get()->map(function ($i) { $i->type = 'pasar_daerah'; $i->item_name = 'Pesanan Pasar'; return $i; });
        
        $laporanRequests = $baseLaporan->clone()->with('user')->where('status', 'Pending')->get()->map(function ($i) { $i->type = 'laporan'; $i->item_name = 'Laporan Warga'; return $i; });
        
        $latestRequests = collect()->concat($rentalRequests)->concat($gasRequests)->concat($mobilRequests)->concat($fasilitasRequests)->concat($pasarRequests)->concat($laporanRequests)->sortByDesc('created_at')->take(10);
        
        $totalPending = $rentalRequests->count() + $gasRequests->count() + $mobilRequests->count() + $fasilitasRequests->count() + $pasarRequests->count() + $laporanRequests->count();
    }"""

content = content.replace(old_requests_logic, new_requests_logic)

# 3. Remove the old $totalPending assignment
content = content.replace("$totalPending = $rentalRequests->count() + $gasRequests->count();", "")

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print("DashboardController.php updated successfully!")
