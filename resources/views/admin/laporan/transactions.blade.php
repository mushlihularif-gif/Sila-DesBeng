@extends('admin.layouts.admin')

@section('title', 'Laporan Transaksi')

@section('content')
<style>
    .animate-fade-up {
        animation: fadeUp 0.5s ease-out forwards;
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .tabs-scroll-wrapper {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding-bottom: 4px;
    }
    .tabs-scroll-wrapper::-webkit-scrollbar {
        display: none;
    }
    .nav-pills .nav-link {
        color: #64748b;
        font-size: 0.85rem;
        transition: all 0.25s ease;
        border: 1px solid rgba(100, 116, 139, 0.15);
        background-color: #fff;
    }
    @media (min-width: 768px) {
        .nav-pills .nav-link {
            font-size: 0.9rem;
        }
    }
    .nav-pills .nav-link:hover {
        background-color: #eff6ff !important;
        color: #0095ff !important;
        border-color: rgba(0, 149, 255, 0.3);
    }
    .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
        background-color: #0095ff !important;
        color: #fff !important;
        border-color: #0095ff !important;
        box-shadow: 0 4px 10px rgba(0, 149, 255, 0.25) !important;
    }
    .nav-pills .nav-link.active .badge.bg-white {
        color: #0095ff !important;
    }
    .stat-card {
        border-radius: 14px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
    }
    .stat-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .stat-number {
        font-size: 1.2rem;
        font-weight: 700;
        line-height: 1.2;
    }
    @media (min-width: 992px) {
        .stat-icon {
            width: 42px;
            height: 42px;
            font-size: 1.35rem;
        }
        .stat-number {
            font-size: 1.45rem;
        }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-0">
                <span class="text-muted fw-light">Data & Laporan /</span> Laporan Transaksi
            </h4>
        </div>
        <div class="d-flex gap-2 w-100 w-sm-auto justify-content-start justify-content-sm-end">
            <button class="btn btn-white border shadow-sm rounded-pill px-3 px-sm-4 flex-grow-1 flex-sm-grow-0" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bx bx-filter-alt me-2"></i>Filter
            </button>
            <button class="btn btn-primary shadow-sm rounded-pill px-3 px-sm-4 flex-grow-1 flex-sm-grow-0" onclick="window.print()">
                <i class="bx bx-printer me-2"></i>Cetak
            </button>
        </div>
    </div>

    <!-- Panduan Banner -->
    <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex align-items-center p-3 p-md-4">
            <div class="me-3">
                <div class="bg-primary p-2 p-md-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px;">
                    <i class="bx bx-line-chart fs-4 fs-md-3"></i>
                </div>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-primary fs-6 fs-md-5">Laporan Transaksi</h5>
                <p class="mb-0 text-primary small" style="opacity: 0.85;">
                    Rekapitulasi lengkap seluruh transaksi yang tercatat di {{ auth()->user()->role === 'admin' ? 'Kabupaten Bengkalis' : (auth()->user()->region->name ?? 'Anda') }}.
                </p>
            </div>
        </div>
    </div>

    <div id="transactions-container">
        @include('admin.laporan.partials.transactions_content')
    </div>
</div>

@push('modals')
<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Filter Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="filter-form">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Status</label>
                        <select name="status" class="form-select border-0 bg-light py-2">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control border-0 bg-light py-2" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control border-0 bg-light py-2" value="{{ request('end_date') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" id="reset-filter">Reset</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const resetBtn = document.getElementById('reset-filter');
    const container = document.getElementById('transactions-container');
    const modalElement = document.getElementById('filterModal');
    let bsModal = null;
    if(typeof bootstrap !== 'undefined' && modalElement) {
        bsModal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    }

    function fetchFilteredData(url) {
        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';

        // Remember currently active tab
        const activeTab = document.querySelector('#reportTabs .nav-link.active');
        const activeTabId = activeTab ? activeTab.getAttribute('id') : null;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
            if (bsModal) bsModal.hide();

            // Restore active tab
            if (activeTabId) {
                const targetTabBtn = container.querySelector('#' + activeTabId);
                if (targetTabBtn) {
                    const tabTrigger = new bootstrap.Tab(targetTabBtn);
                    tabTrigger.show();
                }
            }

            // Animate count-up if available
            if (typeof window.animateCountUp === 'function') {
                window.animateCountUp('.count-up', false);
            }
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            const url = '{{ route("admin.laporan.transaksi") }}?' + params.toString();
            fetchFilteredData(url);
        });
    }
    
    if (resetBtn) {
        resetBtn.addEventListener('click', function(e) {
            e.preventDefault();
            filterForm.reset();
            fetchFilteredData('{{ route("admin.laporan.transaksi") }}');
        });
    }
});
</script>
@endpush