@extends('admin.layouts.admin')

@section('title', 'Manajemen Staf')

@section('content')
<style>
    .staff-card {
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.04);
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05) !important;
    }
    .staff-card:hover {
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    }
    .modern-search .input-group-text {
        background: transparent;
        border-right: none;
        padding-left: 1.2rem;
    }
    .modern-search .form-control {
        border-left: none;
        padding-left: 0.5rem;
    }
    .modern-search .form-control:focus {
        box-shadow: none;
        border-color: #d9dee3;
    }
    .modern-search:focus-within {
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
        border-radius: 0.375rem;
    }
    .animate-fade-up {
        animation: fadeUp 0.5s ease-out forwards;
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <h4 class="fw-bold m-0"><span class="text-muted fw-light">Manajemen /</span> Staf Layanan</h4>
            <a href="{{ route('admin.staff.create') }}" class="btn btn-primary shadow-sm text-nowrap">
                <i class="bx bx-plus me-1"></i> Tambah Staf Baru
            </a>
        </div>
    </div>

    <!-- Panduan -->
    <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex align-items-center p-4">
            <div class="me-3">
                <div class="bg-primary p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                    <i class="bx bx-support fs-3"></i>
                </div>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-primary">Manajemen Staf Layanan</h5>
                <p class="mb-0 text-primary" style="opacity: 0.85;">
                    Halaman ini digunakan untuk mengelola akun petugas atau staf operasional (misal: Petugas Penjualan Gas, Petugas Pasar, dsb). 
                    Staf yang ditambahkan di sini akan diberikan hak akses terbatas hanya untuk mengelola unit layanan yang ditugaskan kepadanya.
                </p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center" role="alert">
            <i class="bx bx-check-circle fs-4 me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center" role="alert">
            <i class="bx bx-error-circle fs-4 me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card staff-card mt-2">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-3 px-sm-4 flex-wrap gap-2">
            <h5 class="mb-0 fw-bold d-flex align-items-center text-primary">
                <i class="bx bx-group fs-4 me-2"></i> Daftar Akun Staf
            </h5>
            <span class="badge bg-label-primary rounded-pill px-3 py-2 fw-semibold">
                {{ $staffUsers->total() }} Total Staf
            </span>
        </div>
        <div class="card-body border-bottom pt-3 pb-3 px-3 px-sm-4 bg-light bg-opacity-25">
            <form id="filter-form" class="row g-3 align-items-center" method="GET" action="{{ route('admin.staff.index') }}">
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="input-group modern-search shadow-sm bg-white rounded">
                        <span class="input-group-text text-muted"><i class="bx bx-search"></i></span>
                        <input type="text" id="search" name="search" class="form-control" placeholder="Cari nama atau email staf..." value="{{ $search }}">
                        <button class="btn btn-primary px-3 px-sm-4" type="submit">Cari</button>
                    </div>
                </div>
            </form>
        </div>
        <div id="table-container">
            @include('admin.staff.partials.table')
        </div>
    </div>
</div>
@endsection
