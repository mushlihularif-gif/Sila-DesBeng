@extends('admin.layouts.admin')

@section('title', 'Detail Kendaraan Operasional')

@section('content')
@php
    $photos = collect(['foto', 'foto_2', 'foto_3'])
        ->map(fn ($f) => $kendaraan->$f)
        ->filter()
        ->values();
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">Kendaraan Operasional /</span> Detail Kendaraan
            </h4>
            <p class="text-muted mb-0 small">Informasi lengkap kendaraan operasional beserta supir penanggung jawab.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
            <a href="{{ route('admin.unit.kendaraan_operasional.index') }}" class="segi-back btn btn-outline-secondary rounded-pill px-3 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-arrow-back"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('admin.unit.kendaraan_operasional.edit', $kendaraan->id) }}" class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-edit-alt"></i>
                <span>Ubah Kendaraan</span>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary" style="width: 38px; height: 38px;">
                            <i class="bx bx-car fs-5 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">{{ $kendaraan->nama_mobil }}</h5>
                            <small class="text-muted">{{ $kendaraan->region?->name ?? 'Wilayah Desa' }}</small>
                        </div>
                    </div>
                    <span class="badge bg-label-primary rounded-pill px-3 py-1.5 fw-semibold">
                        {{ $kendaraan->plat_nomor ?: 'Plat belum diisi' }}
                    </span>
                </div>

                <div class="card-body p-4">
                    @if($photos->isNotEmpty())
                        <div class="row g-3 mb-4">
                            @foreach($photos as $photo)
                                <div class="col-12 col-sm-6 col-md-4">
                                    <img src="{{ asset('storage/' . $photo) }}" alt="Foto {{ $kendaraan->nama_mobil }}"
                                         class="rounded-3 border w-100 bg-light" style="height: 200px; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <div class="p-3 rounded-3 bg-light border h-100">
                                <small class="text-muted d-block mb-0.5" style="font-size: 0.72rem;">Nomor Polisi</small>
                                <span class="fw-bold text-dark">{{ $kendaraan->plat_nomor ?: 'Belum diisi' }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="p-3 rounded-3 bg-light border h-100">
                                <small class="text-muted d-block mb-0.5" style="font-size: 0.72rem;">Wilayah</small>
                                <span class="fw-bold text-dark">{{ $kendaraan->region?->name ?: '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 rounded-3 bg-light border">
                        <small class="text-muted d-block mb-1" style="font-size: 0.72rem;">Keterangan Kendaraan</small>
                        <div class="text-dark small" style="white-space: pre-line; line-height: 1.6;">{{ $kendaraan->deskripsi ?: 'Belum ada keterangan tambahan.' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom px-4 py-3 d-flex align-items-center gap-2.5">
                    <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-success" style="width: 36px; height: 36px;">
                        <i class="bx bx-user-check fs-5 text-success"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Supir Penanggung Jawab</h6>
                        <small class="text-muted">{{ $kendaraan->supirs->count() }} petugas ditugaskan</small>
                    </div>
                </div>
                <div class="card-body p-4">
                    @forelse($kendaraan->supirs as $supir)
                        <div class="d-flex align-items-center gap-3 p-2.5 rounded-3 border bg-light bg-opacity-50 mb-2">
                            <div class="d-flex align-items-center justify-content-center rounded-circle bg-label-primary flex-shrink-0" style="width: 36px; height: 36px;">
                                <i class="bx bx-user text-primary"></i>
                            </div>
                            <div class="min-w-0">
                                <span class="fw-bold text-dark small d-block text-truncate">{{ $supir->nama }}</span>
                                <small class="text-muted d-block">{{ $supir->kontak ?: 'Kontak belum diisi' }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3">
                            <i class="bx bx-user-x fs-2 text-muted opacity-50 d-block mb-1"></i>
                            <p class="text-muted small mb-0">Belum ada supir yang ditugaskan.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
