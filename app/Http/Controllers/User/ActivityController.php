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
            ->with('barang') // Assuming relationship exists
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Fetch user's gas orders with gas details
        $gasOrders = GasOrder::where('user_id', $user->id)
            ->with('gas') // Load gas relationship
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Fetch system settings for location
        $setting = SystemSetting::first();
        
        return view('users.activity', compact('rentalBookings', 'gasOrders', 'setting'));
    }

    public function requestCancellation(Request $request, $type, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($type === 'rental') {
            $order = RentalBooking::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            abort_if($order->user_id !== Auth::id(), 403, 'Unauthorized access');
            
            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak dapat dibatalkan'
                ], 400);
            }

            $order->update([
                'cancellation_reason' => $request->reason,
                'cancellation_requested_at' => now(),
                'cancellation_status' => 'pending',
            ]);
        } else {
            $order = GasOrder::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            abort_if($order->user_id !== Auth::id(), 403, 'Unauthorized access');
            
            if (!$order->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak dapat dibatalkan'
                ], 400);
            }

            $order->update([
                'cancellation_reason_user' => $request->reason,
                'cancellation_requested_at' => now(),
                'cancellation_status' => 'pending',
            ]);
        }

        // Create notification for admin
        Notification::create([
            'title' => 'Permintaan Pembatalan Pesanan',
            'message' => "User " . Auth::user()->name . " mengajukan pembatalan pesanan #{$order->order_number}. Alasan: {$request->reason}",
            'type' => 'cancellation_request',
            'user_id' => null, // For admin
            'admin_id' => \App\Models\User::where('role', 'admin')->first()->id ?? 1, // Dynamically get the first admin ID
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan pembatalan berhasil diajukan'
        ]);
    }

    public function destroy($type, $id)
    {
        if ($type === 'rental') {
            $order = RentalBooking::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            abort_if($order->user_id !== Auth::id(), 403, 'Unauthorized access');
        } else {
            $order = GasOrder::where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();
            abort_if($order->user_id !== Auth::id(), 403, 'Unauthorized access');
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

            $query = ($type === 'rental') 
                ? RentalBooking::where('user_id', Auth::id())
                : GasOrder::where('user_id', Auth::id());

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
