<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\GasOrder;
use App\Models\RentalBooking;
use App\Models\MobilBooking;
use App\Models\FasilitasUmumBooking;
use App\Models\PasarOrder;
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
            $totalPrice = $order->price * $order->quantity;
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
            if ($rental->rental_duration) {
                $title .= ' - ' . $rental->rental_duration . ' Hari';
            }
            $history->push([
                'id' => $rental->id,
                'category' => 'Penyewaan Alat',
                'title' => $title,
                'price' => 'Rp ' . number_format($rental->total_amount ?? $rental->total_price ?? 0, 0, ',', '.'),
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
                'category' => 'Sewa Kendaraan',
                'title' => $mobil->mobil ? $mobil->mobil->nama_mobil : 'Sewa Mobil',
                'price' => 'Rp ' . number_format($mobil->total_amount ?? $mobil->total_price ?? 0, 0, ',', '.'),
                'date' => Carbon::parse($mobil->created_at)->isoFormat('dddd, D MMMM Y HH:mm') . ' WIB',
                'status' => $this->mapStatus($mobil->status),
                'payment' => $mobil->payment_method ?? 'Tunai',
                'image' => url('User/img/elemen/mobil.png'),
                'raw_date' => $mobil->created_at,
                'raw_data' => $mobil
            ]);
        }

        // 4. Fasilitas Umum Bookings
        $fasilitas = FasilitasUmumBooking::where('user_id', $user->id)->with('fasilitasUmum')->get();
        foreach ($fasilitas as $fas) {
            $history->push([
                'id' => $fas->id,
                'category' => 'Sewa Fasilitas',
                'title' => $fas->fasilitasUmum ? $fas->fasilitasUmum->nama_fasilitas : 'Sewa Fasilitas',
                'price' => 'Rp ' . number_format($fas->total_amount ?? $fas->total_price ?? 0, 0, ',', '.'),
                'date' => Carbon::parse($fas->created_at)->isoFormat('dddd, D MMMM Y HH:mm') . ' WIB',
                'status' => $this->mapStatus($fas->status),
                'payment' => $fas->payment_method ?? 'Tunai',
                'image' => url('User/img/elemen/fasilitas.png'),
                'raw_date' => $fas->created_at,
                'raw_data' => $fas
            ]);
        }

        // 5. Laporan Warga
        $laporans = Laporan::where('user_id', $user->id)->get();
        foreach ($laporans as $laporan) {
            $history->push([
                'id' => $laporan->id,
                'category' => 'Laporan Warga',
                'title' => $laporan->judul_laporan ?? 'Laporan',
                'price' => 'Prioritas: ' . ($laporan->tingkat_prioritas ?? 'Normal'), // Use price field for priority display
                'date' => Carbon::parse($laporan->created_at)->isoFormat('dddd, D MMMM Y HH:mm') . ' WIB',
                'status' => $this->mapStatus($laporan->status),
                'payment' => '-', // No payment for laporan
                'image' => url('User/img/elemen/lapor.png'),
                'raw_date' => $laporan->created_at,
                'raw_data' => $laporan
            ]);
        }

        // 6. Pasar Daerah Orders (BUMDes)
        $pasarOrders = PasarOrder::where('user_id', $user->id)
            ->with(['items.produk', 'region', 'complaint'])
            ->get();
        foreach ($pasarOrders as $order) {
            $firstItem = $order->items->first();
            $firstProduct = $firstItem ? $firstItem->produk : null;
            $title = $firstProduct ? $firstProduct->nama_produk : 'Pesanan Pasar Daerah';
            if ($order->items->count() > 1) {
                $title .= ' (+' . ($order->items->count() - 1) . ' produk)';
            }
            
            $imageUrl = url('User/img/elemen/pasar.png');
            if ($firstProduct && $firstProduct->foto) {
                $imageUrl = url('storage/' . $firstProduct->foto);
            }

            $history->push([
                'id' => $order->id,
                'category' => 'Pasar Daerah',
                'title' => $title,
                'price' => 'Rp ' . number_format($order->grand_total, 0, ',', '.'),
                'date' => Carbon::parse($order->created_at)->isoFormat('dddd, D MMMM Y HH:mm') . ' WIB',
                'status' => $this->mapStatus($order->status),
                'payment' => $order->payment_method ? strtoupper(str_replace('_', ' ', $order->payment_method)) : 'Tunai',
                'image' => $imageUrl,
                'raw_date' => $order->created_at,
                'raw_data' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'mapped_status' => $this->mapStatus($order->status),
                    'delivery_method' => $order->delivery_method,
                    'delivery_address' => $order->delivery_address,
                    'payment_method' => $order->payment_method,
                    'total_amount' => $order->total_amount,
                    'shipping_cost' => $order->shipping_cost,
                    'grand_total' => $order->grand_total,
                    'delivery_proof_image' => $order->delivery_proof_image ? url('storage/' . $order->delivery_proof_image) : null,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'pasar_produk_id' => $item->pasar_produk_id,
                            'product_id' => $item->pasar_produk_id,
                            'name' => $item->produk ? $item->produk->nama_produk : 'Produk',
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'subtotal' => $item->subtotal ?? ($item->quantity * $item->price),
                            'image' => $item->produk && $item->produk->foto ? url('storage/' . $item->produk->foto) : null,
                            'satuan' => $item->produk ? $item->produk->satuan : 'pcs',
                        ];
                    }),
                    'region' => $order->region ? [
                        'id' => $order->region->id,
                        'name' => $order->region->name,
                    ] : null,
                    'complaint' => $order->complaint ? [
                        'id' => $order->complaint->id,
                        'reason' => $order->complaint->reason,
                        'solution_requested' => $order->complaint->solution_requested,
                        'description' => $order->complaint->description,
                        'status' => $order->complaint->status,
                        'admin_response' => $order->complaint->admin_response,
                    ] : null,
                    'created_at' => $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : null,
                    'confirmed_at' => $order->confirmed_at ? $order->confirmed_at->format('Y-m-d H:i:s') : null,
                    'completion_time' => $order->completion_time ? $order->completion_time->format('Y-m-d H:i:s') : null,
                ]
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
            'dikirim' => 'Dikonfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'processing' => 'Dikonfirmasi',
            'ready' => 'Dikonfirmasi',
            'selesai' => 'Selesai',
            'completed' => 'Selesai',
            'ditolak' => 'Batal',
            'batal' => 'Batal',
            'cancelled' => 'Batal',
            'rejected' => 'Batal',
        ];

        $lowerStatus = strtolower($status);
        return $statusMap[$lowerStatus] ?? ucfirst($status);
    }
}
