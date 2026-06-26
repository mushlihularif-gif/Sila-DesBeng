<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Mobil;
use App\Models\MobilBooking;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MobilBookingController extends Controller
{
    public function create($itemId)
    {
        $item = Mobil::findOrFail($itemId);

        if (Auth::user()->region_id != $item->region_id) {
            return redirect()->back()->with('error', 'Anda Tidak Bisa Melanjutkan karena desa/wilayah ini hanya menyediakan layanan untuk warganya sendiri. Silakan sesuaikan dengan wilayah Anda.');
        }
        
        $setting = SystemSetting::first();
        
        $quantity = request()->get('quantity', 1);
        
        return view('users.mobil-rental-booking', compact('item', 'setting', 'quantity'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mobil_id' => 'required|exists:mobils,id',
            'delivery_method' => 'required|in:antar,jemput',
            'quantity' => 'required|integer|min:1|max:50',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'distance_km' => 'required|integer|min:1',
            'payment_method' => 'required|in:tunai',
            
            'recipient_name' => 'required|string|max:255',
            'delivery_address' => 'required|string',
            
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            
            'rental_purpose' => 'required|string|max:1000',
        ]);

        $item = Mobil::findOrFail($validated['mobil_id']);
        
        // Validate availability before proceeding
        if ($item->status !== 'tersedia') {
            return response()->json([
                'success' => false,
                'message' => "Mohon maaf, mobil sedang tidak tersedia saat ini."
            ], 400);
        }

        $totalAmount = $item->harga_sewa * $validated['quantity'] * $validated['distance_km'];

        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        $booking = MobilBooking::create([
            'user_id' => Auth::id(),
            'mobil_id' => $validated['mobil_id'],
            'delivery_method' => $validated['delivery_method'],
            'rental_purpose' => $validated['rental_purpose'],
            'quantity' => $validated['quantity'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'distance_km' => $validated['distance_km'],
            'recipient_name' => $validated['recipient_name'] ?? null,
            'delivery_address' => $validated['delivery_address'] ?? null,
            'payment_method' => $validated['payment_method'],
            'payment_proof' => $paymentProofPath,
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);

        $receipt = \App\Models\TransactionReceipt::create([
            'booking_type' => 'mobil',
            'booking_id' => $booking->id,
            'receipt_number' => \App\Models\TransactionReceipt::generateReceiptNumber('mobil'),
            'user_id' => Auth::id(),
            'amount' => $totalAmount,
            'issued_at' => now(),
        ]);

        $booking->update(['receipt_path' => $receipt->receipt_number]);

        return redirect()->route('user.dashboard')
            ->with('success', 'Pemesanan Mobil berhasil dibuat! Menunggu konfirmasi admin.')
            ->with('show_receipt', $receipt->id);
    }
}
