@extends('admin.layouts.admin')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
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

    <div class="nav-align-top mb-4">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-keluar">
                    Menunggu Pelepasan (Keluar)
                    @if($pengajuanKeluar->count() > 0)
                    <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-danger">{{ $pengajuanKeluar->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-masuk">
                    Menunggu Persetujuan Desa Lama (Masuk)
                    @if($pengajuanMasuk->count() > 0)
                    <span class="badge rounded-pill badge-center h-px-20 w-px-20 bg-warning">{{ $pengajuanMasuk->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-riwayat">Riwayat Mutasi</button>
            </li>
        </ul>
        <div class="tab-content">
            <!-- TAB: PENGAJUAN KELUAR -->
            <div class="tab-pane fade show active" id="navs-keluar" role="tabpanel">
                <div class="alert alert-info">
                    <i class='bx bx-info-circle'></i> Ini adalah daftar warga Anda yang meminta pindah ke desa lain, atau warga yang ditarik oleh Kepala Desa lain. Anda memegang "Kunci Gembok" NIK mereka.
                </div>
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
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
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
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
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
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

<!-- Modal Tarik Warga -->
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
                    <div class="alert alert-info py-2">Fitur ini akan melacak NIK di seluruh sistem SilaDesBeng dan meminta "Handshake" persetujuan pelepasan dari Kades lamanya.</div>
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
@endsection
