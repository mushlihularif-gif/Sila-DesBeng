@extends('admin.layouts.admin')

@section('title', 'Detail Verifikasi Identitas - ' . ($kyc->user->name ?? 'Warga'))

@section('content')
<style>
    .animate-fade-up {
        animation: fadeUp 0.4s ease-out forwards;
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(16px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .kyc-card {
        border-radius: 14px;
        border: 1px solid rgba(67, 89, 113, 0.1);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        background: #ffffff;
        transition: box-shadow 0.2s ease;
    }
    .kyc-card:hover {
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.07);
    }
    .compare-item {
        border-radius: 10px;
        border: 1px solid #edf0f3;
        background: #ffffff;
        padding: 14px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }
    .compare-item:hover {
        border-color: #d2d8e0;
        background: #fafbfc;
    }
    .data-box {
        border-radius: 8px;
        padding: 10px 12px;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .data-box-user {
        background-color: #f8f9fa;
        border: 1px solid #edf0f2;
    }
    .data-box-ocr {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
    }
    .ktp-preview-container {
        border-radius: 12px;
        border: 1px solid #e2e6eb;
        background: #f8f9fc;
        padding: 12px;
        text-align: center;
        overflow: hidden;
    }
    .ktp-preview-img {
        max-height: 280px;
        width: auto;
        max-width: 100%;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: transform 0.25s ease;
    }
    .ktp-preview-img:hover {
        transform: scale(1.02);
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item text-muted">Permintaan & Aktivitas</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.kyc.index') }}" class="text-muted">Verifikasi Identitas</a></li>
                    <li class="breadcrumb-item active text-dark fw-semibold">Detail Pengajuan #{{ $kyc->id }}</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="fw-bold text-dark mb-0">Detail Verifikasi Identitas</h4>
                @if($kyc->status === 'pending')
                    <span class="badge bg-label-warning rounded-pill px-3 py-2 fw-semibold"><i class="bx bx-time-five me-1"></i> Menunggu Persetujuan</span>
                @elseif($kyc->status === 'approved')
                    <span class="badge bg-label-success rounded-pill px-3 py-2 fw-semibold"><i class="bx bx-check-shield me-1"></i> Disetujui</span>
                @else
                    <span class="badge bg-label-danger rounded-pill px-3 py-2 fw-semibold"><i class="bx bx-x-circle me-1"></i> Ditolak</span>
                @endif
            </div>
            <p class="text-muted small mb-0 mt-1">Diajukan oleh <strong class="text-dark">{{ $kyc->user->name ?? 'Warga' }}</strong> pada {{ $kyc->created_at->format('d M Y, H:i') }} WIB ({{ $kyc->created_at->diffForHumans() }})</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.kyc.index') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-none">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-error-circle fs-4 me-2"></i>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-check-circle fs-4 me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($kyc->status === 'approved')
        <div class="alert alert-success border-success-subtle rounded-3 mb-4 d-flex align-items-center gap-3 py-3 px-4">
            <i class="bx bx-shield-check fs-2 text-success flex-shrink-0"></i>
            <div>
                <div class="fw-bold text-dark">Verifikasi Selesai & Berkas Fisik Dimusnahkan (Kepatuhan Privasi UU PDP)</div>
                <div class="small text-muted">Pengajuan ini telah disetujui resmi. Demi melindungi privasi warga dan mencegah ancaman kebocoran data (zero footprint), seluruh file fisik foto e-KTP dan foto wajah/biometrik telah dihapus permanen secara otomatis dari server.</div>
            </div>
        </div>
    @elseif(!$kyc->face_image_path)
        <div class="alert alert-warning border-warning-subtle rounded-3 mb-4 d-flex align-items-center gap-3 py-3 px-4">
            <i class="bx bx-info-circle fs-2 text-warning flex-shrink-0"></i>
            <div>
                <div class="fw-bold text-dark">Informasi Peninjauan: Pengajuan Berkas KTP</div>
                <div class="small text-muted">Warga mengajukan verifikasi tanpa melampirkan foto scan wajah/selfie (misalnya kendala perangkat kamera atau jaringan). Anda tetap dapat meninjau keabsahan berkas e-KTP dan data kependudukan secara langsung, kemudian memberikan persetujuan atau meminta perbaikan berkas.</div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Kolom Kiri: Dokumen Visual (Foto KTP & Foto Biometrik) -->
        <div class="col-12 col-lg-5">
            <!-- Card Foto KTP -->
            <div class="card kyc-card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between py-3 border-bottom bg-light">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-id-card text-primary fs-4 me-2"></i>
                        <h6 class="mb-0 fw-bold text-dark">Dokumen e-KTP</h6>
                    </div>
                    @if($kyc->status === 'approved')
                        <span class="badge bg-label-success rounded-pill px-2 py-1"><i class="bx bx-check-shield me-1"></i> Terverifikasi</span>
                    @else
                        <span class="badge bg-label-secondary rounded-pill px-2 py-1">e-KTP Asli</span>
                    @endif
                </div>
                <div class="card-body pt-4">
                    @if($kyc->ktp_image_path)
                        <div class="ktp-preview-container mb-3">
                            <img src="{{ route('media.secure.ktp', basename($kyc->ktp_image_path)) }}" class="ktp-preview-img" alt="KTP {{ $kyc->user->name ?? 'Warga' }}">
                        </div>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#ktpModal">
                                <i class="bx bx-zoom-in me-1"></i> Perbesar Foto KTP
                            </button>
                            <a href="{{ route('media.secure.ktp', basename($kyc->ktp_image_path)) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-none">
                                <i class="bx bx-link-external me-1"></i> Buka Tab Baru
                            </a>
                        </div>
                    @elseif($kyc->status === 'approved')
                        <div class="text-center py-4">
                            <div class="mx-auto mb-2 rounded-circle d-flex align-items-center justify-content-center bg-label-success" style="width: 60px; height: 60px;">
                                <i class="bx bx-shield-quarter fs-2 text-success"></i>
                            </div>
                            <p class="text-dark small mb-0 fw-bold">Berkas e-KTP Telah Dihapus</p>
                            <small class="text-muted" style="font-size: 0.75rem;">Dimusnahkan otomatis dari server demi keamanan dan privasi warga (UU PDP).</small>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-image-alt text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                            <p class="text-muted small mt-2 mb-0">Foto e-KTP tidak tersedia atau tidak dapat dimuat.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card Foto Wajah / Selfie & Biometrik -->
            <div class="card kyc-card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between py-3 border-bottom bg-light">
                    <div class="d-flex align-items-center">
                        @if($kyc->status === 'approved')
                            <i class="bx bx-shield-check text-success fs-4 me-2"></i>
                            <h6 class="mb-0 fw-bold text-dark">Foto Wajah / Biometrik</h6>
                        @elseif($kyc->face_scan_data && count($kyc->face_scan_data) > 0)
                            <i class="bx bx-scan text-success fs-4 me-2"></i>
                            <h6 class="mb-0 fw-bold text-dark">Hasil Scan Wajah (Biometrik)</h6>
                        @elseif($kyc->face_image_path)
                            <i class="bx bx-camera text-primary fs-4 me-2"></i>
                            <h6 class="mb-0 fw-bold text-dark">Foto Wajah / Selfie</h6>
                        @else
                            <i class="bx bx-user-x text-warning fs-4 me-2"></i>
                            <h6 class="mb-0 fw-bold text-dark">Foto Wajah / Biometrik</h6>
                        @endif
                    </div>
                    @if($kyc->status === 'approved')
                        <span class="badge bg-label-success rounded-pill px-2 py-1"><i class="bx bx-check-shield me-1"></i> Terverifikasi</span>
                    @elseif($kyc->face_scan_data && count($kyc->face_scan_data) > 0)
                        <span class="badge bg-label-success rounded-pill px-2 py-1"><i class="bx bx-scan me-1"></i> Scan Kamera AI (Liveness)</span>
                    @elseif($kyc->face_image_path)
                        <span class="badge bg-label-primary rounded-pill px-2 py-1"><i class="bx bx-camera me-1"></i> Foto Selfie (Unggah)</span>
                    @else
                        <span class="badge bg-label-warning rounded-pill px-2 py-1"><i class="bx bx-user-x me-1"></i> Belum Ada Foto</span>
                    @endif
                </div>
                <div class="card-body pt-4">
                    @if($kyc->face_image_path)
                        <div class="ktp-preview-container mb-3" style="max-height: 290px; display: flex; align-items: center; justify-content: center;">
                            <img src="{{ route('media.secure.face', basename($kyc->face_image_path)) }}" class="ktp-preview-img" style="max-height: 260px;" alt="Foto Wajah / Selfie {{ $kyc->user->name ?? 'Warga' }}">
                        </div>
                        <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#faceModal">
                                <i class="bx bx-zoom-in me-1"></i> Perbesar Foto Wajah
                            </button>
                            <a href="{{ route('media.secure.face', basename($kyc->face_image_path)) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-none">
                                <i class="bx bx-link-external me-1"></i> Buka Tab Baru
                            </a>
                        </div>
                    @elseif($kyc->status === 'approved')
                        <div class="text-center py-4 mb-3 bg-light rounded-3 border">
                            <div class="mx-auto mb-2 rounded-circle d-flex align-items-center justify-content-center bg-label-success" style="width: 60px; height: 60px;">
                                <i class="bx bx-shield-check fs-2 text-success"></i>
                            </div>
                            <p class="text-dark small mb-0 fw-bold">Foto Wajah Telah Dihapus</p>
                            <small class="text-muted" style="font-size: 0.75rem;">Dokumen biometrik fisik telah dimusnahkan secara aman dari server.</small>
                        </div>
                    @else
                        <div class="text-center py-4 mb-3 bg-light rounded-3 border">
                            <div class="mx-auto mb-2 rounded-circle d-flex align-items-center justify-content-center bg-white border shadow-xs text-muted" style="width: 60px; height: 60px;">
                                <i class="bx bx-user-x fs-2 text-warning"></i>
                            </div>
                            <p class="text-dark small mb-0 fw-semibold">Foto Wajah / Selfie Tidak Dilampirkan</p>
                            <small class="text-muted" style="font-size: 0.75rem;">(Kendala kamera perangkat / pengajuan hanya melampirkan e-KTP)</small>
                        </div>
                    @endif

                    @if($kyc->status === 'approved')
                        <div class="p-3 rounded-3 bg-label-success border border-success-subtle d-flex align-items-start gap-3">
                            <i class="bx bx-shield-quarter text-success fs-3 flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="mb-1 fw-bold text-success" style="font-size: 0.9rem;">Status: Terverifikasi & Dilindungi</h6>
                                <p class="mb-0 text-muted small" style="line-height: 1.45;">Identitas warga telah terverifikasi resmi. Seluruh data kependudukan tersimpan terenkripsi dengan algoritma ChaCha20-Poly1305 dan blind indexing SHA-256.</p>
                            </div>
                        </div>
                    @elseif($kyc->face_scan_data && count($kyc->face_scan_data) > 0)
                        <div class="p-3 rounded-3 bg-label-success border border-success-subtle d-flex align-items-start gap-3">
                            <i class="bx bx-check-shield text-success fs-3 flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="mb-1 fw-bold text-success" style="font-size: 0.9rem;">Liveness Detection Valid (Scan AI)</h6>
                                <p class="mb-0 text-muted small" style="line-height: 1.45;">Sistem otomatis memvalidasi keaslian wajah, kedipan mata, dan gerakan anti-spoofing via kamera langsung ({{ count($kyc->face_scan_data) }} frame biometrik terekam).</p>
                            </div>
                        </div>
                    @elseif($kyc->face_image_path)
                        <div class="p-3 rounded-3 bg-label-primary border border-primary-subtle d-flex align-items-start gap-3">
                            <i class="bx bx-camera text-primary fs-3 flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="mb-1 fw-bold text-primary" style="font-size: 0.9rem;">Metode: Unggah Foto Wajah / Selfie</h6>
                                <p class="mb-0 text-muted small" style="line-height: 1.45;">Warga melampirkan foto wajah / selfie langsung dari perangkat. Silakan cocokkan kesesuaian wajah pada foto selfie ini dengan foto pada e-KTP di atas.</p>
                            </div>
                        </div>
                    @else
                        <div class="p-3 rounded-3 bg-label-warning border border-warning-subtle d-flex align-items-start gap-3">
                            <i class="bx bx-info-circle text-warning fs-3 flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="mb-1 fw-bold text-warning" style="font-size: 0.9rem;">Foto Wajah Tidak Tersedia</h6>
                                <p class="mb-0 text-muted small" style="line-height: 1.45;">Pengajuan tidak menyertakan foto wajah atau rekaman biometrik. Peninjauan dilakukan manual melalui pencocokan berkas dokumen e-KTP.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card Ringkasan Akun Warga -->
            <div class="card kyc-card">
                <div class="card-header py-3 border-bottom bg-light">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                        <i class="bx bx-user-circle text-primary fs-4 me-2"></i> Profil Pemohon
                    </h6>
                </div>
                <div class="card-body pt-3 pb-3">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                            <span class="text-muted small">Nama Pengguna</span>
                            <span class="fw-semibold text-dark text-end">{{ $kyc->user->name ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                            <span class="text-muted small">Email Akun</span>
                            <span class="fw-medium text-dark text-end font-monospace small">{{ $kyc->user->email ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                            <span class="text-muted small">Nomor Telepon</span>
                            <span class="fw-medium text-dark text-end font-monospace small">{{ $kyc->user->phone ?? '-' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted small">Wilayah Terdaftar</span>
                            <span class="fw-semibold text-dark text-end">{{ $kyc->user->region->name ?? '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Pencocokan Data Ekstraksi OCR vs Akun -->
        <div class="col-12 col-lg-7">
            <div class="card kyc-card">
                <div class="card-header d-flex align-items-center justify-content-between py-3 border-bottom bg-light">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-git-compare text-primary fs-4 me-2"></i>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Pencocokan Data Kependudukan</h6>
                            <small class="text-muted">Bandingkan data akun saat ini dengan hasil pembacaan OCR e-KTP</small>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-4">
                    @php
                        // Helper sensor NIK
                        $sensorNik = function($nik) {
                            if (!$nik || strlen($nik) < 8) return $nik ?: '-';
                            return substr($nik, 0, 4) . '********' . substr($nik, -4);
                        };

                        $userNik = $kyc->user->nik ?? null;
                        $ocrNik = $kyc->nik_from_ocr ?? null;
                        $isNikMatch = ($userNik && $ocrNik && $userNik === $ocrNik);

                        $userName = trim(strtolower($kyc->user->name ?? ''));
                        $ocrName = trim(strtolower($kyc->name_from_ocr ?? ''));
                        $isNameMatch = ($userName && $ocrName && ($userName === $ocrName || str_contains($ocrName, $userName) || str_contains($userName, $ocrName)));

                        $userGender = strtolower($kyc->user->gender ?? '');
                        $ocrGender = strtolower($kyc->gender_from_ocr ?? '');
                        $isGenderMatch = ($userGender && $ocrGender && ($userGender === $ocrGender || (str_starts_with($userGender, 'l') && str_starts_with($ocrGender, 'l')) || (str_starts_with($userGender, 'p') && str_starts_with($ocrGender, 'p'))));

                        $userKec = ($kyc->user->region && $kyc->user->region->parent) ? $kyc->user->region->parent->name : '-';
                    @endphp

                    <!-- 1. Field NIK -->
                    <div class="compare-item">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-dark d-flex align-items-center" style="font-size: 0.88rem;">
                                <i class="bx bx-card text-primary me-2 fs-5"></i> Nomor Induk Kependudukan (NIK)
                            </span>
                            @if($isNikMatch)
                                <span class="badge bg-label-success rounded-pill px-2 py-1"><i class="bx bx-check me-1"></i> Cocok</span>
                            @elseif($ocrNik)
                                <span class="badge bg-label-info rounded-pill px-2 py-1">Hasil Scan OCR</span>
                            @else
                                <span class="badge bg-label-secondary rounded-pill px-2 py-1">Belum Terdata</span>
                            @endif
                        </div>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <div class="data-box data-box-user">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Data Akun Awal</small>
                                    <span class="fw-semibold text-dark font-monospace" id="user-nik-val" style="font-size: 0.88rem;">{{ $sensorNik($userNik) }}</span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="data-box data-box-ocr">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <small class="text-muted" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Data e-KTP (OCR)</small>
                                        @if($ocrNik)
                                            <button type="button" class="btn btn-xs p-0 text-primary border-0 bg-transparent shadow-none" onclick="toggleNikSensor()" title="Tampilkan / Sembunyikan Sensor NIK" style="font-size: 0.75rem; text-decoration: none;">
                                                <i class="bx bx-show me-1" id="nik-toggle-icon"></i><span id="nik-toggle-text">Buka Sensor</span>
                                            </button>
                                        @endif
                                    </div>
                                    <span class="fw-bold text-dark font-monospace" id="ocr-nik-val" style="font-size: 0.88rem;">{{ $sensorNik($ocrNik) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Field Nama Lengkap -->
                    <div class="compare-item">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-dark d-flex align-items-center" style="font-size: 0.88rem;">
                                <i class="bx bx-user text-primary me-2 fs-5"></i> Nama Lengkap
                            </span>
                            @if($isNameMatch)
                                <span class="badge bg-label-success rounded-pill px-2 py-1"><i class="bx bx-check me-1"></i> Sesuai</span>
                            @elseif($ocrName)
                                <span class="badge bg-label-warning rounded-pill px-2 py-1"><i class="bx bx-error-circle me-1"></i> Periksa Ulang</span>
                            @endif
                        </div>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <div class="data-box data-box-user">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Data Akun Awal</small>
                                    <span class="fw-semibold text-dark" style="font-size: 0.88rem;">{{ $kyc->user->name ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="data-box data-box-ocr">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Data e-KTP (OCR)</small>
                                    <span class="fw-bold text-dark text-uppercase" style="font-size: 0.88rem;">{{ $kyc->name_from_ocr ?? 'Tidak terdeteksi' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Field Jenis Kelamin -->
                    <div class="compare-item">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-dark d-flex align-items-center" style="font-size: 0.88rem;">
                                <i class="bx bx-male-female text-primary me-2 fs-5"></i> Jenis Kelamin
                            </span>
                            @if($isGenderMatch)
                                <span class="badge bg-label-success rounded-pill px-2 py-1"><i class="bx bx-check me-1"></i> Sesuai</span>
                            @endif
                        </div>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <div class="data-box data-box-user">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Data Akun Awal</small>
                                    <span class="fw-semibold text-dark" style="font-size: 0.88rem;">{{ ucfirst($kyc->user->gender ?? '-') }}</span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="data-box data-box-ocr">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Data e-KTP (OCR)</small>
                                    <span class="fw-bold text-dark" style="font-size: 0.88rem;">{{ ucfirst($kyc->gender_from_ocr ?? 'Tidak terdeteksi') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Field Alamat -->
                    <div class="compare-item">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-dark d-flex align-items-center" style="font-size: 0.88rem;">
                                <i class="bx bx-home text-primary me-2 fs-5"></i> Alamat Domisili
                            </span>
                        </div>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <div class="data-box data-box-user h-100">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Data Akun Awal</small>
                                    <span class="fw-semibold text-dark" style="font-size: 0.88rem;">{{ $kyc->user->address ?? 'Belum diisi' }}</span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="data-box data-box-ocr h-100">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Data e-KTP (OCR)</small>
                                    <span class="fw-bold text-dark d-block" style="font-size: 0.88rem;">{{ $kyc->address_from_ocr ?? 'Tidak terdeteksi' }}</span>
                                    @if($kyc->rt_from_ocr || $kyc->rw_from_ocr)
                                        <div class="mt-2">
                                            <span class="badge bg-label-secondary rounded-pill px-2 py-1" style="font-size: 0.72rem;">RT {{ $kyc->rt_from_ocr ?? '-' }} / RW {{ $kyc->rw_from_ocr ?? '-' }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Field Desa & Kecamatan -->
                    <div class="compare-item">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-dark d-flex align-items-center" style="font-size: 0.88rem;">
                                <i class="bx bx-map-pin text-primary me-2 fs-5"></i> Wilayah Administrasi (Desa & Kecamatan)
                            </span>
                        </div>
                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <div class="data-box data-box-user h-100">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Data Akun Awal</small>
                                    <div class="fw-semibold text-dark" style="font-size: 0.88rem;">Desa: {{ $kyc->user->region->name ?? '-' }}</div>
                                    <small class="text-muted">Kecamatan: {{ $userKec }}</small>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="data-box data-box-ocr h-100">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Data e-KTP (OCR)</small>
                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">Desa: {{ $kyc->desa_from_ocr ?? '-' }}</div>
                                    <small class="text-muted">Kecamatan: {{ $kyc->kecamatan_from_ocr ?? '-' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tindakan Persetujuan / Riwayat Keputusan -->
                    <div class="mt-4 pt-3 border-top">
                        @if($kyc->status === 'pending')
                            <div class="p-3 rounded-3 bg-light border mb-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bx bx-info-circle text-primary fs-4 flex-shrink-0"></i>
                                    <span class="small text-muted" style="line-height: 1.5;">
                                        Menyetujui verifikasi ini akan otomatis memperbarui NIK, Nama Lengkap, dan status verifikasi akun warga menjadi <strong class="text-success">Terverifikasi (Verified)</strong>.
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <form action="{{ route('admin.kyc.approve', $kyc->id) }}" method="POST" id="form-approve" class="m-0">
                                    @csrf
                                    <input type="hidden" name="admin_notes" id="approve_notes" value="Data Sesuai dengan e-KTP">
                                    <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold d-inline-flex align-items-center" onclick="confirmApprove()">
                                        <i class="bx bx-check-double me-1 fs-5"></i> Setujui & Perbarui Profil
                                    </button>
                                </form>

                                <button type="button" class="btn btn-outline-danger rounded-pill px-4 fw-semibold d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="bx bx-x me-1 fs-5"></i> Tolak Pengajuan
                                </button>
                            </div>
                        @else
                            <div class="p-3 rounded-3 bg-light border">
                                <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                                        <i class="bx bx-history me-1 text-primary"></i> Keputusan Pemeriksaan
                                    </h6>
                                    @if($kyc->status === 'approved')
                                        <span class="badge bg-success rounded-pill px-3 py-1 fw-semibold">Disetujui</span>
                                    @else
                                        <span class="badge bg-danger rounded-pill px-3 py-1 fw-semibold">Ditolak</span>
                                    @endif
                                </div>
                                <p class="text-body mb-2" style="font-size: 0.88rem;"><strong>Catatan Admin:</strong> {{ $kyc->admin_notes ?? 'Tidak ada catatan khusus.' }}</p>
                                <small class="text-muted d-block" style="font-size: 0.78rem;">
                                    Ditinjau oleh: <strong class="text-dark">{{ $kyc->reviewer->name ?? 'Admin' }}</strong> pada {{ $kyc->reviewed_at ? $kyc->reviewed_at->format('d M Y, H:i') . ' WIB' : '-' }}
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Modal Perbesar Foto KTP -->
@if($kyc->ktp_image_path)
<div class="modal fade" id="ktpModal" tabindex="-1" aria-labelledby="ktpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-light">
                <h5 class="modal-title fw-bold text-dark" id="ktpModalLabel">
                    <i class="bx bx-id-card text-primary me-2"></i> Foto e-KTP Pemohon
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center bg-dark">
                <img src="{{ route('media.secure.ktp', basename($kyc->ktp_image_path)) }}" class="img-fluid rounded-3" alt="Foto KTP" style="max-height: 75vh; width: auto; object-fit: contain;">
            </div>
            <div class="modal-footer border-top py-2 px-4 bg-light d-flex justify-content-between">
                <small class="text-muted">Dokumen dilindungi dengan watermark keamanan sistem.</small>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Modal Perbesar Foto Wajah / Selfie -->
@if($kyc->face_image_path)
<div class="modal fade" id="faceModal" tabindex="-1" aria-labelledby="faceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 px-4 bg-light">
                <h5 class="modal-title fw-bold text-dark" id="faceModalLabel">
                    <i class="bx bx-face text-primary me-2"></i> Foto Wajah / Selfie Pemohon
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center bg-dark">
                <img src="{{ route('media.secure.face', basename($kyc->face_image_path)) }}" class="img-fluid rounded-3" alt="Foto Wajah" style="max-height: 75vh; width: auto; object-fit: contain;">
            </div>
            <div class="modal-footer border-top py-2 px-4 bg-light d-flex justify-content-between">
                <small class="text-muted">Dokumen biometrik tersimpan terenkripsi secara aman.</small>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Modal Tolak Pengajuan -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form action="{{ route('admin.kyc.reject', $kyc->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold text-danger mb-0" id="rejectModalLabel">
                        <i class="bx bx-x-circle me-1"></i> Tolak Verifikasi Identitas
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning d-flex align-items-center p-3 rounded-3 mb-3">
                        <i class="bx bx-error-circle fs-4 me-2 text-warning flex-shrink-0"></i>
                        <span class="small text-dark" style="line-height: 1.45;">Pengguna akan menerima pemberitahuan penolakan beserta alasan yang Anda cantumkan di bawah ini.</span>
                    </div>

                    <div class="mb-3">
                        <label for="admin_notes" class="form-label fw-semibold text-dark">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-3" id="admin_notes" name="admin_notes" rows="4" required placeholder="Contoh: Foto e-KTP buram/tidak terbaca, NIK pada e-KTP tidak sesuai, atau wajah tidak cocok dengan foto dokumen."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4 bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 shadow-none" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm fw-bold">Tolak & Kirim Notifikasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function confirmApprove() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Setujui Verifikasi?',
                text: 'Data profil akun warga akan otomatis diselaraskan sesuai dengan hasil pembacaan e-KTP resmi.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#71dd37',
                cancelButtonColor: '#8592a3',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-success rounded-pill px-4 me-2',
                    cancelButton: 'btn btn-outline-secondary rounded-pill px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-approve').submit();
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin menyetujui verifikasi ini? Data profil akun warga akan otomatis diperbarui sesuai e-KTP.')) {
                document.getElementById('form-approve').submit();
            }
        }
    }

    let isNikSensorOpen = false;
    const fullOcrNik = "{{ $ocrNik }}";
    const maskedOcrNik = "{{ $sensorNik($ocrNik) }}";
    const fullUserNik = "{{ $userNik }}";
    const maskedUserNik = "{{ $sensorNik($userNik) }}";

    function toggleNikSensor() {
        isNikSensorOpen = !isNikSensorOpen;
        const ocrEl = document.getElementById('ocr-nik-val');
        const userEl = document.getElementById('user-nik-val');
        const iconEl = document.getElementById('nik-toggle-icon');
        const textEl = document.getElementById('nik-toggle-text');

        if (ocrEl) ocrEl.innerText = isNikSensorOpen ? fullOcrNik : maskedOcrNik;
        if (userEl) userEl.innerText = isNikSensorOpen ? fullUserNik : maskedUserNik;
        if (iconEl) iconEl.className = isNikSensorOpen ? 'bx bx-hide me-1' : 'bx bx-show me-1';
        if (textEl) textEl.innerText = isNikSensorOpen ? 'Tutup Sensor' : 'Buka Sensor';
    }
</script>
@endpush

