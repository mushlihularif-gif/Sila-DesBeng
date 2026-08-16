@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Unit Layanan /</span> Pasar Daerah
                </h4>
            </div>
        </div>

        <!-- Panduan -->
        <div class="card bg-label-success border-0 shadow-none mb-4" style="border-radius: 12px;">
            <div class="card-body d-flex align-items-center p-4">
                <div class="me-3">
                    <div class="bg-success p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                        <i class="bx bx-store-alt fs-3"></i>
                    </div>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-success">Manajemen Pasar Daerah</h5>
                    <p class="mb-0 text-success" style="opacity: 0.85;">
                        Kelola etalase produk-produk hasil bumi, kerajinan, dan komoditas unggulan desa yang akan dijual ke publik.
                    </p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center" role="alert">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <style>
            .nav-pills .nav-link { color: #6c757d; font-weight: 600; padding: 0.6rem 1.2rem; transition: all 0.3s; border-radius: 50rem; }
            .nav-pills .nav-link:hover { background-color: #f8f9fa; color: #566a7f; }
            .nav-pills .nav-link.active { background-color: #696cff; color: #fff; box-shadow: 0 4px 6px rgba(105, 108, 255, 0.2); }
            .product-card { transition: all 0.3s ease; border: none; box-shadow: 0 0.125rem 0.25rem rgba(161, 172, 184, 0.2); border-radius: 1rem; }
            .product-card:hover { transform: translateY(-5px); box-shadow: 0 0.5rem 1rem rgba(161, 172, 184, 0.15); }
        </style>

        <div class="nav-align-top mb-4">
            <ul class="nav nav-pills gap-2 mb-4" role="tablist">
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'produk' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-produk" aria-controls="navs-top-produk" aria-selected="{{ $tab == 'produk' ? 'true' : 'false' }}">
                        <i class="bx bx-box me-2"></i> Daftar Produk
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'pesanan' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-pesanan" aria-controls="navs-top-pesanan" aria-selected="{{ $tab == 'pesanan' ? 'true' : 'false' }}">
                        <i class="bx bx-cart me-2"></i> Daftar Pesanan
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'laporan' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-laporan" aria-controls="navs-top-laporan" aria-selected="{{ $tab == 'laporan' ? 'true' : 'false' }}">
                        <i class="bx bx-line-chart me-2"></i> Laporan
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'pengaturan' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-pengaturan" aria-controls="navs-top-pengaturan" aria-selected="{{ $tab == 'pengaturan' ? 'true' : 'false' }}">
                        <i class="bx bx-cog me-2"></i> Pengaturan Toko
                    </button>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- TAB 1: DAFTAR PRODUK -->
                <div class="tab-pane fade {{ $tab == 'produk' ? 'show active' : '' }}" id="navs-top-produk" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-success-subtle text-success rounded-circle me-3 d-flex justify-content-center align-items-center">
                                <i class="bx bx-box fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Daftar Produk Pasar Daerah</h5>
                                <small class="text-muted">Kelola komoditas dan hasil tani yang akan dijual di platform Pasar Daerah.</small>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('admin.unit.pasar_daerah.create') }}" class="btn btn-success rounded-pill px-4 shadow-sm"><i class="bx bx-plus me-1"></i> Tambah Produk</a>
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
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="badge bg-label-success rounded-pill px-3">{{ $produk->kategori ?? 'Lainnya' }}</span>
                                                @if($produk->status == 'tersedia')
                                                    <span class="badge bg-success shadow-sm rounded-pill px-3">Tersedia</span>
                                                @elseif($produk->status == 'habis')
                                                    <span class="badge bg-danger shadow-sm rounded-pill px-3">Habis</span>
                                                @else
                                                    <span class="badge bg-secondary shadow-sm rounded-pill px-3">Nonaktif</span>
                                                @endif
                                            </div>
                                            <h5 class="card-title fw-bold text-capitalize mb-1">{{ $produk->nama_produk }}</h5>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="text-success fw-bold fs-5 me-1">Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                                                <span class="text-muted small">/ {{ $produk->satuan }}</span>
                                            </div>
                                            
                                            <div class="d-flex align-items-center mb-3 text-muted small bg-light p-2 rounded-3">
                                                <i class="bx bx-package me-2 text-primary"></i> Sisa Stok: <strong class="ms-1 text-dark">{{ $produk->stok }}</strong>
                                            </div>

                                            <p class="card-text text-muted flex-grow-1" style="font-size: 0.85rem;">{{ Str::limit($produk->deskripsi, 80) }}</p>
                                            
                                            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                                                <a href="{{ route('admin.unit.pasar_daerah.edit', $produk->id) }}"
                                                    class="btn btn-sm btn-light border text-warning shadow-sm rounded-pill px-3"><i class="bx bx-edit me-1"></i>Ubah</a>
                                                <form action="{{ route('admin.unit.pasar_daerah.destroy', $produk->id) }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light border text-danger shadow-sm rounded-pill px-3"><i class="bx bx-trash me-1"></i>Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                            <div class="card-body">
                                <div class="mb-4">
                                    <i class="bx bx-store-alt text-success" style="font-size: 80px; opacity: 0.5;"></i>
                                </div>
                                <h4 class="fw-bold">Belum Ada Produk</h4>
                                <p class="text-muted mb-4">Mulai tambahkan produk atau komoditas hasil desa yang siap dipasarkan.</p>
                                <a href="{{ route('admin.unit.pasar_daerah.create') }}" class="btn btn-success rounded-pill px-4 shadow-sm">
                                    <i class="bx bx-plus me-1"></i> Tambah Produk Pertama
                                </a>
                            </div>
                        </div>
                    @endif
                </div>


                <!-- TAB 2: DAFTAR PESANAN -->
                <div class="tab-pane fade {{ $tab == 'pesanan' ? 'show active' : '' }}" id="navs-top-pesanan" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-info-subtle text-info rounded-circle me-3 d-flex justify-content-center align-items-center">
                                <i class="bx bx-cart fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Daftar Pesanan Pasar Daerah</h5>
                                <small class="text-muted">Kelola pesanan dari warga</small>
                            </div>
                        </div>
                        <div>
                            <form action="{{ route('admin.unit.pasar_daerah.index') . '?tab=pesanan' }}" method="GET" class="d-flex gap-2">
                                <select name="status" class="form-select border-light-subtle shadow-sm rounded-pill px-3" onchange="this.form.submit()">
                                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending (Menunggu Bayar)</option>
                                    <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid (Sudah Bayar)</option>
                                    <option value="confirmed" {{ $status == 'confirmed' ? 'selected' : '' }}>Confirmed (Diproses)</option>
                                    <option value="in_delivery" {{ $status == 'in_delivery' ? 'selected' : '' }}>In Delivery (Dikirim)</option>
                                    <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">No. Pesanan</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Waktu</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Pelanggan</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Total Tagihan</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Pembayaran</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @forelse($pesanans as $pesanan)
                                    <tr>
                                        <td class="ps-4"><span class="fw-bold text-primary">#{{ $pesanan->order_number }}</span></td>
                                        <td>{{ $pesanan->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                 <div class="avatar avatar-sm border rounded-circle p-1 me-2">
                                                    <span class="avatar-initial rounded-circle bg-secondary-subtle text-secondary fw-bold">
                                                        {{ strtoupper(substr($pesanan->user->name ?? 'U', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium text-dark">{{ $pesanan->user->name ?? 'Anonim' }}</span>
                                                    <small class="text-muted">{{ $pesanan->phone }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-dark fw-bold">Rp {{ number_format($pesanan->grand_total, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @if(strtolower($pesanan->payment_method) == 'tunai')
                                                <span class="badge bg-label-secondary shadow-sm rounded-pill px-3">COD / Tunai</span>
                                            @else
                                                <span class="badge bg-label-info shadow-sm rounded-pill px-3">{{ str_replace('_', ' ', strtoupper($pesanan->payment_method)) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $pesanan->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.unit.pasar_daerah.pesanan.show', $pesanan->id) }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 text-primary">
                                                <i class="bx bx-search-alt me-1"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="mb-3"><i class="bx bx-receipt fs-1 text-muted opacity-25"></i></div>
                                            <h6 class="text-muted fw-bold">Belum ada data pesanan</h6>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- TAB 3: LAPORAN -->
                <div class="tab-pane fade {{ $tab == 'laporan' ? 'show active' : '' }}" id="navs-top-laporan" role="tabpanel">
                    
                    <!-- Filter and Summary -->
                    <div class="row mb-4">
                        <div class="col-xl-8">
                            <div class="card bg-primary text-white border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body d-flex align-items-center justify-content-between p-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-md bg-white rounded-circle p-2 text-primary d-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px;">
                                            <i class="bx bx-wallet fs-2"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 text-white fw-medium" style="opacity: 0.9;">Total Pendapatan Bersih</h6>
                                            <small style="opacity: 0.75;">Dari {{ count($laporans) }} transaksi yang telah selesai</small>
                                        </div>
                                    </div>
                                    <h3 class="mb-0 text-white fw-bold">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 mt-4 mt-xl-0">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body p-4">
                                    <form action="{{ route('admin.unit.pasar_daerah.index') . '?tab=laporan' }}" method="GET" class="h-100 d-flex flex-column justify-content-center">
                                        <div class="input-group input-group-sm mb-3 border-light-subtle shadow-sm rounded-3">
                                            <span class="input-group-text bg-light"><i class="bx bx-calendar"></i></span>
                                            <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}" placeholder="Mulai">
                                        </div>
                                        <div class="input-group input-group-sm mb-3 border-light-subtle shadow-sm rounded-3">
                                            <span class="input-group-text bg-light"><i class="bx bx-calendar"></i></span>
                                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}" placeholder="Sampai">
                                        </div>
                                        <div class="d-flex gap-2 mt-auto">
                                            <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100 shadow-sm">Terapkan Filter</button>
                                            <a href="{{ route('admin.unit.pasar_daerah.index') . '?tab=laporan' }}" class="btn btn-sm btn-light border rounded-pill px-3"><i class="bx bx-refresh"></i></a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Laporan -->
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Daftar Transaksi Selesai</h5>
                            <button onclick="window.print()" class="btn btn-sm btn-secondary rounded-pill px-3 shadow-sm"><i class="bx bx-printer me-1"></i> Cetak Laporan</button>
                        </div>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">No. Pesanan</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Tanggal Selesai</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Pelanggan</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Metode Bayar</th>
                                        <th class="text-end py-3 text-secondary text-uppercase small fw-bold">Total Belanja</th>
                                        <th class="text-end py-3 text-secondary text-uppercase small fw-bold">Ongkos Kirim</th>
                                        <th class="text-end pe-4 py-3 text-primary text-uppercase small fw-bold">Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                    @forelse($laporans as $laporan)
                                    <tr>
                                        <td class="ps-4"><a href="{{ route('admin.unit.pasar_daerah.pesanan.show', $laporan->id) }}" class="fw-bold text-primary">#{{ $laporan->order_number }}</a></td>
                                        <td>{{ $laporan->updated_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                 <div class="avatar avatar-sm border rounded-circle p-1 me-2">
                                                    <span class="avatar-initial rounded-circle bg-secondary-subtle text-secondary fw-bold">
                                                        {{ strtoupper(substr($laporan->user->name ?? 'U', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span class="fw-medium text-dark">{{ $laporan->user->name ?? 'Anonim' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if(strtolower($laporan->payment_method) == 'tunai')
                                                <span class="badge bg-label-secondary shadow-sm rounded-pill px-3">COD</span>
                                            @else
                                                <span class="badge bg-label-info shadow-sm rounded-pill px-3">{{ str_replace('_', ' ', strtoupper($laporan->payment_method)) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">Rp {{ number_format($laporan->total_price, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($laporan->shipping_cost, 0, ',', '.') }}</td>
                                        <td class="text-end pe-4 fw-bold text-primary fs-6">Rp {{ number_format($laporan->grand_total, 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="mb-3"><i class="bx bx-line-chart fs-1 text-muted opacity-25"></i></div>
                                            <h6 class="text-muted fw-bold">Belum ada transaksi selesai pada rentang waktu ini.</h6>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>


                <!-- TAB 4: PENGATURAN TOKO -->
                <div class="tab-pane fade {{ $tab == 'pengaturan' ? 'show active' : '' }}" id="navs-top-pengaturan" role="tabpanel">
                    <div class="row">
                        <div class="col-xl-8">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-header bg-white border-bottom p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-label-primary rounded-circle me-3 d-flex justify-content-center align-items-center"><i class="bx bx-cog"></i></div>
                                        <h5 class="mb-0 fw-bold">Pengaturan Toko Pasar Daerah</h5>
                                    </div>
                                </div>
                                <div class="card-body p-4">
                                    <form action="{{ route('admin.unit.pasar_daerah.sop') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label text-dark fw-bold" for="ongkir_dalam_desa">Ongkir Dalam Desa (Satu Desa) <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-merge border-light-subtle shadow-sm rounded-3">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" class="form-control" id="ongkir_dalam_desa" name="ongkir_dalam_desa" 
                                                    value="{{ $settings['ongkir_dalam_desa'] ?? 0 }}" required min="0">
                                            </div>
                                            <small class="text-muted mt-1 d-block">Ongkos kirim untuk pembeli yang beralamat di desa yang sama dengan toko.</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label text-dark fw-bold" for="ongkir_luar_desa">Ongkir Luar Desa (Beda Desa, Satu Kecamatan) <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-merge border-light-subtle shadow-sm rounded-3">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" class="form-control" id="ongkir_luar_desa" name="ongkir_luar_desa" 
                                                    value="{{ $settings['ongkir_luar_desa'] ?? 10000 }}" required min="0">
                                            </div>
                                            <small class="text-muted mt-1 d-block">Ongkos kirim untuk pembeli dari desa tetangga di dalam 1 kecamatan.</small>
                                        </div>

                                        <hr class="my-4 text-light">

                                        <div class="mb-4">
                                            <label class="form-label text-dark fw-bold mb-3">Pilih Metode Ongkos Kirim Luar Kecamatan (Tampilan Baru) <span class="text-danger">*</span></label>
                                            
                                            <div class="row g-3 mb-4">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center gap-3 p-3 border rounded-3 w-100" style="cursor: pointer; transition: all 0.2s;" id="label_pukul_rata" onclick="window.setOngkirType('pukul_rata')">
                                                        <input type="radio" name="tipe_ongkir_luar_kecamatan" id="tipe_pukul_rata" value="pukul_rata" class="form-check-input mt-0" style="transform: scale(1.2);" {{ ($settings['tipe_ongkir_luar_kecamatan'] ?? 'pukul_rata') == 'pukul_rata' ? 'checked' : '' }} onclick="window.setOngkirType('pukul_rata')">
                                                        <div>
                                                            <div class="fw-bold text-dark mb-1">Pukul Rata</div>
                                                            <div class="small text-muted" style="line-height: 1.2;">Satu harga untuk semua luar kecamatan</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center gap-3 p-3 border rounded-3 w-100" style="cursor: pointer; transition: all 0.2s;" id="label_per_kecamatan" onclick="window.setOngkirType('per_kecamatan')">
                                                        <input type="radio" name="tipe_ongkir_luar_kecamatan" id="tipe_per_kecamatan" value="per_kecamatan" class="form-check-input mt-0" style="transform: scale(1.2);" {{ ($settings['tipe_ongkir_luar_kecamatan'] ?? 'pukul_rata') == 'per_kecamatan' ? 'checked' : '' }} onclick="window.setOngkirType('per_kecamatan')">
                                                        <div>
                                                            <div class="fw-bold text-dark mb-1">Per Kecamatan</div>
                                                            <div class="small text-muted" style="line-height: 1.2;">Tentukan harga untuk masing-masing daerah</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Opsi Pukul Rata -->
                                            <div id="div_pukul_rata" class="mt-3 p-3 bg-light rounded-4 border" style="display: {{ ($settings['tipe_ongkir_luar_kecamatan'] ?? 'pukul_rata') == 'pukul_rata' ? 'block' : 'none' }};">
                                                <label class="form-label fw-medium" for="ongkir_luar_kecamatan">Harga Ongkir Pukul Rata</label>
                                                <div class="input-group input-group-merge border-light-subtle shadow-sm rounded-3">
                                                    <span class="input-group-text bg-white">Rp</span>
                                                    <input type="number" class="form-control border-start-0" id="ongkir_luar_kecamatan" name="ongkir_luar_kecamatan" 
                                                        value="{{ $settings['ongkir_luar_kecamatan'] ?? 25000 }}" min="0">
                                                </div>
                                                <small class="text-muted mt-2 d-block">Ongkos kirim otomatis berlaku untuk semua pembeli dari kecamatan lain.</small>
                                            </div>

                                            <!-- Opsi Per Kecamatan -->
                                            <div id="div_per_kecamatan" class="mt-3" style="display: {{ ($settings['tipe_ongkir_luar_kecamatan'] ?? 'pukul_rata') == 'per_kecamatan' ? 'block' : 'none' }};">
                                                <div class="table-responsive text-nowrap border rounded-4 shadow-sm">
                                                    <table class="table table-hover mb-0">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th class="py-3 text-secondary text-uppercase small fw-bold" style="width: 50px;">Layanan</th>
                                                                <th class="py-3 text-secondary text-uppercase small fw-bold">Nama Kecamatan</th>
                                                                <th class="py-3 text-secondary text-uppercase small fw-bold text-end pe-4">Tarif Ongkir (Rp)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="table-border-bottom-0">
                                                            @foreach($semuaKecamatan as $kec)
                                                                @php 
                                                                    $isActive = isset($settings['ongkir_kecamatan_khusus'][$kec->id]);
                                                                    $hargaKec = $isActive ? $settings['ongkir_kecamatan_khusus'][$kec->id] : '';
                                                                @endphp
                                                                <tr>
                                                                    <td class="text-center">
                                                                        <div class="form-check form-switch d-flex justify-content-center mb-0">
                                                                            <input class="form-check-input" type="checkbox" role="switch" id="switch_kec_{{ $kec->id }}" 
                                                                                {{ $isActive ? 'checked' : '' }} onchange="toggleKecamatan({{ $kec->id }})">
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <label class="form-check-label fw-medium {{ $isActive ? 'text-dark' : 'text-muted' }}" id="label_kec_{{ $kec->id }}" for="switch_kec_{{ $kec->id }}">
                                                                            {{ $kec->name }}
                                                                        </label>
                                                                    </td>
                                                                    <td class="pe-3">
                                                                        <div class="input-group input-group-sm">
                                                                            <span class="input-group-text bg-light text-muted">Rp</span>
                                                                            <input type="number" class="form-control text-end" 
                                                                                name="ongkir_kecamatan_khusus[{{ $kec->id }}]" id="input_kec_{{ $kec->id }}" 
                                                                                value="{{ $hargaKec }}" min="0" placeholder="0" {{ $isActive ? '' : 'disabled' }}>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <small class="text-danger mt-2 d-block"><i class="bx bx-info-circle me-1"></i>Kecamatan yang dimatikan (switch abu-abu) berarti toko Anda TIDAK melayani pengiriman ke daerah tersebut.</small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="bx bx-save me-1"></i> Simpan Pengaturan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card bg-primary text-white border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-body p-4">
                                    <h5 class="fw-bold text-white mb-3"><i class="bx bx-bulb me-2"></i>Tips Layanan Delivery</h5>
                                    <ul class="list-unstyled mb-0" style="opacity: 0.9">
                                        <li class="mb-2"><i class="bx bx-check-circle me-2"></i>Matikan layanan pada opsi <b>Per Kecamatan</b> untuk area yang terlalu jauh atau tidak terjangkau kurir.</li>
                                        <li class="mb-2"><i class="bx bx-check-circle me-2"></i>Pastikan Kontak WhatsApp telah diaktifkan di menu <b>Pengaturan &gt; Layanan Wilayah</b> agar pembeli mudah berkomunikasi.</li>
                                        <li class="mb-2"><i class="bx bx-check-circle me-2"></i>Tetapkan harga ongkos kirim yang wajar agar tidak memberatkan pembeli.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    window.setOngkirType = function(type) {
        try {
            document.getElementById('tipe_' + type).checked = true;
            
            document.getElementById('div_pukul_rata').style.display = type === 'pukul_rata' ? 'block' : 'none';
            document.getElementById('div_per_kecamatan').style.display = type === 'per_kecamatan' ? 'block' : 'none';
            
            const lblPukulRata = document.getElementById('label_pukul_rata');
            const lblPerKecamatan = document.getElementById('label_per_kecamatan');
            
            if (lblPukulRata && lblPerKecamatan) {
                if (type === 'pukul_rata') {
                    lblPukulRata.classList.add('border-primary', 'bg-label-primary');
                    lblPerKecamatan.classList.remove('border-primary', 'bg-label-primary');
                } else {
                    lblPerKecamatan.classList.add('border-primary', 'bg-label-primary');
                    lblPukulRata.classList.remove('border-primary', 'bg-label-primary');
                }
            }
        } catch (e) {
            console.error("Error toggling ongkir:", e);
        }
    };

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const initType = document.getElementById('tipe_per_kecamatan').checked ? 'per_kecamatan' : 'pukul_rata';
        window.setOngkirType(initType);

        // Cek apakah ada hash di URL (misal: #navs-top-pengaturan)
        var hash = window.location.hash;
        if (hash) {
            // Cari tombol tab yang menargetkan hash tersebut
            var tabTarget = document.querySelector('button[data-bs-target="' + hash + '"]');
            if (tabTarget) {
                // Aktifkan tab menggunakan Bootstrap Tab API
                var tab = new bootstrap.Tab(tabTarget);
                tab.show();
            }
        }
    });

    function toggleKecamatan(id) {
        const isChecked = document.getElementById('switch_kec_' + id).checked;
        const input = document.getElementById('input_kec_' + id);
        const label = document.getElementById('label_kec_' + id);
        
        if (isChecked) {
            input.disabled = false;
            label.classList.remove('text-muted');
            label.classList.add('text-dark');
        } else {
            input.disabled = true;
            input.value = '';
            label.classList.add('text-muted');
            label.classList.remove('text-dark');
        }
    }
</script>
@endsection

@section('styles')
<style>
    @media print {
        .navbar, .layout-menu, .footer, .btn, form, .nav-tabs {
            display: none !important;
        }
        .layout-page {
            padding: 0 !important;
            margin: 0 !important;
        }
        .container-xxl {
            max-width: 100% !important;
            padding: 0 !important;
        }
        .card {
            box-shadow: none !important;
            border: none !important;
        }
        body {
            background-color: white !important;
        }
    }
</style>
@endsection
