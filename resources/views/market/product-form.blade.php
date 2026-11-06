@extends('layouts.user')
@section('title','Kelola Produk - '.$store->name)
@section('page')
<main class="market">@include('market.ui')<a href="{{ route('market.seller.home') }}#produk">← Toko Saya</a><section class="mk-card"><h1>{{ $product->exists ? 'Ubah Produk / Stok' : 'Tambah Produk Pertama atau Berikutnya' }}</h1>
<form method="POST" action="{{ route('market.seller.product',$product->exists ? $product->id : null) }}" enctype="multipart/form-data">@csrf
<label for="nama_produk">Nama produk</label><input id="nama_produk" name="nama_produk" required maxlength="150" value="{{ old('nama_produk',$product->nama_produk) }}" placeholder="Contoh: Kerupuk Pedas 100 gram">
<label for="deskripsi">Ceritakan produk Anda</label><textarea id="deskripsi" name="deskripsi" required maxlength="4000" placeholder="Rasa, ukuran, isi kemasan, dan informasi penting untuk pembeli">{{ old('deskripsi',$product->deskripsi) }}</textarea>
<div class="mk-grid"><div><label for="harga">Harga (Rp)</label><input type="number" id="harga" name="harga" min="1" max="100000000" step="1" required value="{{ old('harga',$product->harga ? (int)$product->harga : '') }}"></div><div><label for="satuan">Dijual per</label><input id="satuan" name="satuan" required maxlength="30" placeholder="bungkus / kg / botol" value="{{ old('satuan',$product->satuan) }}"></div><div><label for="stok">Jumlah stok tersedia</label><input id="stok" type="number" name="stok" min="0" max="1000000" required value="{{ old('stok',$product->stok ?? 0) }}"></div><div><label for="status">Tampilkan produk sebagai</label><select id="status" name="status">
@foreach(['tersedia'=>'Tersedia','habis'=>'Stok habis','nonaktif'=>'Nonaktif — sembunyikan produk'] as $key=>$label)<option value="{{ $key }}" @selected(old('status',$product->status)===$key)>{{ $label }}</option>@endforeach</select></div></div>
<label for="kategori">Kategori</label><select id="kategori" name="kategori">
@foreach(['Hasil Tani & Bumi','Pangan & Olahan','Material & Bangunan','Kerajinan & Kesenian','Lainnya'] as $category)<option @selected(old('kategori',$product->kategori)===$category)>{{ $category }}</option>@endforeach</select>
@foreach(['foto'=>'Foto utama','foto_2'=>'Foto tambahan (opsional)','foto_3'=>'Foto tambahan lainnya (opsional)'] as $key=>$label)<label for="{{ $key }}">{{ $label }}</label>
@if($product->$key)<img src="{{ Storage::url($product->$key) }}" alt="{{ $label }}" style="width:100px">@endif<input id="{{ $key }}" type="file" name="{{ $key }}" accept="image/jpeg,image/png,image/webp" @required(!$product->exists && $key==='foto')>@endforeach
<p class="mk-muted">Foto maksimal 3 MB per gambar. Produk yang pernah terjual tetap disimpan untuk menjaga riwayat pesanan.</p><button class="mk-btn">Simpan Produk</button>
</form></section></main>@endsection
