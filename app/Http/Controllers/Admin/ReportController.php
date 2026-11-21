<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\FasilitasUmumBooking;
use App\Models\GasOrder;
use App\Models\ManualReport;
use App\Models\MobilBooking;
use App\Models\Region;
use App\Models\RentalBooking;
use App\Support\FinanceRecords;
use App\Support\FinancialProof;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ReportController extends Controller
{
    public function transactions(Request $request)
    {
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Query Penyewaan
        $rentalQuery = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)->with(['user', 'barang', 'walletEntries'])->orderByDesc('created_at');

        // Query Gas
        $gasQuery = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)->with(['user', 'gas', 'walletEntries'])->orderByDesc('created_at');

        // Query Mobil
        $mobilQuery = $this->applyRegionFilter(MobilBooking::withTrashed(), 'mobil', true)->with(['user', 'mobil', 'walletEntries'])->orderByDesc('created_at');

        // Query Fasilitas Umum
        $fasilitasQuery = $this->applyRegionFilter(FasilitasUmumBooking::withTrashed(), 'fasilitas', true)->with(['user', 'fasilitas', 'walletEntries'])->orderByDesc('created_at');

        $user = auth()->user();
        if ($user->isStaff()) {
            if (! $user->hasUnitPermission('sewa_alat')) {
                $rentalQuery->whereRaw('1 = 0');
            }
            if (! $user->hasUnitPermission('gas')) {
                $gasQuery->whereRaw('1 = 0');
            }
            if (! $user->hasUnitPermission('sewa_mobil')) {
                $mobilQuery->whereRaw('1 = 0');
            }
            if (! $user->hasUnitPermission('fasilitas_umum')) {
                $fasilitasQuery->whereRaw('1 = 0');
            }
        }

        // Terapkan Filter
        if ($status && $status !== 'all') {
            $rentalQuery->where('status', $status);
            $gasQuery->where('status', $status);
            $mobilQuery->where('status', $status);
            $fasilitasQuery->where('status', $status);
        }

        if ($startDate) {
            $rentalQuery->whereDate('created_at', '>=', $startDate);
            $gasQuery->whereDate('created_at', '>=', $startDate);
            $mobilQuery->whereDate('created_at', '>=', $startDate);
            $fasilitasQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $rentalQuery->whereDate('created_at', '<=', $endDate);
            $gasQuery->whereDate('created_at', '<=', $endDate);
            $mobilQuery->whereDate('created_at', '<=', $endDate);
            $fasilitasQuery->whereDate('created_at', '<=', $endDate);
        }

        $rentalRequests = $rentalQuery->get();
        $gasOrders = $gasQuery->get();
        $mobilBookings = $mobilQuery->get();
        $fasilitasBookings = $fasilitasQuery->get();

        $activeServices = $this->getActivatedServices();
        if ($request->ajax()) {
            return view('admin.laporan.partials.transactions_content', compact('rentalRequests', 'gasOrders', 'mobilBookings', 'fasilitasBookings', 'activeServices'))->render();
        }

        return view('admin.laporan.transactions', compact('rentalRequests', 'gasOrders', 'mobilBookings', 'fasilitasBookings', 'status', 'startDate', 'endDate', 'activeServices'));
    }

    public function income(Request $request)
    {
        FinanceRecords::authorize();
        $request->validate(['year' => 'nullable|integer|between:1900,2100', 'month' => 'nullable|integer|between:1,12']);
        $year = $request->integer('year', now()->year);
        $current = FinanceRecords::summary($year);
        $previous = FinanceRecords::summary($year - 1);
        $totalPenyewaan = $current['rental']['revenue'];
        $totalGas = $current['gas']['revenue'];
        $totalMobil = $current['mobil']['revenue'];
        $totalFasilitas = $current['fasilitas']['revenue'];
        $totalPasar = $current['pasar']['revenue']; // Historical transactions remain available.
        $manualLainnya = $current['lainnya']['revenue'];
        $totalPendapatan = $current['total']['revenue'];
        $totalTrxYear = $current['total']['transactions'];
        $rentalCount = $current['rental']['transactions'];
        $gasCount = $current['gas']['transactions'];
        $mobilCount = $current['mobil']['transactions'];
        $fasilitasCount = $current['fasilitas']['transactions'];
        $pasarCount = $current['pasar']['transactions'];
        $monthlyIncome = [];
        $dataPoints = [];
        $growth = [];
        for ($month = 1; $month <= 12; $month++) {
            $value = FinanceRecords::summary($year, $month)['total']['revenue'];
            $monthlyIncome[self::getMonthName($month)] = $value;
            $dataPoints[] = ['label' => self::getMonthName($month), 'y' => $value];
        }
        foreach (['total', 'rental', 'gas', 'mobil', 'fasilitas', 'pasar'] as $type) {
            $old = $previous[$type]['revenue'];
            $value = $current[$type]['revenue'];
            $growth[$type] = $old == 0 ? ($value > 0 ? 100 : 0) : round(($value - $old) / $old * 100, 1);
        }
        $availableYears = FinanceRecords::payments()->pluck('created_at')
            ->concat(FinanceRecords::manual()->pluck('transaction_date'))
            ->filter()->map(fn ($date) => Carbon::parse($date)->year)
            ->push(now()->year)->unique()->sortDesc()->values()->all();
        $manualReports = FinanceRecords::manual()->with('creator')->whereYear('transaction_date', $year)->orderByDesc('transaction_date')->get();
        $rentalRequests = collect();
        $gasOrders = collect();
        $totalPendapatanData = FinanceRecords::summary($year, $request->integer('month', now()->month));
        $unitPopulerData = $this->getUnitPopulerData($year);
        $activeServices = $this->getActivatedServices();

        return view('admin.laporan.income', compact('totalPenyewaan', 'totalGas', 'totalMobil', 'totalFasilitas', 'totalPasar',
            'totalPendapatan', 'totalTrxYear', 'monthlyIncome', 'dataPoints', 'rentalRequests', 'gasOrders', 'manualReports', 'manualLainnya',
            'rentalCount', 'gasCount', 'mobilCount', 'fasilitasCount', 'pasarCount', 'year', 'totalPendapatanData',
            'unitPopulerData', 'growth', 'availableYears', 'activeServices'));
    }

    /**
     * Laporan Wilayah - Grafik tren kinerja layanan
     */
    public function wilayah(Request $request)
    {
        // Menyembunyikan menunya saja tidak cukup: middleware rutenya 'role:admin',
        // dan itu peran semu yang ikut meloloskan staff. Tanpa penjaga ini,
        // halamannya tetap bisa dibuka dengan mengetik URL-nya langsung.
        abort_if(auth()->user()?->isStaff(), 403, 'Laporan wilayah bukan bagian dari tugas staf layanan.');

        // Parameter filter
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $selectedKecamatanId = Region::where('type', 'kecamatan')
            ->where('name', 'Kecamatan Bengkalis')
            ->value('id');
        abort_if(! $selectedKecamatanId, 404, 'Kecamatan Bengkalis belum terdaftar.');
        $desaIdsDalamCakupan = Region::where('parent_id', $selectedKecamatanId)
            ->whereIn('type', ['desa', 'kelurahan'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $selectedDesaId = $request->input('desa_id');
        if ($selectedDesaId && $selectedDesaId !== 'all'
            && ! in_array((int) $selectedDesaId, $desaIdsDalamCakupan, true)) {
            $selectedDesaId = 'all';
        }
        $regionIdsDalamCakupan = array_merge([(int) $selectedKecamatanId], $desaIdsDalamCakupan);

        // Base query for transactions (NOT strict, to see descendant activity)
        $baseRental = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', false);
        $baseGas = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', false);
        $baseMobil = $this->applyRegionFilter(MobilBooking::withTrashed(), 'mobil', false);
        $baseFasilitas = $this->applyRegionFilter(FasilitasUmumBooking::withTrashed(), 'fasilitas', false);

        $baseRental->whereHas('barang', fn ($q) => $q->whereIn('region_id', $regionIdsDalamCakupan));
        $baseGas->whereHas('gas', fn ($q) => $q->whereIn('region_id', $regionIdsDalamCakupan));
        $baseMobil->whereHas('mobil', fn ($q) => $q->whereIn('region_id', $regionIdsDalamCakupan));
        $baseFasilitas->whereHas('fasilitas', fn ($q) => $q->whereIn('region_id', $regionIdsDalamCakupan));

        $user = auth()->user();
        if ($user->isStaff()) {
            if (! $user->hasUnitPermission('sewa_alat')) {
                $baseRental->whereRaw('1 = 0');
            }
            if (! $user->hasUnitPermission('gas')) {
                $baseGas->whereRaw('1 = 0');
            }
            if (! $user->hasUnitPermission('sewa_mobil')) {
                $baseMobil->whereRaw('1 = 0');
            }
            if (! $user->hasUnitPermission('fasilitas_umum')) {
                $baseFasilitas->whereRaw('1 = 0');
            }
        }

        // Terapkan filter dropdown Kecamatan/Desa jika ada
        if ($selectedDesaId && $selectedDesaId !== 'all') {
            $baseRental->whereHas('barang', function ($q) use ($selectedDesaId) {
                $q->where('region_id', $selectedDesaId);
            });
            $baseGas->whereHas('gas', function ($q) use ($selectedDesaId) {
                $q->where('region_id', $selectedDesaId);
            });
            $baseMobil->whereHas('mobil', function ($q) use ($selectedDesaId) {
                $q->where('region_id', $selectedDesaId);
            });
            $baseFasilitas->whereHas('fasilitas', function ($q) use ($selectedDesaId) {
                $q->where('region_id', $selectedDesaId);
            });
        } elseif ($selectedKecamatanId && $selectedKecamatanId !== 'all') {
            $desaIds = Region::where('parent_id', $selectedKecamatanId)->pluck('id')->toArray();
            $desaIds[] = $selectedKecamatanId;
            $baseRental->whereHas('barang', function ($q) use ($desaIds) {
                $q->whereIn('region_id', $desaIds);
            });
            $baseGas->whereHas('gas', function ($q) use ($desaIds) {
                $q->whereIn('region_id', $desaIds);
            });
            $baseMobil->whereHas('mobil', function ($q) use ($desaIds) {
                $q->whereIn('region_id', $desaIds);
            });
            $baseFasilitas->whereHas('fasilitas', function ($q) use ($desaIds) {
                $q->whereIn('region_id', $desaIds);
            });
        }

        // Kinerja Per Bulan (Januari - Desember)
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlyPerformance = array_fill_keys($months, 0);

        $rentalMonthly = $baseRental->clone()->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', $year)->whereNotIn('status', ['pending', 'cancelled', 'rejected'])->groupBy('month')->pluck('total', 'month');
        $gasMonthly = $baseGas->clone()->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', $year)->whereNotIn('status', ['pending', 'cancelled', 'rejected'])->groupBy('month')->pluck('total', 'month');
        $mobilMonthly = $baseMobil->clone()->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', $year)->whereNotIn('status', ['pending', 'cancelled', 'rejected'])->groupBy('month')->pluck('total', 'month');
        $fasilitasMonthly = $baseFasilitas->clone()->selectRaw('COUNT(*) as total, MONTH(created_at) as month')
            ->whereYear('created_at', $year)->whereNotIn('status', ['pending', 'cancelled', 'rejected'])->groupBy('month')->pluck('total', 'month');
        $laporanMonthly = collect();

        for ($i = 1; $i <= 12; $i++) {
            $monthName = $months[$i - 1];
            $monthlyPerformance[$monthName] = ($rentalMonthly[$i] ?? 0) + ($gasMonthly[$i] ?? 0) + ($mobilMonthly[$i] ?? 0) + ($fasilitasMonthly[$i] ?? 0) + ($laporanMonthly[$i] ?? 0);
        }

        // Kalkulasi Persentase Kinerja (Bulan ini vs Bulan lalu)
        $safeMonth = max(1, min(12, (int) $month));
        $currentMonthTotal = $monthlyPerformance[$months[$safeMonth - 1]] ?? 0;
        $lastMonthTotal = $safeMonth > 1 ? ($monthlyPerformance[$months[$safeMonth - 2]] ?? 0) : 0;

        $growth = 0;
        if ($lastMonthTotal > 0) {
            $growth = round((($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1);
        } elseif ($currentMonthTotal > 0) {
            $growth = 100;
        }

        // Daftar Kecamatan & Desa untuk filter
        $kecamatanList = collect();
        $desaList = collect();
        if (in_array(auth()->user()->role, ['super_admin'])) {
            $kecamatanList = Region::whereKey($selectedKecamatanId)->get();
            if ($selectedKecamatanId && $selectedKecamatanId !== 'all') {
                $desaList = Region::whereIn('type', ['desa', 'kelurahan'])->where('parent_id', $selectedKecamatanId)->get();
            } else {
                $desaList = Region::whereIn('type', ['desa', 'kelurahan'])->where('parent_id', $selectedKecamatanId)->get();
            }
        }

        // Dynamic Year Generation
        $rentalYears = RentalBooking::withTrashed()->selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
        $gasYears = GasOrder::withTrashed()->selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
        $mobilYears = MobilBooking::withTrashed()->selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
        $fasilitasYears = FasilitasUmumBooking::withTrashed()->selectRaw('YEAR(created_at) as year')->distinct()->pluck('year')->toArray();
        $laporanYears = [];

        $availableYears = array_unique(array_merge($rentalYears, $gasYears, $mobilYears, $fasilitasYears, $laporanYears, [now()->year]));
        rsort($availableYears);
        $years = collect($availableYears);

        // Total per unit layanan
        $serviceTotals = [
            'rental' => $rentalMonthly->sum(),
            'gas' => $gasMonthly->sum(),
            'mobil' => $mobilMonthly->sum(),
            'fasilitas' => $fasilitasMonthly->sum(),
            'laporan' => $laporanMonthly->sum(),
        ];

        $activeServices = $this->getActivatedServices();

        if ($request->ajax()) {
            // Render HTML options for desa dropdown
            $desaOptionsHtml = '<option value="all">-- Semua Desa --</option>';
            foreach ($desaList as $desa) {
                $selected = ($selectedDesaId == $desa->id) ? 'selected' : '';
                $desaOptionsHtml .= '<option value="'.$desa->id.'" '.$selected.'>'.$desa->name.'</option>';
            }

            return response()->json([
                'performanceData' => array_values($monthlyPerformance),
                'months' => array_keys($monthlyPerformance),
                'growth' => $growth,
                'currentMonthTotal' => $currentMonthTotal,
                'serviceTotals' => $serviceTotals,
                'desaOptionsHtml' => $desaOptionsHtml,
            ]);
        }

        return view('admin.laporan.wilayah', compact(
            'monthlyPerformance', 'months', 'growth', 'currentMonthTotal',
            'year', 'month', 'years', 'kecamatanList', 'desaList',
            'selectedKecamatanId', 'selectedDesaId', 'serviceTotals', 'activeServices'
        ));
    }

    /**
     * Ambil data Unit Populer - Perbandingan antara penyewaan dan penjualan gas
     */
    private function getUnitPopulerData($year)
    {
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $rentalData = [];
        $gasData = [];
        $mobilData = [];
        $fasilitasData = [];

        $user = auth()->user();
        $isStaff = $user->isStaff();
        $canViewRental = ! $isStaff || $user->hasUnitPermission('sewa_alat');
        $canViewGas = ! $isStaff || $user->hasUnitPermission('gas');
        $canViewMobil = ! $isStaff || $user->hasUnitPermission('sewa_mobil');
        $canViewFasilitas = ! $isStaff || $user->hasUnitPermission('fasilitas_umum');

        for ($month = 1; $month <= 12; $month++) {
            // Hitung pesanan penyewaan
            $rentalCount = $canViewRental ? $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count() : 0;

            // Hitung pesanan gas
            $gasCount = $canViewGas ? $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count() : 0;

            // Hitung pesanan mobil
            $mobilCount = $canViewMobil ? $this->applyRegionFilter(MobilBooking::withTrashed(), 'mobil', true)->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count() : 0;

            // Hitung pesanan fasilitas umum
            $fasilitasCount = $canViewFasilitas ? $this->applyRegionFilter(FasilitasUmumBooking::withTrashed(), 'fasilitas', true)->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotIn('status', ['pending', 'cancelled', 'rejected'])
                ->count() : 0;

            $rentalData[] = $rentalCount;
            $gasData[] = $gasCount;
            $mobilData[] = $mobilCount;
            $fasilitasData[] = $fasilitasCount;
        }

        return [
            'categories' => $months,
            'rental' => $rentalData,
            'gas' => $gasData,
            'mobil' => $mobilData,
            'fasilitas' => $fasilitasData,
        ];
    }

    /**
     * Ambil data Total Pendapatan - Rincian pendapatan berdasarkan unit
     */
    private function getTotalPendapatanData($month, $year)
    {
        return FinanceRecords::summary((int) $year, (int) $month);
    }

    public function logs(Request $request)
    {
        $query = $this->applyRegionFilter(ActivityLog::query(), 'user', false)->with('user')->orderByDesc('created_at');

        // Cari (Deskripsi atau Nama Pengguna)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->searchWhereLike(['description', 'action'], $search)
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->searchWhereLike('name', $search);
                    });
            });
        }

        // Filter berdasarkan Aksi
        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        // Filter berdasarkan Tanggal
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(10)->withQueryString();

        return view('admin.laporan.logs', compact('logs'));
    }

    /**
     * Bersihkan semua log aktivitas (Soft Delete)
     * Requires password confirmation (Sudo Mode) for security.
     */
    public function clearLogs(Request $request)
    {
        // Hanya super_admin atau admin kabupaten yang boleh membersihkan log
        if (! in_array(auth()->user()->role, ['super_admin'])) {
            return back()->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk membersihkan log.');
        }

        // Sudo Mode: require password confirmation
        $request->validate([
            'password' => 'required',
        ]);

        if (! Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', 'Password tidak valid. Pembersihan log dibatalkan.');
        }

        // Log the clear action BEFORE clearing (to a separate, undeletable record)
        \Log::channel('single')->critical('SECURITY AUDIT: Activity logs cleared by admin', [
            'admin_id' => auth()->id(),
            'admin_email' => auth()->user()->email,
            'ip_address' => $request->ip(),
            'timestamp' => now()->toISOString(),
            'logs_count' => ActivityLog::count(),
        ]);

        // Soft delete instead of hard delete
        ActivityLog::query()->delete(); // This triggers soft delete since model uses SoftDeletes

        return back()->with('success', 'Log aktivitas berhasil dibersihkan.');
    }

    /**
     * Simpan transaksi manual baru
     */
    public function storeManualTransaction(Request $request)
    {
        return $this->saveManual($request);
    }

    public function updateManualTransaction(Request $request, $id)
    {
        return $this->saveManual($request, (int) $id);
    }

    private function saveManual(Request $request, ?int $id = null)
    {
        FinanceRecords::authorize(true);
        if ($id) {
            FinanceRecords::manual()->findOrFail($id);
        }
        $data = $request->validate([
            'category' => 'required|in:penyewaan,gas,mobil,fasilitas,lainnya', 'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000', 'amount' => 'required|numeric|min:0|max:9999999999.99',
            'quantity' => 'required|integer|min:1|max:10000', 'payment_method' => 'required|in:tunai',
            'transaction_date' => 'required|date|before_or_equal:today', 'proof_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        unset($data['proof_image']);
        $path = null;
        try {
            if ($request->hasFile('proof_image')) {
                $path = $request->file('proof_image')->store('manual-reports', 'private');
                if (! $path) {
                    throw new \RuntimeException('Evidence storage failed');
                }
                $data['proof_image'] = $path;
            }
            $report = \DB::transaction(function () use ($request, $data, $id) {
                $report = $id ? FinanceRecords::manual()->lockForUpdate()->findOrFail($id) : new ManualReport;
                $before = $report->getAttributes();
                $report->fill($data);
                if (! $id) {
                    $report->created_by = auth()->id();
                }
                $report->region_id = auth()->user()->region_id;
                $report->save();
                ActivityLog::create(['user_id' => auth()->id(), 'action' => $id ? 'Update Manual Report' : 'Create Manual Report',
                    'description' => json_encode(['report_id' => $report->id, 'before' => $before, 'after' => $report->getAttributes()], JSON_UNESCAPED_UNICODE), 'ip_address' => $request->ip()]);

                return $report;
            });

            return response()->json(['success' => true, 'message' => 'Catatan pendapatan tersimpan.', 'data' => $report]);
        } catch (\Throwable $e) {
            if ($path) {
                \Storage::disk('private')->delete($path);
            }
            throw $e;
        }
    }

    public function destroyManualTransaction($id)
    {
        FinanceRecords::authorize(true);
        \DB::transaction(function () use ($id) {
            $report = FinanceRecords::manual()->lockForUpdate()->findOrFail($id);
            ActivityLog::create(['user_id' => auth()->id(), 'action' => 'Cancel Manual Report',
                'description' => json_encode(['report_id' => $report->id, 'before' => $report->getAttributes()], JSON_UNESCAPED_UNICODE), 'ip_address' => request()->ip()]);
            $report->delete(); // Retain the record and evidence for audit; exclude it from active income.
        });

        return response()->json(['success' => true, 'message' => 'Catatan dibatalkan. Riwayat dan bukti tetap disimpan untuk audit.']);
    }

    public function manualProof(int $id)
    {
        $report = FinanceRecords::manual()->withTrashed()->findOrFail($id);

        return FinancialProof::response($report->proof_image);
    }

    public function paymentProof(int $id)
    {
        $entry = FinanceRecords::payments()->findOrFail($id);

        return FinancialProof::response($entry->proof_path);
    }

    /**
     * Helper: Konversi nomor bulan ke nama bulan Indonesia
     */
    private static function getMonthName($month)
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $months[$month] ?? 'Unknown';
    }

    /**
     * Riwayat Pendapatan - Menampilkan daftar semua transaksi pendapatan
     */
    public function incomeHistory(Request $request)
    {
        FinanceRecords::authorize();
        $request->validate(['filter' => 'nullable|in:minggu_ini,bulan_ini,tahun_ini,semua']);
        $filter = $request->input('filter', 'bulan_ini');
        $activeServices = $this->getActivatedServices();
        $queryStartDate = match ($filter) {
            'minggu_ini' => now()->startOfWeek(), 'bulan_ini' => now()->startOfMonth(), 'tahun_ini' => now()->startOfYear(), default => null
        };
        $queryEndDate = now();
        $query = FinanceRecords::payments()->when($queryStartDate, fn ($q) => $q->whereBetween('created_at', [$queryStartDate, $queryEndDate]));
        $names = ['rental' => 'Sewa Alat', 'gas' => 'Gas Daerah', 'mobil' => 'Sewa Transportasi', 'fasilitas' => 'Fasilitas Umum', 'pasar' => 'Arsip Pasar Daerah'];
        $history = $query->get()->map(function ($entry) use ($names) {
            return (object) ['id' => $entry->reference_id, 'type' => $names[$entry->reference_type],
                'item_name' => 'Pembayaran pesanan #'.$entry->reference_id, 'date' => $entry->created_at, 'amount' => $entry->amount,
                'payment_method' => $entry->source === 'gateway' ? 'Gateway' : 'Manual (tunai/transfer/e-wallet)',
                'status' => 'verified', 'user_name' => '-', 'user_photo' => null, 'location' => '-', 'proof' => $entry->proof_path,
                'proof_route' => $entry->proof_path ? route('admin.finance.proof', $entry->id) : null, 'proof_download' => null];
        });

        // Laporan Manual
        $manualQuery = FinanceRecords::manual()
            ->with('creator');

        if ($queryStartDate) {
            $manualQuery->whereBetween('transaction_date', [$queryStartDate->format('Y-m-d'), $queryEndDate->format('Y-m-d')]);
        }

        $manual = $manualQuery->get()->map(function ($item) {
            return (object) [
                'id' => $item->id,
                'type' => 'Laporan Manual ('.($item->category_label ?? ucfirst($item->category)).')',
                'item_name' => $item->name.($item->description ? ' - '.$item->description : ''),
                'date' => Carbon::parse($item->transaction_date),
                'amount' => $item->amount * $item->quantity,
                'payment_method' => $item->payment_method ?? 'Tunai',
                'status' => 'completed',
                'user_name' => $item->creator->name ?? 'Admin',
                'user_photo' => $item->creator->profile_photo_url ?? null,
                'location' => '-',
                'proof' => $item->proof_image,
                'proof_route' => $item->proof_image ? route('admin.laporan.manual.proof', $item->id) : null,
                'proof_download' => null,
            ];
        });

        $history = $history->merge($manual);

        // Sort by date descending
        $history = $history->sortByDesc('date')->values();

        return view('admin.laporan.income_history', compact('history', 'filter', 'activeServices'));
    }
}
