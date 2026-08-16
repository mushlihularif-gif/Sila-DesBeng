@extends('admin.layouts.admin')

@section('title', 'Manajemen Verifikasi Identitas')

@section('content')
<style>
    .animate-fade-up {
        animation: fadeUp 0.5s ease-out forwards;
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .filter-btn {
        border-radius: 50rem;
        padding: 0.6rem 1.2rem;
        font-weight: 600;
        transition: all 0.2s;
        border: 1px solid transparent;
        text-decoration: none;
        background: transparent;
    }
    
    /* Warna khusus untuk setiap status saat active */
    .filter-btn-primary.active {
        background-color: #0d6efd !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25) !important;
    }
    .filter-btn-warning.active {
        background-color: #ffab00 !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(255, 171, 0, 0.25) !important;
    }
    .filter-btn-success.active {
        background-color: #71dd37 !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(113, 221, 55, 0.25) !important;
    }
    .filter-btn-danger.active {
        background-color: #ff3e1d !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(255, 62, 29, 0.25) !important;
    }
    
    /* Penyesuaian badge saat active */
    .filter-btn.active .badge {
        background-color: white !important;
    }
    .filter-btn-primary.active .badge { color: #0d6efd !important; }
    .filter-btn-warning.active .badge { color: #ffab00 !important; }
    .filter-btn-success.active .badge { color: #71dd37 !important; }
    .filter-btn-danger.active .badge { color: #ff3e1d !important; }

    .filter-btn:not(.active) {
        color: #697a8d !important;
    }
    .filter-btn:not(.active):hover {
        background-color: rgba(13, 110, 253, 0.08) !important;
    }

    .table-modern {
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    .table-modern tbody tr {
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        border-radius: 8px;
        transition: all 0.2s;
        background: #fff;
    }
    .table-modern tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .table-modern td {
        border: none;
        padding: 1.2rem 1.5rem;
        vertical-align: middle;
    }
    .table-modern td:first-child { border-radius: 8px 0 0 8px; }
    .table-modern td:last-child { border-radius: 0 8px 8px 0; }
</style>

<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Sistem / Permintaan /</span> Verifikasi Identitas
            </h4>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Panduan -->
    <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex align-items-center p-4">
            <div class="me-3">
                <div class="bg-primary p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                    <i class="bx bx-shield-quarter fs-3"></i>
                </div>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-primary">Panduan Verifikasi Identitas</h5>
                <p class="mb-0 text-primary" style="opacity: 0.85;">
                    Periksa kesesuaian data KTP yang diunggah warga dengan NIK yang terbaca oleh sistem. 
                    Pilih <strong>Tinjau</strong> pada pengajuan yang berstatus <span class="badge bg-warning text-dark py-1 px-2 mx-1 rounded-pill" style="font-size: 0.7rem;">Menunggu</span> untuk melakukan persetujuan atau penolakan.
                </p>
            </div>
        </div>
    </div>

    <!-- TABS BUTTONS -->
    <ul class="nav nav-pills d-flex flex-wrap gap-2 mb-4" role="tablist" style="border: none;">
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-primary active" role="tab" data-bs-toggle="pill" data-bs-target="#navs-all">
                <i class="bx bx-list-ul me-1"></i> Semua Data
                <span class="badge rounded-pill bg-label-primary ms-1">{{ $counts['all'] }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-warning" role="tab" data-bs-toggle="pill" data-bs-target="#navs-pending">
                <i class="bx bx-time-five me-1"></i> Menunggu Verifikasi
                <span class="badge rounded-pill bg-label-warning ms-1">{{ $counts['pending'] }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-success" role="tab" data-bs-toggle="pill" data-bs-target="#navs-approved">
                <i class="bx bx-check-circle me-1"></i> Disetujui
                <span class="badge rounded-pill bg-label-success ms-1">{{ $counts['approved'] }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-danger" role="tab" data-bs-toggle="pill" data-bs-target="#navs-rejected">
                <i class="bx bx-x-circle me-1"></i> Ditolak
                <span class="badge rounded-pill bg-label-danger ms-1">{{ $counts['rejected'] }}</span>
            </button>
        </li>
    </ul>

    <!-- TABS CONTENT -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-header bg-white pt-4 pb-0 border-bottom-0">
            <h5 class="mb-4 fw-bold text-primary"><i class="bx bx-id-card fs-4 me-2 align-middle"></i> Daftar Pengajuan Verifikasi</h5>
        </div>
        <div class="card-body bg-light bg-opacity-25 pt-4">
            <div class="tab-content p-0 m-0 border-0 shadow-none bg-transparent">
                <!-- TAB: ALL -->
                <div class="tab-pane fade show active" id="navs-all" role="tabpanel">
                    @include('admin.kyc._table', ['data' => $all])
                </div>
                
                <!-- TAB: PENDING -->
                <div class="tab-pane fade" id="navs-pending" role="tabpanel">
                    @include('admin.kyc._table', ['data' => $pending])
                </div>
                
                <!-- TAB: APPROVED -->
                <div class="tab-pane fade" id="navs-approved" role="tabpanel">
                    @include('admin.kyc._table', ['data' => $approved])
                </div>
                
                <!-- TAB: REJECTED -->
                <div class="tab-pane fade" id="navs-rejected" role="tabpanel">
                    @include('admin.kyc._table', ['data' => $rejected])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
