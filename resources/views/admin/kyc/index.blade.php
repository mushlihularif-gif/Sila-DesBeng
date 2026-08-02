@extends('admin.layouts.admin')

@section('title', 'Manajemen Verifikasi Identitas')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Permintaan & Aktivitas /</span> Verifikasi Identitas Warga
    </h4>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Menu Filter Status (Nav Tabs) -->
    <ul class="nav nav-pills flex-column flex-md-row mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $status === 'all' ? 'active' : '' }}" href="{{ route('admin.kyc.index', ['status' => 'all']) }}">
                <i class="bx bx-list-ul me-1"></i> Semua Data
                <span class="badge rounded-pill bg-{{ $status === 'all' ? 'white text-primary' : 'primary' }} ms-1">{{ $counts['all'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" href="{{ route('admin.kyc.index', ['status' => 'pending']) }}">
                <i class="bx bx-time-five me-1"></i> Menunggu Verifikasi
                <span class="badge rounded-pill bg-{{ $status === 'pending' ? 'white text-primary' : 'danger' }} ms-1">{{ $counts['pending'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'approved' ? 'active' : '' }}" href="{{ route('admin.kyc.index', ['status' => 'approved']) }}">
                <i class="bx bx-check-circle me-1"></i> Disetujui
                <span class="badge rounded-pill bg-{{ $status === 'approved' ? 'white text-primary' : 'success' }} ms-1">{{ $counts['approved'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" href="{{ route('admin.kyc.index', ['status' => 'rejected']) }}">
                <i class="bx bx-x-circle me-1"></i> Ditolak
                <span class="badge rounded-pill bg-{{ $status === 'rejected' ? 'white text-primary' : 'secondary' }} ms-1">{{ $counts['rejected'] }}</span>
            </a>
        </li>
    </ul>

    <!-- Tabel Data -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bx bx-id-card me-2"></i> Daftar Pengajuan Verifikasi</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Dokumen KTP</th>
                        <th>Pengguna</th>
                        <th>Waktu Pengajuan</th>
                        <th>Kesesuaian NIK</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse ($verifications as $kyc)
                    <tr>
                        <td>
                            @if($kyc->ktp_image_path)
                                <div style="width: 60px; height: 40px; overflow: hidden; border-radius: 4px; border: 1px solid #d9dee3;">
                                    <img src="{{ route('media.secure.ktp', basename($kyc->ktp_image_path)) }}" alt="KTP" style="width: 100%; height: 100%; object-fit: cover; filter: blur(2px);" title="Dokumen dilindungi">
                                </div>
                            @else
                                <span class="text-muted"><i class="bx bx-image-alt"></i> -</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-start align-items-center">
                                <div class="avatar-wrapper">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ strtoupper(substr($kyc->user->name, 0, 1)) }}</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $kyc->user->name }}</span>
                                    <small class="text-muted">{{ $kyc->user->email ?? $kyc->user->phone }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $kyc->created_at->format('d M Y') }}</div>
                            <small class="text-muted">{{ $kyc->created_at->format('H:i') }} WIB</small>
                        </td>
                        <td>
                            @php
                                $censoredOcr = $kyc->nik_from_ocr ? substr($kyc->nik_from_ocr, 0, 4) . str_repeat('*', max(0, strlen($kyc->nik_from_ocr) - 8)) . substr($kyc->nik_from_ocr, -4) : '-';
                                $censoredAsli = $kyc->user->nik ? substr($kyc->user->nik, 0, 4) . str_repeat('*', max(0, strlen($kyc->user->nik) - 8)) . substr($kyc->user->nik, -4) : 'Belum diset';
                            @endphp
                            <div class="fw-semibold text-primary">{{ $censoredOcr }}</div>
                            <small class="text-muted">Asli: {{ $censoredAsli }}</small>
                        </td>
                        <td>
                            @if($kyc->status === 'pending')
                                <span class="badge bg-label-warning"><i class="bx bx-time me-1"></i> Menunggu</span>
                            @elseif($kyc->status === 'approved')
                                <span class="badge bg-label-success"><i class="bx bx-check me-1"></i> Disetujui</span>
                            @else
                                <span class="badge bg-label-danger"><i class="bx bx-x me-1"></i> Ditolak</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.kyc.show', $kyc->id) }}" class="btn btn-sm btn-info" title="Tinjau Berkas">
                                <i class="bx bx-show me-1"></i> Tinjau
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bx bx-folder-open mb-2" style="font-size: 2rem;"></i><br>
                                Tidak ada data pengajuan verifikasi untuk kategori ini.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($verifications->hasPages())
        <div class="card-footer pb-0">
            {{ $verifications->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
