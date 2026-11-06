@extends('layouts.user')
@section('title',$produk->nama_produk.' - '.$store->name)
@section('page')
<main class="market">@include('market.ui')<a href="{{ route('market.store',$store->id) }}">← {{ $store->name }}</a>
<section class="mk-card mk-grid"><div>
<div data-market-gallery aria-label="Foto produk">
@php
    $photos=collect(['foto','foto_2','foto_3'])->map(fn($field)=>$produk->$field)->filter()->values();
@endphp
@foreach($photos as $photo)<a data-gallery-slide @if(!$loop->first) hidden @endif href="{{ Storage::url($photo) }}" target="_blank" rel="noopener"><img class="mk-product-img" style="height:320px;object-fit:contain" src="{{ Storage::url($photo) }}" alt="{{ $produk->nama_produk }} — foto {{ $loop->iteration }}"></a>@endforeach
@if($photos->count()>1)<div class="mk-actions"><button type="button" class="mk-btn mk-secondary" data-gallery-prev aria-label="Foto sebelumnya">← Sebelumnya</button><span data-gallery-count aria-live="polite">1 / {{ $photos->count() }}</span><button type="button" class="mk-btn mk-secondary" data-gallery-next aria-label="Foto berikutnya">Berikutnya →</button></div>@endif
</div>
</div><div><h1>{{ $produk->nama_produk }}</h1><h2>Rp{{ number_format($produk->harga,0,',','.') }} / {{ $produk->satuan }}</h2><p style="white-space:pre-wrap">{{ $produk->deskripsi }}</p><p>Stok: {{ $produk->stok }}</p><p>Dijual oleh <a href="{{ route('market.store',$store->id) }}">{{ $store->name }}</a></p>
@if(!$store->is_open)<div class="mk-alert">Toko sedang tutup sementara. Anda tetap bisa melihat produk.</div>@elseif($produk->status!=='tersedia' || $produk->stok<1)<div class="mk-alert">Produk sedang habis.</div>@else
@auth<form action="{{ route('pasar.cart.add') }}" method="POST" data-market-cart-form>@csrf<input type="hidden" name="pasar_produk_id" value="{{ $produk->id }}"><label for="quantity">Jumlah yang dibeli</label><input type="number" id="quantity" name="quantity" min="1" max="{{ $produk->stok }}" value="1" required><button class="mk-btn">Tambah ke Keranjang</button><p role="status"></p></form>@else<a class="mk-btn" href="{{ route('pasar.cart') }}">Masuk untuk Berbelanja</a>@endauth
@endif
<a class="mk-btn mk-secondary" href="{{ route('pasar.cart') }}">Buka Keranjang</a><a class="mk-btn mk-secondary" href="{{ route('market.chat',$store->id) }}">Chat Penjual</a></div></section>
<section class="mk-card"><h2>Ulasan Pembeli</h2>@forelse($reviews as $review)<p><strong>{{ $review->user?->name }} · {{ $review->rating }}/5</strong><br>{{ $review->comment }}</p>@empty<p>Belum ada ulasan.</p>@endforelse{{ $reviews->links() }}
@if($canReview)<form action="{{ route('pasar.review.store',$produk->id) }}" method="POST">@csrf<label for="rating">Nilai produk</label><select name="rating" id="rating">
@foreach([5,4,3,2,1] as $rating)<option>{{ $rating }}</option>@endforeach</select><label for="comment">Pengalaman Anda</label><textarea id="comment" name="comment" maxlength="1000"></textarea><button class="mk-btn">Kirim Ulasan</button></form>@else<p class="mk-muted">Ulasan tersedia setelah pesanan selesai, satu kali per produk.</p>@endif
</section></main>@endsection
@push('scripts')<script src="{{ asset('js/market-store.js') }}" defer></script>@endpush
