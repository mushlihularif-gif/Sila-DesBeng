@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y py-2 py-sm-3">
        <!-- Page Header -->
        <div class="row mb-2 mb-sm-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold py-1 mb-0 fs-5 fs-sm-4">
                    <span class="text-muted fw-light">Unit Layanan /</span> Fasilitas Umum & Aset
                </h4>
            </div>
        </div>

        <!-- Panduan -->
        <div class="card bg-label-secondary border-0 shadow-none mb-2 mb-sm-3" style="border-radius: 12px;">
            <div class="card-body d-flex align-items-center p-2.5 p-sm-3">
                <div class="me-2 me-sm-3 flex-shrink-0">
                    <div class="bg-secondary rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm unit-icon-circle">
                        <i class="bx bx-building fs-5 fs-sm-4"></i>
                    </div>
                </div>
                <div class="min-w-0">
                    <h6 class="fw-bold mb-0 text-secondary" style="font-size: 0.9rem;">Manajemen Fasilitas Umum & Aset</h6>
                    <p class="mb-0 text-secondary small d-none d-sm-block" style="opacity: 0.85; font-size: 0.78rem;">
                        Kelola data kendaraan operasional (Ambulans, Truk Sampah) dan fasilitas publik (Gedung Serbaguna, Lapangan) yang dapat diakses oleh warga.
                    </p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center py-2 px-3 mb-2 mb-sm-3" role="alert">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                <div style="font-size: 0.85rem;">{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <style>
            .unit-icon-circle {
                width: 34px;
                height: 34px;
                min-width: 34px;
            }
            @media (min-width: 576px) {
                .unit-icon-circle {
                    width: 48px;
                    height: 48px;
                    min-width: 48px;
                }
            }
            .nav-pills-scrollable {
                display: flex;
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding: 3px 4px;
                gap: 0.3rem;
                scrollbar-width: none;
            }
            .nav-pills-scrollable::-webkit-scrollbar {
                display: none;
            }
            .nav-pills-scrollable .nav-item {
                flex: 1 1 0px;
                min-width: fit-content;
            }
            .nav-pills-scrollable .nav-link { 
                width: 100%;
                justify-content: center;
                white-space: nowrap;
                color: #64748b; 
                font-weight: 600; 
                padding: 0.4rem 0.65rem; 
                font-size: 0.78rem;
                transition: all 0.25s ease; 
                border-radius: 50rem; 
                display: inline-flex;
                align-items: center;
                border: 1px solid #e2e8f0;
                background-color: #ffffff;
            }
            @media (min-width: 576px) {
                .nav-pills-scrollable .nav-link {
                    padding: 0.5rem 0.85rem; 
                    font-size: 0.85rem;
                }
            }
            .nav-pills-scrollable .nav-link:hover { 
                background-color: #f8fafc; 
                color: #334155; 
                border-color: #cbd5e1;
            }
            .nav-pills-scrollable .nav-link.active { 
                background-color: #696cff; 
                color: #fff; 
                border-color: #696cff;
                box-shadow: 0 4px 10px rgba(105, 108, 255, 0.25); 
            }
            
            /* Responsive Product Cards */
            .product-card { 
                transition: all 0.3s ease; 
                border: none; 
                box-shadow: 0 0.125rem 0.25rem rgba(161, 172, 184, 0.15); 
                border-radius: 0.85rem; 
                overflow: hidden;
            }
            .product-card:hover { 
                transform: translateY(-3px); 
                box-shadow: 0 0.5rem 1rem rgba(161, 172, 184, 0.15); 
            }
            .product-card .card-img-top,
            .product-card .carousel-inner img {
                aspect-ratio: 16/10;
                object-fit: cover;
                object-position: center;
                width: 100%;
            }
            .product-card .carousel-placeholder {
                aspect-ratio: 16/10;
                width: 100%;
            }
            @media (min-width: 768px) {
                .product-card .card-img-top,
                .product-card .carousel-inner img,
                .product-card .carousel-placeholder {
                    aspect-ratio: 4/3;
                }
            }
            @media (max-width: 575.98px) {
                .product-card .card-img-top,
                .product-card .carousel-inner img,
                .product-card .carousel-placeholder {
                    height: 110px !important;
                    aspect-ratio: auto !important;
                }
                .product-card .card-body {
                    padding: 0.65rem !important;
                }
                .product-card .card-title {
                    font-size: 0.85rem !important;
                    min-height: auto !important;
                    line-height: 1.25 !important;
                    margin-bottom: 0.25rem !important;
                }
                .product-card .card-badge-kategori {
                    font-size: 0.65rem !important;
                    padding: 2px 6px !important;
                    line-height: 1.2 !important;
                }
                .product-card .card-price-badge {
                    font-size: 0.68rem !important;
                    padding: 2px 5px !important;
                }
                .product-card .card-status-badge {
                    font-size: 0.65rem !important;
                    padding: 2px 5px !important;
                }
                .product-card .card-btn-action {
                    padding: 0.3rem 0.4rem !important;
                    font-size: 0.72rem !important;
                }
            }
        </style>

        <div class="nav-align-top mb-2 mb-sm-3 w-100 overflow-hidden">
            <div class="bg-light p-1 rounded-pill mb-2 mb-sm-3 border border-light-subtle shadow-sm w-100 overflow-hidden">
                <ul class="nav nav-pills nav-pills-scrollable mb-0" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $tab == 'kendaraan' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-kendaraan" aria-controls="navs-top-kendaraan" aria-selected="{{ $tab == 'kendaraan' ? 'true' : 'false' }}">
                            <i class="bx bx-car me-1"></i> Kendaraan
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $tab == 'gedung' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-gedung" aria-controls="navs-top-gedung" aria-selected="{{ $tab == 'gedung' ? 'true' : 'false' }}">
                            <i class="bx bx-building-house me-1"></i> Gedung & Ruang Publik
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-pengaturan" aria-controls="navs-top-pengaturan" aria-selected="false">
                            <i class="bx bx-cog me-1"></i> Pengaturan & SOP
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link {{ $tab == 'chat' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-chat" aria-controls="navs-top-chat" aria-selected="{{ $tab == 'chat' ? 'true' : 'false' }}">
                            <i class="bx bx-chat me-1"></i> Layanan Pesan
                            @if(isset($totalUnreadChats) && $totalUnreadChats > 0)
                                <span class="badge rounded-pill bg-danger ms-1 px-1 py-0" style="font-size: 0.7rem;">{{ $totalUnreadChats }}</span>
                            @endif
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="tab-content">
                <!-- TAB 1: KENDARAAN OPERASIONAL -->
                <div class="tab-pane fade {{ $tab == 'kendaraan' ? 'show active' : '' }}" id="navs-top-kendaraan" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-3 bg-white p-2.5 p-sm-3 rounded-4 shadow-sm gap-2">
                        <div class="d-flex align-items-center min-w-0">
                            <div class="avatar avatar-sm bg-info-subtle text-info rounded-circle me-2 me-sm-3 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 34px; height: 34px;">
                                <i class="bx bx-car fs-5"></i>
                            </div>
                            <div class="min-w-0">
                                <h6 class="mb-0 fw-bold text-truncate" style="font-size: 0.95rem;">Daftar Kendaraan Layanan</h6>
                                <small class="text-muted d-none d-sm-block" style="font-size: 0.78rem;">Ambulans Darurat, Mobil Siaga, Truk Sampah, dll</small>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('admin.unit.ambulans.create') }}" class="btn btn-primary btn-sm rounded-pill px-2.5 px-sm-3 py-1.5 shadow-sm d-inline-flex align-items-center fw-semibold text-nowrap" style="font-size: 0.8rem;">
                                <i class="bx bx-plus me-1"></i> <span>Tambah</span><span class="d-none d-sm-inline ms-1">Kendaraan</span>
                            </a>
                        </div>
                    </div>

                    @if($mobils->count() > 0)
                        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2 g-md-3">
                            @foreach ($mobils as $mobil)
                                @php
                                    $validMobilPhotos = [];
                                    foreach (['foto', 'foto_2', 'foto_3'] as $mCol) {
                                        if (!empty($mobil->$mCol) && \Illuminate\Support\Facades\Storage::disk('public')->exists($mobil->$mCol)) {
                                            $validMobilPhotos[] = $mobil->$mCol;
                                        }
                                    }
                                    $mFotoCount = count($validMobilPhotos);
                                @endphp
                                <div class="col">
                                    <div class="card h-100 product-card shadow-sm border-0">
                                        <div class="position-relative">
                                            <div id="carouselMobil{{ $mobil->id }}" class="carousel slide" data-bs-ride="carousel">
                                                <div class="carousel-inner">
                                                    @if ($mFotoCount > 0)
                                                        @foreach ($validMobilPhotos as $idx => $photo)
                                                            <div class="carousel-item {{ $idx === 0 ? 'active' : '' }}">
                                                                <img src="{{ asset('storage/' . $photo) }}" class="card-img-top"
                                                                    alt="{{ $mobil->nama_mobil }}"
                                                                    onerror="this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center bg-light text-muted w-100 carousel-placeholder\'><i class=\'bx bx-car\' style=\'font-size: 48px; color: #cbd5e1;\'></i></div>';">
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="carousel-item active">
                                                            <div class="d-flex align-items-center justify-content-center bg-light text-muted w-100 carousel-placeholder">
                                                                <i class="bx bx-car" style="font-size: 48px; color: #cbd5e1;"></i>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($mFotoCount > 1)
                                                    <button class="carousel-control-prev" type="button"
                                                        data-bs-target="#carouselMobil{{ $mobil->id }}" data-bs-slide="prev">
                                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Previous</span>
                                                    </button>
                                                    <button class="carousel-control-next" type="button"
                                                        data-bs-target="#carouselMobil{{ $mobil->id }}" data-bs-slide="next">
                                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                        <span class="visually-hidden">Next</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-column p-2 p-sm-3">
                                            <div class="mb-1">
                                                <h5 class="card-title fw-bold text-capitalize mb-1 text-truncate" title="{{ $mobil->nama_mobil }}">
                                                    {{ $mobil->nama_mobil }}
                                                </h5>
                                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                                    @if($mobil->kategori === 'ambulans')
                                                        <span class="badge bg-label-danger rounded-pill card-badge-kategori"><i class="bx bx-plus-medical me-1"></i>Ambulans</span>
                                                    @else
                                                        <span class="badge bg-label-secondary rounded-pill card-badge-kategori"><i class="bx bx-car me-1"></i>Operasional</span>
                                                    @endif
                                                    @php
                                                        $plat = str_replace('Plat: ', '', $mobil->deskripsi);
                                                    @endphp
                                                    @if($plat && $plat !== '-')
                                                        <span class="badge bg-light text-dark border font-monospace card-status-badge">{{ $plat }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($mobil->kategori === 'ambulans')
                                                <div class="mt-1 mb-1.5 mb-sm-2">
                                                    <small class="text-muted d-block mb-0.5 fw-semibold" style="font-size: 0.7rem;"><i class="bx bx-user-pin me-1"></i>Supir Siaga:</small>
                                                    @if($mobil->supirs && $mobil->supirs->count() > 0)
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @foreach($mobil->supirs as $supir)
                                                                <span class="badge bg-label-primary card-status-badge text-truncate" style="max-width: 100%;" title="{{ $supir->kontak }}">{{ $supir->nama }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="badge bg-label-warning card-status-badge">Belum ada supir</span>
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            <div class="mt-auto pt-2 pt-sm-3 border-top d-flex gap-1 flex-nowrap justify-content-center">
                                                <a href="{{ route('admin.unit.ambulans.show', $mobil->id) }}"
                                                    class="btn btn-sm btn-outline-info flex-grow-1 card-btn-action text-nowrap d-inline-flex align-items-center justify-content-center" title="Lihat Detail">
                                                    <i class="bx bx-info-circle"></i><span class="d-none d-sm-inline ms-1">Detail</span>
                                                </a>
                                                <a href="{{ route('admin.unit.ambulans.edit', $mobil->id) }}"
                                                    class="btn btn-sm btn-outline-warning flex-grow-1 card-btn-action text-nowrap d-inline-flex align-items-center justify-content-center" title="Ubah">
                                                    <i class="bx bx-edit"></i><span class="d-none d-sm-inline ms-1">Edit</span>
                                                </a>
                                                <form action="{{ route('admin.unit.ambulans.destroy', $mobil->id) }}" method="POST"
                                                    data-konfirmasi="Apakah Anda yakin ingin menghapus kendaraan ini?" class="d-flex flex-grow-1 m-0 p-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100 card-btn-action text-nowrap d-inline-flex align-items-center justify-content-center" title="Hapus">
                                                        <i class="bx bx-trash"></i><span class="d-none d-sm-inline ms-1">Hapus</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if ($mobils->hasPages())
                            <div class="mt-3 d-flex justify-content-center">
                                {{ $mobils->links() }}
                            </div>
                        @endif
                    @else
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body text-center py-4 py-sm-5">
                                <div class="empty-state-icon mb-3">
                                    <i class="bx bx-car" style="font-size: 80px; color: #d1d5db;"></i>
                                </div>
                                <h5 class="fw-bold text-muted mb-2">Belum Ada Kendaraan</h5>
                                <p class="text-muted mb-3 small" style="max-width: 400px; margin: 0 auto;">
                                    Belum ada data kendaraan operasional (misalnya Ambulans Darurat).
                                </p>
                                <a href="{{ route('admin.unit.ambulans.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 shadow-sm">
                                    <i class="bx bx-plus-circle me-1"></i> Tambah Kendaraan
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 2: GEDUNG & RUANG PUBLIK -->
                <div class="tab-pane fade {{ $tab == 'gedung' ? 'show active' : '' }}" id="navs-top-gedung" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-2 mb-sm-3 bg-white p-2.5 p-sm-3 rounded-4 shadow-sm gap-2">
                        <div class="d-flex align-items-center min-w-0">
                            <div class="avatar avatar-sm bg-success-subtle text-success rounded-circle me-2 me-sm-3 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 34px; height: 34px;">
                                <i class="bx bx-building-house fs-5"></i>
                            </div>
                            <div class="min-w-0">
                                <h6 class="mb-0 fw-bold text-truncate" style="font-size: 0.95rem;">Daftar Gedung & Fasilitas</h6>
                                <small class="text-muted d-none d-sm-block" style="font-size: 0.78rem;">Gedung Serbaguna, Balai Pertemuan, Lapangan, dll</small>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('admin.unit.fasilitas_umum.create') }}" class="btn btn-success btn-sm rounded-pill px-2.5 px-sm-3 py-1.5 shadow-sm d-inline-flex align-items-center fw-semibold text-nowrap" style="font-size: 0.8rem;">
                                <i class="bx bx-plus me-1"></i> <span>Tambah</span><span class="d-none d-sm-inline ms-1">Gedung</span>
                            </a>
                        </div>
                    </div>

                    @if($fasilitas->count() > 0)
                        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2 g-md-3">
                            @foreach ($fasilitas as $item)
                                @php
                                    $validPhotos = [];
                                    foreach (['foto', 'foto_2', 'foto_3'] as $fCol) {
                                        if (!empty($item->$fCol) && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->$fCol)) {
                                            $validPhotos[] = $item->$fCol;
                                        }
                                    }
                                    $fotoCount = count($validPhotos);
                                @endphp
                                <div class="col">
                                    <div class="card h-100 product-card shadow-sm border-0">
                                        <div class="position-relative">
                                            <div id="carouselExample{{ $item->id }}" class="carousel slide" data-bs-ride="carousel">
                                                <div class="carousel-inner">
                                                    @if ($fotoCount > 0)
                                                        @foreach ($validPhotos as $idx => $photo)
                                                            <div class="carousel-item {{ $idx === 0 ? 'active' : '' }}">
                                                                <img src="{{ asset('storage/' . $photo) }}" class="card-img-top"
                                                                    alt="{{ $item->nama_fasilitas }}"
                                                                    onerror="this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center bg-light text-muted w-100 carousel-placeholder\'><i class=\'bx bx-building-house\' style=\'font-size: 48px; color: #cbd5e1;\'></i></div>';">
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="carousel-item active">
                                                            <div class="d-flex align-items-center justify-content-center bg-light text-muted w-100 carousel-placeholder">
                                                                <i class="bx bx-building-house" style="font-size: 48px; color: #cbd5e1;"></i>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($fotoCount > 1)
                                                <button class="carousel-control-prev" type="button"
                                                    data-bs-target="#carouselExample{{ $item->id }}" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button"
                                                    data-bs-target="#carouselExample{{ $item->id }}" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Next</span>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-column p-2 p-sm-3">
                                            <div class="mb-1">
                                                <h5 class="card-title fw-bold text-capitalize mb-1 text-truncate" title="{{ $item->nama_fasilitas }}">
                                                    {{ $item->nama_fasilitas }}
                                                </h5>
                                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                                    <span class="badge bg-label-success rounded-pill px-2 py-0.5 card-badge-kategori text-wrap text-start" style="line-height: 1.2; max-width: 100%; word-break: break-word;">
                                                        {{ $item->kategori }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 my-1.5">
                                                @if($item->status_biaya === 'berbayar' && $item->harga_sewa > 0)
                                                    <span class="badge bg-primary text-white fw-semibold card-price-badge">
                                                        Rp {{ number_format($item->harga_sewa, 0, ',', '.') }} <small class="d-none d-sm-inline" style="font-size: 0.65rem; font-weight: normal;">/ hari</small>
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-success fw-semibold card-price-badge">Gratis</span>
                                                @endif
                                                <span class="badge {{ $item->status === 'Tersedia' ? 'bg-label-info' : 'bg-label-secondary' }} card-status-badge">{{ $item->status ?? 'Tersedia' }}</span>
                                            </div>

                                            @if($item->pengurus && $item->pengurus->count() > 0)
                                                <div class="mt-0.5 mb-1.5">
                                                    <small class="text-muted d-block mb-0.5 fw-semibold" style="font-size: 0.7rem;"><i class="bx bx-key me-1"></i>Pengurus / Kunci:</small>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($item->pengurus as $p)
                                                            <span class="badge bg-label-secondary card-status-badge text-truncate" style="max-width: 100%;" title="{{ $p->kontak }}">{{ $p->nama }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <div class="mt-auto pt-2 pt-sm-3 border-top d-flex gap-1 flex-nowrap justify-content-center">
                                                <a href="{{ route('admin.unit.fasilitas_umum.show', $item->id) }}"
                                                    class="btn btn-sm btn-outline-info flex-grow-1 card-btn-action text-nowrap d-inline-flex align-items-center justify-content-center" title="Lihat Detail">
                                                    <i class="bx bx-info-circle"></i><span class="d-none d-sm-inline ms-1">Detail</span>
                                                </a>
                                                <a href="{{ route('admin.unit.fasilitas_umum.edit', $item->id) }}"
                                                    class="btn btn-sm btn-outline-warning flex-grow-1 card-btn-action text-nowrap d-inline-flex align-items-center justify-content-center" title="Ubah">
                                                    <i class="bx bx-edit"></i><span class="d-none d-sm-inline ms-1">Edit</span>
                                                </a>
                                                <form action="{{ route('admin.unit.fasilitas_umum.destroy', $item->id) }}" method="POST"
                                                    data-konfirmasi="Apakah Anda yakin ingin menghapus gedung/fasilitas ini?" class="d-flex flex-grow-1 m-0 p-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100 card-btn-action text-nowrap d-inline-flex align-items-center justify-content-center" title="Hapus">
                                                        <i class="bx bx-trash"></i><span class="d-none d-sm-inline ms-1">Hapus</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        
                        @if ($fasilitas->hasPages())
                            <div class="mt-4 d-flex justify-content-center">
                                {{ $fasilitas->links() }}
                            </div>
                        @endif
                    @else
                        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                            <div class="card-body">
                                <div class="mb-4">
                                    <i class="bx bx-building text-secondary" style="font-size: 80px; opacity: 0.5;"></i>
                                </div>
                                <h4 class="fw-bold">Belum Ada Fasilitas</h4>
                                <p class="text-muted mb-4">Mulai kelola infrastruktur desa dengan menambahkan fasilitas baru.</p>
                                <a href="{{ route('admin.unit.fasilitas_umum.create') }}" class="btn btn-success rounded-pill px-4">Tambah Gedung</a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 3: PENGATURAN SOP -->
                <div class="tab-pane fade" id="navs-top-pengaturan" role="tabpanel">
                    <form action="{{ route('admin.unit.fasilitas_umum.sop.update') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-xl-8">
                                <!-- SOP Card -->
                                <div class="card border-0 shadow-sm rounded-4 mb-4">
                                    <div class="card-header bg-white border-bottom p-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-label-info rounded-circle me-3 d-flex justify-content-center align-items-center"><i class="bx bx-book"></i></div>
                                            <h5 class="mb-0 fw-bold">SOP Peminjaman Gedung & Kendaraan</h5>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="alert alert-info d-flex align-items-start mb-4 shadow-sm border-0 rounded-4 p-3 text-dark">
                                            <i class="bx bx-info-circle fs-4 me-3 mt-1"></i>
                                            <div>
                                                <strong class="d-block mb-1">Informasi Penting</strong>
                                                <span>SOP ini akan ditampilkan kepada masyarakat sebagai syarat dan ketentuan sebelum mereka mengajukan peminjaman. Pilih salah satu model kebijakan di bawah ini.</span>
                                            </div>
                                        </div>

                                        <style>
                                            .sop-card {
                                                transition: all 0.2s ease-in-out;
                                                border: 2px solid #ffab00 !important;
                                                background-color: #fff3cd !important;
                                            }
                                            .sop-card.active-sop {
                                                border-width: 2px !important;
                                                box-shadow: 0 0.25rem 1rem rgba(255, 171, 0, 0.4) !important;
                                            }
                                            .sop-icon {
                                                color: #ffab00;
                                                font-size: 1.25rem;
                                                vertical-align: middle;
                                            }
                                        </style>

                                        <div class="row mb-4">
                                            <!-- Opsi A: Ditanggung -->
                                            <div class="col-md-6 mb-3">
                                                <div class="card sop-card {{ $sop_active == 'ditanggung' ? 'active-sop' : '' }} h-100">
                                                    <div class="card-header d-flex justify-content-between align-items-center pb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="sop_active" id="sop_active_ditanggung" value="ditanggung" {{ $sop_active == 'ditanggung' ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bold text-dark" for="sop_active_ditanggung">
                                                                <i class="bx bx-error sop-icon"></i> <span class="align-middle">PENTING: Ditanggung Penyewa</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="text-muted small mb-2">Kerusakan fasilitas/kendaraan sepenuhnya menjadi tanggung jawab penyewa.</p>
                                                        
                                                        <div class="mb-3">
                                                            <textarea class="form-control border-light-subtle shadow-sm rounded-3" name="sop_ditanggung" id="sop_ditanggung_text" rows="8">{{ $sop_ditanggung }}</textarea>
                                                        </div>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 shadow-sm" onclick="resetSop('ditanggung')">
                                                            <i class="bx bx-reset"></i> Reset ke Bawaan
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Opsi B: Tidak Ditanggung -->
                                            <div class="col-md-6 mb-3">
                                                <div class="card sop-card {{ $sop_active == 'tidak_ditanggung' ? 'active-sop' : '' }} h-100">
                                                    <div class="card-header d-flex justify-content-between align-items-center pb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="sop_active" id="sop_active_tidak_ditanggung" value="tidak_ditanggung" {{ $sop_active == 'tidak_ditanggung' ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bold text-dark" for="sop_active_tidak_ditanggung">
                                                                <i class="bx bx-error sop-icon"></i> <span class="align-middle">PENTING: Ditanggung Dana Desa</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <p class="text-muted small mb-2">Kerusakan tidak disengaja ditanggung oleh Dana Operasional (Gratis).</p>
                                                        
                                                        <div class="mb-3">
                                                            <textarea class="form-control border-light-subtle shadow-sm rounded-3" name="sop_tidak_ditanggung" id="sop_tidak_ditanggung_text" rows="8">{{ $sop_tidak_ditanggung }}</textarea>
                                                        </div>
                                                        
                                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 shadow-sm" onclick="resetSop('tidak_ditanggung')">
                                                            <i class="bx bx-reset"></i> Reset ke Bawaan
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="bx bx-save me-1"></i> Simpan Pengaturan</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="card bg-primary text-white border-0 shadow-sm rounded-4 mb-4">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold text-white mb-3"><i class="bx bx-bulb me-2"></i>Tips Menulis SOP</h5>
                                        <ul class="list-unstyled mb-0" style="opacity: 0.9">
                                            <li class="mb-2"><i class="bx bx-check-circle me-2"></i>Sertakan biaya (jika ada) atau infokan jika gratis</li>
                                            <li class="mb-2"><i class="bx bx-check-circle me-2"></i>Jelaskan prosedur pengembalian aset/kendaraan</li>
                                            <li class="mb-2"><i class="bx bx-check-circle me-2"></i>Aturan denda jika terjadi kerusakan/keterlambatan</li>
                                            <li class="mb-2"><i class="bx bx-check-circle me-2"></i>Syarat dokumen yang harus dibawa warga</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TAB 4: CHAT WARGA FASILITAS -->
                <div class="tab-pane fade {{ $tab == 'chat' ? 'show active' : '' }}" id="navs-top-chat" role="tabpanel">
                    @include('admin.unit.partials.unit_chat_panel', [
                        'serviceType' => 'fasilitas_umum',
                        'chatServiceTitle' => 'Fasilitas Umum & Ruang Publik',
                        'chats' => $chats
                    ])
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // SOP Teks Bawaan
    const defaultSops = {
        'ditanggung': @json($default_ditanggung ?? ''),
        'tidak_ditanggung': @json($default_tidak_ditanggung ?? '')
    };

    function resetSop(type) {
        konfirmasi({
            judul: 'Reset Teks SOP',
            pesan: 'Teks SOP akan dikembalikan ke versi bawaan. Perubahan yang belum disimpan akan hilang.',
            jenis: 'bahaya',
            tombolYa: 'Ya, Reset'
        }).then(function (setuju) {
            if (setuju) document.getElementById('sop_' + type + '_text').value = defaultSops[type];
        });
    }

    // Interactive Card selection
    document.querySelectorAll('input[name="sop_active"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            // Reset border dari semua card
            document.querySelectorAll('.sop-card').forEach(function(card) {
                card.classList.remove('active-sop');
            });
            // Tambahkan border orange menyala ke card yang dipilih
            if(this.checked) {
                this.closest('.sop-card').classList.add('active-sop');
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    .card {
        transition: transform 0.2s ease;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.03);
    }
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.08);
    }
    .pagination .page-link {
        color: #495057;
        border: 1px solid #dee2e6;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }
    .pagination .page-link:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
    }
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }
    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
    }
</style>
@endpush
