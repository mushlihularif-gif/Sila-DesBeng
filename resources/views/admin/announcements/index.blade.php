@extends('admin.layouts.admin')

@section('title', 'Pengumuman & Event')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem /</span> Pengumuman & Event</h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0">Daftar Pengumuman & Event</h5>
            <a href="{{ route('admin.announcements.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Buat Baru
            </a>
        </div>
        <div class="card-body border-bottom pt-3 pb-3">
            <form id="filter-form" class="row g-3 align-items-center" method="GET">
                <div class="col-md-3">
                    <select id="filter_type" name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Tipe</option>
                        <option value="Pengumuman" {{ request('type') == 'Pengumuman' ? 'selected' : '' }}>Pengumuman</option>
                        <option value="Event" {{ request('type') == 'Event' ? 'selected' : '' }}>Event</option>
                        <option value="Gotong Royong" {{ request('type') == 'Gotong Royong' ? 'selected' : '' }}>Gotong Royong</option>
                    </select>
                </div>
                
                @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                <div class="col-md-4">
                    <select id="filter_kecamatan_id" name="filter_kecamatan_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kecamatan --</option>
                        @foreach($kecamatanOptions as $opt)
                            <option value="{{ $opt->id }}" {{ request('filter_kecamatan_id') == $opt->id ? 'selected' : '' }}>
                                {{ $opt->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="filter_desa_id" name="filter_desa_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Desa --</option>
                        @foreach($desaOptions as $opt)
                            <option value="{{ $opt->id }}" {{ request('filter_desa_id') == $opt->id ? 'selected' : '' }}>
                                {{ $opt->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @elseif(auth()->user()->role === 'admin_kecamatan')
                <div class="col-md-4">
                    <select id="filter_desa_id" name="filter_desa_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Desa --</option>
                        @foreach($desaOptions as $opt)
                            <option value="{{ $opt->id }}" {{ request('filter_desa_id') == $opt->id ? 'selected' : '' }}>
                                {{ $opt->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </form>
        </div>

        <div id="table-container">
            @include('admin.announcements.partials.table')
        </div>
    </div>
</div>


@endsection
