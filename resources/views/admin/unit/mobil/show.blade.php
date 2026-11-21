@extends('admin.layouts.admin')

@section('title', 'Detail Transportasi - ' . $mobil->nama_mobil)

@include('admin.unit.partials.unit_product_styles')

@section('content')
@php
    $photos = collect([$mobil->foto, $mobil->foto_2, $mobil->foto_3])
        ->filter()
        ->map(fn ($p) => asset('storage/' . $p))
        ->values();

    $statusLower = strtolower((string) ($mobil->status ?? 'tersedia'));
    $statusCfg = match ($statusLower) {
        'tersedia' => ['label' => 'Tersedia', 'badge' => 'bg-success', 'soft' => 'bg-label-success', 'icon' => 'bx-check-circle'],
        'disewa'   => ['label' => 'Sedang Disewa', 'badge' => 'bg-warning text-dark', 'soft' => 'bg-label-warning', 'icon' => 'bx-time-five'],
        default    => ['label' => ucfirst($statusLower ?: 'Rusak'), 'badge' => 'bg-danger', 'soft' => 'bg-label-danger', 'icon' => 'bx-error-circle'],
    };

    $satuanLabel = $mobil->satuan ?: 'Unit';
    $statusBadgeHtml = '<span class="badge ' . $statusCfg['badge'] . ' rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 0.74rem;"><i class="bx ' . $statusCfg['icon'] . ' me-1"></i>' . e($statusCfg['label']) . '</span>';
    $stockBadgeHtml = '<span class="badge bg-dark bg-opacity-75 rounded-pill px-3 py-1.5 shadow-sm fw-semibold" style="font-size: 0.74rem;">Stok: ' . (int) $mobil->stok . ' ' . e($satuanLabel) . '</span>';
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">
                    <a href="{{ route('admin.unit.mobil.index') }}" class="text-muted text-decoration-none">Penyewaan Transportasi</a> /
                </span>
                Detail Kendaraan
            </h4>
            <p class="text-muted mb-0 small">Informasi armada, skema tarif sewa harian &amp; borongan, serta ketentuan supir dan BBM.</p>
        </div>

        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-start justify-content-md-end flex-shrink-0">
            <a href="{{ route('admin.unit.mobil.index') }}"
               class="segi-back btn btn-outline-secondary rounded-pill px-3 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-arrow-back"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('admin.unit.mobil.edit', $mobil->id) }}"
               class="btn btn-primary rounded-pill px-4 shadow-sm flex-grow-1 flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-edit-alt"></i>
                <span>Ubah Kendaraan</span>
            </a>
            <form action="{{ route('admin.unit.mobil.destroy', $mobil->id) }}"
                  method="POST"
                  data-konfirmasi="Apakah Anda yakin ingin menghapus kendaraan ini?"
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
        {{-- Kolom Kiri: Galeri & Identitas Armada --}}
        <div class="col-12 col-lg-5 col-xl-4">
            <div class="card up-card overflow-hidden">
                <div class="card-body p-3 p-md-4">
                    @include('admin.unit.partials.product_gallery', [
                        'galleryId' => 'galleryMobil-' . $mobil->id,
                        'photos' => $photos,
                        'fallbackIcon' => 'bx-car',
                        'altText' => $mobil->nama_mobil,
                        'statusBadgeHtml' => $statusBadgeHtml,
                        'stockBadgeHtml' => $stockBadgeHtml,
                    ])

                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge bg-label-primary rounded-pill px-2.5 py-1 fw-semibold font-monospace" style="font-size: 0.74rem;">
                                <i class="bx bx-id-card me-1"></i>{{ $mobil->plat_nomor ?: 'Tanpa Plat' }}
                            </span>
                            <span class="badge bg-label-info rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.74rem;">
                                <i class="bx bx-category me-1"></i>{{ $mobil->kategori ?: 'Transportasi' }}
                            </span>
                        </div>
                        <h5 class="fw-bold text-dark mb-3">{{ $mobil->nama_mobil }}</h5>

                        <div class="d-flex flex-column gap-2">
                            <div class="up-spec-tile py-2.5">
                                <span class="up-spec-label">Pangkalan / Lokasi</span>
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <span class="fw-semibold text-dark small d-inline-flex align-items-start gap-1">
                                        <i class="bx bx-map-pin text-danger mt-0.5 flex-shrink-0"></i>
                                        <span>{{ $mobil->lokasi ?: 'Belum ditentukan' }}</span>
                                    </span>
                                    @if($mobil->latitude && $mobil->longitude)
                                        <a href="https://www.google.com/maps?q={{ $mobil->latitude }},{{ $mobil->longitude }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="btn btn-xs btn-outline-primary rounded-pill px-2.5 flex-shrink-0">
                                            <i class="bx bx-map-alt me-1"></i>Peta
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="up-spec-tile py-2.5">
                                        <span class="up-spec-label">Satuan</span>
                                        <span class="fw-semibold text-dark small">{{ $satuanLabel }}</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="up-spec-tile py-2.5">
                                        <span class="up-spec-label">Terdaftar</span>
                                        <span class="fw-semibold text-dark small">{{ $mobil->created_at ? $mobil->created_at->translatedFormat('d M Y') : '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Deskripsi + Skema Tarif Harian & Borongan --}}
        <div class="col-12 col-lg-7 col-xl-8">
            {{-- Deskripsi --}}
            <div class="card up-card mb-4">
                <div class="up-card-header d-flex align-items-center gap-2.5">
                    <div class="up-icon-box bg-label-info">
                        <i class="bx bx-detail fs-5 text-info"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Deskripsi &amp; Spesifikasi Kendaraan</h6>
                        <small class="text-muted">Kondisi armada, kapasitas penumpang, dan catatan layanan</small>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="p-3 rounded-3 bg-light border text-dark small" style="white-space: pre-line; line-height: 1.65;">{{ $mobil->deskripsi ?: 'Belum ada deskripsi tambahan untuk kendaraan ini.' }}</div>
                </div>
            </div>

            {{-- Skema 1: Sewa Harian --}}
            <div class="card up-card mb-4">
                <div class="up-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="up-icon-box bg-label-primary">
                            <i class="bx bx-car fs-5 text-primary"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Skema 1: Sewa Harian (Per 24 Jam)</h6>
                            <small class="text-muted">Tarif dikalikan otomatis sesuai jumlah hari penyewaan</small>
                        </div>
                    </div>
                    @if($mobil->is_harian_active)
                        <span class="badge bg-label-success rounded-pill px-3 py-1.5 fw-semibold">
                            <i class="bx bx-check-circle me-1"></i>Layanan Aktif
                        </span>
                    @else
                        <span class="badge bg-label-secondary rounded-pill px-3 py-1.5 fw-semibold">
                            <i class="bx bx-x-circle me-1"></i>Nonaktif
                        </span>
                    @endif
                </div>
                <div class="card-body p-3 p-md-4">
                    @if($mobil->is_harian_active)
                        <div class="up-price-banner mb-3">
                            <span class="up-spec-label text-primary mb-1">Tarif Sewa Harian</span>
                            <div class="d-flex align-items-baseline flex-wrap gap-1.5">
                                <h3 class="fw-bold text-primary mb-0">Rp {{ number_format((float) $mobil->harga_sewa, 0, ',', '.') }}</h3>
                                <span class="text-muted small fw-medium">/ hari (24 jam)</span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <div class="up-spec-tile">
                                    <span class="up-spec-label"><i class="bx bx-user-check text-primary me-1"></i>Layanan Supir</span>
                                    @if($mobil->opsi_supir === 'Lepas Kunci')
                                        <span class="badge bg-label-primary rounded-pill mb-1.5 fw-semibold">Lepas Kunci</span>
                                        <p class="text-muted small mb-0">Penyewa mengemudi sendiri tanpa supir pengelola.</p>
                                    @elseif($mobil->opsi_supir === 'Dengan Supir')
                                        <span class="badge bg-label-info rounded-pill mb-1.5 fw-semibold">Dengan Supir Pengelola</span>
                                        <p class="text-muted small mb-0">Pengelola menugaskan supir resmi saat pesanan disetujui.</p>
                                    @else
                                        <span class="badge bg-label-success rounded-pill mb-1.5 fw-semibold">Bebas Pilih</span>
                                        <p class="text-muted small mb-0">Warga bebas memilih lepas kunci atau dengan supir.</p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="up-spec-tile">
                                    <span class="up-spec-label"><i class="bx bx-gas-pump text-warning me-1"></i>Ketentuan BBM</span>
                                    @if(in_array($mobil->bbm_ditanggung, ['Pengelola', 'Pemerintah Desa']))
                                        <span class="badge bg-label-success rounded-pill mb-1.5 fw-semibold">Disediakan Pengelola</span>
                                        <p class="text-muted small mb-0">Tarif sewa harian sudah termasuk bahan bakar.</p>
                                    @else
                                        <span class="badge bg-label-secondary rounded-pill mb-1.5 fw-semibold">Ditanggung Penyewa</span>
                                        <p class="text-muted small mb-0">Penyewa mengisi bahan bakar secara mandiri.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-3 bg-light border text-muted text-center small">
                            Layanan Sewa Harian sedang dinonaktifkan untuk kendaraan ini.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Skema 2: Sewa Borongan --}}
            <div class="card up-card">
                <div class="up-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="up-icon-box bg-label-success">
                            <i class="bx bx-map-alt fs-5 text-success"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Skema 2: Sewa Borongan (Carter / Drop-Off)</h6>
                            <small class="text-muted">Tarif sekali jalan atau antar berdasarkan wilayah tujuan</small>
                        </div>
                    </div>
                    @if($mobil->is_borongan_active)
                        <span class="badge bg-label-success rounded-pill px-3 py-1.5 fw-semibold">
                            <i class="bx bx-check-circle me-1"></i>Layanan Aktif
                        </span>
                    @else
                        <span class="badge bg-label-secondary rounded-pill px-3 py-1.5 fw-semibold">
                            <i class="bx bx-x-circle me-1"></i>Nonaktif
                        </span>
                    @endif
                </div>
                <div class="card-body p-3 p-md-4">
                    @if($mobil->is_borongan_active)
                        <div class="mb-3">
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5 fw-medium small">
                                <i class="bx bx-slider-alt text-primary me-1"></i>Metode Tarif:
                                <strong>{{ $mobil->tipe_tarif_borongan === 'wilayah' ? 'Wilayah Administrasi Tujuan' : 'Jarak Tempuh (Kilometer)' }}</strong>
                            </span>
                        </div>

                        @if($mobil->tipe_tarif_borongan === 'wilayah')
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-6">
                                    <div class="up-spec-tile">
                                        <span class="up-spec-label text-primary"><i class="bx bx-home-alt me-1"></i>Dalam Desa</span>
                                        <h5 class="fw-bold text-dark mb-0.5">Rp {{ number_format((float) ($mobil->harga_dalam_desa_wilayah ?? 0), 0, ',', '.') }}</h5>
                                        <small class="text-muted">Tujuan di dalam satu desa asal</small>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="up-spec-tile">
                                        <span class="up-spec-label text-warning"><i class="bx bx-map me-1"></i>Luar Desa (1 Kecamatan)</span>
                                        <h5 class="fw-bold text-dark mb-0.5">Rp {{ number_format((float) ($mobil->harga_luar_desa_wilayah ?? 0), 0, ',', '.') }}</h5>
                                        <small class="text-muted">Desa tetangga dalam Kecamatan Bengkalis</small>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted small mb-3">
                                <i class="bx bx-info-circle text-primary me-1"></i>Rute luar Kecamatan Bengkalis tidak tersedia pada cakupan layanan saat ini.
                            </p>
                        @else
                            <div class="table-responsive border rounded-3 mb-3">
                                <table class="table table-hover mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3 py-2.5 text-muted text-uppercase fw-bold">Zonasi Jarak Tempuh</th>
                                            <th class="pe-3 py-2.5 text-end text-muted text-uppercase fw-bold">Tarif Borongan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="ps-3 py-2.5 fw-semibold text-dark">Dalam Desa (0 – {{ $mobil->batas_km_dalam_desa ?? 0 }} Km)</td>
                                            <td class="pe-3 py-2.5 text-end fw-bold text-primary">Rp {{ number_format((float) ($mobil->harga_dalam_desa ?? 0), 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 py-2.5 fw-semibold text-dark">Luar Desa ({{ ($mobil->batas_km_dalam_desa ?? 0) + 1 }} – {{ $mobil->batas_km_luar_desa ?? 0 }} Km)</td>
                                            <td class="pe-3 py-2.5 text-end fw-bold text-primary">Rp {{ number_format((float) ($mobil->harga_luar_desa ?? 0), 0, ',', '.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-3 py-2.5 fw-semibold text-dark">Luar Kota (&gt; {{ $mobil->batas_km_luar_desa ?? 0 }} Km)</td>
                                            <td class="pe-3 py-2.5 text-end fw-bold text-primary">Rp {{ number_format((float) ($mobil->harga_luar_kota ?? 0), 0, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-12 col-sm-6">
                                <div class="up-spec-tile">
                                    <span class="up-spec-label"><i class="bx bx-user-pin text-success me-1"></i>Supir Borongan</span>
                                    @if($mobil->opsi_supir_borongan === 'Lepas Kunci')
                                        <span class="badge bg-label-secondary rounded-pill mb-1.5 fw-semibold">Tanpa Supir (Bawa Sendiri)</span>
                                        <p class="text-muted small mb-0">Penyewa mengemudi sendiri kendaraan borongan.</p>
                                    @elseif($mobil->opsi_supir_borongan === 'Dengan Supir')
                                        <span class="badge bg-label-success rounded-pill mb-1.5 fw-semibold">Dengan Supir Pengelola</span>
                                        <p class="text-muted small mb-0">Pengelola menugaskan supir resmi untuk mengantar.</p>
                                    @else
                                        <span class="badge bg-label-primary rounded-pill mb-1.5 fw-semibold">Bebas Pilih</span>
                                        <p class="text-muted small mb-0">Warga bebas memilih bawa sendiri atau pakai supir.</p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="up-spec-tile">
                                    <span class="up-spec-label"><i class="bx bx-gas-pump text-warning me-1"></i>BBM Borongan</span>
                                    @if(in_array($mobil->bbm_ditanggung_borongan, ['Pengelola', 'Pemerintah Desa']))
                                        <span class="badge bg-label-success rounded-pill mb-1.5 fw-semibold">BBM Disediakan Pengelola</span>
                                        <p class="text-muted small mb-0">Tarif borongan sudah termasuk bahan bakar sampai tujuan.</p>
                                    @else
                                        <span class="badge bg-label-secondary rounded-pill mb-1.5 fw-semibold">Ditanggung Penyewa</span>
                                        <p class="text-muted small mb-0">Biaya bahan bakar selama perjalanan ditanggung penyewa.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-3 bg-light border text-muted text-center small">
                            Layanan Sewa Borongan sedang dinonaktifkan untuk kendaraan ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
