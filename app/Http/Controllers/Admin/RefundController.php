<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Support\FinanceRecords;
use App\Support\FinancialProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RefundController extends Controller
{
    private function records()
    {
        FinanceRecords::authorize(true);

        return WalletTransaction::where('region_id', auth()->user()->region_id)->where('source', 'manual')->whereNotNull('refund_status');
    }

    public function index()
    {
        $refunds = $this->records()->orderByDesc('id')->paginate(20);
        foreach ($refunds as $entry) {
            [$userId] = WalletTransaction::pemilikPesanan($entry->reference_type, $entry->reference_id);
            $entry->recipient_name = User::find($userId)?->name ?? 'Pemilik perlu diperiksa';
        }

        return view('admin.keuangan.refunds', ['refunds' => $refunds,
            'pendingTotal' => (clone $this->records())->where('refund_status', 'pending')->sum('amount')]);
    }

    public function complete(Request $request, int $id)
    {
        $this->records()->findOrFail($id);
        $request->validate(['proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120', 'confirmed' => 'accepted']);
        $path = null;
        try {
            DB::transaction(function () use ($request, $id, &$path) {
                $entry = $this->records()->lockForUpdate()->findOrFail($id);
                abort_unless($entry->refund_status === 'pending' && $entry->status === 'rejected', 409, 'Pengembalian ini sudah dicatat atau statusnya berubah.');
                [$userId] = WalletTransaction::pemilikPesanan($entry->reference_type, $entry->reference_id);
                abort_unless($userId, 409, 'Pemilik pesanan tidak ditemukan.');
                $path = $request->file('proof')->store('refund-evidence', 'private');
                abort_unless($path, 500, 'Bukti belum berhasil disimpan.');
                $entry->update(['refund_status' => 'completed', 'refund_proof' => $path, 'refunded_at' => now(), 'refunded_by' => auth()->id()]);
                ActivityLog::create(['user_id' => auth()->id(), 'action' => 'manual_refund_completed',
                    'description' => 'Pengembalian manual ledger #'.$id.' sebesar '.$entry->amount.'; bukti '.$path, 'ip_address' => $request->ip()]);
                Notification::create(['user_id' => $userId, 'type' => 'status_berubah', 'title' => 'Pengembalian Dana Dicatat',
                    'message' => 'Pengelola mencatat pengembalian Rp '.number_format($entry->amount, 0, ',', '.').' untuk pesanan #'.$entry->reference_id.'. Periksa penerimaan dana dan hubungi pengelola bila belum diterima.', 'is_read' => false]);
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('private')->delete($path);
            }
            throw $e;
        }

        return back()->with('success', 'Pengembalian dana dan bukti tersimpan.');
    }

    public function proof(int $id)
    {
        return FinancialProof::response($this->records()->findOrFail($id)->refund_proof);
    }
}
