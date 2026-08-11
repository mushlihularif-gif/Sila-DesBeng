@extends('admin.layouts.admin')

@section('title', 'Tambah Staf (RBAC)')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Manajemen Staf /</span> Tambah Baru
            </h4>
            <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-10 col-md-12 mx-auto">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold">Formulir Pendaftaran Staf Baru</h5>
                </div>
                <div class="card-body mt-4">
                    <form action="{{ route('admin.staff.store') }}" method="POST">
                        @csrf
                        
                        <h6 class="fw-semibold text-primary mb-3">1. Informasi Akun</h6>
                        
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label" for="name">Nama Lengkap</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Masukkan nama lengkap" required />
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label" for="email">Alamat Email</label>
                            <div class="col-sm-9">
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="email@contoh.com" required />
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label" for="password">Kata Sandi</label>
                            <div class="col-sm-9">
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter" required />
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <label class="col-sm-3 col-form-label" for="password_confirmation">Ulangi Kata Sandi</label>
                            <div class="col-sm-9">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi kata sandi" required />
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-semibold text-primary mb-3">2. Hak Akses (RBAC)</h6>
                        <p class="text-muted small mb-3">Pilih modul atau layanan mana saja yang boleh diakses dan dikelola oleh staf ini.</p>

                        <div class="row mb-4">
                            <label class="col-sm-3 col-form-label pt-0">Izin Unit Layanan</label>
                            <div class="col-sm-9">
                                <div class="row">
                                    @foreach($availableUnits as $key => $label)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="units[]" value="{{ $key }}" id="unit_{{ $key }}" {{ (is_array(old('units')) && in_array($key, old('units'))) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="unit_{{ $key }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @error('units') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row justify-content-end">
                            <div class="col-sm-9">
                                <button type="submit" class="btn btn-primary">Simpan Staf Baru</button>
                                <a href="{{ route('admin.staff.index') }}" class="btn btn-label-secondary ms-2">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
