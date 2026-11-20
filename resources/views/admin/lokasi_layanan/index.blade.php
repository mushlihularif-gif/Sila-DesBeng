@extends('admin.layouts.admin')

@section('title', 'Lokasi Layanan Desa')

@section('content')
<style>
    .loc-stat-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        background: #fff;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .loc-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.08) !important;
    }
    .loc-item-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .loc-item-card:hover {
        transform: translateY(-3px);
        border-color: #d6dcf5;
        box-shadow: 0 8px 22px rgba(67, 89, 113, 0.09) !important;
    }
</style>

@php
    $totalLokasi = $daftarLokasi->count();
    $denganKoordinat = $daftarLokasi->filter(fn($l) => $l->punyaKoordinat())->count();
    $aktifCount = $daftarLokasi->where('is_aktif', true)->count();
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Pengaturan Desa /</span> Lokasi Layanan Desa
            </h4>
            <p class="text-muted small mb-0">
                Simpan alamat kantor desa, gudang alat, agen gas, atau titik jemput kendaraan agar warga mendapatkan rute peta yang akurat.
            </p>
        </div>
        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2 flex-shrink-0 w-100 w-sm-auto justify-content-center"
            data-bs-toggle="modal" data-bs-target="#modalLokasi"
            onclick="siapkanFormulir()">
            <i class="bx bx-plus-circle fs-5"></i>
            <span>Tambah Lokasi</span>
        </button>
    </div>

    <!-- Flash & Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible border-0 shadow-sm rounded-4 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-check-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <div class="fw-bold mb-1"><i class="bx bx-error-circle me-1"></i>Periksa kembali data lokasi:</div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Info Banner & KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card bg-label-primary border-0 shadow-none rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bx bx-map-pin fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Fungsi Titik Lokasi Layanan</h6>
                        <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                            Saat Anda menambah produk gas, alat sewa, kendaraan, atau fasilitas umum, Anda cukup memilih lokasi yang sudah disimpan di sini tanpa perlu menggeser peta berulang kali.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="loc-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Total Lokasi</span>
                        <h4 class="fw-bold text-primary mb-0">{{ $totalLokasi }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="loc-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Titik Peta</span>
                        <h4 class="fw-bold text-success mb-0">{{ $denganKoordinat }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="loc-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Status Aktif</span>
                        <h4 class="fw-bold text-info mb-0">{{ $aktifCount }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar (if locations exist) -->
    @if($totalLokasi > 0)
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="bx bx-buildings text-primary"></i>
            <span>Daftar Titik Lokasi</span>
            <span class="badge bg-label-primary rounded-pill">{{ $totalLokasi }}</span>
        </h5>
        <div class="input-group input-group-merge input-group-sm w-100 w-sm-auto" style="min-width: 240px; max-width: 320px;">
            <span class="input-group-text bg-white border-end-0"><i class="bx bx-search text-muted"></i></span>
            <input type="text" id="searchLokasiInput" class="form-control border-start-0" placeholder="Cari nama lokasi atau alamat...">
        </div>
    </div>
    @endif

    <!-- Grid Lokasi -->
    <div class="row g-3" id="lokasiGridContainer">
        @forelse($daftarLokasi as $lokasi)
            @php
                $hasCoord = $lokasi->punyaKoordinat();
                $searchText = strtolower(($lokasi->nama ?? '') . ' ' . ($lokasi->alamat ?? '') . ' ' . ($lokasi->catatan ?? ''));
            @endphp
            <div class="col-12 col-md-6 col-xl-4 lokasi-card-col" data-search="{{ $searchText }}">
                <div class="card loc-item-card h-100 shadow-sm">
                    <div class="card-body p-3 p-md-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2.5 min-w-0">
                                <div class="avatar avatar-md rounded-3 {{ $hasCoord ? 'bg-label-primary text-primary' : 'bg-label-warning text-warning' }} d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                    <i class="bx {{ $hasCoord ? 'bx-map-pin' : 'bx-map-alt' }} fs-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <h6 class="fw-bold text-dark mb-0 text-truncate" title="{{ $lokasi->nama }}">{{ $lokasi->nama }}</h6>
                                    <small class="text-muted">
                                        @if($lokasi->is_aktif)
                                            <span class="text-success fw-semibold"><i class="bx bxs-circle me-1" style="font-size: 0.5rem;"></i>Aktif</span>
                                        @else
                                            <span class="text-secondary fw-semibold"><i class="bx bxs-circle me-1" style="font-size: 0.5rem;"></i>Nonaktif</span>
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <span class="badge {{ $hasCoord ? 'bg-label-success' : 'bg-label-warning' }} rounded-pill flex-shrink-0" style="font-size: 0.7rem;">
                                <i class="bx {{ $hasCoord ? 'bx-check-circle' : 'bx-error-circle' }} me-1"></i>
                                {{ $hasCoord ? 'Titik Peta Siap' : 'Belum Ada Titik' }}
                            </span>
                        </div>

                        <div class="bg-light rounded-3 p-3 mb-3 flex-grow-1">
                            <div class="d-flex align-items-start gap-2 mb-2">
                                <i class="bx bx-home-alt text-primary mt-0.5 flex-shrink-0"></i>
                                <p class="text-dark small mb-0" style="line-height: 1.5;">{{ $lokasi->alamat }}</p>
                            </div>

                            @if($lokasi->catatan)
                                <div class="d-flex align-items-start gap-2 pt-2 border-top border-white">
                                    <i class="bx bx-info-circle text-info mt-0.5 flex-shrink-0"></i>
                                    <small class="text-muted" style="line-height: 1.45;">Patokan: {{ $lokasi->catatan }}</small>
                                </div>
                            @endif

                            @if($hasCoord)
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 mt-2 border-top border-white">
                                    <span class="badge bg-white text-secondary border font-monospace" style="font-size: 0.68rem;">
                                        {{ number_format((float) $lokasi->latitude, 5) }}, {{ number_format((float) $lokasi->longitude, 5) }}
                                    </span>
                                    <a href="https://www.google.com/maps?q={{ $lokasi->latitude }},{{ $lokasi->longitude }}" target="_blank" rel="noopener" class="small fw-semibold text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                        <i class="bx bx-navigation"></i>
                                        <span>Buka Maps</span>
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex align-items-center gap-2 pt-2 border-top mt-auto">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1"
                                data-bs-toggle="modal" data-bs-target="#modalLokasi"
                                data-lokasi='@json($lokasi)'
                                onclick="siapkanFormulir(JSON.parse(this.dataset.lokasi))">
                                <i class="bx bx-edit-alt"></i>
                                <span>Ubah Lokasi</span>
                            </button>
                            <form action="{{ route('admin.lokasi-layanan.destroy', $lokasi) }}" method="POST"
                                onsubmit="return confirm('Hapus lokasi {{ $lokasi->nama }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 d-inline-flex align-items-center justify-content-center gap-1" title="Hapus Lokasi">
                                    <i class="bx bx-trash"></i>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body text-center py-5 px-3">
                        <div class="avatar avatar-lg bg-label-primary text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 68px; height: 68px;">
                            <i class="bx bx-map-pin fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Belum Ada Lokasi Layanan</h5>
                        <p class="text-muted small mb-3 mx-auto" style="max-width: 460px;">
                            Tambahkan lokasi pertama (seperti Kantor Desa, Balai Pertemuan, atau Gudang BUMDes) agar mudah dipilih saat mengisi produk layanan desa.
                        </p>
                        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm"
                            data-bs-toggle="modal" data-bs-target="#modalLokasi"
                            onclick="siapkanFormulir()">
                            <i class="bx bx-plus-circle me-1"></i>Tambah Lokasi Pertama
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>

@include('admin.lokasi_layanan._modal')

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchLokasiInput');
        if (!searchInput) return;

        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.lokasi-card-col').forEach(col => {
                const hay = (col.getAttribute('data-search') || '').toLowerCase();
                col.style.display = (!q || hay.includes(q)) ? '' : 'none';
            });
        });
    });
</script>
@endpush
@endsection
