<?php

namespace App\Http\Controllers\Admin;

use App\Models\RentalBooking;
use App\Models\GasOrder;
use App\Models\MobilBooking;
use App\Models\FasilitasUmumBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ActivityLog;

class TransactionController extends Controller
{
    /**
     * Jenis transaksi -> kunci izin unit staf.
     * Nilai $type datang dari route {type} di admin.aktivitas.bukti-transaksi.*
     *
     * Tipe di luar layanan aktif harus ditolak, bukan diteruskan ke GasOrder.
     */
    private const IZIN_PER_TIPE = [
        'rental'    => 'sewa_alat',
        'gas'       => 'gas',
        'mobil'     => 'sewa_mobil',
        'fasilitas' => 'fasilitas_umum',
    ];

    /**
     * Jenis transaksi -> [model, nama relasi yang memuat region_id].
     * Nama relasinya sama persis dengan yang dipakai index(), supaya yang boleh
     * DIPROSES tidak pernah lebih luas dari yang boleh DILIHAT.
     */
    private const WILAYAH_PER_TIPE = [
        'rental'    => [RentalBooking::class, 'barang'],
        'gas'       => [GasOrder::class, 'gas'],
        'mobil'     => [MobilBooking::class, 'mobil'],
        'fasilitas' => [FasilitasUmumBooking::class, 'fasilitas'],
    ];

    /**
     * Penjaga untuk semua tindakan atas bukti pembayaran.
     *
     * Menggantikan `abort_unless($user->role === 'admin')` yang membandingkan
     * peran secara harfiah. Peran yang benar-benar ada adalah admin_desa,
     * admin_kecamatan, super_admin, dan staff - ketiganya kena 403, sehingga
     * bukti transfer yang diunggah warga bisa dilihat tetapi tidak pernah bisa
     * dinyatakan lunas maupun ditolak oleh siapa pun yang memegang uangnya.
     *
     * Dua hal diperiksa, keduanya wajib:
     *
     * 1. IZIN UNIT - staf gas tidak boleh menyentuh bukti bayar sewa alat.
     * 2. KEPEMILIKAN WILAYAH - tanpa ini, melonggarkan peran justru membuka
     *    celah baru: admin Desa A tinggal mengganti id di URL untuk
     *    memverifikasi uang Desa B.
     *
     * Dipakai juga oleh downloadProof(), yang sebelumnya sama sekali tidak
     * punya pemeriksaan apa pun padahal berkasnya memuat nama pengirim,
     * nominal, dan nomor rekening.
     */
    private function pastikanBolehMenangani(string $type, $id): void
    {
        $user = auth()->user();

        $izin = self::IZIN_PER_TIPE[$type] ?? null;
        abort_if($izin === null, 404, 'Jenis transaksi tidak dikenal.');

        abort_unless(
            $user && $user->isAdmin() && $user->hasUnitPermission($izin),
            403,
            'Anda tidak memiliki izin untuk memproses bukti pembayaran unit ini.'
        );

        [$model, $relasi] = self::WILAYAH_PER_TIPE[$type];

        $milikWilayahIni = $this->applyRegionFilter($model::withTrashed(), $relasi, true)
            ->whereKey($id)
            ->exists();

        abort_unless($milikWilayahIni, 404, 'Transaksi tidak ditemukan di wilayah Anda.');
    }

    public function index(Request $request)
    {
        // Get filter parameters
        $request->validate(['category' => 'sometimes|in:all,latest,rental,gas,mobil,fasilitas']);
        $category = $request->get('category', 'all');
        $paymentMethod = $request->get('payment_method', 'all');
        $search = $request->get('search');

        // Build queries
        $user = auth()->user();
        $isStaff = $user->isStaff();
        
        $canViewRental = !$isStaff || $user->hasUnitPermission('sewa_alat');
        $canViewGas = !$isStaff || $user->hasUnitPermission('gas');
        $canViewMobil = !$isStaff || $user->hasUnitPermission('sewa_mobil');
        $canViewFasilitas = !$isStaff || $user->hasUnitPermission('fasilitas_umum');
        // Include transactions with proof OR system generated (active/completed statuses)
        $activeStatuses = ['confirmed', 'approved', 'being_prepared', 'in_delivery', 'arrived', 'completed', 'returned'];
        
        $rentalQuery = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)->with(['user', 'barang', 'walletEntries'])->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('payment_proof')
              ->orWhereIn('status', $activeStatuses);
        });

        $gasQuery = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)->with(['user', 'walletEntries'])->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('proof_of_payment')
              ->orWhereIn('status', $activeStatuses);
        });

        $mobilQuery = $this->applyRegionFilter(MobilBooking::withTrashed(), 'mobil', true)->with(['user', 'mobil', 'walletEntries'])->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('payment_proof')
              ->orWhereIn('status', $activeStatuses);
        });

        $fasilitasQuery = $this->applyRegionFilter(FasilitasUmumBooking::withTrashed(), 'fasilitas', true)->with(['user', 'fasilitas', 'walletEntries'])->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('payment_proof')
              ->orWhereIn('status', $activeStatuses);
        });


        // Apply staff permission filters
        if (!$canViewRental) $rentalQuery->whereRaw('1 = 0');
        if (!$canViewGas) $gasQuery->whereRaw('1 = 0');
        if (!$canViewMobil) $mobilQuery->whereRaw('1 = 0');
        if (!$canViewFasilitas) $fasilitasQuery->whereRaw('1 = 0');

        // Filter by search
        if ($search) {
            $rentalQuery->where(function($q) use ($search) {
                $q->searchWhereLike('status', $search)
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->searchWhereLike('name', $search);
                  });
            });
            
            $gasQuery->where(function($q) use ($search) {
                $q->searchWhereLike('status', $search)
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->searchWhereLike('name', $search);
                  });
            });

            $mobilQuery->where(function($q) use ($search) {
                $q->searchWhereLike('status', $search)
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->searchWhereLike('name', $search);
                  });
            });

            $fasilitasQuery->where(function($q) use ($search) {
                $q->searchWhereLike('status', $search)
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->searchWhereLike('name', $search);
                  });
            });
        }

        // Filter by payment method
        if ($paymentMethod !== 'all') {
            $rentalQuery->where('payment_method', $paymentMethod);
            $gasQuery->where('payment_method', $paymentMethod);
            $mobilQuery->where('payment_method', $paymentMethod);
            $fasilitasQuery->where('payment_method', $paymentMethod);
        }

        // Get results based on category filter
        if ($category === 'rental') {
            $rentalPayments = $rentalQuery->orderByDesc('updated_at')->get();
            $gasPayments = collect();
            $mobilPayments = collect();
            $fasilitasPayments = collect();
        } elseif ($category === 'gas') {
            $rentalPayments = collect();
            $gasPayments = $gasQuery->orderByDesc('updated_at')->get();
            $mobilPayments = collect();
            $fasilitasPayments = collect();
        } elseif ($category === 'mobil') {
            $rentalPayments = collect();
            $gasPayments = collect();
            $mobilPayments = $mobilQuery->orderByDesc('updated_at')->get();
            $fasilitasPayments = collect();
        } elseif ($category === 'fasilitas') {
            $rentalPayments = collect();
            $gasPayments = collect();
            $mobilPayments = collect();
            $fasilitasPayments = $fasilitasQuery->orderByDesc('updated_at')->get();
        } else {
            $rentalPayments = $rentalQuery->orderByDesc('updated_at')->get();
            $gasPayments = $gasQuery->orderByDesc('updated_at')->get();
            $mobilPayments = $mobilQuery->orderByDesc('updated_at')->get();
            $fasilitasPayments = $fasilitasQuery->orderByDesc('updated_at')->get();
        }

        // Count statistics (WITH region filter to match the table data)
        // Kita simpan query dasar agar tidak mengeksekusi ulang query yang sudah dimodifikasi filter
        $baseRental = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('payment_proof')->orWhereIn('status', $activeStatuses);
        });
        if (!$canViewRental) $baseRental->whereRaw('1 = 0');
        $rentalCount = (clone $baseRental)->count();
        
        $baseGas = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('proof_of_payment')->orWhereIn('status', $activeStatuses);
        });
        if (!$canViewGas) $baseGas->whereRaw('1 = 0');
        $gasCount = (clone $baseGas)->count();

        $baseMobil = $this->applyRegionFilter(MobilBooking::withTrashed(), 'mobil', true)->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('payment_proof')->orWhereIn('status', $activeStatuses);
        });
        if (!$canViewMobil) $baseMobil->whereRaw('1 = 0');
        $mobilCount = (clone $baseMobil)->count();

        $baseFasilitas = $this->applyRegionFilter(FasilitasUmumBooking::withTrashed(), 'fasilitas', true)->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('payment_proof')
              ->orWhereIn('status', $activeStatuses);
        });
        if (!$canViewFasilitas) $baseFasilitas->whereRaw('1 = 0');
        $fasilitasCount = (clone $baseFasilitas)->count();


        // Gabungkan semua metode pembayaran
        $allMethods = collect();
        $allMethods = $allMethods->concat((clone $baseRental)->pluck('payment_method'));
        $allMethods = $allMethods->concat((clone $baseGas)->pluck('payment_method'));
        $allMethods = $allMethods->concat((clone $baseMobil)->pluck('payment_method'));
        $allMethods = $allMethods->concat((clone $baseFasilitas)->pluck('payment_method'));

        $transferCount = 0;
        $digitalCount = 0;
        $tunaiCount = 0;

        foreach ($allMethods as $method) {
            $m = strtolower($method ?? '');
            if (str_contains($m, 'transfer') || str_contains($m, 'bank')) {
                $transferCount++;
            } elseif (str_contains($m, 'ewallet') || str_contains($m, 'qris') || str_contains($m, 'gopay') || str_contains($m, 'ovo') || str_contains($m, 'dana') || str_contains($m, 'shopee') || str_contains($m, 'linkaja') || str_contains($m, 'digital')) {
                $digitalCount++;
            } else {
                // Asumsi jika bukan transfer dan bukan digital, maka Tunai / Cash
                $tunaiCount++;
            }
        }

        $stats = [
            'total' => $rentalCount + $gasCount + $mobilCount + $fasilitasCount,
            'rental_total' => $rentalCount,
            'gas_total' => $gasCount,
            'mobil_total' => $mobilCount,
            'fasilitas_total' => $fasilitasCount,
            'transfer_total' => $transferCount,
            'digital_total' => $digitalCount,
            'cash_total' => $tunaiCount,
        ];

        $activeServices = $this->getActivatedServices();

        return view('admin.aktivitas.transactions', compact('rentalPayments', 'gasPayments', 'mobilPayments', 'fasilitasPayments', 'stats', 'category', 'paymentMethod', 'search', 'activeServices'));
    }

    public function verify(Request $request, $id, $type)
    {
        if ($type === 'rental') return app(\App\Services\RentalOrderActions::class)->admin($request, (int)$id, 'verify');
        if ($type === 'gas') return app(\App\Services\GasOrderActions::class)->admin($request, (int)$id, 'verify');
        return $this->reviewPayment($request, $id, $type, false);
    }

    public function reject(Request $request, $id, $type)
    {
        if ($type === 'rental') return app(\App\Services\RentalOrderActions::class)->admin($request, (int)$id, 'reject-payment');
        if ($type === 'gas') return app(\App\Services\GasOrderActions::class)->admin($request, (int)$id, 'reject-payment');
        $request->validate(['reason'=>'required|string|max:500']);
        return $this->reviewPayment($request, $id, $type, true);
    }

    private function reviewPayment(Request $request, int $id, string $type, bool $reject)
    {
        $this->pastikanBolehMenangani($type, $id);
        $shortfallRecorded = false;
        \DB::transaction(function () use ($request, $id, $type, $reject, &$shortfallRecorded) {
            [$model] = self::WILAYAH_PER_TIPE[$type];
            $order = $model::lockForUpdate()->findOrFail($id);
            abort_if(in_array($order->status,['completed','cancelled','rejected','returned'],true),409,'Pesanan sudah ditutup.');
            $rows = \App\Models\WalletTransaction::where('reference_type',$type)->where('reference_id',$id)->lockForUpdate()->get();
            abort_unless($rows->isNotEmpty() && abs((float)$rows->sum('amount')-(float)$order->total_amount)<0.005
                && $rows->every(fn($r)=>(int)$r->region_id === (int)auth()->user()->region_id && $r->source==='manual' && in_array($r->status,['pending','verified'],true)),409,'Nominal, wilayah, atau catatan pembayaran perlu diperiksa.');
            $paid=$rows->every(fn($r)=>$r->status==='verified');
            if ($reject) {
                abort_if($paid || $rows->contains('status','verified') || !$order->payment_proof,409,'Pembayaran ini tidak dapat dikembalikan untuk diperbaiki.');
                $receivedPartial = \App\Support\PaymentVerificationGuard::parsePartialReceivedAmount($request, (float) $order->total_amount);
                $oldProof = $order->payment_proof;
                \DB::afterCommit(fn () => Storage::disk('public')->delete($oldProof));
                $order->payment_proof=null;
                $order->save();
                if ($receivedPartial !== null && $receivedPartial > 0) {
                    $shortfallRecorded = true;
                    $remaining = max(0, round((float) $order->total_amount - $receivedPartial, 2));
                    \App\Models\WalletTransaction::catatKurangBayar($type, $id, $receivedPartial, $request->reason, auth()->id());
                    $notifTitle = 'Pembayaran Kurang — Perlu Pelunasan';
                    $notifMsg = sprintf(
                        'Baru diterima Rp %s dari subtotal Rp %s (kurang Rp %s) untuk pesanan #%d. %s Silakan lunasi kekurangan dan unggah bukti pelunasan.',
                        number_format($receivedPartial, 0, ',', '.'),
                        number_format((float) $order->total_amount, 0, ',', '.'),
                        number_format($remaining, 0, ',', '.'),
                        $id,
                        $request->reason
                    );
                } else {
                    $updatePayload = ['proof_path'=>null,'notes'=>$request->reason];
                    if (\App\Models\WalletTransaction::hasVerificationColumns()) {
                        $updatePayload['detected_proof_amount'] = null;
                        $updatePayload['proof_scan_status'] = null;
                        $updatePayload['proof_scan_meta'] = null;
                    }
                    \App\Models\WalletTransaction::whereKey($rows->modelKeys())->update($updatePayload);
                    $notifTitle = 'Perbaiki Bukti Pembayaran';
                    $notifMsg = 'Bukti pembayaran perlu diperbaiki: '.$request->reason;
                }
            } else {
                abort_unless(strtolower((string) $order->payment_method)==='tunai' || $order->payment_proof,422,'Unggah bukti pembayaran terlebih dahulu.');
                if ($paid) return;
                $confirmedAmount = \App\Support\PaymentVerificationGuard::validateConfirmedAmount(
                    $request,
                    (float) $order->total_amount,
                    $order->paidAmount()
                );
                \App\Models\WalletTransaction::tandaiPembayaranManualDiterima($type,$id,auth()->id(),$confirmedAmount);
                $notifTitle = 'Pembayaran Diterima (Lunas)';
                $notifMsg = 'Pembayaran pesanan #'.$id.' sebesar Rp '.number_format($confirmedAmount, 0, ',', '.').' telah diterima. Proses layanan dilanjutkan pengelola.';
            }
            ActivityLog::create(['user_id'=>auth()->id(),'action'=>$reject?($shortfallRecorded?'record_shortfall_payment':'reject_payment'):'verify_payment',
                'description'=>$type.' #'.$id.': '.$notifMsg,'ip_address'=>$request->ip()]);
            Notification::create(['user_id'=>$order->user_id,'type'=>'status_berubah','title'=>$notifTitle,
                'message'=>$notifMsg]);
        });
        $flash = $reject
            ? ($shortfallRecorded ? 'Nominal kurang bayar dicatat dan warga diminta mengunggah bukti pelunasan.' : 'Bukti dikembalikan untuk diperbaiki.')
            : 'Pembayaran dikonfirmasi lunas. Status layanan tidak berubah.';
        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $flash])
            : back()->with('success', $flash);
    }

    /**
     * Pindai ulang nominal pada foto bukti transfer menggunakan AI Vision / OCR
     * untuk membantu Admin/Staf mendeteksi transfer kurang bayar.
     */
    public function scanProof(Request $request, $id, $type)
    {
        $this->pastikanBolehMenangani($type, $id);
        [$model] = self::WILAYAH_PER_TIPE[$type];
        $order = $model::withTrashed()->findOrFail($id);

        $proofPath = $order->paymentProofPath();
        abort_unless($proofPath, 422, 'Pesanan ini belum memiliki file bukti pembayaran.');

        $result = app(\App\Services\PaymentProofAnalyzerService::class)->analyzeAndStore(
            $type,
            (int) $order->id,
            $proofPath,
            $order->subtotalAmount(),
            $order->paidAmount(),
            true
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'scan' => $result,
            ]);
        }

        $flashType = ($result['status'] ?? '') === 'mismatch' ? 'error' : 'success';

        return back()->with($flashType, $result['message'] ?? 'Pemindaian bukti selesai.');
    }

    public function updateStatus(Request $request, $id, $type, $status)
    {
        $request->merge(['status'=>$status]);
        return app(RequestController::class)->updateStatus($request,$type,$id);
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'being_prepared' => 'Sedang Dipersiapkan',
            'in_delivery' => 'Dalam Pengiriman',
            'arrived' => 'Tiba di Lokasi',
            'completed' => 'Selesai',
            'approved' => 'Siap Diambil',
        ];

        return $labels[$status] ?? $status;
    }

    public function downloadProof($id, $type)
    {
        $this->pastikanBolehMenangani($type, $id);

        if ($type === 'rental') {
            $model = RentalBooking::withTrashed()->findOrFail($id);
        } elseif ($type === 'mobil') {
            $model = MobilBooking::withTrashed()->findOrFail($id);
        } elseif ($type === 'fasilitas') {
            $model = FasilitasUmumBooking::withTrashed()->findOrFail($id);
        } else {
            $model = GasOrder::withTrashed()->findOrFail($id);
        }

        // Determine proof column
        $proofColumn = ($type === 'gas') ? 'proof_of_payment' : 'payment_proof';

        if (!$model->$proofColumn) {
            abort(404, 'Bukti pembayaran tidak ditemukan.');
        }

        // Check if file exists in storage
        if (!Storage::disk('public')->exists($model->$proofColumn)) {
            abort(404, 'File bukti pembayaran tidak ditemukan di storage.');
        }

        return Storage::disk('public')->download($model->$proofColumn);
    }
}
