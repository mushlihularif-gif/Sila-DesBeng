<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RentalBooking;
use App\Models\GasOrder;
use App\Models\SystemSetting;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        // Get authenticated user
        $user = Auth::user();
        
        // Fetch user's rental bookings with product details
        $rentalBookings = RentalBooking::where('user_id', $user->id)
            ->with('barang')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Fetch user's gas orders with gas details
        $gasOrders = GasOrder::where('user_id', $user->id)
            ->with('gas')
            ->orderBy('created_at', 'desc')
            ->get();

        // Fetch user's mobil bookings
        $mobilBookings = \App\Models\MobilBooking::where('user_id', $user->id)
            ->with('mobil')
            ->orderBy('created_at', 'desc')
            ->get();

        // Fetch user's fasilitas umum bookings
        $fasilitasBookings = \App\Models\FasilitasUmumBooking::where('user_id', $user->id)
            ->with('fasilitas')
            ->orderBy('created_at', 'desc')
            ->get();

        // Fetch user's laporan (Pelaporan Warga)
        $laporans = \App\Models\Laporan::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Fetch system settings for location
        $setting = SystemSetting::first();
        
        return view('users.activity', compact(
            'rentalBookings', 
            'gasOrders', 
            'mobilBookings', 
            'fasilitasBookings', 
            'laporans', 
            'setting'
        ));
    }

    public function requestCancellation(Request $request, $type, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($type === 'rental') {
            $order = RentalBooking::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            $reasonField = 'cancellation_reason';
        } elseif ($type === 'gas') {
            $order = GasOrder::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            $reasonField = 'cancellation_reason_user';
        } elseif ($type === 'mobil') {
            $order = \App\Models\MobilBooking::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            $reasonField = 'cancellation_reason';
        } elseif ($type === 'fasilitas') {
            $order = \App\Models\FasilitasUmumBooking::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            $reasonField = 'cancellation_reason';
        } else {
            return response()->json(['success' => false, 'message' => 'Tipe tidak valid'], 400);
        }
            
        if (!$order->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dapat dibatalkan'
            ], 400);
        }

        $order->update([
            $reasonField => $request->reason,
            'cancellation_requested_at' => now(),
            'cancellation_status' => 'pending',
        ]);

        $regionId = null;
        if ($type === 'rental') {
            $regionId = $order->barang->region_id ?? null;
        } elseif ($type === 'gas') {
            $regionId = $order->gas->region_id ?? null;
        } elseif ($type === 'mobil') {
            $regionId = $order->mobil->region_id ?? null;
        } elseif ($type === 'fasilitas') {
            $regionId = $order->fasilitas->region_id ?? null;
        }

        // Create notification for admin
        \App\Models\AdminNotification::create([
            'title' => 'Permintaan Pembatalan Pesanan',
            'message' => "User " . Auth::user()->name . " mengajukan pembatalan pesanan #{$order->order_number}. Alasan: {$request->reason}",
            'type' => 'cancellation_request',
            'reference_id' => $order->id,
            'region_id' => $regionId,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan pembatalan berhasil diajukan'
        ]);
    }

    public function destroy($type, $id)
    {
        if ($type === 'rental') {
            $order = RentalBooking::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        } elseif ($type === 'gas') {
            $order = GasOrder::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        } elseif ($type === 'mobil') {
            $order = \App\Models\MobilBooking::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        } elseif ($type === 'fasilitas') {
            $order = \App\Models\FasilitasUmumBooking::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        } elseif ($type === 'laporan') {
            $order = \App\Models\Laporan::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
            $order->delete();
            return response()->json(['success' => true, 'message' => 'Riwayat berhasil dihapus']);
        } else {
            return response()->json(['success' => false, 'message' => 'Tipe tidak valid'], 400);
        }

        // Only allow deleting if status is completed, cancelled or rejected (Independent status)
        if (!in_array($order->status, ['completed', 'cancelled', 'rejected'])) {
             return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dapat dihapus kecuali selesai, dibatalkan atau ditolak'
            ], 400);
        }

        $order->delete(); // Soft delete

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pesanan berhasil dihapus'
        ]);
    }
    public function clearAll($type)
    {
        try {
            \Illuminate\Support\Facades\Log::info("Attempting to clear history for type: {$type}, User ID: " . Auth::id());

            if ($type === 'rental') {
                $query = RentalBooking::where('user_id', Auth::id());
            } elseif ($type === 'gas') {
                $query = GasOrder::where('user_id', Auth::id());
            } elseif ($type === 'mobil') {
                $query = \App\Models\MobilBooking::where('user_id', Auth::id());
            } elseif ($type === 'fasilitas') {
                $query = \App\Models\FasilitasUmumBooking::where('user_id', Auth::id());
            } elseif ($type === 'laporan') {
                $query = \App\Models\Laporan::where('user_id', Auth::id());
                // For laporan, we can delete all
                $orders = $query->get();
                $count = $orders->count();
                foreach ($orders as $order) {
                    $order->delete();
                }
                return response()->json(['success' => true, 'message' => "Berhasil menghapus {$count} riwayat aktivitas."]);
            } else {
                return response()->json(['success' => false, 'message' => 'Tipe tidak valid'], 400);
            }

            $orders = $query->whereIn('status', ['completed', 'cancelled', 'rejected'])->get();
            $count = $orders->count();

            \Illuminate\Support\Facades\Log::info("Found {$count} records to delete.");

            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada riwayat yang dapat dibersihkan'
                ], 404);
            }

            foreach ($orders as $order) {
                $order->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$count} riwayat aktivitas."
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to clear history: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus riwayat: ' . $e->getMessage()
            ], 500);
        }
    }
}
