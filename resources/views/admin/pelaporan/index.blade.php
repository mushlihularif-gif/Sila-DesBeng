@extends('admin.layouts.admin')

@section('title', isset($isArchive) && $isArchive ? 'Bukti Pelaporan Warga' : 'Pelaporan Warga')

@section('styles')
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
        padding: 0.45rem 0.9rem;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.2s;
        border: 1px solid transparent;
        text-decoration: none;
        background: transparent;
        white-space: nowrap;
    }
    @media (min-width: 768px) {
        .filter-btn {
            padding: 0.6rem 1.2rem;
            font-size: 0.9375rem;
        }
    }
    .filter-btn-secondary.active {
        background-color: #697a8d !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(105, 122, 141, 0.25) !important;
    }
    .filter-btn-warning.active {
        background-color: #ffab00 !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(255, 171, 0, 0.25) !important;
    }
    .filter-btn-info.active {
        background-color: #03c3ec !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(3, 195, 236, 0.25) !important;
    }
    .filter-btn-primary.active {
        background-color: #0d6efd !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25) !important;
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
    .filter-btn:not(.active) { color: #697a8d !important; }
    .filter-btn:not(.active):hover { background-color: rgba(105, 122, 141, 0.08) !important; }

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
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    
    <!-- Banner Modern -->
    <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex align-items-center justify-content-between p-4 flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="bg-primary p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                        <i class="bx {{ isset($isArchive) && $isArchive ? 'bx-archive' : 'bx-message-rounded-dots' }} fs-3"></i>
                    </div>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-primary">
                        {{ isset($isArchive) && $isArchive ? 'Bukti Pelaporan Warga' : 'Pelaporan Warga' }}
                    </h5>
                    <p class="mb-0 text-primary" style="opacity: 0.85;">
                        {{ isset($isArchive) && $isArchive ? 'Arsip rekam jejak keluhan warga yang telah selesai ditindaklanjuti.' : 'Pantau dan tanggapi keluhan, saran, serta laporan dari warga secara terpusat.' }}
                    </p>
                </div>
            </div>
            
            <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="location.reload()">
                <i class="bx bx-refresh me-1"></i> Refresh
            </button>
        </div>
    </div>

    @if(!isset($isArchive) || !$isArchive)
    <!-- Statistik -->
    <div class="row g-2 g-md-3 mb-4 row-cols-2 row-cols-md-3 row-cols-lg-6">
        <!-- Total Laporan -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-secondary-subtle text-secondary rounded-circle p-2 mb-2">
                        <i class="bx bx-folder fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">Total Laporan</small>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['total']) }}</h4>
                </div>
            </div>
        </div>
        <!-- Pending -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-warning-subtle text-warning rounded-circle p-2 mb-2">
                        <i class="bx bx-time fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">Pending</small>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['pending']) }}</h4>
                </div>
            </div>
        </div>
        <!-- Proses -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-info-subtle text-info rounded-circle p-2 mb-2">
                        <i class="bx bx-cog fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">Sedang Proses</small>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['proses']) }}</h4>
                </div>
            </div>
        </div>
        <!-- Dilanjutkan -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-primary-subtle text-primary rounded-circle p-2 mb-2">
                        <i class="bx bx-right-arrow-alt fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">Eskalasi</small>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['dilanjutkan']) }}</h4>
                </div>
            </div>
        </div>
        <!-- Selesai -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-success-subtle text-success rounded-circle p-2 mb-2">
                        <i class="bx bx-check-circle fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">Selesai</small>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['selesai']) }}</h4>
                </div>
            </div>
        </div>
        <!-- SLA Terlewat -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-2 p-md-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-danger-subtle text-danger rounded-circle p-2 mb-2">
                        <i class="bx bx-error fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1 text-truncate w-100" style="font-size: 0.65rem;">SLA Terlewat</small>
                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($stats['overdue']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons (Pills) -->
    <ul class="nav nav-pills mb-4 d-flex flex-wrap gap-2">
        <li class="nav-item">
            <button type="button" class="nav-link filter-btn filter-status-btn filter-btn-secondary {{ !request('status') ? 'active' : '' }}" data-status-filter="all">
                <i class="bx bx-list-ul me-1"></i> Semua
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link filter-btn filter-status-btn filter-btn-warning {{ request('status') == 'Pending' ? 'active' : '' }}" data-status-filter="Pending">
                <i class="bx bx-time me-1"></i> Pending
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link filter-btn filter-status-btn filter-btn-info {{ request('status') == 'Proses' ? 'active' : '' }}" data-status-filter="Proses">
                <i class="bx bx-cog me-1"></i> Proses
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link filter-btn filter-status-btn filter-btn-primary {{ request('status') == 'Dilanjutkan' ? 'active' : '' }}" data-status-filter="Dilanjutkan">
                <i class="bx bx-right-arrow-alt me-1"></i> Eskalasi (Dilanjutkan)
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link filter-btn filter-status-btn filter-btn-success {{ request('status') == 'Selesai' ? 'active' : '' }}" data-status-filter="Selesai">
                <i class="bx bx-check-circle me-1"></i> Selesai
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link filter-btn filter-status-btn filter-btn-danger {{ request('status') == 'Ditolak' ? 'active' : '' }}" data-status-filter="Ditolak">
                <i class="bx bx-x-circle me-1"></i> Ditolak
            </button>
        </li>
    </ul>
    @endif

    <!-- Filter Form & Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-3 p-md-4">
            <form id="filter-form" action="{{ isset($isArchive) && $isArchive ? route('admin.pelaporan.archive') : route('admin.pelaporan.index') }}" method="GET" class="row g-3 align-items-center">
                <input type="hidden" name="status" id="filter-status" value="{{ request('status') }}">

                <div class="col-12 col-md-5">
                    <div class="input-group input-group-merge shadow-sm" style="border-radius: 8px; overflow: hidden; border: 1px solid #d9dee3;">
                        <span class="input-group-text bg-white border-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" name="search" id="filter-search" class="form-control bg-white border-0 ps-0 shadow-none" placeholder="Cari nama pelapor atau deskripsi keluhan..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-merge shadow-sm" style="border-radius: 8px; overflow: hidden; border: 1px solid #d9dee3;">
                        <span class="input-group-text bg-white border-0"><i class="bx bx-category text-muted"></i></span>
                        <select name="kategori" id="filter-kategori" class="form-select bg-white border-0 text-capitalize shadow-none">
                            <option value="">Semua Kategori Keluhan</option>
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-12 col-md-3">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary shadow-sm flex-grow-1" style="border-radius: 8px;"><i class="bx bx-filter-alt me-2"></i>Filter Data</button>
                        <button type="button" id="btn-reset-filter" class="btn btn-light shadow-sm border px-3" style="border-radius: 8px;" title="Reset Filter"><i class="bx bx-reset text-secondary"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div id="table-container" class="card-body p-0 position-relative" style="transition: opacity 0.2s ease;">
            @include('admin.pelaporan.partials.table')
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableContainer = document.getElementById('table-container');
        const filterForm = document.getElementById('filter-form');
        const searchInput = document.getElementById('filter-search');
        const kategoriSelect = document.getElementById('filter-kategori');
        const statusInput = document.getElementById('filter-status');
        const statusButtons = document.querySelectorAll('.filter-status-btn');
        const resetBtn = document.getElementById('btn-reset-filter');
        const baseRoute = "{{ isset($isArchive) && $isArchive ? route('admin.pelaporan.archive') : route('admin.pelaporan.index') }}";

        // Main AJAX fetch function
        function fetchLaporan(url) {
            if (!tableContainer) return;
            
            tableContainer.style.opacity = '0.4';
            tableContainer.style.pointerEvents = 'none';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Gagal mengambil data laporan');
                return response.text();
            })
            .then(html => {
                tableContainer.innerHTML = html;
                tableContainer.style.opacity = '1';
                tableContainer.style.pointerEvents = 'auto';
                
                // Re-bind pagination & empty state reset button
                attachTableEvents();
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                tableContainer.style.opacity = '1';
                tableContainer.style.pointerEvents = 'auto';
            });
        }

        // Build target URL from current filter state
        function buildFilterUrl(page = null) {
            const url = new URL(baseRoute, window.location.origin);
            
            if (searchInput && searchInput.value.trim() !== '') {
                url.searchParams.set('search', searchInput.value.trim());
            }
            if (kategoriSelect && kategoriSelect.value !== '') {
                url.searchParams.set('kategori', kategoriSelect.value);
            }
            if (statusInput && statusInput.value !== '' && statusInput.value !== 'all') {
                url.searchParams.set('status', statusInput.value);
            }
            if (page) {
                url.searchParams.set('page', page);
            }
            return url.toString();
        }

        // Handle Status Pill clicks
        statusButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Switch active pill immediately
                statusButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filterValue = this.getAttribute('data-status-filter');
                if (statusInput) {
                    statusInput.value = (filterValue === 'all') ? '' : filterValue;
                }
                
                const targetUrl = buildFilterUrl();
                window.history.pushState({}, '', targetUrl);
                fetchLaporan(targetUrl);
            });
        });

        // Handle Search Form submit
        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const targetUrl = buildFilterUrl();
                window.history.pushState({}, '', targetUrl);
                fetchLaporan(targetUrl);
            });
        }

        // Handle Category dropdown change
        if (kategoriSelect) {
            kategoriSelect.addEventListener('change', function() {
                const targetUrl = buildFilterUrl();
                window.history.pushState({}, '', targetUrl);
                fetchLaporan(targetUrl);
            });
        }

        // Handle live search with debounce
        let searchTimeout = null;
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const targetUrl = buildFilterUrl();
                    window.history.pushState({}, '', targetUrl);
                    fetchLaporan(targetUrl);
                }, 400);
            });
        }

        // Reset Filter handler
        function resetFilters(e) {
            if (e) e.preventDefault();
            if (searchInput) searchInput.value = '';
            if (kategoriSelect) kategoriSelect.value = '';
            if (statusInput) statusInput.value = '';
            
            // Set "Semua" pill as active
            statusButtons.forEach(b => {
                if (b.getAttribute('data-status-filter') === 'all') {
                    b.classList.add('active');
                } else {
                    b.classList.remove('active');
                }
            });

            window.history.pushState({}, '', baseRoute);
            fetchLaporan(baseRoute);
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', resetFilters);
        }

        // Attach events to dynamic table content (Pagination & Empty State Reset)
        function attachTableEvents() {
            // Pagination click without reload
            const paginationLinks = tableContainer.querySelectorAll('.pagination a, .page-link');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('href');
                    if (url && url !== '#') {
                        window.history.pushState({}, '', url);
                        fetchLaporan(url);
                    }
                });
            });

            // Reset button inside empty state
            const emptyResetBtn = tableContainer.querySelector('.btn-reset-filter');
            if (emptyResetBtn) {
                emptyResetBtn.addEventListener('click', resetFilters);
            }
        }

        // Initial binding for first load
        attachTableEvents();

        // Support browser Back/Forward buttons
        window.addEventListener('popstate', function() {
            const currentUrl = new URL(window.location.href);
            const statusVal = currentUrl.searchParams.get('status') || 'all';
            
            statusButtons.forEach(b => {
                if (b.getAttribute('data-status-filter') === statusVal) {
                    b.classList.add('active');
                } else {
                    b.classList.remove('active');
                }
            });

            if (statusInput) statusInput.value = (statusVal === 'all') ? '' : statusVal;
            if (searchInput) searchInput.value = currentUrl.searchParams.get('search') || '';
            if (kategoriSelect) kategoriSelect.value = currentUrl.searchParams.get('kategori') || '';

            fetchLaporan(window.location.href);
        });
    });
</script>
@endsection
