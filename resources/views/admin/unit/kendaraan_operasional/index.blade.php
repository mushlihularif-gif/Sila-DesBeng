@extends('admin.layouts.admin')

@section('title', 'Kendaraan Operasional')

@section('content')
@php
    $totalKendaraan = $kendaraans->total();
    $denganSupir = $kendaraans->getCollection()->filter(fn ($k) => $k->supirs->isNotEmpty())->count();
    $tanpaSupir = $kendaraans->getCollection()->filter(fn ($k) => $k->supirs->isEmpty())->count();
@endphp

<style>
    .ko-card {
        border: 1px solid #eef1f6;
        border-radius: 16px;
        background: #ffffff;
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }
    .ko-card:hover {
        transform: translateY(-2px);
        border-color: #dbeafe;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.07) !important;
    }
    .ko-thumb {
        height: 185px;
        background: #f8fafc;
        position: relative;
        overflow: hidden;
    }
    .ko-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">Unit Layanan / Fasilitas Umum /</span> Kendaraan Operasional
            </h4>
            <p class="text-muted mb-0 small">
                Kelola inventaris kendaraan operasional desa beserta penugasan supir penanggung jawabnya.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
            <a href="{{ route('admin.unit.fasilitas_umum.index') }}" class="segi-back btn btn-outline-secondary rounded-pill px-3 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-arrow-back"></i>
                <span>Fasilitas Umum</span>
            </a>
            <a href="{{ route('admin.unit.kendaraan_operasional.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-plus fs-5"></i>
                <span>Tambah Kendaraan</span>
            </a>
        </div>
    </div>

    {{-- Flash Alerts --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bx bx-check-circle fs-4 flex-shrink-0"></i>
            <div class="flex-grow-1">{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    {{-- Banner + 3 KPI --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card bg-label-primary border-0 shadow-none rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bx bx-car fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Inventaris Kendaraan Operasional</h6>
                        <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                            Data kendaraan di halaman ini khusus untuk inventaris operasional desa (terpisah dari armada sewa berbayar). Anda dapat menetapkan satu atau lebih supir penanggung jawab untuk tiap kendaraan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 d-flex flex-column justify-content-center text-center">
                            <span class="text-muted small fw-semibold mb-1" style="font-size: 0.72rem;">Total Unit</span>
                            <h4 class="fw-bold text-primary mb-0">{{ $totalKendaraan }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 d-flex flex-column justify-content-center text-center">
                            <span class="text-muted small fw-semibold mb-1" style="font-size: 0.72rem;">Ada Supir</span>
                            <h4 class="fw-bold text-success mb-0">{{ $denganSupir }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-3 d-flex flex-column justify-content-center text-center">
                            <span class="text-muted small fw-semibold mb-1" style="font-size: 0.72rem;">Tanpa Supir</span>
                            <h4 class="fw-bold text-warning mb-0">{{ $tanpaSupir }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search Toolbar --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" class="d-flex flex-column flex-sm-row gap-2">
                <div class="input-group flex-grow-1">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bx bx-search"></i>
                    </span>
                    <input name="search" value="{{ $search }}" maxlength="100" placeholder="Cari nama kendaraan operasional..." class="form-control bg-light border-start-0">
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1 flex-sm-grow-0">Cari</button>
                    @if(!empty($search))
                        <a href="{{ route('admin.unit.kendaraan_operasional.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Vehicle Cards Grid --}}
    <div class="row g-3">
        @forelse($kendaraans as $kendaraan)
            <div class="col-12 col-md-6 col-xl-4">
                <article class="card ko-card shadow-sm h-100 d-flex flex-column">
                    <div class="ko-thumb">
                        @if($kendaraan->foto)
                            <img src="{{ asset('storage/' . $kendaraan->foto) }}" alt="{{ $kendaraan->nama_mobil }}">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                <i class="bx bx-car" style="font-size: 3.2rem; opacity: 0.35;"></i>
                            </div>
                        @endif
                        <span class="badge bg-white text-dark border shadow-sm rounded-pill position-absolute top-0 end-0 m-2.5 px-2.5 py-1 fw-semibold" style="font-size: 0.72rem;">
                            {{ $kendaraan->plat_nomor ?: 'Plat belum diisi' }}
                        </span>
                    </div>

                    <div class="card-body p-4 d-flex flex-column flex-grow-1">
                        <h6 class="fw-bold text-dark mb-1 text-break">{{ $kendaraan->nama_mobil }}</h6>
                        @if($kendaraan->deskripsi)
                            <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $kendaraan->deskripsi }}
                            </p>
                        @else
                            <p class="text-muted small fst-italic mb-3">Belum ada keterangan tambahan.</p>
                        @endif

                        <div class="p-2.5 rounded-3 bg-light border mb-3 mt-auto">
                            <small class="text-muted d-block mb-0.5" style="font-size: 0.7rem;">
                                <i class="bx bx-user-check text-primary me-1"></i>Supir Penanggung Jawab
                            </small>
                            <span class="small fw-semibold text-dark d-block text-break">
                                {{ $kendaraan->supirs->pluck('nama')->join(', ') ?: 'Belum ditugaskan' }}
                            </span>
                        </div>

                        <div class="d-flex gap-2">
                            <a class="btn btn-sm btn-outline-primary rounded-pill flex-fill fw-semibold" href="{{ route('admin.unit.kendaraan_operasional.show', $kendaraan->id) }}">
                                <i class="bx bx-show me-1"></i>Detail
                            </a>
                            <a class="btn btn-sm btn-primary rounded-pill flex-fill fw-semibold" href="{{ route('admin.unit.kendaraan_operasional.edit', $kendaraan->id) }}">
                                <i class="bx bx-edit-alt me-1"></i>Ubah
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light mb-3" style="width: 64px; height: 64px;">
                            <i class="bx bx-car fs-1 text-muted"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">Belum ada kendaraan operasional</h6>
                        <p class="text-muted small mb-3">Tambahkan kendaraan operasional wilayah Anda untuk memudahkan pendataan dan penugasan supir.</p>
                        <a href="{{ route('admin.unit.kendaraan_operasional.create') }}" class="btn btn-primary rounded-pill px-4">
                            <i class="bx bx-plus me-1"></i>Tambah Kendaraan
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($kendaraans->hasPages())
        <div class="mt-4 d-flex justify-content-center">{{ $kendaraans->links() }}</div>
    @endif
</div>
@endsection
