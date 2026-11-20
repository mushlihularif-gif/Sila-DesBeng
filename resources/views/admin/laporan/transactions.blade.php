@extends('admin.layouts.admin')

@section('title', 'Laporan Transaksi')

@section('content')
<style>
    .trx-stat-card {
        border-radius: 14px;
        border: 1px solid #eef1f6;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        background: #fff;
    }
    .trx-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(67, 89, 113, 0.08) !important;
    }
    .trx-stat-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .tabs-scroll-wrapper {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
        padding-bottom: 2px;
    }
    .tabs-scroll-wrapper::-webkit-scrollbar {
        display: none;
    }
    #reportTabs .nav-link {
        color: #566a7f;
        font-size: 0.85rem;
        border: 1px solid #e4e7ec;
        background-color: #fff;
        transition: all 0.2s ease;
    }
    #reportTabs .nav-link:hover {
        background-color: #f4f6ff;
        color: #696cff;
        border-color: rgba(105, 108, 255, 0.35);
    }
    #reportTabs .nav-link.active {
        background-color: #696cff !important;
        color: #fff !important;
        border-color: #696cff !important;
        box-shadow: 0 4px 12px rgba(105, 108, 255, 0.28) !important;
    }
    #reportTabs .nav-link.active .trx-tab-count {
        background-color: rgba(255, 255, 255, 0.22) !important;
        color: #fff !important;
    }
    .trx-mobile-card {
        border: 1px solid #eef1f6;
        border-radius: 14px;
        background: #fff;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }
    .trx-preset-btn {
        font-size: 0.76rem;
        padding: 0.32rem 0.75rem;
        border-radius: 50rem;
    }
    @media (max-width: 575.98px) {
        .trx-stat-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            font-size: 1.1rem;
        }
    }
</style>

@php
    $activeFilterCount = 0;
    if (request('status') && request('status') !== 'all') $activeFilterCount++;
    if (request('start_date')) $activeFilterCount++;
    if (request('end_date')) $activeFilterCount++;
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Laporan /</span> Laporan Transaksi
            </h4>
            <p class="text-muted small mb-0">
                Rekapitulasi lengkap seluruh pesanan &amp; transaksi di
                <strong>{{ auth()->user()->role === 'super_admin' ? 'Kabupaten Bengkalis' : (auth()->user()->region->name ?? 'Desa Anda') }}</strong>.
            </p>
        </div>
        <div class="d-flex gap-2 w-100 w-sm-auto justify-content-start justify-content-sm-end no-print">
            <button type="button" class="btn btn-white border shadow-sm rounded-pill px-3 px-sm-4 flex-grow-1 flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bx bx-filter-alt"></i>
                <span>Filter</span>
                <span id="active-filter-badge" class="badge bg-primary rounded-pill ms-1 {{ $activeFilterCount > 0 ? '' : 'd-none' }}">{{ $activeFilterCount }}</span>
            </button>
            <button type="button" class="btn btn-primary shadow-sm rounded-pill px-3 px-sm-4 flex-grow-1 flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1" onclick="window.print()">
                <i class="bx bx-printer"></i>
                <span>Cetak</span>
            </button>
        </div>
    </div>

    <!-- Active Filter Chips (tampil hanya saat ada filter aktif) -->
    <div id="active-filter-bar" class="mb-3 no-print {{ $activeFilterCount > 0 ? '' : 'd-none' }}">
        <div class="d-flex flex-wrap align-items-center gap-2 bg-white border rounded-3 px-3 py-2 shadow-xs">
            <span class="small fw-semibold text-muted"><i class="bx bx-filter me-1"></i>Filter aktif:</span>
            <div id="active-filter-chips" class="d-flex flex-wrap gap-2"></div>
            <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none p-0 ms-auto fw-semibold" id="quick-reset-filter">
                <i class="bx bx-x me-1"></i>Hapus Semua Filter
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
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom bg-white py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="avatar avatar-sm bg-label-primary rounded-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-filter-alt fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 fs-6">Filter Laporan Transaksi</h5>
                        <small class="text-muted">Saring berdasarkan status dan rentang tanggal</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="filter-form">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Status Pesanan</label>
                        <select name="status" id="filter-status" class="form-select py-2">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small d-block">Pilih Cepat Periode</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary trx-preset-btn" onclick="setDatePreset('today')">Hari Ini</button>
                            <button type="button" class="btn btn-outline-primary trx-preset-btn" onclick="setDatePreset('7days')">7 Hari Terakhir</button>
                            <button type="button" class="btn btn-outline-primary trx-preset-btn" onclick="setDatePreset('month')">Bulan Ini</button>
                            <button type="button" class="btn btn-outline-primary trx-preset-btn" onclick="setDatePreset('year')">Tahun Ini</button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold text-dark small">Dari Tanggal</label>
                            <input type="date" name="start_date" id="filter-start-date" class="form-control py-2" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold text-dark small">Sampai Tanggal</label>
                            <input type="date" name="end_date" id="filter-end-date" class="form-control py-2" value="{{ request('end_date') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light py-3 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3" id="reset-filter">
                        <i class="bx bx-refresh me-1"></i>Reset
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bx bx-check me-1"></i>Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@endsection

@push('scripts')
<script>
window.setDatePreset = function(preset) {
    const startInput = document.getElementById('filter-start-date');
    const endInput = document.getElementById('filter-end-date');
    if (!startInput || !endInput) return;

    const today = new Date();
    const fmt = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    let start = new Date(today);

    if (preset === '7days') {
        start.setDate(today.getDate() - 6);
    } else if (preset === 'month') {
        start = new Date(today.getFullYear(), today.getMonth(), 1);
    } else if (preset === 'year') {
        start = new Date(today.getFullYear(), 0, 1);
    }

    startInput.value = fmt(start);
    endInput.value = fmt(today);
};

window.bindTransactionSearch = function() {
    const searchInput = document.getElementById('trx-quick-search');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const keyword = this.value.trim().toLowerCase();
        document.querySelectorAll('.trx-searchable-item').forEach(function(item) {
            const text = (item.getAttribute('data-search') || item.textContent || '').toLowerCase();
            item.style.display = keyword === '' || text.includes(keyword) ? '' : 'none';
        });
    });
};

document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const resetBtn = document.getElementById('reset-filter');
    const quickResetBtn = document.getElementById('quick-reset-filter');
    const container = document.getElementById('transactions-container');
    const modalElement = document.getElementById('filterModal');
    const filterBar = document.getElementById('active-filter-bar');
    const filterChips = document.getElementById('active-filter-chips');
    const filterBadge = document.getElementById('active-filter-badge');

    const statusLabels = {
        pending: 'Menunggu',
        approved: 'Disetujui',
        rejected: 'Ditolak',
        completed: 'Selesai',
        cancelled: 'Dibatalkan'
    };

    let bsModal = null;
    if (typeof bootstrap !== 'undefined' && modalElement) {
        bsModal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    }

    function updateFilterUI() {
        if (!filterForm || !filterBar || !filterChips || !filterBadge) return;
        const status = document.getElementById('filter-status')?.value || 'all';
        const start = document.getElementById('filter-start-date')?.value || '';
        const end = document.getElementById('filter-end-date')?.value || '';

        const chips = [];
        if (status && status !== 'all') {
            chips.push('<span class="badge bg-label-primary rounded-pill px-3 py-1">Status: ' + (statusLabels[status] || status) + '</span>');
        }
        if (start) {
            chips.push('<span class="badge bg-label-info rounded-pill px-3 py-1">Dari: ' + start + '</span>');
        }
        if (end) {
            chips.push('<span class="badge bg-label-info rounded-pill px-3 py-1">Sampai: ' + end + '</span>');
        }

        filterChips.innerHTML = chips.join('');
        if (chips.length > 0) {
            filterBar.classList.remove('d-none');
            filterBadge.textContent = chips.length;
            filterBadge.classList.remove('d-none');
        } else {
            filterBar.classList.add('d-none');
            filterBadge.classList.add('d-none');
        }
    }

    function fetchFilteredData(url) {
        container.style.opacity = '0.5';
        container.style.pointerEvents = 'none';

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

            if (activeTabId) {
                const targetTabBtn = container.querySelector('#' + activeTabId);
                if (targetTabBtn) {
                    new bootstrap.Tab(targetTabBtn).show();
                }
            }

            updateFilterUI();
            window.bindTransactionSearch();

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
            const params = new URLSearchParams(new FormData(filterForm));
            fetchFilteredData('{{ route("admin.laporan.transaksi") }}?' + params.toString());
        });
    }

    function handleReset(e) {
        e.preventDefault();
        if (filterForm) {
            filterForm.reset();
            const statusEl = document.getElementById('filter-status');
            const startEl = document.getElementById('filter-start-date');
            const endEl = document.getElementById('filter-end-date');
            if (statusEl) statusEl.value = 'all';
            if (startEl) startEl.value = '';
            if (endEl) endEl.value = '';
        }
        fetchFilteredData('{{ route("admin.laporan.transaksi") }}');
    }

    if (resetBtn) resetBtn.addEventListener('click', handleReset);
    if (quickResetBtn) quickResetBtn.addEventListener('click', handleReset);

    updateFilterUI();
    window.bindTransactionSearch();
});
</script>
@endpush
