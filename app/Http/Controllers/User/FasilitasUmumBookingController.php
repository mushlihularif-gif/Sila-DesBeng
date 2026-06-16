<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FasilitasUmum;
use App\Models\FasilitasUmumBooking;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FasilitasUmumBookingController extends Controller
{
    public function create($itemId)
    {
        $item = FasilitasUmum::findOrFail($itemId);

        if (Auth::user()->region_id != $item->region_id) {
            return redirect()->back()->with('error', 'Anda Tidak Bisa Melanjutkan karena desa/wilayah ini hanya menyediakan layanan untuk warganya sendiri. Silakan sesuaikan dengan wilayah Anda.');
        }
        
        $setting = SystemSetting::first();
        
        $quantity = request()->get('quantity', 1);
        
        return view('users.fasilitas-umum-booking', compact('item', 'setting', 'quantity'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fasilitas_id' => 'required|exists:fasilitas_umums,id',
            'quantity' => 'required|integer|min:1|max:50',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'rental_purpose' => 'required|string|max:1000',
        ]);

        $item = FasilitasUmum::findOrFail($validated['fasilitas_id']);

        $booking = FasilitasUmumBooking::create([
            'user_id' => Auth::id(),
            'fasilitas_id' => $validated['fasilitas_id'],
            'quantity' => $validated['quantity'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'rental_purpose' => $validated['rental_purpose'],
            'status' => 'pending',
            'region_id' => $item->region_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan Peminjaman Fasilitas Umum berhasil dibuat! Menunggu konfirmasi admin.',
            'receipt_id' => $booking->id
        ]);
    }
}
