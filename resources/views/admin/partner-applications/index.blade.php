@extends('admin.layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Persetujuan Mitra /</span> Daftar Permohonan</h4>

    <!-- Basic Bootstrap Table -->
    <div class="card shadow-sm border-0">
        <h5 class="card-header border-bottom bg-transparent">Permohonan Kemitraan Menunggu Persetujuan</h5>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Nama Pemohon</th>
                        <th>Wilayah (Tipe)</th>
                        <th>Kontak</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($applications as $index => $app)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $app->created_at->format('d M Y H:i') }}</td>
                            <td><strong>{{ $app->applicant_name }}</strong></td>
                            <td>
                                {{ $app->region_name }} 
                                <span class="badge bg-label-info">{{ Str::upper($app->region_type) }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <small><i class="bx bx-envelope me-1"></i>{{ $app->contact_email }}</small>
                                    <small><i class="bx bx-phone me-1"></i>{{ $app->contact_phone }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-warning"><i class="bx bx-time me-1"></i> Menunggu</span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.kemitraan.approve', $app->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui kemitraan ini? Ini akan otomatis membuatkan akun admin untuk wilayah tersebut.');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Setujui">
                                            <i class="bx bx-check"></i> Setujui
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.kemitraan.reject', $app->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak permohonan ini?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Tolak">
                                            <i class="bx bx-x"></i> Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="empty-state-icon mb-3">
                                    <i class="bx bx-check-shield text-success" style="font-size: 4rem;"></i>
                                </div>
                                <h5 class="fw-bold">Belum ada permohonan baru</h5>
                                <p class="text-muted">Semua permohonan kemitraan sudah diproses.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
