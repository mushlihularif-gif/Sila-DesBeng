@extends('admin.layouts.admin')

@section('title', 'Tambah Transportasi Sewa')

@include('admin.unit.partials.unit_product_styles')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">
                    <a href="{{ route('admin.unit.mobil.index') }}" class="text-muted text-decoration-none">Penyewaan Transportasi</a> /
                </span>
                Tambah Kendaraan
            </h4>
            <p class="text-muted mb-0 small">Tambahkan armada transportasi beserta skema tarif harian, borongan, supir, dan BBM.</p>
        </div>
        <a href="{{ route('admin.unit.mobil.index') }}"
           class="segi-back btn btn-outline-secondary rounded-pill px-4 w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-1">
            <i class="bx bx-arrow-back"></i>
            <span>Kembali</span>
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bx bx-error-circle fs-4 mt-0.5 flex-shrink-0"></i>
                <div>
                    <strong class="d-block mb-1">Periksa kembali data yang dimasukkan:</strong>
                    <ul class="mb-0 ps-3 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.unit.mobil.store') }}" method="POST" enctype="multipart/form-data" class="up-form">
        @csrf
        @include('admin.unit.mobil.partials.form_fields', ['mobil' => null])
    </form>
</div>
@endsection

@section('modals')
    @include('admin.unit.partials.category_satuan_modals', [
        'categoryType' => 'mobil',
        'categoryPlaceholder' => 'Contoh: Minibus / Mobil Pickup',
        'includeSatuanModal' => false,
    ])
@endsection
