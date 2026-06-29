@extends('admin.layouts.admin')

@section('title', 'Laporan Transaksi')

@section('content')
<style>
    .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
        background-color: #0095ff !important;
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(0, 149, 255, 0.3) !important;
    }
    .nav-pills .nav-link {
        color: #64748b;
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link:hover {
        background-color: #eff6ff !important;
        color: #0095ff !important;
    }
    .nav-pills .nav-link.active .badge.bg-white {
        color: #0095ff !important;
    }
</style>
<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold fs-3 mb-1 text-primary">Laporan Transaksi</h4>
            <p class="text-muted mb-0">Rekapitulasi lengkap seluruh transaksi yang tercatat di {{ auth()->user()->role === 'admin' ? 'Kabupaten Bengkalis' : (auth()->user()->region->name ?? 'Anda') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary shadow-sm rounded-pill px-4" onclick="window.print()">
                <i class="bx bx-printer me-2"></i>Cetak Laporan
            </button>
            <button class="btn btn-outline-secondary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bx bx-filter-alt me-2"></i>Filter
            </button>
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

<style>
    /* Custom Tab Styling */
    .nav-pills .nav-link {
        color: #6c757d;
        background-color: transparent;
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd;
        color: #fff;
        box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
    }
</style>
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

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
            if (bsModal) bsModal.hide();
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