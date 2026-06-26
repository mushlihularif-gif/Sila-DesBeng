<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\RentalBooking;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RentalBookingController extends Controller
{
    /**
     * Tampilkan formulir pemesanan
     */
    public function create($itemId)
    {
        // Ambil item penyewaan
        $item = Barang::findOrFail($itemId);

        // Validasi: Warga hanya bisa memesan layanan di wilayahnya sendiri
        if (Auth::user()->region_id != $item->region_id) {
            return redirect()->back()->with('error', 'Anda Tidak Bisa Melanjutkan karena desa/wilayah ini hanya menyediakan layanan untuk warganya sendiri. Silakan sesuaikan dengan wilayah Anda.');
        }
        
        // Ambil pengaturan sistem untuk rekening bank dan lokasi
        $setting = SystemSetting::first();
        
        // Ambil SOP Penyewaan Alat
        $region = \App\Models\Region::find(Auth::user()->region_id);
        $paymentInfo = $region ? ($region->payment_info ?? []) : [];
        
        $activeSop = $paymentInfo['sop_penyewaan_active'] ?? 'ditanggung';
        
        $defaultSopDitanggung = "1. Penyewa wajib menjaga barang sewaan dengan baik.\n2. Jika terjadi KERUSAKAN atau KEHILANGAN barang selama masa penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki alat tersebut sesuai dengan nilai barang.\n3. Keterlambatan pengembalian dapat dikenakan denda sesuai ketentuan yang berlaku.";
        $defaultSopTidakDitanggung = "1. Penyewa wajib menjaga barang sewaan dengan baik.\n2. Jika terjadi kerusakan atau kehilangan barang selama masa penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna (penyewa) karena telah didukung oleh dana operasional/APBD.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan.";
        
        $sop_penyewaan_alat = $paymentInfo['sop_penyewaan_' . $activeSop] ?? ($activeSop == 'ditanggung' ? $defaultSopDitanggung : $defaultSopTidakDitanggung);
        
        // Ambil jumlah dari permintaan (dari halaman detail)
        $quantity = request()->get('quantity', 1);
        
        return view('users.rental-booking', compact('item', 'setting', 'quantity', 'sop_penyewaan_alat'));
    }

    /**
     * Simpan pemesanan
     */
    public function store(Request $request)
    {
        // Validasi permintaan
        $validated = $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'delivery_method' => 'required|in:antar,jemput',
            'quantity' => 'required|integer|min:1|max:50',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'payment_method' => 'required|in:tunai',
            
            // Penerima & Alamat (Wajib untuk Antar & Jemput)
            'recipient_name' => 'required|string|max:255',
            'delivery_address' => 'required|string',
            
            // Untuk metode pembayaran 'transfer'
            'payment_proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            
            // Bidang Tujuan Baru
            'rental_purpose' => 'required|string|max:1000',
        ]);

        // Hitung jumlah hari
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $endDate = \Carbon\Carbon::parse($validated['end_date']);
        $daysCount = $startDate->diffInDays($endDate) + 1;

        // Server-side price recalculation to prevent parameter tampering
        $item = Barang::findOrFail($validated['barang_id']);

        // Validate stock before proceeding
        if (!$item->hasStock($validated['quantity'])) {
            return response()->json([
                'success' => false,
                'message' => "Mohon maaf, stok tidak mencukupi. Sisa stok: {$item->stok}"
            ], 400);
        }

        $totalAmount = $item->harga_sewa * $validated['quantity'] * $daysCount;

        // Tangani unggahan bukti pembayaran
        $paymentProofPath = null;
        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        // Buat pemesanan
        $booking = RentalBooking::create([
            'user_id' => Auth::id(),
            'barang_id' => $validated['barang_id'],
            'delivery_method' => $validated['delivery_method'],
            'rental_purpose' => $validated['rental_purpose'],
            'quantity' => $validated['quantity'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'days_count' => $daysCount,
            'recipient_name' => $validated['recipient_name'] ?? null,
            'delivery_address' => $validated['delivery_address'] ?? null,
            'payment_method' => $validated['payment_method'],
            'payment_proof' => $paymentProofPath,
            'total_amount' => $totalAmount,
            'status' => 'pending',
        ]);

        // Buat bukti transaksi
        $receipt = \App\Models\TransactionReceipt::create([
            'booking_type' => 'rental',
            'booking_id' => $booking->id,
            'receipt_number' => \App\Models\TransactionReceipt::generateReceiptNumber('rental'),
            'user_id' => Auth::id(),
            'item_name' => $item->nama_barang,
            'quantity' => $validated['quantity'],
            'total_amount' => $totalAmount,
            'payment_method' => $validated['payment_method'],
        ]);

        // Buat notifikasi admin
        \App\Models\AdminNotification::create([
            'type' => 'rental_request',
            'reference_id' => $booking->id,
            'region_id' => $item->region_id,
            'title' => 'Permintaan Penyewaan Baru',
            'message' => 'Permintaan penyewaan ' . $item->nama_barang . ' dari ' . Auth::user()->name,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil dibuat!',
            'booking_id' => $booking->id,
            'receipt_id' => $booking->id, // Gunakan ID pesanan untuk rute bukti transaksi
            'receipt_number' => $receipt->receipt_number,
        ]);
    }
}

