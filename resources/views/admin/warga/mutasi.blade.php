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

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div class="flex-grow-1">
                <h4 class="fw-bold m-0"><span class="text-muted fw-light">Warga /</span> Mutasi Penduduk</h4>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-md-end flex-shrink-0" style="position: relative; z-index: 10;">
                <button type="button" class="btn btn-warning text-dark shadow-sm text-nowrap fw-semibold" data-bs-toggle="modal" data-bs-target="#dorongWargaModal">
                    <i class='bx bx-export me-1'></i> Mutasi Keluar
                </button>
                <button type="button" class="btn btn-primary shadow-sm text-nowrap fw-semibold" data-bs-toggle="modal" data-bs-target="#tarikWargaModal">
                    <i class='bx bx-user-plus me-1'></i> Tarik Data Warga
                </button>
            </div>
        </div>
    </div>

    <!-- Panduan -->
    <div class="card bg-label-info border-0 shadow-none mb-4" style="border-radius: 12px;">
        <div class="card-body d-flex align-items-center p-4">
            <div class="me-3">
                <div class="bg-info p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                    <i class="bx bx-transfer-alt fs-3"></i>
                </div>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-info">Sistem Mutasi Lintas Wilayah</h5>
                <p class="mb-0 text-info" style="opacity: 0.85; line-height: 1.5;">
                    <span class="fw-bold text-decoration-underline text-danger">PENTING:</span> Ini <b>bukan</b> fitur untuk mengurus surat pindah administrasi secara fisik. Fitur ini khusus digunakan untuk <strong class="text-dark shadow-sm" style="background-color: rgba(255, 255, 255, 0.7); padding: 3px 6px; border-radius: 4px;">memindahkan domisili akun digital warga (Mutasi Akun) agar mereka bisa mengakses layanan SiladesBeng di wilayah barunya jika mereka pindah domisili.</strong><br><br>
                    Pemindahan akun ini menggunakan sistem <b>Persetujuan Dua Arah</b>: Jika Anda <b>Memutasi Keluar</b> warga ke desa lain, maka Kepala Desa tujuan wajib menyetujuinya. Sebaliknya, jika Anda <b>Menarik Data</b> warga dari desa luar, maka desa asalnya harus melepasnya.
                </p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-4" role="alert">
        <i class="bx bx-check-circle fs-4 me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible shadow-sm rounded-4 border-0 d-flex align-items-center mb-4" role="alert">
        <i class="bx bx-error-circle fs-4 me-2"></i>
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
                <div class="table-responsive text-nowrap mt-2">
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
                                            <button type="submit" class="btn btn-sm btn-success" data-konfirmasi="Anda yakin melepaskan warga ini? NIK akan dipindah ke desa tujuan.">
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
                <div class="table-responsive text-nowrap mt-2">
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
                                                <button type="submit" class="btn btn-sm btn-success" data-konfirmasi="Anda yakin menerima warga ini?">
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
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold text-primary"><i class='bx bx-import me-2'></i>Tarik Data Warga (Mutasi Masuk)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.warga.mutasi.tarik') }}" method="POST">
                @csrf
                <div class="modal-body pt-4">
                    <div class="alert bg-label-primary p-3 mb-4 rounded-3 d-flex border-0 shadow-none">
                        <i class='bx bx-info-circle fs-3 me-3 text-primary'></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1 text-primary">Panduan Tarik Warga</h6>
                            <p class="mb-0 text-primary" style="font-size: 0.85rem;">Gunakan pencarian NIK untuk melacak warga dari luar desa. Akun warga tidak akan langsung berpindah sampai Kepala Desa asal <b>menyetujui pelepasannya</b>.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Dari Kecamatan <span class="text-danger">*</span></label>
                            <select id="selectKecamatanTarik" class="form-select select2-modal" style="width: 100%;" required>
                                <option value="">Pilih Kecamatan Asal...</option>
                                @foreach(\App\Models\Region::where('type', 'kecamatan')->orderBy('name')->get() as $k)
                                    <option value="{{ $k->id }}">{{ $k->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Dari Desa <span class="text-danger">*</span></label>
                            <select id="selectDesaTarik" class="form-select select2-modal" style="width: 100%;" required disabled>
                                <option value="">Pilih Kecamatan Terlebih Dahulu</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Pilih Warga (Nama/NIK) <span class="text-danger">*</span></label>
                        <select name="nik" id="selectWargaTarik" class="form-select select2-global" style="width: 100%;" required disabled>
                            <option value="">Pilih Desa Terlebih Dahulu</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Alasan Penarikan (Mutasi Masuk) <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control rounded-3" rows="2" required placeholder="Contoh: Warga lansia yang pindah domisili untuk ikut anaknya"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class='bx bx-check me-1'></i> Ajukan Penarikan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Mutasi Keluar -->
<div class="modal fade" id="dorongWargaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom pb-3">
                <h5 class="modal-title fw-bold text-warning"><i class='bx bx-export me-2'></i>Mutasi Keluar (Ekspor Warga)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.warga.mutasi.push') }}" method="POST">
                @csrf
                <div class="modal-body pt-4">
                    <div class="alert bg-label-warning p-3 mb-4 rounded-3 d-flex border-0 shadow-none">
                        <i class='bx bx-info-circle fs-3 me-3 text-warning'></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1 text-warning">Panduan Ekspor Warga</h6>
                            <p class="mb-0 text-warning" style="font-size: 0.85rem;">Fitur ini digunakan untuk melempar akun warga Anda ke desa lain. Kepala Desa tujuan wajib <b>mengonfirmasi (Handshake)</b> untuk dapat menerima warga ini.</p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Pilih Warga (Nama/NIK) <span class="text-danger">*</span></label>
                        <select name="user_id" class="form-select select2-local" style="width: 100%;" required>
                            <option value="">Ketik nama atau NIK warga Anda...</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Kecamatan Tujuan <span class="text-danger">*</span></label>
                            <select id="selectKecamatan" class="form-select select2-modal" style="width: 100%;" required>
                                <option value="">Pilih Kecamatan...</option>
                                @foreach(\App\Models\Region::where('type', 'kecamatan')->orderBy('name')->get() as $k)
                                    <option value="{{ $k->id }}">{{ $k->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Desa Tujuan <span class="text-danger">*</span></label>
                            <select name="to_region_id" id="selectDesa" class="form-select select2-modal" style="width: 100%;" required disabled>
                                <option value="">Pilih Kecamatan Terlebih Dahulu</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Alasan Mutasi Keluar <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control rounded-3" rows="2" required placeholder="Contoh: Warga tersebut pindah tugas pekerjaan"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top pt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 shadow-sm"><i class='bx bx-export me-1'></i> Ekspor Warga</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 40px;
        padding-left: 14px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Data desa untuk filter kecamatan
        var allDesas = @json(\App\Models\Region::where('type', 'desa')->orderBy('name')->get());
        var currentAdminRegionId = {{ auth()->user()->region_id ?? 0 }};

        // Select2 standard init
        $('.select2-modal').select2({
            dropdownParent: $('#dorongWargaModal')
        });
        
        // Fix dropdown parent for Tarik modal
        $('#selectKecamatanTarik, #selectDesaTarik').select2({
            dropdownParent: $('#tarikWargaModal')
        });

        // Cascading Dropdown Logic (Ekspor)
        $('#selectKecamatan').on('change', function() {
            var kecId = $(this).val();
            var desaSelect = $('#selectDesa');
            
            desaSelect.empty().append('<option value="">Pilih Desa Tujuan...</option>');
            
            if(kecId) {
                var filtered = allDesas.filter(d => d.parent_id == kecId && d.id != currentAdminRegionId);
                filtered.forEach(function(d) {
                    desaSelect.append(new Option(d.name, d.id));
                });
                desaSelect.prop('disabled', false);
            } else {
                desaSelect.empty().append('<option value="">Pilih Kecamatan Terlebih Dahulu</option>');
                desaSelect.prop('disabled', true);
            }
        });

        // Cascading Dropdown Logic (Tarik)
        $('#selectKecamatanTarik').on('change', function() {
            var kecId = $(this).val();
            var desaSelect = $('#selectDesaTarik');
            var wargaSelect = $('#selectWargaTarik');
            
            desaSelect.empty().append('<option value="">Pilih Desa Asal...</option>');
            wargaSelect.empty().append('<option value="">Pilih Desa Terlebih Dahulu</option>').prop('disabled', true);
            
            if(kecId) {
                var filtered = allDesas.filter(d => d.parent_id == kecId && d.id != currentAdminRegionId);
                filtered.forEach(function(d) {
                    desaSelect.append(new Option(d.name, d.id));
                });
                desaSelect.prop('disabled', false);
            } else {
                desaSelect.empty().append('<option value="">Pilih Kecamatan Terlebih Dahulu</option>');
                desaSelect.prop('disabled', true);
            }
        });

        $('#selectDesaTarik').on('change', function() {
            var desaId = $(this).val();
            var wargaSelect = $('#selectWargaTarik');
            wargaSelect.empty().append('<option value="">Ketik nama atau NIK warga...</option>');
            
            if(desaId) {
                wargaSelect.prop('disabled', false);
            } else {
                wargaSelect.empty().append('<option value="">Pilih Desa Terlebih Dahulu</option>');
                wargaSelect.prop('disabled', true);
            }
        });

        // Select2 Global Search (Tarik)
        $('.select2-global').select2({
            dropdownParent: $('#tarikWargaModal'),
            placeholder: 'Ketik nama atau NIK warga yang dicari...',
            ajax: {
                url: '{{ route("admin.warga.mutasi.search-global") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term,
                        region_id: $('#selectDesaTarik').val() // Kirim ID desa yang dipilih ke backend
                    };
                },
                processResults: function (data) {
                    return { results: data.results };
                },
                cache: true
            },
            minimumInputLength: 3
        });

        // Select2 Local Search (Ekspor)
        $('.select2-local').select2({
            dropdownParent: $('#dorongWargaModal'),
            placeholder: 'Ketik nama atau NIK warga Anda...',
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
    });
</script>
@endpush
@endsection














