@extends('admin.layouts.admin')

@section('title', 'Detail Gas - ' . $gas->jenis_gas)

@include('admin.unit.partials.unit_product_styles')

@section('content')
@php
    $photos = collect([$gas->foto, $gas->foto_2, $gas->foto_3])
        ->filter()
        ->map(fn ($p) => asset('storage/' . $p))
        ->values();

    $statusLower = strtolower((string) ($gas->status ?? 'tersedia'));
    $statusCfg = match ($statusLower) {
        'tersedia' => ['label' => 'Tersedia', 'badge' => 'bg-success', 'soft' => 'bg-label-success', 'icon' => 'bx-check-circle'],
        'dipesan'  => ['label' => 'Sedang Dipesan', 'badge' => 'bg-warning text-dark', 'soft' => 'bg-label-warning', 'icon' => 'bx-time-five'],
        default    => ['label' => ucfirst($statusLower ?: 'Rusak'), 'badge' => 'bg-danger', 'soft' => 'bg-label-danger', 'icon' => 'bx-error-circle'],
    };

    $satuanLabel = $gas->satuan ?: 'Tabung';
    $isSubsidi = preg_match('/(?<![0-9])3\s*kg/i', (string) ($gas->jenis_gas ?? '')) === 1;
    $statusBadgeHtml = '<span class="badge ' . $statusCfg['badge'] . ' rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 0.74rem;"><i class="bx ' . $statusCfg['icon'] . ' me-1"></i>' . e($statusCfg['label']) . '</span>';
    $stockBadgeHtml = '<span class="badge bg-dark bg-opacity-75 rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 0.74rem;">Stok: ' . (int) $gas->stok . ' ' . e($satuanLabel) . '</span>';
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">
                    <a href="{{ route('admin.unit.penjualan_gas.index') }}" class="text-muted text-decoration-none">Penjualan Gas</a> /
                </span>
                Detail Produk Gas
            </h4>
            <p class="text-muted mb-0 small">Informasi harga satuan, stok tabung tersedia, kategori, dan lokasi pangkalan gas.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-start justify-content-md-end flex-shrink-0">
            <a href="{{ route('admin.unit.penjualan_gas.index') }}"
               class="segi-back btn btn-outline-secondary rounded-pill px-3 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-arrow-back"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('admin.unit.penjualan_gas.edit', $gas->id) }}"
               class="btn btn-primary rounded-pill px-4 shadow-sm flex-grow-1 flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-edit-alt"></i>
                <span>Ubah Produk</span>
            </a>
            <form action="{{ route('admin.unit.penjualan_gas.destroy', $gas->id) }}"
                  method="POST"
                  data-konfirmasi="Apakah Anda yakin ingin menghapus produk gas ini?"
                  class="flex-grow-1 flex-sm-grow-0 m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger rounded-pill px-3 w-100 d-inline-flex align-items-center justify-content-center gap-1">
                    <i class="bx bx-trash"></i>
                    <span>Hapus</span>
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        {{-- Kolom Kiri: Galeri Foto --}}
        <div class="col-12 col-lg-5">
            <div class="card up-card overflow-hidden">
                <div class="card-body p-3 p-md-4">
                    @include('admin.unit.partials.product_gallery', [
                        'galleryId' => 'galleryGas-' . $gas->id,
                        'photos' => $photos,
                        'fallbackIcon' => 'bxs-gas-pump',
                        'altText' => $gas->jenis_gas,
                        'statusBadgeHtml' => $statusBadgeHtml,
                        'stockBadgeHtml' => $stockBadgeHtml,
                    ])

                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge bg-label-primary rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.74rem;">
                                <i class="bx bx-category me-1"></i>{{ $gas->kategori ?: 'Gas LPG' }}
                            </span>
                            @if($isSubsidi)
                                <span class="badge bg-label-success rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.74rem;">
                                    <i class="bx bx-shield-quarter me-1"></i>Gas Subsidi 3 Kg
                                </span>
                            @endif
                        </div>
                        <h5 class="fw-bold text-dark mb-1">{{ $gas->jenis_gas }}</h5>
                        @if($gas->created_at)
                            <small class="text-muted d-inline-flex align-items-center gap-1">
                                <i class="bx bx-time-five"></i>Terdaftar sejak {{ $gas->created_at->translatedFormat('d F Y') }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Harga, Stok, Lokasi & Deskripsi --}}
        <div class="col-12 col-lg-7">
            <div class="card up-card mb-4">
                <div class="up-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="up-icon-box bg-label-primary">
                            <i class="bx bxs-gas-pump fs-5 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Informasi &amp; Harga Penjualan</h6>
                            <small class="text-muted">Detail harga satuan, stok tersedia, dan pangkalan penjualan</small>
                        </div>
                    </div>
                    <span class="badge {{ $statusCfg['soft'] }} rounded-pill px-3 py-1.5 fw-semibold">
                        <i class="bx {{ $statusCfg['icon'] }} me-1"></i>{{ $statusCfg['label'] }}
                    </span>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="up-price-banner mb-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <div>
                                <span class="up-spec-label text-primary mb-1">Harga Satuan</span>
                                <div class="d-flex align-items-baseline flex-wrap gap-1.5">
                                    <h3 class="fw-bold text-primary mb-0">Rp {{ number_format((float) $gas->harga_satuan, 0, ',', '.') }}</h3>
                                    <span class="text-muted small fw-medium">/ {{ $satuanLabel }}</span>
                                </div>
                            </div>
                            <span class="badge {{ (int) $gas->stok > 0 ? 'bg-label-success' : 'bg-label-danger' }} rounded-pill px-3 py-1.5 fw-semibold">
                                {{ (int) $gas->stok > 0 ? 'Siap Dipesan' : 'Stok Habis' }}
                            </span>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="up-spec-tile">
                                <span class="up-spec-label">Stok Tersedia</span>
                                <span class="fw-bold text-dark fs-6 d-block">
                                    {{ (int) $gas->stok }} <span class="text-muted fw-normal small">{{ $satuanLabel }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="up-spec-tile">
                                <span class="up-spec-label">Kategori</span>
                                <span class="fw-bold text-dark small d-block">{{ $gas->kategori ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="up-spec-tile d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                                <div class="min-w-0">
                                    <span class="up-spec-label">Lokasi Pangkalan / Pengambilan</span>
                                    <span class="fw-semibold text-dark small d-inline-flex align-items-start gap-1">
                                        <i class="bx bx-map-pin text-danger mt-0.5 flex-shrink-0"></i>
                                        <span>{{ $gas->lokasi ?: 'Lokasi belum ditentukan' }}</span>
                                    </span>
                                </div>
                                @if($gas->latitude && $gas->longitude)
                                    <a href="https://www.google.com/maps?q={{ $gas->latitude }},{{ $gas->longitude }}"
                                       target="_blank"
                                       rel="noopener"
                                       class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-shrink-0">
                                        <i class="bx bx-map-alt me-1"></i>Buka Peta
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card up-card">
                <div class="up-card-header d-flex align-items-center gap-2.5">
                    <div class="up-icon-box bg-label-info">
                        <i class="bx bx-detail fs-5 text-info"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Deskripsi Produk Gas</h6>
                        <small class="text-muted">Keterangan dan ketentuan pembelian untuk warga</small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="p-3 rounded-3 bg-light border text-dark small" style="white-space: pre-line; line-height: 1.65;">{{ $gas->deskripsi ?: 'Belum ada deskripsi tambahan untuk produk ini.' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
