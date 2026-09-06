@extends('admin.layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
<style>
    .user-mgmt-card {
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.06) !important;
        overflow: hidden;
    }
    .filter-card-body {
        padding: 1.25rem 1.5rem !important;
        background-color: rgba(245, 246, 248, 0.6) !important;
        border-bottom: 1px solid #f0f2f4 !important;
    }
    .modern-search {
        border: 1px solid #d9dee3;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s ease;
        background: #fff;
    }
    .modern-search .input-group-text {
        background: transparent;
        border: none;
        padding-left: 1.1rem;
        padding-right: 0.4rem;
        font-size: 1.15rem;
        color: #a1acb8;
    }
    .modern-search .form-control {
        border: none;
        padding-left: 0.4rem;
        font-size: 0.9rem;
        height: 44px;
    }
    .modern-search .form-control:focus {
        box-shadow: none;
    }
    .modern-search:focus-within {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.12) !important;
    }
    .modern-select {
        border: 1px solid #d9dee3;
        border-radius: 8px;
        font-size: 0.9rem;
        height: 44px;
        transition: all 0.2s ease;
    }
    .modern-select:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.12);
    }
    .animate-fade-up {
        animation: fadeUp 0.35s ease-out forwards;
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(12px); }
        100% { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <h4 class="fw-bold m-0"><span class="text-muted fw-light">Sistem /</span> Manajemen Pengguna</h4>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-3" role="alert">
            <i class="bx bx-check-circle fs-4 me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-3" role="alert">
            <i class="bx bx-error-circle fs-4 me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card user-mgmt-card">
        <!-- Card Header with Generous Padding & Clean Gap -->
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3.5 px-3 px-sm-4" style="padding-top: 1.25rem !important; padding-bottom: 1.25rem !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-label-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bx bx-user-check fs-4 text-primary"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold text-dark fs-5">Daftar Pengguna</h5>
                    <small class="text-muted d-none d-sm-inline" style="font-size: 0.8rem;">Kelola seluruh akun pengguna dan hak akses wilayah</small>
                </div>
            </div>
            <span class="badge bg-label-primary rounded-pill px-3 py-2 fw-semibold" style="font-size: 0.8rem;">
                {{ $users->total() }} Total Pengguna
            </span>
        </div>

        <!-- Filter & Search Bar with Generous Padding -->
        <div class="card-body filter-card-body">
            <form id="filter-form" class="row g-3 align-items-center" method="GET" action="{{ route('admin.manajemen-pengguna.index') }}">
                @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                    <div class="col-12 col-md-5">
                        <div class="input-group modern-search shadow-sm bg-white">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Cari nama, username, email..." value="{{ $search }}">
                            <button class="btn btn-primary px-4 fw-semibold" type="submit" style="font-size: 0.9rem; height: 44px;">Cari</button>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <select id="filter_kecamatan_id" name="filter_kecamatan_id" class="form-select modern-select shadow-sm bg-white">
                            <option value="">-- Semua Kecamatan --</option>
                            @foreach($kecamatanOptions as $opt)
                                <option value="{{ $opt->id }}" {{ $filter_kecamatan_id == $opt->id ? 'selected' : '' }}>
                                    {{ $opt->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <select id="filter_desa_id" name="filter_desa_id" class="form-select modern-select shadow-sm bg-white">
                            <option value="">-- Semua Desa --</option>
                            @foreach($desaOptions as $opt)
                                <option value="{{ $opt->id }}" {{ $filter_desa_id == $opt->id ? 'selected' : '' }}>
                                    {{ $opt->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @elseif(auth()->user()->role === 'admin_kecamatan')
                    <div class="col-12 col-md-7">
                        <div class="input-group modern-search shadow-sm bg-white">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Cari nama, username, email..." value="{{ $search }}">
                            <button class="btn btn-primary px-4" type="submit" style="font-weight: 600; font-size: 0.9rem; height: 44px;">Cari</button>
                        </div>
                    </div>
                    <div class="col-12 col-md-5">
                        <select id="filter_desa_id" name="filter_desa_id" class="form-select modern-select shadow-sm bg-white">
                            <option value="">-- Semua Desa --</option>
                            @foreach($desaOptions as $opt)
                                <option value="{{ $opt->id }}" {{ $filter_desa_id == $opt->id ? 'selected' : '' }}>
                                    {{ $opt->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="col-12 col-md-6">
                        <div class="input-group modern-search shadow-sm bg-white">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input type="text" id="search" name="search" class="form-control" placeholder="Cari nama, username, email..." value="{{ $search }}">
                            <button class="btn btn-primary px-4" type="submit" style="font-weight: 600; font-size: 0.9rem; height: 44px;">Cari</button>
                        </div>
                    </div>
                @endif
            </form>
        </div>

        <!-- Table Container -->
        <div id="table-container">
            @include('admin.user_management.partials.table')
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const filterKecamatan = document.getElementById('filter_kecamatan_id');
    const filterDesa = document.getElementById('filter_desa_id');
    const searchInput = document.getElementById('search');
    const tableContainer = document.getElementById('table-container');

    function initTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip && tableContainer) {
            const tooltipTriggerList = [].slice.call(tableContainer.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    function fetchFilteredData(url) {
        let params = new URLSearchParams();
        if (searchInput && searchInput.value) params.append('search', searchInput.value);
        if (filterKecamatan && filterKecamatan.value) params.append('filter_kecamatan_id', filterKecamatan.value);
        if (filterDesa && filterDesa.value) params.append('filter_desa_id', filterDesa.value);

        let finalUrl = url;
        if (url === '{{ route("admin.manajemen-pengguna.index") }}') {
            finalUrl = url + '?' + params.toString();
        } else {
            const urlObj = new URL(url);
            if (searchInput && searchInput.value) urlObj.searchParams.set('search', searchInput.value);
            if (filterKecamatan && filterKecamatan.value) urlObj.searchParams.set('filter_kecamatan_id', filterKecamatan.value);
            if (filterDesa && filterDesa.value) urlObj.searchParams.set('filter_desa_id', filterDesa.value);
            finalUrl = urlObj.toString();
        }

        tableContainer.style.opacity = '0.5';
        tableContainer.style.pointerEvents = 'none';

        fetch(finalUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';
            attachPaginationListeners();
            initTooltips();
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchFilteredData('{{ route("admin.manajemen-pengguna.index") }}');
        });
    }

    if (filterKecamatan) {
        filterKecamatan.addEventListener('change', function() {
            filterForm.submit();
        });
    }

    if (filterDesa) {
        filterDesa.addEventListener('change', function() {
            fetchFilteredData('{{ route("admin.manajemen-pengguna.index") }}');
        });
    }

    function attachPaginationListeners() {
        const links = tableContainer.querySelectorAll('.pagination a');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                fetchFilteredData(this.href);
            });
        });
    }

    attachPaginationListeners();
    initTooltips();
});
</script>
@endpush
@endsection