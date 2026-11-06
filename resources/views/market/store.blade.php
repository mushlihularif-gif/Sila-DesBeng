@extends('layouts.user')
@section('title',$store->name.' - Pasar Daerah')
@section('page')
<main class="market">@include('market.ui')<a href="{{ route('pasar.index') }}">← Pasar Daerah</a>
<section class="mk-card" style="padding:0;overflow:hidden">
    @if($store->cover)<img class="mk-cover" src="{{ Storage::url($store->cover) }}" style="object-position:center {{ $store->cover_position }}%" alt="Sampul {{ $store->name }}">@else<div class="mk-cover" aria-label="Sampul bawaan SegiDaerah"></div>@endif
    <div class="mk-profile"><img class="mk-avatar" src="{{ $store->avatar ? Storage::url($store->avatar) : asset('Admin/img/illustrations/logodomain.webp') }}" alt="Foto {{ $store->name }}" onerror="this.hidden=true">
    <h1>{{ $store->name }}</h1><p>Dikelola oleh {{ $store->owner->name }}</p><p class="mk-muted">{{ $store->region->name }} · {{ $store->is_open ? 'Menerima pesanan' : 'Toko sedang tutup sementara' }}</p>
    <a class="mk-btn" href="{{ route('market.chat',$store->id) }}">Chat Penjual</a>
    @if($store->latitude!==null && $store->longitude!==null)<a class="mk-btn mk-secondary" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($store->latitude.','.$store->longitude) }}" target="_blank" rel="noopener noreferrer">Lihat Lokasi</a>@endif
    <nav class="mk-tabs"><a href="#produk">Produk</a><a href="#tentang">Tentang Toko</a><a href="#ulasan">Ulasan Pembeli</a></nav></div>
</section>
<section id="produk"><h2>Produk Toko</h2><div class="mk-products">@forelse($products as $product)<article class="mk-card"><a href="{{ route('pasar.show',$product->id) }}">
@if($product->foto)<img class="mk-product-img" src="{{ Storage::url($product->foto) }}" alt="{{ $product->nama_produk }}">@endif<h3>{{ $product->nama_produk }}</h3></a><p>Rp{{ number_format($product->harga,0,',','.') }} / {{ $product->satuan }}</p><p>{{ $product->status==='habis' ? 'Stok habis' : 'Stok '.$product->stok }}</p><a class="mk-btn mk-secondary" href="{{ route('pasar.show',$product->id) }}">Lihat Produk</a></article>@empty<p>Penjual belum memasang produk.</p>@endforelse</div>{{ $products->links() }}</section>
<section class="mk-card" id="tentang"><h2>Tentang Toko</h2><p style="white-space:pre-wrap">{{ $store->description }}</p><h3>Alamat</h3><p>{{ $store->address }}, {{ $store->region->name }}</p><p class="mk-muted">Pilihan pengiriman dan ongkir ditampilkan setelah Anda memilih alamat saat checkout.</p></section>
<section class="mk-card" id="ulasan"><h2>Ulasan Pembeli</h2>@forelse($reviews as $review)<article class="mk-card"><strong>{{ $review->user?->name ?? 'Pembeli' }} · {{ $review->rating }}/5</strong><p>{{ $review->comment }}</p></article>@empty<p>Belum ada ulasan pembeli.</p>@endforelse{{ $reviews->links() }}</section>
</main>@endsection
