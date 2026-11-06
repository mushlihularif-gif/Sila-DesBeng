@extends('layouts.user')
@section('title','Keranjang - Pasar Daerah')
@section('page')
<main class="market">@include('market.ui')<h1>Keranjang Belanja</h1><p>Pesan dari satu toko terlebih dahulu. Barang toko lain tetap tersimpan.</p><a class="mk-btn mk-secondary" href="{{ route('pasar.index') }}">Lanjut Belanja</a>
@forelse($carts->groupBy(fn($cart)=>$cart->produk->storeKey()) as $key=>$items)
@php
 $product=$items->first()->produk; $store=$product->store; $storeName=$store?->name ?? ('Pasar '.$product->region->name);
 $available=(!$store || ($store->status==='approved' && $store->is_open)) && $items->every(fn($item)=>$item->produk->status==='tersedia' && $item->produk->stok >= $item->quantity);
@endphp
<section class="mk-card"><h2>{{ $storeName }}</h2>
@foreach($items as $item)<div class="mk-card"><h3>{{ $item->produk->nama_produk }}</h3><p>Rp{{ number_format($item->produk->harga,0,',','.') }} × {{ $item->quantity }}</p><div class="mk-actions"><form action="{{ route('pasar.cart.update') }}" method="POST" data-cart-update>@csrf @method('PATCH')<input type="hidden" name="cart_id" value="{{ $item->id }}"><label for="cart-{{ $item->id }}">Jumlah</label><input id="cart-{{ $item->id }}" name="quantity" type="number" min="1" max="{{ max(1,$item->produk->stok) }}" value="{{ $item->quantity }}" required><button class="mk-btn mk-secondary">Ubah Jumlah</button></form><form action="{{ route('pasar.cart.remove',$item->id) }}" method="POST" data-cart-update>@csrf @method('DELETE')<button class="mk-btn mk-secondary">Hapus dari Keranjang</button></form></div></div>@endforeach
<strong>Total produk: Rp{{ number_format($items->sum(fn($item)=>$item->produk->harga*$item->quantity),0,',','.') }}</strong><p class="mk-muted">Ongkir dihitung setelah alamat tujuan dipilih.</p>
@if($available)<a class="mk-btn" href="{{ route('pasar.checkout',['store_key'=>$key]) }}">Pesan dari {{ $storeName }}</a>@else<p class="mk-alert">Toko sedang tutup, produk tidak tersedia, atau jumlah melebihi stok. Periksa jumlah sebelum memesan.</p>@endif
</section>@empty<div class="mk-card">Keranjang masih kosong. Pilih produk dari Pasar Daerah untuk mulai berbelanja.</div>@endforelse
<p id="cart-status" role="status"></p></main>@endsection
@push('scripts')<script>
document.querySelectorAll('[data-cart-update]').forEach(form=>form.addEventListener('submit',async event=>{event.preventDefault();event.submitter.disabled=true;try{const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json'}});const data=await response.json();if(!response.ok||data.success===false)throw new Error(data.message||'Keranjang belum berhasil diperbarui.');location.reload();}catch(error){document.getElementById('cart-status').textContent=error.message;event.submitter.disabled=false;}}));
</script>@endpush
