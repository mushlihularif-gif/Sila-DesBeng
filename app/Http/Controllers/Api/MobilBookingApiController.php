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
    public function index()
    {
        $items = Mobil::where('status', 'tersedia')
            ->where('stok', '>', 0)
            ->get()
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
        $item = Mobil::find($id);

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kendaraan tidak ditemukan.',
            ], 404);
        }

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
                'nama_supir' => $item->nama_supir,
                'kontak_supir' => $item->kontak_supir,
                'bbm_ditanggung' => $item->bbm_ditanggung,
                'harga_dalam_desa' => (float) $item->harga_dalam_desa,
                'batas_km_dalam_desa' => $item->batas_km_dalam_desa,
                'harga_luar_desa' => (float) $item->harga_luar_desa,
                'batas_km_luar_desa' => $item->batas_km_luar_desa,
                'harga_luar_kota' => (float) $item->harga_luar_kota,
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
            'rental_purpose' => 'nullable|string|max:500',
            'delivery_method' => 'nullable|in:antar,jemput',
            'dengan_supir' => 'nullable|boolean',
            'distance_km' => 'nullable|numeric|min:0',
        ]);

        $mobil = Mobil::findOrFail($request->mobil_id);

        if (!$mobil->hasStock(1)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Kendaraan sedang tidak tersedia.',
            ], 422);
        }

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $daysCount = $startDate->diffInDays($endDate) + 1;
        $totalAmount = $mobil->harga_sewa * $daysCount;

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
            'distance_km' => $request->distance_km,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'region_id' => $request->user()->region_id,
        ]);

        // Kurangi stok
        $mobil->decreaseStock(1);

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
        $booking = MobilBooking::withoutGlobalScopes()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking tidak ditemukan.',
            ], 404);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya booking dengan status pending yang bisa dibatalkan.',
            ], 422);
        }

        // Kembalikan stok
        if ($booking->mobil) {
            $booking->mobil->increaseStock(1);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason ?? 'Dibatalkan oleh pengguna',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking kendaraan berhasil dibatalkan.',
        ]);
    }
}
