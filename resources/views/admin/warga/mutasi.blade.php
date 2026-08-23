@extends('admin.layouts.admin')

@section('content')
<style>
    .animate-fade-up {
        animation: fadeUp 0.5s ease-out forwards;
    }
    @keyframes fadeUp {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .filter-btn {
        border-radius: 50rem;
        padding: 0.6rem 1.2rem;
        font-weight: 600;
        transition: all 0.2s;
        border: 1px solid transparent;
        text-decoration: none;
        background: transparent;
    }
    .filter-btn-primary.active {
        background-color: #0d6efd !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25) !important;
    }
    .filter-btn-danger.active {
        background-color: #ff3e1d !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(255, 62, 29, 0.25) !important;
    }
    .filter-btn-success.active {
        background-color: #71dd37 !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(113, 221, 55, 0.25) !important;
    }
    .filter-btn.active .badge { background-color: white !important; }
    .filter-btn-primary.active .badge { color: #0d6efd !important; }
    .filter-btn-danger.active .badge { color: #ff3e1d !important; }
    .filter-btn-success.active .badge { color: #71dd37 !important; }
    .filter-btn:not(.active) { color: #697a8d !important; }
    .filter-btn:not(.active):hover { background-color: rgba(13, 110, 253, 0.08) !important; }

    .table-modern {
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    .table-modern tbody tr {
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        border-radius: 8px;
        transition: all 0.2s;
        background: #fff;
    }
    .table-modern tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .table-modern td {
        border: none;
        padding: 1.2rem 1.5rem;
        vertical-align: middle;
    }
    .table-modern td:first-child { border-radius: 8px 0 0 8px; }
    .table-modern td:last-child { border-radius: 0 8px 8px 0; }
</style>

<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Warga /</span> Mutasi Penduduk (Handshake)
        </h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tarikWargaModal">
            <i class='bx bx-user-plus'></i> Tarik Warga (Lansia/Pindahan)
        </button>
    </div>

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

    <!-- TABS BUTTONS -->
    <ul class="nav nav-pills d-flex flex-wrap gap-2 mb-4" role="tablist" style="border: none;">
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-danger active" role="tab" data-bs-toggle="pill" data-bs-target="#navs-keluar">
                <i class="bx bx-export me-1"></i> Menunggu Pelepasan (Keluar)
                @if($pengajuanKeluar->count() > 0)
                <span class="badge rounded-pill bg-label-danger ms-1">{{ $pengajuanKeluar->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-success" role="tab" data-bs-toggle="pill" data-bs-target="#navs-masuk">
                <i class="bx bx-import me-1"></i> Menunggu Persetujuan (Masuk)
                @if($pengajuanMasuk->count() > 0)
                <span class="badge rounded-pill bg-label-success ms-1">{{ $pengajuanMasuk->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-primary" role="tab" data-bs-toggle="pill" data-bs-target="#navs-riwayat">
                <i class="bx bx-history me-1"></i> Riwayat Mutasi
            </button>
        </li>
    </ul>
    
    <!-- TABS CONTENT -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body bg-light bg-opacity-25 pt-4">
            <div class="tab-content p-0 m-0 border-0 shadow-none bg-transparent">
            <!-- TAB: PENGAJUAN KELUAR -->
            <div class="tab-pane fade show active" id="navs-keluar" role="tabpanel">
                <div class="alert alert-info">
                    <i class='bx bx-info-circle'></i> Ini adalah daftar warga Anda yang meminta pindah ke desa lain, atau warga yang ditarik oleh Kepala Desa lain. Anda memegang "Kunci Gembok" NIK mereka.
                </div>
                <div class="table-responsive text-nowrap mt-3">
                    <table class="table table-modern align-middle w-100">
                        <thead>
                            <tr>
                                <th>Nama & NIK</th>
                                <th>Desa Tujuan</th>
                                <th>Pemohon</th>
                                <th>Alasan</th>
                                <th>Aksi (Handshake)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengajuanKeluar as $p)
                            <tr>
                                <td>
                                    <strong>{{ $p->user->name }}</strong><br>
                                    <span class="text-muted text-sm">NIK: {{ $p->user->nik ?? '-' }}</span>
                                </td>
                                <td>{{ $p->toRegion->kecamatan }} - {{ $p->toRegion->desa }}</td>
                                <td>
                                    @if($p->requested_by == 'user')
                                    <span class="badge bg-label-info">Warga Sendiri</span>
                                    @else
                                    <span class="badge bg-label-primary">Kades Tujuan</span>
                                    @endif
                                </td>
                                <td style="max-width:200px; white-space:pre-wrap;">{{ $p->reason }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('admin.warga.mutasi.approve', $p->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Anda yakin melepaskan warga ini? NIK akan dipindah ke desa tujuan.')">
                                                Lepaskan
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $p->id }}">Tahan</button>
                                    </div>
                                    
                                    <!-- Reject Modal -->
                                    @push('modals')
                                    <div class="modal fade" id="rejectModal{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Tahan Warga: {{ $p->user->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.warga.mutasi.reject', $p->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <label class="form-label">Alasan Penahanan</label>
                                                        <input type="text" name="rejection_reason" class="form-control" required placeholder="Contoh: Belum lunas pinjaman BUMDes">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-danger">Tolak Perpindahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endpush
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada pengajuan keluar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: PENGAJUAN MASUK -->
            <div class="tab-pane fade" id="navs-masuk" role="tabpanel">
                <div class="alert alert-warning">
                    <i class='bx bx-time'></i> Daftar warga yang ingin masuk ke desa Anda namun masih menunggu desa lamanya melepaskan data (menunggu Handshake Kades lama).
                </div>
                <div class="table-responsive text-nowrap mt-3">
                    <table class="table table-modern align-middle w-100">
                        <thead>
                            <tr>
                                <th>Nama & NIK</th>
                                <th>Desa Asal</th>
                                <th>Pemohon</th>
                                <th>Alasan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengajuanMasuk as $p)
                            <tr>
                                <td>
                                    <strong>{{ $p->user->name }}</strong><br>
                                    <span class="text-muted text-sm">NIK: {{ $p->user->nik ?? '-' }}</span>
                                </td>
                                <td>{{ $p->fromRegion->kecamatan }} - {{ $p->fromRegion->desa }}</td>
                                <td>
                                    @if($p->requested_by == 'user')
                                    <span class="badge bg-label-info">Warga Sendiri</span>
                                    @else
                                    <span class="badge bg-label-primary">Anda (Ditarik)</span>
                                    @endif
                                </td>
                                <td style="max-width:200px; white-space:pre-wrap;">{{ $p->reason }}</td>
                                <td>
                                    <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
                                    <span class="text-warning fw-bold ms-1">Menunggu Kades Asal</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada pengajuan masuk yang menunggu.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB: RIWAYAT -->
            <div class="tab-pane fade" id="navs-riwayat" role="tabpanel">
                <div class="table-responsive text-nowrap mt-3">
                    <table class="table table-modern align-middle w-100">
                        <thead>
                            <tr>
                                <th>Tgl</th>
                                <th>Nama & NIK</th>
                                <th>Rute Mutasi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayat as $r)
                            <tr>
                                <td>{{ $r->updated_at->format('d M Y') }}</td>
                                <td><strong>{{ $r->user->name }}</strong> ({{ $r->user->nik }})</td>
                                <td>{{ $r->fromRegion->desa }} <i class='bx bx-right-arrow-alt'></i> {{ $r->toRegion->desa }}</td>
                                <td>
                                    @if($r->status == 'approved')
                                    <span class="badge bg-success">Disetujui</span>
                                    @else
                                    <span class="badge bg-danger">Ditolak</span>
                                    <br><small class="text-muted">{{ $r->rejection_reason }}</small>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada riwayat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $riwayat->links() }}</div>
            </div>
        </div>
    </div>
    </div>
    </div>
</div>

<!-- Modal Tarik Warga -->
@push('modals')
<div class="modal fade" id="tarikWargaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tarik NIK Warga (Untuk Lansia / Pindahan)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.warga.mutasi.tarik') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-4">
                        Fitur ini melacak NIK di sistem SiladesBeng dan meminta "Handshake" pelepasan dari Kades lama.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">NIK Warga</label>
                        <input type="text" name="nik" class="form-control" required placeholder="Masukkan 16 digit NIK">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Penarikan</label>
                        <input type="text" name="reason" class="form-control" required placeholder="Contoh: Warga lansia pindah domisili ikut anaknya">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Lacak & Tarik NIK</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@endsection
