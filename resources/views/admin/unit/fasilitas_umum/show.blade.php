@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y py-2 py-sm-3">
        <!-- Breadcrumb -->
        <div class="mb-3">
            <h4 class="fw-bold mb-1 fs-5 fs-sm-4">
                <span class="text-muted fw-light">
                    <a href="{{ route('admin.unit.fasilitas_umum.index', ['tab' => 'gedung']) }}" class="text-decoration-none text-muted">
                        <i class="bx bx-left-arrow-alt me-1"></i>Fasilitas Umum & Aset
                    </a> /
                </span> Detail Fasilitas
            </h4>
            <p class="text-muted mb-0 small d-none d-sm-block">Rincian aset gedung, ruang publik desa, serta personil pengurus penanggung jawab.</p>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-3 mb-sm-4 shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-3 p-sm-4">
                        <div class="d-flex flex-column flex-md-row gap-3 gap-md-4">
                            
                            <!-- Gambar Carousel -->
                            @php
                                $validPhotos = [];
                                foreach (['foto', 'foto_2', 'foto_3'] as $fCol) {
                                    if (!empty($fasilitas->$fCol) && \Illuminate\Support\Facades\Storage::disk('public')->exists($fasilitas->$fCol)) {
                                        $validPhotos[] = $fasilitas->$fCol;
                                    }
                                }
                                $fotoCount = count($validPhotos);
                            @endphp
                            <div class="flex-shrink-0 w-100" style="max-width: 400px;">
                                <div id="carouselBarang" class="carousel slide rounded-3 overflow-hidden shadow-sm" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        @if ($fotoCount > 0)
                                            @foreach ($validPhotos as $idx => $photo)
                                                <div class="carousel-item {{ $idx === 0 ? 'active' : '' }}">
                                                    <img src="{{ asset('storage/' . $photo) }}" class="d-block w-100 fasilitas-detail-img"
                                                        alt="{{ $fasilitas->nama_fasilitas }}"
                                                        onerror="this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center bg-light text-muted w-100 fasilitas-detail-img\'><i class=\'bx bx-building-house\' style=\'font-size: 64px; color: #cbd5e1;\'></i></div>';">
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="carousel-item active">
                                                <div class="d-flex align-items-center justify-content-center bg-light text-muted w-100 fasilitas-detail-img">
                                                    <i class="bx bx-building-house" style="font-size: 64px; color: #cbd5e1;"></i>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    @if ($fotoCount > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselBarang"
                                            data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselBarang"
                                            data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Informasi Detail -->
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2 border-bottom gap-2">
                                    <h5 class="fw-bold mb-0 text-dark fs-6 fs-sm-5 text-truncate">Informasi Fasilitas</h5>
                                    <span class="badge {{ $fasilitas->status == 'tersedia' ? 'bg-success' : ($fasilitas->status == 'disewa' ? 'bg-warning' : 'bg-danger') }} px-2.5 py-1 rounded-pill flex-shrink-0" style="font-size: 0.72rem;">
                                        {{ Str::upper($fasilitas->status) }}
                                    </span>
                                </div>

                                <!-- Key-Value Detail List -->
                                <div class="d-flex flex-column gap-1.5 mb-3">
                                    <div class="p-2 rounded-3 bg-light d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-1">
                                        <span class="text-uppercase text-muted fw-bold small" style="font-size: 0.72rem;">Nama Fasilitas</span>
                                        <span class="fw-bold text-dark text-sm-end" style="font-size: 0.88rem;">{{ $fasilitas->nama_fasilitas }}</span>
                                    </div>
                                    <div class="p-2 rounded-3 bg-light d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-1">
                                        <span class="text-uppercase text-muted fw-bold small" style="font-size: 0.72rem;">Kategori & Kapasitas</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-label-primary px-2 py-0.5" style="font-size: 0.7rem;">{{ $fasilitas->kategori }}</span>
                                            <span class="fw-semibold text-dark" style="font-size: 0.82rem;">{{ $fasilitas->stok }} {{ Str::upper($fasilitas->satuan ?? 'UNIT') }}</span>
                                        </div>
                                    </div>
                                    <div class="p-2 rounded-3 bg-light d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-1">
                                        <span class="text-uppercase text-muted fw-bold small" style="font-size: 0.72rem;">Status Biaya & Tarif</span>
                                        <div class="text-sm-end">
                                            @if($fasilitas->status_biaya === 'berbayar')
                                                <span class="badge bg-label-info px-2 py-0.5" style="font-size: 0.7rem;">Multifungsi</span>
                                                @if($fasilitas->harga_sewa)
                                                    <span class="text-dark fw-bold ms-1" style="font-size: 0.82rem;">Rp {{ number_format($fasilitas->harga_sewa, 0, ',', '.') }} / hari</span>
                                                @endif
                                            @else
                                                <span class="badge bg-label-success px-2 py-0.5" style="font-size: 0.7rem;">Gratis Sepenuhnya</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="p-2 rounded-3 bg-light d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-1">
                                        <span class="text-uppercase text-muted fw-bold small flex-shrink-0" style="font-size: 0.72rem;">Lokasi</span>
                                        <span class="text-dark text-sm-end fw-semibold" style="font-size: 0.82rem;"><i class="bx bx-map-pin text-danger me-1"></i>{{ $fasilitas->lokasi }}</span>
                                    </div>
                                    <div class="p-2.5 rounded-3 bg-light">
                                        <span class="text-uppercase text-muted fw-bold small d-block mb-1" style="font-size: 0.72rem;">Deskripsi</span>
                                        <p class="text-muted mb-0" style="font-size: 0.8rem; line-height: 1.5; word-break: break-word;">{{ $fasilitas->deskripsi ?: 'Tidak ada deskripsi tambahan.' }}</p>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 flex-wrap flex-sm-nowrap w-100 pt-1">
                                    <a href="{{ route('admin.unit.fasilitas_umum.edit', $fasilitas->id) }}" class="btn btn-warning btn-sm rounded-pill flex-fill d-inline-flex align-items-center justify-content-center py-1.5 px-3" style="font-size: 0.82rem;">
                                        <i class="bx bx-edit-alt me-1"></i> <span>Ubah</span>
                                    </a>
                                    <form action="{{ route('admin.unit.fasilitas_umum.destroy', $fasilitas->id) }}" method="POST"
                                        data-konfirmasi="Apakah Anda yakin ingin menghapus fasilitas ini?" class="flex-fill">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm rounded-pill w-100 d-inline-flex align-items-center justify-content-center py-1.5 px-3" style="font-size: 0.82rem;">
                                            <i class="bx bx-trash me-1"></i> <span>Hapus</span>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.unit.fasilitas_umum.index', ['tab' => 'gedung']) }}" class="btn btn-outline-secondary btn-sm rounded-pill flex-fill d-inline-flex align-items-center justify-content-center py-1.5 px-3" style="font-size: 0.82rem;">
                                        <i class="bx bx-arrow-back me-1"></i> <span>Kembali</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card Daftar Pengurus & Pemegang Kunci Gedung -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-3 mb-sm-4">
                    <div class="card-header bg-white border-bottom p-3 p-sm-4 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="p-2 bg-success-subtle text-success rounded-3 flex-shrink-0">
                                <i class="bx bx-key fs-4"></i>
                            </div>
                            <div class="min-w-0">
                                <h5 class="fw-bold mb-0 text-dark fs-6 fs-sm-5 text-truncate">Pengurus & Pemegang Kunci Gedung</h5>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">Personil penanggung jawab serah terima kunci dan fasilitas</small>
                            </div>
                        </div>
                        <span class="badge bg-label-success px-2.5 px-sm-3 py-1.5 rounded-pill flex-shrink-0 align-self-start align-self-sm-center" style="font-size: 0.72rem;">
                            {{ $fasilitas->pengurus ? $fasilitas->pengurus->count() : 0 }} Pengurus Ditugaskan
                        </span>
                    </div>
                    <div class="card-body p-3 p-sm-4">
                        @if($fasilitas->pengurus && $fasilitas->pengurus->count() > 0)
                            <div class="row g-2 g-sm-3">
                                @foreach($fasilitas->pengurus as $pengurus)
                                    @php
                                        $cleanWa = preg_replace('/[^0-9]/', '', $pengurus->kontak ?? '');
                                        $waUrl = $cleanWa ? ('https://wa.me/' . (str_starts_with($cleanWa, '0') ? '62' . substr($cleanWa, 1) : $cleanWa)) : null;
                                        $avatar = $pengurus->foto ? asset('storage/' . $pengurus->foto) : asset('Admin/img/avatars/pria.png');
                                        $isTersedia = ($pengurus->status === 'Tersedia');
                                    @endphp
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <div class="p-2.5 p-sm-3 border rounded-3 bg-light h-100 position-relative">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <div class="position-relative flex-shrink-0" style="width: 44px; height: 44px;">
                                                    <img src="{{ $avatar }}" 
                                                         alt="{{ $pengurus->nama }}" 
                                                         class="rounded-circle shadow-xs border object-fit-cover w-100 h-100"
                                                         onerror="this.src='{{ asset('Admin/img/avatars/pria.png') }}'">
                                                    <span class="position-absolute {{ $isTersedia ? 'bg-success' : 'bg-warning' }} border border-2 border-white rounded-circle" style="width: 11px; height: 11px; bottom: 1px; right: 1px;"></span>
                                                </div>
                                                <div class="flex-grow-1 min-w-0">
                                                    <div class="d-flex align-items-center justify-content-between gap-1 mb-0.5">
                                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.85rem;">{{ $pengurus->nama }}</h6>
                                                        <span class="badge {{ $isTersedia ? 'bg-label-success' : 'bg-label-warning' }} px-1.5 py-0.5 rounded flex-shrink-0" style="font-size: 0.65rem;">
                                                            {{ $pengurus->status }}
                                                        </span>
                                                    </div>
                                                    <div class="text-muted small" style="font-size: 0.75rem;">
                                                        <i class="bx bxl-whatsapp text-success me-0.5"></i>{{ $pengurus->kontak ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                            @if($waUrl)
                                                <div class="mt-2.5 pt-2 border-top">
                                                    <a href="{{ $waUrl }}" target="_blank" class="btn btn-sm btn-success w-100 d-flex align-items-center justify-content-center gap-1 py-1 rounded-pill" style="font-size: 0.78rem;">
                                                        <i class="bx bxl-whatsapp fs-5"></i> Hubungi WhatsApp
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 p-sm-4 bg-light rounded-3 text-center border">
                                <i class="bx bx-key text-muted fs-1 mb-2"></i>
                                <h6 class="fw-bold text-dark mb-1 fs-6">Pengelolaan Langsung Kantor Desa</h6>
                                <p class="text-muted small mb-3" style="font-size: 0.78rem;">Belum ada personil pengurus / pemegang kunci khusus yang ditugaskan ke fasilitas ini. Koordinasi serah terima kunci dapat dilakukan langsung melalui perangkat desa.</p>
                                <a href="{{ route('admin.unit.fasilitas_umum.edit', $fasilitas->id) }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                    <i class="bx bx-user-plus me-1"></i> Tugaskan Pengurus Sekarang
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
    .fasilitas-detail-img {
        height: 220px;
        object-fit: cover;
        object-position: center;
        width: 100%;
    }
    @media (min-width: 768px) {
        .fasilitas-detail-img {
            height: 380px;
        }
    }
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0,0,0,0.3);
        border-radius: 50%;
        padding: 10px;
    }
</style>
@endpush


