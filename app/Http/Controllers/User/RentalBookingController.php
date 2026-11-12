<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\RentalBooking;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\AdminNotification;

class RentalBookingController extends Controller
{
    /**
     * Tampilkan formulir pemesanan
     */
    public function create($itemId)
    {
        // Ambil item penyewaan
        $item = app(\App\Services\RentalOrdering::class)->visibleQuery(Auth::user())->findOrFail($itemId);

        // Validasi KYC: Pengguna harus sudah terverifikasi
        if (Auth::user()->verification_status !== 'verified') {
            return redirect()->back()->with('show_kyc_modal', true);
        }

        // Rekening yang ditampilkan harus milik wilayah barangnya, bukan rekening
        // pusat. Sejak pemasukan dipegang tiap daerah, SystemSetting::first()
        // akan menampilkan rekening yang salah ke warga.
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($item->region_id);
        
        // Ambil SOP Penyewaan Alat
        $region = $item->region;
        $paymentInfo = $region ? ($region->payment_info ?? []) : [];
        
        $activeSop = $paymentInfo['sop_penyewaan_active'] ?? 'ditanggung';
        
        $defaultSopDitanggung = "1. Penyewa wajib menjaga barang sewaan dengan baik.\n2. Jika terjadi KERUSAKAN atau KEHILANGAN barang selama masa penyewaan, maka SEPENUHNYA menjadi tanggung jawab PENGGUNA (penyewa) untuk mengganti rugi atau memperbaiki alat tersebut sesuai dengan nilai barang.\n3. Keterlambatan pengembalian dapat dikenakan denda sesuai ketentuan yang berlaku.";
        $defaultSopTidakDitanggung = "1. Penyewa wajib menjaga barang sewaan dengan baik.\n2. Jika terjadi kerusakan atau kehilangan barang selama masa penyewaan yang diakibatkan oleh faktor ketidaksengajaan/bencana, maka TIDAK DITANGGUNG oleh pengguna (penyewa) karena telah didukung oleh dana operasional/APBD.\n3. Namun pengguna tetap diwajibkan melaporkan kejadian tersebut secara transparan.";
        
        $sop_penyewaan_alat = $paymentInfo['sop_penyewaan_' . $activeSop] ?? ($activeSop == 'ditanggung' ? $defaultSopDitanggung : $defaultSopTidakDitanggung);
        
        // Ambil jumlah dari permintaan (dari halaman detail)
        request()->validate(['quantity'=>'sometimes|integer|min:1|max:50']);
        $quantity = request()->integer('quantity', 1);
        

        // Buku alamat warga, supaya alamat pengiriman tidak perlu diketik ulang
        // di setiap unit layanan.
        $alamatTersimpan = \App\Models\AlamatWarga::milik(auth()->id())
            ->with('region')
            ->orderByDesc('is_utama')
            ->orderBy('id')
            ->get();

        $adaGateway = false;

        return view('users.rental-booking', compact('item', 'setting', 'quantity', 'sop_penyewaan_alat', 'alamatTersimpan', 'adaGateway'));
    }

    /**
     * Simpan pemesanan
     */
    public function store(Request $request)
    {
        $booking = app(\App\Services\RentalOrdering::class)->store($request)[0];
        return response()->json(['success'=>true, 'booking_id'=>$booking->id, 'receipt_id'=>$booking->id,
            'receipt_number'=>$booking->order_number, 'payment_url'=>route('rental.payment', $booking->id)]);
    }

    public function payment($id)
    {
        $order = RentalBooking::where('user_id', auth()->id())->findOrFail($id);
        $setting = \App\Support\ProfilPembayaranWilayah::untuk($order->region_id ?? $order->barang?->region_id);
        $paid = $order->paymentReceived();
        return view('users.rental-payment', compact('order', 'setting', 'paid'));
    }
    public function uploadPayment(Request $request, int $id)
    {
        abort_unless($request->user(), 401);
        // Check ownership before validating or storing an uploaded file.
        RentalBooking::where('user_id', $request->user()->id)->findOrFail($id);
        $request->validate(['payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120']);
        $storedPath = null;

        try {
            DB::transaction(function () use ($request, $id, &$storedPath) {
                $order = RentalBooking::where('user_id', $request->user()->id)->lockForUpdate()->findOrFail($id);
                abort_unless($order->status === 'pending' && $order->cancellation_status !== 'pending', 409,
                    'Bukti hanya dapat dikirim untuk pesanan yang masih menunggu pembayaran.');
                abort_unless(in_array(strtolower(trim($order->payment_method)), ['transfer', 'transfer_manual', 'transfer manual', 'bank_transfer', 'ewallet'], true), 422,
                    'Pesanan ini tidak menggunakan pembayaran transfer atau e-wallet manual.');
                abort_if($order->payment_proof, 409, 'Bukti sudah dikirim dan sedang menunggu pemeriksaan petugas.');
                $ledger = \App\Models\WalletTransaction::where('reference_type', 'rental')
                    ->where('reference_id', $order->id)->lockForUpdate()->get();
                abort_if($ledger->isEmpty() || $ledger->contains(fn ($entry) => $entry->status !== 'pending'), 409,
                    'Pembayaran sudah diproses petugas. Bukti tidak dapat diganti.');

                $storedPath = $request->file('payment_proof')->store('payment_proofs/rental', 'public');
                abort_unless($storedPath, 503, 'Bukti pembayaran belum berhasil disimpan.');
                $order->update(['payment_proof' => $storedPath]);
                // Uploading evidence never verifies payment or changes the amount/status.
                \App\Models\WalletTransaction::whereKey($ledger->modelKeys())->update(['proof_path' => $storedPath]);
                AdminNotification::create([
                    'type' => 'rental_request', 'reference_id' => $order->id, 'region_id' => $order->barang?->region_id,
                    'title' => 'Bukti Pembayaran Sewa Alat Dikirim',
                    'message' => 'Bukti pembayaran pesanan '.$order->order_number.' perlu diperiksa.', 'is_read' => false,
                ]);
            });
        } catch (\Throwable $e) {
            if ($storedPath) Storage::disk('public')->delete($storedPath);
            throw $e;
        }

        if (!$request->expectsJson() && !$request->is('api/*')) return back()->with('success', 'Bukti pembayaran dikirim. Petugas akan memeriksanya.');
        return response()->json(['success' => true, 'message' => 'Bukti pembayaran dikirim. Petugas akan memeriksanya.', 'order_id' => $id])
            ->header('Cache-Control', 'private, no-store');
    }

}
