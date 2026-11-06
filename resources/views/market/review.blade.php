@extends('admin.layouts.admin')
@section('title','Pendaftaran Toko Warga')
@section('content')
<div class="container-xxl py-4"><h3>Toko Warga di Wilayah Anda</h3><p>Periksa identitas pemilik, lokasi dan informasi toko. Persetujuan hanya membuka fitur jualan; akun warga tidak menjadi admin.</p>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@forelse($stores as $store)<section class="card p-4 mb-3"><h4>{{ $store->name }}</h4><p>Pemilik: {{ $store->owner->name }} · {{ $store->category }}</p><p>{{ $store->address }}</p><p>{{ $store->description }}</p><p>Status: {{ ['pending'=>'Menunggu pemeriksaan','approved'=>'Disetujui','rejected'=>'Ditolak','suspended'=>'Dinonaktifkan'][$store->status] ?? $store->status }}</p>
@if($store->review_reason)<p>Alasan terakhir: {{ $store->review_reason }}</p>@endif
@if(in_array($store->status,['pending','approved']))<form method="POST" action="{{ route('market.review',$store->id) }}">@csrf<label class="form-label" for="reason-{{ $store->id }}">Alasan (wajib jika menolak atau menonaktifkan)</label><textarea class="form-control mb-3" id="reason-{{ $store->id }}" name="reason" maxlength="1000"></textarea>
@if($store->status==='pending')<button class="btn btn-primary" name="decision" value="approved">Setujui Toko</button><button class="btn btn-outline-danger" name="decision" value="rejected">Tolak dengan Alasan</button>@else<button class="btn btn-outline-danger" name="decision" value="suspended">Nonaktifkan dengan Alasan</button>@endif</form>@endif
</section>@empty<div class="alert alert-info">Belum ada pendaftaran toko warga.</div>@endforelse{{ $stores->links() }}
@if(isset($complaints))<h4 class="mt-4">Komplain Transaksi Toko Warga</h4>
@foreach($complaints as $complaint)<div class="card p-3 mb-2"><p>{{ $complaint->order?->order_number }} — {{ $complaint->reason }}</p><a class="btn btn-outline-primary" href="{{ route('market.review.order',$complaint->pasar_order_id) }}">Periksa Komplain</a></div>@endforeach{{ $complaints->links() }}@endif
</div>@endsection
