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
        
        // Ambil SOP Fasilitas Umum
        $region = \App\Models\Region::find(Auth::user()->region_id);
        $paymentInfo = $region ? ($region->payment_info ?? []) : [];
        
        $activeSop = $paymentInfo['sop_fasilitas_active'] ?? 'ditanggung';
        
        $defaultSopDitanggung = "1. Penyewa wajib menjaga fasilitas umum dengan baik.\n2. Jika terjadi KERUSAKAN fasilitas selama masa peminjaman/penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki fasilitas tersebut sesuai dengan kerusakan.\n3. Fasilitas harus dikembalikan dalam keadaan bersih dan rapi.";
        $defaultSopTidakDitanggung = "1. Penyewa wajib menjaga fasilitas umum dengan baik.\n2. Jika terjadi kerusakan fasilitas selama masa peminjaman/penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna karena telah didukung oleh dana operasional/APBD.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan dan menjaga kebersihan.";
        
        $sop_fasilitas = $paymentInfo['sop_fasilitas_' . $activeSop] ?? ($activeSop == 'ditanggung' ? $defaultSopDitanggung : $defaultSopTidakDitanggung);
        
        $quantity = request()->get('quantity', 1);
        
        return view('users.fasilitas-umum-booking', compact('item', 'setting', 'quantity', 'sop_fasilitas', 'region'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fasilitas_id' => 'required|exists:fasilitas_umums,id',
            'delivery_method' => 'required|in:antar,jemput',
            'quantity' => 'required|integer|min:1|max:50',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'rental_purpose' => 'required|string|max:1000',
            'recipient_name' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string',
        ]);

        if ($validated['delivery_method'] == 'antar') {
            $request->validate([
                'recipient_name' => 'required|string|max:255',
                'delivery_address' => 'required|string',
            ]);
        }

        $item = FasilitasUmum::findOrFail($validated['fasilitas_id']);
        
        // Validate stock before proceeding
        if ($item->stok < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => "Mohon maaf, fasilitas sedang tidak tersedia. Sisa stok: {$item->stok}"
            ], 400);
        }

        $booking = FasilitasUmumBooking::create([
            'user_id' => Auth::id(),
            'fasilitas_id' => $validated['fasilitas_id'],
            'delivery_method' => $validated['delivery_method'],
            'recipient_name' => $validated['recipient_name'] ?? null,
            'delivery_address' => $validated['delivery_address'] ?? null,
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
