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
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('admin.kemitraan.document', $app->id) }}" target="_blank" class="btn btn-sm btn-info" title="Lihat Dokumen">
                                        <i class="bx bx-file"></i> Dokumen
                                    </a>
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $app->id }}" title="Setujui">
                                        <i class="bx bx-check"></i> Setujui
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $app->id }}" title="Tolak">
                                        <i class="bx bx-x"></i> Tolak
                                    </button>
                                </div>

                                <!-- Approve Modal -->
                                <div class="modal fade" id="approveModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Setujui Kemitraan: {{ $app->applicant_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.kemitraan.approve', $app->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body text-start">
                                                    <div class="alert alert-warning">
                                                        <i class="bx bx-info-circle me-1"></i> Ini akan otomatis membuatkan akun admin untuk wilayah {{ $app->region_name }}.
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label text-dark">Alasan / Catatan Persetujuan</label>
                                                        <textarea name="reason" class="form-control" rows="3" required placeholder="Contoh: Dokumen lengkap dan valid."></textarea>
                                                        <small class="text-muted">Pesan ini akan dikirimkan ke notifikasi pemohon.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success">Ya, Setujui</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Tolak Kemitraan: {{ $app->applicant_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.kemitraan.reject', $app->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body text-start">
                                                    <p>Apakah Anda yakin ingin menolak permohonan kemitraan ini?</p>
                                                    <div class="mb-3">
                                                        <label class="form-label text-dark">Alasan Penolakan</label>
                                                        <textarea name="reason" class="form-control" rows="3" required placeholder="Contoh: Dokumen SK tidak valid."></textarea>
                                                        <small class="text-muted">Alasan ini akan dikirimkan ke notifikasi pemohon.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger">Ya, Tolak</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
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
