@extends('admin.layouts.admin')

@section('title', 'Profil Saya')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Pengaturan Akun /</span> Profil Saya</h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Berhasil!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-md-12">
                <form id="formAccountSettings" method="POST" action="{{ route('admin.profile.update') }}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="card mb-4 profile-card">
                        <h5 class="card-header bg-gradient-primary text-white">
                            <i class="bx bx-user-circle me-2"></i>Detail Profil
                        </h5>
                        <!-- Account -->
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex align-items-start align-items-sm-center gap-4 mb-4">
                                <div class="avatar-wrapper position-relative">
                                    @if($user && $user->file)
                                        <img src="{{ route('media.avatar', ['filename' => basename($user->file->path)]) }}"
                                            alt="user-avatar" class="avatar-preview rounded-circle" 
                                            id="uploadedAvatar" />
                                    @else
                                        <div class="avatar-preview avatar-default rounded-circle d-flex align-items-center justify-content-center" id="uploadedAvatar" style="background-color: #D1D5DB; background-image: none;">
                                            <svg viewBox="0 0 24 24" fill="currentColor" style="width: 60px; height: 60px; color: white;">
                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="avatar-overlay rounded-circle">
                                        <i class="bx bx-camera"></i>
                                    </div>
                                </div>
                                <div class="button-wrapper flex-grow-1">
                                    <label for="upload" class="btn btn-primary me-2 mb-2" tabindex="0">
                                        <i class="bx bx-upload me-1"></i>
                                        <span>Upload Foto Baru</span>
                                        <input type="file" id="upload" class="account-file-input" hidden
                                            name="avatar" accept="image/png, image/jpeg, image/jpg, image/gif" />
                                    </label>
                                    <button type="button" class="btn btn-outline-secondary account-image-reset mb-2">
                                        <i class="bx bx-reset me-1"></i>
                                        <span>Reset</span>
                                    </button>

                                    <p class="text-muted mb-0 mt-2">
                                        <small><i class="bx bx-info-circle me-1"></i>Diizinkan JPG, PNG, atau GIF. Ukuran maksimal 8MB</small>
                                    </p>
                                </div>
                            </div>
                            
                            <hr class="my-5" />
                            
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="username" class="form-label fw-semibold mb-2">
                                        <i class="bx bx-user me-1"></i>Username
                                    </label>
                                    <input class="form-control form-control-lg" type="text" id="username" name="username"
                                        value="{{ $user->username ?? 'admin_user' }}" disabled style="background-color: #f3f4f6;" />
                                    <small class="text-muted mt-2 d-block">Username tidak dapat diubah</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold mb-2">
                                        <i class="bx bx-envelope me-1"></i>E-mail
                                    </label>
                                    <input class="form-control form-control-lg" type="email" id="email" name="email"
                                        value="{{ old('email', $user->email ?? 'admin@example.com') }}"
                                        placeholder="admin@example.com" readonly style="background-color: #f3f4f6;" />
                                    <small class="text-muted mt-2 d-block">Email tidak dapat diubah</small>
                                </div>
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label fw-semibold mb-2">
                                        <i class="bx bx-id-card me-1"></i>Nama Lengkap
                                    </label>
                                    <input class="form-control form-control-lg" type="text" id="firstName" name="name"
                                        value="{{ old('name', $user->name ?? 'Admin Nama') }}" autofocus />
                                </div>
                                
                                @if($user && $user->region)
                                <div class="col-md-6">
                                    <label for="region" class="form-label fw-semibold mb-2">
                                        <i class="bx bx-map-pin me-1"></i>Wilayah Tugas ({{ ucfirst($user->region->type) }})
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="region" name="region"
                                        value="{{ $user->region->full_path }}" disabled style="background-color: #f3f4f6;" />
                                    <small class="text-muted mt-2 d-block">Wilayah tugas sesuai penempatan akun</small>
                                </div>
                                @endif

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold mb-2" for="phoneNumber">
                                        <i class="bx bx-phone me-1"></i>Nomor Telepon
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text">ID (+62)</span>
                                        <input type="text" id="phoneNumber" name="phone" class="form-control"
                                            value="{{ old('phone', $user->phone ?? '') }}"
                                            placeholder="812 3456 7890" />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="address" class="form-label fw-semibold mb-2">
                                        <i class="bx bx-map me-1"></i>Alamat
                                    </label>
                                    <input type="text" class="form-control form-control-lg" id="address" name="address"
                                        value="{{ old('address', $user->address ?? '') }}"
                                        placeholder="Alamat Lengkap" />
                                </div>
                                <div class="col-md-6">
                                    <label for="state" class="form-label fw-semibold mb-2">
                                        <i class="bx bx-male-female me-1"></i>Jenis Kelamin
                                    </label>
                                    <select id="state" class="form-select form-select-lg" name="gender">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="laki-laki"
                                            {{ old('gender', $user->gender ?? '') == 'laki-laki' ? 'selected' : '' }}>
                                            Laki-laki</option>
                                        <option value="perempuan"
                                            {{ old('gender', $user->gender ?? '') == 'perempuan' ? 'selected' : '' }}>
                                            Perempuan</option>
                                    </select>
                                </div>
                                <input type="hidden" id="deleteAvatarInput" name="delete_avatar" value="0">
                            </div>
                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bx bx-save me-1"></i>Simpan Perubahan
                                </button>
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="bx bx-x me-1"></i>Batal
                                </a>
                            </div>
                        </div>
                        <!-- /Account -->
                    </div>
                    
                    <div class="card security-card">
                        <h5 class="card-header bg-gradient-warning text-white">
                            <i class="bx bx-shield me-2"></i>Keamanan Akun
                        </h5>
                        <div class="card-body p-4 p-md-5">
                            <div class="alert alert-warning border-warning mb-4">
                                <h6 class="alert-heading fw-bold mb-2">
                                    <i class="bx bx-info-circle me-1"></i>Apakah Anda ingin mengubah kata sandi?
                                </h6>
                                <p class="mb-0">Disarankan untuk mengganti kata sandi secara berkala demi keamanan akun Anda.</p>
                            </div>
                            <button type="button" class="btn btn-danger btn-lg" id="changePasswordBtn" onclick="window.openChangePasswordModal()">
                                <i class="bx bx-key me-1"></i>Ubah Kata Sandi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @section('modals')
        @include('admin.profile.modals')
    @endsection

    <style>
        .profile-card, .security-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .profile-card:hover, .security-card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .bg-gradient-primary {
            background: #4a4a4a;
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #4a4a4a 0%, #2c2c2c 100%);
        }

        .avatar-wrapper {
            position: relative;
            width: 120px;
            height: 120px;
        }

        .avatar-preview {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .avatar-default {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #0099ff 0%, #ffb300 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .avatar-initials {
            font-size: 48px;
            font-weight: bold;
            color: white;
        }

        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 120px;
            height: 120px;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }

        .avatar-overlay i {
            font-size: 32px;
            color: white;
        }

        .avatar-wrapper:hover .avatar-overlay {
            opacity: 1;
        }

        .avatar-wrapper:hover .avatar-preview {
            transform: scale(1.05);
        }

        .form-control-lg, .form-select-lg {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control-lg:focus, .form-select-lg:focus {
            border-color: #0099ff;
            box-shadow: 0 0 0 0.2rem rgba(0, 153, 255, 0.25);
        }

        .btn-lg {
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #0099ff;
            border: none;
        }

        .btn-primary:hover {
            background: #0088ee;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 153, 255, 0.4);
        }

        .btn-outline-secondary:hover {
            transform: translateY(-2px);
        }

        .otp-input {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 24px;
            font-weight: bold;
        }

        .otp-input:focus {
            border-color: #0099ff;
            box-shadow: 0 0 0 0.2rem rgba(0, 153, 255, 0.25);
        }

        .modal-content {
            border-radius: 12px;
            border: none;
            overflow: hidden;
        }

        .modal-header {
            border-bottom: none;
        }

        .toggle-password {
            z-index: 10;
        }

        .toggle-password:hover {
            color: #0099ff;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-card, .security-card {
            animation: fadeInUp 0.5s ease;
        }

        .security-card {
            animation-delay: 0.1s;
        }
    </style>
@endsection

@section('scripts')
    @include('admin.profile.scripts')
@endsection