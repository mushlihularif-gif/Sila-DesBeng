@extends('admin.layouts.admin')

@section('content')
@php
    $cleanPlat = str_replace('Plat: ', '', $ambulans->deskripsi);
    $isAmbulans = $ambulans->kategori === 'ambulans';
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Breadcrumb & Header Halaman -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">
                    <a href="{{ route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan']) }}" class="text-decoration-none text-muted">
                        <i class="bx bx-left-arrow-alt me-1"></i>Fasilitas Umum & Kendaraan
                    </a> /
                </span> Detail Kendaraan
            </h4>
            <p class="text-muted mb-0 small">Rincian identitas armada, spesifikasi kendaraan, serta data supir siaga penanggung jawab.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap flex-sm-nowrap w-100 w-md-auto">
            <a href="{{ route('admin.unit.ambulans.edit', $ambulans->id) }}" class="btn btn-warning btn-sm rounded-pill flex-fill d-inline-flex align-items-center justify-content-center py-1.5 px-3" style="font-size: 0.82rem;">
                <i class="bx bx-edit-alt me-1"></i> <span>Ubah</span>
            </a>
            <form action="{{ route('admin.unit.ambulans.destroy', $ambulans->id) }}" method="POST"
                data-konfirmasi="Apakah Anda yakin ingin menghapus kendaraan ini?" class="flex-fill">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm rounded-pill w-100 d-inline-flex align-items-center justify-content-center py-1.5 px-3" style="font-size: 0.82rem;">
                    <i class="bx bx-trash me-1"></i> <span>Hapus</span>
                </button>
            </form>
            <a href="{{ route('admin.unit.fasilitas_umum.index', ['tab' => 'kendaraan']) }}" class="btn btn-outline-secondary btn-sm rounded-pill flex-fill d-inline-flex align-items-center justify-content-center py-1.5 px-3" style="font-size: 0.82rem;">
                <i class="bx bx-arrow-back me-1"></i> <span>Kembali</span>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Kiri: Galeri Foto & Kartu Identitas Kendaraan -->
        <div class="col-lg-5 col-xl-4">
            <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-4">
                <div class="position-relative bg-light">
                    <div id="carouselAmbulans" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="{{ $ambulans->foto ? asset('storage/' . $ambulans->foto) : asset('portal/assets/img/unit/mobil.png') }}" 
                                     class="d-block w-100"
                                     alt="{{ $ambulans->nama_mobil }}"
                                     style="height: 280px; object-fit: cover; object-position: center;">
                            </div>
                            @if ($ambulans->foto_2)
                                <div class="carousel-item">
                                    <img src="{{ asset('storage/' . $ambulans->foto_2) }}" 
                                         class="d-block w-100"
                                         alt="{{ $ambulans->nama_mobil }}"
                                         style="height: 280px; object-fit: cover; object-position: center;">
                                </div>
                            @endif
                            @if ($ambulans->foto_3)
                                <div class="carousel-item">
                                    <img src="{{ asset('storage/' . $ambulans->foto_3) }}" 
                                         class="d-block w-100"
                                         alt="{{ $ambulans->nama_mobil }}"
                                         style="height: 280px; object-fit: cover; object-position: center;">
                                </div>
                            @endif
                        </div>
                        @if ($ambulans->foto_2 || $ambulans->foto_3)
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselAmbulans" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselAmbulans" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        @endif
                    </div>

                    <!-- Badge Kategori Overlay -->
                    <div class="position-absolute top-0 start-0 m-3">
                        @if($isAmbulans)
                            <span class="badge bg-danger fs-7 px-3 py-2 rounded-pill shadow-sm">
                                <i class="bx bx-plus-medical me-1"></i>AMBULANS DESA
                            </span>
                        @else
                            <span class="badge bg-secondary fs-7 px-3 py-2 rounded-pill shadow-sm">
                                <i class="bx bx-car me-1"></i>KENDARAAN OPERASIONAL
                            </span>
                        @endif
                    </div>

                    <!-- Badge Status Siaga Overlay -->
                    <div class="position-absolute top-0 end-0 m-3">
                        <span class="badge bg-success fs-7 px-3 py-2 rounded-pill shadow-sm">
                            <i class="bx bx-check-shield me-1"></i>SIAP SIAGA
                        </span>
                    </div>
                </div>

                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-2">{{ $ambulans->nama_mobil }}</h5>

                    <!-- Plat Nomor & Kategori Badge -->
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        @if($cleanPlat && $cleanPlat !== '-')
                            <span class="badge bg-dark font-monospace fs-7 px-3 py-1.5 shadow-xs">
                                <i class="bx bx-barcode me-1"></i>{{ $cleanPlat }}
                            </span>
                        @else
                            <span class="badge bg-light text-muted border fs-7 px-2.5 py-1">
                                Plat Belum Diisi
                            </span>
                        @endif

                        <span class="badge {{ $isAmbulans ? 'bg-label-danger' : 'bg-label-info' }} fs-7 px-2.5 py-1">
                            <i class="bx {{ $isAmbulans ? 'bx-heart' : 'bx-cog' }} me-1"></i>
                            {{ $isAmbulans ? 'Layanan Darurat Warga' : 'Operasional Desa' }}
                        </span>
                    </div>

                    <hr class="my-3 text-muted opacity-25">

                    <!-- Detail Ringkas -->
                    <div class="d-flex flex-column gap-2 small">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bx bx-map-pin me-1"></i>Wilayah / Desa:</span>
                            <span class="fw-semibold text-dark">{{ $ambulans->region->name ?? 'Pemerintah Desa' }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bx bx-money me-1"></i>Tarif Layanan:</span>
                            <span class="badge bg-label-success fw-bold">GRATIS / SOSIAL</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted"><i class="bx bx-calendar me-1"></i>Terdaftar Sejak:</span>
                            <span class="fw-semibold text-dark">{{ $ambulans->created_at ? $ambulans->created_at->translatedFormat('d F Y') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Informasi Lengkap & Daftar Supir Siaga -->
        <div class="col-lg-7 col-xl-8">
            <!-- Card Profil & Ketentuan Kendaraan -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bx bx-info-circle text-primary fs-4"></i>
                        <h5 class="fw-bold mb-0 text-dark">Informasi & Ketentuan Layanan</h5>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 bg-light rounded-3 text-secondary lh-base mb-3" style="font-size: 0.95rem;">
                        @if($isAmbulans)
                            Armada ambulans desa ini disiagakan khusus untuk melayani kebutuhan gawat darurat warga, rujukan medis ke fasilitas kesehatan terdekat, serta penanganan warga yang memerlukan pertolongan cepat. Layanan operasional didukung oleh pemerintah desa dan supir siaga yang bertugas.
                        @else
                            Kendaraan operasional ini digunakan untuk menunjang kegiatan pelayanan masyarakat desa, kegiatan kedinasan, kebersihan lingkungan, serta mobilitas logistik pemerintah desa.
                        @endif
                    </div>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 border bg-white h-100">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bx bx-time-five text-primary fs-5"></i>
                                    <span class="fw-bold text-dark small">Ketersediaan Layanan:</span>
                                </div>
                                <span class="badge bg-label-primary fs-7 mb-1">Siaga 24 Jam</span>
                                <small class="text-muted d-block" style="font-size: 0.8rem;">Dapat dihubungi sewaktu-waktu oleh warga dalam keadaan mendesak.</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 rounded-3 border bg-white h-100">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="bx bx-shield-check text-success fs-5"></i>
                                    <span class="fw-bold text-dark small">Biaya & Operasional:</span>
                                </div>
                                <span class="badge bg-label-success fs-7 mb-1">Fasilitas Publik</span>
                                <small class="text-muted d-block" style="font-size: 0.8rem;">Disediakan oleh Pemerintah Desa untuk kesejahteraan warga.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Daftar Supir Siaga -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white border-bottom p-3 p-sm-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <i class="bx bx-user-pin text-danger fs-4 flex-shrink-0"></i>
                        <div class="min-w-0">
                            <h5 class="fw-bold mb-0 text-dark fs-6 fs-sm-5 text-truncate">Data Supir Siaga Penanggung Jawab</h5>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Personil bertugas untuk armada operasional desa</small>
                        </div>
                    </div>
                    <span class="badge bg-label-primary px-2.5 px-sm-3 py-1.5 rounded-pill flex-shrink-0 align-self-start align-self-sm-center" style="font-size: 0.72rem;">
                        {{ $ambulans->supirs ? $ambulans->supirs->count() : 0 }} Supir Ditugaskan
                    </span>
                </div>
                <div class="card-body p-3 p-sm-4">
                    @if($ambulans->supirs && $ambulans->supirs->count() > 0)
                        <div class="row g-3">
                            @foreach($ambulans->supirs as $supir)
                                <div class="col-md-6">
                                    <div class="p-3 border rounded-3 bg-light h-100 position-relative">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($supir->foto)
                                                <img src="{{ asset('storage/' . $supir->foto) }}" 
                                                     alt="{{ $supir->nama }}" 
                                                     class="rounded-circle shadow-xs" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="avatar avatar-md bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="bx bx-user fs-4"></i>
                                                </div>
                                            @endif
                                            <div class="flex-grow-1 min-w-0">
                                                <h6 class="fw-bold text-dark mb-0 text-truncate">{{ $supir->nama }}</h6>
                                                <span class="badge bg-label-success px-2 py-0.5 rounded font-11 mb-1">Siaga Bertugas</span>
                                                <div class="text-muted small">
                                                    <i class="bx bx-phone me-1 text-primary"></i>
                                                    <span class="fw-semibold text-dark">{{ $supir->kontak ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        @if($supir->kontak)
                                            @php
                                                $cleanPhone = preg_replace('/[^0-9]/', '', $supir->kontak);
                                                if (str_starts_with($cleanPhone, '0')) {
                                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                                }
                                            @endphp
                                            <div class="mt-3 pt-2 border-top d-flex gap-2">
                                                <a href="https://wa.me/{{ $cleanPhone }}" target="_blank" class="btn btn-xs btn-outline-success flex-grow-1">
                                                    <i class="bx bxl-whatsapp me-1"></i> WhatsApp
                                                </a>
                                                <a href="tel:{{ $supir->kontak }}" class="btn btn-xs btn-outline-primary flex-grow-1">
                                                    <i class="bx bx-phone-call me-1"></i> Telepon
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="avatar avatar-lg bg-warning-subtle text-warning rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bx bx-user-x fs-2"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-1">Belum Ada Supir yang Ditugaskan</h6>
                            <p class="text-muted small mb-3">Armada ini belum memiliki supir siaga penanggung jawab.</p>
                            <a href="{{ route('admin.unit.ambulans.edit', $ambulans->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-user-plus me-1"></i> Tugaskan Supir Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .shadow-xs {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.4);
        border-radius: 50%;
        padding: 12px;
    }
</style>
@endpush
