@extends('admin.layouts.admin')

@section('title', 'Detail Alat - ' . $barang->nama_barang)

@include('admin.unit.partials.unit_product_styles')

@section('content')
@php
    $photos = collect([$barang->foto, $barang->foto_2, $barang->foto_3])
        ->filter()
        ->map(fn ($p) => asset('storage/' . $p))
        ->values();

    $statusLower = strtolower((string) ($barang->status ?? 'tersedia'));
    $statusCfg = match ($statusLower) {
        'tersedia' => ['label' => 'Tersedia', 'badge' => 'bg-success', 'soft' => 'bg-label-success', 'icon' => 'bx-check-circle'],
        'disewa'   => ['label' => 'Sedang Disewa', 'badge' => 'bg-warning text-dark', 'soft' => 'bg-label-warning', 'icon' => 'bx-time-five'],
        default    => ['label' => ucfirst($statusLower ?: 'Rusak'), 'badge' => 'bg-danger', 'soft' => 'bg-label-danger', 'icon' => 'bx-error-circle'],
    };

    $isPerDay = $barang->billing_basis === 'per_day';
    $satuanLabel = $barang->satuan ?: 'Unit';
    $statusBadgeHtml = '<span class="badge ' . $statusCfg['badge'] . ' rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 0.74rem;"><i class="bx ' . $statusCfg['icon'] . ' me-1"></i>' . e($statusCfg['label']) . '</span>';
    $stockBadgeHtml = '<span class="badge bg-dark bg-opacity-75 rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 0.74rem;">Stok: ' . (int) $barang->stok . ' ' . e($satuanLabel) . '</span>';
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">
                    <a href="{{ route('admin.unit.penyewaan.index') }}" class="text-muted text-decoration-none">Penyewaan Alat</a> /
                </span>
                Detail Alat
            </h4>
            <p class="text-muted mb-0 small">Ringkasan spesifikasi, skema tarif sewa, stok, dan lokasi penyimpanan peralatan.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-start justify-content-md-end flex-shrink-0">
            <a href="{{ route('admin.unit.penyewaan.index') }}"
               class="segi-back btn btn-outline-secondary rounded-pill px-3 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-arrow-back"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('admin.unit.penyewaan.edit', $barang->id) }}"
               class="btn btn-primary rounded-pill px-4 shadow-sm flex-grow-1 flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-edit-alt"></i>
                <span>Ubah Alat</span>
            </a>
            <form action="{{ route('admin.unit.penyewaan.destroy', $barang->id) }}"
                  method="POST"
                  data-konfirmasi="Apakah Anda yakin ingin menghapus alat ini?"
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
        {{-- Kolom Kiri: Galeri & Ringkasan Cepat --}}
        <div class="col-12 col-lg-5">
            <div class="card up-card overflow-hidden">
                <div class="card-body p-3 p-md-4">
                    @include('admin.unit.partials.product_gallery', [
                        'galleryId' => 'galleryBarang-' . $barang->id,
                        'photos' => $photos,
                        'fallbackIcon' => 'bx-package',
                        'altText' => $barang->nama_barang,
                        'statusBadgeHtml' => $statusBadgeHtml,
                        'stockBadgeHtml' => $stockBadgeHtml,
                    ])

                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge bg-label-primary rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.74rem;">
                                <i class="bx bx-category me-1"></i>{{ $barang->kategori ?: 'Umum' }}
                            </span>
                            <span class="badge {{ $isPerDay ? 'bg-label-info' : 'bg-label-secondary' }} rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.74rem;">
                                <i class="bx {{ $isPerDay ? 'bx-calendar' : 'bx-calendar-check' }} me-1"></i>{{ $isPerDay ? 'Tarif Harian' : 'Sekali Kegiatan' }}
                            </span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">{{ $barang->nama_barang }}</h5>
                        @if($barang->created_at)
                            <small class="text-muted d-inline-flex align-items-center gap-1">
                                <i class="bx bx-time-five"></i>Terdaftar sejak {{ $barang->created_at->translatedFormat('d F Y') }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Tarif, Spesifikasi, Lokasi & Deskripsi --}}
        <div class="col-12 col-lg-7">
            {{-- Card Harga & Spesifikasi --}}
            <div class="card up-card mb-4">
                <div class="up-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="up-icon-box bg-label-primary">
                            <i class="bx bx-info-circle fs-5 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Informasi &amp; Tarif Penyewaan</h6>
                            <small class="text-muted">Detail harga sewa, ketersediaan stok, dan lokasi alat</small>
                        </div>
                    </div>
                    <span class="badge {{ $statusCfg['soft'] }} rounded-pill px-3 py-1.5 fw-semibold">
                        <i class="bx {{ $statusCfg['icon'] }} me-1"></i>{{ $statusCfg['label'] }}
                    </span>
                </div>

                <div class="card-body p-3 p-md-4">
                    {{-- Price Highlight --}}
                    <div class="up-price-banner mb-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <div>
                                <span class="up-spec-label text-primary mb-1">Harga Sewa per {{ $satuanLabel }}</span>
                                <div class="d-flex align-items-baseline flex-wrap gap-1.5">
                                    <h3 class="fw-bold text-primary mb-0">Rp {{ number_format((float) $barang->harga_sewa, 0, ',', '.') }}</h3>
                                    <span class="text-muted small fw-medium">/ {{ $satuanLabel }} &middot; {{ $isPerDay ? 'per hari' : 'sekali kegiatan' }}</span>
                                </div>
                            </div>
                            <span class="badge bg-white text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-semibold small">
                                {{ $isPerDay ? 'Dihitung × jumlah hari' : 'Flat per 1 kegiatan' }}
                            </span>
                        </div>
                    </div>

                    {{-- Key-Value Spec Grid --}}
                    <div class="row g-3">
                        <div class="col-6 col-sm-4">
                            <div class="up-spec-tile">
                                <span class="up-spec-label">Stok Tersedia</span>
                                <span class="fw-bold text-dark fs-6 d-block">
                                    {{ (int) $barang->stok }} <span class="text-muted fw-normal small">{{ $satuanLabel }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4">
                            <div class="up-spec-tile">
                                <span class="up-spec-label">Kategori</span>
                                <span class="fw-bold text-dark small d-block">{{ $barang->kategori ?: '-' }}</span>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="up-spec-tile">
                                <span class="up-spec-label">Perhitungan Tarif</span>
                                <span class="fw-bold text-dark small d-block">{{ $isPerDay ? 'Harian (Per Hari)' : 'Sekali Kegiatan' }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="up-spec-tile d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                                <div class="min-w-0">
                                    <span class="up-spec-label">Lokasi Penyimpanan / Pengambilan</span>
                                    <span class="fw-semibold text-dark small d-inline-flex align-items-start gap-1">
                                        <i class="bx bx-map-pin text-danger mt-0.5 flex-shrink-0"></i>
                                        <span>{{ $barang->lokasi ?: 'Lokasi belum ditentukan' }}</span>
                                    </span>
                                </div>
                                @if($barang->latitude && $barang->longitude)
                                    <a href="https://www.google.com/maps?q={{ $barang->latitude }},{{ $barang->longitude }}"
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

            {{-- Card Deskripsi --}}
            <div class="card up-card">
                <div class="up-card-header d-flex align-items-center gap-2.5">
                    <div class="up-icon-box bg-label-info">
                        <i class="bx bx-detail fs-5 text-info"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Deskripsi &amp; Kelengkapan Alat</h6>
                        <small class="text-muted">Informasi yang dilihat warga pada halaman detail katalog</small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="p-3 rounded-3 bg-light border text-dark small" style="white-space: pre-line; line-height: 1.65;">{{ $barang->deskripsi ?: 'Belum ada deskripsi tambahan untuk alat ini.' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
