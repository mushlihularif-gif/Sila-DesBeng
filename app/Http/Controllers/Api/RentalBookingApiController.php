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
    public function index(Request $request)
    {
        $query = app(\App\Services\RentalOrdering::class)->visibleQuery($request->user())->where('status', 'tersedia')->where('stok', '>', 0);

        $items = $query->get()
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
                    'type' => 'rental', 'billing_basis' => 'per_activity',
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
        $item = app(\App\Services\RentalOrdering::class)->visibleQuery(auth()->user())->find($id);

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
                'type' => 'rental', 'billing_basis' => 'per_activity',
            ],
        ]);
    }

    /**
     * Buat pemesanan alat sewa
     */
    public function store(Request $request)
    {
        $booking = app(\App\Services\RentalOrdering::class)->store($request)[0];
        return response()->json(['status'=>'success', 'message'=>'Pemesanan alat tersimpan.', 'data'=>$this->bookingData($booking)], 201);
    }

    public function storePackage(Request $request)
    {
        $orders = app(\App\Services\RentalOrdering::class)->store($request, true);
        $bookings = array_map(fn ($booking) => $this->bookingData($booking), $orders);
        return response()->json(['status'=>'success', 'message'=>'Paket alat tersimpan.',
            'data'=>['bookings'=>$bookings, 'total_amount'=>array_sum(array_column($bookings, 'total_amount'))]], 201);
    }

    private function bookingData(RentalBooking $booking): array
    {
        return ['id'=>$booking->id, 'order_number'=>$booking->order_number, 'item_name'=>$booking->barang?->nama_barang,
            'quantity'=>$booking->quantity, 'days_count'=>$booking->days_count, 'total_amount'=>(float)$booking->total_amount,
            'status'=>$booking->status, 'start_date'=>$booking->start_date->format('Y-m-d'), 'end_date'=>$booking->end_date->format('Y-m-d'),
            'payment_url'=>route('rental.payment', $booking->id)];
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
        return app(\App\Services\RentalOrderActions::class)->cancelUnpaid($request, (int) $id);
    }
}
