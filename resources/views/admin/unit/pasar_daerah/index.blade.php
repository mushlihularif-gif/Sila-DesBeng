@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Unit Layanan /</span> Pasar Daerah</h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('admin.unit.pasar_daerah.index') }}"><i class="bx bx-box me-1"></i> Daftar Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.unit.pasar_daerah.pesanan') }}"><i class="bx bx-cart me-1"></i> Daftar Pesanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.unit.pasar_daerah.laporan') }}"><i class="bx bx-line-chart me-1"></i> Laporan</a>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-pengaturan" aria-controls="navs-top-pengaturan" aria-selected="false"><i class="bx bx-cog me-1"></i> Pengaturan & SOP</button>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- TAB 1: DAFTAR PRODUK -->
                <div class="tab-pane fade show active" id="navs-top-produk" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-0">Daftar Produk Pasar Daerah</h5>
                            <small class="text-muted">Kelola produk-produk yang dijual di pasar daerah.</small>
                        </div>
                        <div>
                            <a href="{{ route('admin.unit.pasar_daerah.create') }}" class="btn btn-primary"><i class="bx bx-plus"></i> Tambah Produk</a>
                        </div>
                    </div>

                    @if($produks->count() > 0)
                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                            @foreach ($produks as $produk)
                                <div class="col">
                                    <div class="card h-100 product-card">
                                        <div class="position-relative">
                                            <div id="carouselProduk{{ $produk->id }}" class="carousel slide" data-bs-ride="carousel">
                                                <div class="carousel-inner">
                                                    @if ($produk->foto)
                                                    <div class="carousel-item active">
                                                        <img src="{{ asset('storage/' . $produk->foto) }}" class="card-img-top"
                                                            alt="{{ $produk->nama_produk }}"
                                                            style="height: 300px; object-fit: cover; object-position: center;">
                                                    </div>
                                                    @endif
                                                    @if ($produk->foto_2)
                                                        <div class="carousel-item {{ !$produk->foto ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $produk->foto_2) }}" class="card-img-top"
                                                                alt="{{ $produk->nama_produk }}"
                                                                style="height: 300px; object-fit: cover; object-position: center;">
                                                        </div>
                                                    @endif
                                                    @if ($produk->foto_3)
                                                        <div class="carousel-item {{ !$produk->foto && !$produk->foto_2 ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $produk->foto_3) }}" class="card-img-top"
                                                                alt="{{ $produk->nama_produk }}"
                                                                style="height: 300px; object-fit: cover; object-position: center;">
                                                        </div>
                                                    @endif
                                                </div>
                                                <button class="carousel-control-prev" type="button"
                                                    data-bs-target="#carouselProduk{{ $produk->id }}" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button"
                                                    data-bs-target="#carouselProduk{{ $produk->id }}" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Next</span>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="badge bg-label-primary">{{ $produk->kategori ?? 'Lainnya' }}</span>
                                                @if($produk->status == 'tersedia')
                                                    <span class="badge bg-success">Tersedia</span>
                                                @elseif($produk->status == 'habis')
                                                    <span class="badge bg-danger">Habis</span>
                                                @else
                                                    <span class="badge bg-secondary">Nonaktif</span>
                                                @endif
                                            </div>
                                            <h5 class="card-title text-capitalize mb-1">{{ $produk->nama_produk }}</h5>
                                            <p class="text-primary fw-bold mb-2">Rp {{ number_format($produk->harga, 0, ',', '.') }} / {{ $produk->satuan }}</p>
                                            <p class="card-text text-muted small">{{ Str::limit($produk->deskripsi, 100) }}</p>
                                            <p class="card-text"><small class="text-muted">Stok: {{ $produk->stok }}</small></p>
                                            
                                            <div class="mt-3 d-flex gap-2">
                                                <a href="{{ route('admin.unit.pasar_daerah.edit', $produk->id) }}"
                                                    class="btn btn-sm btn-outline-warning">Ubah</a>
                                                <form action="{{ route('admin.unit.pasar_daerah.destroy', $produk->id) }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="d-flex justify-content-center align-items-center" style="min-height: 400px;">
                            <div class="text-center">
                                <div class="mb-4">
                                    <i class="bx bx-store-alt text-muted" style="font-size: 5rem;"></i>
                                </div>
                                <h3 class="fw-bold text-muted mb-3">Belum Ada Produk</h3>
                                <p class="text-muted mb-4">Mulai tambahkan produk yang akan dijual di pasar daerah.</p>
                                <a href="{{ route('admin.unit.pasar_daerah.create') }}" class="btn btn-primary btn-lg">
                                    <i class="bx bx-plus me-1"></i> Tambah Produk Pertama
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 2: PENGATURAN -->
                <div class="tab-pane fade" id="navs-top-pengaturan" role="tabpanel">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Pengaturan Toko Pasar Daerah</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.unit.pasar_daerah.sop') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold" for="ongkir_per_km">Ongkos Kirim per Kilometer (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="ongkir_per_km" name="ongkir_per_km" 
                                           value="{{ $settings['ongkir_per_km'] ?? 5000 }}" required min="0">
                                    <small class="text-muted">Biaya ini akan dikalikan dengan jarak (dalam KM) dari titik toko ke lokasi pembeli.</small>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold" for="sop_pasar">Ketentuan & SOP Pasar Daerah</label>
                                    <textarea class="form-control" id="sop_pasar" name="sop_pasar" rows="6">{{ $settings['sop_pasar'] ?? '1. Pesanan akan diproses setelah pembayaran terkonfirmasi.\n2. Pengiriman dilakukan pada jam operasional kerja.\n3. Harap periksa kembali barang saat diterima.' }}</textarea>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1"></i> Simpan Pengaturan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
