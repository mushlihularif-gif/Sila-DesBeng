@extends('layouts.user')
@section('title','Pesanan '.$order->order_number)
@section('page')
<main class="market">@include('market.ui')<a href="{{ route('market.seller.home') }}#pesanan">← Pesanan Toko</a><h1>{{ $order->order_number }}</h1><section class="mk-card"><h2>Rincian Pesanan</h2><p>Pembeli: {{ $order->full_name }} · {{ $order->phone }}</p><p>{{ $order->delivery_method==='antar' ? 'Diantar ke: '.$order->delivery_address : 'Diambil di toko' }}</p><p>Catatan: {{ $order->notes ?: 'Tidak ada' }}</p>
@foreach($order->items as $item)<p>{{ $item->product_name }} × {{ $item->quantity }} — Rp{{ number_format($item->subtotal,0,',','.') }}</p>@endforeach<p>Ongkir: Rp{{ number_format($order->shipping_cost,0,',','.') }}</p><strong>Total: Rp{{ number_format($order->grand_total,0,',','.') }}</strong>
<p>Status: {{ ['pending'=>'Menunggu tanggapan','paid'=>'Dibayar','confirmed'=>'Sedang diproses','in_delivery'=>'Sedang diantar','delivered'=>'Sudah diserahkan','completed'=>'Selesai','cancelled'=>'Dibatalkan','rejected'=>'Ditolak'][$order->status] ?? $order->status }}</p>
<p>Pembayaran {{ $order->payment_method }}: {{ ['pending'=>'Belum dikonfirmasi diterima','verified'=>'Sudah diterima','refund_pending'=>'Perlu dikembalikan kepada pembeli','refunded'=>'Pengembalian dicatat penjual','cancelled'=>'Dibatalkan'][$payment->status ?? 'pending'] ?? 'Periksa pembayaran' }}</p>
@if($order->proof_of_payment)<a class="mk-btn mk-secondary" href="{{ Storage::url($order->proof_of_payment) }}" target="_blank" rel="noopener">Lihat Bukti Pembayaran</a>@endif
<a class="mk-btn mk-secondary" href="{{ route('market.chat',[$store->id,$order->user_id]) }}">Chat Pembeli</a>
@if($order->complaint?->status==='pending')<div class="mk-alert">Komplain sedang ditangani admin desa. {{ $order->complaint->reason }} — {{ $order->complaint->description }}</div>@else
@php
 $actions=[];
 if(($payment->status ?? '')==='pending' && !in_array($order->status,['cancelled','rejected']))$actions['payment_received']='Catat Pembayaran Sudah Diterima';
 if(($order->status==='pending' && $order->isCashPayment()) || $order->status==='paid')$actions['accept']='Terima dan Proses Pesanan';
 if($order->status==='confirmed' && $order->delivery_method==='antar')$actions['send']='Mulai Antar Pesanan';
 if(in_array($order->status,['confirmed','in_delivery']))$actions['arrive']=$order->delivery_method==='antar'?'Barang Sudah Diterima Pembeli':'Barang Sudah Diambil Pembeli';
@endphp
<p class="mk-muted">Konfirmasi pembayaran hanya setelah uang benar-benar diterima. Tandai barang diserahkan hanya setelah pembeli menerimanya.</p>
@foreach($actions as $action=>$label)<form method="POST" action="{{ route('market.seller.order.change',$order->id) }}" style="display:inline">@csrf<input type="hidden" name="action" value="{{ $action }}"><button class="mk-btn">{{ $label }}</button></form>@endforeach
@if(in_array($order->status,['pending','paid','confirmed']))<details><summary>Tolak / batalkan pesanan</summary>
@if($order->cancellation_status==='pending')<p>Pembeli meminta pembatalan: {{ $order->cancellation_reason_user }}</p>@endif<form method="POST" action="{{ route('market.seller.order.change',$order->id) }}">@csrf<label for="reason">Alasan atau tanggapan untuk pembeli</label><textarea id="reason" name="reason" required maxlength="1000"></textarea><button name="action" value="cancel" class="mk-btn">Batalkan Pesanan</button>
@if($order->cancellation_status==='pending')<button name="action" value="reject_cancellation" class="mk-btn mk-secondary">Tolak Permintaan Pembatalan</button>@endif</form><p class="mk-muted">Pembayaran yang telah diterima harus dikembalikan oleh penjual, bukan otomatis ditransfer oleh sistem.</p></details>@endif
@if(($payment->status ?? '')==='refund_pending')<form method="POST" action="{{ route('market.seller.order.change',$order->id) }}">@csrf<input type="hidden" name="action" value="refund_done"><label for="refund_note">Tanggal dan referensi pengembalian uang</label><textarea name="reason" id="refund_note" required maxlength="1000" placeholder="Tuliskan setelah uang benar-benar dikembalikan."></textarea><button class="mk-btn">Catat Dana Sudah Dikembalikan</button></form>@endif
@endif</section></main>@endsection
