@extends('admin.layouts.admin')

@section('title', 'Manajemen Admin RT & RW')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container .select2-selection--single {
        height: 38px;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        color: #697a8d;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    .animate-fade-up { animation: fadeUp 0.5s ease-out forwards; }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .table-modern th {
        font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; color: #a1acb8 !important; border-bottom: 2px solid #f0f2f4; background-color: #fafbfc;
    }
    .table-modern td { vertical-align: middle; padding: 0.85rem 1.25rem; border-bottom: 1px solid #f0f2f4; transition: all 0.2s; }
    .table-modern tbody tr:hover { background-color: #f8f9fa; }

    /* Soft Badge */
    .badge-soft {
        padding: 0.35em 0.7em;
        font-weight: 600;
        border-radius: 6px;
        font-size: 0.75rem;
    }

    /* Fixed Avatar Wrapper */
    .staff-avatar-wrap {
        width: 38px !important;
        height: 38px !important;
        min-width: 38px !important;
        max-width: 38px !important;
        flex-shrink: 0;
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .staff-avatar-wrap .avatar-initial {
        width: 38px !important;
        height: 38px !important;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Mobile Card Box Container */
    .user-mobile-list {
        background: #f5f6f8;
        padding: 0.85rem 0.75rem;
        overflow-x: hidden;
        box-sizing: border-box;
        width: 100%;
    }

    /* Individual Card Box (Strictly Bounded) */
    .user-card-box {
        background: #ffffff;
        border: 1px solid #e7eaf0;
        border-radius: 12px;
        padding: 0.85rem 0.95rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 1px 4px rgba(67, 89, 113, 0.05);
        overflow: hidden !important;
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 100% !important;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .user-card-box:last-child {
        margin-bottom: 0 !important;
    }
    .user-card-box:active {
        transform: scale(0.99);
        box-shadow: 0 1px 2px rgba(67, 89, 113, 0.08);
    }

    /* Card Top Header Row */
    .user-card-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        width: 100%;
        min-width: 0;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }
    .user-card-identity {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex: 1 1 0%;
        min-width: 0;
        overflow: hidden;
    }
    .user-card-box .staff-avatar-wrap {
        width: 42px !important;
        height: 42px !important;
        min-width: 42px !important;
        max-width: 42px !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .user-card-box .staff-avatar-wrap .avatar-initial {
        width: 42px !important;
        height: 42px !important;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .user-card-text {
        flex: 1 1 0%;
        min-width: 0;
        overflow: hidden;
    }
    .user-card-name {
        font-size: 0.925rem;
        font-weight: 700;
        color: #384554;
        line-height: 1.3;
        display: block;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .user-card-username {
        font-size: 0.775rem;
        color: #8592a3;
        line-height: 1.2;
        display: block;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .user-card-badge {
        flex-shrink: 0;
        margin-left: auto;
    }

    /* Middle: Wilayah Info Badge */
    .user-card-region {
        background-color: #f8f9fa;
        border: 1px solid #edf0f5;
        border-radius: 7px;
        padding: 0.35rem 0.65rem;
        margin-bottom: 0.65rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.775rem;
        color: #566a7f;
        min-width: 0;
        overflow: hidden;
        width: 100%;
        box-sizing: border-box;
    }

    /* Bottom: Footer with Status & Action Buttons */
    .user-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 0.75rem;
        margin-top: 0.65rem;
        border-top: 1px solid #f2f4f7;
    }

    .region-status-pill {
        white-space: normal !important;
        text-align: left !important;
        display: inline-flex !important;
        align-items: center !important;
        font-size: 0.72rem !important;
        line-height: 1.35 !important;
        padding: 0.32rem 0.6rem !important;
        border-radius: 6px !important;
    }

    /* Header action buttons responsive rules */
    @media (max-width: 767.98px) {
        .header-action-btns {
            width: 100% !important;
            flex-direction: column !important;
        }
        .header-action-btns .btn {
            width: 100% !important;
        }
        .banner-action-wrap {
            width: 100% !important;
        }
        .banner-action-wrap .btn {
            width: 100% !important;
        }
    }

    /* Prevent mobile horizontal viewport overflow */
    .container-xxl, .tab-content {
        max-width: 100% !important;
        overflow-x: hidden !important;
        box-sizing: border-box !important;
    }
    .nav-pills::-webkit-scrollbar {
        display: none;
    }
    .nav-pills {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h4 class="fw-bold m-0"><span class="text-muted fw-light">Manajemen /</span> Wilayah &amp; RT/RW</h4>
            </div>
            <div class="header-action-btns d-flex flex-wrap gap-2 justify-content-md-end flex-shrink-0" style="position: relative; z-index: 10;">
                <button type="button" class="btn btn-primary shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalTambahAdmin">
                    <i class="bx bx-plus me-1"></i> Buat Akun dan Wilayah
                </button>
                <button type="button" class="btn btn-outline-primary shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modalPromosiAdmin">
                    <i class="bx bx-user-check me-1"></i> Jadikan Warga RT/RW
                </button>
            </div>
        </div>
    </div>

    @php
        $activeTab = session('active_tab', request('tab', 'pejabat'));
    @endphp
    <!-- Navigation Tabs -->
    <div class="nav-align-top mb-4 w-100" style="max-width: 100%;">
        <ul class="nav nav-pills gap-2 flex-nowrap overflow-x-auto pb-1 w-100" role="tablist" style="max-width: 100%; -webkit-overflow-scrolling: touch;">
            <li class="nav-item flex-shrink-0">
                <button type="button" class="nav-link {{ $activeTab === 'pejabat' ? 'active' : '' }} rounded-pill shadow-sm px-3 px-sm-4 py-2" role="tab" data-bs-toggle="tab" data-bs-target="#tab-pejabat" aria-controls="tab-pejabat" aria-selected="{{ $activeTab === 'pejabat' ? 'true' : 'false' }}" style="font-size: 0.85rem; white-space: nowrap;">
                    <i class="bx bx-user-pin me-1"></i> Pejabat RT &amp; RW
                </button>
            </li>
            <li class="nav-item flex-shrink-0">
                <button type="button" class="nav-link {{ $activeTab === 'struktur' ? 'active' : '' }} rounded-pill shadow-sm px-3 px-sm-4 py-2" role="tab" data-bs-toggle="tab" data-bs-target="#tab-struktur" aria-controls="tab-struktur" aria-selected="{{ $activeTab === 'struktur' ? 'true' : 'false' }}" style="font-size: 0.85rem; white-space: nowrap;">
                    <i class="bx bx-map-alt me-1"></i> Struktur Wilayah (RW &amp; RT)
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content p-0 bg-transparent shadow-none">
        <!-- TAB 1: Pejabat RT & RW -->
        <div class="tab-pane fade {{ $activeTab === 'pejabat' ? 'show active' : '' }}" id="tab-pejabat" role="tabpanel">
            <!-- Panduan -->
            <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex align-items-start align-items-sm-center p-3 p-md-4">
            <div class="me-3 flex-shrink-0">
                <div class="bg-primary p-2 p-sm-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                    <i class="bx bx-building-house fs-3"></i>
                </div>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-primary" style="font-size: 1.05rem;">Manajemen Pejabat Kewilayahan</h5>
                <p class="mb-0 text-primary" style="opacity: 0.85; font-size: 0.875rem; line-height: 1.5;">
                    Kelola akun khusus untuk para ketua/pengurus RT dan RW. Sangat direkomendasikan untuk <b>Menjadikan Warga sebagai RT atau RW</b> (memilih langsung dari daftar warga yang sudah ada di sistem). Opsi <b>Akun Dinas</b> hanya digunakan jika pejabat terkait belum memiliki akun sama sekali (gaptek).
                </p>
            </div>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center" role="alert">
            <i class="bx bx-check-circle fs-4 me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('duplicate_nik') || session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center" role="alert">
            <i class="bx bx-error-circle fs-4 me-2"></i>
            {{ session('duplicate_nik') ?? session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Pengajuan Warga -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden;">
                <div class="card-header bg-warning py-3 px-3 px-md-4">
                    <h5 class="mb-0 text-white fw-bold d-flex align-items-center">
                        <i class="bx bx-time-five fs-4 me-2"></i> Menunggu Persetujuan (Pengajuan Kemitraan)
                    </h5>
                </div>
                
                <!-- Desktop View (>= 768px) -->
                <div class="table-responsive text-nowrap d-none d-md-block">
                    <table class="table table-modern table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="py-3 ps-4">NAMA WARGA</th>
                                <th class="py-3">PENGAJUAN ROLE</th>
                                <th class="py-3">TARGET WILAYAH</th>
                                <th class="py-3 text-end pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse($applications as $app)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-md me-3 flex-shrink-0">
                                                @if ($app->user && $app->user->file)
                                                    <img src="{{ $app->user->file->file_stream }}" alt="{{ $app->applicant_name }}" class="rounded-circle shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                                @elseif ($app->user && $app->user->avatar)
                                                    <img src="{{ asset('storage/' . $app->user->avatar) }}" alt="{{ $app->applicant_name }}" class="rounded-circle shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                                @else
                                                    <span class="avatar-initial rounded-circle bg-label-warning shadow-sm fw-bold" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                                        {{ strtoupper(substr($app->applicant_name, 0, 2)) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block fs-6">{{ $app->applicant_name }}</span>
                                                <small class="text-muted"><i class="bx bx-envelope me-1"></i>{{ $app->contact_email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-label-primary px-3 py-2 rounded-pill fw-semibold">Admin {{ strtoupper($app->region_type) }}</span></td>
                                    <td>
                                        <span class="fw-bold text-dark d-block">{{ $app->region_name }}</span>
                                        @if($app->region_type === 'rt' && $app->parentRegion)
                                            <small class="text-primary fw-semibold"><i class="bx bx-subdirectory-right me-1"></i>Di bawah {{ $app->parentRegion->name }}</small>
                                        @elseif($app->region_type === 'rw')
                                            <small class="text-muted"><i class="bx bx-buildings me-1"></i>Tingkat RW</small>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.wilayah-admins.approve', $app->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui pengajuan ini?')">Setujui</button>
                                        </form>
                                        <form action="{{ route('admin.wilayah-admins.reject', $app->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tolak pengajuan ini?')">Tolak</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Tidak ada pengajuan baru.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (< 768px) -->
                <div class="d-block d-md-none">
                    @if($applications->isEmpty())
                        <div class="text-center py-4 px-3 text-muted">
                            <i class="bx bx-check-circle fs-2 text-warning mb-1 d-block"></i>
                            <span class="small fw-semibold">Tidak ada pengajuan kemitraan baru yang menunggu persetujuan.</span>
                        </div>
                    @else
                        <div class="user-mobile-list p-3">
                            @foreach($applications as $app)
                            <div class="user-card-box">
                                <div class="user-card-header-row">
                                    <div class="user-card-identity">
                                        <div class="staff-avatar-wrap">
                                            @if ($app->user && $app->user->file)
                                                <img src="{{ $app->user->file->file_stream }}" alt="{{ $app->applicant_name }}" class="rounded-circle shadow-xs" style="width: 42px; height: 42px; object-fit: cover;">
                                            @elseif ($app->user && $app->user->avatar)
                                                <img src="{{ asset('storage/' . $app->user->avatar) }}" alt="{{ $app->applicant_name }}" class="rounded-circle shadow-xs" style="width: 42px; height: 42px; object-fit: cover;">
                                            @else
                                                <span class="avatar-initial rounded-circle bg-label-warning shadow-xs fw-bold notranslate" translate="no" style="width: 42px; height: 42px; font-size: 14px; display: flex; align-items: center; justify-content: center;">
                                                    {{ strtoupper(substr($app->applicant_name, 0, 2)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="user-card-text">
                                            <span class="user-card-name" title="{{ $app->applicant_name }}">{{ $app->applicant_name }}</span>
                                            <span class="user-card-username"><i class="bx bx-envelope me-1"></i>{{ $app->contact_email }}</span>
                                        </div>
                                    </div>
                                    <span class="badge bg-label-primary badge-soft py-1 px-2 user-card-badge" style="font-size: 0.72rem; font-weight: 700;">
                                        Admin {{ strtoupper($app->region_type) }}
                                    </span>
                                </div>
                                <div class="user-card-region mb-3">
                                    <i class="bx bx-map-pin text-primary flex-shrink-0" style="font-size: 0.9rem;"></i>
                                    <span class="text-truncate fw-medium" style="min-width: 0; flex: 1 1 0%; overflow: hidden;">
                                        {{ $app->region_name }}
                                        @if($app->region_type === 'rt' && $app->parentRegion)
                                            <span class="text-muted fw-normal">(Di bawah {{ $app->parentRegion->name }})</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                                    <form action="{{ route('admin.wilayah-admins.approve', $app->id) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success px-3" onclick="return confirm('Setujui pengajuan ini?')">
                                            <i class="bx bx-check me-1"></i> Setujui
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.wilayah-admins.reject', $app->id) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-3" onclick="return confirm('Tolak pengajuan ini?')">
                                            <i class="bx bx-x me-1"></i> Tolak
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Daftar Admin RT/RW Aktif -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div>
                        <h5 class="mb-0 fw-bold text-primary d-flex align-items-center">
                            <i class="bx bx-list-check fs-4 me-2"></i> Daftar Admin RT & RW Aktif
                        </h5>
                        <small class="text-muted">Data terkelompok berdasarkan Rukun Warga (RW) dan Rukun Tetangga (RT) binaan.</small>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-md-auto">
                        <div class="input-group input-group-merge input-group-sm shadow-none flex-grow-1 flex-md-grow-0" style="min-width: 140px;">
                            <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                            <input type="text" id="searchAdminTable" class="form-control form-control-sm bg-light border-0" placeholder="Cari nama, email, RT/RW...">
                        </div>
                        <select id="filterRwTable" class="form-select form-select-sm bg-light border-0 flex-shrink-0" style="width: auto; min-width: 110px;">
                            <option value="">Semua RW</option>
                            @foreach($rws as $rw)
                                <option value="{{ $rw->name }}">{{ $rw->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Desktop & Tablet View (>= 768px) -->
                <div class="table-responsive text-nowrap d-none d-md-block">
                    <table class="table table-modern table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th class="py-3 ps-4">NAMA PEJABAT</th>
                                <th class="py-3">STATUS KYC / NIK</th>
                                <th class="py-3">ROLE</th>
                                <th class="py-3">WILAYAH KERJA</th>
                                <th class="py-3 text-end pe-4">AKSI</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0" id="adminTableBody">
                            @php
                                $currentRwGroup = null;
                            @endphp
                            @forelse($admins as $admin)
                                @php
                                    $isRw = $admin->role === 'admin_rw';
                                    $rwParentName = $isRw 
                                        ? ($admin->region->name ?? 'RW') 
                                        : ($admin->region && $admin->region->parent ? $admin->region->parent->name : 'RW Lainnya');
                                @endphp

                                {{-- Baris Pemisah Pengelompokan RW --}}
                                @if($currentRwGroup !== $rwParentName)
                                    @php $currentRwGroup = $rwParentName; @endphp
                                    <tr class="rw-group-header bg-light bg-opacity-75" data-group-rw="{{ $rwParentName }}">
                                        <td colspan="5" class="py-2 ps-4">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center text-primary fw-bold" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                                                    <i class="bx bx-buildings fs-5 me-2"></i>
                                                    <span>WILAYAH BINAAN: {{ strtoupper($rwParentName) }}</span>
                                                </div>
                                                <span class="badge bg-label-primary rounded-pill px-2 py-1" style="font-size: 0.75rem;">
                                                    Lingkup {{ $rwParentName }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endif

                                <tr class="admin-data-row" data-rw-name="{{ $rwParentName }}" data-search="{{ strtolower($admin->name . ' ' . $admin->email . ' ' . ($admin->region->name ?? '') . ' ' . $rwParentName) }}">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-md me-3 flex-shrink-0">
                                                @if ($admin->file)
                                                    <img src="{{ $admin->file->file_stream }}" alt="{{ $admin->name }}" class="rounded-circle shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                                @elseif ($admin->avatar)
                                                    <img src="{{ asset('storage/' . $admin->avatar) }}" alt="{{ $admin->name }}" class="rounded-circle shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                                @else
                                                    <span class="avatar-initial rounded-circle {{ $isRw ? 'bg-label-primary' : 'bg-label-success' }} shadow-sm fw-bold" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                                        {{ strtoupper(substr($admin->name, 0, 2)) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block fs-6">{{ $admin->name }}</span>
                                                <small class="text-muted"><i class="bx bx-envelope me-1"></i>{{ $admin->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($admin->nik)
                                            <span class="badge bg-label-success px-3 py-2 fw-semibold"><i class="bx bx-check-shield me-1"></i> NIK Terdata</span>
                                        @else
                                            <span class="badge bg-label-warning px-3 py-2 fw-semibold"><i class="bx bx-info-circle me-1"></i> Akun Dinas (Tanpa NIK)</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isRw)
                                            <span class="badge bg-label-primary px-3 py-2 rounded-pill fw-semibold"><i class="bx bx-shield-quarter me-1"></i>ADMIN RW</span>
                                        @else
                                            <span class="badge bg-label-success px-3 py-2 rounded-pill fw-semibold"><i class="bx bx-shield me-1"></i>ADMIN RT</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($isRw)
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark fs-6">{{ $admin->region->name ?? '-' }}</span>
                                                <small class="text-muted mt-1"><i class="bx bx-home-alt me-1"></i>Wilayah RW Induk</small>
                                            </div>
                                        @else
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark fs-6">{{ $admin->region->name ?? '-' }}</span>
                                                <div class="mt-1">
                                                    <span class="badge bg-label-info rounded-pill px-2 py-1" style="font-size: 0.78rem;">
                                                        <i class="bx bx-subdirectory-right me-1"></i>Bagian dari {{ $rwParentName }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.wilayah-admins.revoke', $admin->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm" onclick="return confirm('PERINGATAN: Cabut wewenang admin wilayah ini? Data akun tidak dihapus, hanya role dikembalikan menjadi warga biasa.')" title="Cabut Wewenang">
                                                <i class="bx bx-user-x me-1"></i> Cabut Akses
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada Admin RT/RW yang aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (< 768px) -->
                <div class="user-mobile-list d-block d-md-none" id="adminMobileList">
                    @php
                        $currentRwGroupMobile = null;
                    @endphp
                    @forelse($admins as $admin)
                        @php
                            $isRw = $admin->role === 'admin_rw';
                            $rwParentName = $isRw 
                                ? ($admin->region->name ?? 'RW') 
                                : ($admin->region && $admin->region->parent ? $admin->region->parent->name : 'RW Lainnya');
                        @endphp

                        {{-- Baris Pemisah Pengelompokan RW di Mobile --}}
                        @if($currentRwGroupMobile !== $rwParentName)
                            @php $currentRwGroupMobile = $rwParentName; @endphp
                            <div class="mobile-rw-header mb-2 {{ $loop->first ? 'mt-1' : 'mt-3' }} text-primary fw-bold d-flex align-items-center justify-content-between px-1" data-group-rw="{{ $rwParentName }}" style="font-size: 0.825rem; letter-spacing: 0.5px;">
                                <span><i class="bx bx-buildings me-1"></i> WILAYAH: {{ strtoupper($rwParentName) }}</span>
                                <span class="badge bg-label-primary rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                    Lingkup {{ $rwParentName }}
                                </span>
                            </div>
                        @endif

                        <div class="admin-data-card user-card-box" data-rw-name="{{ $rwParentName }}" data-search="{{ strtolower($admin->name . ' ' . $admin->email . ' ' . ($admin->region->name ?? '') . ' ' . $rwParentName) }}">
                            <!-- Top Header: Avatar + Name + Email on Left, Role Badge on Right -->
                            <div class="user-card-header-row">
                                <div class="user-card-identity">
                                    <div class="staff-avatar-wrap">
                                        @if ($admin->file)
                                            <img src="{{ $admin->file->file_stream }}" alt="{{ $admin->name }}" class="rounded-circle shadow-xs" style="width: 42px; height: 42px; object-fit: cover;">
                                        @elseif ($admin->avatar)
                                            <img src="{{ asset('storage/' . $admin->avatar) }}" alt="{{ $admin->name }}" class="rounded-circle shadow-xs" style="width: 42px; height: 42px; object-fit: cover;">
                                        @else
                                            <span class="avatar-initial rounded-circle {{ $isRw ? 'bg-label-primary' : 'bg-label-success' }} shadow-xs fw-bold notranslate" translate="no" style="width: 42px; height: 42px; font-size: 14px; display: flex; align-items: center; justify-content: center;">
                                                {{ strtoupper(substr($admin->name, 0, 2)) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="user-card-text">
                                        <span class="user-card-name" title="{{ $admin->name }}">{{ $admin->name }}</span>
                                        <span class="user-card-username"><i class="bx bx-envelope me-1"></i>{{ $admin->email }}</span>
                                    </div>
                                </div>
                                <span class="badge {{ $isRw ? 'bg-label-primary' : 'bg-label-success' }} badge-soft py-1 px-2 user-card-badge" style="font-size: 0.72rem; font-weight: 700;">
                                    {{ $isRw ? 'ADMIN RW' : 'ADMIN RT' }}
                                </span>
                            </div>

                            <!-- Middle: Wilayah Kerja Info -->
                            <div class="user-card-region mb-2">
                                <i class="bx bx-map-pin text-primary flex-shrink-0" style="font-size: 0.9rem;"></i>
                                <div class="text-truncate" style="min-width: 0; flex: 1 1 0%; overflow: hidden;">
                                    <span class="fw-bold text-dark">{{ $admin->region->name ?? '-' }}</span>
                                    @if(!$isRw)
                                        <small class="text-muted ms-1">({{ $rwParentName }})</small>
                                    @else
                                        <small class="text-muted ms-1">(RW Induk)</small>
                                    @endif
                                </div>
                            </div>

                            <!-- Bottom Row: KYC/NIK Badge on Left, Cabut Akses on Right -->
                            <div class="user-card-footer">
                                <div>
                                    @if($admin->nik)
                                        <span class="badge bg-label-success badge-soft py-1 px-2 d-inline-flex align-items-center" style="font-size: 0.73rem; font-weight: 600;">
                                            <i class="bx bx-check-shield me-1"></i> NIK Terdata
                                        </span>
                                    @else
                                        <span class="badge bg-label-warning badge-soft py-1 px-2 d-inline-flex align-items-center" style="font-size: 0.73rem; font-weight: 600;">
                                            <i class="bx bx-info-circle me-1"></i> Akun Dinas
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <form action="{{ route('admin.wilayah-admins.revoke', $admin->id) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1 d-inline-flex align-items-center shadow-xs" onclick="return confirm('PERINGATAN: Cabut wewenang admin wilayah ini? Data akun tidak dihapus, hanya role dikembalikan menjadi warga biasa.')" title="Cabut Wewenang">
                                            <i class="bx bx-user-x me-1"></i> Cabut Akses
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 px-3 text-muted">Belum ada Admin RT/RW yang aktif.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
<!-- / TAB 1: Pejabat RT & RW -->

<!-- TAB 2: Struktur Wilayah (RW & RT) -->
<div class="tab-pane fade {{ $activeTab === 'struktur' ? 'show active' : '' }}" id="tab-struktur" role="tabpanel">
        <!-- Banner Struktur Wilayah -->
        <div class="card bg-label-info border-0 shadow-none mb-4" style="border-radius: 12px;">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div class="d-flex align-items-start align-items-sm-center gap-3 flex-grow-1" style="min-width: 0;">
                        <div class="bg-info p-2 p-sm-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="bx bx-map-alt fs-3"></i>
                        </div>
                        <div style="min-width: 0; flex: 1 1 0%;">
                            <h5 class="fw-bold mb-1 text-info" style="font-size: 1.05rem;">Hierarki Wilayah: {{ auth()->user()->region->name ?? 'Desa' }}</h5>
                            <p class="mb-0 text-info" style="opacity: 0.85; font-size: 0.875rem; line-height: 1.5;">
                                Kelola seluruh unit Rukun Warga (RW) dan Rukun Tetangga (RT) di desa Anda secara terpusat. Anda dapat menambah RW/RT baru secara langsung, mengubah nama wilayah, atau menghapus wilayah yang kosong.
                            </p>
                        </div>
                    </div>
                    <div class="flex-shrink-0 banner-action-wrap mt-2 mt-md-0">
                        <button type="button" class="btn btn-info text-white rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahAdmin" style="white-space: nowrap;">
                            <i class="bx bx-plus me-1"></i> Buat Akun &amp; Wilayah Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Wilayah -->
        @php
            $totalRw = $rwStructure->count();
            $totalRt = $rwStructure->sum(fn($rw) => $rw->children->count());
            $totalWarga = $rwStructure->sum(fn($rw) => $rw->citizens_count + $rw->children->sum('citizens_count'));
            $totalUnitWilayah = $totalRw + $totalRt;
            $unitTerisi = $rwStructure->filter(fn($rw) => $rw->users->isNotEmpty())->count() + $rwStructure->sum(fn($rw) => $rw->children->filter(fn($rt) => $rt->users->isNotEmpty())->count());
        @endphp
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <span class="avatar avatar-md bg-label-primary rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center">
                            <i class="bx bx-map fs-4"></i>
                        </span>
                        <h4 class="fw-bold mb-0 text-primary">{{ $totalRw }}</h4>
                        <small class="text-muted fw-semibold">Total RW</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <span class="avatar avatar-md bg-label-info rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center">
                            <i class="bx bx-git-branch fs-4"></i>
                        </span>
                        <h4 class="fw-bold mb-0 text-info">{{ $totalRt }}</h4>
                        <small class="text-muted fw-semibold">Total RT</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <span class="avatar avatar-md bg-label-success rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center">
                            <i class="bx bx-user-check fs-4"></i>
                        </span>
                        <h4 class="fw-bold mb-0 text-success">{{ $unitTerisi }} / {{ $totalUnitWilayah }}</h4>
                        <small class="text-muted fw-semibold">Wilayah Terisi Pejabat</small>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-3 text-center">
                        <span class="avatar avatar-md bg-label-warning rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center">
                            <i class="bx bx-group fs-4"></i>
                        </span>
                        <h4 class="fw-bold mb-0 text-warning">{{ $totalWarga }}</h4>
                        <small class="text-muted fw-semibold">Warga Terdata di RW/RT</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Hierarki RW & RT -->
        @forelse($rwStructure as $rw)
            @php
                $rwAdmin = $rw->users->first();
            @endphp
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-label-primary p-2 rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bx bx-map-pin fs-4 text-primary"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="fw-bold text-dark mb-0">{{ $rw->name }}</h5>
                                @if($rwAdmin)
                                    <span class="badge bg-label-success rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                        <i class="bx bx-check-circle me-1"></i>Ketua RW: {{ $rwAdmin->name }}
                                    </span>
                                @else
                                    <span class="badge bg-label-warning rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                        <i class="bx bx-time-five me-1"></i>Belum Ada Ketua RW
                                    </span>
                                @endif
                                <span class="badge bg-label-info rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                    <i class="bx bx-git-branch me-1"></i>{{ $rw->children->count() }} RT
                                </span>
                                <span class="badge bg-label-secondary rounded-pill px-2.5 py-1" style="font-size: 0.75rem;">
                                    <i class="bx bx-user me-1"></i>{{ $rw->citizens_count }} Warga
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-2 flex-wrap w-100 w-md-auto justify-content-start justify-content-md-end">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="openTambahRtModal('{{ $rw->id }}', '{{ $rw->name }}')">
                            <i class="bx bx-plus me-1"></i> Tambah RT
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="openEditRegionModal('{{ $rw->id }}', '{{ $rw->name }}', 'rw')">
                            <i class="bx bx-edit-alt me-1"></i> Ubah Nama
                        </button>
                        @if($rw->children->isEmpty() && $rw->citizens_count == 0 && !$rwAdmin)
                            <form action="{{ route('admin.wilayah-admins.region.destroy', $rw->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus {{ $rw->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Hapus RW kosong">
                                    <i class="bx bx-trash me-1"></i> Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    @if($rw->children->isNotEmpty())
                        <!-- Desktop Table View (>= 768px) -->
                        <div class="table-responsive text-nowrap rounded-3 border d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3 py-2.5" style="font-size: 0.75rem;">NAMA RT</th>
                                        <th class="py-2.5" style="font-size: 0.75rem;">KETUA / PENGURUS RT</th>
                                        <th class="py-2.5" style="font-size: 0.75rem;">JUMLAH WARGA</th>
                                        <th class="pe-3 py-2.5 text-end" style="font-size: 0.75rem;">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rw->children as $rt)
                                        @php
                                            $rtAdmin = $rt->users->first();
                                        @endphp
                                        <tr>
                                            <td class="ps-3 fw-bold text-dark">
                                                <i class="bx bx-subdirectory-right text-muted me-1"></i> {{ $rt->name }}
                                            </td>
                                            <td>
                                                @if($rtAdmin)
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-xs me-2 flex-shrink-0">
                                                            <span class="avatar-initial rounded-circle bg-label-success fw-bold" style="font-size: 10px;">{{ strtoupper(substr($rtAdmin->name, 0, 2)) }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="fw-semibold text-dark small d-block">{{ $rtAdmin->name }}</span>
                                                            <small class="text-muted" style="font-size: 0.72rem;">{{ $rtAdmin->phone ?? $rtAdmin->email }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-label-warning rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">
                                                            Belum Ada Pejabat
                                                        </span>
                                                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-0.5 px-2" style="font-size: 0.72rem;" onclick="openTugaskanAdminRt('{{ $rt->name }}', '{{ $rw->id }}')">
                                                            + Tugaskan
                                                        </button>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-label-secondary rounded-pill">{{ $rt->citizens_count }} Warga</span>
                                            </td>
                                            <td class="pe-3 text-end">
                                                <button type="button" class="btn btn-sm btn-icon btn-outline-secondary me-1" onclick="openEditRegionModal('{{ $rt->id }}', '{{ $rt->name }}', 'rt')" title="Ubah Nama RT">
                                                    <i class="bx bx-edit-alt"></i>
                                                </button>
                                                @if($rt->citizens_count == 0 && !$rtAdmin)
                                                    <form action="{{ route('admin.wilayah-admins.region.destroy', $rt->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus {{ $rt->name }}?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus RT kosong">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View (< 768px) -->
                        <div class="d-block d-md-none">
                            <div class="d-flex flex-column gap-2">
                                @foreach($rw->children as $rt)
                                    @php
                                        $rtAdmin = $rt->users->first();
                                    @endphp
                                    <div class="p-3 bg-light bg-opacity-50 rounded-3 border">
                                        <!-- Top row: RT name and Citizen count -->
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div class="d-flex align-items-center gap-1">
                                                <i class="bx bx-subdirectory-right text-primary fs-5"></i>
                                                <span class="fw-bold text-dark fs-6">{{ $rt->name }}</span>
                                            </div>
                                            <span class="badge bg-label-secondary rounded-pill" style="font-size: 0.75rem;">
                                                <i class="bx bx-user me-1"></i>{{ $rt->citizens_count }} Warga
                                            </span>
                                        </div>

                                        <!-- Middle row: Official Admin Info -->
                                        <div class="py-2 border-top border-bottom border-light d-flex align-items-center justify-content-between gap-2">
                                            @if($rtAdmin)
                                                <div class="d-flex align-items-center" style="min-width: 0; flex: 1 1 0%;">
                                                    <div class="avatar avatar-xs me-2 flex-shrink-0">
                                                        <span class="avatar-initial rounded-circle bg-label-success fw-bold" style="font-size: 10px;">{{ strtoupper(substr($rtAdmin->name, 0, 2)) }}</span>
                                                    </div>
                                                    <div class="text-truncate" style="min-width: 0; flex: 1 1 0%;">
                                                        <span class="fw-semibold text-dark small d-block text-truncate">{{ $rtAdmin->name }}</span>
                                                        <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;">{{ $rtAdmin->phone ?? $rtAdmin->email }}</small>
                                                    </div>
                                                </div>
                                                <span class="badge bg-label-success rounded-pill px-2 py-0.5 flex-shrink-0" style="font-size: 0.7rem;">
                                                    <i class="bx bx-check me-0.5"></i>Terisi
                                                </span>
                                            @else
                                                <span class="badge bg-label-warning rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                                    <i class="bx bx-time-five me-1"></i>Belum Ada Pejabat
                                                </span>
                                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill py-1 px-2.5 shadow-none flex-shrink-0" style="font-size: 0.72rem;" onclick="openTugaskanAdminRt('{{ $rt->name }}', '{{ $rw->id }}')">
                                                    <i class="bx bx-user-plus me-1"></i>Tugaskan
                                                </button>
                                            @endif
                                        </div>

                                        <!-- Bottom row: Actions -->
                                        <div class="d-flex align-items-center justify-content-end gap-2 pt-2">
                                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-1 px-2.5" onclick="openEditRegionModal('{{ $rt->id }}', '{{ $rt->name }}', 'rt')">
                                                <i class="bx bx-edit-alt me-1"></i>Ubah Nama
                                            </button>
                                            @if($rt->citizens_count == 0 && !$rtAdmin)
                                                <form action="{{ route('admin.wilayah-admins.region.destroy', $rt->id) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus {{ $rt->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-outline-danger rounded-pill py-1 px-2.5" title="Hapus RT kosong">
                                                        <i class="bx bx-trash me-1"></i>Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 bg-light bg-opacity-50 rounded-3">
                            <i class="bx bx-git-branch text-muted fs-2 mb-1 d-block"></i>
                            <span class="text-muted small d-block mb-2">Belum ada RT yang didaftarkan di bawah {{ $rw->name }}.</span>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="openTambahRtModal('{{ $rw->id }}', '{{ $rw->name }}')">
                                <i class="bx bx-plus me-1"></i> Tambah RT Pertama di {{ $rw->name }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-5">
                    <i class="bx bx-map-pin fs-1 text-muted mb-3 d-block"></i>
                    <h6 class="fw-bold text-dark mb-1">Belum Ada Rukun Warga (RW) yang Terdaftar</h6>
                    <p class="text-muted small mb-3">Daftarkan RW pertama di desa Anda untuk mulai mengelola struktur RT dan pejabat wilayah.</p>
                    <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahAdmin">
                        <i class="bx bx-plus me-1"></i> Buat Akun &amp; Wilayah Pertama
                    </button>
                </div>
            </div>
        @endforelse
    </div>
    <!-- / TAB 2: Struktur Wilayah -->
</div>
<!-- / tab-content -->
</div>
@endsection

@section('modals')
<!-- Modal Tambah Akun Dinas (Bypass) -->
<div class="modal fade" id="modalTambahAdmin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <form action="{{ route('admin.wilayah-admins.store') }}" method="POST" class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold text-primary" id="modalTambahAdminTitle">
                    <i class="bx bx-user-plus me-2 fs-4"></i>Buat Akun &amp; Wilayah (RT/RW)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @csrf
            <div class="modal-body pt-4">
                <div class="alert alert-info d-flex align-items-start mb-4 bg-label-info border-0" role="alert">
                    <i class="bx bx-info-circle fs-4 me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading mb-1 fw-bold">Buat Akun Sekaligus Daftarkan Wilayah</h6>
                        <span style="font-size: 0.85rem; line-height: 1.4; display: block;">Gunakan formulir ini untuk membuat akun pejabat RT/RW sekaligus mendaftarkan nomor RW/RT baru secara otomatis ke database desa jika belum ada. Jika nomor wilayah yang dimasukkan sudah memiliki admin aktif, sistem akan mencegah duplikasi.</span>
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark">Nama Lengkap Pejabat</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-user"></i></span>
                            <input type="text" name="name" class="form-control" style="padding-left: 10px !important;" placeholder="Contoh: Budi Santoso" required>
                        </div>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark">Email Resmi / Pribadi</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                            <input type="email" name="email" class="form-control" style="padding-left: 10px !important;" placeholder="rt01@SiladesBeng.com" required>
                        </div>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark">Nomor WhatsApp</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bxl-whatsapp"></i></span>
                            <input type="text" name="phone" class="form-control" style="padding-left: 10px !important;" placeholder="081234567890" required>
                        </div>
                    </div>
                    <!-- Tingkat Jabatan -->
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark">Tingkat Jabatan (Role)</label>
                        <select name="role" id="roleTambah" class="form-select form-select-lg" required onchange="handleRoleChange('tambah')">
                            <option value="">-- Pilih Role --</option>
                            <option value="admin_rw">Pengurus RW (Ketua/Wakil RW)</option>
                            <option value="admin_rt">Pengurus RT (Ketua/Wakil RT)</option>
                        </select>
                    </div>

                    <!-- Area Penentuan Wilayah Tugas (Dinamis Sesuai Data Riil) -->
                    <div class="col-12 mb-2">
                        <!-- Placeholder Sebelum Memilih Role -->
                        <div id="placeholderWilayahTambah" class="p-3 bg-light rounded-3 text-center text-muted border border-dashed">
                            <i class="bx bx-lock-alt fs-4 d-block mb-1 text-secondary"></i>
                            <span class="small fw-semibold">Pilih Tingkat Jabatan di atas terlebih dahulu untuk menentukan nomor wilayah tugas.</span>
                        </div>

                        <!-- Form Khusus RW -->
                        <div id="sectionRwTambah" class="d-none">
                            <label class="form-label fw-bold text-dark">
                                <i class="bx bx-buildings text-primary me-1"></i> Nomor RW yang Dipimpin
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white fw-bold px-3">RW</span>
                                <input type="text" name="rw_number" id="rwNumberTambah" class="form-control form-control-lg fw-bold" style="padding-left: 16px !important;" placeholder="Ketik nomor, contoh: 02 atau 2" oninput="checkRwStatus('tambah')" autocomplete="off">
                            </div>
                            <!-- Feedback Status RW -->
                            <div id="rwFeedbackTambah" class="mt-2"></div>
                            <!-- Real Data Reference Pills -->
                            <div class="mt-2 p-2 bg-light rounded-2 border">
                                <small class="text-muted d-block mb-1 fw-semibold"><i class="bx bx-info-circle me-1"></i> Status RW di Desa Saat Ini:</small>
                                <div id="rwPillsTambah" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>

                        <!-- Form Khusus RT -->
                        <div id="sectionRtTambah" class="d-none">
                            <label class="form-label fw-bold text-dark">
                                <i class="bx bx-home text-success me-1"></i> 1. Tentukan Nomor RT yang Dipimpin
                            </label>
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-success text-white fw-bold px-3">RT</span>
                                <input type="text" name="rt_number" id="rtNumberTambah" class="form-control form-control-lg fw-bold" style="padding-left: 16px !important;" placeholder="Ketik nomor, contoh: 02 atau 2" oninput="checkRtStatus('tambah')" autocomplete="off">
                            </div>

                            <label class="form-label fw-bold text-dark">
                                <i class="bx bx-map-pin text-primary me-1"></i> 2. Tentukan RW Induk (RT ini berada di RW berapa)
                            </label>
                            <select name="parent_rw_id" id="parentRwTambah" class="form-select form-select-lg" onchange="checkRtStatus('tambah')">
                                <option value="">-- Pilih RW Induk --</option>
                            </select>

                            <!-- Feedback Status RT -->
                            <div id="rtFeedbackTambah" class="mt-2"></div>
                            <!-- Real Data Reference Pills for Selected RW -->
                            <div id="rtReferenceBoxTambah" class="mt-2 p-2 bg-light rounded-2 border d-none">
                                <small class="text-muted d-block mb-1 fw-semibold"><i class="bx bx-info-circle me-1"></i> Status RT di RW Terpilih:</small>
                                <div id="rtPillsTambah" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark">NIK (Nomor Induk Kependudukan)</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                            <input type="text" name="nik" id="nikTambah" class="form-control" style="padding-left: 10px !important;" required placeholder="16 Digit NIK">
                        </div>
                        <small class="text-muted mt-1 d-block" id="nikHelper"><i class="bx bx-shield-alt-2 text-success"></i> Jika NIK sudah terdaftar, sistem akan memberikan peringatan keamanan pintar.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top pt-3">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" id="btnSubmitTambah" class="btn btn-primary shadow-sm"><i class="bx bx-save me-1"></i> Simpan Akun &amp; Wilayah</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Promosi Akun Warga -->
<div class="modal fade" id="modalPromosiAdmin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
        <form action="{{ route('admin.wilayah-admins.promote') }}" method="POST" class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold text-primary" id="modalPromosiAdminTitle">
                    <i class="bx bx-user-plus me-2 fs-4"></i>Beri Akses Admin RT/RW ke Warga
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @csrf
            <div class="modal-body pt-4">
                <div class="alert alert-primary d-flex align-items-start mb-4 bg-label-primary border-0" role="alert">
                    <i class="bx bx-info-circle fs-4 me-3 mt-1"></i>
                    <div>
                        <h6 class="alert-heading mb-1 fw-bold">Panduan Penunjukan Admin</h6>
                        <span style="font-size: 0.85rem; line-height: 1.4; display: block;">Gunakan formulir ini untuk mencari akun warga yang telah terdaftar di <strong>SiladesBeng</strong>, lalu ubah statusnya menjadi pengurus RT atau RW agar mereka dapat mengelola data kependudukan wilayahnya.</span>
                    </div>
                </div>
                
                <div class="row g-3">
                    <!-- Bagian 1: Cari & Pilih Akun Warga -->
                    <!-- Bagian 1: Cari & Pilih Akun Warga -->
                    <div class="col-12 mb-2" id="wargaSearchWrapper">
                        <label class="form-label fw-bold text-dark"><i class="bx bx-search-alt text-primary me-1"></i> 1. Cari &amp; Pilih Akun Warga</label>
                        <div class="position-relative">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-white"><i class="bx bx-search text-muted"></i></span>
                                <input type="text" id="wargaSearchInput" class="form-control form-control-lg" style="padding-left: 12px !important;" placeholder="Cari nama, email, atau nomor HP..." autocomplete="off" oninput="filterWargaSearch()" onfocus="filterWargaSearch()">
                            </div>
                            <input type="hidden" name="user_email" id="selectedUserEmail" required>
                            
                            <!-- Dropdown Hasil Pencarian Warga -->
                            <div id="wargaSearchResults" class="position-absolute w-100 bg-white border rounded-3 shadow-lg mt-1 overflow-auto d-none" style="max-height: 240px; z-index: 1060; border-color: #d9dee3 !important;">
                                <!-- Item list rendered by JS -->
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block"><i class="bx bx-help-circle"></i> Ketik nama, email, atau no. WhatsApp untuk memilih akun warga.</small>
                    </div>

                    <!-- Profil Warga Terpilih (Menggantikan input saat sudah dipilih) -->
                    <div class="col-12 mb-3 d-none" id="wargaDetailPreview">
                        <label class="form-label fw-bold text-dark mb-2"><i class="bx bx-user-check text-primary me-1"></i> 1. AKUN WARGA TERPILIH</label>
                        <div class="card border shadow-none bg-white rounded-3 overflow-hidden" style="padding: 18px 18px; border-color: #d9dee3 !important;">
                            <div class="d-flex align-items-center justify-content-between gap-3" style="width: 100%; max-width: 100%; min-width: 0;">
                                <div class="d-flex align-items-center min-w-0" style="flex: 1 1 0%; min-width: 0; overflow: hidden;">
                                    <div id="previewAvatarContainer" class="flex-shrink-0" style="margin-right: 16px !important;">
                                        <!-- Injected by JS: Image or Initials -->
                                    </div>
                                    <div class="min-w-0" style="flex: 1 1 0%; min-width: 0; overflow: hidden;">
                                        <h6 class="fw-bold text-dark text-truncate" id="previewName" style="font-size: 0.95rem; max-width: 100%; margin-bottom: 5px !important;">Nama Warga</h6>
                                        <div class="text-muted d-flex align-items-center" style="font-size: 0.82rem; max-width: 100%; overflow: hidden; margin-bottom: 4px !important;">
                                            <i class="bx bx-envelope text-primary flex-shrink-0" style="margin-right: 8px !important;"></i>
                                            <span id="previewEmail" class="text-truncate" style="flex: 1 1 0%; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">email</span>
                                        </div>
                                        <div class="text-muted d-flex align-items-center" style="font-size: 0.82rem; max-width: 100%; overflow: hidden; margin-bottom: 8px !important;">
                                            <i class="bx bxl-whatsapp text-success flex-shrink-0" style="margin-right: 8px !important;"></i>
                                            <span id="previewPhone" class="text-truncate" style="flex: 1 1 0%; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">phone</span>
                                        </div>
                                        <div>
                                            <span class="badge bg-label-secondary" id="previewNik" style="font-size: 0.7rem; font-weight: 600; padding: 0.25rem 0.55rem;">BELUM KTP</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-sm btn-outline-primary px-3 rounded-2 d-inline-flex align-items-center shadow-xs" onclick="clearWargaSelection()" title="Ganti Warga Lain" style="font-size: 0.8rem; font-weight: 600; padding-top: 6px !important; padding-bottom: 6px !important;">
                                        <i class="bx bx-refresh me-1"></i>
                                        <span>Ganti</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-2">
                        <label class="form-label fw-bold text-dark"><i class="bx bx-briefcase text-primary me-1"></i> 2. Tetapkan Jabatan Baru</label>
                        <select name="role" id="rolePromosi" class="form-select form-select-lg" required onchange="handleRoleChange('promosi')">
                            <option value="">-- Pilih Tingkat Jabatan --</option>
                            <option value="admin_rw">Pengurus RW (Ketua/Wakil RW)</option>
                            <option value="admin_rt">Pengurus RT (Ketua/Wakil RT)</option>
                        </select>
                    </div>

                    <!-- Area Penentuan Wilayah Tugas (Dinamis Sesuai Data Riil) -->
                    <div class="col-12 mb-2">
                        <!-- Placeholder Sebelum Memilih Role -->
                        <div id="placeholderWilayahPromosi" class="p-3 bg-light rounded-3 text-center text-muted border border-dashed">
                            <i class="bx bx-lock-alt fs-4 d-block mb-1 text-secondary"></i>
                            <span class="small fw-semibold">Pilih Tingkat Jabatan di atas terlebih dahulu untuk menentukan nomor wilayah tugas.</span>
                        </div>

                        <!-- Form Khusus RW -->
                        <div id="sectionRwPromosi" class="d-none">
                            <label class="form-label fw-bold text-dark">
                                <i class="bx bx-buildings text-primary me-1"></i> 3. Tentukan Nomor RW yang Dipimpin
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary text-white fw-bold px-3">RW</span>
                                <input type="text" name="rw_number" id="rwNumberPromosi" class="form-control form-control-lg fw-bold" style="padding-left: 16px !important;" placeholder="Ketik nomor, contoh: 02 atau 2" oninput="checkRwStatus('promosi')" autocomplete="off">
                            </div>
                            <!-- Feedback Status RW -->
                            <div id="rwFeedbackPromosi" class="mt-2"></div>
                            <!-- Real Data Reference Pills -->
                            <div class="mt-2 p-2 bg-light rounded-2 border">
                                <small class="text-muted d-block mb-1 fw-semibold"><i class="bx bx-info-circle me-1"></i> Status RW di Desa Saat Ini:</small>
                                <div id="rwPillsPromosi" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>

                        <!-- Form Khusus RT -->
                        <div id="sectionRtPromosi" class="d-none">
                            <label class="form-label fw-bold text-dark">
                                <i class="bx bx-home text-success me-1"></i> 3. Tentukan Nomor RT yang Dipimpin
                            </label>
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-success text-white fw-bold px-3">RT</span>
                                <input type="text" name="rt_number" id="rtNumberPromosi" class="form-control form-control-lg fw-bold" style="padding-left: 16px !important;" placeholder="Ketik nomor, contoh: 02 atau 2" oninput="checkRtStatus('promosi')" autocomplete="off">
                            </div>

                            <label class="form-label fw-bold text-dark">
                                <i class="bx bx-map-pin text-primary me-1"></i> 4. Tentukan RW Induk (RT ini berada di RW berapa)
                            </label>
                            <select name="parent_rw_id" id="parentRwPromosi" class="form-select form-select-lg" onchange="checkRtStatus('promosi')">
                                <option value="">-- Pilih RW Induk --</option>
                            </select>

                            <!-- Feedback Status RT -->
                            <div id="rtFeedbackPromosi" class="mt-2"></div>
                            <!-- Real Data Reference Pills for Selected RW -->
                            <div id="rtReferenceBoxPromosi" class="mt-2 p-2 bg-light rounded-2 border d-none">
                                <small class="text-muted d-block mb-1 fw-semibold"><i class="bx bx-info-circle me-1"></i> Status RT di RW Terpilih:</small>
                                <div id="rtPillsPromosi" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top pt-3">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" id="btnSubmitPromosi" class="btn btn-primary shadow-sm"><i class="bx bx-check-circle me-1"></i> Tetapkan Jadi Admin</button>
            </div>
        </form>
    </div>
</div>


<!-- Modal Edit Nama Wilayah -->
<div class="modal fade" id="modalEditWilayah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="formEditRegion" action="" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-primary" id="edit_region_title">
                    <i class="bx bx-edit-alt me-2 fs-4"></i>Ubah Nama Wilayah
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @csrf
            @method('PUT')
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small text-uppercase" for="edit_region_name">Nama Wilayah <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="edit_region_name" class="form-control" required>
                    <small class="text-muted" style="font-size: 0.75rem;">Anda dapat menambahkan keterangan dusun, misalnya: <em>RW 01 (Dusun Timur)</em></small>
                </div>
            </div>
            <div class="modal-footer bg-light px-4 py-3 rounded-bottom-4 border-top">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary shadow-sm"><i class="bx bx-save me-1"></i>Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const rwAdminMap = @json($rwAdminMap ?? []);
    const rtAdminMap = @json($rtAdminMap ?? []);
    const wargaJson = @json($wargaJson ?? []);

    function formatUnitNumber(type, val) {
        if (!val) return '';
        let clean = val.toString().trim();
        clean = clean.replace(/^(rw|rt)[\s\.\-]*/i, '').trim();
        if (/^\d+$/.test(clean)) {
            return type.toUpperCase() + ' ' + (clean.length === 1 ? '0' + clean : clean);
        }
        return type.toUpperCase() + ' ' + clean.toUpperCase();
    }

    function normalizeDigits(name) {
        if (!name) return '';
        const lower = name.toLowerCase().replace(/[^a-z0-9]/g, '');
        return lower.replace(/^(rw|rt)0*(\d+)$/, '$1$2');
    }

    function handleRoleChange(mode) {
        const roleEl = document.getElementById(mode === 'tambah' ? 'roleTambah' : 'rolePromosi');
        const role = roleEl ? roleEl.value : '';
        const placeholder = document.getElementById(mode === 'tambah' ? 'placeholderWilayahTambah' : 'placeholderWilayahPromosi');
        const secRw = document.getElementById(mode === 'tambah' ? 'sectionRwTambah' : 'sectionRwPromosi');
        const secRt = document.getElementById(mode === 'tambah' ? 'sectionRtTambah' : 'sectionRtPromosi');
        const rwInput = document.getElementById(mode === 'tambah' ? 'rwNumberTambah' : 'rwNumberPromosi');
        const rtInput = document.getElementById(mode === 'tambah' ? 'rtNumberTambah' : 'rtNumberPromosi');
        const parentRw = document.getElementById(mode === 'tambah' ? 'parentRwTambah' : 'parentRwPromosi');

        if (!secRw || !secRt) return;

        // Populate parent RW dropdown
        if (parentRw) {
            parentRw.innerHTML = '<option value="">-- Pilih RW Induk --</option>';
            rwAdminMap.forEach(rw => {
                parentRw.innerHTML += `<option value="${rw.id}">${rw.name}</option>`;
            });
        }

        // Render RW reference pills
        renderRwPills(mode);

        if (role === 'admin_rw') {
            if (placeholder) placeholder.classList.add('d-none');
            secRw.classList.remove('d-none');
            secRt.classList.add('d-none');
            if (rwInput) {
                rwInput.required = true;
                rwInput.focus();
            }
            if (rtInput) rtInput.required = false;
            if (parentRw) parentRw.required = false;
            checkRwStatus(mode);
        } else if (role === 'admin_rt') {
            if (placeholder) placeholder.classList.add('d-none');
            secRw.classList.add('d-none');
            secRt.classList.remove('d-none');
            if (rwInput) rwInput.required = false;
            if (rtInput) {
                rtInput.required = true;
                rtInput.focus();
            }
            if (parentRw) parentRw.required = true;
            checkRtStatus(mode);
        } else {
            if (placeholder) placeholder.classList.remove('d-none');
            secRw.classList.add('d-none');
            secRt.classList.add('d-none');
            if (rwInput) rwInput.required = false;
            if (rtInput) rtInput.required = false;
            if (parentRw) parentRw.required = false;
            setSubmitState(mode, false);
        }
    }

    function renderRwPills(mode) {
        const pillsContainer = document.getElementById(mode === 'tambah' ? 'rwPillsTambah' : 'rwPillsPromosi');
        if (!pillsContainer) return;
        if (!rwAdminMap || rwAdminMap.length === 0) {
            pillsContainer.innerHTML = '<span class="badge bg-label-secondary region-status-pill">Belum ada RW terdaftar di desa ini</span>';
            return;
        }
        let html = '';
        rwAdminMap.forEach(rw => {
            if (rw.has_admin) {
                html += `<span class="badge bg-label-danger region-status-pill me-1 mb-1 shadow-xs" title="Sudah memiliki Admin: ${rw.admin_name}"><i class="bx bx-x-circle me-1 flex-shrink-0"></i><span><strong>${rw.name}</strong> &bull; Terisi (${rw.admin_name})</span></span>`;
            } else {
                html += `<a href="javascript:void(0)" onclick="quickSelectRw('${mode}', '${rw.name}')" class="badge bg-label-success region-status-pill me-1 mb-1 shadow-xs text-decoration-none" title="Klik untuk memilih RW ini"><i class="bx bx-check-circle me-1 flex-shrink-0"></i><span><strong>${rw.name}</strong> &bull; Tersedia</span></a>`;
            }
        });
        pillsContainer.innerHTML = html;
    }

    function quickSelectRw(mode, rwName) {
        const rwInput = document.getElementById(mode === 'tambah' ? 'rwNumberTambah' : 'rwNumberPromosi');
        if (rwInput) {
            rwInput.value = rwName.replace(/^RW\s*/i, '');
            checkRwStatus(mode);
        }
    }

    function checkRwStatus(mode) {
        const input = document.getElementById(mode === 'tambah' ? 'rwNumberTambah' : 'rwNumberPromosi');
        const feedback = document.getElementById(mode === 'tambah' ? 'rwFeedbackTambah' : 'rwFeedbackPromosi');
        if (!input || !feedback) return;

        const val = input.value.trim();

        if (!val) {
            feedback.innerHTML = '<small class="text-muted"><i class="bx bx-info-circle me-1"></i> Masukkan nomor RW (contoh: 02 atau 2).</small>';
            setSubmitState(mode, false);
            return;
        }

        const formatted = formatUnitNumber('RW', val);
        const normVal = normalizeDigits(formatted);

        // Cari di data riil database
        const matched = rwAdminMap.find(rw => normalizeDigits(rw.name) === normVal);

        if (matched) {
            if (matched.has_admin) {
                feedback.innerHTML = `
                    <div class="alert alert-danger py-2 px-3 mb-0 d-flex align-items-center rounded-3 shadow-xs">
                        <i class="bx bx-x-circle fs-4 me-2 flex-shrink-0 text-danger"></i>
                        <div class="small">
                            <strong class="text-danger">${matched.name} Sudah Memiliki Admin!</strong><br>
                            Dipimpin oleh: <strong>${matched.admin_name}</strong> (${matched.admin_email}).<br>
                            <span class="text-muted">RW ini sudah terisi. Silakan tentukan nomor RW lain (misal: 02, 03).</span>
                        </div>
                    </div>`;
                setSubmitState(mode, false);
            } else {
                feedback.innerHTML = `
                    <div class="alert alert-success py-2 px-3 mb-0 d-flex align-items-center rounded-3 shadow-xs">
                        <i class="bx bx-check-circle fs-4 me-2 flex-shrink-0 text-success"></i>
                        <div class="small">
                            <strong class="text-success">${matched.name} Tersedia!</strong><br>
                            <span class="text-muted">Wilayah RW ini sudah terdaftar di desa dan belum memiliki admin.</span>
                        </div>
                    </div>`;
                setSubmitState(mode, true);
            }
        } else {
            // RW baru yang belum ada di database
            feedback.innerHTML = `
                <div class="alert alert-info py-2 px-3 mb-0 d-flex align-items-center rounded-3 shadow-xs">
                    <i class="bx bx-plus-circle fs-4 me-2 flex-shrink-0 text-info"></i>
                    <div class="small">
                        <strong class="text-info">${formatted} Baru &amp; Tersedia!</strong><br>
                        <span class="text-muted">Sistem akan otomatis mendaftarkan <strong>${formatted}</strong> baru di database desa saat disimpan.</span>
                    </div>
                </div>`;
            setSubmitState(mode, true);
        }
    }

    function checkRtStatus(mode) {
        const rtInput = document.getElementById(mode === 'tambah' ? 'rtNumberTambah' : 'rtNumberPromosi');
        const parentRwSelect = document.getElementById(mode === 'tambah' ? 'parentRwTambah' : 'parentRwPromosi');
        const feedback = document.getElementById(mode === 'tambah' ? 'rtFeedbackTambah' : 'rtFeedbackPromosi');
        const refBox = document.getElementById(mode === 'tambah' ? 'rtReferenceBoxTambah' : 'rtReferenceBoxPromosi');
        const pillsContainer = document.getElementById(mode === 'tambah' ? 'rtPillsTambah' : 'rtPillsPromosi');

        if (!rtInput || !parentRwSelect || !feedback) return;

        const rtVal = rtInput.value.trim();
        const rwId = parentRwSelect.value;
        const selectedRwName = parentRwSelect.options[parentRwSelect.selectedIndex]?.text || '';

        // Render RT pills untuk RW yang dipilih
        if (rwId && refBox && pillsContainer) {
            refBox.classList.remove('d-none');
            const rtsInRw = rtAdminMap.filter(rt => rt.parent_id == rwId);
            if (rtsInRw.length === 0) {
                pillsContainer.innerHTML = `<span class="badge bg-label-secondary region-status-pill">Belum ada RT terdaftar di ${selectedRwName}</span>`;
            } else {
                let html = '';
                rtsInRw.forEach(rt => {
                    if (rt.has_admin) {
                        html += `<span class="badge bg-label-danger region-status-pill me-1 mb-1 shadow-xs" title="Sudah memiliki Admin: ${rt.admin_name}"><i class="bx bx-x-circle me-1 flex-shrink-0"></i><span><strong>${rt.name}</strong> &bull; Terisi (${rt.admin_name})</span></span>`;
                    } else {
                        html += `<a href="javascript:void(0)" onclick="quickSelectRt('${mode}', '${rt.name}')" class="badge bg-label-success region-status-pill me-1 mb-1 shadow-xs text-decoration-none" title="Klik untuk memilih RT ini"><i class="bx bx-check-circle me-1 flex-shrink-0"></i><span><strong>${rt.name}</strong> &bull; Tersedia</span></a>`;
                    }
                });
                pillsContainer.innerHTML = html;
            }
        } else if (refBox) {
            refBox.classList.add('d-none');
        }

        if (!rtVal) {
            feedback.innerHTML = '<small class="text-muted"><i class="bx bx-info-circle me-1"></i> Tentukan nomor RT (contoh: 02 atau 2).</small>';
            setSubmitState(mode, false);
            return;
        }

        if (!rwId) {
            feedback.innerHTML = '<div class="alert alert-warning py-2 px-3 mb-0 small rounded-3 shadow-xs"><i class="bx bx-error-circle me-1"></i> Harap pilih <strong>RW Induk</strong> di atas untuk memeriksa ketersediaan RT.</div>';
            setSubmitState(mode, false);
            return;
        }

        const formattedRt = formatUnitNumber('RT', rtVal);
        const normRtVal = normalizeDigits(formattedRt);

        // Cari di data riil RT di bawah RW yang dipilih
        const matched = rtAdminMap.find(rt => rt.parent_id == rwId && normalizeDigits(rt.name) === normRtVal);

        if (matched) {
            if (matched.has_admin) {
                feedback.innerHTML = `
                    <div class="alert alert-danger py-2 px-3 mb-0 d-flex align-items-center rounded-3 shadow-xs">
                        <i class="bx bx-x-circle fs-4 me-2 flex-shrink-0 text-danger"></i>
                        <div class="small">
                            <strong class="text-danger">${matched.name} di ${selectedRwName} Sudah Memiliki Admin!</strong><br>
                            Dipimpin oleh: <strong>${matched.admin_name}</strong> (${matched.admin_email}).<br>
                            <span class="text-muted">RT ini sudah terisi. Silakan tentukan nomor RT lain (misal: 02, 03).</span>
                        </div>
                    </div>`;
                setSubmitState(mode, false);
            } else {
                feedback.innerHTML = `
                    <div class="alert alert-success py-2 px-3 mb-0 d-flex align-items-center rounded-3 shadow-xs">
                        <i class="bx bx-check-circle fs-4 me-2 flex-shrink-0 text-success"></i>
                        <div class="small">
                            <strong class="text-success">${matched.name} di ${selectedRwName} Tersedia!</strong><br>
                            <span class="text-muted">Unit RT ini sudah terdaftar dan belum memiliki admin.</span>
                        </div>
                    </div>`;
                setSubmitState(mode, true);
            }
        } else {
            // RT baru yang belum ada di database
            feedback.innerHTML = `
                <div class="alert alert-info py-2 px-3 mb-0 d-flex align-items-center rounded-3 shadow-xs">
                    <i class="bx bx-plus-circle fs-4 me-2 flex-shrink-0 text-info"></i>
                    <div class="small">
                        <strong class="text-info">${formattedRt} di ${selectedRwName} Baru &amp; Tersedia!</strong><br>
                        <span class="text-muted">Sistem akan otomatis mendaftarkan <strong>${formattedRt}</strong> di bawah <strong>${selectedRwName}</strong> saat disimpan.</span>
                    </div>
                </div>`;
            setSubmitState(mode, true);
        }
    }

    function quickSelectRt(mode, rtName) {
        const rtInput = document.getElementById(mode === 'tambah' ? 'rtNumberTambah' : 'rtNumberPromosi');
        if (rtInput) {
            rtInput.value = rtName.replace(/^RT\s*/i, '');
            checkRtStatus(mode);
        }
    }

    function setSubmitState(mode, enabled) {
        const btn = document.getElementById(mode === 'tambah' ? 'btnSubmitTambah' : 'btnSubmitPromosi');
        if (btn) {
            btn.disabled = !enabled;
            if (!enabled) {
                btn.classList.add('opacity-50');
            } else {
                btn.classList.remove('opacity-50');
            }
        }
    }

    function getAvatarHtml(w, size = 36) {
        if (w.photo) {
            return `<img src="${w.photo}" alt="${w.name}" class="rounded-circle shadow-xs flex-shrink-0" style="width: ${size}px; height: ${size}px; object-fit: cover;" onerror="this.outerHTML='<span class=\\'avatar-initial rounded-circle bg-label-primary shadow-xs fw-bold notranslate flex-shrink-0\\' style=\\'width: ${size}px; height: ${size}px; font-size: ${Math.round(size * 0.38)}px; display: inline-flex; align-items: center; justify-content: center;\\'>${w.initials || 'WA'}</span>'">`;
        }
        return `<span class="avatar-initial rounded-circle bg-label-primary shadow-xs fw-bold notranslate flex-shrink-0" style="width: ${size}px; height: ${size}px; font-size: ${Math.round(size * 0.38)}px; display: inline-flex; align-items: center; justify-content: center;">${w.initials || 'WA'}</span>`;
    }

    function filterWargaSearch() {
        const input = document.getElementById('wargaSearchInput');
        const resultsBox = document.getElementById('wargaSearchResults');
        const query = (input ? input.value : '').toLowerCase().trim();

        if (!resultsBox) return;

        let filtered = wargaJson;
        if (query) {
            filtered = wargaJson.filter(w => {
                const name = (w.name || '').toLowerCase();
                const email = (w.email || '').toLowerCase();
                const phone = (w.phone || '').toLowerCase();
                return name.includes(query) || email.includes(query) || phone.includes(query);
            });
        }

        if (filtered.length === 0) {
            resultsBox.innerHTML = '<div class="p-3 text-center text-muted small"><i class="bx bx-info-circle me-1"></i> Tidak ditemukan akun warga dengan kata kunci tersebut.</div>';
            resultsBox.classList.remove('d-none');
            return;
        }

        let html = '<div class="list-group list-group-flush p-1">';
        filtered.forEach(w => {
            const safeEmail = w.email.replace(/'/g, "\\'");
            const avatarHtml = getAvatarHtml(w, 38);
            const badgeClass = w.is_verified ? 'bg-label-success' : 'bg-label-secondary';
            html += `
                <a href="javascript:void(0)" onclick="selectWargaItem('${safeEmail}')" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between rounded-2 mb-1 border-0" style="padding: 10px 12px !important; transition: background 0.15s ease;">
                    <div class="d-flex align-items-center min-w-0 me-2" style="flex: 1 1 0%; min-width: 0; overflow: hidden;">
                        <div class="flex-shrink-0" style="margin-right: 14px !important;">${avatarHtml}</div>
                        <div class="min-w-0" style="flex: 1 1 0%; min-width: 0; overflow: hidden;">
                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem; margin-bottom: 2px !important;">${w.name}</div>
                            <div class="text-muted text-truncate d-flex align-items-center" style="font-size: 0.78rem; margin-bottom: 2px !important;">
                                <i class="bx bx-envelope text-primary flex-shrink-0" style="margin-right: 7px !important;"></i>
                                <span class="text-truncate">${w.email}</span>
                            </div>
                            <div class="text-muted text-truncate d-flex align-items-center" style="font-size: 0.78rem;">
                                <i class="bx bxl-whatsapp text-success flex-shrink-0" style="margin-right: 7px !important;"></i>
                                <span class="text-truncate">${w.phone}</span>
                            </div>
                        </div>
                    </div>
                    <span class="badge ${badgeClass} flex-shrink-0 py-1 px-2" style="font-size: 0.68rem; font-weight: 600;">${w.nik_status}</span>
                </a>
            `;
        });
        html += '</div>';

        resultsBox.innerHTML = html;
        resultsBox.classList.remove('d-none');
    }

    function selectWargaItem(email) {
        const warga = wargaJson.find(w => w.email === email);
        if (!warga) return;

        document.getElementById('selectedUserEmail').value = warga.email;
        document.getElementById('wargaSearchResults').classList.add('d-none');
        document.getElementById('wargaSearchWrapper').classList.add('d-none');

        // Update preview card
        document.getElementById('previewName').innerText = warga.name;
        document.getElementById('previewEmail').innerText = warga.email;
        document.getElementById('previewPhone').innerText = warga.phone || '-';
        document.getElementById('previewNik').innerText = warga.nik_status;
        document.getElementById('previewNik').className = `badge ${warga.is_verified ? 'bg-label-success' : 'bg-label-secondary'} py-1 px-2`;
        document.getElementById('previewAvatarContainer').innerHTML = getAvatarHtml(warga, 40);
        document.getElementById('wargaDetailPreview').classList.remove('d-none');
    }

    function clearWargaSelection() {
        document.getElementById('selectedUserEmail').value = '';
        document.getElementById('wargaSearchInput').value = '';
        document.getElementById('wargaDetailPreview').classList.add('d-none');
        document.getElementById('wargaSearchResults').classList.add('d-none');
        document.getElementById('wargaSearchWrapper').classList.remove('d-none');
        document.getElementById('wargaSearchInput').focus();
    }

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('wargaSearchWrapper');
        const resultsBox = document.getElementById('wargaSearchResults');
        if (wrapper && resultsBox && !wrapper.contains(e.target)) {
            resultsBox.classList.add('d-none');
        }
    });

    // Jika ada session duplicate NIK, buka kembali modal tambah dan ubah NIK menjadi opsional
    @if(session('duplicate_nik'))
    document.addEventListener('DOMContentLoaded', function() {
        var modalTambah = new bootstrap.Modal(document.getElementById('modalTambahAdmin'));
        modalTambah.show();
        
        // Ubah NIK menjadi opsional
        const nikInput = document.getElementById('nikTambah');
        nikInput.required = false;
        nikInput.value = ''; // Kosongkan agar bisa dilanjut submit sebagai Akun Dinas
        
        const nikHelper = document.getElementById('nikHelper');
        nikHelper.innerHTML = "<span class='text-danger fw-bold'>* Peringatan NIK Ganda terpicu. Kolom NIK ini sekarang berstatus Opsional. Kosongkan untuk melanjutkan membuat Akun Dinas terpisah.</span>";
    });
    @endif

    // Filter dan Search Tabel & Kartu Admin RT/RW
    const searchInput = document.getElementById('searchAdminTable');
    const filterRwSelect = document.getElementById('filterRwTable');
    
    function filterAdminTable() {
        if (!searchInput || !filterRwSelect) return;
        const query = searchInput.value.toLowerCase().trim();
        const selectedRw = filterRwSelect.value.trim();
        
        // 1. Filter Desktop Table Rows
        const rows = document.querySelectorAll('.admin-data-row');
        const headers = document.querySelectorAll('.rw-group-header');
        const visibleGroups = new Set();

        rows.forEach(row => {
            const searchData = (row.getAttribute('data-search') || '').toLowerCase();
            const rowRw = row.getAttribute('data-rw-name') || '';
            
            const matchesQuery = !query || searchData.includes(query);
            const matchesRw = !selectedRw || rowRw === selectedRw;
            
            if (matchesQuery && matchesRw) {
                row.style.display = '';
                visibleGroups.add(rowRw);
            } else {
                row.style.display = 'none';
            }
        });
        
        headers.forEach(header => {
            const groupRw = header.getAttribute('data-group-rw');
            header.style.display = visibleGroups.has(groupRw) ? '' : 'none';
        });

        // 2. Filter Mobile Cards
        const cards = document.querySelectorAll('.admin-data-card');
        const mobileHeaders = document.querySelectorAll('.mobile-rw-header');
        const visibleMobileGroups = new Set();

        cards.forEach(card => {
            const searchData = (card.getAttribute('data-search') || '').toLowerCase();
            const cardRw = card.getAttribute('data-rw-name') || '';
            
            const matchesQuery = !query || searchData.includes(query);
            const matchesRw = !selectedRw || cardRw === selectedRw;
            
            if (matchesQuery && matchesRw) {
                card.style.display = '';
                visibleMobileGroups.add(cardRw);
            } else {
                card.style.display = 'none';
            }
        });

        mobileHeaders.forEach(mHeader => {
            const groupRw = mHeader.getAttribute('data-group-rw');
            mHeader.style.display = visibleMobileGroups.has(groupRw) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterAdminTable);
    if (filterRwSelect) filterRwSelect.addEventListener('change', filterAdminTable);

    function openTambahRtModal(parentRwId, parentRwName) {
        var modal = new bootstrap.Modal(document.getElementById('modalTambahAdmin'));
        var roleSelect = document.getElementById('roleTambah');
        if (roleSelect) {
            roleSelect.value = 'admin_rt';
            handleRoleChange('tambah');
        }
        var parentSelect = document.getElementById('parentRwTambah');
        if (parentSelect) {
            parentSelect.value = parentRwId;
            checkRtStatus('tambah');
        }
        modal.show();
    }

    function openEditRegionModal(id, currentName, type) {
        var form = document.getElementById('formEditRegion');
        if (form) {
            form.action = "{{ url('admin/wilayah-admins/region') }}/" + id;
        }
        var nameInput = document.getElementById('edit_region_name');
        if (nameInput) {
            nameInput.value = currentName;
        }
        var titleElem = document.getElementById('edit_region_title');
        if (titleElem) {
            titleElem.innerHTML = '<i class="bx bx-edit-alt me-2 fs-4"></i>Ubah Nama ' + (type === 'rw' ? 'RW' : 'RT');
        }
        var modal = new bootstrap.Modal(document.getElementById('modalEditWilayah'));
        modal.show();
    }

    function openTugaskanAdminRt(rtName, parentRwId) {
        var modalPromosi = new bootstrap.Modal(document.getElementById('modalPromosiAdmin'));
        var roleSelect = document.getElementById('rolePromosi');
        if (roleSelect) {
            roleSelect.value = 'admin_rt';
            handleRoleChange('promosi');
        }
        var rwSelect = document.getElementById('parentRwPromosi');
        if (rwSelect) {
            rwSelect.value = parentRwId;
            checkRtStatus('promosi');
        }
        var rtInput = document.getElementById('rtNumberPromosi');
        if (rtInput) {
            var num = rtName.replace(/[^0-9]/g, '');
            rtInput.value = num || rtName;
            checkRtStatus('promosi');
        }
        modalPromosi.show();
    }

    // Tab switching event handling & URL sync
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function(tabBtn) {
        tabBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var targetSelector = this.getAttribute('data-bs-target');
            if (!targetSelector) return;
            var targetPane = document.querySelector(targetSelector);
            if (!targetPane) return;

            // Activate tab button
            document.querySelectorAll('.nav-pills .nav-link').forEach(function(btn) {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');

            // Activate tab pane
            document.querySelectorAll('.tab-content > .tab-pane').forEach(function(pane) {
                pane.classList.remove('show', 'active');
            });
            targetPane.classList.add('show', 'active');

            // Update URL without reloading
            var tabName = targetSelector === '#tab-struktur' ? 'struktur' : 'pejabat';
            var url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url);
        });
    });
</script>
@endsection


