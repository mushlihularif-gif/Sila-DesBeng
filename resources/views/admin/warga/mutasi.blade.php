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
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <h4 class="fw-bold m-0">
            <span class="text-muted fw-light">Warga /</span> Mutasi Penduduk
        </h4>
        <div class="w-100 w-md-auto d-flex flex-column flex-sm-row justify-content-end gap-2">
            <button type="button" class="btn btn-warning text-dark w-100" data-bs-toggle="modal" data-bs-target="#dorongWargaModal">
                <i class='bx bx-export me-1'></i> Mutasi Keluar
            </button>
            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#tarikWargaModal">
                <i class='bx bx-user-plus me-1'></i> Tarik Data Warga
            </button>
        </div>
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
                                    @elseif($p->requested_by == 'admin_asal')
                                    <span class="badge bg-label-warning">Anda (Ekspor)</span>
                                    @else
                                    <span class="badge bg-label-primary">Desa Tujuan</span>
                                    @endif
                                </td>
                                <td style="max-width:200px; white-space:pre-wrap;">
                                    {{ $p->reason }}
                                    @if($p->ktp_image_path)
                                    <div class="mt-2">
                                        <a href="{{ route('admin.warga.mutasi.ktp', $p->id) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="bx bx-id-card"></i> Lihat KTP</a>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        @if($p->requested_by != 'admin_asal')
                                        <form action="{{ route('admin.warga.mutasi.approve', $p->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Anda yakin melepaskan warga ini? NIK akan dipindah ke desa tujuan.')">
                                                Lepaskan
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $p->id }}">Tahan</button>
                                        @else
                                        <span class="badge bg-label-warning"><i class='bx bx-time'></i> Menunggu Desa Tujuan</span>
                                        @endif
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
                    <i class='bx bx-time'></i> Ini adalah daftar pengajuan mutasi MASUK ke desa Anda. Termasuk warga yang Anda tarik atau warga yang diekspor oleh desa asalnya (menunggu Handshake persetujuan).
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
                                    @elseif($p->requested_by == 'admin_asal')
                                    <span class="badge bg-label-warning">Desa Asal</span>
                                    @else
                                    <span class="badge bg-label-primary">Anda (Ditarik)</span>
                                    @endif
                                </td>
                                <td style="max-width:200px; white-space:pre-wrap;">
                                    {{ $p->reason }}
                                    @if($p->ktp_image_path)
                                    <div class="mt-2">
                                        <a href="{{ route('admin.warga.mutasi.ktp', $p->id) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="bx bx-id-card"></i> Lihat KTP</a>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    @if($p->requested_by == 'admin_asal')
                                        <div class="d-flex gap-2">
                                            <form action="{{ route('admin.warga.mutasi.approve', $p->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Anda yakin menerima warga ini?')">
                                                    Terima Warga
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModalMasuk{{ $p->id }}">Tolak</button>
                                        </div>
                                        @push('modals')
                                        <div class="modal fade" id="rejectModalMasuk{{ $p->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Tolak Masuk: {{ $p->user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.warga.mutasi.reject', $p->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <label class="form-label">Alasan Penolakan</label>
                                                            <input type="text" name="rejection_reason" class="form-control" required placeholder="Contoh: Warga tidak melapor ke aparat RT/RW setempat">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-danger">Tolak Perpindahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endpush
                                    @else
                                        <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
                                        <span class="text-warning fw-bold ms-1">Menunggu Desa Asal</span>
                                    @endif
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
                <h5 class="modal-title">Tarik Data Warga (Mutasi Masuk)</h5>
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
                        <select name="nik" class="form-select select2-global" style="width: 100%;" required><option value="">Ketik nama atau NIK...</option></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Mutasi Masuk</label>
                        <input type="text" name="reason" class="form-control" required placeholder="Contoh: Warga lansia pindah domisili ikut anaknya">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Lacak & Tarik Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Modal Mutasi Keluar -->
<div class="modal fade" id="dorongWargaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mutasi Keluar (Ekspor Warga)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.warga.mutasi.push') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning py-2 mb-4">
                        Fitur ini melempar data warga Anda ke desa lain. Kepala Desa tujuan harus mengonfirmasi (Handshake) untuk menerima warga ini.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cari Warga (Nama/NIK)</label>
                        <select name="user_id" class="form-select select2-local" style="width: 100%;" required>
                            <option value="">Ketik nama atau NIK...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Desa Tujuan</label>
                        <select name="to_region_id" class="form-select select2-region" style="width: 100%;" required>
                            <option value="">Pilih Desa Tujuan...</option>
                            @foreach(\App\Models\Region::where('type', 'desa')->orderBy('name')->get() as $r)
                                <option value="{{ $r->id }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Mutasi Keluar</label>
                        <input type="text" name="reason" class="form-control" required placeholder="Contoh: Warga pindah tugas">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark">Ekspor Warga</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-global').select2({
            dropdownParent: $('#tarikWargaModal'),
            placeholder: 'Ketik nama atau NIK warga...',
            ajax: {
                url: '{{ route("admin.warga.mutasi.search-global") }}',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return { results: data.results };
                },
                cache: true
            },
            minimumInputLength: 3
        });

        $('.select2-local').select2({
            dropdownParent: $('#dorongWargaModal'),
            placeholder: 'Ketik nama atau NIK warga...',
            ajax: {
                url: '{{ route("admin.warga.mutasi.search-local") }}',
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return { results: data.results };
                },
                cache: true
            },
            minimumInputLength: 3
        });
        
        $('.select2-region').select2({
            dropdownParent: $('#dorongWargaModal'),
            placeholder: 'Pilih Desa Tujuan...'
        });
    });
</script>
@endpush
@endpush
@endsection












