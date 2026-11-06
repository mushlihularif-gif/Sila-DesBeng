@extends('layouts.user')
@section('title','Toko Saya - SegiDaerah')
@section('page')
<main class="market">
@include('market.ui')
<a href="{{ route('pasar.index') }}">← Kembali ke Pasar Daerah</a>
<h1>{{ $store ? 'Toko Saya' : 'Mulai Jualan' }}</h1>
<p class="mk-muted">Jual hasil kebun, makanan, kerajinan, dan produk Anda. Akun Anda tetap bisa digunakan untuk layanan warga.</p>
@if($owner->verification_status !== 'verified')
    <div class="mk-alert">Verifikasi akun terlebih dahulu agar identitas pemilik toko dapat dipastikan. <a href="{{ route('profile') }}">Buka profil saya</a></div>
@endif
@if($store)
    @php
        $statusLabels=['pending'=>'Toko sedang diperiksa admin desa','approved'=>'Toko sudah disetujui','rejected'=>'Pendaftaran toko ditolak','suspended'=>'Toko dinonaktifkan'];
    @endphp
    <div class="mk-alert"><strong>{{ $statusLabels[$store->status] ?? $store->status }}</strong>
        @if($store->review_reason)<p>Alasan: {{ $store->review_reason }}</p>@endif
        @if($store->status==='pending')<p>Pengajuan diterima oleh admin {{ $store->region->name }}. Hasil pemeriksaan akan masuk ke notifikasi Anda.</p>@endif
        @if($store->status==='approved')<a class="mk-btn mk-secondary" href="{{ route('market.store',$store->id) }}">Lihat Tampilan Toko</a>@endif
    </div>
@endif
@if($store?->status==='approved')
    <nav class="mk-tabs"><a href="#produk">Produk</a><a href="#pesanan">Pesanan</a><a href="#pengiriman">Pengiriman & Pembayaran</a><a href="#profil-toko">Profil Toko</a><a href="#pesan">Pesan Pembeli</a></nav>
    <section class="mk-card" id="produk"><h2>Produk Saya</h2>
        @if($products->isEmpty())<p>Langkah selanjutnya: tambahkan produk pertama Anda. Siapkan foto, harga, dan jumlah stok.</p>@endif
        <a class="mk-btn" href="{{ route('market.seller.product') }}">Tambah Produk</a>
        <div class="mk-products">
@foreach($products as $product)<article class="mk-card">
            @if($product->foto)<img class="mk-product-img" src="{{ Storage::url($product->foto) }}" alt="{{ $product->nama_produk }}">@endif
            <h3>{{ $product->nama_produk }}</h3><p>Rp{{ number_format($product->harga,0,',','.') }} · Stok {{ $product->stok }}</p>
            <p>{{ ucfirst($product->status) }}</p><a class="mk-btn mk-secondary" href="{{ route('market.seller.product',$product->id) }}">Ubah Produk / Stok</a>
        </article>@endforeach</div>{{ $products->links() }}
    </section>
@endif
@if($store)
    <section class="mk-card" id="pesanan"><h2>Pesanan Pembeli</h2>
        <p class="mk-muted">Pesanan lama tetap dapat ditangani saat toko tutup. Status selesai bukan berarti uang tunai sudah diterima.</p>
        @forelse($orders as $order)<div class="mk-card"><strong>{{ $order->order_number }}</strong><p>{{ $order->full_name }} · Rp{{ number_format($order->grand_total,0,',','.') }}</p><p>{{ ['pending'=>'Menunggu tanggapan','paid'=>'Sudah dibayar','confirmed'=>'Diproses','in_delivery'=>'Sedang diantar','delivered'=>'Sudah diserahkan','completed'=>'Selesai','cancelled'=>'Dibatalkan','rejected'=>'Ditolak'][$order->status] ?? $order->status }}</p><a class="mk-btn" href="{{ route('market.seller.order',$order->id) }}">Lihat dan Tangani Pesanan</a></div>@empty<p>Belum ada pesanan. Pesanan baru akan muncul di sini.</p>@endforelse
        {{ $orders->links() }}
    </section>
    <section class="mk-card" id="pesan"><h2>Pesan Pembeli</h2>@forelse($contacts as $contact)<p><a class="mk-btn mk-secondary" href="{{ route('market.chat',[$store->id,$contact->id]) }}">Percakapan {{ $contact->name }}</a></p>@empty<p>Belum ada percakapan pembeli.</p>@endforelse</section>
@endif
@if($store?->status==='approved')
    <section class="mk-card" id="pengiriman"><h2>Pengiriman & Pembayaran</h2>
        <form action="{{ route('market.seller.settings') }}" method="POST" enctype="multipart/form-data">@csrf
            <input type="hidden" name="is_open" value="0"><label><input type="checkbox" name="is_open" value="1" @checked(old('is_open',$store->is_open))>Toko menerima pesanan baru</label>
            <p class="mk-muted">Matikan saat libur atau belum bisa melayani. Produk dan riwayat tidak dihapus.</p>
            @include('admin.unit.pasar_daerah.shipping-settings')
            <h2>Uang pembayaran diterima toko Anda</h2><p class="mk-muted">Gunakan rekening atau QRIS milik toko Anda. Pembayaran ini tidak masuk ke kas desa.</p>
            @foreach(['enable_cod'=>'Tunai saat barang diterima / diambil','enable_bank_transfer'=>'Transfer bank','enable_qris'=>'QRIS toko'] as $key=>$label)
                <input type="hidden" name="{{ $key }}" value="0"><label><input type="checkbox" name="{{ $key }}" value="1" @checked(old($key,$store->settings[$key] ?? false))>{{ $label }}</label>
            @endforeach
            <div class="mk-grid">
@foreach(['rekening_bank'=>'Nama bank','rekening_nomor'=>'Nomor rekening','rekening_nama'=>'Nama pemilik rekening'] as $key=>$label)<div><label for="{{ $key }}">{{ $label }} (jika transfer aktif)</label><input id="{{ $key }}" name="{{ $key }}" value="{{ old($key,$store->settings[$key] ?? '') }}" maxlength="100"></div>@endforeach</div>
            <label for="qris_image">Foto QRIS toko (jika QRIS aktif)</label><input id="qris_image" type="file" name="qris_image" accept="image/jpeg,image/png,image/webp">
            @if(!empty($store->settings['qris_image']))<img src="{{ Storage::url($store->settings['qris_image']) }}" alt="QRIS toko tersimpan" style="width:160px">@endif
            <button class="mk-btn" type="submit">Simpan Pengiriman dan Pembayaran</button>
        </form>
    </section>
@endif
<section class="mk-card" id="profil-toko"><h2>{{ $store ? 'Informasi Toko' : 'Daftarkan Toko Anda' }}</h2>
<p class="mk-muted">Nama pemilik mengikuti akun terverifikasi: <strong>{{ $owner->name }}</strong>. Alamat toko akan terlihat oleh pembeli. Perubahan nama atau alamat diperiksa kembali oleh admin desa.</p>
<form action="{{ route('market.seller.profile') }}" method="POST" enctype="multipart/form-data" data-store-profile>@csrf
    <fieldset><legend>1. Tentang toko</legend>
        <label for="store_name">Nama toko</label><input id="store_name" name="name" required maxlength="100" placeholder="Contoh: Kerupuk Bu Siti" value="{{ old('name',$store?->name) }}">
        <label for="category">Jenis jualan</label><select name="category" id="category" required>
@foreach(['Makanan dan Minuman','Hasil Kebun','Kerajinan','Kebutuhan Rumah Tangga','Lainnya'] as $category)<option @selected(old('category',$store?->category)===$category)>{{ $category }}</option>@endforeach</select>
        <label for="description">Ceritakan singkat tentang toko</label><textarea id="description" name="description" required minlength="15" maxlength="2000" placeholder="Contoh: Kerupuk pedas buatan rumahan, dibuat setiap pagi.">{{ old('description',$store?->description) }}</textarea>
    </fieldset>
    <fieldset><legend>2. Foto dan lokasi</legend>
        <label for="avatar_source">Foto profil toko</label><select id="avatar_source" name="avatar_source"><option value="keep">{{ $store?->avatar ? 'Tetap gunakan foto toko sekarang' : 'Gunakan gambar bawaan dulu' }}</option><option value="account">Gunakan foto profil akun saya</option><option value="upload">Unggah foto toko baru</option></select>
        <label for="avatar">Foto toko baru (jika memilih unggah)</label><input id="avatar" type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
        <label for="cover">Foto sampul memanjang (opsional)</label><input id="cover" type="file" name="cover" accept="image/jpeg,image/png,image/webp" data-cover-input>
        <div class="mk-cover" data-cover-preview style="margin-top:12px;overflow:hidden">
@if($store?->cover)<img src="{{ Storage::url($store->cover) }}" alt="Pratinjau sampul toko" style="width:100%;height:100%;object-fit:cover;object-position:center {{ $store->cover_position }}%">@endif</div>
        <label for="cover_position">Geser posisi foto sampul agar bagian penting terlihat</label><input type="range" name="cover_position" id="cover_position" min="0" max="100" value="{{ old('cover_position',$store?->cover_position ?? 50) }}" style="width:100%" data-cover-position>
        <button type="button" class="mk-btn mk-secondary" data-use-profile-address data-region="{{ $profileVillage?->id }}" data-address="{{ $owner->address ?? '' }}" data-lat="{{ $owner->latitude ?? '' }}" data-lng="{{ $owner->longitude ?? '' }}">Sama dengan alamat saya</button>
        <label for="region_id">Desa / kelurahan toko</label><select id="region_id" name="region_id" required>
@foreach($regions as $region)<option value="{{ $region->id }}" @selected(old('region_id',$store?->region_id ?? $profileVillage?->id)==$region->id)>{{ $region->name }}</option>@endforeach</select>
        <label for="address">Alamat toko dan patokan</label><textarea id="address" name="address" required maxlength="1000" placeholder="Jalan, RT/RW, dan patokan yang mudah ditemukan">{{ old('address',$store?->address ?? $owner->address) }}</textarea>
        <details><summary>Tambahkan titik lokasi toko (opsional)</summary><p class="mk-muted">Gunakan lokasi perangkat saat Anda berada di toko. Anda juga bisa mengisi koordinat lokasi dari peta.</p><button type="button" class="mk-btn mk-secondary" data-store-location>Gunakan Lokasi Saya</button><p data-location-status role="status"></p><div class="mk-grid"><div><label for="latitude">Lintang</label><input type="number" step="any" min="-90" max="90" name="latitude" id="latitude" value="{{ old('latitude',$store?->latitude) }}"></div><div><label for="longitude">Bujur</label><input type="number" step="any" min="-180" max="180" name="longitude" id="longitude" value="{{ old('longitude',$store?->longitude) }}"></div></div></details>
    </fieldset>
    <fieldset><legend>3. Periksa dan kirim</legend><p>Pastikan nama dan alamat toko benar. Pengajuan diperiksa admin desa sesuai lokasi toko, lalu hasilnya masuk ke notifikasi akun Anda.</p><label><input type="checkbox" required>Saya sudah memeriksa informasi toko dan memahami alamatnya akan terlihat oleh pembeli.</label></fieldset>
    <button class="mk-btn" type="submit" @disabled($owner->verification_status!=='verified')>{{ $store ? 'Simpan Informasi Toko' : 'Kirim Pendaftaran Toko' }}</button>
</form></section>
</main>
@endsection
@push('scripts')<script src="{{ asset('js/market-shipping-editor.js') }}" defer></script><script src="{{ asset('js/market-store.js') }}" defer></script>@endpush
