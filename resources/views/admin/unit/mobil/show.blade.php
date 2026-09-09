@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Breadcrumb -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold mb-1">
                    <span class="text-muted fw-light">Unit Layanan / Penyewaan Mobil /</span> Detail Mobil
                </h4>
                <p class="text-muted mb-0">Informasi spesifikasi, skema tarif harian & borongan, serta kebijakan operasional kendaraan</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.unit.mobil.edit', $mobil->id) }}" class="btn btn-warning">
                    <i class="bx bx-edit-alt me-1"></i> Ubah Kendaraan
                </a>
                <form action="{{ route('admin.unit.mobil.destroy', $mobil->id) }}" method="POST"
                    data-konfirmasi="Apakah Anda yakin ingin menghapus mobil ini?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Hapus
                    </button>
                </form>
                <a href="{{ route('admin.unit.mobil.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Kolom Kiri: Galeri Foto & Informasi Ringkas Kendaraan -->
            <div class="col-lg-5 col-xl-4">
                <!-- Card Media Foto -->
                <div class="card shadow-sm border-0 rounded-3 overflow-hidden mb-4">
                    <div class="position-relative bg-light">
                        <div id="carouselMobil" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="{{ $mobil->foto ? asset('storage/' . $mobil->foto) : asset('portal/assets/img/unit/mobil.png') }}" 
                                         class="d-block w-100"
                                         alt="{{ $mobil->nama_mobil }}"
                                         style="height: 280px; object-fit: cover; object-position: center;">
                                </div>
                                @if ($mobil->foto_2)
                                    <div class="carousel-item">
                                        <img src="{{ asset('storage/' . $mobil->foto_2) }}" 
                                             class="d-block w-100"
                                             alt="{{ $mobil->nama_mobil }}"
                                             style="height: 280px; object-fit: cover; object-position: center;">
                                    </div>
                                @endif
                                @if ($mobil->foto_3)
                                    <div class="carousel-item">
                                        <img src="{{ asset('storage/' . $mobil->foto_3) }}" 
                                             class="d-block w-100"
                                             alt="{{ $mobil->nama_mobil }}"
                                             style="height: 280px; object-fit: cover; object-position: center;">
                                    </div>
                                @endif
                            </div>
                            @if ($mobil->foto_2 || $mobil->foto_3)
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselMobil" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselMobil" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            @endif
                        </div>

                        <!-- Status Badge Overlay -->
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge {{ $mobil->status == 'tersedia' ? 'bg-success' : ($mobil->status == 'disewa' ? 'bg-warning text-dark' : 'bg-danger') }} fs-7 px-3 py-2 rounded-pill shadow-sm">
                                <i class="bx {{ $mobil->status == 'tersedia' ? 'bx-check-circle' : ($mobil->status == 'disewa' ? 'bx-time-five' : 'bx-error-circle') }} me-1"></i>
                                {{ Str::upper($mobil->status) }}
                            </span>
                        </div>

                        <!-- Stok Badge Overlay -->
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-dark bg-opacity-75 fs-7 px-3 py-2 rounded-pill shadow-sm">
                                Stok: {{ $mobil->stok }} {{ Str::upper($mobil->satuan) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-1">{{ $mobil->nama_mobil }}</h5>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <span class="badge bg-label-primary font-monospace fs-7 px-2.5 py-1">
                                <i class="bx bx-card me-1"></i>{{ $mobil->plat_nomor ? $mobil->plat_nomor : 'Tanpa Plat' }}
                            </span>
                            <span class="badge bg-label-info fs-7 px-2.5 py-1">
                                <i class="bx bx-category me-1"></i>{{ $mobil->kategori }}
                            </span>
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <!-- Detail Atribut -->
                        <div class="d-flex flex-column gap-2 small">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted"><i class="bx bx-map-pin me-1"></i>Pangkalan / Lokasi:</span>
                                <span class="fw-semibold text-dark text-end">{{ $mobil->lokasi ?? '-' }}</span>
                            </div>
                            @if($mobil->latitude && $mobil->longitude)
                                <div class="text-end">
                                    <a href="https://www.google.com/maps?q={{ $mobil->latitude }},{{ $mobil->longitude }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill">
                                        <i class="bx bx-link-external me-1"></i>Lihat Titik Peta
                                    </a>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between mt-1">
                                <span class="text-muted"><i class="bx bx-layer me-1"></i>Satuan Hitung:</span>
                                <span class="fw-semibold text-dark">{{ $mobil->satuan }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted"><i class="bx bx-calendar me-1"></i>Terdaftar Sejak:</span>
                                <span class="fw-semibold text-dark">{{ $mobil->created_at ? $mobil->created_at->translatedFormat('d F Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Detail Spesifikasi & Skema Layanan Tarif -->
            <div class="col-lg-7 col-xl-8">
                <!-- Card Deskripsi -->
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bx bx-detail text-primary fs-4"></i>
                            <h5 class="fw-bold mb-0 text-dark">Deskripsi & Spesifikasi Kendaraan</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="p-3 bg-light rounded-3 text-secondary lh-base" style="font-size: 0.95rem;">
                            {!! nl2br(e($mobil->deskripsi)) !!}
                        </div>
                    </div>
                </div>

                <!-- Card Skema Tarif & Layanan Penyewaan -->
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bx bx-money text-success fs-4"></i>
                            <h5 class="fw-bold mb-0 text-dark">Skema Tarif & Kebijakan Penyewaan</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- SECTION 1: SEWA HARIAN -->
                        <div class="border {{ $mobil->is_harian_active ? 'border-primary-subtle' : 'border-light-subtle' }} rounded-3 p-3.5 mb-4 bg-white shadow-xs">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center pb-3 border-bottom mb-3 gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-car fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">1. Sewa Harian (Rental Per 24 Jam)</h6>
                                        <small class="text-muted">Perhitungan durasi berdasarkan hari pemakaian</small>
                                    </div>
                                </div>
                                <div>
                                    @if($mobil->is_harian_active)
                                        <span class="badge bg-success px-3 py-1.5 rounded-pill">
                                            <i class="bx bx-check-circle me-1"></i>Layanan Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-1.5 rounded-pill">
                                            <i class="bx bx-x-circle me-1"></i>Nonaktif
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if($mobil->is_harian_active)
                                <div class="row g-3">
                                    <!-- Tarif Harian -->
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 h-100 border">
                                            <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Tarif Dasar Sewa</span>
                                            <div class="d-flex align-items-baseline gap-1">
                                                <span class="fs-4 fw-bold text-primary">Rp. {{ number_format($mobil->harga_sewa, 0, ',', '.') }}</span>
                                                <span class="text-muted small">/ Hari</span>
                                            </div>
                                            <small class="text-muted mt-1 d-block" style="font-size: 0.8rem;">Dihitung otomatis kelipatan hari oleh sistem.</small>
                                        </div>
                                    </div>

                                    <!-- Layanan Supir Harian -->
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 h-100 border">
                                            <div class="d-flex align-items-center gap-1 mb-1">
                                                <i class="bx bx-user-check text-primary"></i>
                                                <span class="text-muted small fw-semibold text-uppercase">Layanan Supir</span>
                                            </div>
                                            <div class="mt-1">
                                                @if($mobil->opsi_supir === 'Lepas Kunci')
                                                    <span class="badge bg-label-primary fw-semibold fs-7 mb-1">Lepas Kunci</span>
                                                    <small class="text-muted d-block" style="font-size: 0.8rem;">Penyewa mengemudi sendiri tanpa supir pengelola.</small>
                                                @elseif($mobil->opsi_supir === 'Dengan Supir')
                                                    <span class="badge bg-label-info fw-semibold fs-7 mb-1">Dengan Supir Pengelola</span>
                                                    <small class="text-muted d-block" style="font-size: 0.8rem;">Pengelola resmi menugaskan supir.</small>
                                                @else
                                                    <span class="badge bg-label-success fw-semibold fs-7 mb-1">Bebas Pilih</span>
                                                    <small class="text-muted d-block" style="font-size: 0.8rem;">Warga bebas memilih bawa sendiri atau pakai supir.</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Kebijakan BBM Harian -->
                                    <div class="col-md-4">
                                        <div class="p-3 bg-light rounded-3 h-100 border">
                                            <div class="d-flex align-items-center gap-1 mb-1">
                                                <i class="bx bx-gas-pump text-warning"></i>
                                                <span class="text-muted small fw-semibold text-uppercase">Bahan Bakar (BBM)</span>
                                            </div>
                                            <div class="mt-1">
                                                @if(in_array($mobil->bbm_ditanggung, ['Pengelola', 'Pemerintah Desa']))
                                                    <span class="badge bg-success fw-semibold fs-7 mb-1">
                                                        <i class="bx bx-check me-1"></i>Disediakan Pengelola
                                                    </span>
                                                    <small class="text-muted d-block" style="font-size: 0.8rem;">Harga sewa harian sudah termasuk bensin.</small>
                                                @else
                                                    <span class="badge bg-secondary fw-semibold fs-7 mb-1">
                                                        <i class="bx bx-info-circle me-1"></i>Ditanggung Penyewa
                                                    </span>
                                                    <small class="text-muted d-block" style="font-size: 0.8rem;">Penyewa mengisi bensin secara mandiri.</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="p-3 bg-light rounded-3 text-muted text-center small">
                                    Layanan Sewa Harian sedang dinonaktifkan untuk unit kendaraan ini.
                                </div>
                            @endif
                        </div>

                        <!-- SECTION 2: SEWA BORONGAN -->
                        <div class="border {{ $mobil->is_borongan_active ? 'border-success-subtle' : 'border-light-subtle' }} rounded-3 p-3.5 bg-white shadow-xs">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center pb-3 border-bottom mb-3 gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bx bx-map-alt fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">2. Sewa Borongan (Drop-Off / Carter Berdasarkan Rute)</h6>
                                        <small class="text-muted">Perhitungan tarif sekali jalan/antar berdasarkan wilayah tujuan</small>
                                    </div>
                                </div>
                                <div>
                                    @if($mobil->is_borongan_active)
                                        <span class="badge bg-success px-3 py-1.5 rounded-pill">
                                            <i class="bx bx-check-circle me-1"></i>Layanan Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-1.5 rounded-pill">
                                            <i class="bx bx-x-circle me-1"></i>Nonaktif
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if($mobil->is_borongan_active)
                                <!-- Badge Metode Penentuan Tarif -->
                                <div class="mb-3">
                                    <span class="badge bg-light text-dark border px-3 py-1.5 fs-7">
                                        <i class="bx bx-slider me-1"></i>Metode Tarif: 
                                        <strong>{{ $mobil->tipe_tarif_borongan === 'wilayah' ? 'Berdasarkan Wilayah Administrasi Tujuan' : 'Berdasarkan Jarak Tempuh (Kilometer)' }}</strong>
                                    </span>
                                </div>

                                @if($mobil->tipe_tarif_borongan === 'wilayah')
                                    <!-- TABEL RINCIAN TARIF BERDASARKAN WILAYAH -->
                                    <div class="table-responsive border rounded-3 bg-white mb-3">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="py-2.5 ps-3 text-muted small text-uppercase fw-bold" style="width: 60%;">Wilayah Administrasi Tujuan</th>
                                                    <th class="py-2.5 pe-3 text-end text-muted small text-uppercase fw-bold">Tarif Borongan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="ps-3 py-2.5">
                                                        <span class="text-primary fw-semibold d-flex align-items-center">
                                                            <i class="bx bx-home-alt me-2 fs-5"></i>Dalam Desa (Satu Desa Asal)
                                                        </span>
                                                        <small class="text-muted ps-4 d-block">Tujuan di dalam batas desa asal pengelola</small>
                                                    </td>
                                                    <td class="pe-3 py-2.5 text-end fw-bold text-dark fs-6 align-middle">
                                                        Rp. {{ number_format($mobil->harga_dalam_desa_wilayah ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-3 py-2.5">
                                                        <span class="text-warning fw-semibold d-flex align-items-center">
                                                            <i class="bx bx-map me-2 fs-5"></i>Luar Desa (Beda Desa, Masih 1 Kecamatan)
                                                        </span>
                                                        <small class="text-muted ps-4 d-block">Tujuan ke desa tetangga di dalam kecamatan yang sama</small>
                                                    </td>
                                                    <td class="pe-3 py-2.5 text-end fw-bold text-dark fs-6 align-middle">
                                                        Rp. {{ number_format($mobil->harga_luar_desa_wilayah ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-3 py-2.5 align-middle">
                                                        <span class="text-danger fw-semibold d-flex align-items-center">
                                                            <i class="bx bx-buildings me-2 fs-5"></i>Luar / Antar Kecamatan
                                                        </span>
                                                        <small class="text-muted ps-4 d-block">
                                                            {{ ($mobil->tipe_luar_kecamatan_wilayah ?? 'pukul_rata') === 'pukul_rata' ? 'Berlaku tarif flat ke semua kecamatan lain' : 'Tarif ditetapkan khusus untuk masing-masing kecamatan tujuan' }}
                                                        </small>
                                                    </td>
                                                    <td class="pe-3 py-2.5 text-end align-middle">
                                                        @if(($mobil->tipe_luar_kecamatan_wilayah ?? 'pukul_rata') === 'pukul_rata')
                                                            <span class="fw-bold text-dark fs-6">
                                                                Rp. {{ number_format($mobil->harga_luar_kecamatan_wilayah ?? 0, 0, ',', '.') }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-label-danger fs-7">Tarif Khusus Per Kecamatan</span>
                                                        @endif
                                                    </td>
                                                </tr>

                                                @if(($mobil->tipe_luar_kecamatan_wilayah ?? 'pukul_rata') === 'per_kecamatan')
                                                    <tr>
                                                        <td colspan="2" class="p-0 border-top-0">
                                                            <div class="p-3 bg-light rounded-bottom border-top">
                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span class="fw-bold text-dark small text-uppercase">
                                                                        <i class="bx bx-list-ul me-1"></i>Daftar Kecamatan yang Dilayani & Tarifnya:
                                                                    </span>
                                                                    <span class="badge bg-secondary">
                                                                        {{ count(array_filter($mobil->harga_kecamatan_khusus ?? [], fn($v) => (int)$v > 0)) }} Kecamatan Aktif
                                                                    </span>
                                                                </div>

                                                                <div class="border rounded-3 bg-white overflow-hidden shadow-xs">
                                                                    <table class="table table-sm table-hover mb-0">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th class="ps-3 py-2 text-muted small fw-semibold">Nama Kecamatan</th>
                                                                                <th class="pe-3 py-2 text-end text-muted small fw-semibold">Tarif Carter/Drop-Off</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @php
                                                                                $khusus = $mobil->harga_kecamatan_khusus ?? [];
                                                                                $activeKhususCount = 0;
                                                                            @endphp
                                                                            @foreach($khusus as $kId => $kHarga)
                                                                                @if((int)$kHarga > 0)
                                                                                    @php $activeKhususCount++; @endphp
                                                                                    <tr>
                                                                                        <td class="ps-3 py-1.5 text-dark fw-medium">
                                                                                            <i class="bx bx-chevron-right text-primary me-1"></i>
                                                                                            {{ $kecamatans[$kId]->name ?? ('Kecamatan ID #' . $kId) }}
                                                                                        </td>
                                                                                        <td class="pe-3 py-1.5 text-end fw-bold text-primary">
                                                                                            Rp. {{ number_format($kHarga, 0, ',', '.') }}
                                                                                        </td>
                                                                                    </tr>
                                                                                @endif
                                                                            @endforeach

                                                                            @if($activeKhususCount === 0)
                                                                                <tr>
                                                                                    <td colspan="2" class="text-center py-2 text-muted small fst-italic">
                                                                                        Belum ada kecamatan khusus yang diaktifkan tarifnya.
                                                                                    </td>
                                                                                </tr>
                                                                            @endif
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <!-- TABEL RINCIAN TARIF BERDASARKAN JARAK (KM) -->
                                    <div class="table-responsive border rounded-3 bg-white mb-3">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="py-2.5 ps-3 text-muted small text-uppercase fw-bold">Zonasi Jarak Tempuh</th>
                                                    <th class="py-2.5 pe-3 text-end text-muted small text-uppercase fw-bold">Tarif Borongan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="ps-3 py-2.5">
                                                        <span class="text-primary fw-semibold"><i class="bx bx-home-alt me-2 fs-5"></i>Dalam Desa (0 - {{ $mobil->batas_km_dalam_desa ?? 0 }} Km)</span>
                                                    </td>
                                                    <td class="pe-3 py-2.5 text-end fw-bold text-dark fs-6">
                                                        Rp. {{ number_format($mobil->harga_dalam_desa ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-3 py-2.5">
                                                        <span class="text-warning fw-semibold"><i class="bx bx-map me-2 fs-5"></i>Luar Desa ({{ ($mobil->batas_km_dalam_desa ?? 0) + 1 }} - {{ $mobil->batas_km_luar_desa ?? 0 }} Km)</span>
                                                    </td>
                                                    <td class="pe-3 py-2.5 text-end fw-bold text-dark fs-6">
                                                        Rp. {{ number_format($mobil->harga_luar_desa ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="ps-3 py-2.5">
                                                        <span class="text-danger fw-semibold"><i class="bx bx-buildings me-2 fs-5"></i>Luar Kota (> {{ $mobil->batas_km_luar_desa ?? 0 }} Km)</span>
                                                    </td>
                                                    <td class="pe-3 py-2.5 text-end fw-bold text-dark fs-6">
                                                        Rp. {{ number_format($mobil->harga_luar_kota ?? 0, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                @endif

                                <!-- Fitur Supir & BBM Borongan -->
                                <div class="row g-3">
                                    <!-- Supir Borongan -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 h-100 border">
                                            <div class="d-flex align-items-center gap-1 mb-1">
                                                <i class="bx bx-user-pin text-success"></i>
                                                <span class="text-muted small fw-semibold text-uppercase">Layanan Supir Borongan</span>
                                            </div>
                                            <div class="mt-1">
                                                @if($mobil->opsi_supir_borongan === 'Lepas Kunci')
                                                    <span class="badge bg-label-secondary fw-semibold fs-7 mb-1">Tanpa Supir (Bawa Sendiri)</span>
                                                    <small class="text-muted d-block" style="font-size: 0.8rem;">Penyewa membawa sendiri mobil borongan.</small>
                                                @elseif($mobil->opsi_supir_borongan === 'Dengan Supir')
                                                    <span class="badge bg-label-success fw-semibold fs-7 mb-1">Dengan Supir Pengelola</span>
                                                    <small class="text-muted d-block" style="font-size: 0.8rem;">Pengelola resmi menugaskan supir mengantar rombongan.</small>
                                                @else
                                                    <span class="badge bg-label-primary fw-semibold fs-7 mb-1">Bebas Pilih</span>
                                                    <small class="text-muted d-block" style="font-size: 0.8rem;">Warga bebas memilih sewa mandiri atau didampingi supir.</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- BBM Borongan -->
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 h-100 border">
                                            <div class="d-flex align-items-center gap-1 mb-1">
                                                <i class="bx bx-gas-pump text-warning"></i>
                                                <span class="text-muted small fw-semibold text-uppercase">Bahan Bakar (BBM) Borongan</span>
                                            </div>
                                            <div class="mt-1">
                                                @if(in_array($mobil->bbm_ditanggung_borongan, ['Pengelola', 'Pemerintah Desa']))
                                                    <span class="badge bg-success fw-semibold fs-7 mb-1">
                                                        <i class="bx bx-check me-1"></i>BBM Disediakan Pengelola
                                                    </span>
                                                    <small class="text-muted d-block" style="font-size: 0.8rem;">Tarif borongan sudah all-in termasuk bensin penuh sampai tujuan.</small>
                                                @else
                                                    <span class="badge bg-secondary fw-semibold fs-7 mb-1">
                                                        <i class="bx bx-info-circle me-1"></i>Ditanggung Penyewa
                                                    </span>
                                                    <small class="text-muted d-block" style="font-size: 0.8rem;">Biaya bensin selama perjalanan carter ditanggung penyewa.</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="p-3 bg-light rounded-3 text-muted text-center small">
                                    Layanan Sewa Borongan sedang dinonaktifkan untuk unit kendaraan ini.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .avatar-sm {
        width: 38px;
        height: 38px;
    }
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

