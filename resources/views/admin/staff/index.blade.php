@extends('admin.layouts.admin')

@section('title', 'Manajemen Staf (RBAC)')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Sistem /</span> Manajemen Staf</h4>
            <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Tambah Staf Baru
            </a>
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

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold">Daftar Akun Staf</h5>
            <span class="badge bg-label-primary rounded-pill">{{ $staffUsers->total() }} Staf</span>
        </div>
        <div class="card-body border-bottom pt-3 pb-3">
            <form id="filter-form" class="row g-3 align-items-center" method="GET" action="{{ route('admin.staff.index') }}">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" id="search" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ $search }}">
                        <button class="btn btn-primary" type="submit">Cari</button>
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
