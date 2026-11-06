@extends('admin.layouts.admin')
@section('title','Komplain Transaksi Toko Warga')
@section('content')
<div class="container-xxl py-4"><a href="{{ route('market.review.index') }}">← Toko Warga</a><div class="card p-4"><h3>{{ $order->order_number }} — {{ $order->store->name }}</h3><p>Pembeli: {{ $order->full_name }}</p><p>{{ $order->complaint->reason }} — {{ $order->complaint->description }}</p><p>Permintaan pembeli: {{ $order->complaint->solution_requested==='refund'?'Pengembalian dana':'Penggantian barang' }}</p>
@foreach($order->items as $item)<p>{{ $item->product_name }} × {{ $item->quantity }}</p>@endforeach<p>Total Rp{{ number_format($order->grand_total,0,',','.') }}</p>
@foreach(['evidence_1','evidence_2','evidence_3','evidence_4','evidence_5','evidence_video'] as $field)@if($order->complaint->$field)<a class="btn btn-outline-primary m-1" target="_blank" rel="noopener" href="{{ Storage::url($order->complaint->$field) }}">Buka Bukti {{ $loop->iteration }}</a>@endif
@endforeach
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($order->complaint->status==='pending')<form action="{{ route('market.review.complaint',$order->complaint->id) }}" method="POST">@csrf<label class="form-label" for="response">Hasil pemeriksaan dan alasan</label><textarea class="form-control" id="response" name="admin_response" required maxlength="1000"></textarea><button class="btn btn-primary m-1" name="status" value="approved_replacement">Setujui Penggantian Barang</button><button class="btn btn-primary m-1" name="status" value="approved_refund">Setujui Pengembalian Dana</button><button class="btn btn-outline-danger m-1" name="status" value="rejected">Tolak dengan Alasan</button></form><p class="text-muted">Persetujuan pengembalian dana tidak melakukan transfer bank. Penjual wajib mengembalikan uang dan mencatat pelaksanaannya.</p>@else<p>Komplain sudah ditangani: {{ $order->complaint->admin_response }}</p>@endif
</div></div>@endsection
