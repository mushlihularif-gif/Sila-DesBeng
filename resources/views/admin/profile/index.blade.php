@extends('admin.layouts.admin')

@section('title', 'Profil Saya')

@section('content')
@php
    $roleLabels = [
        'super_admin' => 'Super Admin',
        'admin'       => 'Admin Pusat',
        'admin_desa'  => 'Admin Desa',
        'admin_rw'    => 'Admin RW',
        'admin_rt'    => 'Admin RT',
        'staff'       => 'Staf Unit',
        'user'        => 'Pengguna',
    ];
    $roleLabel = $roleLabels[$user->role ?? ''] ?? ucfirst($user->role ?? 'Admin');

    $desaStr = '';
    $kecStr = '';
    $kabStr = '';
    if ($user && $user->region) {
        $curr = $user->region;
        while ($curr) {
            $type = strtolower((string) $curr->type);
            if (in_array($type, ['desa', 'kelurahan'], true)) {
                $desaStr = $curr->name;
            } elseif ($type === 'kecamatan') {
                $kecStr = $curr->name;
            } elseif (in_array($type, ['kabupaten', 'kota'], true)) {
                $kabStr = $curr->name;
            }
            $curr = $curr->parent;
        }
    }
    $wilayahRingkas = collect([$desaStr ? 'Desa ' . $desaStr : null, $kecStr ? 'Kec. ' . $kecStr : null])
        ->filter()
        ->implode(' • ');
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <span class="text-muted fw-normal">Pengaturan Akun /</span> Profil Saya
            </h4>
            <p class="text-muted mb-0 small">
                Kelola informasi identitas akun, foto profil, dan pengaturan keamanan kata sandi Anda.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-label-primary rounded-pill px-3 py-2 fw-semibold">
                <i class="bx bx-shield-quarter me-1"></i>{{ $roleLabel }}
            </span>
        </div>
    </div>

    {{-- Flash & Validation Alerts --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bx bx-check-circle fs-4 flex-shrink-0"></i>
            <div class="flex-grow-1">
                <strong>Berhasil!</strong> {{ session('success') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bx bx-error-circle fs-4 flex-shrink-0"></i>
            <div class="flex-grow-1">
                <strong>Gagal!</strong> {{ session('error') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="bx bx-error-circle fs-4 mt-0.5 flex-shrink-0"></i>
                <div class="flex-grow-1">
                    <strong class="d-block mb-1">Periksa kembali data yang Anda masukkan:</strong>
                    <ul class="mb-0 ps-3 small">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <form id="formAccountSettings" method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" id="deleteAvatarInput" name="delete_avatar" value="0">

        <div class="row g-4 align-items-start">
            {{-- Left Column: Profile Summary & Account Security --}}
            <div class="col-12 col-lg-4">
                {{-- Profile Summary Card --}}
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="profile-cover-strip"></div>
                    <div class="card-body text-center pt-0 px-4 pb-4">
                        <div class="d-flex justify-content-center mb-3" style="margin-top: -54px;">
                            <div class="avatar-wrapper position-relative avatar-trigger" title="Klik untuk mengganti foto profil">
                                @if($user && $user->file)
                                    <img src="{{ route('media.avatar', ['filename' => basename($user->file->path)]) }}"
                                        alt="Foto Profil" class="avatar-preview rounded-circle" id="uploadedAvatar" />
                                @else
                                    <div class="avatar-preview avatar-default rounded-circle d-flex align-items-center justify-content-center" id="uploadedAvatar">
                                        <svg viewBox="0 0 24 24" fill="currentColor" style="width: 52px; height: 52px; color: #ffffff;">
                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                        </svg>
                                    </div>
                                @endif
                                <span class="avatar-cam-badge shadow-sm">
                                    <i class="bx bx-camera"></i>
                                </span>
                            </div>
                        </div>

                        <h5 class="fw-bold text-dark mb-1 text-break">{{ $user->name ?? 'Administrator' }}</h5>
                        <p class="text-muted small mb-2">
                            {{ $user->position ?: $roleLabel }}
                        </p>

                        @if($wilayahRingkas)
                            <div class="d-inline-flex align-items-center gap-1 px-2.5 py-1 rounded-pill bg-light border text-secondary small mb-3" style="font-size: 0.76rem;">
                                <i class="bx bx-map-pin text-primary"></i>
                                <span>{{ $wilayahRingkas }}</span>
                            </div>
                        @endif

                        {{-- Upload / Reset Photo Actions --}}
                        <div class="d-flex flex-wrap justify-content-center gap-2 mt-1">
                            <label for="upload" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold mb-0" tabindex="0">
                                <i class="bx bx-upload me-1"></i>Ubah Foto
                                <input type="file" id="upload" class="account-file-input" hidden
                                    name="avatar" accept="image/png, image/jpeg, image/jpg, image/webp" />
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold account-image-reset">
                                <i class="bx bx-trash me-1"></i>Reset
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">
                            Format JPG, PNG, atau WEBP &bull; Maks. 8MB
                        </small>

                        <hr class="my-3 opacity-50" />

                        {{-- Quick Account Info --}}
                        <div class="text-start d-flex flex-column gap-2.5">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="profile-mini-icon bg-label-primary">
                                    <i class="bx bx-at text-primary"></i>
                                </div>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Nama Pengguna</small>
                                    <span class="fw-semibold text-dark small text-truncate d-block">{{ $user->username ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="profile-mini-icon bg-label-info">
                                    <i class="bx bx-envelope text-info"></i>
                                </div>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Email Terdaftar</small>
                                    <span class="fw-semibold text-dark small text-break d-block">{{ $user->email ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="profile-mini-icon bg-label-success">
                                    <i class="bx bx-phone text-success"></i>
                                </div>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Nomor Telepon</small>
                                    <span class="fw-semibold text-dark small d-block">{{ $user->phone ?: 'Belum diisi' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Security & Password Card --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-warning flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="bx bx-shield-alt-2 fs-4 text-warning"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Keamanan Akun</h6>
                                <small class="text-muted">Perlindungan kata sandi &amp; OTP</small>
                            </div>
                        </div>

                        <p class="text-muted small mb-3" style="line-height: 1.55;">
                            Ganti kata sandi secara berkala untuk menjaga keamanan panel administrasi. Perubahan kata sandi diverifikasi dengan kode OTP ke email Anda.
                        </p>

                        <div class="d-flex flex-wrap gap-1.5 mb-3">
                            <span class="badge bg-light text-secondary border fw-medium" style="font-size: 0.72rem;">
                                <i class="bx bx-check text-success me-0.5"></i>Min. 12 Karakter
                            </span>
                            <span class="badge bg-light text-secondary border fw-medium" style="font-size: 0.72rem;">
                                <i class="bx bx-mail-send text-primary me-0.5"></i>Verifikasi OTP Email
                            </span>
                        </div>

                        <button type="button" class="btn btn-outline-danger w-100 rounded-pill fw-semibold" id="changePasswordBtn" onclick="window.openChangePasswordModal()">
                            <i class="bx bx-key me-1"></i>Ubah Kata Sandi
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right Column: Profile Edit Form --}}
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom px-4 py-3.5 d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 bg-label-primary flex-shrink-0" style="width: 42px; height: 42px;">
                            <i class="bx bx-user-circle fs-4 text-primary"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Informasi Profil &amp; Kontak</h5>
                            <small class="text-muted">Perbarui data diri yang ditampilkan pada panel administrasi.</small>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        {{-- Section 1: Kredensial Akun (Read-only) --}}
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.72rem; letter-spacing: 0.06em;">
                                    Identitas Akun
                                </span>
                                <span class="flex-grow-1 border-bottom"></span>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="username" class="form-label fw-semibold small text-dark mb-1.5">
                                        Nama Pengguna
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted">
                                            <i class="bx bx-user"></i>
                                        </span>
                                        <input class="form-control bg-light text-muted border-start-0" type="text" id="username" name="username"
                                            value="{{ $user->username ?? 'admin_user' }}" disabled />
                                        <span class="input-group-text bg-light text-muted" title="Tidak dapat diubah">
                                            <i class="bx bx-lock-alt"></i>
                                        </span>
                                    </div>
                                    <small class="text-muted mt-1 d-block" style="font-size: 0.74rem;">Nama pengguna dikunci oleh sistem.</small>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="email" class="form-label fw-semibold small text-dark mb-1.5">
                                        Alamat Email
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 text-muted">
                                            <i class="bx bx-envelope"></i>
                                        </span>
                                        <input class="form-control bg-light text-muted border-start-0" type="email" id="email" name="email"
                                            value="{{ old('email', $user->email ?? '') }}"
                                            placeholder="admin@example.com" readonly />
                                        <span class="input-group-text bg-light text-muted" title="Tidak dapat diubah">
                                            <i class="bx bx-lock-alt"></i>
                                        </span>
                                    </div>
                                    <small class="text-muted mt-1 d-block" style="font-size: 0.74rem;">Digunakan untuk pengiriman OTP keamanan.</small>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Data Pribadi & Kontak --}}
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.72rem; letter-spacing: 0.06em;">
                                    Data Pribadi &amp; Kontak
                                </span>
                                <span class="flex-grow-1 border-bottom"></span>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="firstName" class="form-label fw-semibold small text-dark mb-1.5">
                                        Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <input class="form-control @error('name') is-invalid @enderror" type="text" id="firstName" name="name"
                                        value="{{ old('name', $user->name ?? '') }}" placeholder="Masukkan nama lengkap" required />
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="position" class="form-label fw-semibold small text-dark mb-1.5">
                                        Jabatan / Posisi
                                    </label>
                                    <input class="form-control @error('position') is-invalid @enderror" type="text" id="position" name="position"
                                        value="{{ old('position', $user->position ?? '') }}" placeholder="Contoh: Sekretaris Desa / Operator" />
                                    @error('position')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-dark mb-1.5" for="phoneNumber">
                                        Nomor Telepon / WhatsApp
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted fw-semibold">+62</span>
                                        <input type="text" id="phoneNumber" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                            value="{{ old('phone', $user->phone ?? '') }}"
                                            placeholder="812 3456 7890" />
                                    </div>
                                    @error('phone')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="state" class="form-label fw-semibold small text-dark mb-1.5">
                                        Jenis Kelamin
                                    </label>
                                    <select id="state" class="form-select @error('gender') is-invalid @enderror" name="gender">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="laki-laki" {{ old('gender', $user->gender ?? '') == 'laki-laki' ? 'selected' : '' }}>
                                            Laki-laki
                                        </option>
                                        <option value="perempuan" {{ old('gender', $user->gender ?? '') == 'perempuan' ? 'selected' : '' }}>
                                            Perempuan
                                        </option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label fw-semibold small text-dark mb-1.5">
                                        Alamat Lengkap
                                    </label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address"
                                        value="{{ old('address', $user->address ?? '') }}"
                                        placeholder="Masukkan alamat tempat tinggal atau kantor" />
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Wilayah Tugas (jika ada) --}}
                        @if($user && $user->region)
                            <div class="mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="text-uppercase fw-bold text-muted" style="font-size: 0.72rem; letter-spacing: 0.06em;">
                                        Wilayah Tugas Penempatan
                                    </span>
                                    <span class="flex-grow-1 border-bottom"></span>
                                </div>

                                <div class="row g-2.5">
                                    <div class="col-12 col-sm-4">
                                        <div class="region-info-tile p-3 rounded-3 border bg-light bg-opacity-50 h-100">
                                            <small class="text-muted d-block mb-0.5" style="font-size: 0.72rem;">Desa / Kelurahan</small>
                                            <span class="fw-bold text-dark small">{{ $desaStr ?: '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="region-info-tile p-3 rounded-3 border bg-light bg-opacity-50 h-100">
                                            <small class="text-muted d-block mb-0.5" style="font-size: 0.72rem;">Kecamatan</small>
                                            <span class="fw-bold text-dark small">{{ $kecStr ?: '-' }}</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="region-info-tile p-3 rounded-3 border bg-light bg-opacity-50 h-100">
                                            <small class="text-muted d-block mb-0.5" style="font-size: 0.72rem;">Kabupaten / Kota</small>
                                            <span class="fw-bold text-dark small">{{ $kabStr ?: '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block" style="font-size: 0.74rem;">
                                    <i class="bx bx-info-circle me-1"></i>Wilayah tugas diambil otomatis dari struktur penempatan akun Anda.
                                </small>
                            </div>
                        @endif

                        {{-- Form Footer Actions --}}
                        <div class="pt-3 border-top d-flex flex-column flex-sm-row justify-content-end gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 w-100 w-sm-auto order-2 order-sm-1">
                                <i class="bx bx-arrow-back me-1"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm w-100 w-sm-auto order-1 order-sm-2">
                                <i class="bx bx-save me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@section('modals')
    @include('admin.profile.modals')
@endsection

<style>
    .profile-cover-strip {
        height: 82px;
        background: linear-gradient(135deg, #0d6efd 0%, #3b82f6 55%, #60a5fa 100%);
        position: relative;
    }

    .profile-cover-strip::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255, 255, 255, 0.2) 1px, transparent 1px);
        background-size: 16px 16px;
        opacity: 0.55;
    }

    .avatar-wrapper {
        width: 108px;
        height: 108px;
        cursor: pointer;
    }

    .avatar-preview {
        width: 108px;
        height: 108px;
        object-fit: cover;
        border: 4px solid #ffffff;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.14);
        background-color: #cbd5e1;
        transition: transform 0.22s ease;
    }

    .avatar-wrapper:hover .avatar-preview {
        transform: scale(1.03);
    }

    .avatar-cam-badge {
        position: absolute;
        right: 2px;
        bottom: 2px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #0d6efd;
        color: #ffffff;
        border: 2.5px solid #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .avatar-wrapper:hover .avatar-cam-badge {
        background: #0a58ca;
        transform: scale(1.08);
    }

    .profile-mini-icon {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1rem;
    }

    #formAccountSettings .form-control,
    #formAccountSettings .form-select {
        border-radius: 10px;
        border-color: #dbe2ea;
        padding-top: 0.55rem;
        padding-bottom: 0.55rem;
        font-size: 0.9rem;
    }

    #formAccountSettings .input-group > .form-control {
        border-radius: 0;
    }

    #formAccountSettings .input-group > .form-control:last-child {
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }

    #formAccountSettings .input-group > .input-group-text:first-child {
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
        border-color: #dbe2ea;
    }

    #formAccountSettings .input-group > .input-group-text:last-child {
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
        border-color: #dbe2ea;
    }

    #formAccountSettings .form-control:focus,
    #formAccountSettings .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.14);
    }

    .region-info-tile {
        border-color: #e2e8f0 !important;
    }
</style>
@endsection

@section('scripts')
    @include('admin.profile.scripts')
@endsection
