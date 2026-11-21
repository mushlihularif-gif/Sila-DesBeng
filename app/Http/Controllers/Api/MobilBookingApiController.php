<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mobil;
use App\Models\MobilBooking;

class MobilBookingApiController extends Controller
{
    /**
     * List semua kendaraan yang tersedia
     */
    public function index(Request $request)
    {
        $query = Mobil::inCatalog()->whereRaw('LOWER(status) = ?', ['tersedia'])
            ->where('stok', '>', 0);

        $user = $request->user('sanctum');
        if ($user && $user->region_id) {
            if (\Illuminate\Support\Facades\Schema::hasTable('regions') && \Illuminate\Support\Facades\Schema::hasTable('region_services')) {
                $visibleRegionIds = \App\Models\Region::wilayahLayananTerlihat($user->region_id, 'penyewaan-mobil');
                $query->whereIn('region_id', $visibleRegionIds);
            } else {
                $query->where('region_id', $user->region_id);
            }
        }

        $items = $query->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->nama_mobil,
                    'description' => $item->deskripsi,
                    'price' => (float) $item->harga_sewa,
                    'stock' => $item->stok,
                    'category' => $item->kategori,
                    'unit' => $item->satuan,
                    'location' => $item->lokasi,
                    'image' => $item->foto ? asset('storage/' . $item->foto) : null,
                    'image_2' => $item->foto_2 ? asset('storage/' . $item->foto_2) : null,
                    'image_3' => $item->foto_3 ? asset('storage/' . $item->foto_3) : null,
                    'type' => 'mobil',
                    'opsi_supir' => $item->opsi_supir,
                    'bbm_ditanggung' => $item->bbm_ditanggung,
                    'harga_dalam_desa' => (float) $item->harga_dalam_desa,
                    'harga_luar_desa' => (float) $item->harga_luar_desa,
                    'harga_luar_kota' => (float) $item->harga_luar_kota,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    /**
     * Detail kendaraan
     */
    public function show($id)
    {
        $item = Mobil::inCatalog()->find($id);

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kendaraan tidak ditemukan.',
            ], 404);
        }

        $firstSupir = (\Illuminate\Support\Facades\Schema::hasTable('mobil_supir') && \Illuminate\Support\Facades\Schema::hasTable('supirs'))
            ? $item->supirs()->first()
            : null;

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $item->id,
                'name' => $item->nama_mobil,
                'description' => $item->deskripsi,
                'price' => (float) $item->harga_sewa,
                'stock' => $item->stok,
                'category' => $item->kategori,
                'unit' => $item->satuan,
                'location' => $item->lokasi,
                'image' => $item->foto ? asset('storage/' . $item->foto) : null,
                'image_2' => $item->foto_2 ? asset('storage/' . $item->foto_2) : null,
                'image_3' => $item->foto_3 ? asset('storage/' . $item->foto_3) : null,
                'type' => 'mobil',
                'opsi_supir' => $item->opsi_supir,
                'nama_supir' => $firstSupir?->nama,
                'kontak_supir' => $firstSupir?->no_hp,
                'bbm_ditanggung' => $item->bbm_ditanggung,
                'harga_dalam_desa' => (float) $item->harga_dalam_desa,
                'batas_km_dalam_desa' => $item->batas_km_dalam_desa,
                'harga_luar_desa' => (float) $item->harga_luar_desa,
                'batas_km_luar_desa' => $item->batas_km_luar_desa,
                'harga_luar_kota' => (float) $item->harga_luar_kota,
                'payment_info' => \App\Support\ProfilPembayaranWilayah::untuk($item->region_id)->publik(),
            ],
        ]);
    }

    /**
     * Buat pemesanan kendaraan
     */
    public function store(Request $request)
    {
        $request->validate([
            'mobil_id' => 'required|exists:mobils,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'recipient_name' => 'required|string|max:255',
            'delivery_address' => 'nullable|string|max:500',
            'payment_method' => 'required|in:tunai,transfer',
            'payment_proof' => 'required_if:payment_method,transfer|nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'rental_purpose' => 'nullable|string|max:500',
            'delivery_method' => 'nullable|in:antar,jemput',
            'dengan_supir' => 'nullable|boolean',
            'distance_km' => 'nullable|numeric|min:0',
        ], ['payment_proof.required_if'=>'Unggah bukti pembayaran untuk melanjutkan pembayaran transfer atau e-wallet.', 'payment_proof.mimes'=>'Bukti pembayaran harus berupa JPG, PNG, atau PDF.', 'payment_proof.max'=>'Ukuran bukti pembayaran maksimal 5 MB.']);

        $proofPath = null;
        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, &$proofPath) {
                $mobil = Mobil::inCatalog()->lockForUpdate()->findOrFail($request->mobil_id);

                if (strtolower((string) $mobil->status) !== 'tersedia' || !$mobil->hasStock(1)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Kendaraan sedang tidak tersedia.',
                    ], 422);
                }

                $startDate = \Carbon\Carbon::parse($request->start_date);
                $endDate = \Carbon\Carbon::parse($request->end_date);
                $daysCount = $startDate->diffInDays($endDate) + 1;
                $totalAmount = $mobil->harga_sewa * $daysCount;

                if ($request->payment_method === 'transfer') {
                    $proofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
                }

                $booking = MobilBooking::create([
                    'user_id' => $request->user()->id,
                    'mobil_id' => $mobil->id,
                    'quantity' => 1,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'recipient_name' => $request->recipient_name,
                    'delivery_address' => $request->delivery_address,
                    'delivery_method' => $request->delivery_method ?? 'jemput',
                    'payment_method' => $request->payment_method,
                    'rental_purpose' => $request->rental_purpose,
                    'dengan_supir' => $request->dengan_supir ?? false,
                    'distance_km' => $request->distance_km ?? 1,
                    'total_amount' => $totalAmount,
                    'status' => 'pending',
                    'payment_proof' => $proofPath,
                    'region_id' => $mobil->region_id,
                ]);

                if ($mobil->region_id) {
                    \App\Models\WalletTransaction::catatPemasukan((int) $mobil->region_id, 'mobil', $booking->id, $totalAmount, $request->payment_method, $proofPath);
                    if (\Illuminate\Support\Facades\Schema::hasTable('admin_notifications')) {
                        \App\Models\AdminNotification::create([
                            'type' => 'mobil_booking',
                            'reference_id' => $booking->id,
                            'region_id' => (int) $mobil->region_id,
                            'title' => 'Pesanan Transportasi Baru',
                            'message' => "{$request->recipient_name} mengajukan sewa {$mobil->nama_mobil} (#{$booking->order_number}).",
                            'is_read' => false,
                        ]);
                    }
                }

                // Stok dialokasikan saat admin menyetujui, sama seperti pemesanan web.

                // Notifikasi untuk User
                \App\Models\Notification::create([
                    'user_id' => $request->user()->id,
                    'type' => 'status_berubah',
                    'title' => 'Pemesanan Berhasil',
                    'message' => 'Pemesanan Mobil (Order ID: ' . $booking->order_number . ') berhasil dibuat.',
                    'link' => \App\Services\NotificationService::orderLink($booking, 'mobil'),
                    'icon' => 'fas fa-car text-blue-500',
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pemesanan kendaraan berhasil dibuat.',
                    'data' => [
                        'id' => $booking->id,
                        'order_number' => $booking->order_number,
                        'item_name' => $mobil->nama_mobil,
                        'days_count' => $daysCount,
                        'total_amount' => (float) $totalAmount,
                        'dengan_supir' => $booking->dengan_supir,
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
        $bookings = MobilBooking::withoutGlobalScopes()
            ->where('user_id', $request->user()->id)
            ->with('mobil')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) {
                $startDate = $booking->start_date;
                $endDate = $booking->end_date;
                $daysCount = ($startDate && $endDate) ? $startDate->diffInDays($endDate) + 1 : 0;

                return [
                    'id' => $booking->id,
                    'order_number' => $booking->order_number,
                    'item_name' => $booking->mobil->nama_mobil ?? '-',
                    'days_count' => $daysCount,
                    'total_amount' => (float) $booking->total_amount,
                    'dengan_supir' => $booking->dengan_supir,
                    'status' => $booking->status,
                    'payment_method' => $booking->payment_method,
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
            $booking = MobilBooking::withoutGlobalScopes()->whereKey($id)
                ->where('user_id', $request->user()->id)->lockForUpdate()->firstOrFail();
            if ($booking->status !== 'pending') {
                return response()->json(['status' => 'error', 'message' => 'Hanya booking pending yang dapat dibatalkan langsung.'], 422);
            }
            // Pending belum mengalokasikan stok. Pembatalan tidak boleh menambah persediaan.
            $booking->update(['status' => 'cancelled', 'cancellation_reason' => $request->reason ?? 'Dibatalkan oleh pengguna']);
            \App\Models\WalletTransaction::batalkanDanRefund('mobil', $booking->id, 'Dibatalkan oleh pengguna');
            return response()->json(['status' => 'success', 'message' => 'Booking berhasil dibatalkan.']);
        });
    }
}
