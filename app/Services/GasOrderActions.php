<?php

namespace App\Services;

use App\Models\{ActivityLog, AdminNotification, Gas, GasOrder, Notification, User, WalletTransaction};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GasOrderActions
{
    public function admin(Request $request, int $id, string $action)
    {
        $user = $request->user();
        abort_unless($user && $user->isAdmin() && $user->region_id && $user->hasUnitPermission('gas'), 403);
        if (in_array($action, ['reject', 'reject-payment'], true)) $request->validate(['reason' => 'required|string|max:500']);
        $order = DB::transaction(function () use ($request, $id, $action, $user) {
            $order = GasOrder::whereHas('gas', fn ($q) => $q->where('region_id', $user->region_id))->lockForUpdate()->findOrFail($id);
            abort_if(in_array($order->status, ['completed', 'cancelled', 'rejected'], true), 409, 'Pesanan sudah selesai atau dibatalkan.');
            abort_if($order->cancellation_status === 'pending', 409, 'Tangani permintaan pembatalan terlebih dahulu.');
            $ledger = WalletTransaction::where('reference_type', 'gas')->where('reference_id', $id)->lockForUpdate()->get();
            $paid = $ledger->isNotEmpty() && $ledger->every(fn ($entry) => $entry->status === 'verified');
            $cash = strtolower($order->payment_method) === 'tunai';
            switch ($action) {
                case 'verify':
                    abort_unless($cash || $order->proof_of_payment, 422, 'Bukti pembayaran belum diunggah.');
                    abort_if($ledger->isEmpty() || $ledger->contains(fn ($row) => $row->source !== 'manual' || $row->status === 'rejected'), 409, 'Catatan pembayaran tidak dapat diverifikasi.');
                    if ($paid) return $order;
                    WalletTransaction::tandaiPembayaranManualDiterima('gas', $id, $user->id);
                    $message = 'Pembayaran gas telah dikonfirmasi diterima. Status pengiriman tetap mengikuti proses pesanan.';
                    break;
                case 'reject-payment':
                    abort_unless(!$cash && !$paid && $order->proof_of_payment && $order->status === 'pending', 409, 'Bukti ini tidak dapat dikembalikan untuk diperbaiki.');
                    $oldProof = $order->proof_of_payment;
                    DB::afterCommit(fn () => \Illuminate\Support\Facades\Storage::disk('public')->delete($oldProof));
                    $order->proof_of_payment = null;
                    WalletTransaction::whereKey($ledger->modelKeys())->where('status', 'pending')->update(['proof_path' => null, 'notes' => $request->reason]);
                    $message = 'Bukti pembayaran perlu diganti: '.$request->reason.'. Buka rincian pesanan untuk mengirim ulang.';
                    break;
                case 'reject':
                    abort_unless($order->status === 'pending', 409, 'Hanya pesanan yang belum diproses dapat ditolak.');
                    $order->status = 'rejected';
                    $order->rejection_reason = $request->reason;
                    WalletTransaction::batalkanDanRefund('gas', $id, 'Pesanan ditolak: '.$request->reason);
                    $message = 'Pesanan gas ditolak: '.$request->reason.'. Jika sudah membayar manual, hubungi pengelola untuk pengembalian dana.';
                    break;
                case 'approved':
                case 'confirmed':
                    abort_unless($order->status === 'pending', 409, 'Pesanan sudah diproses sebelumnya.');
                    abort_unless($cash || $paid, 409, 'Periksa dan konfirmasi pembayaran terlebih dahulu.');
                    $gas = Gas::lockForUpdate()->findOrFail($order->gas_id);
                    abort_unless($gas->status === 'tersedia' && $gas->hasStock($order->quantity), 409, 'Gas tidak tersedia atau stok tidak mencukupi.');
                    $gas->decreaseStock($order->quantity);
                    $order->status = 'confirmed';
                    $order->confirmed_at = now();
                    $message = 'Pesanan gas disetujui dan sedang disiapkan.';
                    break;
                default:
                    $transitions = [
                        'confirmed' => $order->delivery_method === 'antar' ? ['being_prepared', 'in_delivery'] : ['being_prepared', 'arrived', 'completed'],
                        'approved' => $order->delivery_method === 'antar' ? ['being_prepared', 'in_delivery'] : ['being_prepared', 'arrived', 'completed'],
                        'being_prepared' => $order->delivery_method === 'antar' ? ['in_delivery'] : ['arrived', 'completed'],
                        'in_delivery' => ['arrived'], 'arrived' => ['completed'],
                    ];
                    abort_unless(in_array($action, $transitions[$order->status] ?? [], true), 409, 'Urutan status tidak sesuai. Selesaikan tahap sebelumnya terlebih dahulu.');
                    if ($action === 'arrived' && $order->delivery_method === 'antar') {
                        abort_unless($order->delivery_proof_image, 422, 'Unggah bukti pengiriman terlebih dahulu.');
                    }
                    if ($action === 'completed') abort_unless($paid, 409, 'Catat pembayaran diterima sebelum menyelesaikan pesanan.');
                    $order->status = $action;
                    $timestamp = ['in_delivery' => 'delivery_time', 'arrived' => 'arrival_time', 'completed' => 'completion_time'][$action] ?? null;
                    if ($timestamp) $order->$timestamp = now();
                    $labels = ['being_prepared' => 'sedang disiapkan', 'in_delivery' => 'sedang diantar', 'arrived' => 'sudah tiba / siap diambil', 'completed' => 'selesai'];
                    $message = 'Pesanan gas '.$labels[$action].'.';
            }
            $order->handled_by = $user->id;
            $order->save();
            Notification::create(['user_id' => $order->user_id, 'type' => 'status_berubah', 'title' => 'Perkembangan Pesanan Gas',
                'message' => $message, 'link' => route('user.gas.payment', $order->id), 'is_read' => false]);
            ActivityLog::create(['user_id' => $user->id, 'action' => 'gas_'.$action, 'description' => 'Pesanan gas #'.$id.': '.$message, 'ip_address' => $request->ip()]);
            return $order;
        });
        $message = $action === 'verify' ? 'Pembayaran dikonfirmasi. Lanjutkan proses pesanan melalui Permintaan dan Pengajuan.' : 'Perubahan pesanan gas tersimpan.';
        return $request->expectsJson() ? response()->json(['success' => true, 'message' => $message, 'status' => $order->status]) : back()->with('success', $message);
    }

    public function requestCancellation(Request $request, int $id)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        DB::transaction(function () use ($request, $id) {
            $order = GasOrder::where('user_id', $request->user()->id)->lockForUpdate()->findOrFail($id);
            abort_unless($order->canBeCancelled(), 409, 'Pesanan tidak dapat dibatalkan atau permintaan sudah dikirim.');
            $order->update(['cancellation_reason_user' => $request->reason, 'cancellation_requested_at' => now(), 'cancellation_status' => 'pending']);
            AdminNotification::create(['type' => 'gas_order', 'reference_id' => $id, 'region_id' => $order->gas->region_id,
                'title' => 'Permintaan Pembatalan Gas', 'message' => 'Periksa pembatalan pesanan '.$order->order_number.'.', 'is_read' => false]);
        });
        return response()->json(['success' => true, 'message' => 'Permintaan pembatalan dikirim ke pengelola.']);
    }

    public function cancelUnpaid(Request $request, int $id)
    {
        DB::transaction(function () use ($request, $id) {
            $order = GasOrder::where('user_id', $request->user()->id)->lockForUpdate()->findOrFail($id);
            $paid = WalletTransaction::where('reference_type', 'gas')->where('reference_id', $id)->where('status', 'verified')->exists();
            abort_unless($order->canBeCancelled() && !$paid && !$order->proof_of_payment, 409, 'Ajukan pembatalan melalui Riwayat Pesanan agar pembayaran diperiksa pengelola.');
            $order->update(['status' => 'cancelled']);
            WalletTransaction::where('reference_type', 'gas')->where('reference_id', $id)->where('status', 'pending')->update(['status' => 'rejected', 'notes' => 'Dibatalkan pemesan sebelum pembayaran']);
            Notification::create(['user_id' => $order->user_id, 'type' => 'status_berubah', 'title' => 'Pesanan Gas Dibatalkan',
                'message' => 'Pesanan '.$order->order_number.' dibatalkan atas permintaan Anda.', 'link' => NotificationService::orderLink($order, 'gas')]);
        });
        return response()->json(['success' => true, 'message' => 'Pesanan dibatalkan.']);
    }
}
