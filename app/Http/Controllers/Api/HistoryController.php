<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GasOrder;
use App\Models\RentalBooking;
use App\Models\MobilBooking;
use App\Models\FasilitasUmumBooking;
use Carbon\Carbon;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $history = collect();

        // 1. Gas Orders
        $gasOrders = GasOrder::where('user_id', $user->id)->with('gas')->get();
        foreach ($gasOrders as $order) {
            $totalPrice = (float)($order->price ?? 0) * (int)($order->quantity ?? 0);
            $history->push([
                'id' => $order->id,
                'category' => 'Pesanan Gas',
                'title' => $order->gas ? $order->gas->jenis_gas : ($order->item_name ?? 'Pesanan Gas'),
                'price' => 'Rp ' . number_format($totalPrice, 0, ',', '.'),
                'date' => Carbon::parse($order->created_at)->isoFormat('dddd, D MMMM Y HH:mm') . ' WIB',
                'status' => $this->mapStatus($order->status),
                'payment' => $order->payment_method ?? 'Tunai',
                'image' => url('User/img/elemen/F2.png'), // Default image for gas
                'raw_date' => $order->created_at,
                'raw_data' => $order
            ]);
        }

        // 2. Rental Bookings (Alat)
        $rentals = RentalBooking::where('user_id', $user->id)->with('barang')->get();
        foreach ($rentals as $rental) {
            $title = $rental->barang ? $rental->barang->nama_barang : 'Sewa Alat';
            if ($rental->days_count) {
                $title .= ' - ' . $rental->days_count . ' Hari';
            }
            $history->push([
                'id' => $rental->id,
                'category' => 'Penyewaan', // MATCHES FLUTTER 'Penyewaan'
                'title' => $title,
                'price' => 'Rp ' . number_format((float)($rental->total_amount ?? $rental->total_price ?? 0), 0, ',', '.'),
                'date' => Carbon::parse($rental->created_at)->isoFormat('dddd, D MMMM Y HH:mm') . ' WIB',
                'status' => $this->mapStatus($rental->status),
                'payment' => $rental->payment_method ?? 'Tunai',
                'image' => url('User/img/elemen/F1.png'),
                'raw_date' => $rental->created_at,
                'raw_data' => $rental
            ]);
        }

        // 3. Mobil Bookings
        $mobils = MobilBooking::where('user_id', $user->id)->with('mobil')->get();
        foreach ($mobils as $mobil) {
            $history->push([
                'id' => $mobil->id,
                'category' => 'Sewa Kendaraan', // MATCHES FLUTTER
                'title' => $mobil->mobil ? $mobil->mobil->nama_mobil : 'Sewa Transportasi',
                'price' => 'Rp ' . number_format((float)($mobil->total_amount ?? $mobil->total_price ?? 0), 0, ',', '.'),
                'date' => Carbon::parse($mobil->created_at)->isoFormat('dddd, D MMMM Y HH:mm') . ' WIB',
                'status' => $this->mapStatus($mobil->status),
                'payment' => $mobil->payment_method ?? 'Tunai',
                'image' => url('User/img/elemen/mobil.png'),
                'raw_date' => $mobil->created_at,
                'raw_data' => $mobil
            ]);
        }

        // 4. Fasilitas Umum Bookings
        $fasilitas = FasilitasUmumBooking::where('user_id', $user->id)->with('fasilitas')->get();
        foreach ($fasilitas as $fas) {
            $history->push([
                'id' => $fas->id,
                'category' => 'Fasilitas', // MATCHES FLUTTER 'Fasilitas'
                'title' => $fas->fasilitas ? $fas->fasilitas->nama_fasilitas : 'Sewa Fasilitas',
                'price' => 'Rp ' . number_format((float)($fas->total_amount ?? $fas->total_price ?? 0), 0, ',', '.'),
                'date' => Carbon::parse($fas->created_at)->isoFormat('dddd, D MMMM Y HH:mm') . ' WIB',
                'status' => $this->mapStatus($fas->status),
                'payment' => $fas->payment_method ?? 'Tunai',
                'image' => url('User/img/elemen/fasilitas.png'),
                'raw_date' => $fas->created_at,
                // Preserve the existing mobile response key while loading the
                // relationship that actually exists on FasilitasUmumBooking.
                'raw_data' => [...$fas->attributesToArray(), 'fasilitas_umum' => $fas->fasilitas?->toArray()]
            ]);
        }

        

        // Sort by raw_date descending
        $sortedHistory = $history->sortByDesc('raw_date')->values()->all();

        return response()->json([
            'status' => 'success',
            'data' => $sortedHistory
        ]);
    }

    private function mapStatus($status)
    {
        $statusMap = [
            'pending' => 'Menunggu',
            'menunggu' => 'Menunggu',
            'menunggu pembayaran' => 'Menunggu',
            'diproses' => 'Dikonfirmasi',
            'dikonfirmasi' => 'Dikonfirmasi',
            'dikirim' => 'Dalam Pengiriman',
            'confirmed' => 'Dikonfirmasi',
            'approved' => 'Disetujui',
            'processing' => 'Diproses',
            'process' => 'Diproses',
            'being_prepared' => 'Sedang Dipersiapkan',
            'in_delivery' => 'Dalam Pengiriman',
            'delivering' => 'Dalam Pengiriman',
            'arrived' => 'Tiba di Lokasi',
            'ongoing' => 'Sedang Berjalan',
            'ready' => 'Siap Diambil',
            'selesai' => 'Selesai',
            'completed' => 'Selesai',
            'returned' => 'Selesai',
            'resolved' => 'Selesai',
            'ditolak' => 'Ditolak',
            'batal' => 'Batal',
            'cancelled' => 'Batal',
            'rejected' => 'Ditolak',
        ];

        $raw = (string) ($status ?? '');
        $lowerStatus = strtolower($raw);
        return $statusMap[$lowerStatus] ?? ($raw !== '' ? ucfirst(str_replace('_', ' ', $raw)) : 'Menunggu');
    }
}
