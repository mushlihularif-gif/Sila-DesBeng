@extends('admin.layouts.admin')

@section('title', 'Staf Layanan Desa')

@section('content')
<style>
    .staff-hero-card {
        border-radius: 16px;
        background: linear-gradient(135deg, rgba(105, 108, 255, 0.08) 0%, rgba(3, 195, 236, 0.06) 100%);
        border: 1px solid rgba(105, 108, 255, 0.14);
    }
    .staff-perm-option {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.7rem 0.9rem;
        border-radius: 12px;
        border: 1.5px solid #e4e7ec;
        background: #fff;
        cursor: pointer;
        transition: all 0.18s ease;
        user-select: none;
    }
    .staff-perm-option:hover {
        border-color: #93c5fd;
        background: #f8fbff;
    }
    .staff-perm-option:has(input:checked) {
        border-color: #0d6efd;
        background: #f0f6ff;
        box-shadow: 0 2px 10px rgba(13, 110, 253, 0.12);
    }
    .staff-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .staff-card:hover {
        border-color: #d6dcf5;
        box-shadow: 0 6px 20px rgba(67, 89, 113, 0.08) !important;
    }
    .staff-avatar-circle {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.15rem;
    }
    .staff-stat-pill {
        border-radius: 12px;
        background: #fff;
        border: 1px solid #eef1f6;
        padding: 0.85rem 1rem;
    }
    @media (max-width: 575.98px) {
        .staff-avatar-circle {
            width: 42px;
            height: 42px;
            min-width: 42px;
            font-size: 1rem;
        }
    }
</style>

@php
    $totalStaff = $staff->count();
    $activeStaff = $staff->filter(fn($p) => $p->hasSupportedRole())->count();
    $inactiveStaff = $totalStaff - $activeStaff;

    $unitIcons = [
        'gas' => ['bx-gas-pump', 'danger', 'Stok tabung, pesanan & chat gas'],
        'sewa_alat' => ['bx-wrench', 'warning', 'Katalog alat, booking & pengantaran'],
        'sewa_mobil' => ['bx-car', 'info', 'Armada, supir & booking transportasi'],
        'fasilitas_umum' => ['bx-buildings', 'success', 'Gedung, aula & pemegang kunci'],
        'kabar_informasi' => ['bx-news', 'primary', 'Berita, dokumentasi & pengumuman'],
    ];
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Tata Kelola Desa /</span> Staf Layanan Desa
            </h4>
            <p class="text-muted small mb-0">Atur petugas lapangan dan batasi akses hanya pada unit layanan yang ditugaskan.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm d-inline-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTambahPetugas" aria-expanded="{{ $errors->any() || $totalStaff === 0 ? 'true' : 'false' }}">
            <i class="bx bx-user-plus fs-5"></i>
            <span>Tambah Petugas</span>
        </button>
    </div>

    <!-- Flash & Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible border-0 shadow-sm rounded-4 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-check-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible border-0 shadow-sm rounded-4 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-error-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible border-0 shadow-sm rounded-4 mb-4" role="alert">
            <div class="d-flex align-items-start">
                <i class="bx bx-error-circle fs-4 me-2 mt-1 flex-shrink-0"></i>
                <div>
                    <div class="fw-bold mb-1">Periksa kembali data formulir:</div>
                    <ul class="mb-0 ps-3 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Info Banner + Ringkasan -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card bg-label-primary border-0 shadow-none rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bx bx-shield-quarter fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Batasan Hak Akses Staf Layanan</h6>
                        <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                            Staf hanya dapat mengelola produk, pesanan, verifikasi pembayaran, chat, atau <strong>Kabar &amp; Informasi Daerah (berita, dokumentasi &amp; pengumuman)</strong> sesuai unit yang Anda centang.
                            <strong>Rekening desa, verifikasi warga, akun admin, serta pengaturan wilayah tetap hanya dipegang Admin Desa.</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="staff-stat-pill h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Total Staf</span>
                        <h4 class="fw-bold text-primary mb-0">{{ $totalStaff }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="staff-stat-pill h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Staf Aktif</span>
                        <h4 class="fw-bold text-success mb-0">{{ $activeStaff }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="staff-stat-pill h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Nonaktif</span>
                        <h4 class="fw-bold text-secondary mb-0">{{ $inactiveStaff }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Tambah Petugas (Collapsible, otomatis terbuka jika belum ada staf atau ada error) -->
    <div class="collapse {{ $errors->any() || $totalStaff === 0 ? 'show' : '' }} mb-4" id="collapseTambahPetugas">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm bg-label-primary rounded-3 d-flex align-items-center justify-content-center">
                        <i class="bx bx-user-plus fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 fs-6">Tambah Petugas Baru</h5>
                        <small class="text-muted">Buat akun khusus petugas unit layanan desa</small>
                    </div>
                </div>
                @if($totalStaff > 0)
                <button type="button" class="btn-close shadow-none" data-bs-toggle="collapse" data-bs-target="#collapseTambahPetugas" aria-label="Tutup"></button>
                @endif
            </div>
            <div class="card-body p-3 p-md-4">
                <form method="post" action="{{ route('admin.staff.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small text-dark" for="staff_name">Nama Lengkap Petugas <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-light"><i class="bx bx-user text-muted"></i></span>
                                <input id="staff_name" class="form-control" name="name" required maxlength="255" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small text-dark" for="staff_email">Alamat Email <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-light"><i class="bx bx-envelope text-muted"></i></span>
                                <input id="staff_email" class="form-control" type="email" name="email" required value="{{ old('email') }}" placeholder="petugas@desa.id">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small text-dark" for="staff_username">Username Login <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text bg-light"><i class="bx bx-at text-muted"></i></span>
                                <input id="staff_username" class="form-control" name="username" required maxlength="50" value="{{ old('username') }}" autocomplete="off" placeholder="petugas_gas">
                            </div>
                            <small class="text-muted" style="font-size: 0.72rem;">Huruf, angka, garis bawah, atau tanda hubung tanpa spasi.</small>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-semibold small text-dark mb-0" for="staff_password">Kata Sandi <span class="text-danger">*</span></label>
                                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-0.5" onclick="generateStaffPassword()" title="Buat kata sandi acak yang aman (14 karakter)">
                                    <i class="bx bx-key me-0.5"></i>Buat Sandi Acak
                                </button>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bx bx-lock-alt text-muted"></i></span>
                                <input id="staff_password" class="form-control" name="password" type="password" required minlength="12" autocomplete="new-password" placeholder="Minimal 12 karakter (contoh: DesaBengkalis2026)">
                                <button class="btn btn-outline-secondary" type="button" id="toggle_staff_password" onclick="toggleStaffPassword('staff_password', this)" title="Tampilkan/Sembunyikan">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-1 gap-2">
                                <small id="staff_password_hint" class="text-muted" style="font-size: 0.74rem;">
                                    <i class="bx bx-info-circle me-0.5"></i>Wajib minimal <strong>12 karakter</strong> (kombinasi huruf &amp; angka disarankan).
                                </small>
                                <span id="staff_password_count" class="badge bg-label-secondary rounded-pill flex-shrink-0" style="font-size: 0.68rem;">0 / 12</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-dark mb-1" for="staff_password_confirmation">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bx bx-lock-alt text-muted"></i></span>
                                <input id="staff_password_confirmation" class="form-control" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" placeholder="Ketik ulang kata sandi yang sama">
                                <button class="btn btn-outline-secondary" type="button" id="toggle_staff_password_confirmation" onclick="toggleStaffPassword('staff_password_confirmation', this)" title="Tampilkan/Sembunyikan">
                                    <i class="bx bx-show"></i>
                                </button>
                            </div>
                            <small id="staff_password_match" class="text-muted d-block mt-1" style="font-size: 0.74rem;">Harus sama persis dengan kata sandi di sebelah kiri.</small>
                        </div>
                    </div>

                    <fieldset class="mt-4 pt-3 border-top">
                        <legend class="fs-6 fw-bold text-dark mb-1">Unit Layanan yang Boleh Dikelola <span class="text-danger">*</span></legend>
                        <p class="text-muted small mb-3">Pilih minimal satu unit layanan (termasuk <strong>Kabar &amp; Informasi Daerah</strong> untuk petugas berita/dokumentasi).</p>
                        <div class="row g-2">
                            @foreach($units as $key => $label)
                                @php
                                    [$uIcon, $uColor, $uSub] = $unitIcons[$key] ?? ['bx-grid-alt', 'primary', ''];
                                @endphp
                                <div class="col-12 col-sm-6 col-lg-4">
                                    <label class="staff-perm-option w-100 h-100">
                                        <input type="checkbox" class="form-check-input mt-0 flex-shrink-0" name="units[]" value="{{ $key }}" @checked(in_array($key, old('units', [])))>
                                        <span class="badge bg-label-{{ $uColor }} rounded-3 p-2 d-inline-flex align-items-center justify-content-center flex-shrink-0">
                                            <i class="bx {{ $uIcon }} fs-6"></i>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="fw-semibold text-dark small d-block">{{ $label }}</span>
                                            @if($uSub)
                                                <small class="text-muted d-block" style="font-size: 0.7rem; line-height: 1.25;">{{ $uSub }}</small>
                                            @endif
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mt-4 pt-3 border-top">
                        <small class="text-muted">
                            <i class="bx bx-info-circle me-1 text-primary"></i>
                            Sampaikan username dan kata sandi secara langsung kepada petugas setelah akun dibuat.
                        </small>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto flex-shrink-0">
                            <i class="bx bx-check me-1"></i> Buat Akun Staf
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Daftar Petugas Terdaftar -->
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-2 mb-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="bx bx-id-card text-primary"></i>
            <span>Petugas Terdaftar</span>
            <span class="badge bg-label-primary rounded-pill">{{ $totalStaff }}</span>
        </h5>
        @if($totalStaff > 0)
        <div class="input-group input-group-merge input-group-sm w-100 w-sm-auto" style="min-width: 240px; max-width: 320px;">
            <span class="input-group-text bg-white border-end-0"><i class="bx bx-search text-muted"></i></span>
            <input type="text" id="searchStaffInput" class="form-control border-start-0" placeholder="Cari nama, username, atau unit...">
        </div>
        @endif
    </div>

    @forelse($staff as $person)
        @php
            $isActive = $person->hasSupportedRole();
            $assignedKeys = $person->staffPermissions->pluck('unit_key')->all();
            $collapseId = 'editStaffCollapse' . $person->id;
        @endphp
        <div class="card staff-card shadow-sm mb-3 overflow-hidden">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <!-- Kiri: Avatar + Info -->
                    <div class="d-flex align-items-start gap-3 min-w-0 flex-grow-1">
                        <div class="staff-avatar-circle {{ $isActive ? 'bg-label-primary text-primary' : 'bg-label-secondary text-secondary' }}">
                            {{ strtoupper(substr($person->name ?? 'S', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h6 class="fw-bold text-dark mb-0">{{ $person->name }}</h6>
                                @if($isActive)
                                    <span class="badge bg-label-success rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                        <i class="bx bx-check-circle me-1"></i>Aktif
                                    </span>
                                @else
                                    <span class="badge bg-label-secondary rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                        <i class="bx bx-pause-circle me-1"></i>Nonaktif / Perlu Izin Ulang
                                    </span>
                                @endif
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2 gap-sm-3 text-muted small mb-2">
                                <span class="d-inline-flex align-items-center">
                                    <i class="bx bx-at me-1"></i>{{ $person->username }}
                                </span>
                                <span class="d-inline-flex align-items-center text-break">
                                    <i class="bx bx-envelope me-1"></i>{{ $person->email }}
                                </span>
                            </div>

                            <!-- Badge Unit yang Dipegang -->
                            <div class="d-flex flex-wrap gap-1">
                                @forelse($assignedKeys as $uKey)
                                    @php
                                        [$uIcon, $uColor] = $unitIcons[$uKey] ?? ['bx-grid-alt', 'primary'];
                                    @endphp
                                    <span class="badge bg-label-{{ $uColor }} rounded-pill px-2 py-1 fw-medium" style="font-size: 0.72rem;">
                                        <i class="bx {{ $uIcon }} me-1"></i>{{ $units[$uKey] ?? $uKey }}
                                    </span>
                                @empty
                                    <span class="badge bg-label-warning rounded-pill px-2 py-1 fw-medium" style="font-size: 0.72rem;">
                                        <i class="bx bx-error me-1"></i>Belum ada izin unit
                                    </span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Tombol Kelola -->
                    <div class="w-100 w-md-auto flex-shrink-0">
                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3 w-100 w-md-auto d-inline-flex align-items-center justify-content-center gap-1" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false">
                            <i class="bx bx-edit-alt"></i>
                            <span>Kelola Izin &amp; Akun</span>
                        </button>
                    </div>
                </div>

                <!-- Panel Edit Collapsible -->
                <div class="collapse mt-3 pt-3 border-top" id="{{ $collapseId }}">
                    <form method="post" action="{{ route('admin.staff.update', $person->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold small text-dark">Nama Petugas <span class="text-danger">*</span></label>
                                <input class="form-control" name="name" required value="{{ $person->name }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold small text-dark">Status Akun</label>
                                <select name="enabled" class="form-select">
                                    <option value="1" @selected($isActive)>Aktif (Boleh Login)</option>
                                    <option value="0" @selected(!$isActive)>Nonaktif (Cabut Akses)</option>
                                </select>
                            </div>
                        </div>

                        <fieldset class="mt-3">
                            <legend class="fs-6 fw-bold text-dark mb-2" style="font-size: 0.88rem !important;">Izin Unit Layanan</legend>
                            <div class="row g-2">
                                @foreach($units as $key => $label)
                                    @php
                                        [$uIcon, $uColor] = $unitIcons[$key] ?? ['bx-grid-alt', 'primary'];
                                    @endphp
                                    <div class="col-12 col-sm-6 col-lg-4">
                                        <label class="staff-perm-option w-100">
                                            <input type="checkbox" class="form-check-input mt-0 flex-shrink-0" name="units[]" value="{{ $key }}" @checked($person->staffPermissions->contains('unit_key', $key))>
                                            <span class="badge bg-label-{{ $uColor }} rounded-3 p-1 d-inline-flex align-items-center justify-content-center flex-shrink-0">
                                                <i class="bx {{ $uIcon }}"></i>
                                            </span>
                                            <span class="fw-medium text-dark small">{{ $label }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>

                        <div class="row g-3 mt-1">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold small text-dark">Kata Sandi Baru <span class="text-muted fw-normal">(Opsional · Min. 12 karakter)</span></label>
                                <input type="password" name="password" minlength="12" class="form-control js-staff-edit-pass" autocomplete="new-password" placeholder="Kosongkan jika tidak ingin mengubah">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold small text-dark">Ulangi Kata Sandi Baru</label>
                                <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" placeholder="Ulangi kata sandi baru">
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mt-4 pt-3 border-top">
                            <small class="text-muted">
                                <i class="bx bx-info-circle me-1 text-primary"></i>
                                Perubahan izin unit langsung berlaku saat staf memuat ulang halaman tanpa mengakhiri sesi login. Sesi hanya diakhiri jika akun dinonaktifkan atau kata sandi diubah.
                            </small>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto flex-shrink-0">
                                <i class="bx bx-save me-1"></i> Simpan Izin dan Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5 px-3">
                <div class="avatar avatar-lg bg-label-primary text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bx bx-group fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Belum Ada Staf Layanan</h6>
                <p class="text-muted small mb-0">Tambahkan petugas melalui formulir di atas dan pilih layanan yang akan dibantunya.</p>
            </div>
        </div>
    @endforelse
</div>

@push('scripts')
<script>
    function toggleStaffPassword(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPass = input.type === 'password';
        input.type = isPass ? 'text' : 'password';
        const icon = btn?.querySelector('i');
        if (icon) {
            icon.className = isPass ? 'bx bx-hide' : 'bx bx-show';
        }
    }

    function generateStaffPassword() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        const randomChunk = (len) => {
            let out = '';
            for (let i = 0; i < len; i++) {
                out += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return out;
        };
        const generated = 'Desa-' + randomChunk(4) + '-' + randomChunk(4);
        const passEl = document.getElementById('staff_password');
        const confEl = document.getElementById('staff_password_confirmation');
        if (passEl && confEl) {
            passEl.type = 'text';
            confEl.type = 'text';
            passEl.value = generated;
            confEl.value = generated;
            const icon1 = document.querySelector('#toggle_staff_password i');
            const icon2 = document.querySelector('#toggle_staff_password_confirmation i');
            if (icon1) icon1.className = 'bx bx-hide';
            if (icon2) icon2.className = 'bx bx-hide';
            passEl.dispatchEvent(new Event('input'));
            confEl.dispatchEvent(new Event('input'));
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const passEl = document.getElementById('staff_password');
        const confEl = document.getElementById('staff_password_confirmation');
        const countEl = document.getElementById('staff_password_count');
        const hintEl = document.getElementById('staff_password_hint');
        const matchEl = document.getElementById('staff_password_match');

        function validateStaffPasswords() {
            if (!passEl || !confEl) return;
            const len = passEl.value.length;

            if (countEl) {
                countEl.textContent = len + ' / 12';
                countEl.className = 'badge rounded-pill flex-shrink-0 ' + (len >= 12 ? 'bg-label-success' : (len > 0 ? 'bg-label-warning' : 'bg-label-secondary'));
            }
            if (hintEl) {
                if (len >= 12) {
                    hintEl.className = 'text-success fw-semibold';
                    hintEl.innerHTML = '<i class="bx bx-check-circle me-0.5"></i>Panjang kata sandi memenuhi syarat minimal 12 karakter.';
                } else if (len > 0) {
                    hintEl.className = 'text-warning fw-semibold';
                    hintEl.innerHTML = '<i class="bx bx-error-circle me-0.5"></i>Kurang ' + (12 - len) + ' karakter lagi (minimal 12 karakter).';
                } else {
                    hintEl.className = 'text-muted';
                    hintEl.innerHTML = '<i class="bx bx-info-circle me-0.5"></i>Wajib minimal <strong>12 karakter</strong> (kombinasi huruf &amp; angka disarankan).';
                }
            }

            if (len === 0) {
                passEl.setCustomValidity('Kata sandi wajib diisi (minimal 12 karakter).');
            } else if (len < 12) {
                passEl.setCustomValidity('Kata sandi minimal 12 karakter (saat ini baru ' + len + ' karakter).');
            } else {
                passEl.setCustomValidity('');
            }

            const confVal = confEl.value;
            if (confVal.length === 0) {
                confEl.setCustomValidity('Silakan ketik ulang kata sandi.');
                if (matchEl) {
                    matchEl.className = 'text-muted d-block mt-1';
                    matchEl.textContent = 'Harus sama persis dengan kata sandi di sebelah kiri.';
                }
            } else if (confVal !== passEl.value) {
                confEl.setCustomValidity('Konfirmasi kata sandi tidak sama.');
                if (matchEl) {
                    matchEl.className = 'text-danger fw-semibold d-block mt-1';
                    matchEl.innerHTML = '<i class="bx bx-x-circle me-0.5"></i>Kata sandi belum sama.';
                }
            } else {
                confEl.setCustomValidity('');
                if (matchEl) {
                    matchEl.className = 'text-success fw-semibold d-block mt-1';
                    matchEl.innerHTML = '<i class="bx bx-check-circle me-0.5"></i>Kata sandi cocok.';
                }
            }
        }

        if (passEl && confEl) {
            passEl.addEventListener('input', validateStaffPasswords);
            confEl.addEventListener('input', validateStaffPasswords);
            validateStaffPasswords();
        }

        document.querySelectorAll('.js-staff-edit-pass').forEach(function(el) {
            el.addEventListener('input', function() {
                if (this.value.length > 0 && this.value.length < 12) {
                    this.setCustomValidity('Kata sandi baru minimal 12 karakter (saat ini baru ' + this.value.length + ' karakter).');
                } else {
                    this.setCustomValidity('');
                }
            });
        });

        const searchInput = document.getElementById('searchStaffInput');
        if (!searchInput) return;
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.staff-card').forEach(card => {
                const text = (card.textContent || '').toLowerCase();
                card.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
        });
    });
</script>
@endpush
@endsection
