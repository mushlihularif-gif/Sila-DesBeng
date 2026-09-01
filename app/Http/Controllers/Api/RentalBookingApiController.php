<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\RentalBooking;

class RentalBookingApiController extends Controller
{
    /**
     * List semua alat sewa yang tersedia
     */
    public function index()
    {
        $items = Barang::where('status', 'tersedia')
            ->where('stok', '>', 0)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->nama_barang,
                    'description' => $item->deskripsi,
                    'price' => (float) $item->harga_sewa,
                    'stock' => $item->stok,
                    'category' => $item->kategori,
                    'unit' => $item->satuan,
                    'location' => $item->lokasi,
                    'image' => $item->foto ? asset('storage/' . $item->foto) : null,
                    'image_2' => $item->foto_2 ? asset('storage/' . $item->foto_2) : null,
                    'image_3' => $item->foto_3 ? asset('storage/' . $item->foto_3) : null,
                    'type' => 'rental',
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    /**
     * Detail alat sewa
     */
    public function show($id)
    {
        $item = Barang::find($id);

        if (!$item) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $item->id,
                'name' => $item->nama_barang,
                'description' => $item->deskripsi,
                'price' => (float) $item->harga_sewa,
                'stock' => $item->stok,
                'category' => $item->kategori,
                'unit' => $item->satuan,
                'location' => $item->lokasi,
                'image' => $item->foto ? asset('storage/' . $item->foto) : null,
                'image_2' => $item->foto_2 ? asset('storage/' . $item->foto_2) : null,
                'image_3' => $item->foto_3 ? asset('storage/' . $item->foto_3) : null,
                'type' => 'rental',
            ],
        ]);
    }

    /**
     * Buat pemesanan alat sewa
     */
    public function store(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'recipient_name' => 'required|string|max:255',
            'delivery_address' => 'nullable|string|max:500',
            'payment_method' => 'required|string|max:50',
            'rental_purpose' => 'nullable|string|max:500',
            'delivery_method' => 'nullable|in:antar,jemput',
        ]);

        $barang = Barang::findOrFail($request->barang_id);

        if (!$barang->hasStock($request->quantity)) {
            return response()->json([
                'status' => 'error',
                'message' => "Stok tidak mencukupi. Tersedia: {$barang->stok}",
            ], 422);
        }

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $daysCount = $startDate->diffInDays($endDate) + 1;
        $totalAmount = $barang->harga_sewa * $request->quantity * $daysCount;

        $booking = RentalBooking::create([
            'user_id' => $request->user()->id,
            'barang_id' => $barang->id,
            'order_number' => RentalBooking::generateOrderNumber(),
            'quantity' => $request->quantity,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days_count' => $daysCount,
            'recipient_name' => $request->recipient_name,
            'delivery_address' => $request->delivery_address,
            'delivery_method' => $request->delivery_method ?? 'jemput',
            'payment_method' => $request->payment_method,
            'rental_purpose' => $request->rental_purpose,
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);

        // Kurangi stok
        $barang->decreaseStock($request->quantity);

        // Notifikasi untuk User
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'type' => 'status_berubah',
            'title' => 'Pemesanan Berhasil',
            'message' => 'Pemesanan Alat Sewa (Order ID: ' . $booking->order_number . ') berhasil dibuat.',
            'link' => '/unit-penyewaan/alat',
            'icon' => 'fas fa-tools text-purple-500',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pemesanan alat sewa berhasil dibuat.',
            'data' => [
                'id' => $booking->id,
                'order_number' => $booking->order_number,
                'item_name' => $barang->nama_barang,
                'quantity' => $booking->quantity,
                'days_count' => $daysCount,
                'total_amount' => (float) $totalAmount,
                'status' => $booking->status,
                'start_date' => $booking->start_date->format('Y-m-d'),
                'end_date' => $booking->end_date->format('Y-m-d'),
            ],
        ], 201);
    }

    /**
     * Buat pemesanan paket alat sewa (bundling)
     */
    public function storePackage(Request $request)
    {
        $request->validate([
            'package_name' => 'required|string|max:255',
            'items_description' => 'required|string|max:1000',
            'total_amount' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'recipient_name' => 'required|string|max:255',
            'payment_method' => 'nullable|string|max:50',
        ]);

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $daysCount = $startDate->diffInDays($endDate) + 1;

        $booking = RentalBooking::create([
            'user_id' => $request->user()->id,
            'barang_id' => null,
            'order_number' => RentalBooking::generateOrderNumber(),
            'quantity' => 1,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days_count' => $daysCount,
            'recipient_name' => $request->recipient_name,
            'delivery_address' => null,
            'delivery_method' => 'antar',
            'payment_method' => $request->payment_method ?? 'tunai',
            'rental_purpose' => "[PAKET] {$request->package_name}: {$request->items_description}",
            'total_amount' => $request->total_amount,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pemesanan paket alat berhasil dibuat.',
            'data' => [
                'id' => $booking->id,
                'order_number' => $booking->order_number,
                'package_name' => $request->package_name,
                'days_count' => $daysCount,
                'total_amount' => (float) $request->total_amount,
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
        $bookings = RentalBooking::withoutGlobalScopes()
            ->where('user_id', $request->user()->id)
            ->with('barang')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'order_number' => $booking->order_number,
                    'item_name' => $booking->barang->nama_barang ?? '-',
                    'quantity' => $booking->quantity,
                    'days_count' => $booking->days_count,
                    'total_amount' => (float) $booking->total_amount,
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
        $booking = RentalBooking::withoutGlobalScopes()
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking tidak ditemukan.',
            ], 404);
        }

        if (!$booking->canBeCancelled()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Booking ini tidak dapat dibatalkan.',
            ], 422);
        }

        // Kembalikan stok
        if ($booking->barang) {
            $booking->barang->increaseStock($booking->quantity);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason ?? 'Dibatalkan oleh pengguna',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Booking berhasil dibatalkan.',
        ]);
    }
}
