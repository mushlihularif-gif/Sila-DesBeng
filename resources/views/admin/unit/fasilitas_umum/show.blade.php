@extends('admin.layouts.admin')

@section('title', 'Detail Fasilitas - ' . $fasilitas->nama_fasilitas)

@include('admin.unit.partials.unit_product_styles')

@section('content')
@php
    $photos = collect([$fasilitas->foto, $fasilitas->foto_2, $fasilitas->foto_3])
        ->filter(fn ($p) => !empty($p))
        ->map(fn ($p) => asset('storage/' . $p))
        ->values();

    $statusLower = strtolower((string) ($fasilitas->status ?? 'tersedia'));
    $statusCfg = match ($statusLower) {
        'tersedia' => ['label' => 'Tersedia', 'badge' => 'bg-success', 'soft' => 'bg-label-success', 'icon' => 'bx-check-circle'],
        'disewa'   => ['label' => 'Sedang Dipakai', 'badge' => 'bg-warning text-dark', 'soft' => 'bg-label-warning', 'icon' => 'bx-time-five'],
        default    => ['label' => 'Tidak Tersedia', 'badge' => 'bg-danger', 'soft' => 'bg-label-danger', 'icon' => 'bx-error-circle'],
    };

    $satuanLabel = $fasilitas->satuan ?: 'Unit';
    $skemaBiaya = $fasilitas->effectivePricingScheme();
    $isBerbayar = $fasilitas->isPaidScheme();
    $pengurusList = $fasilitas->pengurus ?? collect();
    $statusBadgeHtml = '<span class="badge ' . $statusCfg['badge'] . ' rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 0.74rem;"><i class="bx ' . $statusCfg['icon'] . ' me-1"></i>' . e($statusCfg['label']) . '</span>';
    $stockBadgeHtml = '<span class="badge bg-dark bg-opacity-75 rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 0.74rem;">Kapasitas: ' . (int) $fasilitas->stok . ' ' . e($satuanLabel) . '</span>';
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">
                    <a href="{{ route('admin.unit.fasilitas_umum.index', ['tab' => 'gedung']) }}" class="text-muted text-decoration-none">Fasilitas Umum</a> /
                </span>
                Detail Fasilitas
            </h4>
            <p class="text-muted mb-0 small">Rincian gedung &amp; aset publik desa, skema pembiayaan, serta pengurus pemegang kunci.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-start justify-content-md-end flex-shrink-0">
            <a href="{{ route('admin.unit.fasilitas_umum.index', ['tab' => 'gedung']) }}"
               class="segi-back btn btn-outline-secondary rounded-pill px-3 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-arrow-back"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('admin.unit.fasilitas_umum.edit', $fasilitas->id) }}"
               class="btn btn-primary rounded-pill px-4 shadow-sm flex-grow-1 flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-edit-alt"></i>
                <span>Ubah Fasilitas</span>
            </a>
            <form action="{{ route('admin.unit.fasilitas_umum.destroy', $fasilitas->id) }}"
                  method="POST"
                  data-konfirmasi="Apakah Anda yakin ingin menghapus fasilitas ini?"
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
                        'galleryId' => 'galleryFasilitas-' . $fasilitas->id,
                        'photos' => $photos,
                        'fallbackIcon' => 'bx-building-house',
                        'altText' => $fasilitas->nama_fasilitas,
                        'statusBadgeHtml' => $statusBadgeHtml,
                        'stockBadgeHtml' => $stockBadgeHtml,
                    ])

                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge bg-label-primary rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.74rem;">
                                <i class="bx bx-category me-1"></i>{{ str_replace('&', 'dan', (string) ($fasilitas->kategori ?: 'Fasilitas')) }}
                            </span>
                            <span class="badge {{ $skemaBiaya === 'berbayar_semua' ? 'bg-label-primary' : ($skemaBiaya === 'berbayar' ? 'bg-label-info' : 'bg-label-success') }} rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.74rem;">
                                {{ $fasilitas->pricingSchemeLabel() }}
                            </span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">{{ $fasilitas->nama_fasilitas }}</h5>
                        @if($fasilitas->created_at)
                            <small class="text-muted d-inline-flex align-items-center gap-1">
                                <i class="bx bx-time-five"></i>Terdaftar sejak {{ $fasilitas->created_at->translatedFormat('d F Y') }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Info, Tarif, Deskripsi --}}
        <div class="col-12 col-lg-7">
            <div class="card up-card mb-4">
                <div class="up-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="up-icon-box bg-label-primary">
                            <i class="bx bx-building-house fs-5 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Informasi &amp; Pembiayaan Fasilitas</h6>
                            <small class="text-muted">Skema biaya peminjaman, kapasitas unit, dan alamat lokasi</small>
                        </div>
                    </div>
                    <span class="badge {{ $statusCfg['soft'] }} rounded-pill px-3 py-1.5 fw-semibold">
                        <i class="bx {{ $statusCfg['icon'] }} me-1"></i>{{ $statusCfg['label'] }}
                    </span>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="up-price-banner mb-4">
                        @if($skemaBiaya === 'berbayar_semua')
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                                <div>
                                    <span class="up-spec-label text-primary mb-1">Tarif Sewa Semua Acara</span>
                                    <div class="d-flex align-items-baseline flex-wrap gap-1.5">
                                        <h3 class="fw-bold text-primary mb-0">Rp {{ number_format((float) ($fasilitas->harga_sewa ?? 0), 0, ',', '.') }}</h3>
                                        <span class="text-muted small fw-medium">/ hari</span>
                                    </div>
                                </div>
                                <span class="badge bg-label-primary rounded-pill px-3 py-1.5 fw-semibold">
                                    Berbayar untuk Sosial &amp; Pribadi
                                </span>
                            </div>
                        @elseif($skemaBiaya === 'berbayar')
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                                <div>
                                    <span class="up-spec-label text-primary mb-1">Tarif Acara Komersial / Pribadi</span>
                                    <div class="d-flex align-items-baseline flex-wrap gap-1.5">
                                        <h3 class="fw-bold text-primary mb-0">Rp {{ number_format((float) ($fasilitas->harga_sewa ?? 0), 0, ',', '.') }}</h3>
                                        <span class="text-muted small fw-medium">/ hari</span>
                                    </div>
                                </div>
                                <span class="badge bg-label-success rounded-pill px-3 py-1.5 fw-semibold">
                                    Gratis untuk Kegiatan Sosial
                                </span>
                            </div>
                        @else
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                                <div>
                                    <span class="up-spec-label text-success mb-1">Status Biaya Peminjaman</span>
                                    <h4 class="fw-bold text-success mb-0">Gratis Sepenuhnya</h4>
                                </div>
                                <span class="badge bg-white text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold small">
                                    Acara Sosial &amp; Pribadi Gratis
                                </span>
                            </div>
                        @endif
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="up-spec-tile">
                                <span class="up-spec-label">Kapasitas / Jumlah</span>
                                <span class="fw-bold text-dark fs-6 d-block">
                                    {{ (int) $fasilitas->stok }} <span class="text-muted fw-normal small">{{ $satuanLabel }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="up-spec-tile">
                                <span class="up-spec-label">Kategori</span>
                                <span class="fw-bold text-dark small d-block">{{ str_replace('&', 'dan', (string) ($fasilitas->kategori ?: '-')) }}</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="up-spec-tile d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                                <div class="min-w-0">
                                    <span class="up-spec-label">Lokasi Fasilitas</span>
                                    <span class="fw-semibold text-dark small d-inline-flex align-items-start gap-1">
                                        <i class="bx bx-map-pin text-danger mt-0.5 flex-shrink-0"></i>
                                        <span>{{ $fasilitas->lokasi ?: 'Lokasi belum ditentukan' }}</span>
                                    </span>
                                </div>
                                @if($fasilitas->latitude && $fasilitas->longitude)
                                    <a href="https://www.google.com/maps?q={{ $fasilitas->latitude }},{{ $fasilitas->longitude }}"
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

            {{-- Deskripsi --}}
            <div class="card up-card mb-4">
                <div class="up-card-header d-flex align-items-center gap-2.5">
                    <div class="up-icon-box bg-label-info">
                        <i class="bx bx-detail fs-5 text-info"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Deskripsi &amp; Daya Tampung</h6>
                        <small class="text-muted">Keterangan fasilitas untuk pemohon peminjaman</small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="p-3 rounded-3 bg-light border text-dark small" style="white-space: pre-line; line-height: 1.65;">{{ $fasilitas->deskripsi ?: 'Belum ada deskripsi tambahan untuk fasilitas ini.' }}</div>
                </div>
            </div>
        </div>

        {{-- Pengurus & Pemegang Kunci --}}
        <div class="col-12">
            <div class="card up-card">
                <div class="up-card-header d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="up-icon-box bg-label-success">
                            <i class="bx bx-key fs-5 text-success"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Pengurus &amp; Pemegang Kunci Gedung</h6>
                            <small class="text-muted">Personil penanggung jawab serah terima kunci dan kesiapan fasilitas</small>
                        </div>
                    </div>
                    <span class="badge bg-label-success rounded-pill px-3 py-1.5 fw-semibold">
                        {{ $pengurusList->count() }} Pengurus Ditugaskan
                    </span>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if($pengurusList->count() > 0)
                        <div class="row g-3">
                            @foreach($pengurusList as $pengurus)
                                @php
                                    $cleanWa = preg_replace('/[^0-9]/', '', (string) ($pengurus->kontak ?? ''));
                                    $waUrl = $cleanWa ? ('https://wa.me/' . (str_starts_with($cleanWa, '0') ? '62' . substr($cleanWa, 1) : $cleanWa)) : null;
                                    $avatar = $pengurus->foto ? asset('storage/' . $pengurus->foto) : asset('Admin/img/avatars/pria.png');
                                    $isTersedia = ($pengurus->status === 'Tersedia');
                                @endphp
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="p-3 border rounded-3 bg-light bg-opacity-50 h-100 d-flex flex-column justify-content-between">
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="position-relative flex-shrink-0" style="width: 44px; height: 44px;">
                                                <img src="{{ $avatar }}"
                                                     alt="{{ $pengurus->nama }}"
                                                     class="rounded-circle border object-fit-cover w-100 h-100"
                                                     onerror="this.src='{{ asset('Admin/img/avatars/pria.png') }}'">
                                                <span class="position-absolute {{ $isTersedia ? 'bg-success' : 'bg-warning' }} border border-2 border-white rounded-circle" style="width: 11px; height: 11px; bottom: 0; right: 0;"></span>
                                            </div>
                                            <div class="flex-grow-1 min-w-0">
                                                <div class="d-flex align-items-center justify-content-between gap-1 mb-0.5">
                                                    <span class="fw-bold text-dark small text-truncate">{{ $pengurus->nama }}</span>
                                                    <span class="badge {{ $isTersedia ? 'bg-label-success' : 'bg-label-warning' }} rounded-pill px-2 py-0.5 flex-shrink-0" style="font-size: 0.66rem;">
                                                        {{ $pengurus->status }}
                                                    </span>
                                                </div>
                                                <small class="text-muted d-block">
                                                    <i class="bx bxl-whatsapp text-success me-0.5"></i>{{ $pengurus->kontak ?: 'Kontak belum diisi' }}
                                                </small>
                                            </div>
                                        </div>
                                        @if($waUrl)
                                            <div class="mt-3 pt-2 border-top">
                                                <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                                                   class="btn btn-sm btn-success rounded-pill w-100 d-inline-flex align-items-center justify-content-center gap-1">
                                                    <i class="bx bxl-whatsapp fs-5"></i>Hubungi WhatsApp
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 bg-light rounded-3 text-center border">
                            <div class="up-icon-box bg-white border mx-auto mb-2" style="width: 48px; height: 48px; border-radius: 50%;">
                                <i class="bx bx-key text-muted fs-4"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Pengelolaan Langsung Kantor Desa</h6>
                            <p class="text-muted small mb-3 mx-auto" style="max-width: 520px;">
                                Belum ada pengurus atau pemegang kunci khusus yang ditugaskan. Serah terima kunci dikoordinasikan langsung melalui kantor desa.
                            </p>
                            <a href="{{ route('admin.unit.fasilitas_umum.edit', $fasilitas->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bx bx-user-plus me-1"></i>Tugaskan Pengurus
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
