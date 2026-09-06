@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Unit Layanan /</span> Fasilitas Umum & Aset
                </h4>
            </div>
        </div>

        <!-- Panduan -->
        <div class="card bg-label-secondary border-0 shadow-none mb-3 mb-sm-4" style="border-radius: 12px;">
            <div class="card-body d-flex align-items-center p-3 p-sm-4">
                <div class="me-2 me-sm-3 flex-shrink-0">
                    <div class="bg-secondary rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm unit-icon-circle">
                        <i class="bx bx-building fs-4 fs-sm-3"></i>
                    </div>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 mb-sm-1 text-secondary fs-6 fs-sm-5">Manajemen Fasilitas Umum & Aset</h5>
                    <p class="mb-0 text-secondary small" style="opacity: 0.85; font-size: 0.8rem;">
                        Kelola data kendaraan operasional (Ambulans, Truk Sampah) dan fasilitas publik (Gedung Serbaguna, Lapangan) yang dapat diakses oleh warga.
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
            .unit-icon-circle {
                width: 40px;
                height: 40px;
                min-width: 40px;
            }
            @media (min-width: 576px) {
                .unit-icon-circle {
                    width: 52px;
                    height: 52px;
                    min-width: 52px;
                }
            }
            .nav-pills-scrollable {
                display: flex;
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                padding: 4px 6px;
                gap: 0.35rem;
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
                padding: 0.45rem 0.75rem; 
                font-size: 0.82rem;
                transition: all 0.25s ease; 
                border-radius: 50rem; 
                display: inline-flex;
                align-items: center;
                border: 1px solid #e2e8f0;
                background-color: #ffffff;
            }
            @media (min-width: 576px) {
                .nav-pills-scrollable .nav-link {
                    padding: 0.55rem 0.85rem; 
                    font-size: 0.875rem;
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
            .product-card { transition: all 0.3s ease; border: none; box-shadow: 0 0.125rem 0.25rem rgba(161, 172, 184, 0.2); border-radius: 1rem; }
            .product-card:hover { transform: translateY(-5px); box-shadow: 0 0.5rem 1rem rgba(161, 172, 184, 0.15); }
        </style>

        <div class="nav-align-top mb-3 mb-sm-4">
            <div class="bg-light p-1 rounded-pill mb-3 mb-sm-4 border border-light-subtle shadow-sm">
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
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 mb-sm-4 bg-white p-3 rounded-4 shadow-sm gap-2 gap-sm-3">
                        <div class="d-flex align-items-center me-sm-3">
                            <div class="avatar avatar-sm bg-info-subtle text-info rounded-circle me-2 me-sm-3 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i class="bx bx-car fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold fs-6 fs-sm-5">Daftar Kendaraan Layanan Masyarakat</h5>
                                <small class="text-muted d-none d-sm-block">Ambulans Darurat, Mobil Siaga, Truk Sampah, dll</small>
                            </div>
                        </div>
                        <div class="w-100 w-sm-auto">
                            <a href="{{ route('admin.unit.ambulans.create') }}" class="btn btn-primary rounded-pill px-3 px-sm-4 py-2 shadow-sm d-flex d-sm-inline-flex align-items-center justify-content-center fw-semibold w-100 w-sm-auto text-nowrap">
                                <i class="bx bx-plus me-1 fs-5"></i> Tambah Kendaraan
                            </a>
                        </div>
                    </div>

                    @if($mobils->count() > 0)
                        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2 g-md-3">
                            @foreach ($mobils as $mobil)
                                <div class="col">
                                    <div class="card h-100 product-card">
                                        <div class="position-relative">
                                            <div id="carouselMobil{{ $mobil->id }}" class="carousel slide" data-bs-ride="carousel">
                                                <div class="carousel-inner">
                                                    @if ($mobil->foto)
                                                    <div class="carousel-item active">
                                                        <img src="{{ asset('storage/' . $mobil->foto) }}" class="card-img-top"
                                                            alt="{{ $mobil->nama_mobil }}"
                                                            style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                    </div>
                                                    @endif
                                                    @if ($mobil->foto_2)
                                                        <div class="carousel-item {{ !$mobil->foto ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $mobil->foto_2) }}" class="card-img-top"
                                                                alt="{{ $mobil->nama_mobil }}"
                                                                style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                        </div>
                                                    @endif
                                                    @if ($mobil->foto_3)
                                                        <div class="carousel-item {{ !$mobil->foto && !$mobil->foto_2 ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $mobil->foto_3) }}" class="card-img-top"
                                                                alt="{{ $mobil->nama_mobil }}"
                                                                style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                        </div>
                                                    @endif
                                                </div>
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
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                                                <h5 class="card-title fw-bold text-capitalize mb-0">{{ $mobil->nama_mobil }}</h5>
                                                @if($mobil->kategori === 'ambulans')
                                                    <span class="badge bg-label-danger rounded-pill px-3 py-1 text-nowrap"><i class="bx bx-plus-medical me-1"></i>Ambulans</span>
                                                @else
                                                    <span class="badge bg-label-secondary rounded-pill px-3 py-1 text-nowrap"><i class="bx bx-car me-1"></i>Operasional</span>
                                                @endif
                                            </div>

                                            @php
                                                $plat = str_replace('Plat: ', '', $mobil->deskripsi);
                                            @endphp
                                            @if($plat && $plat !== '-')
                                                <div class="mb-2">
                                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1"><i class="bx bx-barcode me-1"></i>{{ $plat }}</span>
                                                </div>
                                            @endif

                                            @if($mobil->kategori === 'ambulans')
                                                <div class="mt-1 mb-2">
                                                    <small class="text-muted d-block mb-1 fw-semibold"><i class="bx bx-user-pin me-1"></i>Supir Siaga:</small>
                                                    @if($mobil->supirs && $mobil->supirs->count() > 0)
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @foreach($mobil->supirs as $supir)
                                                                <span class="badge bg-label-primary px-2 py-1" style="font-size: 0.75rem;" title="{{ $supir->kontak }}">{{ $supir->nama }}</span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="badge bg-label-warning px-2 py-1" style="font-size: 0.75rem;">Belum ada supir siaga</span>
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            <div class="mt-auto pt-3 border-top d-flex gap-2 flex-nowrap justify-content-center">
                                                <a href="{{ route('admin.unit.ambulans.edit', $mobil->id) }}"
                                                    class="btn btn-sm btn-outline-warning flex-grow-1"><i class="bx bx-edit me-1"></i> Edit</a>
                                                <form action="{{ route('admin.unit.ambulans.destroy', $mobil->id) }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus kendaraan ini?');" class="d-flex flex-grow-1 m-0 p-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bx bx-trash me-1"></i> Hapus</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if ($mobils->hasPages())
                            <div class="mt-4 d-flex justify-content-center">
                                {{ $mobils->links() }}
                            </div>
                        @endif
                    @else
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <div class="empty-state-icon mb-4">
                                    <i class="bx bx-car" style="font-size: 120px; color: #d1d5db;"></i>
                                </div>
                                <h3 class="fw-bold text-muted mb-3">Belum Ada Kendaraan</h3>
                                <p class="text-muted mb-4" style="max-width: 500px; margin: 0 auto;">
                                    Belum ada data kendaraan operasional (misalnya Ambulans Darurat).
                                </p>
                                <a href="{{ route('admin.unit.ambulans.create') }}" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                                    <i class="bx bx-plus-circle me-2"></i>Tambah Kendaraan
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 2: GEDUNG & RUANG PUBLIK -->
                <div class="tab-pane fade {{ $tab == 'gedung' ? 'show active' : '' }}" id="navs-top-gedung" role="tabpanel">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 mb-sm-4 bg-white p-3 rounded-4 shadow-sm gap-2 gap-sm-3">
                        <div class="d-flex align-items-center me-sm-3">
                            <div class="avatar avatar-sm bg-success-subtle text-success rounded-circle me-2 me-sm-3 d-flex justify-content-center align-items-center flex-shrink-0" style="width: 38px; height: 38px;">
                                <i class="bx bx-building-house fs-5"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold fs-6 fs-sm-5">Daftar Gedung & Infrastruktur Publik</h5>
                                <small class="text-muted d-none d-sm-block">Gedung Serbaguna, Balai Pertemuan, Lapangan, dll</small>
                            </div>
                        </div>
                        <div class="w-100 w-sm-auto">
                            <a href="{{ route('admin.unit.fasilitas_umum.create') }}" class="btn btn-success rounded-pill px-3 px-sm-4 py-2 shadow-sm d-flex d-sm-inline-flex align-items-center justify-content-center fw-semibold w-100 w-sm-auto text-nowrap">
                                <i class="bx bx-plus me-1 fs-5"></i> Tambah Gedung
                            </a>
                        </div>
                    </div>

                    @if($fasilitas->count() > 0)
                        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-2 g-md-3">
                            @foreach ($fasilitas as $item)
                                <div class="col">
                                    <div class="card h-100 product-card">
                                        <div class="position-relative">
                                            <div id="carouselExample{{ $item->id }}" class="carousel slide" data-bs-ride="carousel">
                                                <div class="carousel-inner">
                                                    @if ($item->foto)
                                                    <div class="carousel-item active">
                                                        <img src="{{ asset('storage/' . $item->foto) }}" class="card-img-top"
                                                            alt="{{ $item->nama_fasilitas }}"
                                                            style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                    </div>
                                                    @endif
                                                    @if ($item->foto_2)
                                                        <div class="carousel-item {{ !$item->foto ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $item->foto_2) }}" class="card-img-top"
                                                                alt="{{ $item->nama_fasilitas }}"
                                                                style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                        </div>
                                                    @endif
                                                    @if ($item->foto_3)
                                                        <div class="carousel-item {{ !$item->foto && !$item->foto_2 ? 'active' : '' }}">
                                                            <img src="{{ asset('storage/' . $item->foto_3) }}" class="card-img-top"
                                                                alt="{{ $item->nama_fasilitas }}"
                                                                style="aspect-ratio: 1/1; object-fit: cover; object-position: center; width: 100%;">
                                                        </div>
                                                    @endif
                                                </div>
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
                                            </div>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                                <h5 class="card-title fw-bold text-capitalize mb-0">{{ $item->nama_fasilitas }}</h5>
                                                <span class="badge bg-label-success rounded-pill px-3 text-nowrap">{{ $item->kategori }}</span>
                                            </div>
                                            
                                            <div class="mt-4 pt-3 border-top d-flex gap-1 flex-nowrap justify-content-center">
                                                <a href="{{ route('admin.unit.fasilitas_umum.show', $item->id) }}"
                                                    class="btn btn-sm btn-outline-info flex-grow-1"><i class="bx bx-info-circle"></i></a>
                                                <a href="{{ route('admin.unit.fasilitas_umum.edit', $item->id) }}"
                                                    class="btn btn-sm btn-outline-warning flex-grow-1"><i class="bx bx-edit"></i></a>
                                                <form action="{{ route('admin.unit.fasilitas_umum.destroy', $item->id) }}" method="POST"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus gedung/fasilitas ini?');" class="d-flex flex-grow-1 m-0 p-0">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100"><i class="bx bx-trash"></i></button>
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
        if (confirm('Apakah Anda yakin ingin mereset teks SOP ini ke versi bawaan?')) {
            document.getElementById('sop_' + type + '_text').value = defaultSops[type];
        }
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
