<?php

namespace App\Http\Controllers\Admin;

use App\Models\RentalBooking;
use App\Models\GasOrder;
use App\Models\Gas;
use App\Models\Barang;
use App\Models\MobilBooking;
use App\Models\FasilitasUmumBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\ActivityLog;
use App\Services\NotificationService;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        // Ambil parameter filter
        $status = $request->get('status', 'all');
        $category = $request->get('category', 'all');

        // Buat query untuk pemesanan penyewaan (Include deleted for history)
        $rentalQuery = RentalBooking::withTrashed()->with(['user', 'barang']);
        if ($status !== 'all') {
            if ($status === 'cancellation_pending') {
                $rentalQuery->where('cancellation_status', 'pending');
            } elseif ($status === 'in_process') {
                $rentalQuery->whereIn('status', ['confirmed', 'approved', 'being_prepared', 'in_delivery', 'arrived']);
            } elseif ($status === 'completed') {
                $rentalQuery->whereIn('status', ['completed', 'resolved', 'returned']);
            } elseif ($status === 'rejected') {
                $rentalQuery->whereIn('status', ['cancelled', 'rejected']);
            } else {
                $rentalQuery->where('status', $status);
            }
        }

        // Buat query untuk pesanan gas (Include deleted for history)
        $gasQuery = GasOrder::withTrashed()->with('user');
        if ($status !== 'all') {
            if ($status === 'cancellation_pending') {
                $gasQuery->where('cancellation_status', 'pending');
            } elseif ($status === 'in_process') {
                $gasQuery->whereIn('status', ['confirmed', 'approved', 'being_prepared', 'in_delivery', 'arrived']);
            } elseif ($status === 'completed') {
                $gasQuery->whereIn('status', ['completed', 'resolved']);
            } elseif ($status === 'rejected') {
                $gasQuery->whereIn('status', ['cancelled', 'rejected']);
            } else {
                $gasQuery->where('status', $status);
            }
        }

        // Buat query untuk mobil
        $mobilQuery = MobilBooking::withTrashed()->with(['user', 'mobil']);
        if ($status !== 'all') {
            if ($status === 'cancellation_pending') {
                $mobilQuery->where('cancellation_status', 'pending');
            } elseif ($status === 'in_process') {
                $mobilQuery->whereIn('status', ['confirmed', 'approved', 'process', 'delivering', 'arrived']);
            } elseif ($status === 'completed') {
                $mobilQuery->whereIn('status', ['completed', 'resolved']);
            } elseif ($status === 'rejected') {
                $mobilQuery->whereIn('status', ['cancelled', 'rejected']);
            } else {
                $mobilQuery->where('status', $status);
            }
        }

        // Buat query untuk fasilitas umum
        $fasilitasQuery = FasilitasUmumBooking::withTrashed()->with(['user', 'fasilitas']);
        if ($status !== 'all') {
            if ($status === 'cancellation_pending') {
                $fasilitasQuery->where('cancellation_status', 'pending');
            } elseif ($status === 'in_process') {
                $fasilitasQuery->whereIn('status', ['confirmed', 'approved', 'ongoing', 'delivering', 'arrived']);
            } elseif ($status === 'completed') {
                $fasilitasQuery->whereIn('status', ['completed', 'resolved']);
            } elseif ($status === 'rejected') {
                $fasilitasQuery->whereIn('status', ['cancelled', 'rejected']);
            } else {
                $fasilitasQuery->where('status', $status);
            }
        }

        // Ambil hasil berdasarkan filter kategori
        if ($category === 'rental') {
            $rentalRequests = $rentalQuery->orderByDesc('created_at')->get();
            $gasOrders = collect();
            $mobilRequests = collect();
            $fasilitasRequests = collect();
        } elseif ($category === 'gas') {
            $rentalRequests = collect();
            $gasOrders = $gasQuery->orderByDesc('created_at')->get();
            $mobilRequests = collect();
            $fasilitasRequests = collect();
        } elseif ($category === 'mobil') {
            $rentalRequests = collect();
            $gasOrders = collect();
            $mobilRequests = $mobilQuery->orderByDesc('created_at')->get();
            $fasilitasRequests = collect();
        } elseif ($category === 'fasilitas') {
            $rentalRequests = collect();
            $gasOrders = collect();
            $mobilRequests = collect();
            $fasilitasRequests = $fasilitasQuery->orderByDesc('created_at')->get();
        } elseif ($category === 'gas') {
            $rentalRequests = collect();
            $gasOrders = $gasQuery->orderByDesc('created_at')->get();
        } elseif ($category === 'latest') {
            // Filter terbaru (7 hari terakhir)
            $rentalRequests = $rentalQuery->where('created_at', '>=', now()->subDays(7))->orderByDesc('created_at')->get();
            $gasOrders = $gasQuery->where('created_at', '>=', now()->subDays(7))->orderByDesc('created_at')->get();
            $mobilRequests = $mobilQuery->where('created_at', '>=', now()->subDays(7))->orderByDesc('created_at')->get();
            $fasilitasRequests = $fasilitasQuery->where('created_at', '>=', now()->subDays(7))->orderByDesc('created_at')->get();
        } else {
            $rentalRequests = $rentalQuery->orderByDesc('created_at')->get();
            $gasOrders = $gasQuery->orderByDesc('created_at')->get();
            $mobilRequests = $mobilQuery->orderByDesc('created_at')->get();
            $fasilitasRequests = $fasilitasQuery->orderByDesc('created_at')->get();
        }

        // Hitung statistik
        // Hitung statistik (Include deleted for history functionality)
        $stats = [
            'total' => RentalBooking::withTrashed()->count() + GasOrder::withTrashed()->count() + MobilBooking::withTrashed()->count() + FasilitasUmumBooking::withTrashed()->count(),
            'pending' => RentalBooking::where('status', 'pending')->count() + GasOrder::where('status', 'pending')->count() + MobilBooking::where('status', 'pending')->count() + FasilitasUmumBooking::where('status', 'pending')->count(),
            'approved' => RentalBooking::where('status', 'approved')->count() + GasOrder::where('status', 'approved')->count() + MobilBooking::where('status', 'approved')->count() + FasilitasUmumBooking::where('status', 'approved')->count(),
            'rejected' => RentalBooking::withTrashed()->whereIn('status', ['cancelled', 'rejected'])->count() + GasOrder::withTrashed()->whereIn('status', ['cancelled', 'rejected'])->count() + MobilBooking::withTrashed()->whereIn('status', ['cancelled', 'rejected'])->count() + FasilitasUmumBooking::withTrashed()->whereIn('status', ['cancelled', 'rejected'])->count(),
            'cancellation_pending' => RentalBooking::where('cancellation_status', 'pending')->count() + GasOrder::where('cancellation_status', 'pending')->count() + MobilBooking::where('cancellation_status', 'pending')->count() + FasilitasUmumBooking::where('cancellation_status', 'pending')->count(),
            'rental_total' => RentalBooking::withTrashed()->count(),
            'gas_total' => GasOrder::withTrashed()->count(),
            'mobil_total' => MobilBooking::withTrashed()->count(),
            'fasilitas_total' => FasilitasUmumBooking::withTrashed()->count(),
            'active_rental_count' => RentalBooking::whereIn('status', ['confirmed', 'being_prepared', 'in_delivery', 'arrived'])->sum('quantity'),
        ];

        // Hitung notifikasi detail
        $notificationCounts = [
            'rental' => [
                'pending' => RentalBooking::where('status', 'pending')->count(),
                'cancellation' => RentalBooking::where('cancellation_status', 'pending')->count(),
                'total' => RentalBooking::where('status', 'pending')->orWhere('cancellation_status', 'pending')->count()
            ],
            'gas' => [
                'pending' => GasOrder::where('status', 'pending')->count(),
                'cancellation' => GasOrder::where('cancellation_status', 'pending')->count(),
                'total' => GasOrder::where('status', 'pending')->orWhere('cancellation_status', 'pending')->count()
            ],
            'mobil' => [
                'pending' => MobilBooking::where('status', 'pending')->count(),
                'cancellation' => MobilBooking::where('cancellation_status', 'pending')->count(),
                'total' => MobilBooking::where('status', 'pending')->orWhere('cancellation_status', 'pending')->count()
            ],
            'fasilitas' => [
                'pending' => FasilitasUmumBooking::where('status', 'pending')->count(),
                'cancellation' => FasilitasUmumBooking::where('cancellation_status', 'pending')->count(),
                'total' => FasilitasUmumBooking::where('status', 'pending')->orWhere('cancellation_status', 'pending')->count()
            ],
        ];

        return response()
            ->view('admin.aktivitas.requests', compact('rentalRequests', 'gasOrders', 'mobilRequests', 'fasilitasRequests', 'stats', 'status', 'category', 'notificationCounts'))
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
    }

    public function getCounts()
    {
        $counts = [
            'rental' => [
                'pending' => RentalBooking::where('status', 'pending')->count(),
                'cancellation' => RentalBooking::where('cancellation_status', 'pending')->count(),
                'total' => RentalBooking::where('status', 'pending')->orWhere('cancellation_status', 'pending')->count()
            ],
            'gas' => [
                'pending' => GasOrder::where('status', 'pending')->count(),
                'cancellation' => GasOrder::where('cancellation_status', 'pending')->count(),
                'total' => GasOrder::where('status', 'pending')->orWhere('cancellation_status', 'pending')->count()
            ],
            'mobil' => [
                'pending' => MobilBooking::where('status', 'pending')->count(),
                'cancellation' => MobilBooking::where('cancellation_status', 'pending')->count(),
                'total' => MobilBooking::where('status', 'pending')->orWhere('cancellation_status', 'pending')->count()
            ],
            'fasilitas' => [
                'pending' => FasilitasUmumBooking::where('status', 'pending')->count(),
                'cancellation' => FasilitasUmumBooking::where('cancellation_status', 'pending')->count(),
                'total' => FasilitasUmumBooking::where('status', 'pending')->orWhere('cancellation_status', 'pending')->count()
            ],
        ];

        return response()->json($counts);
    }

    public function show($id, $type)
    {
        if ($type === 'rental') {
            $request = RentalBooking::withTrashed()->with(['user', 'barang'])->findOrFail($id);
        } elseif ($type === 'mobil') {
            $request = MobilBooking::withTrashed()->with(['user', 'mobil'])->findOrFail($id);
        } elseif ($type === 'fasilitas') {
            $request = FasilitasUmumBooking::withTrashed()->with(['user', 'fasilitas'])->findOrFail($id);
        } else {
            $request = GasOrder::withTrashed()->with('user')->findOrFail($id);
        }

        return view('admin.aktivitas.request-detail', compact('request', 'type'));
    }

    public function approve(Request $request, $id, $type)
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Unauthorized: Admin access required');

        $notificationService = new NotificationService();

        try {
            DB::beginTransaction();

            if ($type === 'rental') {
                $model = RentalBooking::with('barang')->findOrFail($id);
                
                // Periksa apakah sudah disetujui
                if ($model->status !== 'pending') {
                    throw new \Exception("Permintaan sudah diproses sebelumnya.");
                }

                // Ambil barang dan validasi stok
                $barang = $model->barang;
                $quantity = $model->quantity;

                if (!$barang->hasStock($quantity)) {
                    // Kirim notifikasi tentang stok tidak mencukupi
                    $notificationService->notifyStockInsufficient($model, 'rental', $barang->stok, $quantity);
                    
                    throw new \Exception("Stok tidak mencukupi. Tersedia: {$barang->stok}, diminta: {$quantity}");
                }

                // Kurangi stok
                $barang->decreaseStock($quantity);

                // Periksa apakah stok rendah setelah pengurangan
                if ($barang->stok < 5) {
                    $notificationService->notifyLowStock($barang, 'barang', $barang->stok);
                }

                // Periksa apakah stok habis
                if ($barang->stok == 0) {
                    $notificationService->notifyStockDepleted($barang, 'barang');
                }

                // Perbarui status pemesanan
                $newStatus = 'confirmed';
                $updateData = [
                    'status' => $newStatus,
                    'confirmed_at' => now()
                ];
                
                // Generate order number if not exists
                if (!$model->order_number) {
                    $updateData['order_number'] = \App\Models\RentalBooking::generateOrderNumber();
                }
                
                $model->update($updateData);

            } elseif ($type === 'mobil') {
                $model = MobilBooking::with('mobil')->findOrFail($id);
                if ($model->status !== 'pending') throw new \Exception("Permintaan sudah diproses sebelumnya.");

                $mobil = $model->mobil;
                if ($mobil->status !== 'tersedia') throw new \Exception("Mobil sedang tidak tersedia.");
                
                $mobil->update(['status' => 'disewa']);

                $newStatus = 'confirmed';
                $updateData = ['status' => $newStatus, 'confirmed_at' => now()];
                $model->update($updateData);

            } elseif ($type === 'fasilitas') {
                $model = FasilitasUmumBooking::with('fasilitas')->findOrFail($id);
                if ($model->status !== 'pending') throw new \Exception("Permintaan sudah diproses sebelumnya.");

                $fasilitas = $model->fasilitas;
                if ($fasilitas->stok < 1) throw new \Exception("Fasilitas sedang tidak tersedia.");
                
                $fasilitas->decrement('stok');
                if ($fasilitas->stok == 0) {
                    $fasilitas->update(['status' => 'disewa']);
                }

                $newStatus = 'confirmed';
                $updateData = ['status' => $newStatus, 'confirmed_at' => now()];
                $model->update($updateData);

            } else {
                $model = GasOrder::findOrFail($id);
                
                // Periksa apakah sudah disetujui
                if ($model->status !== 'pending') {
                    throw new \Exception("Permintaan sudah diproses sebelumnya.");
                }

                // Ambil gas dan validasi stok
                $gas = Gas::findOrFail($model->gas_id);
                $quantity = $model->quantity;

                if (!$gas->hasStock($quantity)) {
                    // Send notifications about insufficient stock
                    $notificationService->notifyStockInsufficient($model, 'gas', $gas->stok, $quantity);
                    
                    throw new \Exception("Stok tidak mencukupi. Tersedia: {$gas->stok}, diminta: {$quantity}");
                }

                // Kurangi stok (PERMANEN untuk gas)
                $gas->decreaseStock($quantity);

                // Check if stock is low after decrease
                if ($gas->stok < 5) {
                    $notificationService->notifyLowStock($gas, 'gas', $gas->stok);
                }

                // Check if stock is depleted
                if ($gas->stok == 0) {
                    $notificationService->notifyStockDepleted($gas, 'gas');
                }

                // Perbarui status pesanan
                $newStatus = 'confirmed';
                $updateData = [
                    'status' => $newStatus,
                    'confirmed_at' => now()
                ];
                
                // Buat nomor pesanan jika belum ada
                if (!$model->order_number) {
                    $updateData['order_number'] = GasOrder::generateOrderNumber();
                }

                $model->update($updateData);
            }

            // Kirim notifikasi persetujuan berhasil ke pengguna
            $notificationService->notifyOrderApproved($model, $type);

            // Audit log for approval
            ActivityLog::create([
                'action' => 'approve_request',
                'description' => "Admin approved {$type} request #{$id}",
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            $message = "Permintaan {$type} berhasil disetujui dan stok telah diperbarui.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'status' => $newStatus
                ]);
            }

            session()->flash('success', $message);
            return redirect()->back();

        } catch (\Exception $e) {
            DB::rollBack();

            $errorMessage = $e->getMessage();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 400);
            }

            session()->flash('error', $errorMessage);
            return redirect()->back();
        }
    }

    public function reject(Request $request, $id, $type)
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Unauthorized: Admin access required');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $notificationService = new NotificationService();

        if ($type === 'rental') {
            $model = RentalBooking::findOrFail($id);
            // RentalBooking menggunakan ENUM Ketat: pending, confirmed, in_progress, completed, cancelled
            // Kami menggunakan 'cancelled' untuk mewakili Penolakan oleh Admin, dan menjelaskan dalam catatan
            $newStatus = 'cancelled';
            $model->update([
                'status' => $newStatus,
                'admin_notes' => "Ditolak: " . $request->reason,
                'cancellation_reason' => "Ditolak Admin: " . $request->reason,
                // Pastikan cancellation_status tidak 'pending' untuk menghindari kebingungan
                'cancellation_status' => null 
            ]);
        } elseif ($type === 'mobil') {
            $model = MobilBooking::findOrFail($id);
            $newStatus = 'rejected';
            $model->update([
                'status' => $newStatus,
                'admin_notes' => "Ditolak: " . $request->reason,
                'cancellation_reason' => "Ditolak Admin: " . $request->reason,
                'cancellation_status' => null 
            ]);
        } elseif ($type === 'fasilitas') {
            $model = FasilitasUmumBooking::findOrFail($id);
            $newStatus = 'rejected';
            $model->update([
                'status' => $newStatus,
                'admin_notes' => "Ditolak: " . $request->reason,
                'cancellation_reason' => "Ditolak Admin: " . $request->reason,
                'cancellation_status' => null 
            ]);
        } else {
            $model = GasOrder::findOrFail($id);
            // GasOrder menggunakan status string, kemungkinan mendukung 'rejected'
            $newStatus = 'rejected';
            $model->update([
                'status' => $newStatus,
                'rejection_reason' => $request->reason,
            ]);
        }

        // Kirim notifikasi penolakan ke pengguna
        $notificationService->notifyOrderRejected($model, $request->reason, $type);

        // Audit log for rejection
        ActivityLog::create([
            'action' => 'reject_request',
            'description' => "Admin rejected {$type} request #{$id}. Reason: {$request->reason}",
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
        ]);

        $message = "Permintaan {$type} ditolak dengan alasan: {$request->reason}";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $newStatus
            ]);
        }

        session()->flash('warning', $message);
        return redirect()->back();
    }

    public function updateStatus(Request $request, $type, $id)
    {
        abort_unless(auth()->user()->role === 'admin', 403, 'Unauthorized: Admin access required');

        $request->validate([
            'status' => 'required|string|in:confirmed,being_prepared,in_delivery,arrived,completed,approved',
        ]);

        $notificationService = new NotificationService();

        if ($type === 'rental') {
            $order = \App\Models\RentalBooking::with('barang')->findOrFail($id);
        } elseif ($type === 'mobil') {
            $order = MobilBooking::findOrFail($id);
        } elseif ($type === 'fasilitas') {
            $order = FasilitasUmumBooking::findOrFail($id);
        } else {
            $order = GasOrder::findOrFail($id);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        try {
            DB::beginTransaction();

            // Buat stempel waktu otomatis berdasarkan status
            switch ($newStatus) {
                case 'confirmed':
                    if (!$order->confirmed_at) {
                        $order->confirmed_at = now();
                    }
                    // Buat nomor pesanan jika belum ada
                    if (!$order->order_number) {
                        $order->order_number = $type === 'rental' 
                            ? \App\Models\RentalBooking::generateOrderNumber()
                            : GasOrder::generateOrderNumber();
                    }
                    break;
                case 'being_prepared':
                    // Tidak ada kolom khusus, hanya pembaruan status
                    break;
                case 'in_delivery':
                    if (!$order->delivery_time) {
                        $order->delivery_time = now();
                    }
                    break;
                case 'arrived':
                    // Validasi bukti pengiriman harus ada jika status diubah ke arrived
                    // Kecuali jika metode pengiriman adalah jemput sendiri/diambil
                    
                    // Kita cek metode pengiriman
                    $isDelivery = false;
                    if ($type === 'rental') {
                        $isDelivery = $order->delivery_method == 'antar';
                    } else {
                        // Untuk gas, biasanya diantar, tapi check logic
                        $isDelivery = true; // Asumsi default gas diantar, sesuaikan jika ada field delivery_method di GasOrder
                        if (isset($order->delivery_method) && $order->delivery_method == 'jemput') {
                            $isDelivery = false;
                        }
                    }

                    if ($isDelivery && !$order->delivery_proof_image && !$request->hasFile('delivery_proof') && $oldStatus != 'arrived') {
                         // Jika ingin strict, uncomment baris ini. 
                         // Tapi karena kita handled upload terpisah, kita biarkan saja, tapi idealnya update ke arrived dilakukan via upload.
                         // throw new \Exception("Bukti pengiriman wajib diupload sebelum mengubah status ke Tiba.");
                    }

                    if (!$order->arrival_time) {
                        $order->arrival_time = now();
                    }
                    break;
                case 'completed':
                    if (!$order->completion_time) {
                        $order->completion_time = now();
                    }

                    // FIX: Return stock when admin marks rental as completed
                    if ($type === 'rental' && $oldStatus !== 'completed') {
                        // Ensure barang is loaded
                        if (!$order->relationLoaded('barang')) {
                            $order->load('barang');
                        }
                        
                        if ($order->barang) {
                            $order->barang->increaseStock($order->quantity);
                            
                            // Check if stock is still low even after return (rare but possible)
                            if ($order->barang->stok < 5 && $order->barang->stok > 0) {
                                $notificationService->notifyLowStock($order->barang, 'barang', $order->barang->stok);
                            }
                        }
                    } elseif ($type === 'mobil' && $oldStatus !== 'completed') {
                        if (!$order->relationLoaded('mobil')) $order->load('mobil');
                        if ($order->mobil) {
                            $order->mobil->update(['status' => 'tersedia']);
                        }
                    } elseif ($type === 'fasilitas' && $oldStatus !== 'completed') {
                        if (!$order->relationLoaded('fasilitas')) $order->load('fasilitas');
                        if ($order->fasilitas) {
                            $order->fasilitas->increment('stok');
                            $order->fasilitas->update(['status' => 'tersedia']);
                        }
                    }
                    break;
            }

            // Perbarui status
            $order->status = $newStatus;
            $order->save();

            // Hanya kirim notifikasi pembaruan status tertentu jika bukan 'completed'
            // (untuk completed kita mungkin ingin menanganinya secara berbeda atau mengizinkan switch case di service untuk menanganinya)
            // Berdasarkan permintaan pengguna completed juga memiliki pesan khusus
            
            $notificationService->notifyOrderStatusUpdate($order, $newStatus);

            // Log Activity
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Update Status',
                'description' => "Mengubah status pesanan #{$order->order_number} dari {$oldStatus} menjadi {$newStatus}",
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status berhasil diperbarui',
                    'order' => $order
                ]);
            }

            return redirect()->back()->with('success', 'Status berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error updating status: ' . $e->getMessage());

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function uploadDeliveryProof(Request $request, $type, $id)
    {
        $request->validate([
            'delivery_proof' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        try {
            DB::beginTransaction();

            if ($type === 'rental') {
                $order = \App\Models\RentalBooking::findOrFail($id);
            } else {
                $order = GasOrder::findOrFail($id);
            }

            // Simpan gambar
            if ($request->hasFile('delivery_proof')) {
                // Hapus bukti lama jika ada
                if ($order->delivery_proof_image) {
                    Storage::disk('public')->delete($order->delivery_proof_image);
                }

                $path = $request->file('delivery_proof')->store('delivery_proofs', 'public');
                
                if (!$path) {
                    throw new \Exception("Gagal menyimpan file gambar.");
                }

                $order->delivery_proof_image = $path;
                
                // Perbarui status otomatis ke arrived jika belum
                if ($order->status !== 'arrived' && $order->status !== 'completed') {
                    $order->status = 'arrived';
                    if (!$order->arrival_time) {
                        $order->arrival_time = now();
                    }
                }
                
                $order->save();

                // Kirim notifikasi ke pengguna
                Notification::create([
                    'title' => 'Bukti Pengiriman Tersedia',
                    'message' => "Bukti pengiriman untuk pesanan #{$order->order_number} telah tersedia.",
                    'type' => 'delivery_proof',
                    'user_id' => $order->user_id,
                    'admin_id' => auth()->id(),
                ]);
                
                // Log Activity
                \App\Models\ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'Upload Proof',
                    'description' => "Upload bukti pengiriman untuk pesanan #{$order->order_number}",
                    'ip_address' => $request->ip()
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Bukti pengiriman berhasil diunggah',
                    'path' => $path
                ]);
            }

            throw new \Exception("Tidak ada file yang diunggah.");

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Upload Proof Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    public function handleCancellation(Request $request, $type, $id, $action)
    {
        if (!in_array($action, ['approve', 'reject'])) {
            return response()->json([
                'success' => false,
                'message' => 'Aksi tidak valid'
            ], 400);
        }

        if ($type === 'rental') {
            $order = \App\Models\RentalBooking::findOrFail($id);
        } else {
            $order = GasOrder::findOrFail($id);
        }

        if ($order->cancellation_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada permintaan pembatalan yang pending'
            ], 400);
        }

        $order->cancellation_status = $action === 'approve' ? 'approved' : 'rejected';
        $order->admin_cancellation_response = $request->admin_response;

        $message = '';
        if ($action === 'approve') {
            $order->status = 'cancelled'; // Update status utama jadi cancelled
            $message = 'Permintaan pembatalan disetujui';
            
            // Logika pengembalian dana atau stok jika perlu (biasanya stok dikembalikan jika dibatalkan)
             if ($type === 'rental') {
                $order->load('barang');
                if ($order->barang) {
                    $order->barang->increaseStock($order->quantity);
                }
            }
            
            // Kirim notifikasi ke pengguna
            Notification::create([
                'title' => 'Pembatalan Disetujui',
                'message' => "Permintaan pembatalan pesanan #{$order->order_number} Anda telah disetujui.",
                'type' => 'cancellation_approved',
                'user_id' => $order->user_id,
                'admin_id' => auth()->id(),
            ]);

        } else {
            // Jika ditolak, status utama tetap seperti sebelumnya (misal: pending atau confirmed)
            // Tidak perlu ubah $order->status
            $message = 'Permintaan pembatalan ditolak';
            
            // Kirim notifikasi ke pengguna
            Notification::create([
                'title' => 'Pembatalan Ditolak',
                'message' => "Permintaan pembatalan pesanan #{$order->order_number} ditolak. Alasan: {$request->admin_response}",
                'type' => 'cancellation_rejected',
                'user_id' => $order->user_id,
                'admin_id' => auth()->id(),
            ]);
        }

        // Log Activity
        \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'Cancellation Review',
            'description' => ucfirst($action) . " permintaan pembatalan pesanan #{$order->order_number}",
            'ip_address' => $request->ip()
        ]);

        $order->save();

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
    public function returnRental(Request $request, $id)
    {
        $request->validate([
            'return_time' => 'required|date',
        ]);

        $notificationService = new NotificationService();
        $order = \App\Models\RentalBooking::with('barang')->findOrFail($id);

        if ($order->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan sudah selesai sebelumnya.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Perbarui waktu
            $order->return_time = $request->return_time;
            $order->completion_time = $request->return_time;
            $order->status = 'completed';
            $order->save();

            // PENGEMBALIAN STOK 
            $barang = $order->barang;
            $quantity = $order->quantity;

            // Tambah stok kembali
            $barang->increaseStock($quantity);

            // Notifikasi
            $notificationService->notifyRentalCompleted($order);

            // Periksa stok rendah
            if ($barang->stok < 5 && $barang->stok > 0) {
                $notificationService->notifyLowStock($barang, 'barang', $barang->stok);
            }

            // Log Activity
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Return Rental',
                'description' => "Memproses pengembalian alat pesanan #{$order->order_number}",
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil dikembalikan dan stok diperbarui',
                'return_time' => $request->return_time
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}