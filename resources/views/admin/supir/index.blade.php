@extends('admin.layouts.admin')

@section('title', 'Manajemen Supir & Petugas')

@section('content')
<style>
    .supir-stat-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        background: #fff;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .supir-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.08) !important;
    }
    .supir-table th {
        font-size: 0.73rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        font-weight: 700;
        color: #8592a3 !important;
        background-color: #f8fafc;
        border-bottom: 1px solid #eef1f6;
    }
    .supir-table td {
        vertical-align: middle;
        padding: 0.95rem 1.25rem;
        border-bottom: 1px solid #f1f4f9;
    }
</style>

@php
    $totalSupirCount = (int) ($countSupir ?? 0);
    $totalPengurusCount = (int) ($countPengurus ?? 0);
    $totalAllPersonil = $totalSupirCount + $totalPengurusCount;
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Layanan Desa /</span> Data Supir &amp; Petugas
            </h4>
            <p class="text-muted small mb-0">Kelola personil pengemudi armada transportasi serta petugas pemegang kunci gedung dan fasilitas desa.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto">
            <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm flex-grow-1 flex-md-grow-0 d-inline-flex align-items-center justify-content-center gap-1.5" data-bs-toggle="modal" data-bs-target="#addSupirModal">
                <i class="bx bx-plus-circle fs-5"></i>
                <span>Tambah Supir</span>
            </button>
            @if(auth()->user()->hasUnitPermission('fasilitas_umum'))
            <button type="button" class="btn btn-success rounded-pill px-3 shadow-sm flex-grow-1 flex-md-grow-0 d-inline-flex align-items-center justify-content-center gap-1.5" data-bs-toggle="modal" data-bs-target="#addPengurusModal">
                <i class="bx bx-building-house fs-5"></i>
                <span>Tambah Pengurus</span>
            </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-check-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-error-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-error-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ $errors->first() }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Info Banner & KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card bg-label-primary border-0 shadow-none rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bx bx-user-check fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Pemisahan Tugas Personil Lapangan</h6>
                        <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                            Data <strong>Supir Kendaraan</strong> digunakan untuk penugasan armada Penyewaan Transportasi &amp; Kendaraan Operasional, sedangkan <strong>Pengurus Gedung</strong> khusus untuk penanggung jawab kunci Balai Pertemuan, Gedung Serbaguna, atau Fasilitas Publik.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="supir-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Supir Armada</span>
                        <h4 class="fw-bold text-primary mb-0" id="stat-count-supir">{{ $totalSupirCount }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="supir-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Pengurus Gedung</span>
                        <h4 class="fw-bold text-success mb-0" id="stat-count-pengurus">{{ $totalPengurusCount }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="supir-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Total Personil</span>
                        <h4 class="fw-bold text-dark mb-0" id="stat-count-total">{{ $totalAllPersonil }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar: Nav Pills Tabs AJAX + Search -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3">
            <div class="bg-light p-1 rounded-pill border border-light-subtle d-flex flex-nowrap gap-1 overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                <button type="button" class="btn btn-tab-filter rounded-pill px-3 px-sm-4 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 transition-all flex-fill text-nowrap {{ ($tab ?? 'supir') === 'supir' ? 'btn-primary shadow-sm text-white' : 'btn-light text-secondary' }}" data-tab="supir" onclick="switchTab('supir')" style="font-size: 0.84rem;">
                    <i class="bx bx-car fs-5"></i>
                    <span>Supir Kendaraan</span>
                    <span class="badge rounded-pill bg-white text-primary ms-1 px-2 py-0.5" id="badge-count-supir" style="font-size: 0.72rem;">{{ $countSupir ?? 0 }}</span>
                </button>
                <button type="button" class="btn btn-tab-filter rounded-pill px-3 px-sm-4 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 transition-all flex-fill text-nowrap {{ ($tab ?? 'supir') === 'pengurus_gedung' ? 'btn-success shadow-sm text-white' : 'btn-light text-secondary' }}" data-tab="pengurus_gedung" onclick="switchTab('pengurus_gedung')" style="font-size: 0.84rem;">
                    <i class="bx bx-building-house fs-5"></i>
                    <span>Pengurus Gedung</span>
                    <span class="badge rounded-pill bg-white text-success ms-1 px-2 py-0.5" id="badge-count-pengurus" style="font-size: 0.72rem;">{{ $countPengurus ?? 0 }}</span>
                </button>
            </div>

            <div class="input-group input-group-merge input-group-sm" style="max-width: 320px;">
                <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" id="searchSupirInput" class="form-control bg-light border-0" placeholder="Cari nama, no. WA, atau status..." oninput="filterSupirItems()">
            </div>
        </div>
    </div>

    <!-- Loading Spinner AJAX -->
    <div id="tabLoading" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Memuat data...</span>
        </div>
        <p class="text-muted mt-2 small">Memuat daftar personil...</p>
    </div>

    <!-- Tampilan Desktop (Tabel) -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden d-none d-md-block" id="desktopCardWrapper">
        <div class="table-responsive text-nowrap">
            <table class="table supir-table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" id="th-col-nama">Profil {{ ($tab ?? 'supir') === 'pengurus_gedung' ? 'Pengurus' : 'Supir' }}</th>
                        <th>Kontak dan Akun</th>
                        <th>Peran dan Kategori</th>
                        <th>Status Kesiagaan</th>
                        <th class="text-center pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0" id="tableBodyContainer">
                    @include('admin.supir.partials.table_rows', ['supirs' => $supirs, 'tab' => $tab])
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tampilan Mobile (Card View) -->
    <div class="d-block d-md-none" id="mobileCardsContainer">
        @include('admin.supir.partials.mobile_cards', ['supirs' => $supirs, 'tab' => $tab])
    </div>
</div>
@endsection

@push('modals')
<!-- Container Modal Edit & Detail Dinamis -->
<div id="modalsContainer">
    @include('admin.supir.partials.modals', ['supirs' => $supirs, 'users' => $users, 'tab' => $tab])
</div>

<!-- Add Modal 1: Tambah Supir Baru -->
<div class="modal fade" id="addSupirModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center">
                    <i class="bx bx-user-plus text-primary me-2 fs-4"></i> Tambah Supir Kendaraan Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('supir.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="tipe" value="supir">
                @if(Auth::user()->role === 'super_admin')
                <input type="hidden" name="region_id" value="{{ \App\Models\Region::whereIn('type', ['desa', 'kelurahan'])->value('id') }}">
                @endif
                <div class="modal-body p-4">
                    <div class="alert alert-primary d-flex mb-4 rounded-3 border-0" role="alert">
                        <i class="bx bx-bulb fs-4 me-3 mt-1"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1">Panduan Supir Kendaraan</h6>
                            <p class="mb-0 small">Data supir ini akan tersedia untuk ditugaskan pada pesanan <strong>Penyewaan Transportasi</strong> dan <strong>Kendaraan Operasional Desa</strong>.</p>
                        </div>
                    </div>

                    <!-- Photo Upload -->
                    <div class="d-flex flex-column flex-sm-row align-items-center text-center text-sm-start gap-3 mb-4 p-3 p-sm-4 bg-label-secondary rounded-4 border-0">
                        <div class="position-relative flex-shrink-0">
                            <div id="preview_add_supir" class="rounded-circle bg-white d-flex align-items-center justify-content-center shadow-sm border border-2 border-white text-primary" style="width: 80px; height: 80px;">
                                <i class="bx bx-camera fs-1"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="fw-bold mb-1">Foto Profil Supir (Opsional)</h6>
                            <p class="text-muted mb-2" style="font-size: 0.8rem;">Gunakan rasio 1:1 (persegi). Maksimal 8MB.</p>
                            <div class="d-flex align-items-center justify-content-center justify-content-sm-start gap-2 flex-wrap">
                                <label for="foto_upload_supir" class="btn btn-sm btn-primary cursor-pointer shadow-sm rounded-pill px-3">
                                    <i class="bx bx-upload me-1"></i> Pilih Foto...
                                </label>
                                <input type="file" name="foto" id="foto_upload_supir" class="d-none" accept="image/*" onchange="previewImage(this, 'preview_add_supir', 'filename_add_supir')">
                                <span id="filename_add_supir" class="text-muted small text-truncate" style="max-width: 180px;">Belum ada file dipilih</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Nama Lengkap Supir <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" name="nama" class="form-control" placeholder="Cth: Budi Santoso" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">No. WhatsApp</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bxl-whatsapp"></i></span>
                                <input type="text" name="kontak" class="form-control" placeholder="Cth: 08123456789">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 bg-label-info p-3 rounded-4 border-0">
                        <label class="form-label text-uppercase fw-bold text-info mb-1" style="font-size: 0.75rem;"><i class="bx bx-link-alt me-1"></i>Tautkan Akun Aplikasi (Opsional)</label>
                        <p class="text-info mb-2" style="font-size: 0.75rem;">Notifikasi order atau kendaraan operasional akan dikirimkan ke akun warga ini.</p>
                        <select name="user_id" class="form-select bg-white border-info text-dark shadow-none py-2" style="cursor: pointer; font-size: 0.88rem;">
                            <option value="" selected>-- Tidak Ditautkan (Hanya Data Profil) --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="border rounded-4 p-3 h-100 border-gray-200">
                                <label class="form-label text-uppercase text-muted fw-bold mb-3" style="font-size: 0.75rem;">Layanan Aktif <span class="text-danger">*</span></label>
                                <div class="d-flex flex-column gap-3">
                                    @if(auth()->user()->hasUnitPermission('sewa_mobil'))
                                    <div class="form-check form-switch d-flex align-items-center">
                                        <input class="form-check-input mt-0 me-3 cursor-pointer" type="checkbox" name="is_sewa_mobil" value="1" id="is_rental_add" style="width: 2.5em; height: 1.25em;" checked>
                                        <label class="form-check-label cursor-pointer fw-bold text-dark" for="is_rental_add">Penyewaan Transportasi</label>
                                    </div>
                                    @endif
                                    @if(auth()->user()->hasUnitPermission('fasilitas_umum'))
                                    <div class="form-check form-switch d-flex align-items-center">
                                        <input class="form-check-input mt-0 me-3 cursor-pointer bg-danger border-danger" type="checkbox" name="is_fasilitas_umum" value="1" id="is_fasilitas_add" style="width: 2.5em; height: 1.25em;" checked>
                                        <label class="form-check-label cursor-pointer fw-bold text-danger" for="is_fasilitas_add">Kendaraan Operasional Darurat</label>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="border rounded-4 p-3 h-100 border-gray-200">
                                <label class="form-label text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem;">Status Awal <span class="text-danger">*</span></label>
                                <select name="status" class="form-select mb-2 shadow-none" required>
                                    <option value="Tersedia" selected>Tersedia (Aktif)</option>
                                    <option value="Tidak Aktif">Tidak Aktif (Cuti/Sakit)</option>
                                </select>
                                <small class="text-muted d-block" style="font-size:0.7rem;">Pilih status awal kesiagaan supir.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4">
                    <button type="button" class="btn btn-label-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"><i class="bx bx-save me-2"></i>Simpan Supir</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Modal 2: Tambah Pengurus Gedung Baru -->
<div class="modal fade" id="addPengurusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center">
                    <i class="bx bx-building-house me-2 fs-4"></i> Tambah Pengurus / Pemegang Kunci Gedung
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('supir.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="tipe" value="pengurus_gedung">
                @if(Auth::user()->role === 'super_admin')
                <input type="hidden" name="region_id" value="{{ \App\Models\Region::whereIn('type', ['desa', 'kelurahan'])->value('id') }}">
                @endif
                <div class="modal-body p-4">
                    <div class="alert alert-success d-flex mb-4 rounded-3 border-0 bg-label-success" role="alert">
                        <i class="bx bx-info-circle fs-4 me-3 mt-1 text-success"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1 text-success">Fungsi Pengurus Gedung</h6>
                            <p class="mb-0 small text-dark">Data ini khusus untuk <strong>Petugas / Pemegang Kunci</strong> Gedung Serbaguna, Balai Pertemuan, atau Ruang Publik. Kontak mereka akan ditampilkan kepada warga peminjam gedung untuk koordinasi serah terima kunci dan fasilitas.</p>
                        </div>
                    </div>

                    <!-- Photo Upload -->
                    <div class="d-flex flex-column flex-sm-row align-items-center text-center text-sm-start gap-3 mb-4 p-3 p-sm-4 bg-label-secondary rounded-4 border-0">
                        <div class="position-relative flex-shrink-0">
                            <div id="preview_add_pengurus" class="rounded-circle bg-white d-flex align-items-center justify-content-center shadow-sm border border-2 border-white text-success" style="width: 80px; height: 80px;">
                                <i class="bx bx-camera fs-1"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <h6 class="fw-bold mb-1">Foto Profil Petugas (Opsional)</h6>
                            <p class="text-muted mb-2" style="font-size: 0.8rem;">Gunakan rasio 1:1 (persegi). Maksimal 8MB.</p>
                            <div class="d-flex align-items-center justify-content-center justify-content-sm-start gap-2 flex-wrap">
                                <label for="foto_upload_pengurus" class="btn btn-sm btn-success cursor-pointer shadow-sm rounded-pill px-3">
                                    <i class="bx bx-upload me-1"></i> Pilih Foto...
                                </label>
                                <input type="file" name="foto" id="foto_upload_pengurus" class="d-none" accept="image/*" onchange="previewImage(this, 'preview_add_pengurus', 'filename_add_pengurus')">
                                <span id="filename_add_pengurus" class="text-muted small text-truncate" style="max-width: 180px;">Belum ada file dipilih</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">Nama Lengkap Pengurus <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input type="text" name="nama" class="form-control" placeholder="Cth: Pak Slamet (Pemegang Kunci Balai)" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase text-muted fw-bold" style="font-size: 0.75rem;">No. WhatsApp Aktif <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bxl-whatsapp"></i></span>
                                <input type="text" name="kontak" class="form-control" placeholder="Cth: 08123456789" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 bg-label-info p-3 rounded-4 border-0">
                        <label class="form-label text-uppercase fw-bold text-info mb-1" style="font-size: 0.75rem;"><i class="bx bx-link-alt me-1"></i>Tautkan Akun Aplikasi (Opsional)</label>
                        <p class="text-info mb-2" style="font-size: 0.75rem;">Jika petugas memiliki akun warga, tautkan untuk mempermudah koordinasi.</p>
                        <select name="user_id" class="form-select bg-white border-info text-dark shadow-none py-2" style="cursor: pointer; font-size: 0.88rem;">
                            <option value="" selected>-- Tidak Ditautkan (Hanya Data Profil) --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-7">
                            <div class="border rounded-4 p-3 h-100 border-gray-200">
                                <label class="form-label text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem;">Peruntukan Tugas</label>
                                <div class="alert alert-success border-0 mb-0 py-2 px-3 rounded-3">
                                    <div class="fw-bold font-13"><i class="bx bx-check-circle me-1"></i> Khusus Fasilitas Umum</div>
                                    <small class="text-muted d-block mt-0.5">Petugas ini hanya akan muncul saat memilih penugasan di modul Gedung dan Ruang Publik.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="border rounded-4 p-3 h-100 border-gray-200">
                                <label class="form-label text-uppercase text-muted fw-bold mb-2" style="font-size: 0.75rem;">Status Kesiapan <span class="text-danger">*</span></label>
                                <select name="status" class="form-select mb-2 shadow-none" required>
                                    <option value="Tersedia" selected>Tersedia (Aktif)</option>
                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                </select>
                                <small class="text-muted d-block" style="font-size:0.7rem;">Status ketersediaan petugas.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4">
                    <button type="button" class="btn btn-label-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm"><i class="bx bx-save me-2"></i>Simpan Pengurus Gedung</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
let currentActiveTab = '{{ $tab ?? "supir" }}';

function filterSupirItems() {
    const input = document.getElementById('searchSupirInput');
    const q = (input ? input.value : '').toLowerCase().trim();
    document.querySelectorAll('#tableBodyContainer tr, #mobileCardsContainer > .card').forEach(el => {
        if (el.querySelector('.empty-state')) return;
        const text = (el.textContent || '').toLowerCase();
        el.style.display = (!q || text.includes(q)) ? '' : 'none';
    });
}

function switchTab(tab) {
    if (tab === currentActiveTab) return;
    currentActiveTab = tab;

    // Update styling tab buttons
    document.querySelectorAll('.btn-tab-filter').forEach(btn => {
        const btnTab = btn.getAttribute('data-tab');
        if (btnTab === tab) {
            if (tab === 'pengurus_gedung') {
                btn.className = 'btn btn-tab-filter rounded-pill px-3 px-sm-4 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 transition-all flex-fill text-nowrap btn-success shadow-sm text-white';
            } else {
                btn.className = 'btn btn-tab-filter rounded-pill px-3 px-sm-4 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 transition-all flex-fill text-nowrap btn-primary shadow-sm text-white';
            }
        } else {
            btn.className = 'btn btn-tab-filter rounded-pill px-3 px-sm-4 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 transition-all flex-fill text-nowrap btn-light text-secondary';
        }
    });

    // Update Header Tabel
    const thNama = document.getElementById('th-col-nama');
    if (thNama) {
        thNama.textContent = tab === 'pengurus_gedung' ? 'Profil Pengurus' : 'Profil Supir';
    }

    // Tampilkan Loading
    const loading = document.getElementById('tabLoading');
    const tableBody = document.getElementById('tableBodyContainer');
    const mobileContainer = document.getElementById('mobileCardsContainer');
    const modalsContainer = document.getElementById('modalsContainer');

    if (loading) loading.classList.remove('d-none');
    if (tableBody) tableBody.style.opacity = '0.3';
    if (mobileContainer) mobileContainer.style.opacity = '0.3';

    // Update URL history tanpa reload
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    window.history.pushState({}, '', url);

    // Fetch via AJAX
    fetch('{{ route("supir.index") }}?tab=' + tab + '&ajax_tab=1', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.success) {
            if (tableBody) tableBody.innerHTML = data.table_html;
            if (mobileContainer) mobileContainer.innerHTML = data.cards_html;
            if (modalsContainer) modalsContainer.innerHTML = data.modals_html;

            // Update badge counts
            const bSupir = document.getElementById('badge-count-supir');
            const bPengurus = document.getElementById('badge-count-pengurus');
            const sSupir = document.getElementById('stat-count-supir');
            const sPengurus = document.getElementById('stat-count-pengurus');
            const sTotal = document.getElementById('stat-count-total');
            if (bSupir) bSupir.textContent = data.count_supir;
            if (bPengurus) bPengurus.textContent = data.count_pengurus;
            if (sSupir) sSupir.textContent = data.count_supir;
            if (sPengurus) sPengurus.textContent = data.count_pengurus;
            if (sTotal) sTotal.textContent = (parseInt(data.count_supir || 0) + parseInt(data.count_pengurus || 0));

            filterSupirItems();
        }
    })
    .catch(err => {
        console.error('Gagal memuat data tab:', err);
    })
    .finally(() => {
        if (loading) loading.classList.add('d-none');
        if (tableBody) tableBody.style.opacity = '1';
        if (mobileContainer) mobileContainer.style.opacity = '1';
    });
}

function previewImage(input, previewId, filenameId) {
    var preview = document.getElementById(previewId);
    var filename = document.getElementById(filenameId);
    
    if (input.files && input.files[0]) {
        filename.textContent = input.files[0].name;
        
        if (preview.tagName.toLowerCase() !== 'img') {
            var img = document.createElement('img');
            img.id = previewId;
            img.className = 'rounded-circle object-fit-cover shadow-sm border border-2 border-white';
            img.style.width = '85px';
            img.style.height = '85px';
            preview.parentNode.replaceChild(img, preview);
            preview = img;
        }
        
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
