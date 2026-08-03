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
    public function index()
    {
        $items = FasilitasUmum::where('status', 'tersedia')
            ->get()
            ->map(function ($item) {
                $isAmbulance = str_contains(strtolower($item->nama_fasilitas), 'ambulan');
                $isVehicle = str_contains(strtolower($item->nama_fasilitas), 'mobil')
                    || str_contains(strtolower($item->nama_fasilitas), 'bus')
                    || str_contains(strtolower($item->nama_fasilitas), 'kendaraan')
                    || $isAmbulance;

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
                    'is_ambulance' => $isAmbulance,
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
        $item = FasilitasUmum::find($id);

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fasilitas tidak ditemukan.',
            ], 404);
        }

        $isAmbulance = str_contains(strtolower($item->nama_fasilitas), 'ambulan');

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
                'is_ambulance' => $isAmbulance,
                'opsi_supir' => $item->opsi_supir,
                'nama_supir' => $item->nama_supir,
                'kontak_supir' => $item->kontak_supir,
                'bbm_ditanggung' => $item->bbm_ditanggung,
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
            'jenis_acara' => 'nullable|in:sosial,pribadi',
            'butuh_gudang' => 'nullable|boolean',
            'quantity' => 'nullable|integer|min:1',
            'delivery_method' => 'nullable|string',
            'dengan_supir' => 'nullable|boolean',
        ]);

        $fasilitas = FasilitasUmum::findOrFail($request->fasilitas_id);

        if ($fasilitas->stok !== null && $fasilitas->stok <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fasilitas sedang tidak tersedia.',
            ], 422);
        }

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $daysCount = $startDate->diffInDays($endDate) + 1;

        // Fasilitas untuk acara sosial biasanya gratis
        $totalAmount = 0;

        $booking = FasilitasUmumBooking::create([
            'user_id' => $request->user()->id,
            'fasilitas_id' => $fasilitas->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'quantity' => $request->quantity ?? 1,
            'rental_purpose' => $request->rental_purpose,
            'jenis_acara' => $request->jenis_acara ?? 'sosial',
            'butuh_gudang' => $request->butuh_gudang ?? false,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'delivery_method' => $request->delivery_method,
            'dengan_supir' => $request->dengan_supir ?? false,
            'region_id' => $request->user()->region_id,
        ]);

        // Kurangi stok jika ada
        if ($fasilitas->stok !== null) {
            $fasilitas->decreaseStock($request->quantity ?? 1);
        }

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
        $booking = FasilitasUmumBooking::withoutGlobalScopes()
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

        // Kembalikan stok jika ada
        if ($booking->fasilitas && $booking->fasilitas->stok !== null) {
            $booking->fasilitas->increaseStock($booking->quantity ?? 1);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason ?? 'Dibatalkan oleh pengguna',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking fasilitas berhasil dibatalkan.',
        ]);
    }
}
