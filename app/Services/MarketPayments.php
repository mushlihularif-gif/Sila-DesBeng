<?php
namespace App\Services;

use App\Models\{PasarOrder, WalletTransaction};
use Illuminate\Support\Facades\DB;

class MarketPayments
{
    public function record(PasarOrder $order, string $method, ?string $proof): void
    {
        if (!$order->store_id) {
            WalletTransaction::catatPemasukan($order->region_id,'pasar',$order->id,$order->grand_total,$method,$proof);
            return;
        }
        DB::table('market_payments')->insert(['pasar_order_id'=>$order->id,'store_id'=>$order->store_id,
            'amount'=>$order->grand_total,'method'=>$method,'status'=>'pending','created_at'=>now(),'updated_at'=>now()]);
    }

    public function verify(PasarOrder $order, int $actor): void
    {
        if (!$order->store_id) { WalletTransaction::tandaiPembayaranManualDiterima('pasar',$order->id,$actor); return; }
        DB::table('market_payments')->where('pasar_order_id',$order->id)->where('status','pending')
            ->update(['status'=>'verified','handled_by'=>$actor,'updated_at'=>now()]);
    }

    public function cancel(PasarOrder $order, string $reason): void
    {
        if (!$order->store_id) { WalletTransaction::batalkanDanRefund('pasar',$order->id,$reason); return; }
        // Pembayaran langsung kepada penjual: persetujuan bukan bukti uang telah kembali.
        DB::table('market_payments')->where('pasar_order_id',$order->id)->where('status','verified')
            ->update(['status'=>'refund_pending','note'=>$reason,'updated_at'=>now()]);
        DB::table('market_payments')->where('pasar_order_id',$order->id)->where('status','pending')
            ->update(['status'=>'cancelled','note'=>$reason,'updated_at'=>now()]);
    }
}
