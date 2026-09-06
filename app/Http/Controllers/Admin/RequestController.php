<?php

namespace App\Http\Controllers\Admin;

use App\Models\RentalBooking;
use App\Models\GasOrder;
use App\Models\Gas;
use App\Models\Barang;
use App\Models\MobilBooking;
use App\Models\FasilitasUmumBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Services\NotificationService;

class RequestController extends Controller
{
    /**
     * Jenis permintaan -> kunci izin unit staf.
     * Nilai $type datang dari route {type} di admin.aktivitas.permintaan-pengajuan.*
     */
    private const IZIN_PER_TIPE = [
        'rental'    => 'sewa_alat',
        'gas'       => 'gas',
        'mobil'     => 'sewa_mobil',
        'fasilitas' => 'fasilitas_umum',
    ];

    /**
     * Jenis permintaan -> [model, nama relasi yang memuat region_id].
     * Nama relasinya harus sama persis dengan yang dipakai index(), supaya
     * yang boleh DIPROSES tidak pernah lebih luas dari yang boleh DILIHAT.
     */
    private const WILAYAH_PER_TIPE = [
        'rental'    => [RentalBooking::class, 'barang'],
        'gas'       => [GasOrder::class, 'gas'],
        'mobil'     => [MobilBooking::class, 'mobil'],
        'fasilitas' => [FasilitasUmumBooking::class, 'fasilitas'],
    ];

    /**
     * Penjaga untuk aksi yang mengubah data: setujui, tolak, ubah status.
     *
     * Menggantikan `abort_unless($user->role === 'admin')` yang dulu mengunci
     * ketiganya ke satu role saja — kepala desa dan camat pun ikut kena 403 di
     * dashboard mereka sendiri, dan staf unit hanya bisa menonton pesanan
     * menumpuk tanpa bisa memprosesnya.
     *
     * Dua hal diperiksa, dan keduanya wajib:
     *
     * 1. IZIN UNIT — hasUnitPermission() otomatis true untuk admin kabupaten,
     *    camat, dan kepala desa; untuk staf diperiksa dari centang izinnya.
     *    Jadi staf gas tidak bisa memproses pesanan sewa alat.
     *
     * 2. KEPEMILIKAN WILAYAH — WAJIB ada sekarang. Dulu hanya admin kabupaten
     *    yang bisa bertindak sehingga lintas wilayah bukan masalah. Begitu
     *    kepala desa dan staf ikut bisa, tanpa pemeriksaan ini staf Desa A
     *    dapat menyetujui pesanan Desa B cukup dengan mengganti id di URL.
     */
    private function pastikanBolehMenangani(string $type, $id): void
    {
        $user = auth()->user();

        $izin = self::IZIN_PER_TIPE[$type] ?? null;
        abort_if($izin === null, 404, 'Jenis permintaan tidak dikenal.');

        abort_unless(
            $user && $user->isAdmin() && $user->hasUnitPermission($izin),
            403,
            'Anda tidak memiliki izin untuk memproses permintaan unit ini.'
        );

        [$model, $relasi] = self::WILAYAH_PER_TIPE[$type];

        $milikWilayahIni = $this->applyRegionFilter($model::withTrashed(), $relasi, true)
            ->whereKey($id)
            ->exists();

        abort_unless($milikWilayahIni, 404, 'Permintaan tidak ditemukan di wilayah Anda.');
    }

    public function index(Request $request)
    {
        // Ambil parameter filter
        $status = $request->get('status', 'all');
        $category = $request->get('category', 'all');

        $user = auth()->user();
        $isStaff = $user->isStaff();
        
        $canViewRental = !$isStaff || $user->hasUnitPermission('sewa_alat');
        $canViewGas = !$isStaff || $user->hasUnitPermission('gas');
        $canViewMobil = !$isStaff || $user->hasUnitPermission('sewa_mobil');
        $canViewFasilitas = !$isStaff || $user->hasUnitPermission('fasilitas_umum');
        $canViewPasar = !$isStaff || $user->hasUnitPermission('pasar_daerah');

        // Buat query untuk pemesanan penyewaan (Include deleted for history)
        $rentalQuery = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)->with(['user', 'barang']);
        if ($status !== 'all') {
            if ($status === 'cancellation_pending') {
                $rentalQuery->where('cancellation_status', 'pending');
            } elseif ($status === 'in_process') {
                $rentalQuery->whereIn('status', ['confirmed', 'approved', 'being_prepared', 'in_delivery', 'arrived']);
            } elseif ($status === 'completed') {
                $rentalQuery->whereIn('status', ['completed', 'resolved', 'returned']);
            } elseif ($status === 'rejected') {
                $rentalQuery->whereIn('status', ['cancelled', 'rejected']);
            } else {
                $rentalQuery->where('status', $status);
            }
        }

        // Buat query untuk pesanan gas (Include deleted for history)
        $gasQuery = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)->with('user');
        if ($status !== 'all') {
            if ($status === 'cancellation_pending') {
                $gasQuery->where('cancellation_status', 'pending');
            } elseif ($status === 'in_process') {
                $gasQuery->whereIn('status', ['confirmed', 'approved', 'being_prepared', 'in_delivery', 'arrived']);
            } elseif ($status === 'completed') {
                $gasQuery->whereIn('status', ['completed', 'resolved']);
            } elseif ($status === 'rejected') {
                $gasQuery->whereIn('status', ['cancelled', 'rejected']);
            } else {
                $gasQuery->where('status', $status);
            }
        }

        // Buat query untuk mobil
        $mobilQuery = $this->applyRegionFilter(MobilBooking::withTrashed(), 'mobil', true)->with(['user', 'mobil']);
        if ($status !== 'all') {
            if ($status === 'cancellation_pending') {
                $mobilQuery->where('cancellation_status', 'pending');
            } elseif ($status === 'in_process') {
                $mobilQuery->whereIn('status', ['confirmed', 'approved', 'process', 'delivering', 'arrived']);
            } elseif ($status === 'completed') {
                $mobilQuery->whereIn('status', ['completed', 'resolved']);
            } elseif ($status === 'rejected') {
                $mobilQuery->whereIn('status', ['cancelled', 'rejected']);
            } else {
                $mobilQuery->where('status', $status);
            }
        }

        // Buat query untuk fasilitas umum
        $fasilitasQuery = $this->applyRegionFilter(FasilitasUmumBooking::withTrashed(), 'fasilitas', true)->with(['user', 'fasilitas']);
        if ($status !== 'all') {
            if ($status === 'cancellation_pending') {
                $fasilitasQuery->where('cancellation_status', 'pending');
            } elseif ($status === 'in_process') {
                $fasilitasQuery->whereIn('status', ['confirmed', 'approved', 'ongoing', 'delivering', 'arrived']);
            } elseif ($status === 'completed') {
                $fasilitasQuery->whereIn('status', ['completed', 'resolved']);
            } elseif ($status === 'rejected') {
                $fasilitasQuery->whereIn('status', ['cancelled', 'rejected']);
            } else {
                $fasilitasQuery->where('status', $status);
            }
        }

        // Buat query untuk pasar daerah
        $pasarQuery = \App\Models\PasarOrder::where('region_id', $user->region_id)->withTrashed()->with(['user', 'items.produk']);
        if ($status !== 'all') {
            if ($status === 'in_process') {
                $pasarQuery->whereIn('status', ['paid', 'confirmed', 'in_delivery']);
            } elseif ($status === 'completed') {
                $pasarQuery->whereIn('status', ['completed']);
            } elseif ($status === 'rejected') {
                $pasarQuery->whereIn('status', ['cancelled', 'rejected']);
            } else {
                $pasarQuery->where('status', $status);
            }
        }

        // Apply staff permission filters
        if (!$canViewRental) $rentalQuery->whereRaw('1 = 0');
        if (!$canViewGas) $gasQuery->whereRaw('1 = 0');
        if (!$canViewMobil) $mobilQuery->whereRaw('1 = 0');
        if (!$canViewFasilitas) $fasilitasQuery->whereRaw('1 = 0');
        if (!$canViewPasar) $pasarQuery->whereRaw('1 = 0');

        // Urutan prioritas status:
        // 1. Menunggu / Proses (pending, confirmed, approved, process, delivering, dll)
        // 2. Lainnya
        // 3. Selesai (completed, resolved, returned)
        // 4. Batal/Ditolak (cancelled, rejected)
        // Setelah diurutkan berdasarkan status, urutkan berdasarkan waktu terbaru (created_at DESC)
        $statusOrder = "
            CASE 
                WHEN status IN ('pending', 'confirmed', 'approved', 'process', 'delivering', 'arrived', 'ongoing', 'being_prepared', 'in_delivery') THEN 1
                WHEN status IN ('completed', 'resolved', 'returned') THEN 3
                WHEN status IN ('cancelled', 'rejected') THEN 4
                ELSE 2
            END ASC, created_at DESC
        ";

        // Ambil hasil berdasarkan filter kategori
        if ($category === 'rental') {
            $rentalRequests = $rentalQuery->orderByRaw($statusOrder)->get();
            $gasOrders = collect();
            $mobilRequests = collect();
            $fasilitasRequests = collect();
            $pasarOrders = collect();
        } elseif ($category === 'gas') {
            $rentalRequests = collect();
            $gasOrders = $gasQuery->orderByRaw($statusOrder)->get();
            $mobilRequests = collect();
            $fasilitasRequests = collect();
            $pasarOrders = collect();
        } elseif ($category === 'mobil') {
            $rentalRequests = collect();
            $gasOrders = collect();
            $mobilRequests = $mobilQuery->orderByRaw($statusOrder)->get();
            $fasilitasRequests = collect();
            $pasarOrders = collect();
        } elseif ($category === 'fasilitas') {
            $rentalRequests = collect();
            $gasOrders = collect();
            $mobilRequests = collect();
            $fasilitasRequests = $fasilitasQuery->orderByRaw($statusOrder)->get();
            $pasarOrders = collect();
        } elseif ($category === 'pasar') {
            $rentalRequests = collect();
            $gasOrders = collect();
            $mobilRequests = collect();
            $fasilitasRequests = collect();
            $pasarOrders = $pasarQuery->orderByRaw($statusOrder)->get();
        } elseif ($category === 'latest') {
            // Filter terbaru (7 hari terakhir)
            $rentalRequests = $rentalQuery->where('created_at', '>=', now()->subDays(7))->orderByRaw($statusOrder)->get();
            $gasOrders = $gasQuery->where('created_at', '>=', now()->subDays(7))->orderByRaw($statusOrder)->get();
            $mobilRequests = $mobilQuery->where('created_at', '>=', now()->subDays(7))->orderByRaw($statusOrder)->get();
            $fasilitasRequests = $fasilitasQuery->where('created_at', '>=', now()->subDays(7))->orderByRaw($statusOrder)->get();
            $pasarOrders = $pasarQuery->where('created_at', '>=', now()->subDays(7))->orderByRaw($statusOrder)->get();
        } else {
            $rentalRequests = $rentalQuery->orderByRaw($statusOrder)->get();
            $gasOrders = $gasQuery->orderByRaw($statusOrder)->get();
            $mobilRequests = $mobilQuery->orderByRaw($statusOrder)->get();
            $fasilitasRequests = $fasilitasQuery->orderByRaw($statusOrder)->get();
            $pasarOrders = $pasarQuery->orderByRaw($statusOrder)->get();
        }

        // Helper untuk stats agar filter wilayah selalu diterapkan
        $qRental = clone $rentalQuery; $qRental->getQuery()->orders = null;
        $qGas = clone $gasQuery; $qGas->getQuery()->orders = null;
        $qMobil = clone $mobilQuery; $qMobil->getQuery()->orders = null;
        $qFasilitas = clone $fasilitasQuery; $qFasilitas->getQuery()->orders = null;

        $baseRT = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true);
        $baseGT = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true);
        $baseMT = $this->applyRegionFilter(MobilBooking::withTrashed(), 'mobil', true);
        $baseFT = $this->applyRegionFilter(FasilitasUmumBooking::withTrashed(), 'fasilitas', true);
        $basePT = \App\Models\PasarOrder::where('region_id', $user->region_id)->withTrashed();

        // Hitung statistik (Include deleted for history functionality)
        $stats = [
            'total' => $baseRT->clone()->count() + $baseGT->clone()->count() + $baseMT->clone()->count() + $baseFT->clone()->count() + $basePT->clone()->count(),
            'pending' => $baseRT->clone()->where('status', 'pending')->count() + $baseGT->clone()->where('status', 'pending')->count() + $baseMT->clone()->where('status', 'pending')->count() + $baseFT->clone()->where('status', 'pending')->count() + $basePT->clone()->where('status', 'pending')->count(),
            'approved' => $baseRT->clone()->where('status', 'approved')->count() + $baseGT->clone()->where('status', 'approved')->count() + $baseMT->clone()->where('status', 'approved')->count() + $baseFT->clone()->where('status', 'approved')->count() + $basePT->clone()->where('status', 'confirmed')->count(),
            'rejected' => $baseRT->clone()->whereIn('status', ['cancelled', 'rejected'])->count() + $baseGT->clone()->whereIn('status', ['cancelled', 'rejected'])->count() + $baseMT->clone()->whereIn('status', ['cancelled', 'rejected'])->count() + $baseFT->clone()->whereIn('status', ['cancelled', 'rejected'])->count() + $basePT->clone()->whereIn('status', ['cancelled', 'rejected'])->count(),
            'cancellation_pending' => $baseRT->clone()->where('cancellation_status', 'pending')->count() + $baseGT->clone()->where('cancellation_status', 'pending')->count() + $baseMT->clone()->where('cancellation_status', 'pending')->count() + $baseFT->clone()->where('cancellation_status', 'pending')->count(),
            'rental_total' => $baseRT->clone()->count(),
            'gas_total' => $baseGT->clone()->count(),
            'mobil_total' => $baseMT->clone()->count(),
            'fasilitas_total' => $baseFT->clone()->count(),
            'pasar_total' => $basePT->clone()->count(),
            'active_rental_count' => $baseRT->clone()->whereIn('status', ['confirmed', 'being_prepared', 'in_delivery', 'arrived'])->sum('quantity'),
        ];

        // Hitung notifikasi detail
        $notificationCounts = [
            'rental' => [
                'pending' => $baseRT->clone()->where('status', 'pending')->count(),
                'cancellation' => $baseRT->clone()->where('cancellation_status', 'pending')->count(),
                'total' => $baseRT->clone()->where(function($q) { $q->where('status', 'pending')->orWhere('cancellation_status', 'pending'); })->count()
            ],
            'gas' => [
                'pending' => $baseGT->clone()->where('status', 'pending')->count(),
                'cancellation' => $baseGT->clone()->where('cancellation_status', 'pending')->count(),
                'total' => $baseGT->clone()->where(function($q) { $q->where('status', 'pending')->orWhere('cancellation_status', 'pending'); })->count()
            ],
            'mobil' => [
                'pending' => $baseMT->clone()->where('status', 'pending')->count(),
                'cancellation' => $baseMT->clone()->where('cancellation_status', 'pending')->count(),
                'total' => $baseMT->clone()->where(function($q) { $q->where('status', 'pending')->orWhere('cancellation_status', 'pending'); })->count()
            ],
            'fasilitas' => [
                'pending' => $baseFT->clone()->where('status', 'pending')->count(),
                'cancellation' => $baseFT->clone()->where('cancellation_status', 'pending')->count(),
                'total' => $baseFT->clone()->where(function($q) { $q->where('status', 'pending')->orWhere('cancellation_status', 'pending'); })->count()
            ]
        ];

        $activeServices = $this->getActivatedServices();

        if ($request->ajax()) {
            return response()
                ->view('admin.aktivitas.partials.requests_content', compact('rentalRequests', 'gasOrders', 'mobilRequests', 'fasilitasRequests', 'pasarOrders', 'stats', 'status', 'category', 'notificationCounts', 'activeServices'))
                ->withHeaders([
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ]);
        }

        return response()
            ->view('admin.aktivitas.requests', compact('rentalRequests', 'gasOrders', 'mobilRequests', 'fasilitasRequests', 'pasarOrders', 'stats', 'status', 'category', 'notificationCounts', 'activeServices'))
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
    }

    public function getCounts()
    {
        $counts = [
            'rental' => [
                'pending' => RentalBooking::where('status', 'pending')->count(),
                'cancellation' => RentalBooking::where('cancellation_status', 'pending')->count(),
                'total' => RentalBooking::where('status', 'pending')->orWhere('cancellation_status', 'pending')->count()
            ],
            'gas' => [
                'pending' => GasOrder::where('status', 'pending')->count(),
                'cancellation' => GasOrder::where('cancellation_status', 'pending')->count(),
                'total' => GasOrder::where('status', 'pending')->orWhere('cancellation_status', 'pending')->count()
            ],
            'mobil' => [
                'pending' => MobilBooking::where('status', 'pending')->count(),
                'cancellation' => MobilBooking::where('cancellation_status', 'pending')->count(),
                'total' => MobilBooking::where('status', 'pending')->orWhere('cancellation_status', 'pending')->count()
            ],
            'fasilitas' => [
                'pending' => FasilitasUmumBooking::where('status', 'pending')->count(),
                'cancellation' => FasilitasUmumBooking::where('cancellation_status', 'pending')->count(),
                'total' => FasilitasUmumBooking::where('status', 'pending')->orWhere('cancellation_status', 'pending')->count()
            ],
        ];

        return response()->json($counts);
    }

    public function show($id, $type)
    {
        // Aturannya sama dengan memproses: yang boleh dibuka detailnya hanya
        // unit yang dipegang dan pesanan dari wilayah sendiri. Tanpa ini,
        // findOrFail() polos membocorkan nama, alamat, dan telepon warga
        // lintas desa hanya dengan mengganti id di URL.
        $this->pastikanBolehMenangani($type, $id);

        if ($type === 'rental') {
            $request = RentalBooking::withTrashed()->with(['user', 'barang'])->findOrFail($id);
        } elseif ($type === 'mobil') {
            $request = MobilBooking::withTrashed()->with(['user', 'mobil'])->findOrFail($id);
        } elseif ($type === 'fasilitas') {
            $request = FasilitasUmumBooking::withTrashed()->with(['user', 'fasilitas'])->findOrFail($id);
        } else {
            $request = GasOrder::withTrashed()->with('user')->findOrFail($id);
        }

        return view('admin.aktivitas.request-detail', compact('request', 'type'));
    }

    public function approve(Request $request, $id, $type)
    {
        $this->pastikanBolehMenangani($type, $id);

        $notificationService = new NotificationService();

        try {
            DB::beginTransaction();

            if ($type === 'rental') {
                $model = RentalBooking::with('barang')->findOrFail($id);
                
                // Periksa apakah sudah disetujui
                if ($model->status !== 'pending') {
                    throw new \Exception("Permintaan sudah diproses sebelumnya.");
                }

                // Ambil barang dan validasi stok dengan PESSIMISTIC LOCK (mencegah race condition)
                $barang = \App\Models\Barang::where('id', $model->barang_id)->lockForUpdate()->firstOrFail();
                $quantity = $model->quantity;

                if (!$barang->hasStock($quantity)) {
                    // Kirim notifikasi tentang stok tidak mencukupi
                    $notificationService->notifyStockInsufficient($model, 'rental', $barang->stok, $quantity);
                    
                    throw new \Exception("Stok tidak mencukupi. Tersedia: {$barang->stok}, diminta: {$quantity}");
                }

                // Kurangi stok
                $barang->decreaseStock($quantity);

                // Periksa apakah stok rendah setelah pengurangan
                if ($barang->stok < 5) {
                    $notificationService->notifyLowStock($barang, 'barang', $barang->stok);
                }

                // Periksa apakah stok habis
                if ($barang->stok == 0) {
                    $notificationService->notifyStockDepleted($barang, 'barang');
                }

                // Perbarui status pemesanan
                $newStatus = 'confirmed';
                $updateData = [
                    'status' => $newStatus,
                    'confirmed_at' => now()
                ];
                
                // Generate order number if not exists
                if (!$model->order_number) {
                    $updateData['order_number'] = \App\Models\RentalBooking::generateOrderNumber();
                }
                
                $model->update($updateData);

            } elseif ($type === 'mobil') {
                $model = MobilBooking::with('mobil')->findOrFail($id);
                if ($model->status !== 'pending') throw new \Exception("Permintaan sudah diproses sebelumnya.");

                // PESSIMISTIC LOCK
                $mobil = \App\Models\Mobil::where('id', $model->mobil_id)->lockForUpdate()->firstOrFail();
                if ($mobil->status !== 'tersedia') throw new \Exception("Mobil sedang tidak tersedia.");
                
                $mobil->update(['status' => 'disewa']);

                $newStatus = 'confirmed';
                $updateData = ['status' => $newStatus, 'confirmed_at' => now()];
                $model->update($updateData);

            } elseif ($type === 'fasilitas') {
                $model = FasilitasUmumBooking::with('fasilitas')->findOrFail($id);
                if ($model->status !== 'pending') throw new \Exception("Permintaan sudah diproses sebelumnya.");

                // PESSIMISTIC LOCK
                $fasilitas = \App\Models\FasilitasUmum::where('id', $model->fasilitas_id)->lockForUpdate()->firstOrFail();
                if ($fasilitas->stok < 1) throw new \Exception("Fasilitas sedang tidak tersedia.");
                
                $fasilitas->decrement('stok');
                if ($fasilitas->stok == 0) {
                    $fasilitas->update(['status' => 'disewa']);
                }

                $newStatus = 'confirmed';
                $updateData = ['status' => $newStatus, 'confirmed_at' => now()];
                $model->update($updateData);

            } else {
                $model = GasOrder::findOrFail($id);
                
                // Periksa apakah sudah disetujui
                if ($model->status !== 'pending') {
                    throw new \Exception("Permintaan sudah diproses sebelumnya.");
                }

                // Ambil gas dan validasi stok dengan PESSIMISTIC LOCK
                $gas = \App\Models\Gas::where('id', $model->gas_id)->lockForUpdate()->firstOrFail();
                $quantity = $model->quantity;

                if (!$gas->hasStock($quantity)) {
                    // Send notifications about insufficient stock
                    $notificationService->notifyStockInsufficient($model, 'gas', $gas->stok, $quantity);
                    
                    throw new \Exception("Stok tidak mencukupi. Tersedia: {$gas->stok}, diminta: {$quantity}");
                }

                // Kurangi stok (PERMANEN untuk gas)
                $gas->decreaseStock($quantity);

                // Check if stock is low after decrease
                if ($gas->stok < 5) {
                    $notificationService->notifyLowStock($gas, 'gas', $gas->stok);
                }

                // Check if stock is depleted
                if ($gas->stok == 0) {
                    $notificationService->notifyStockDepleted($gas, 'gas');
                }

                // Perbarui status pesanan
                $newStatus = 'confirmed';
                $updateData = [
                    'status' => $newStatus,
                    'confirmed_at' => now()
                ];
                
                // Buat nomor pesanan jika belum ada
                if (!$model->order_number) {
                    $updateData['order_number'] = GasOrder::generateOrderNumber();
                }

                $model->update($updateData);
            }

            // Kirim notifikasi persetujuan berhasil ke pengguna
            $notificationService->notifyOrderApproved($model, $type);

            // Audit log for approval
            ActivityLog::create([
                'action' => 'approve_request',
                'description' => "Admin approved {$type} request #{$id}",
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            $message = "Permintaan {$type} berhasil disetujui dan stok telah diperbarui.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'status' => $newStatus
                ]);
            }

            session()->flash('success', $message);
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();

            $errorMessage = $e->getMessage();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 400);
            }

            session()->flash('error', $errorMessage);
            return redirect()->back();
        }
    }

    public function reject(Request $request, $id, $type)
    {
        $this->pastikanBolehMenangani($type, $id);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $notificationService = new NotificationService();

        if ($type === 'rental') {
            $model = RentalBooking::findOrFail($id);
            // RentalBooking menggunakan ENUM Ketat: pending, confirmed, in_progress, completed, cancelled
            // Kami menggunakan 'cancelled' untuk mewakili Penolakan oleh Admin, dan menjelaskan dalam catatan
            $newStatus = 'cancelled';
            $model->update([
                'status' => $newStatus,
                'admin_notes' => "Ditolak: " . $request->reason,
                'cancellation_reason' => "Ditolak Admin: " . $request->reason,
                // Pastikan cancellation_status tidak 'pending' untuk menghindari kebingungan
                'cancellation_status' => null 
            ]);
        } elseif ($type === 'mobil') {
            $model = MobilBooking::findOrFail($id);
            $newStatus = 'rejected';
            $model->update([
                'status' => $newStatus,
                'admin_notes' => "Ditolak: " . $request->reason,
                'cancellation_reason' => "Ditolak Admin: " . $request->reason,
                'cancellation_status' => null 
            ]);
        } elseif ($type === 'fasilitas') {
            $model = FasilitasUmumBooking::findOrFail($id);
            $newStatus = 'rejected';
            $model->update([
                'status' => $newStatus,
                'admin_notes' => "Ditolak: " . $request->reason,
                'cancellation_reason' => "Ditolak Admin: " . $request->reason,
                'cancellation_status' => null 
            ]);
        } else {
            $model = GasOrder::findOrFail($id);
            // GasOrder menggunakan status string, kemungkinan mendukung 'rejected'
            $newStatus = 'rejected';
            $model->update([
                'status' => $newStatus,
                'rejection_reason' => $request->reason,
                'handled_by' => auth()->id()
            ]);
        }

        // Batalkan pemasukannya DAN kembalikan uangnya ke dompet warga bila
        // pesanan ini sudah terbayar. Dulu di sini hanya ditandai 'rejected' —
        // uangnya lenyap dari saldo wilayah tanpa pernah menjadi milik siapa pun,
        // padahal fisiknya ada di rekening Midtrans Diskominfotik.
        $walletRefType = $type === 'gas' ? 'gas' : $type;
        \App\Models\WalletTransaction::batalkanDanRefund(
            $walletRefType,
            $model->id,
            'Pesanan ditolak admin: ' . $request->reason
        );

        // Otomatis bebaskan Supir jika order ditolak
        if (in_array($type, ['mobil', 'fasilitas']) && isset($model->assigned_supir_id)) {
            $supirToFree = \App\Models\Supir::find($model->assigned_supir_id);
            if ($supirToFree) {
                $supirToFree->status = 'Tersedia';
                $supirToFree->save();
            }
        }

        // Kirim notifikasi penolakan ke pengguna
        $notificationService->notifyOrderRejected($model, $request->reason, $type);

        // Audit log for rejection
        ActivityLog::create([
            'action' => 'reject_request',
            'description' => "Admin rejected {$type} request #{$id}. Reason: {$request->reason}",
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
        ]);

        $message = "Permintaan {$type} ditolak dengan alasan: {$request->reason}";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $newStatus
            ]);
        }

        session()->flash('warning', $message);
        return redirect()->back();
    }

    public function updateStatus(Request $request, $type, $id)
    {
        $this->pastikanBolehMenangani($type, $id);

        $request->validate([
            'status' => 'required|string|in:confirmed,being_prepared,in_delivery,arrived,completed,approved',
            'assigned_supir_id' => 'nullable|exists:supirs,id',
        ]);

        $notificationService = new NotificationService();

        if ($type === 'rental') {
            $order = \App\Models\RentalBooking::with('barang')->findOrFail($id);
        } elseif ($type === 'mobil') {
            $order = MobilBooking::findOrFail($id);
        } elseif ($type === 'fasilitas') {
            $order = FasilitasUmumBooking::findOrFail($id);
        } else {
            $order = GasOrder::findOrFail($id);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Catat siapa yang memproses
        $order->handled_by = auth()->id();

        try {
            DB::beginTransaction();

            // Buat stempel waktu otomatis berdasarkan status
            switch ($newStatus) {
                case 'confirmed':
                    if (!$order->confirmed_at) {
                        $order->confirmed_at = now();
                    }
                    // Buat nomor pesanan jika belum ada
                    if (!$order->order_number) {
                        $order->order_number = $type === 'rental' 
                            ? \App\Models\RentalBooking::generateOrderNumber()
                            : GasOrder::generateOrderNumber();
                    }
                    break;
                case 'being_prepared':
                    // Tidak ada kolom khusus, hanya pembaruan status
                    break;
                case 'in_delivery':
                    if (!$order->delivery_time) {
                        $order->delivery_time = now();
                    }
                    break;
                case 'arrived':
                    // Validasi bukti pengiriman harus ada jika status diubah ke arrived
                    // Kecuali jika metode pengiriman adalah jemput sendiri/diambil
                    
                    // Kita cek metode pengiriman
                    $isDelivery = false;
                    if ($type === 'rental') {
                        $isDelivery = $order->delivery_method == 'antar';
                    } else {
                        // Untuk gas, biasanya diantar, tapi check logic
                        $isDelivery = true; // Asumsi default gas diantar, sesuaikan jika ada field delivery_method di GasOrder
                        if (isset($order->delivery_method) && $order->delivery_method == 'jemput') {
                            $isDelivery = false;
                        }
                    }

                    if ($isDelivery && !$order->delivery_proof_image && !$request->hasFile('delivery_proof') && $oldStatus != 'arrived') {
                         // Jika ingin strict, uncomment baris ini. 
                         // Tapi karena kita handled upload terpisah, kita biarkan saja, tapi idealnya update ke arrived dilakukan via upload.
                         // throw new \Exception("Bukti pengiriman wajib diupload sebelum mengubah status ke Tiba.");
                    }

                    if (!$order->arrival_time) {
                        $order->arrival_time = now();
                    }
                    break;
                case 'completed':
                    if (!$order->completion_time) {
                        $order->completion_time = now();
                    }

                    // FIX: Return stock when admin marks rental as completed
                    if ($type === 'rental' && $oldStatus !== 'completed') {
                        // Ensure barang is loaded
                        if (!$order->relationLoaded('barang')) {
                            $order->load('barang');
                        }
                        
                        if ($order->barang) {
                            $order->barang->increaseStock($order->quantity);
                            
                            // Check if stock is still low even after return (rare but possible)
                            if ($order->barang->stok < 5 && $order->barang->stok > 0) {
                                $notificationService->notifyLowStock($order->barang, 'barang', $order->barang->stok);
                            }
                        }
                    } elseif ($type === 'mobil' && $oldStatus !== 'completed') {
                        if (!$order->relationLoaded('mobil')) $order->load('mobil');
                        if ($order->mobil) {
                            $order->mobil->update(['status' => 'tersedia']);
                        }
                    } elseif ($type === 'fasilitas' && $oldStatus !== 'completed') {
                        if (!$order->relationLoaded('fasilitas')) $order->load('fasilitas');
                        if ($order->fasilitas) {
                            $order->fasilitas->increment('stok');
                            $order->fasilitas->update(['status' => 'tersedia']);
                        }
                    }
                    
                    // Otomatis bebaskan Supir (kembali Tersedia) saat pesanan selesai
                    if (in_array($type, ['mobil', 'fasilitas']) && $order->assigned_supir_id && $oldStatus !== 'completed') {
                        $supirToFree = \App\Models\Supir::find($order->assigned_supir_id);
                        if ($supirToFree) {
                            $supirToFree->status = 'Tersedia';
                            $supirToFree->save();
                        }
                    }
                    break;
            }

            // PROSES OTOMATISASI PENUGASAN SUPIR
            $assignedSupirId = $request->input('assigned_supir_id');
            if (in_array($type, ['mobil', 'fasilitas']) && $assignedSupirId !== null && $assignedSupirId != $order->assigned_supir_id) {
                // Bebaskan supir lama jika ada
                if ($order->assigned_supir_id) {
                    $oldSupir = \App\Models\Supir::find($order->assigned_supir_id);
                    if ($oldSupir) {
                        $oldSupir->status = 'Tersedia';
                        $oldSupir->save();
                    }
                }
                
                // Tugaskan supir baru
                $order->assigned_supir_id = $assignedSupirId;
                $newSupir = \App\Models\Supir::find($assignedSupirId);
                if ($newSupir && $newStatus !== 'completed' && $newStatus !== 'cancelled' && $newStatus !== 'rejected') {
                    $newSupir->status = 'Sedang Bertugas';
                    $newSupir->save();
                }
            }

            // Perbarui status utama order
            $order->status = $newStatus;
            $order->save();

            // Hanya kirim notifikasi pembaruan status tertentu jika bukan 'completed'
            // (untuk completed kita mungkin ingin menanganinya secara berbeda atau mengizinkan switch case di service untuk menanganinya)
            // Berdasarkan permintaan pengguna completed juga memiliki pesan khusus
            
            $notificationService->notifyOrderStatusUpdate($order, $newStatus);

            // Log Activity
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Update Status',
                'description' => "Mengubah status pesanan #{$order->order_number} dari {$oldStatus} menjadi {$newStatus}",
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status berhasil diperbarui',
                    'order' => $order
                ]);
            }

            return redirect()->back()->with('success', 'Status berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error updating status: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function uploadDeliveryProof(Request $request, $type, $id)
    {
        $request->validate([
            'delivery_proof' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        try {
            DB::beginTransaction();

            if ($type === 'rental') {
                $order = \App\Models\RentalBooking::findOrFail($id);
            } else {
                $order = GasOrder::findOrFail($id);
            }

            // Simpan gambar
            if ($request->hasFile('delivery_proof')) {
                // Hapus bukti lama jika ada
                if ($order->delivery_proof_image) {
                    Storage::disk('public')->delete($order->delivery_proof_image);
                }

                $path = $request->file('delivery_proof')->store('delivery_proofs', 'public');
                
                if (!$path) {
                    throw new \Exception("Gagal menyimpan file gambar.");
                }

                $order->delivery_proof_image = $path;
                
                // Perbarui status otomatis ke arrived jika belum
                if ($order->status !== 'arrived' && $order->status !== 'completed') {
                    $order->status = 'arrived';
                    if (!$order->arrival_time) {
                        $order->arrival_time = now();
                    }
                }
                
                $order->save();

                // Kirim notifikasi ke pengguna
                Notification::create([
                    'title' => 'Bukti Pengiriman Tersedia',
                    'message' => "Bukti pengiriman untuk pesanan #{$order->order_number} telah tersedia.",
                    'type' => 'delivery_proof',
                    'user_id' => $order->user_id,
                    'admin_id' => auth()->id(),
                ]);
                
                // Log Activity
                \App\Models\ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'Upload Proof',
                    'description' => "Upload bukti pengiriman untuk pesanan #{$order->order_number}",
                    'ip_address' => $request->ip()
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Bukti pengiriman berhasil diunggah',
                    'path' => $path
                ]);
            }

            throw new \Exception("Tidak ada file yang diunggah.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Upload Proof Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleCancellation(Request $request, $type, $id, $action)
    {
        if (!in_array($action, ['approve', 'reject'])) {
            return response()->json([
                'success' => false,
                'message' => 'Aksi tidak valid'
            ], 400);
        }

        if ($type === 'rental') {
            $order = \App\Models\RentalBooking::findOrFail($id);
        } else {
            $order = GasOrder::findOrFail($id);
        }

        if ($order->cancellation_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada permintaan pembatalan yang pending'
            ], 400);
        }

        $order->cancellation_status = $action === 'approve' ? 'approved' : 'rejected';
        $order->admin_cancellation_response = $request->admin_response;
        $order->handled_by = auth()->id();

        $message = '';
        if ($action === 'approve') {
            $order->status = 'cancelled'; // Update status utama jadi cancelled
            $message = 'Permintaan pembatalan disetujui';
            
            // Logika pengembalian dana atau stok jika perlu (biasanya stok dikembalikan jika dibatalkan)
             if ($type === 'rental') {
                $order->load('barang');
                if ($order->barang) {
                    $order->barang->increaseStock($order->quantity);
                }
            }
            
            // Otomatis bebaskan Supir jika batal
            if (in_array($type, ['mobil', 'fasilitas']) && $order->assigned_supir_id) {
                $supirToFree = \App\Models\Supir::find($order->assigned_supir_id);
                if ($supirToFree) {
                    $supirToFree->status = 'Tersedia';
                    $supirToFree->save();
                }
            }
            
            // Kirim notifikasi ke pengguna
            Notification::create([
                'title' => 'Pembatalan Disetujui',
                'message' => "Permintaan pembatalan pesanan #{$order->order_number} Anda telah disetujui.",
                'type' => 'cancellation_approved',
                'user_id' => $order->user_id,
                'admin_id' => auth()->id(),
            ]);

        } else {
            // Jika ditolak, status utama tetap seperti sebelumnya (misal: pending atau confirmed)
            // Tidak perlu ubah $order->status
            $message = 'Permintaan pembatalan ditolak';
            
            // Kirim notifikasi ke pengguna
            Notification::create([
                'title' => 'Pembatalan Ditolak',
                'message' => "Permintaan pembatalan pesanan #{$order->order_number} ditolak. Alasan: {$request->admin_response}",
                'type' => 'cancellation_rejected',
                'user_id' => $order->user_id,
                'admin_id' => auth()->id(),
            ]);
        }

        // Log Activity
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Cancellation Review',
            'description' => ucfirst($action) . " permintaan pembatalan pesanan #{$order->order_number}",
            'ip_address' => $request->ip()
        ]);

        $order->save();

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
    public function returnRental(Request $request, $id)
    {
        $request->validate([
            'return_time' => 'required|date',
        ]);

        $notificationService = new NotificationService();
        $order = \App\Models\RentalBooking::with('barang')->findOrFail($id);

        if ($order->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan sudah selesai sebelumnya.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Perbarui waktu
            $order->return_time = $request->return_time;
            $order->completion_time = $request->return_time;
            $order->status = 'completed';
            $order->save();

            // PENGEMBALIAN STOK 
            $barang = $order->barang;
            $quantity = $order->quantity;

            // Tambah stok kembali
            $barang->increaseStock($quantity);

            // Notifikasi
            $notificationService->notifyRentalCompleted($order);

            // Periksa stok rendah
            if ($barang->stok < 5 && $barang->stok > 0) {
                $notificationService->notifyLowStock($barang, 'barang', $barang->stok);
            }

            // Log Activity
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Return Rental',
                'description' => "Memproses pengembalian alat pesanan #{$order->order_number}",
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil dikembalikan dan stok diperbarui',
                'return_time' => $request->return_time
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}