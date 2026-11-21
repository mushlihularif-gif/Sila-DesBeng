<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FasilitasUmum;
use App\Models\FasilitasUmumBooking;

class FasilitasBookingApiController extends Controller
{
    /**
     * List semua fasilitas yang tersedia
     */
    public function index(Request $request)
    {
        $query = FasilitasUmum::inCatalog()->whereRaw('LOWER(status) = ?', ['tersedia']);

        $user = $request->user('sanctum');
        if ($user && $user->region_id) {
            if (\Illuminate\Support\Facades\Schema::hasTable('regions') && \Illuminate\Support\Facades\Schema::hasTable('region_services')) {
                $visibleRegionIds = \App\Models\Region::wilayahLayananTerlihat($user->region_id, 'fasilitas-umum');
                $query->whereIn('region_id', $visibleRegionIds);
            } else {
                $query->where('region_id', $user->region_id);
            }
        }

        $items = $query->get()
            ->map(function ($item) {
                $isVehicle = str_contains(strtolower($item->nama_fasilitas), 'mobil')
                    || str_contains(strtolower($item->nama_fasilitas), 'bus')
                    || str_contains(strtolower($item->nama_fasilitas), 'kendaraan');

                return [
                    'id' => $item->id,
                    'name' => $item->nama_fasilitas,
                    'description' => $item->deskripsi,
                    'stock' => $item->stok,
                    'category' => $item->kategori,
                    'location' => $item->lokasi,
                    'image' => $item->foto ? asset('storage/' . $item->foto) : null,
                    'image_2' => $item->foto_2 ? asset('storage/' . $item->foto_2) : null,
                    'image_3' => $item->foto_3 ? asset('storage/' . $item->foto_3) : null,
                    'type' => 'fasilitas',
                    'is_ambulance' => false,
                    'is_vehicle' => $isVehicle,
                    'opsi_supir' => $item->opsi_supir,
                    'bbm_ditanggung' => $item->bbm_ditanggung,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    /**
     * Detail fasilitas
     */
    public function show($id)
    {
        $item = FasilitasUmum::inCatalog()->find($id);

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fasilitas tidak ditemukan.',
            ], 404);
        }

        $firstPengurus = (\Illuminate\Support\Facades\Schema::hasTable('fasilitas_pengurus') && \Illuminate\Support\Facades\Schema::hasTable('supirs'))
            ? $item->pengurus()->first()
            : null;

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $item->id,
                'name' => $item->nama_fasilitas,
                'description' => $item->deskripsi,
                'stock' => $item->stok,
                'category' => $item->kategori,
                'location' => $item->lokasi,
                'image' => $item->foto ? asset('storage/' . $item->foto) : null,
                'image_2' => $item->foto_2 ? asset('storage/' . $item->foto_2) : null,
                'image_3' => $item->foto_3 ? asset('storage/' . $item->foto_3) : null,
                'type' => 'fasilitas',
                'is_ambulance' => false,
                'opsi_supir' => $item->opsi_supir,
                'nama_supir' => $firstPengurus?->nama,
                'kontak_supir' => $firstPengurus?->no_hp,
                'bbm_ditanggung' => $item->bbm_ditanggung,
                'price' => (float) $item->harga_sewa,
                'status_biaya' => $item->status_biaya,
                'payment_info' => \App\Support\ProfilPembayaranWilayah::untuk($item->region_id)->publik(),
            ],
        ]);
    }

    /**
     * Buat pemesanan fasilitas
     */
    public function store(Request $request)
    {
        $request->validate([
            'fasilitas_id' => 'required|exists:fasilitas_umums,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'rental_purpose' => 'nullable|string|max:500',
            'jenis_acara' => 'nullable|in:sosial,komersial,pribadi',
            'butuh_gudang' => 'nullable|boolean',
            'quantity' => 'nullable|integer|min:1|max:100',
            'payment_method' => 'nullable|in:tunai,transfer',
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'delivery_method' => 'nullable|string',
            'dengan_supir' => 'nullable|boolean',
        ], ['payment_proof.required_if'=>'Unggah bukti pembayaran untuk melanjutkan pembayaran transfer atau e-wallet.', 'payment_proof.mimes'=>'Bukti pembayaran harus berupa JPG, PNG, atau PDF.', 'payment_proof.max'=>'Ukuran bukti pembayaran maksimal 5 MB.']);

        $proofPath = null;
        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, &$proofPath) {
                $fasilitas = FasilitasUmum::inCatalog()->lockForUpdate()->findOrFail($request->fasilitas_id);

                if (strtolower((string) $fasilitas->status) !== 'tersedia' || ($fasilitas->stok !== null && $fasilitas->stok < ($request->quantity ?? 1))) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Fasilitas sedang tidak tersedia.',
                    ], 422);
                }

                $startDate = \Carbon\Carbon::parse($request->start_date);
                $endDate = \Carbon\Carbon::parse($request->end_date);
                $daysCount = $startDate->diffInDays($endDate) + 1;

                // Harga mengikuti katalog dan kebijakan yang sama dengan web.
                $jenisAcara = $request->jenis_acara === 'pribadi' ? 'komersial' : ($request->jenis_acara ?? 'sosial');
                $totalAmount = $fasilitas->status_biaya === 'berbayar' && $jenisAcara === 'komersial'
                    ? $daysCount * $fasilitas->harga_sewa * ($request->quantity ?? 1) : 0;
                $paymentMethod = $totalAmount > 0 ? ($request->payment_method ?? 'tunai') : null;

                if ($paymentMethod === 'transfer') {
                    $request->validate(['payment_proof'=>'required|file|mimes:jpg,jpeg,png,pdf|max:5120'], ['payment_proof.required'=>'Unggah bukti transfer untuk melanjutkan pemesanan.']);
                    $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
                }

                $booking = FasilitasUmumBooking::create([
                    'user_id' => $request->user()->id,
                    'fasilitas_id' => $fasilitas->id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'quantity' => $request->quantity ?? 1,
                    'rental_purpose' => $request->rental_purpose,
                    'jenis_acara' => $jenisAcara,
                    'payment_method' => $paymentMethod,
                    'butuh_gudang' => $request->butuh_gudang ?? false,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'payment_proof' => $proofPath,
                    'delivery_method' => $request->delivery_method ?? 'jemput',
                    'dengan_supir' => $request->dengan_supir ?? false,
                    'region_id' => $fasilitas->region_id,
                ]);

                if ($totalAmount > 0 && $fasilitas->region_id) {
                    \App\Models\WalletTransaction::catatPemasukan((int) $fasilitas->region_id, 'fasilitas', $booking->id, $totalAmount, $paymentMethod, $proofPath);
                }
                if ($fasilitas->region_id && \Illuminate\Support\Facades\Schema::hasTable('admin_notifications')) {
                    \App\Models\AdminNotification::create([
                        'type' => 'fasilitas_booking',
                        'reference_id' => $booking->id,
                        'region_id' => (int) $fasilitas->region_id,
                        'title' => 'Peminjaman Fasilitas Umum Baru',
                        'message' => ($request->user()->name ?? 'Warga') . " mengajukan peminjaman {$fasilitas->nama_fasilitas} (#{$booking->order_number}).",
                        'is_read' => false,
                    ]);
                }
                // Stok dialokasikan saat admin menyetujui, sama seperti pemesanan web.

                // Notifikasi untuk User
                \App\Models\Notification::create([
                    'user_id' => $request->user()->id,
                    'type' => 'status_berubah',
                    'title' => 'Pemesanan Berhasil',
                    'message' => 'Pemesanan Fasilitas (Order ID: ' . $booking->order_number . ') berhasil dibuat.',
                    'link' => \App\Services\NotificationService::orderLink($booking, 'fasilitas'),
                    'icon' => 'fas fa-calendar-check text-green-500',
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pemesanan fasilitas berhasil dibuat.',
                    'data' => [
                        'id' => $booking->id,
                        'order_number' => $booking->order_number,
                        'item_name' => $fasilitas->nama_fasilitas,
                        'days_count' => $daysCount,
                        'jenis_acara' => $booking->jenis_acara,
                        'total_amount' => (float) $totalAmount,
                        'status' => $booking->status,
                        'start_date' => $booking->start_date->format('Y-m-d'),
                        'end_date' => $booking->end_date->format('Y-m-d'),
                    ],
                ], 201);
            });
        } catch (\Throwable $e) {
            if ($proofPath) \Illuminate\Support\Facades\Storage::disk('public')->delete($proofPath);
            throw $e;
        }
    }

    /**
     * Riwayat booking user
     */
    public function myBookings(Request $request)
    {
        $bookings = FasilitasUmumBooking::withoutGlobalScopes()
            ->where('user_id', $request->user()->id)
            ->with('fasilitas')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) {
                $startDate = $booking->start_date;
                $endDate = $booking->end_date;
                $daysCount = ($startDate && $endDate) ? $startDate->diffInDays($endDate) + 1 : 0;

                return [
                    'id' => $booking->id,
                    'order_number' => $booking->order_number,
                    'item_name' => $booking->fasilitas->nama_fasilitas ?? '-',
                    'days_count' => $daysCount,
                    'jenis_acara' => $booking->jenis_acara,
                    'total_amount' => (float) $booking->total_amount,
                    'status' => $booking->status,
                    'start_date' => $booking->start_date?->format('Y-m-d'),
                    'end_date' => $booking->end_date?->format('Y-m-d'),
                    'created_at' => $booking->created_at->toISOString(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $bookings,
        ]);
    }

    /**
     * Batalkan booking
     */
    public function cancel(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
            $booking = FasilitasUmumBooking::withoutGlobalScopes()->whereKey($id)
                ->where('user_id', $request->user()->id)->lockForUpdate()->firstOrFail();
            if ($booking->status !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Hanya booking pending yang dapat dibatalkan langsung.'], 422);
            }
            // Pending belum mengalokasikan stok. Pembatalan tidak boleh menambah persediaan.
            $booking->update(['status' => 'cancelled', 'cancellation_reason' => $request->reason ?? 'Dibatalkan oleh pengguna']);
            \App\Models\WalletTransaction::batalkanDanRefund('fasilitas', $booking->id, 'Dibatalkan oleh pengguna');
            return response()->json(['status' => 'success', 'message' => 'Booking berhasil dibatalkan.']);
        });
    }
}
