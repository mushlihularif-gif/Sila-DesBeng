<?php
namespace App\Services;

use App\Models\{MarketStore, PasarOrder, Notification, User};
use Illuminate\Support\Facades\DB;

class MarketStoreOrders
{
    public function requestCancellation(User $buyer,int $id,string $reason): void
    {
        DB::transaction(function()use($buyer,$id,$reason){
            $order=PasarOrder::where('user_id',$buyer->id)->lockForUpdate()->findOrFail($id);
            abort_unless(in_array($order->status,['pending','paid','confirmed'],true)&&$order->cancellation_status!=='pending',409,'Pesanan tidak dapat diajukan pembatalan atau sedang menunggu tanggapan.');
            abort_if($order->complaint()->where('status','pending')->exists(),409,'Komplain pesanan masih ditangani.');
            $order->cancellation_reason_user=$reason;$order->cancellation_status='pending';$order->cancellation_requested_at=now();$order->save();
            if($order->store_id) Notification::create(['user_id'=>$order->store->user_id,'type'=>'status_berubah','title'=>'Permintaan Pembatalan Pesanan',
                'message'=>'Pembeli meminta pembatalan '.$order->order_number.'. '.$reason,'link'=>route('market.seller.order',$id),'is_read'=>false]);
            else \App\Models\AdminNotification::create(['region_id'=>$order->region_id,'type'=>'pasar_order','reference_id'=>$id,
                'title'=>'Permintaan Pembatalan Pesanan','message'=>'Pembeli meminta pembatalan '.$order->order_number.'. '.$reason,'is_read'=>false]);
        });
    }

    public function change(User $owner, int $id, array $data): PasarOrder
    {
        return DB::transaction(function () use ($owner,$id,$data) {
            $store=MarketStore::where('user_id',$owner->id)->firstOrFail();
            $order=PasarOrder::withTrashed()->where('store_id',$store->id)->lockForUpdate()->findOrFail($id);
            $action=$data['action']; $note=$data['reason'] ?? null;
            abort_if($order->complaint()->where('status','pending')->exists(),409,'Komplain sedang ditangani admin desa.');
            if ($action === 'refund_done') {
                abort_unless(filled($note),422,'Tuliskan tanggal dan referensi pengembalian dana.');
                $changed=DB::table('market_payments')->where('pasar_order_id',$order->id)->where('status','refund_pending')
                    ->update(['status'=>'refunded','note'=>$note,'handled_by'=>$owner->id,'updated_at'=>now()]);
                abort_unless($changed,409,'Pengembalian dana tidak tersedia atau sudah dicatat.');
            } elseif ($action === 'payment_received') {
                abort_if(in_array($order->status,['cancelled','rejected'],true),409,'Pesanan telah dibatalkan.');
                if ($order->paymentIsVerified()) return $order;
                abort_unless(DB::table('market_payments')->where('pasar_order_id',$order->id)->where('status','pending')->exists(),409);
                app(MarketPayments::class)->verify($order,$owner->id);
                if ($order->status === 'pending' && !$order->isCashPayment()) $order->status='paid';
            } elseif ($action === 'reject_cancellation') {
                abort_unless($order->cancellation_status === 'pending' && in_array($order->status,['pending','paid','confirmed'],true) && filled($note),409,'Permintaan pembatalan sudah berubah atau alasan belum diisi.');
                $order->cancellation_status='rejected'; $order->admin_cancellation_response=$note;
            } else {
                $next=match($action){'accept'=>'confirmed','send'=>'in_delivery','arrive'=>'delivered','cancel'=>'cancelled',default=>null};
                $allowed=[
                    'pending'=>$order->isCashPayment() ? ['confirmed','cancelled'] : ['cancelled'],
                    'paid'=>['confirmed','cancelled'],'confirmed'=>['in_delivery','delivered','cancelled'],'in_delivery'=>['delivered'],
                ];
                abort_unless($next && in_array($next,$allowed[$order->status] ?? [],true),409,'Status pesanan sudah berubah atau tindakan belum tersedia.');
                if ($next === 'in_delivery') abort_unless($order->delivery_method === 'antar',422,'Pesanan ini diambil di toko.');
                if ($next === 'cancelled') {
                    abort_unless(filled($note),422,'Tuliskan alasan pembatalan.');
                    foreach($order->items()->orderBy('pasar_produk_id')->get() as $item) {
                        $product=$item->produk()->lockForUpdate()->first();
                        if ($product) $product->increaseStock($item->quantity);
                    }
                    app(MarketPayments::class)->cancel($order,$note);
                    $order->admin_cancellation_response=$note;
                    if ($order->cancellation_status === 'pending') $order->cancellation_status='approved';
                }
                if ($next === 'confirmed') $order->confirmed_at=now();
                if ($next === 'delivered') $order->delivered_at=now();
                $order->status=$next;
            }
            $order->handled_by=$owner->id; $order->save();
            $labels=['accept'=>'diterima penjual','send'=>'sedang diantar','arrive'=>'telah diserahkan kepada pembeli','cancel'=>'dibatalkan',
                'payment_received'=>'pembayarannya sudah diterima','refund_done'=>'pengembalian dananya dicatat penjual','reject_cancellation'=>'permintaan pembatalannya ditolak'];
            Notification::create(['user_id'=>$order->user_id,'type'=>'status_berubah','title'=>'Pembaruan Pesanan',
                'message'=>'Pesanan '.$order->order_number.' '.$labels[$action].($note ? '. '.$note : '.'),
                'link'=>NotificationService::orderLink($order,'pasar'),'is_read'=>false]);
            DB::table('market_store_events')->insert(['store_id'=>$store->id,'user_id'=>$owner->id,'action'=>$action,
                'note'=>$order->order_number.($note ? ': '.$note : ''),'created_at'=>now(),'updated_at'=>now()]);
            return $order;
        },3);
    }
}
