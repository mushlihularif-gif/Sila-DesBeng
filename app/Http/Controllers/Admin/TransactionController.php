<?php

namespace App\Http\Controllers\Admin;

use App\Models\RentalBooking;
use App\Models\GasOrder;
use App\Models\MobilBooking;
use App\Models\FasilitasUmumBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ActivityLog;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        // Get filter parameters
        $category = $request->get('category', 'all');
        $paymentMethod = $request->get('payment_method', 'all');
        $search = $request->get('search');

        // Build queries
        // Build queries
        // Include transactions with proof OR system generated (active/completed statuses)
        $activeStatuses = ['confirmed', 'approved', 'being_prepared', 'in_delivery', 'arrived', 'completed', 'returned'];
        
        $rentalQuery = $this->applyRegionFilter(RentalBooking::withTrashed(), 'barang', true)->with(['user', 'barang'])->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('payment_proof')
              ->orWhereIn('status', $activeStatuses);
        });

        $gasQuery = $this->applyRegionFilter(GasOrder::withTrashed(), 'gas', true)->with('user')->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('proof_of_payment')
              ->orWhereIn('status', $activeStatuses);
        });

        $mobilQuery = $this->applyRegionFilter(MobilBooking::withTrashed(), 'mobil', true)->with(['user', 'mobil'])->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('payment_proof')
              ->orWhereIn('status', $activeStatuses);
        });

        $fasilitasQuery = $this->applyRegionFilter(FasilitasUmumBooking::withTrashed(), 'fasilitas', true)->with(['user', 'fasilitas'])->where(function($q) use ($activeStatuses) {
            $q->whereIn('status', $activeStatuses);
        });

        // Filter by search
        if ($search) {
            $rentalQuery->where(function($q) use ($search) {
                $q->searchWhereLike('status', $search)
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->searchWhereLike('name', $search);
                  });
            });
            
            $gasQuery->where(function($q) use ($search) {
                $q->searchWhereLike('status', $search)
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->searchWhereLike('name', $search);
                  });
            });

            $mobilQuery->where(function($q) use ($search) {
                $q->searchWhereLike('status', $search)
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->searchWhereLike('name', $search);
                  });
            });

            $fasilitasQuery->where(function($q) use ($search) {
                $q->searchWhereLike('status', $search)
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->searchWhereLike('name', $search);
                  });
            });
        }

        // Filter by payment method
        if ($paymentMethod !== 'all') {
            $rentalQuery->where('payment_method', $paymentMethod);
            $gasQuery->where('payment_method', $paymentMethod);
            $mobilQuery->where('payment_method', $paymentMethod);
            $fasilitasQuery->where('payment_method', $paymentMethod);
        }

        // Get results based on category filter
        if ($category === 'rental') {
            $rentalPayments = $rentalQuery->orderByDesc('updated_at')->get();
            $gasPayments = collect();
            $mobilPayments = collect();
            $fasilitasPayments = collect();
        } elseif ($category === 'gas') {
            $rentalPayments = collect();
            $gasPayments = $gasQuery->orderByDesc('updated_at')->get();
            $mobilPayments = collect();
            $fasilitasPayments = collect();
        } elseif ($category === 'mobil') {
            $rentalPayments = collect();
            $gasPayments = collect();
            $mobilPayments = $mobilQuery->orderByDesc('updated_at')->get();
            $fasilitasPayments = collect();
        } elseif ($category === 'fasilitas') {
            $rentalPayments = collect();
            $gasPayments = collect();
            $mobilPayments = collect();
            $fasilitasPayments = $fasilitasQuery->orderByDesc('updated_at')->get();
        } else {
            $rentalPayments = $rentalQuery->orderByDesc('updated_at')->get();
            $gasPayments = $gasQuery->orderByDesc('updated_at')->get();
            $mobilPayments = $mobilQuery->orderByDesc('updated_at')->get();
            $fasilitasPayments = $fasilitasQuery->orderByDesc('updated_at')->get();
        }

        // Count statistics
        $rentalCount = RentalBooking::withTrashed()->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('payment_proof')
              ->orWhereIn('status', $activeStatuses);
        })->count();
        
        $gasCount = GasOrder::withTrashed()->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('proof_of_payment')
              ->orWhereIn('status', $activeStatuses);
        })->count();

        $mobilCount = MobilBooking::withTrashed()->where(function($q) use ($activeStatuses) {
            $q->whereNotNull('payment_proof')
              ->orWhereIn('status', $activeStatuses);
        })->count();

        $fasilitasCount = FasilitasUmumBooking::withTrashed()->where(function($q) use ($activeStatuses) {
            $q->whereIn('status', $activeStatuses);
        })->count();

        $stats = [
            'total' => $rentalCount + $gasCount + $mobilCount + $fasilitasCount,
            'rental_total' => $rentalCount,
            'gas_total' => $gasCount,
            'mobil_total' => $mobilCount,
            'fasilitas_total' => $fasilitasCount,
            'transfer_total' => 0, // Transfer is disabled for now
            'cash_total' => $rentalCount + $gasCount + $mobilCount + $fasilitasCount, // All valid transactions are essentially verified/cash/system
        ];

        $activeServices = $this->getActivatedServices();

        return view('admin.aktivitas.transactions', compact('rentalPayments', 'gasPayments', 'mobilPayments', 'fasilitasPayments', 'stats', 'category', 'paymentMethod', 'search', 'activeServices'));
    }

    public function verify(Request $request, $id, $type)
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Unauthorized: Admin access required');

        if ($type === 'rental') {
            $model = RentalBooking::withTrashed()->findOrFail($id);
        } elseif ($type === 'mobil') {
            $model = MobilBooking::withTrashed()->findOrFail($id);
        } elseif ($type === 'fasilitas') {
            $model = FasilitasUmumBooking::withTrashed()->findOrFail($id);
        } else {
            $model = GasOrder::withTrashed()->findOrFail($id);
        }

        $oldStatus = $model->status;
        $model->update(['status' => 'completed']);

        // Kembalikan stok barang jika status sebelumnya belum selesai
        if ($type === 'rental' && $oldStatus !== 'completed') {
            if (!$model->relationLoaded('barang')) $model->load('barang');
            if ($model->barang) $model->barang->increaseStock($model->quantity);
        } elseif ($type === 'mobil' && $oldStatus !== 'completed') {
            if (!$model->relationLoaded('mobil')) $model->load('mobil');
            if ($model->mobil) $model->mobil->update(['status' => 'tersedia']);
        } elseif ($type === 'fasilitas' && $oldStatus !== 'completed') {
            if (!$model->relationLoaded('fasilitas')) $model->load('fasilitas');
            if ($model->fasilitas) {
                $model->fasilitas->increment('stok');
                $model->fasilitas->update(['status' => 'tersedia']);
            }
        }

        // Trigger notifikasi: Pengajuan Selesai
        $notification = new Notification();
        $notification->title = "Transaksi Selesai";
        $notification->message = "Transaksi Anda (#{$model->id}) untuk {$type} telah diselesaikan.";
        $notification->type = 'pengajuan_selesai';
        $notification->user_id = $model->user_id; // User yang transaksi
        $notification->admin_id = auth()->id(); // Admin yang menyelesaikan
        $notification->save();

        // Audit log for payment verification
        ActivityLog::create([
            'action' => 'verify_payment',
            'description' => "Admin verified payment for {$type} #{$id}",
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
        ]);

        session()->flash('success', "Bukti pembayaran {$type} telah diverifikasi.");

        return redirect()->back();
    }

    public function reject(Request $request, $id, $type)
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Unauthorized: Admin access required');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($type === 'rental') {
            $model = RentalBooking::withTrashed()->findOrFail($id);
        } elseif ($type === 'mobil') {
            $model = MobilBooking::withTrashed()->findOrFail($id);
        } elseif ($type === 'fasilitas') {
            $model = FasilitasUmumBooking::withTrashed()->findOrFail($id);
        } else {
            $model = GasOrder::withTrashed()->findOrFail($id);
        }

        $model->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        // Trigger notifikasi: Bukti Pembayaran Ditolak
        $notification = new Notification();
        $notification->title = "Bukti Pembayaran Ditolak";
        $notification->message = "Bukti pembayaran untuk transaksi Anda (#{$model->id}) untuk {$type} telah ditolak oleh " . auth()->user()->name . ". Alasan: {$request->reason}";
        $notification->type = 'status_berubah'; // atau buat type baru seperti 'bukti_ditolak'
        $notification->user_id = $model->user_id; // User yang upload bukti
        $notification->admin_id = auth()->id(); // Admin yang menolak
        $notification->save();

        // Audit log for payment rejection
        ActivityLog::create([
            'action' => 'reject_payment',
            'description' => "Admin rejected payment for {$type} #{$id}. Reason: {$request->reason}",
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
        ]);

        session()->flash('warning', "Bukti pembayaran {$type} ditolak: {$request->reason}");

        return redirect()->back();
    }

    public function updateStatus(Request $request, $id, $type, $status)
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Unauthorized: Admin access required');

        // Validasi status yang diperbolehkan
        $allowedStatuses = ['being_prepared', 'in_delivery', 'arrived', 'completed', 'approved'];
        
        if (!in_array($status, $allowedStatuses)) {
             return redirect()->back()->with('error', 'Status tidak valid.');
        }

        if ($type === 'rental') {
            $model = RentalBooking::withTrashed()->findOrFail($id); // Model RentalBooking
        } elseif ($type === 'gas') {
            $model = GasOrder::withTrashed()->findOrFail($id); // Model GasOrder
        } elseif ($type === 'mobil') {
            $model = MobilBooking::withTrashed()->findOrFail($id);
        } elseif ($type === 'fasilitas') {
            $model = FasilitasUmumBooking::withTrashed()->findOrFail($id);
        } else {
             return redirect()->back()->with('error', 'Tipe transaksi tidak valid.');
        }

        // Update status
        $model->status = $status;
        $model->save();

        // Buat pesan notifikasi yang deskriptif
        $title = "Status Pesanan Diperbarui";
        $message = "Status pesanan {$type} Anda telah diperbarui menjadi: " . $this->getStatusLabel($status);

        // Kustomisasi pesan berdasarkan status spesifik
        if ($status === 'being_prepared') {
            $message = "Pesanan Anda sedang dipersiapkan oleh petugas.";
        } elseif ($status === 'in_delivery') {
            $message = "Pesanan Anda sedang dalam pengiriman ke lokasi tujuan.";
        } elseif ($status === 'arrived') {
             $message = "Pesanan Anda telah tiba di lokasi pengiriman.";
        } elseif ($status === 'completed') {
             $title = "Pesanan Selesai";
             $message = "Terima kasih! Pesanan Anda telah selesai.";
        }

        // Simpan notifikasi
        $notification = new Notification();
        $notification->title = $title;
        $notification->message = $message;
        $notification->type = 'status_berubah';
        $notification->user_id = $model->user_id;
        $notification->admin_id = auth()->id();
        $notification->save();

        return redirect()->back()->with('success', 'Status berhasil diperbarui menjadi ' . $this->getStatusLabel($status));
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'being_prepared' => 'Sedang Dipersiapkan',
            'in_delivery' => 'Dalam Pengiriman',
            'arrived' => 'Tiba di Lokasi',
            'completed' => 'Selesai',
            'approved' => 'Siap Diambil',
        ];

        return $labels[$status] ?? $status;
    }

    public function downloadProof($id, $type)
    {
        if ($type === 'rental') {
            $model = RentalBooking::withTrashed()->findOrFail($id);
        } elseif ($type === 'mobil') {
            $model = MobilBooking::withTrashed()->findOrFail($id);
        } elseif ($type === 'fasilitas') {
            $model = FasilitasUmumBooking::withTrashed()->findOrFail($id);
        } else {
            $model = GasOrder::withTrashed()->findOrFail($id);
        }

        // Determine proof column
        $proofColumn = ($type === 'gas') ? 'proof_of_payment' : 'payment_proof';

        if (!$model->$proofColumn) {
            abort(404, 'Bukti pembayaran tidak ditemukan.');
        }

        // Check if file exists in storage
        if (!Storage::disk('public')->exists($model->$proofColumn)) {
            abort(404, 'File bukti pembayaran tidak ditemukan di storage.');
        }

        return Storage::disk('public')->download($model->$proofColumn);
    }
}