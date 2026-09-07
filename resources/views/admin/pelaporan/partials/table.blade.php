@if($laporans->isEmpty())
    <div class="text-center py-5 px-3 mx-3 my-4" style="background: rgba(105, 122, 141, 0.02); border-radius: 12px; border: 2px dashed rgba(105, 122, 141, 0.1);">
        <div class="d-inline-flex align-items-center justify-content-center bg-white shadow-sm rounded-circle mb-3" style="width: 80px; height: 80px;">
            <i class="bx bx-folder-open text-primary" style="font-size: 2.8rem;"></i>
        </div>
        <h5 class="fw-bold text-dark mb-2">Belum Ada Data Laporan</h5>
        <p class="text-muted mb-0 mx-auto" style="max-width: 380px; font-size: 0.9rem; line-height: 1.6;">
            @if(request('search') || request('kategori') || request('status'))
                Tidak ada laporan yang sesuai dengan filter atau kata kunci pencarian Anda. Silakan coba atur ulang filter.
            @else
                Saat ini belum ada keluhan atau pelaporan warga yang masuk ke dalam sistem. Laporan yang diajukan warga akan otomatis muncul di sini.
            @endif
        </p>
        @if(request('search') || request('kategori') || request('status'))
            <button type="button" class="btn btn-label-primary mt-3 rounded-pill px-4 shadow-sm btn-reset-filter">
                <i class="bx bx-reset me-2"></i>Reset Pencarian
            </button>
        @endif
    </div>
@else
    <div class="table-responsive px-3 px-md-4 pb-3">
        <table class="table table-modern table-hover w-100 mb-0">
            <thead class="bg-transparent text-uppercase small text-muted">
                <tr>
                    <th class="border-bottom pb-3">Pelapor</th>
                    <th class="border-bottom pb-3">Kategori</th>
                    <th class="border-bottom pb-3">Lokasi</th>
                    <th class="border-bottom pb-3">Tingkat Penanganan</th>
                    <th class="border-bottom pb-3">Status</th>
                    <th class="border-bottom pb-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporans as $laporan)
                <tr data-status="{{ $laporan->status }}" class="pelaporan-row">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($laporan->user->name ?? 'A', 0, 1) }}</span>
                            </div>
                            <div>
                                <h6 class="mb-0 text-dark fw-semibold">{{ $laporan->nama }}</h6>
                                <small class="text-muted">{{ $laporan->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-label-dark">{{ $laporan->kategori }}</span>
                    </td>
                    <td>
                        <div class="text-truncate" style="max-width: 200px;" title="{{ $laporan->lokasi }}">
                            <i class="bx bx-map text-danger me-1"></i>{{ $laporan->lokasi }}
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-label-secondary text-capitalize">
                            <i class="bx bx-building-house me-1"></i>{{ $laporan->escalation_level }}
                        </span>
                    </td>
                    <td>
                        @if($laporan->status === 'Pending')
                            <span class="badge bg-label-warning">Pending</span>
                        @elseif($laporan->status === 'Proses')
                            <span class="badge bg-label-info">Proses</span>
                        @elseif($laporan->status === 'Dilanjutkan')
                            <span class="badge bg-label-primary">Dilanjutkan</span>
                        @elseif($laporan->status === 'Selesai')
                            <span class="badge bg-label-success">Selesai</span>
                        @elseif($laporan->status === 'Ditolak')
                            <span class="badge bg-label-danger">Ditolak</span>
                        @endif
                        
                        @if($laporan->isOverdue())
                            <i class="bx bx-error text-danger ms-1" title="SLA Terlewat"></i>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('admin.pelaporan.show', $laporan->id) }}" class="btn btn-sm btn-label-primary rounded-pill px-3">
                            <i class="bx bx-show me-1"></i> Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<!-- Pagination -->
@if($laporans->hasPages())
<div class="px-4 py-3 border-top pagination-container">
    {{ $laporans->links() }}
</div>
@endif
