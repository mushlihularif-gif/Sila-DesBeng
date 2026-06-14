<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RentalBooking;
use App\Models\GasOrder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ReceiptController extends Controller
{
    protected $receiptService;

    public function __construct(\App\Services\ReceiptGeneratorService $receiptService)
    {
        $this->receiptService = $receiptService;
    }

    /**
     * Lihat bukti transaksi penyewaan
     */
    public function viewRentalReceipt($id)
    {
        $booking = RentalBooking::withTrashed()->findOrFail($id);
        
        // Periksa apakah pengguna memiliki pesanan ini atau adalah admin
        if ((int)$booking->user_id !== (int)auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }
        
        // Selalu buat ulang bukti transaksi untuk memastikan data/status terbaru
        $path = $this->receiptService->generateRentalReceipt($booking);
        
        // Perbarui pesanan dengan jalur baru jika berubah
        if ($booking->receipt_path !== $path) {
            $booking->receipt_path = $path;
            $booking->save();
        }
        
        $fullPath = Storage::disk('public')->path($path);
        
        return Response::file($fullPath, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="Bukti_Transaksi_' . $booking->order_number . '.png"'
        ]);
    }
    
    /**
     * Unduh bukti transaksi penyewaan
     */
    public function downloadRentalReceipt($id)
    {
        $booking = RentalBooking::withTrashed()->findOrFail($id);
        
        // Periksa apakah pengguna memiliki pesanan ini atau adalah admin
        if ((int)$booking->user_id !== (int)auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }
        
        // Selalu buat ulang bukti transaksi untuk memastikan data/status terbaru
        $path = $this->receiptService->generateRentalReceipt($booking);
        
        // Perbarui pesanan dengan jalur baru jika berubah
        if ($booking->receipt_path !== $path) {
            $booking->receipt_path = $path;
            $booking->save();
        }
        
        return Storage::disk('public')->download(
            $path,
            'Bukti_Transaksi_Penyewaan_' . $booking->order_number . '.png'
        );
    }
    
    /**
     * Lihat bukti transaksi gas
     */
    public function viewGasReceipt($id)
    {
        $order = GasOrder::withTrashed()->findOrFail($id);
        
        // Periksa apakah pengguna memiliki pesanan ini atau adalah admin
        if ((int)$order->user_id !== (int)auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }
        
        // Selalu buat ulang bukti transaksi untuk memastikan data/status terbaru
        $path = $this->receiptService->generateGasReceipt($order);
        
        // Perbarui pesanan dengan jalur baru jika berubah
        if ($order->receipt_path !== $path) {
            $order->receipt_path = $path;
            $order->save();
        }
        
        $fullPath = Storage::disk('public')->path($path);
        
        return Response::file($fullPath, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="Bukti_Transaksi_' . $order->order_number . '.png"'
        ]);
    }
    
    /**
     * Unduh bukti transaksi gas
     */
    public function downloadGasReceipt($id)
    {
        $order = GasOrder::withTrashed()->findOrFail($id);
        
        // Periksa apakah pengguna memiliki pesanan ini atau adalah admin
        if ((int)$order->user_id !== (int)auth()->id() && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }
        
        // Selalu buat ulang bukti transaksi untuk memastikan data/status terbaru
        $path = $this->receiptService->generateGasReceipt($order);
        
        // Perbarui pesanan dengan jalur baru jika berubah
        if ($order->receipt_path !== $path) {
            $order->receipt_path = $path;
            $order->save();
        }
        
        return Storage::disk('public')->download(
            $path,
            'Bukti_Transaksi_Gas_' . $order->order_number . '.png'
        );
    }
}
