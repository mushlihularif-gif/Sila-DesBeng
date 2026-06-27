@extends('admin.layouts.admin')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold">Daftar Pengguna</h5>
                    <span class="badge bg-label-primary rounded-pill">{{ $users->total() }} Pengguna</span>
                </div>
                <div class="card-body border-bottom pt-3 pb-3">
                    <form id="filter-form" class="row g-3 align-items-center">
                        @if(in_array(auth()->user()->role, ['super_admin', 'admin']))
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" id="search" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ $search }}">
                                <button class="btn btn-primary" type="submit">Cari</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select id="filter_kecamatan_id" name="filter_kecamatan_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Semua Kecamatan --</option>
                                @foreach($kecamatanOptions as $opt)
                                    <option value="{{ $opt->id }}" {{ $filter_kecamatan_id == $opt->id ? 'selected' : '' }}>
                                        {{ $opt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select id="filter_desa_id" name="filter_desa_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Semua Desa --</option>
                                @foreach($desaOptions as $opt)
                                    <option value="{{ $opt->id }}" {{ $filter_desa_id == $opt->id ? 'selected' : '' }}>
                                        {{ $opt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @elseif(auth()->user()->role === 'admin_kecamatan')
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" id="search" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ $search }}">
                                <button class="btn btn-primary" type="submit">Cari</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select id="filter_desa_id" name="filter_desa_id" class="form-select" onchange="this.form.submit()">
                                <option value="">-- Semua Desa --</option>
                                @foreach($desaOptions as $opt)
                                    <option value="{{ $opt->id }}" {{ $filter_desa_id == $opt->id ? 'selected' : '' }}>
                                        {{ $opt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" id="search" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ $search }}">
                                <button class="btn btn-primary" type="submit">Cari</button>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
                <div id="table-container">
                    @include('admin.user_management.partials.table')
                </div>
            </div>
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
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            tableContainer.style.opacity = '1';
            tableContainer.style.pointerEvents = 'auto';
        });
    }

    if (filterForm) {
        // filterForm.addEventListener('submit', function(e) {
        //     // let it submit normally for full page reload
        // });
    }

    if (filterKecamatan) {
        filterKecamatan.addEventListener('change', function() {
            let url = new URL('{{ route("admin.manajemen-pengguna.index") }}');
            if (searchInput && searchInput.value) url.searchParams.set('search', searchInput.value);
            url.searchParams.set('filter_kecamatan_id', this.value);
            window.location.href = url.toString();
        });
    }

    if (filterDesa) {
        filterDesa.addEventListener('change', function() {
            let url = new URL('{{ route("admin.manajemen-pengguna.index") }}');
            if (searchInput && searchInput.value) url.searchParams.set('search', searchInput.value);
            if (filterKecamatan && filterKecamatan.value) url.searchParams.set('filter_kecamatan_id', filterKecamatan.value);
            url.searchParams.set('filter_desa_id', this.value);
            window.location.href = url.toString();
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
});
</script>
@endpush
@endsection