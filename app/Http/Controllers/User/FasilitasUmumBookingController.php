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
        // Validasi KYC: Pengguna harus sudah terverifikasi
        if (Auth::user()->verification_status !== 'verified') {
            return redirect()->back()->with('show_kyc_modal', true);
        }

        $item = FasilitasUmum::findOrFail($itemId);

        // Validasi: Warga hanya bisa memesan layanan di wilayahnya sendiri
        if (Auth::user()->region_id != $item->region_id) {
            return redirect()->back()->with('error', 'Layanan khusus warga lokal. Silakan sesuaikan wilayah Anda.');
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
            'jenis_acara' => 'required|in:sosial,komersial',
            'surat_pengantar' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
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

        $totalAmount = 0;
        if ($item->status_biaya === 'berbayar' && $validated['jenis_acara'] === 'komersial' && $item->harga_sewa > 0) {
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = \Carbon\Carbon::parse($validated['end_date']);
            $days = $startDate->diffInDays($endDate) + 1;
            $totalAmount = $days * $item->harga_sewa * $validated['quantity'];
        }

        $suratPath = null;
        if ($request->hasFile('surat_pengantar')) {
            $suratPath = $request->file('surat_pengantar')->store('surat_pengantar', 'public');
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
            'jenis_acara' => $validated['jenis_acara'],
            'surat_pengantar' => $suratPath ?? null,
            'butuh_gudang' => $request->has('butuh_gudang'),
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'region_id' => $item->region_id,
        ]);

        // Buat notifikasi admin
        \App\Models\AdminNotification::create([
            'type' => 'fasilitas_order',
            'reference_id' => $booking->id,
            'region_id' => $item->region_id,
            'title' => 'Peminjaman Fasilitas Umum Baru',
            'message' => 'Permohonan peminjaman ' . ($item->nama_fasilitas ?? 'Fasilitas Umum') . ' dari ' . (Auth::user()->name ?? 'Warga'),
            'is_read' => false,
        ]);

        // We can just rely on the normal flow for now, the user can check their activity dashboard
        // If we want a separate payment page, we can build it later.
        
        return response()->json([
            'success' => true,
            'message' => 'Pengajuan Peminjaman Fasilitas Umum berhasil dibuat! ' . ($totalAmount > 0 ? 'Silakan periksa detail tagihan Anda.' : 'Menunggu konfirmasi admin.'),
            'receipt_id' => $booking->id
        ]);
    }
}
