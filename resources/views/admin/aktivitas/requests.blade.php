@extends('admin.layouts.admin')

@section('title', 'Permintaan Pengajuan')

@section('content')
<style>
    .animate-fade-up {
        animation: fadeUp 0.5s ease-out forwards;
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .stat-card {
        border-radius: 12px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
    }
    .stat-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .stat-number {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.2;
    }
    @media (min-width: 992px) {
        .stat-icon {
            width: 48px;
            height: 48px;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .stat-number {
            font-size: 1.65rem;
        }
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
    .filter-btn-primary.active {
        background-color: #0d6efd !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25) !important;
    }
    .filter-btn-danger.active {
        background-color: #ff3e1d !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(255, 62, 29, 0.25) !important;
    }
    .filter-btn-success.active {
        background-color: #71dd37 !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(113, 221, 55, 0.25) !important;
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
    .filter-btn.active .badge { background-color: white !important; }
    .filter-btn-primary.active .badge { color: #0d6efd !important; }
    .filter-btn-danger.active .badge { color: #ff3e1d !important; }
    .filter-btn-success.active .badge { color: #71dd37 !important; }
    .filter-btn-warning.active .badge { color: #ffab00 !important; }
    .filter-btn-info.active .badge { color: #03c3ec !important; }
    .filter-btn:not(.active) { color: #697a8d !important; }
    .filter-btn:not(.active):hover { background-color: rgba(13, 110, 253, 0.08) !important; }

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

    @media (max-width: 767.98px) {
        .table-modern td {
            padding: 0.75rem 0.75rem;
        }
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    @php
        $activeTab = request('tab', 'rental');
    @endphp
    
    <!-- Banner Modern -->
    <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex align-items-center justify-content-between p-4 flex-wrap gap-3">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <div class="bg-primary p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                        <i class="bx bx-list-check fs-3"></i>
                    </div>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-primary">Permintaan & Pengajuan Layanan</h5>
                    <p class="mb-0 text-primary" style="opacity: 0.85;">
                        Kelola dan pantau seluruh aktivitas pesanan, sewa, dan pengajuan layanan dari warga.
                    </p>
                </div>
            </div>
            
            <button class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="location.reload()">
                <i class="bx bx-refresh me-1"></i> Refresh
            </button>
        </div>
    </div>

    <div id="requests-container">
        @include('admin.aktivitas.partials.requests_content')
    </div>
</div>
@endsection
