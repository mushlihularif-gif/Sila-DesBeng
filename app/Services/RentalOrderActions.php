<?php

namespace App\Services;

use App\Models\{ActivityLog, AdminNotification, Barang, RentalBooking, Notification, User, WalletTransaction};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalOrderActions
{
    public function admin(Request $request, int $id, string $action)
    {
        $user = $request->user();
        abort_unless($user && $user->isAdmin() && $user->region_id && $user->hasUnitPermission('sewa_alat'), 403);
        if ($action === 'returned') $request->validate(['return_time'=>'required|date|before_or_equal:now', 'returned_quantity'=>'required|integer|min:0', 'return_notes'=>'nullable|string|max:1000']);
        if (in_array($action, ['reject', 'reject-payment'], true)) $request->validate(['reason' => 'required|string|max:500']);
        $order = DB::transaction(function () use ($request, $id, $action, $user) {
            $order = RentalBooking::whereHas('barang', fn ($q) => $q->where('region_id', $user->region_id))->lockForUpdate()->findOrFail($id);
            abort_if(in_array($order->status, ['completed', 'cancelled', 'rejected'], true), 409, 'Pesanan sudah selesai atau dibatalkan.');
            abort_if($order->cancellation_status === 'pending', 409, 'Tangani permintaan pembatalan terlebih dahulu.');
            $ledger = WalletTransaction::where('reference_type', 'rental')->where('reference_id', $id)->lockForUpdate()->get();
            $matches = abs((float)$ledger->sum('amount') - (float)$order->total_amount) < 0.005
                && $ledger->every(fn ($entry) => (int)$entry->region_id === (int)($order->region_id ?? $order->barang->region_id));
            $paid = $matches && $ledger->isNotEmpty() && $ledger->every(fn ($entry) => $entry->status === 'verified');
            $cash = strtolower($order->payment_method) === 'tunai';
            switch ($action) {
                case 'verify':
                    abort_unless($matches, 409, 'Nilai atau wilayah catatan pembayaran tidak sesuai pesanan. Periksa catatan sebelum konfirmasi.');
                    abort_unless($cash || $order->payment_proof, 422, 'Bukti pembayaran belum diunggah.');
                    abort_if($ledger->isEmpty() || $ledger->contains(fn ($row) => $row->source !== 'manual' || $row->status === 'rejected'), 409, 'Catatan pembayaran tidak dapat diverifikasi.');
                    if ($paid) return $order;
                    WalletTransaction::tandaiPembayaranManualDiterima('rental', $id, $user->id);
                    $message = 'Pembayaran sewa alat telah dikonfirmasi diterima. Status pengiriman tetap mengikuti proses pesanan.';
                    break;
                case 'reject-payment':
                    abort_unless(!$cash && !$paid && $order->payment_proof && $order->status === 'pending', 409, 'Bukti ini tidak dapat dikembalikan untuk diperbaiki.');
                    $oldProof = $order->payment_proof;
                    DB::afterCommit(fn () => \Illuminate\Support\Facades\Storage::disk('public')->delete($oldProof));
                    $order->payment_proof = null;
                    WalletTransaction::whereKey($ledger->modelKeys())->where('status', 'pending')->update(['proof_path' => null, 'notes' => $request->reason]);
                    $message = 'Bukti pembayaran perlu diganti: '.$request->reason.'. Buka rincian pesanan untuk mengirim ulang.';
                    break;
                case 'reject':
                    abort_unless($order->status === 'pending', 409, 'Hanya pesanan yang belum diproses dapat ditolak.');
                    $order->status = 'rejected';
                    $order->admin_notes = $request->reason;
                    WalletTransaction::batalkanDanRefund('rental', $id, 'Pesanan ditolak: '.$request->reason);
                    $message = 'Pesanan sewa alat ditolak: '.$request->reason.'. Jika sudah membayar manual, hubungi pengelola untuk pengembalian dana.';
                    break;
                case 'approved':
                case 'confirmed':
                    abort_unless($order->status === 'pending', 409, 'Pesanan sudah diproses sebelumnya.');
                    abort_unless($cash || $paid, 409, 'Periksa dan konfirmasi pembayaran terlebih dahulu.');
                    $barang = Barang::lockForUpdate()->findOrFail($order->barang_id);
                    abort_unless($barang->status === 'tersedia' && $barang->hasStock($order->quantity), 409, 'Alat tidak tersedia atau stok tidak mencukupi.');
                    $barang->decreaseStock($order->quantity);
                    $order->status = 'confirmed';
                    $order->confirmed_at = now();
                    $message = 'Pesanan sewa alat disetujui dan sedang disiapkan.';
                    break;
                case 'returned':
                    abort_unless(in_array($order->status, ['arrived','picked_up','delivered'], true), 409, 'Alat belum diserahkan kepada penyewa atau sudah dikembalikan.');
                    abort_unless($paid, 409, 'Konfirmasi pembayaran diterima sebelum menyelesaikan sewa.');
                    $returnedAt = \Carbon\Carbon::parse($request->return_time);
                    abort_unless($returnedAt->gte(($order->arrival_time ?? $order->confirmed_at ?? $order->created_at)->copy()->startOfMinute()), 422, 'Waktu kembali tidak boleh mendahului penyerahan alat.');
                    abort_if($request->integer('returned_quantity') > $order->quantity, 422, 'Jumlah kembali melebihi jumlah yang disewa.');
                    if ($request->integer('returned_quantity') < $order->quantity) {
                        $request->validate(['return_notes'=>'required|string|max:1000']);
                    }
                    $barang = Barang::lockForUpdate()->findOrFail($order->barang_id);
                    $barang->increment('stok', $request->integer('returned_quantity'));
                    $order->returned_quantity = $request->integer('returned_quantity');
                    $order->return_notes = $request->input('return_notes');
                    $order->return_time = $returnedAt;
                    $order->completion_time = now();
                    $order->status = 'completed';
                    $message = 'Sewa selesai. Alat layak pakai yang kembali ke stok: '.$order->returned_quantity.' dari '.$order->quantity.'.';
                    break;
                default:
                    // "arrived" means physically handed over, including collection at the provider.
                    $transitions = [
                        'confirmed' => $order->delivery_method === 'antar' ? ['being_prepared','in_delivery'] : ['being_prepared','arrived'],
                        'approved' => $order->delivery_method === 'antar' ? ['being_prepared','in_delivery'] : ['being_prepared','arrived'],
                        'being_prepared' => $order->delivery_method === 'antar' ? ['in_delivery'] : ['arrived'],
                        'in_delivery' => ['arrived'],
                    ];
                    abort_unless(in_array($action, $transitions[$order->status] ?? [], true), 409, 'Ikuti urutan sewa. Gunakan Catat Pengembalian untuk menyelesaikan sewa.');
                    if ($action === 'arrived' && $order->delivery_method === 'antar') abort_unless($order->delivery_proof_image, 422, 'Unggah bukti penyerahan alat terlebih dahulu.');
                    $order->status = $action;
                    $timestamp = ['in_delivery'=>'delivery_time','arrived'=>'arrival_time'][$action] ?? null;
                    if ($timestamp) $order->$timestamp = now();
                    $labels = ['being_prepared'=>'sedang disiapkan','in_delivery'=>'sedang diantar','arrived'=>'telah diserahkan kepada penyewa'];
                    $message = 'Alat sewa '.$labels[$action].'.';
            }
            $order->handled_by = $user->id;
            $order->save();
            Notification::create(['user_id' => $order->user_id, 'type' => 'status_berubah', 'title' => 'Perkembangan Pesanan Sewa Alat',
                'message' => $message, 'link' => route('rental.payment', $order->id), 'is_read' => false]);
            ActivityLog::create(['user_id' => $user->id, 'action' => 'rental_'.$action, 'description' => 'Pesanan sewa alat #'.$id.': '.$message, 'ip_address' => $request->ip()]);
            return $order;
        });
        $message = $action === 'verify' ? 'Pembayaran dikonfirmasi. Lanjutkan proses pesanan melalui Permintaan dan Pengajuan.' : 'Perubahan sewa alat tersimpan.';
        return ($request->expectsJson() || $request->is('api/*')) ? response()->json(['success' => true, 'message' => $message, 'status' => $order->status]) : back()->with('success', $message);
    }

    public function requestCancellation(Request $request, int $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        DB::transaction(function () use ($request, $id) {
            $order = RentalBooking::where('user_id', $request->user()->id)->lockForUpdate()->findOrFail($id);
            abort_unless($order->canBeCancelled(), 409, 'Pesanan tidak dapat dibatalkan atau permintaan sudah dikirim.');
            $order->update(['cancellation_reason' => $request->reason, 'cancellation_requested_at' => now(), 'cancellation_status' => 'pending']);
            AdminNotification::create(['type' => 'rental_request', 'reference_id' => $id, 'region_id' => $order->barang->region_id,
                'title' => 'Permintaan Pembatalan Sewa Alat', 'message' => 'Periksa pembatalan pesanan '.$order->order_number.'.', 'is_read' => false]);
        });
        return response()->json(['success' => true, 'message' => 'Permintaan pembatalan dikirim ke pengelola.']);
    }

    public function cancelUnpaid(Request $request, int $id)
    {
        DB::transaction(function () use ($request, $id) {
            $order = RentalBooking::where('user_id', $request->user()->id)->lockForUpdate()->findOrFail($id);
            $paid = WalletTransaction::where('reference_type', 'rental')->where('reference_id', $id)->where('status', 'verified')->exists();
            abort_unless($order->canBeCancelled() && !$paid && !$order->payment_proof, 409, 'Ajukan pembatalan melalui Riwayat Pesanan agar pembayaran diperiksa pengelola.');
            $order->update(['status' => 'cancelled']);
            WalletTransaction::where('reference_type', 'rental')->where('reference_id', $id)->where('status', 'pending')->update(['status' => 'rejected', 'notes' => 'Dibatalkan pemesan sebelum pembayaran']);
            Notification::create(['user_id' => $order->user_id, 'type' => 'status_berubah', 'title' => 'Pesanan Sewa Alat Dibatalkan',
                'message' => 'Pesanan '.$order->order_number.' dibatalkan atas permintaan Anda.', 'link' => NotificationService::orderLink($order, 'rental')]);
        });
        return response()->json(['success' => true, 'message' => 'Pesanan dibatalkan.']);
    }
}
