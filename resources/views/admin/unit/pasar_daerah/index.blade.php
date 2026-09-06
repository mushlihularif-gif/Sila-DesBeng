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
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'profil' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-profil" aria-controls="navs-top-profil" aria-selected="{{ $tab == 'profil' ? 'true' : 'false' }}">
                        <i class="bx bx-store me-2"></i> Profil Toko
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'ulasan' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-ulasan" aria-controls="navs-top-ulasan" aria-selected="{{ $tab == 'ulasan' ? 'true' : 'false' }}">
                        <i class="bx bx-star me-2"></i> Ulasan & Komentar
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link {{ $tab == 'komplain' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-komplain" aria-controls="navs-top-komplain" aria-selected="{{ $tab == 'komplain' ? 'true' : 'false' }}">
                        <i class="bx bx-shield-quarter me-2"></i> Komplain & Retur
                        @php $pendingComplaints = $complaints->where('status', 'pending')->count(); @endphp
                        @if($pendingComplaints > 0)
                            <span class="badge rounded-pill bg-danger ms-1">{{ $pendingComplaints }}</span>
                        @endif
                    </button>
                </li>
            </ul>
            
            <div class="tab-content">
                <!-- TAB 1: DAFTAR PRODUK -->
                <div class="tab-pane fade {{ $tab == 'produk' ? 'show active' : '' }}" id="navs-top-produk" role="tabpanel">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 bg-white p-3 rounded-4 shadow-sm gap-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-success-subtle text-success rounded-circle me-3 d-flex justify-content-center align-items-center">
                                <i class="bx bx-box fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Daftar Produk Pasar Daerah</h5>
                                <small class="text-muted">Kelola komoditas dan hasil tani yang akan dijual di platform Pasar Daerah.</small>
                            </div>
                        </div>
                        <div class="w-100 w-md-auto text-end">
                            <a href="{{ route('admin.unit.pasar_daerah.create') }}" class="btn btn-success rounded-pill px-4 shadow-sm w-100"><i class="bx bx-plus me-1"></i> Tambah Produk</a>
                        </div>
                    </div>

                    @if($produks->count() > 0)
                        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2 g-md-3">
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
                                                            style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                    </div>
                                                    @endif
                                                    @if ($produk->foto_2)
                                                        <div class="carousel-item {{ !$produk->foto ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $produk->foto_2) }}" class="card-img-top"
                                                                alt="{{ $produk->nama_produk }}"
                                                                style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                        </div>
                                                    @endif
                                                    @if ($produk->foto_3)
                                                        <div class="carousel-item {{ !$produk->foto && !$produk->foto_2 ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $produk->foto_3) }}" class="card-img-top"
                                                                alt="{{ $produk->nama_produk }}"
                                                                style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
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
                                            
                                            <div class="d-flex align-items-center mb-3 text-muted">
                                                <i class="bx bx-package me-2 text-primary"></i> Sisa Stok: <strong class="ms-1 text-dark">{{ $produk->stok }}</strong>
                                            </div>
                                            
                                            <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                                                <a href="{{ route('admin.unit.pasar_daerah.edit', $produk->id) }}"
                                                    class="btn btn-sm btn-light border text-warning shadow-sm rounded-pill px-3"><i class="bx bx-edit me-1"></i><i class="bx bx-edit"></i></a>
                                                <form action="{{ route('admin.unit.pasar_daerah.destroy', $produk->id) }}" method="POST"
                                                    data-konfirmasi="Apakah Anda yakin ingin menghapus produk ini?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light border text-danger shadow-sm rounded-pill px-3"><i class="bx bx-trash me-1"></i><i class="bx bx-trash"></i></button>
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
                                    <form action="{{ route('admin.unit.pasar_daerah.sop') }}" method="POST" enctype="multipart/form-data">
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
                                        
                                        <hr class="my-4 text-light">

                                        <!-- Section: Metode Pembayaran Toko -->
                                        <div class="mb-4">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="avatar avatar-xs bg-label-success rounded-circle me-2 d-flex justify-content-center align-items-center">
                                                    <i class="bx bx-credit-card"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">Metode Pembayaran yang Diterima Toko</h6>
                                                    <small class="text-muted">Atur rekening bank, e-wallet, atau QRIS yang disediakan oleh BUMDes/Desa untuk pembeli.</small>
                                                </div>
                                            </div>

                                            <!-- 1. COD / Bayar Tunai -->
                                            <div class="p-3 border rounded-3 bg-white mb-3 shadow-sm">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-sm bg-label-info rounded-circle d-flex align-items-center justify-content-center">
                                                            <i class="bx bx-money fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark">Bayar di Tempat / Tunai (COD)</div>
                                                            <small class="text-muted">Pembeli membayar tunai saat barang diantar kurir atau diambil di toko.</small>
                                                        </div>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="enable_cod" id="enable_cod" value="1" {{ ($settings['enable_cod'] ?? true) ? 'checked' : '' }} style="transform: scale(1.3);">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 2. Transfer Bank -->
                                            <div class="p-3 border rounded-3 bg-white mb-3 shadow-sm">
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-sm bg-label-primary rounded-circle d-flex align-items-center justify-content-center">
                                                            <i class="bx bxs-bank fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark">Transfer Bank (Rekening BUMDes / Desa)</div>
                                                            <small class="text-muted">Pembeli dapat mentransfer langsung ke rekening bank resmi desa/toko Anda.</small>
                                                        </div>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="enable_bank_transfer" id="enable_bank_transfer" value="1" {{ ($settings['enable_bank_transfer'] ?? true) ? 'checked' : '' }} style="transform: scale(1.3);">
                                                    </div>
                                                </div>

                                                <div class="row g-2 pt-2 border-top">
                                                    <div class="col-md-4">
                                                        <label class="form-label text-xs fw-bold text-dark">Nama Bank</label>
                                                        <input type="text" class="form-control form-control-sm rounded-2" name="rekening_bank" list="bankList" placeholder="Contoh: Bank Riau Kepri Syariah / BRI" value="{{ $settings['rekening_bank'] ?? 'Bank Riau Kepri Syariah' }}">
                                                        <datalist id="bankList">
                                                            <option value="Bank Riau Kepri Syariah">
                                                            <option value="Bank BRI">
                                                            <option value="Bank Mandiri">
                                                            <option value="Bank BCA">
                                                            <option value="Bank BNI">
                                                            <option value="Bank BSI">
                                                        </datalist>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label text-xs fw-bold text-dark">Nomor Rekening</label>
                                                        <input type="text" class="form-control form-control-sm rounded-2" name="rekening_nomor" placeholder="Nomor rekening" value="{{ $settings['rekening_nomor'] ?? '' }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label text-xs fw-bold text-dark">Atas Nama Pemilik</label>
                                                        <input type="text" class="form-control form-control-sm rounded-2" name="rekening_nama" placeholder="Contoh: BUMDes {{ $admin->region->name ?? 'Desa' }}" value="{{ $settings['rekening_nama'] ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 3. QRIS & E-Wallet -->
                                            <div class="p-3 border rounded-3 bg-white mb-3 shadow-sm">
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="avatar avatar-sm bg-label-danger rounded-circle d-flex align-items-center justify-content-center">
                                                            <i class="bx bx-qr-scan fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark">QRIS &amp; E-Wallet (DANA / GoPay / OVO)</div>
                                                            <small class="text-muted">Tampilkan barcode QRIS resmi toko Anda untuk scan pembayaran instan.</small>
                                                        </div>
                                                    </div>
                                                    <div class="form-check form-switch mb-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="enable_qris" id="enable_qris" value="1" {{ ($settings['enable_qris'] ?? false) ? 'checked' : '' }} style="transform: scale(1.3);">
                                                    </div>
                                                </div>

                                                <div class="row g-3 pt-2 border-top">
                                                    <div class="col-md-6">
                                                        <label class="form-label text-xs fw-bold text-dark">Upload Gambar QRIS (Barcode)</label>
                                                        <input type="file" class="form-control form-control-sm rounded-2" name="qris_image" accept="image/*">
                                                        @if(!empty($settings['qris_image']))
                                                            <div class="mt-2 d-flex align-items-center gap-2">
                                                                <a href="{{ Storage::url($settings['qris_image']) }}" target="_blank" class="d-inline-block border rounded p-1" style="width: 50px; height: 50px;">
                                                                    <img src="{{ Storage::url($settings['qris_image']) }}" class="w-100 h-100 object-fit-contain">
                                                                </a>
                                                                <span class="text-xs text-success"><i class="bx bx-check-circle me-1"></i>QRIS aktif tersimpan</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-xs fw-bold text-dark">Atau Nomor HP E-Wallet</label>
                                                        <input type="text" class="form-control form-control-sm rounded-2" name="qris_ewallet_number" placeholder="Contoh: 0812-3456-7890 (DANA/GoPay)" value="{{ $settings['qris_ewallet_number'] ?? '' }}">
                                                    </div>
                                                </div>
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

                <!-- TAB: PROFIL TOKO -->
                <div class="tab-pane fade {{ $tab == 'profil' ? 'show active' : '' }}" id="navs-top-profil" role="tabpanel">
                    <div class="card mb-4 border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom pb-3 pt-4 px-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark"><i class="bx bx-store text-primary me-2"></i>Profil Toko</h5>
                                <small class="text-muted">Lengkapi informasi toko dan foto profil yang akan dilihat oleh pembeli.</small>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.unit.pasar_daerah.profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="row mb-4">
                                    <label class="col-sm-2 col-form-label fw-bold" for="avatar">Foto Profil Toko</label>
                                    <div class="col-sm-10">
                                        @if($admin->avatar)
                                            <div class="mb-3">
                                                <img src="{{ Storage::url($admin->avatar) }}" alt="Avatar" class="d-block rounded-circle border p-1" height="120" width="120" style="object-fit: cover;">
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center justify-content-center bg-light rounded-circle text-muted border p-1" style="height: 120px; width: 120px;">
                                                    <i class="bx bx-image fs-1"></i>
                                                </div>
                                            </div>
                                        @endif
                                        <input class="form-control" type="file" id="avatar" name="avatar" accept="image/png, image/jpeg">
                                        <small class="text-muted">Maksimal 2MB. Format: JPG/PNG.</small>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label class="col-sm-2 col-form-label fw-bold" for="store_banner">Foto Sampul / Background Toko</label>
                                    <div class="col-sm-10">
                                        @if($admin->store_banner)
                                            <div class="mb-3">
                                                <img src="{{ Storage::url($admin->store_banner) }}" alt="Store Banner" class="d-block rounded-3 border p-1 w-100" style="height: 200px; object-fit: cover;">
                                            </div>
                                        @else
                                            <div class="mb-3">
                                                <div class="d-flex flex-column align-items-center justify-content-center bg-light rounded-3 text-muted border p-1 w-100" style="height: 200px;">
                                                    <i class="bx bx-image-alt fs-1 mb-2"></i>
                                                    <span>Belum ada foto sampul</span>
                                                </div>
                                            </div>
                                        @endif
                                        <input class="form-control" type="file" id="store_banner" name="store_banner" accept="image/png, image/jpeg">
                                        <small class="text-muted">Maksimal 3MB. Rekomendasi ukuran: 1200x400 px. Format: JPG/PNG.</small>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label class="col-sm-2 col-form-label fw-bold" for="store_description">Tentang Toko</label>
                                    <div class="col-sm-10">
                                        <textarea id="store_description" name="store_description" class="form-control" rows="6" placeholder="Ceritakan tentang toko Anda, produk unggulan, proses pembuatan, dsb.">{{ old('store_description', $admin->store_description) }}</textarea>
                                        <small class="text-muted">Deskripsi ini akan tampil di halaman utama toko Anda.</small>
                                    </div>
                                </div>

                                <div class="row justify-content-end">
                                    <div class="col-sm-10">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="bx bx-save me-1"></i> Simpan Profil</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- TAB: ULASAN & KOMENTAR -->
                <div class="tab-pane fade {{ $tab == 'ulasan' ? 'show active' : '' }}" id="navs-top-ulasan" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom pb-3 pt-4 px-4 d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark"><i class="bx bx-star text-warning me-2"></i>Ulasan & Komentar Pembeli</h5>
                                <small class="text-muted">Kelola dan balas ulasan dari pembeli terhadap produk Anda.</small>
                            </div>
                        </div>
                        <div class="table-responsive text-nowrap p-3">
                            <table class="table table-hover border rounded-3 overflow-hidden">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th>Pembeli</th>
                                        <th>Rating</th>
                                        <th>Komentar</th>
                                        <th>Balasan Anda</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reviews as $review)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($review->produk->foto)
                                                    <img src="{{ Storage::url($review->produk->foto) }}" class="rounded me-2" width="40" height="40" style="object-fit:cover">
                                                @endif
                                                <strong>{{ $review->produk->nama_produk ?? '-' }}</strong>
                                            </div>
                                        </td>
                                        <td>{{ $review->user->name ?? 'Anonim' }}</td>
                                        <td>
                                            <span class="text-warning">
                                                @for($i=1; $i<=5; $i++)
                                                    <i class="bx {{ $i <= $review->rating ? 'bxs-star' : 'bx-star' }}"></i>
                                                @endfor
                                            </span>
                                        </td>
                                        <td style="white-space: normal; min-width: 250px;">
                                            <div class="bg-light p-2 rounded text-wrap">{{ $review->comment ?: '(Hanya memberikan rating)' }}</div>
                                        </td>
                                        <td style="white-space: normal; min-width: 250px;">
                                            @if($review->reply)
                                                <div class="bg-success-subtle text-success p-2 rounded text-wrap border border-success-subtle">
                                                    <em>"{{ $review->reply }}"</em>
                                                </div>
                                                <small class="text-muted mt-1 d-block"><i class="bx bx-time-five me-1"></i>{{ $review->replied_at->format('d M Y, H:i') }}</small>
                                            @else
                                                <span class="badge bg-label-secondary rounded-pill">Belum dibalas</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#replyModal{{ $review->id }}">
                                                <i class="bx bx-reply me-1"></i> Balas
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal Balas Ulasan -->
                                    <div class="modal fade" id="replyModal{{ $review->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <form action="{{ route('admin.unit.pasar_daerah.reply_review', $review->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-content border-0 shadow-lg">
                                                    <div class="modal-header border-bottom pb-3">
                                                        <h5 class="modal-title fw-bold" id="exampleModalLabel1"><i class="bx bx-reply text-primary me-2"></i>Balas Ulasan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body pt-4">
                                                        <div class="mb-4 bg-light p-3 rounded-3 border">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <span class="text-warning me-2">
                                                                    @for($i=1; $i<=5; $i++)
                                                                        <i class="bx {{ $i <= $review->rating ? 'bxs-star' : 'bx-star' }}"></i>
                                                                    @endfor
                                                                </span>
                                                                <strong>{{ $review->user->name ?? 'Anonim' }}</strong>
                                                            </div>
                                                            <p class="mb-0 text-wrap">{{ $review->comment ?: '(Tidak ada teks komentar)' }}</p>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col mb-3">
                                                                <label for="reply" class="form-label fw-bold">Komentar Balasan Anda</label>
                                                                <textarea id="reply" name="reply" class="form-control" rows="4" placeholder="Terima kasih atas ulasannya... " required>{{ $review->reply }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top pt-3">
                                                        <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill"><i class="bx bx-send me-1"></i> Kirim Balasan</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-label-secondary rounded-circle p-3 mb-3">
                                                    <i class="bx bx-star fs-1 text-muted"></i>
                                                </div>
                                                <h6 class="fw-bold mb-1">Belum ada ulasan</h6>
                                                <p class="text-muted mb-0">Produk Anda belum menerima ulasan dari pembeli.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 7: KOMPLAIN & RETUR -->
                <div class="tab-pane fade {{ $tab == 'komplain' ? 'show active' : '' }}" id="navs-top-komplain" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-danger-subtle text-danger rounded-circle me-3 d-flex justify-content-center align-items-center">
                                <i class="bx bx-shield-quarter fs-4"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Komplain & Retur Barang</h5>
                                <small class="text-muted">Kelola keluhan pembeli terkait barang rusak saat pengiriman, tidak sesuai, atau busuk/basi.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-label-primary rounded-circle me-2 d-flex align-items-center justify-content-center">
                                        <i class="bx bx-list-ul"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Total Komplain</small>
                                        <h5 class="mb-0 fw-bold">{{ $complaints->count() }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-label-warning rounded-circle me-2 d-flex align-items-center justify-content-center">
                                        <i class="bx bx-time-five"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Menunggu Tindakan</small>
                                        <h5 class="mb-0 fw-bold text-warning">{{ $complaints->where('status', 'pending')->count() }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-label-success rounded-circle me-2 d-flex align-items-center justify-content-center">
                                        <i class="bx bx-check-circle"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Disetujui (Ganti/Refund)</small>
                                        <h5 class="mb-0 fw-bold text-success">{{ $complaints->whereIn('status', ['approved_replacement', 'approved_refund'])->count() }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-label-danger rounded-circle me-2 d-flex align-items-center justify-content-center">
                                        <i class="bx bx-x-circle"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Ditolak</small>
                                        <h5 class="mb-0 fw-bold text-danger">{{ $complaints->where('status', 'rejected')->count() }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Complaints Table -->
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="py-3 ps-4">No. Pesanan / Tanggal</th>
                                        <th class="py-3">Pembeli</th>
                                        <th class="py-3">Alasan & Solusi Diminta</th>
                                        <th class="py-3">Bukti Foto</th>
                                        <th class="py-3">Status</th>
                                        <th class="py-3 text-center pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($complaints as $comp)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-primary">#{{ $comp->order->order_number ?? 'PSR-' . $comp->pasar_order_id }}</span>
                                                <small class="text-muted">{{ $comp->created_at->format('d M Y, H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-label-secondary rounded-circle me-2 d-flex align-items-center justify-content-center fw-bold">
                                                    {{ strtoupper(substr($comp->user->name ?? 'W', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold text-dark">{{ $comp->user->name ?? 'Warga' }}</h6>
                                                    <small class="text-muted">{{ $comp->order->phone ?? $comp->user->phone ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="fw-bold text-dark d-block text-truncate" style="max-width: 220px;" title="{{ $comp->reason }}">{{ $comp->reason }}</span>
                                                @if($comp->solution_requested === 'replacement')
                                                    <span class="badge bg-label-info mt-1"><i class="bx bx-refresh me-1"></i> Ganti Barang Baru</span>
                                                @else
                                                    <span class="badge bg-label-warning mt-1"><i class="bx bx-money me-1"></i> Refund Dana</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                @foreach([$comp->evidence_1, $comp->evidence_2, $comp->evidence_3] as $ev)
                                                    @if($ev)
                                                        <a href="{{ Storage::url($ev) }}" target="_blank" class="d-inline-block rounded-2 overflow-hidden border" style="width: 42px; height: 42px;">
                                                            <img src="{{ Storage::url($ev) }}" alt="Bukti" class="w-full h-full object-fit-cover" style="width:100%; height:100%; object-fit:cover;">
                                                        </a>
                                                    @endif
                                                @endforeach
                                                @if(!$comp->evidence_1 && !$comp->evidence_2 && !$comp->evidence_3)
                                                    <span class="text-muted text-xs">Tanpa foto</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($comp->status === 'pending')
                                                <span class="badge bg-label-warning"><i class="bx bx-time-five me-1"></i> Menunggu Tindakan</span>
                                            @elseif($comp->status === 'approved_replacement')
                                                <span class="badge bg-label-info"><i class="bx bx-refresh me-1"></i> Disetujui Ganti Barang</span>
                                            @elseif($comp->status === 'approved_refund')
                                                <span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i> Disetujui Refund</span>
                                            @elseif($comp->status === 'rejected')
                                                <span class="badge bg-label-danger"><i class="bx bx-x-circle me-1"></i> Ditolak</span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-4">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalComplaint{{ $comp->id }}">
                                                <i class="bx bx-edit-alt me-1"></i> Tinjau & Tindak
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal Tindakan Komplain -->
                                    <div class="modal fade" id="modalComplaint{{ $comp->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form action="{{ route('admin.unit.pasar_daerah.complaints.handle', $comp->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header border-bottom py-3 px-4 bg-light">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm bg-danger-subtle text-danger rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                                <i class="bx bx-shield-quarter"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="modal-title fw-bold text-dark mb-0">Tinjau Komplain Pesanan #{{ $comp->order->order_number ?? $comp->pasar_order_id }}</h5>
                                                                <small class="text-muted">Diajukan oleh: {{ $comp->user->name ?? 'Warga' }} • {{ $comp->created_at->format('d M Y, H:i') }}</small>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    
                                                    <div class="modal-body p-4">
                                                        <!-- Ringkasan Masalah -->
                                                        <div class="row g-3 mb-4">
                                                            <div class="col-md-6">
                                                                <div class="p-3 bg-light rounded-3 h-100">
                                                                    <label class="form-label text-xs fw-bold text-muted uppercase">Alasan Komplain</label>
                                                                    <div class="fw-bold text-dark fs-6">{{ $comp->reason }}</div>
                                                                    <div class="mt-2">
                                                                        <span class="text-xs text-muted">Solusi yang diminta:</span>
                                                                        @if($comp->solution_requested === 'replacement')
                                                                            <span class="badge bg-label-info ms-1">Ganti Barang Baru</span>
                                                                        @else
                                                                            <span class="badge bg-label-warning ms-1">Pengembalian Dana (Refund)</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="p-3 bg-light rounded-3 h-100">
                                                                    <label class="form-label text-xs fw-bold text-muted uppercase">Rincian Barang yang Dipesan</label>
                                                                    <div class="fw-bold text-dark">
                                                                        @if($comp->order && $comp->order->items->isNotEmpty())
                                                                            <ul class="list-unstyled mb-0 text-xs">
                                                                                @foreach($comp->order->items as $item)
                                                                                    <li>• {{ $item->produk->nama_produk ?? 'Produk' }} ({{ $item->quantity }}x) - Rp {{ number_format($item->price, 0, ',', '.') }}</li>
                                                                                @endforeach
                                                                            </ul>
                                                                            <div class="mt-1 fw-bold text-primary">Total: Rp {{ number_format($comp->order->grand_total, 0, ',', '.') }}</div>
                                                                        @else
                                                                            <span class="text-muted text-xs">Informasi pesanan tidak ditemukan</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Deskripsi Keluhan Warga -->
                                                        <div class="mb-4">
                                                            <label class="form-label text-xs fw-bold text-muted uppercase">Keterangan / Kronologi dari Pembeli</label>
                                                            <div class="p-3 bg-light rounded-3 text-dark text-sm border">
                                                                {{ $comp->description ?? 'Tidak ada keterangan tambahan.' }}
                                                            </div>
                                                        </div>

                                                        <!-- Foto Bukti Kerusakan -->
                                                        <div class="mb-4">
                                                            <label class="form-label text-xs fw-bold text-muted uppercase">Foto Bukti Kerusakan / Unboxing</label>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                @foreach([$comp->evidence_1, $comp->evidence_2, $comp->evidence_3] as $ev)
                                                                    @if($ev)
                                                                        <a href="{{ Storage::url($ev) }}" target="_blank" class="d-inline-block rounded-3 overflow-hidden border shadow-sm" style="width: 100px; height: 100px;">
                                                                            <img src="{{ Storage::url($ev) }}" alt="Bukti" class="w-full h-full object-fit-cover" style="width:100%; height:100%; object-fit:cover;">
                                                                        </a>
                                                                    @endif
                                                                @endforeach
                                                                @if(!$comp->evidence_1 && !$comp->evidence_2 && !$comp->evidence_3)
                                                                    <div class="text-muted text-xs p-2">Pembeli tidak melampirkan foto.</div>
                                                                @endif
                                                            </div>
                                                            <small class="text-muted">Klik foto untuk melihat ukuran penuh.</small>
                                                        </div>

                                                        <!-- Info Rekening (Jika Refund) -->
                                                        @if($comp->bank_name || $comp->bank_account_number)
                                                            <div class="mb-4 p-3 bg-warning-subtle rounded-3 border border-warning">
                                                                <label class="form-label text-xs fw-bold text-warning-emphasis uppercase mb-1"><i class="bx bx-credit-card me-1"></i> Rekening Tujuan Refund Pembeli</label>
                                                                <div class="row text-xs">
                                                                    <div class="col-sm-4"><strong>Bank / E-Wallet:</strong> {{ $comp->bank_name ?? '-' }}</div>
                                                                    <div class="col-sm-4"><strong>No. Rekening:</strong> {{ $comp->bank_account_number ?? '-' }}</div>
                                                                    <div class="col-sm-4"><strong>Atas Nama:</strong> {{ $comp->bank_account_name ?? '-' }}</div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <hr class="my-3">

                                                        <!-- Formulir Keputusan Admin Desa -->
                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold text-dark">Pilih Tindakan Resmi Admin Desa <span class="text-danger">*</span></label>
                                                            <div class="row g-2">
                                                                <div class="col-md-4">
                                                                    <input type="radio" class="btn-check" name="status" id="action_replace_{{ $comp->id }}" value="approved_replacement" {{ $comp->status === 'approved_replacement' ? 'checked' : '' }} required>
                                                                    <label class="btn btn-outline-info w-100 p-2 text-start rounded-3" for="action_replace_{{ $comp->id }}">
                                                                        <div class="fw-bold"><i class="bx bx-refresh me-1"></i> Ganti Barang Baru</div>
                                                                        <small class="text-muted text-xs">Kirim ulang barang pengganti</small>
                                                                    </label>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <input type="radio" class="btn-check" name="status" id="action_refund_{{ $comp->id }}" value="approved_refund" {{ $comp->status === 'approved_refund' ? 'checked' : '' }}>
                                                                    <label class="btn btn-outline-success w-100 p-2 text-start rounded-3" for="action_refund_{{ $comp->id }}">
                                                                        <div class="fw-bold"><i class="bx bx-check-circle me-1"></i> Setujui Refund Dana</div>
                                                                        <small class="text-muted text-xs">Kembalikan dana ke rekening pembeli</small>
                                                                    </label>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <input type="radio" class="btn-check" name="status" id="action_reject_{{ $comp->id }}" value="rejected" {{ $comp->status === 'rejected' ? 'checked' : '' }}>
                                                                    <label class="btn btn-outline-danger w-100 p-2 text-start rounded-3" for="action_reject_{{ $comp->id }}">
                                                                        <div class="fw-bold"><i class="bx bx-x-circle me-1"></i> Tolak Komplain</div>
                                                                        <small class="text-muted text-xs">Bukti tidak valid atau tidak sesuai SOP</small>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold text-dark">Catatan & Instruksi Respon Admin Desa <span class="text-danger">*</span></label>
                                                            <textarea name="admin_response" rows="3" class="form-control rounded-3" required placeholder="Contoh: Kami mohon maaf atas ketidaknyamanan ini. Barang pengganti akan kami kirimkan hari ini via kurir desa.">{{ $comp->admin_response }}</textarea>
                                                            <small class="text-muted">Catatan ini akan langsung terbaca oleh pembeli di aplikasi mobile / web.</small>
                                                        </div>

                                                        @if($comp->resolved_at)
                                                            <div class="p-2 bg-light rounded text-xs text-muted">
                                                                Terakhir diproses oleh <strong>{{ $comp->handler->name ?? 'Admin' }}</strong> pada {{ $comp->resolved_at->format('d M Y, H:i') }}.
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="modal-footer border-top py-3 px-4 bg-light">
                                                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                                            <i class="bx bx-check me-1"></i> Simpan & Kirim Tindakan
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="bg-label-success rounded-circle p-3 mb-3">
                                                    <i class="bx bx-shield-quarter fs-1 text-success"></i>
                                                </div>
                                                <h6 class="fw-bold mb-1">Tidak Ada Komplain</h6>
                                                <p class="text-muted mb-0">Semua pesanan berjalan lancar dan belum ada keluhan barang rusak / retur.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
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
