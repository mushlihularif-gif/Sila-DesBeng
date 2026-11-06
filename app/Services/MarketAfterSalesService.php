<?php

namespace App\Services;

use App\Models\{PasarOrder, PasarComplaint, User, WalletTransaction};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};

class MarketAfterSalesService
{
    public function canReview(?User $user, int $product): bool
    {
        return $user && PasarOrder::where('user_id', $user->id)->where('status', 'completed')
            ->whereHas('items', fn ($q) => $q->where('pasar_produk_id', $product))->exists()
            && !\App\Models\PasarReview::where('user_id', $user->id)->where('pasar_produk_id', $product)->exists();
    }

    public function review(User $user, int $product, array $data): \App\Models\PasarReview
    {
        return DB::transaction(function () use ($user, $product, $data) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_unless($this->canReview($user, $product), 409, 'Ulasan tersedia setelah pesanan selesai, satu kali untuk setiap produk.');
            return \App\Models\PasarReview::create($data + ['user_id' => $user->id, 'pasar_produk_id' => $product]);
        });
    }

    public function submit(User $user, int $id, Request $request): PasarComplaint
    {
        $data = $request->validate([
            'reason' => 'required|string|max:255', 'solution_requested' => 'required|in:refund,replacement',
            'description' => 'nullable|string|max:1000', 'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100', 'bank_account_name' => 'nullable|string|max:100',
            'evidence_1' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'evidence_2' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'evidence_3' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'evidence_4' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'evidence_5' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'evidence_video' => 'nullable|mimes:mp4,mov,ogg,qt|max:30720',
        ]);
        $paths = [];
        try {
            return DB::transaction(function () use ($user, $id, $request, $data, &$paths) {
                $order = PasarOrder::where('user_id', $user->id)->lockForUpdate()->findOrFail($id);
                abort_unless(in_array($order->status, ['confirmed', 'in_delivery', 'delivered'], true), 409,
                    'Komplain hanya tersedia untuk pesanan yang sedang diproses atau diterima.');
                abort_if($order->delivered_at && now()->gte($order->delivered_at->copy()->addHours(2)), 409,
                    'Batas waktu komplain telah berakhir.');
                abort_if($order->complaint()->exists(), 409, 'Komplain untuk pesanan ini sudah diajukan.');
                foreach (['evidence_1', 'evidence_2', 'evidence_3', 'evidence_4', 'evidence_5', 'evidence_video'] as $field) {
                    unset($data[$field]);
                    if ($request->hasFile($field)) $data[$field] = $paths[] = $request->file($field)->store('pasar_complaints', 'public');
                }
                $complaint = PasarComplaint::create($data + [
                    'pasar_order_id' => $order->id, 'region_id' => $order->region_id, 'user_id' => $user->id, 'status' => 'pending',
                ]);
                if($order->store_id) foreach(User::where('role','admin_desa')->where('region_id',$order->region_id)->get() as $admin) {
                    \App\Models\Notification::create(['user_id'=>$admin->id,'type'=>'status_berubah','title'=>'Komplain Transaksi Toko Warga',
                        'message'=>'Pesanan '.$order->order_number.' perlu ditinjau.','link'=>route('market.review.order',$order->id),'is_read'=>false]);
                }
                return $complaint;
            });
        } catch (\Throwable $e) {
            foreach ($paths as $path) Storage::disk('public')->delete($path);
            throw $e;
        }
    }

    public function decide(User $admin, int $id, array $data): void
    {
        abort_unless(in_array($admin->role, ['admin_desa', 'super_admin'], true) && $admin->region_id, 403);
        DB::transaction(function () use ($admin, $id, $data) {
            $reference = PasarComplaint::where('region_id', $admin->region_id)->findOrFail($id);
            // Semua aksi memakai urutan kunci yang sama: pesanan, kemudian komplain.
            $order = PasarOrder::where('region_id', $admin->region_id)->lockForUpdate()->findOrFail($reference->pasar_order_id);
            $complaint = PasarComplaint::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless($complaint->status === 'pending', 409, 'Komplain sudah ditangani.');
            abort_if(in_array($order->status, ['cancelled', 'rejected', 'completed'], true), 409, 'Status pesanan sudah berubah.');
            $complaint->update($data + ['handled_by' => $admin->id, 'resolved_at' => now()]);
            if ($data['status'] === 'approved_refund') {
                app(MarketPayments::class)->cancel($order, $data['admin_response']);
                $order->status = 'cancelled';
                $order->admin_cancellation_response = $data['admin_response'];
            } elseif ($data['status'] === 'approved_replacement') {
                $order->status = 'confirmed';
                $order->delivered_at = null;
            }
            $order->save();
            if($order->store_id) foreach([$order->user_id,$order->store->user_id] as $recipient) {
                \App\Models\Notification::create(['user_id'=>$recipient,'type'=>'status_berubah','title'=>'Hasil Peninjauan Komplain',
                    'message'=>'Pesanan '.$order->order_number.': '.$data['admin_response'],
                    'link'=>$recipient===$order->user_id ? NotificationService::orderLink($order,'pasar') : route('market.seller.order',$order->id),'is_read'=>false]);
            }
        });
    }
}
