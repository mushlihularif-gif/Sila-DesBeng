@extends('admin.layouts.admin')

@section('title', 'Kabar dan Informasi Daerah & Pengumuman')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Sistem /</span> Kabar & Pengumuman
            </h4>
        </div>
    </div>

    <!-- Panduan -->
    <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex align-items-center p-4">
            <div class="me-3">
                <div class="bg-primary p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                    <i class="bx bx-broadcast fs-3"></i>
                </div>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-primary">Pusat Informasi Wilayah</h5>
                <p class="mb-0 text-primary" style="opacity: 0.85;">
                    Kelola dan publikasikan Berita Daerah (dokumentasi kegiatan) serta Pengumuman Warga (instruksi teknis) untuk memastikan warga selalu mendapatkan informasi terkini.
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
        .nav-align-top > .tab-content { padding: 0 !important; background: transparent !important; border: none !important; box-shadow: none !important; }
        .tab-pane { padding-top: 1rem; }
    </style>

    <div class="nav-align-top mb-4">
        <ul class="nav nav-pills gap-2 mb-4" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link {{ $tab == 'berita' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-berita" aria-controls="navs-top-berita" aria-selected="{{ $tab == 'berita' ? 'true' : 'false' }}">
                    <i class="bx bx-news me-2"></i> Berita Daerah
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link {{ $tab == 'pengumuman' ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#navs-top-pengumuman" aria-controls="navs-top-pengumuman" aria-selected="{{ $tab == 'pengumuman' ? 'true' : 'false' }}">
                    <i class="bx bx-bell me-2"></i> Pengumuman Warga
                </button>
            </li>
        </ul>
        
        <div class="tab-content">
            <!-- TAB 1: BERITA DAERAH -->
            <div class="tab-pane fade {{ $tab == 'berita' ? 'show active' : '' }}" id="navs-top-berita" role="tabpanel">
                
                <div class="card bg-label-info border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body d-flex align-items-start p-4">
                        <span class="text-info me-3 mt-1">
                            <i class="bx bx-info-circle fs-3"></i>
                        </span>
                        <div>
                            <h6 class="fw-bold mb-1 text-info">Kapan menggunakan Berita Daerah?</h6>
                            <p class="mb-2 text-info" style="opacity: 0.9;">Gunakan untuk mempublikasikan <b>dokumentasi kegiatan, acara yang telah/sedang berlangsung, atau pencapaian</b> desa/kecamatan Anda. Berita ini akan tampil untuk seluruh warga se-Kabupaten.</p>
                            <p class="mb-0 small text-info opacity-75"><i>Contoh: "Keseruan Lomba 17 Agustus di Desa X", "Senam Massal Hari Minggu", "Pembangunan Jalan Sukses Diselesaikan".</i></p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-primary-subtle text-primary rounded-circle me-3 d-flex justify-content-center align-items-center">
                            <i class="bx bx-news fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Daftar Berita Daerah</h5>
                            <small class="text-muted">Dokumentasi kegiatan dan informasi publik lintas wilayah.</small>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('admin.announcements.create', ['category' => 'Berita']) }}" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="bx bx-plus me-1"></i> Buat Berita</a>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body pb-0">
                        <form class="row g-3 align-items-end mb-4" method="GET">
                            <input type="hidden" name="tab" value="berita">
                            <div class="col-md-9">
                                <label class="form-label fw-semibold text-muted">Pencarian</label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text bg-light border-0"><i class="bx bx-search"></i></span>
                                    <input type="text" name="search" class="form-control bg-light border-0" placeholder="Cari judul berita..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-secondary w-100 rounded-3"><i class="bx bx-filter-alt me-1"></i> Cari Berita</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    @include('admin.announcements.partials.table', ['announcements' => $beritas, 'category' => 'Berita'])
                </div>
            </div>

            <!-- TAB 2: PENGUMUMAN WARGA -->
            <div class="tab-pane fade {{ $tab == 'pengumuman' ? 'show active' : '' }}" id="navs-top-pengumuman" role="tabpanel">
                
                <div class="card bg-label-warning border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body d-flex align-items-start p-4">
                        <span class="text-warning me-3 mt-1">
                            <i class="bx bx-error-circle fs-3"></i>
                        </span>
                        <div>
                            <h6 class="fw-bold mb-1 text-warning">Kapan menggunakan Pengumuman Warga?</h6>
                            <p class="mb-2 text-warning" style="opacity: 0.9;">Gunakan untuk memberikan <b>instruksi, peringatan, jadwal layanan, atau himbauan penting</b> yang ditujukan khusus untuk warga di wilayah tertentu.</p>
                            <p class="mb-0 small text-warning opacity-75"><i>Contoh: "Jadwal Posyandu Balita Bulan Ini", "Pemadaman Listrik Sementara Besok Pagi", "Himbauan Waspada Banjir".</i></p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-warning-subtle text-warning rounded-circle me-3 d-flex justify-content-center align-items-center">
                            <i class="bx bx-bell fs-4"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Daftar Pengumuman Warga</h5>
                            <small class="text-muted">Informasi teknis dan instruksi yang ditujukan untuk wilayah spesifik.</small>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('admin.announcements.create', ['category' => 'Pengumuman']) }}" class="btn btn-warning rounded-pill px-4 shadow-sm text-dark"><i class="bx bx-plus me-1"></i> Buat Pengumuman</a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body pb-0">
                        <form class="row g-3 align-items-end mb-4" method="GET">
                            <input type="hidden" name="tab" value="pengumuman">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-muted">Filter Tipe</label>
                                <select id="filter_type" name="type" class="form-select bg-light border-0" onchange="this.form.submit()">
                                    <option value="">Semua Tipe</option>
                                    <option value="Pengumuman" {{ request('type') == 'Pengumuman' ? 'selected' : '' }}>Pengumuman</option>
                                    <option value="Event" {{ request('type') == 'Event' ? 'selected' : '' }}>Event</option>
                                    <option value="Gotong Royong" {{ request('type') == 'Gotong Royong' ? 'selected' : '' }}>Gotong Royong</option>
                                </select>
                            </div>
                            @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-muted">Kecamatan</label>
                                <select id="filter_kecamatan_id" name="filter_kecamatan_id" class="form-select bg-light border-0" onchange="this.form.submit()">
                                    <option value="">-- Semua Kecamatan --</option>
                                    @foreach($kecamatanOptions as $opt)
                                        <option value="{{ $opt->id }}" {{ request('filter_kecamatan_id') == $opt->id ? 'selected' : '' }}>
                                            {{ $opt->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-muted">Desa/Kelurahan</label>
                                <select id="filter_desa_id" name="filter_desa_id" class="form-select bg-light border-0" onchange="this.form.submit()">
                                    <option value="">-- Semua Desa --</option>
                                    @foreach($desaOptions as $opt)
                                        <option value="{{ $opt->id }}" {{ request('filter_desa_id') == $opt->id ? 'selected' : '' }}>
                                            {{ $opt->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @elseif(auth()->user()->role === 'admin_kecamatan')
                            <div class="col-md-7">
                                <label class="form-label fw-semibold text-muted">Filter Desa/Kelurahan</label>
                                <select id="filter_desa_id" name="filter_desa_id" class="form-select bg-light border-0" onchange="this.form.submit()">
                                    <option value="">-- Semua Desa --</option>
                                    @foreach($desaOptions as $opt)
                                        <option value="{{ $opt->id }}" {{ request('filter_desa_id') == $opt->id ? 'selected' : '' }}>
                                            {{ $opt->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                            <div class="col-md-7">
                                <!-- Spacer untuk admin desa -->
                            </div>
                            @endif
                            
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-secondary w-100 rounded-3"><i class="bx bx-filter-alt me-1"></i> Terapkan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    @include('admin.announcements.partials.table', ['announcements' => $pengumumans, 'category' => 'Pengumuman'])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
