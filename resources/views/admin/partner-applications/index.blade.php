@extends('admin.layouts.admin')

@section('title', 'Persetujuan Mitra & Pengurus Wilayah')

@section('content')
<style>
    .partner-stat-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        background: #fff;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .partner-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.08) !important;
    }
    .partner-table th {
        font-size: 0.73rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        font-weight: 700;
        color: #8592a3 !important;
        background-color: #f8fafc;
        border-bottom: 1px solid #eef1f6;
    }
    .partner-table td {
        vertical-align: middle;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #f1f4f9;
    }
    .partner-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .partner-mobile-card {
        border-radius: 16px;
        border: 1px solid #e9edf4;
        background: #ffffff;
        transition: box-shadow 0.18s ease;
    }
</style>

@php
    $totalApps = $applications->count();
    $desaApps = $applications->where('region_type', 'desa')->count();
    $rwRtApps = $applications->whereIn('region_type', ['rw', 'rt'])->count();
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Kependudukan /</span> Persetujuan Mitra Wilayah
            </h4>
            <p class="text-muted small mb-0">Verifikasi berkas SK atau Surat Tugas pendaftar pengurus wilayah sebelum mengaktifkan hak akses.</p>
        </div>
        <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
            @if(auth()->user()->role === 'admin_desa')
                <a href="{{ route('admin.wilayah-admins.index') }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-1 flex-grow-1 flex-md-grow-0">
                    <i class="bx bx-map-pin"></i>
                    <span>Kelola Wilayah &amp; RT/RW</span>
                </a>
            @endif
            <button type="button" class="btn btn-white border rounded-pill px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-1" onclick="location.reload()">
                <i class="bx bx-refresh"></i>
                <span class="d-none d-sm-inline">Refresh</span>
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
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

    <!-- Info Banner & KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card bg-label-primary border-0 shadow-none rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bx bx-badge-check fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Verifikasi Pendaftaran Mitra Pengurus</h6>
                        <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.55;">
                            Periksa dokumen lampiran (SK Pengangkatan / Surat Tugas) dan kesesuaian identitas pemohon.
                            Jika disetujui, akun warga terkait akan otomatis mendapatkan hak akses sesuai tingkat wilayah yang diajukan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="row g-2 h-100">
                <div class="col-4">
                    <div class="partner-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Menunggu</span>
                        <h4 class="fw-bold text-primary mb-0">{{ $totalApps }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="partner-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Tingkat RW/RT</span>
                        <h4 class="fw-bold text-info mb-0">{{ $rwRtApps }}</h4>
                    </div>
                </div>
                <div class="col-4">
                    <div class="partner-stat-card p-3 h-100 d-flex flex-column justify-content-center text-center shadow-sm">
                        <span class="text-muted small fw-semibold" style="font-size: 0.72rem;">Tingkat Desa</span>
                        <h4 class="fw-bold text-success mb-0">{{ $desaApps }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <div class="avatar avatar-sm bg-label-warning rounded-3 d-flex align-items-center justify-content-center">
                    <i class="bx bx-time-five fs-5"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 fs-6 text-dark">Daftar Pengajuan Menunggu Persetujuan</h5>
                    <small class="text-muted">Menampilkan {{ $totalApps }} permohonan kemitraan aktif</small>
                </div>
            </div>
            @if($totalApps > 0)
            <div class="input-group input-group-merge input-group-sm w-100 w-sm-auto" style="min-width: 240px; max-width: 320px;">
                <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" id="searchPartnerInput" class="form-control bg-light border-0" placeholder="Cari pemohon, wilayah, jabatan...">
            </div>
            @endif
        </div>

        @if($applications->isEmpty())
            <div class="card-body text-center py-5 px-3">
                <div class="avatar avatar-lg bg-label-success text-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bx bx-check-shield fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Tidak Ada Pengajuan Kemitraan Baru</h6>
                <p class="text-muted small mb-0">Seluruh permohonan kemitraan pengurus wilayah sudah selesai diverifikasi.</p>
            </div>
        @else
            <!-- Desktop View (>= 768px) -->
            <div class="table-responsive d-none d-md-block">
                <table class="table partner-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>Pemohon &amp; Jabatan</th>
                            <th>Wilayah Tujuan</th>
                            <th>Kontak</th>
                            <th>Alasan &amp; Dokumen</th>
                            <th class="text-end pe-4">Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody id="partnerTableBody">
                        @foreach($applications as $app)
                            @php
                                $searchKey = strtolower(($app->applicant_name ?? '') . ' ' . ($app->position ?? '') . ' ' . ($app->region_name ?? '') . ' ' . ($app->region_type ?? '') . ' ' . ($app->contact_email ?? '') . ' ' . ($app->contact_phone ?? ''));
                                $roleColor = match($app->region_type) {
                                    'desa' => 'primary',
                                    'rw' => 'info',
                                    'rt' => 'success',
                                    default => 'secondary'
                                };
                            @endphp
                            <tr class="partner-item-row" data-search="{{ $searchKey }}">
                                <td class="ps-4 text-nowrap">
                                    <span class="fw-semibold text-dark d-block">{{ $app->created_at->format('d M Y') }}</span>
                                    <small class="text-muted">{{ $app->created_at->format('H:i') }} WIB</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-sm flex-shrink-0">
                                            <span class="avatar-initial rounded-circle bg-label-{{ $roleColor }} fw-bold">
                                                {{ strtoupper(substr($app->applicant_name ?? 'M', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ $app->applicant_name }}</h6>
                                            <span class="badge bg-label-secondary rounded-pill mt-1" style="font-size: 0.7rem;">
                                                {{ $app->position ?: 'Pengurus Wilayah' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center gap-1 mb-1">
                                            <span class="badge bg-label-{{ $roleColor }} rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                                {{ strtoupper($app->region_type) }}
                                            </span>
                                            <span class="fw-bold text-dark">{{ $app->region_name }}</span>
                                        </div>
                                        @if($app->parentRegion)
                                            <small class="text-muted">
                                                <i class="bx bx-subdirectory-right text-primary"></i>
                                                Induk: {{ $app->parentRegion->name }}
                                                @if($app->parentRegion->parent)
                                                    ({{ $app->parentRegion->parent->name }})
                                                @endif
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column small">
                                        <span class="text-dark fw-medium text-truncate" style="max-width: 190px;" title="{{ $app->contact_email }}">
                                            <i class="bx bx-envelope text-primary me-1"></i>{{ $app->contact_email }}
                                        </span>
                                        @if($app->contact_phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $app->contact_phone) }}" target="_blank" class="text-success text-decoration-none fw-medium mt-1">
                                                <i class="bx bxl-whatsapp me-1"></i>{{ $app->contact_phone }}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td style="max-width: 240px;">
                                    @if($app->reason)
                                        <p class="text-muted small mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $app->reason }}">
                                            "{{ $app->reason }}"
                                        </p>
                                    @endif
                                    @if($app->document_path)
                                        <a href="{{ route('admin.kemitraan.document', $app->id) }}" target="_blank" class="btn btn-xs btn-outline-info rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1">
                                            <i class="bx bx-file"></i>
                                            <span>Lihat SK / Berkas</span>
                                        </a>
                                    @else
                                        <span class="badge bg-label-secondary rounded-pill" style="font-size: 0.7rem;">Tanpa Lampiran</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4 text-nowrap">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <form action="{{ route('admin.kemitraan.approve', $app->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Setujui pengajuan {{ $app->applicant_name }} untuk wilayah {{ $app->region_name }}?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
                                                <i class="bx bx-check"></i>
                                                <span>Setujui</span>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $app->id }}">
                                            <i class="bx bx-x"></i>
                                            <span>Tolak</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View (< 768px) -->
            <div class="d-block d-md-none p-3 bg-light bg-opacity-50" id="partnerMobileList">
                <div class="d-flex flex-column gap-3">
                    @foreach($applications as $app)
                        @php
                            $searchKey = strtolower(($app->applicant_name ?? '') . ' ' . ($app->position ?? '') . ' ' . ($app->region_name ?? '') . ' ' . ($app->region_type ?? '') . ' ' . ($app->contact_email ?? '') . ' ' . ($app->contact_phone ?? ''));
                            $roleColor = match($app->region_type) {
                                'desa' => 'primary',
                                'rw' => 'info',
                                'rt' => 'success',
                                default => 'secondary'
                            };
                        @endphp
                        <div class="partner-mobile-card p-3 shadow-sm partner-item-card" data-search="{{ $searchKey }}">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <div class="avatar avatar-sm flex-shrink-0">
                                        <span class="avatar-initial rounded-circle bg-label-{{ $roleColor }} fw-bold">
                                            {{ strtoupper(substr($app->applicant_name ?? 'M', 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="min-w-0">
                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $app->applicant_name }}</h6>
                                        <small class="text-muted d-block">{{ $app->position ?: 'Pengurus Wilayah' }} &bull; {{ $app->created_at->format('d M Y') }}</small>
                                    </div>
                                </div>
                                <span class="badge bg-label-{{ $roleColor }} rounded-pill flex-shrink-0" style="font-size: 0.7rem;">
                                    {{ strtoupper($app->region_type) }}
                                </span>
                            </div>

                            <div class="bg-light rounded-3 p-2.5 mb-2" style="font-size: 0.82rem;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Wilayah:</span>
                                    <span class="fw-bold text-dark text-end">
                                        {{ $app->region_name }}
                                        @if($app->parentRegion)
                                            <span class="text-muted fw-normal">({{ $app->parentRegion->name }})</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Email:</span>
                                    <span class="text-dark text-truncate ms-2" style="max-width: 190px;">{{ $app->contact_email }}</span>
                                </div>
                                @if($app->contact_phone)
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">WhatsApp:</span>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $app->contact_phone) }}" target="_blank" class="text-success fw-semibold text-decoration-none">
                                        <i class="bx bxl-whatsapp me-1"></i>{{ $app->contact_phone }}
                                    </a>
                                </div>
                                @endif
                                @if($app->reason)
                                <div class="pt-1 mt-1 border-top">
                                    <span class="text-muted d-block" style="font-size: 0.75rem;">Alasan Pengajuan:</span>
                                    <span class="text-dark">{{ $app->reason }}</span>
                                </div>
                                @endif
                            </div>

                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
                                <div>
                                    @if($app->document_path)
                                        <a href="{{ route('admin.kemitraan.document', $app->id) }}" target="_blank" class="btn btn-sm btn-outline-info rounded-pill px-3 d-inline-flex align-items-center gap-1">
                                            <i class="bx bx-file"></i>
                                            <span>Lihat SK</span>
                                        </a>
                                    @else
                                        <span class="badge bg-label-secondary rounded-pill">Tanpa Berkas</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center gap-2 ms-auto">
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $app->id }}">
                                        <i class="bx bx-x me-1"></i>Tolak
                                    </button>
                                    <form action="{{ route('admin.kemitraan.approve', $app->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Setujui pengajuan {{ $app->applicant_name }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm">
                                            <i class="bx bx-check me-1"></i>Setujui
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modals Penolakan (Berada di luar tabel agar valid secara HTML & responsif di mobile) -->
@foreach($applications as $app)
    <div class="modal fade" id="rejectModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('admin.kemitraan.reject', $app->id) }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
                @csrf
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2">
                        <i class="bx bx-x-circle fs-4"></i>
                        <span>Tolak Pengajuan Kemitraan</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert bg-label-warning border-0 rounded-3 p-3 mb-3 small text-dark">
                        Anda akan menolak permohonan <strong>{{ $app->applicant_name }}</strong> untuk wilayah <strong>{{ strtoupper($app->region_type) }} - {{ $app->region_name }}</strong>.
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small text-dark">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control rounded-3" rows="3" required placeholder="Jelaskan alasan penolakan (misal: dokumen SK tidak terbaca atau wilayah sudah memiliki pengurus aktif)..."></textarea>
                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">
                            Alasan ini akan dikirimkan ke email/notifikasi pemohon agar dapat diperbaiki.
                        </small>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">
                        <i class="bx bx-x me-1"></i>Kirim Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchPartnerInput');
        if (!searchInput) return;

        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.partner-item-row, .partner-item-card').forEach(el => {
                const hay = (el.getAttribute('data-search') || '').toLowerCase();
                el.style.display = (!q || hay.includes(q)) ? '' : 'none';
            });
        });
    });
</script>
@endpush
@endsection
