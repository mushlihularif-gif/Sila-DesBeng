@extends('admin.layouts.admin')

@section('title', 'Tambah Staf')

@section('content')
<style>
    .input-icon-wrapper {
        position: relative;
    }
    .input-icon-wrapper .bx {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #a1acb8;
        font-size: 1.25rem;
    }
    .input-icon-wrapper .form-control {
        padding-left: 2.8rem;
    }
    /* Selectable Cards for RBAC */
    .unit-card-checkbox {
        display: none;
    }
    .unit-card-label {
        display: block;
        padding: 1rem;
        border: 2px solid #eceef1;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        background: #fff;
    }
    .unit-card-label:hover {
        border-color: #d9dee3;
        background: #f8f9fa;
    }
    .unit-card-checkbox:checked + .unit-card-label {
        border-color: #3b82f6; /* Blue border like in screenshot */
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }
    .unit-card-checkbox:checked + .unit-card-label .bx {
        color: #3b82f6;
    }
    .unit-card-checkbox:checked + .unit-card-label .unit-title {
        color: #1e293b !important;
        font-weight: 700;
    }
    .form-section-title {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #566a7f;
        font-weight: 700;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #f0f2f4;
        padding-bottom: 0.5rem;
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
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Manajemen Staf /</span> Tambah Baru
            </h4>
            <a href="{{ route('admin.staff.index') }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white border-bottom py-4 px-4 px-md-5">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bx bx-user-plus fs-4 me-2"></i> Formulir Pendaftaran Staf Baru</h5>
                </div>
                <div class="card-body mt-4 px-4 px-md-5 pb-5">
                    <form action="{{ route('admin.staff.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-section-title"><i class="bx bx-id-card me-2"></i> 1. Informasi Akun</div>
                        
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-semibold" for="name">Nama Lengkap</label>
                            <div class="col-sm-9">
                                <div class="input-icon-wrapper">
                                    <i class="bx bx-user"></i>
                                    <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required />
                                </div>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-semibold" for="email">Alamat Email</label>
                            <div class="col-sm-9">
                                <div class="input-icon-wrapper">
                                    <i class="bx bx-envelope"></i>
                                    <input type="email" id="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="budi@contoh.com" required />
                                </div>
                                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label fw-semibold" for="password">Kata Sandi</label>
                            <div class="col-sm-9">
                                <div class="input-icon-wrapper">
                                    <i class="bx bx-lock-alt"></i>
                                    <input type="password" id="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter" required />
                                </div>
                                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-5">
                            <label class="col-sm-3 col-form-label fw-semibold" for="password_confirmation">Ulangi Kata Sandi</label>
                            <div class="col-sm-9">
                                <div class="input-icon-wrapper">
                                    <i class="bx bx-check-shield"></i>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control form-control-lg" placeholder="Konfirmasi kata sandi" required />
                                </div>
                            </div>
                        </div>

                        <div class="form-section-title d-flex justify-content-between align-items-center">
                            <span><i class="bx bx-shield-quarter me-2"></i> 2. Hak Akses {{ auth()->user()->role === 'super_admin' ? 'Modul Sistem Platform' : 'Unit Layanan' }}</span>
                            <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm" id="selected-access-count" style="font-size: 0.8rem; text-transform: none; letter-spacing: normal;">0 Hak Akses Dipilih</span>
                        </div>
                        <div class="alert alert-warning d-flex mb-4 p-3 rounded" style="border-left: 4px solid #ffab00;" role="alert">
                            <i class="bx bx-error-circle fs-4 me-3 mt-1 text-warning"></i>
                            <div>
                                <h6 class="alert-heading fw-bold mb-1" style="color: #664d03;">PENTING: Wajib Dibaca!</h6>
                                <p class="mb-0" style="color: #664d03;">Silakan pilih <strong>satu atau lebih</strong> sub-menu yang boleh dibuka staf ini. Pilihan dikelompokkan sesuai tab di sidebar, dan staf hanya akan melihat menu yang dicentang.</p>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-12">
                                @foreach($grupIzin as $namaGrup => $isiGrup)
                                    @if($namaGrup !== '')
                                        <div class="d-flex align-items-center gap-2 mb-2 {{ $loop->first ? '' : 'mt-4' }}">
                                            <span class="badge bg-label-primary rounded-pill px-3">{{ $namaGrup }}</span>
                                            <span class="text-muted" style="font-size:.75rem">
                                                {{ count($isiGrup) }} sub-menu
                                            </span>
                                            <hr class="flex-grow-1 my-0" style="opacity:.15">
                                        </div>
                                    @endif
                                <div class="row g-3">
                                    @foreach($isiGrup as $key => $info)
                                    @php
                                        // Daftar unit layanan memakai gambar; modul platform memakai ikon boxicons
                                        // yang sudah ditentukan di User::IZIN_PLATFORM_GRUP.
                                        $label = is_array($info) ? $info['label'] : $info;
                                        $iconClass = is_array($info) ? 'bx ' . $info['ikon'] : '';
                                        $iconImg = '';
                                        if($key == 'sewa_alat') $iconImg = asset('User/img/elemen/F1.png');
                                        elseif($key == 'gas') $iconImg = asset('User/img/elemen/F2.png');
                                        elseif($key == 'sewa_mobil') $iconImg = asset('User/img/elemen/mobil.png');
                                        elseif($key == 'fasilitas_umum') $iconImg = asset('User/img/elemen/fasilitas.png');
                                        elseif($key == 'pelaporan_warga') $iconImg = asset('User/img/elemen/lapor.png');
                                        elseif($key == 'kabar_informasi') $iconImg = asset('User/img/elemen/KabardanInformasiDaerah.png');
                                        elseif($key == 'pasar_daerah') $iconImg = asset('Admin/img/pasardaerah/PasarDaerah2.png');
                                    @endphp
                                    <div class="col-md-6 col-lg-4">
                                        <input class="unit-card-checkbox" type="checkbox" name="units[]" value="{{ $key }}" id="unit_{{ $key }}" {{ (is_array(old('units')) && in_array($key, old('units'))) ? 'checked' : '' }}>
                                        <label class="unit-card-label text-center h-100 d-flex flex-column justify-content-center align-items-center p-4" for="unit_{{ $key }}">
                                            @if($iconImg)
                                                <img src="{{ $iconImg }}" alt="{{ $label }}" class="mb-3" style="width: 64px; height: 64px; object-fit: contain;">
                                            @elseif($iconClass)
                                                <i class="{{ $iconClass }} mb-3" style="font-size: 3.5rem; color: #16a34a;"></i>
                                            @else
                                                <i class="bx bx-buildings mb-3 text-muted" style="font-size: 3.5rem;"></i>
                                            @endif
                                            <span class="unit-title d-block fw-bold text-dark">{{ $label }}</span>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                @endforeach
                                @error('units') <div class="text-danger small mt-2 fw-semibold"><i class="bx bx-error-circle me-1"></i> {{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="pt-4 border-top">
                            <div class="d-flex justify-content-end gap-3">
                                <a href="{{ route('admin.staff.index') }}" class="btn btn-label-secondary btn-lg px-4">Batal</a>
                                <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm"><i class="bx bx-save me-2"></i> Simpan Staf Baru</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.unit-card-checkbox');
        const countBadge = document.getElementById('selected-access-count');
        
        function updateCount() {
            let count = 0;
            checkboxes.forEach(cb => {
                if(cb.checked) count++;
            });
            
            if(count === 0) {
                countBadge.textContent = 'Belum Ada Hak Akses Dipilih';
                countBadge.className = 'badge bg-label-secondary rounded-pill px-3 py-2';
            } else {
                countBadge.textContent = count + ' Hak Akses Dipilih';
                countBadge.className = 'badge bg-primary rounded-pill px-3 py-2 shadow-sm';
            }
        }
        
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateCount);
        });
        
        // Initial run
        updateCount();
    });
</script>
@endpush
@endsection
