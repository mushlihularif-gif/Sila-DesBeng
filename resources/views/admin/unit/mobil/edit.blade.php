@extends('admin.layouts.admin')

@section('title', 'Ubah Transportasi - ' . $mobil->nama_mobil)

@include('admin.unit.partials.unit_product_styles')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div class="min-w-0">
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">
                    <a href="{{ route('admin.unit.mobil.index') }}" class="text-muted text-decoration-none">Penyewaan Transportasi</a> /
                </span>
                Ubah Kendaraan
            </h4>
            <p class="text-muted mb-0 small">Perbarui data armada, tarif harian &amp; borongan, atau lokasi untuk <strong>{{ $mobil->nama_mobil }}</strong>.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-start justify-content-md-end flex-shrink-0">
            <a href="{{ route('admin.unit.mobil.show', $mobil->id) }}"
               class="btn btn-outline-info rounded-pill px-3 flex-grow-1 flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-show"></i>
                <span>Lihat Detail</span>
            </a>
            <a href="{{ route('admin.unit.mobil.index') }}"
               class="segi-back btn btn-outline-secondary rounded-pill px-3 flex-grow-1 flex-sm-grow-0 d-inline-flex align-items-center justify-content-center gap-1">
                <i class="bx bx-arrow-back"></i>
                <span>Kembali</span>
            </a>
        </div>
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

    <form action="{{ route('admin.unit.mobil.update', $mobil->id) }}" method="POST" enctype="multipart/form-data" class="up-form">
        @csrf
        @method('PUT')
        @include('admin.unit.mobil.partials.form_fields', ['mobil' => $mobil])
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
