@extends('admin.layouts.admin')

@section('title', isset($isArchive) && $isArchive ? 'Bukti Pelaporan Warga' : 'Pelaporan Warga')

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
    <div class="row g-3 mb-4 row-cols-2 row-cols-md-3 row-cols-lg-6">
        <!-- Total Laporan -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-secondary-subtle text-secondary rounded-circle p-2 mb-2">
                        <i class="bx bx-folder fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Total Laporan</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['total']) }}</h3>
                </div>
            </div>
        </div>
        <!-- Pending -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-warning-subtle text-warning rounded-circle p-2 mb-2">
                        <i class="bx bx-time fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Pending</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['pending']) }}</h3>
                </div>
            </div>
        </div>
        <!-- Proses -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-info-subtle text-info rounded-circle p-2 mb-2">
                        <i class="bx bx-cog fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Sedang Proses</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['proses']) }}</h3>
                </div>
            </div>
        </div>
        <!-- Dilanjutkan -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-primary-subtle text-primary rounded-circle p-2 mb-2">
                        <i class="bx bx-right-arrow-alt fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Eskalasi</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['dilanjutkan']) }}</h3>
                </div>
            </div>
        </div>
        <!-- Selesai -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-success-subtle text-success rounded-circle p-2 mb-2">
                        <i class="bx bx-check-circle fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Selesai</small>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['selesai']) }}</h3>
                </div>
            </div>
        </div>
        <!-- SLA Terlewat -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-danger-subtle text-danger rounded-circle p-2 mb-2">
                        <i class="bx bx-error fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">SLA Terlewat</small>
                    <h3 class="fw-bold mb-0 text-danger">{{ number_format($stats['overdue']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Buttons (Pills) -->
    <ul class="nav nav-pills mb-4 d-flex flex-nowrap overflow-auto gap-2 pb-2" style="scrollbar-width: thin;">
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
        <div class="card-header bg-white border-bottom-0 p-4">
            <form action="{{ route('admin.pelaporan.index') }}" method="GET" class="row g-3">
                @if(isset($isArchive) && $isArchive)
                    <input type="hidden" name="status" value="Selesai">
                @else
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                @endif

                <div class="col-md-5">
                    <label class="form-label text-muted small fw-bold">Pencarian</label>
                    <div class="input-group input-group-merge">
                        <span class="input-group-text border-end-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama atau deskripsi keluhan..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Filter Kategori</label>
                    <select name="kategori" class="form-select text-capitalize">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat }}" {{ request('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3 d-flex align-items-end">
                    <div class="d-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search-alt me-2"></i>Terapkan</button>
                        <a href="{{ isset($isArchive) && $isArchive ? route('admin.pelaporan.archive') : route('admin.pelaporan.index') }}" class="btn btn-label-secondary px-3" title="Reset Pencarian"><i class="bx bx-reset"></i></a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive px-4 pb-4">
                <table class="table table-modern table-hover w-100">
                    <thead class="bg-transparent text-uppercase small text-muted">
                        <tr>
                            <th class="border-bottom pb-3">Pelapor</th>
                            <th class="border-bottom pb-3">Kategori</th>
                            <th class="border-bottom pb-3">Lokasi</th>
                            <th class="border-bottom pb-3">Tingkat Penanganan</th>
                            <th class="border-bottom pb-3">Status</th>
                            <th class="border-bottom pb-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporans as $laporan)
                        <tr data-status="{{ $laporan->status }}" class="pelaporan-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($laporan->user->name ?? 'A', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 text-dark fw-semibold">{{ $laporan->nama }}</h6>
                                        <small class="text-muted">{{ $laporan->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-dark">{{ $laporan->kategori }}</span>
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $laporan->lokasi }}">
                                    <i class="bx bx-map text-danger me-1"></i>{{ $laporan->lokasi }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-secondary text-capitalize">
                                    <i class="bx bx-building-house me-1"></i>{{ $laporan->escalation_level }}
                                </span>
                            </td>
                            <td>
                                @if($laporan->status === 'Pending')
                                    <span class="badge bg-label-warning">Pending</span>
                                @elseif($laporan->status === 'Proses')
                                    <span class="badge bg-label-info">Proses</span>
                                @elseif($laporan->status === 'Dilanjutkan')
                                    <span class="badge bg-label-primary">Dilanjutkan</span>
                                @elseif($laporan->status === 'Selesai')
                                    <span class="badge bg-label-success">Selesai</span>
                                @elseif($laporan->status === 'Ditolak')
                                    <span class="badge bg-label-danger">Ditolak</span>
                                @endif
                                
                                @if($laporan->isOverdue())
                                    <i class="bx bx-error text-danger ms-1" title="SLA Terlewat"></i>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.pelaporan.show', $laporan->id) }}" class="btn btn-sm btn-label-primary rounded-pill px-3">
                                    <i class="bx bx-show me-1"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="avatar avatar-xl bg-secondary-subtle text-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <i class="bx bx-folder-open" style="font-size: 2.5rem;"></i>
                                </div>
                                <h6 class="text-muted mb-0">Tidak ada data laporan ditemukan.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-4 py-3 border-top">
                {{ $laporans->links() }}
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Client-side status filtering logic
        const statusFilters = document.querySelectorAll('.filter-status-btn');
        statusFilters.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Update active state visually
                statusFilters.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filterValue = this.getAttribute('data-status-filter');
                
                // Update URL parameter without reloading
                let url = new URL(window.location.href);
                if (filterValue === 'all') {
                    url.searchParams.delete('status');
                } else {
                    url.searchParams.set('status', filterValue);
                }
                window.history.replaceState({}, '', url);

                // Update hidden input in form if it exists
                const hiddenInput = document.querySelector('input[name="status"]');
                if (hiddenInput && filterValue !== 'all') {
                    hiddenInput.value = filterValue;
                } else if (hiddenInput && filterValue === 'all') {
                    hiddenInput.value = '';
                }

                // Filter rows in table
                document.querySelectorAll('.pelaporan-row').forEach(row => {
                    if (filterValue === 'all') {
                        row.style.display = '';
                        return;
                    }
                    
                    const rowStatus = row.getAttribute('data-status');
                    row.style.display = (rowStatus === filterValue) ? '' : 'none';
                });
            });
        });
    });
</script>
@endsection
