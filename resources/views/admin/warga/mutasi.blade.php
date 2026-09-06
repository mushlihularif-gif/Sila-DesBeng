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
    .nav-pills-scrollable {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 8px;
        gap: 0.5rem;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .nav-pills-scrollable::-webkit-scrollbar {
        display: none;
    }
    .nav-pills-scrollable .nav-item {
        flex-shrink: 0;
    }
    @media (min-width: 992px) {
        .nav-pills-scrollable {
            flex-wrap: wrap;
            overflow-x: visible;
        }
    }

    .filter-btn {
        border-radius: 50rem;
        padding: 0.55rem 1.1rem;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        border: 1px solid #e0e4e8;
        background-color: #ffffff;
        text-decoration: none;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
    }
    .filter-btn-primary.active {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25) !important;
    }
    .filter-btn-danger.active {
        background-color: #ff3e1d !important;
        border-color: #ff3e1d !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(255, 62, 29, 0.25) !important;
    }
    .filter-btn-success.active {
        background-color: #71dd37 !important;
        border-color: #71dd37 !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(113, 221, 55, 0.25) !important;
    }
    .filter-btn-info.active {
        background-color: #03c3ec !important;
        border-color: #03c3ec !important;
        color: white !important;
        box-shadow: 0 4px 10px rgba(3, 195, 236, 0.25) !important;
    }
    .filter-btn.active .badge { background-color: white !important; }
    .filter-btn-primary.active .badge { color: #0d6efd !important; }
    .filter-btn-danger.active .badge { color: #ff3e1d !important; }
    .filter-btn-success.active .badge { color: #71dd37 !important; }
    .filter-btn-info.active .badge { color: #03c3ec !important; }
    .filter-btn:not(.active) {
        color: #566a7f !important;
        background-color: #ffffff !important;
        border-color: #e0e4e8 !important;
    }
    .filter-btn:not(.active):hover {
        background-color: #f8f9fa !important;
        color: #384554 !important;
        border-color: #cbd5e1 !important;
    }

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
        <div class="card-body d-flex align-items-start align-items-md-center p-3 p-md-4">
            <div class="me-3 flex-shrink-0">
                <div class="bg-info p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px;">
                    <i class="bx bx-transfer-alt fs-3"></i>
                </div>
            </div>
            <div>
                <h5 class="fw-bold mb-1 text-info">Sistem Mutasi Lintas Wilayah</h5>
                <p class="mb-0 text-info" style="opacity: 0.85; line-height: 1.5; font-size: 0.88rem;">
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
    <ul class="nav nav-pills nav-pills-scrollable mb-4" role="tablist" style="border: none;">
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-primary active" role="tab" data-bs-toggle="pill" data-bs-target="#navs-semua">
                <i class="bx bx-list-ul me-1"></i> Semua Data
                @if(($counts['semua'] ?? 0) > 0)
                <span class="badge rounded-pill bg-label-primary ms-1">{{ $counts['semua'] }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-danger" role="tab" data-bs-toggle="pill" data-bs-target="#navs-keluar">
                <i class="bx bx-export me-1"></i> Menunggu Pelepasan (Keluar)
                @if(($counts['keluar'] ?? 0) > 0)
                <span class="badge rounded-pill bg-label-danger ms-1">{{ $counts['keluar'] }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-success" role="tab" data-bs-toggle="pill" data-bs-target="#navs-masuk">
                <i class="bx bx-import me-1"></i> Menunggu Persetujuan (Masuk)
                @if(($counts['masuk'] ?? 0) > 0)
                <span class="badge rounded-pill bg-label-success ms-1">{{ $counts['masuk'] }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button type="button" class="nav-link filter-btn filter-btn-info" role="tab" data-bs-toggle="pill" data-bs-target="#navs-riwayat">
                <i class="bx bx-history me-1"></i> Riwayat Mutasi
                @if(($counts['riwayat'] ?? 0) > 0)
                <span class="badge rounded-pill bg-label-info ms-1">{{ $counts['riwayat'] }}</span>
                @endif
            </button>
        </li>
    </ul>
    
    <!-- TABS CONTENT -->
    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body bg-light bg-opacity-25 p-3 p-md-4">
            <div class="tab-content p-0 m-0 border-0 shadow-none bg-transparent">
            <!-- TAB: SEMUA DATA -->
            <div class="tab-pane fade show active" id="navs-semua" role="tabpanel">
                @if($semuaMutasi->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="bg-white p-3 rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-3 border" style="width: 68px; height: 68px;">
                        <i class="bx bx-list-ul fs-1 text-muted"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Belum Ada Data Mutasi</h6>
                    <p class="text-muted small mb-0">Belum ada aktivitas mutasi keluar, masuk, maupun riwayat mutasi warga di wilayah Anda.</p>
                </div>
                @else
                <!-- Desktop Table View (>= 768px) -->
                <div class="table-responsive text-nowrap d-none d-md-block mt-2">
                    <table class="table table-modern align-middle w-100">
                        <thead>
                            <tr>
                                <th>Tgl</th>
                                <th>Nama & NIK</th>
                                <th>Jenis</th>
                                <th>Rute Mutasi</th>
                                <th>Status</th>
                                <th>Detail / Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($semuaMutasi as $m)
                            @php
                                $currentAdminRegId = auth()->user()->region_id ?? 0;
                                $isKeluar = ($m->from_region_id == $currentAdminRegId);
                            @endphp
                            <tr>
                                <td>{{ $m->created_at ? $m->created_at->format('d M Y') : '-' }}</td>
                                <td>
                                    <strong>{{ $m->user->name ?? 'Warga #' . $m->user_id }}</strong><br>
                                    <span class="text-muted text-sm">NIK: {{ $m->user->nik ?? '-' }}</span>
                                </td>
                                <td>
                                    @if($isKeluar)
                                    <span class="badge bg-label-warning"><i class='bx bx-export me-1'></i>Mutasi Keluar</span>
                                    @else
                                    <span class="badge bg-label-primary"><i class='bx bx-import me-1'></i>Mutasi Masuk</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $m->fromRegion->name ?? ($m->fromRegion->desa ?? 'Desa Asal') }}</span>
                                    <i class='bx bx-right-arrow-alt mx-1 text-primary'></i>
                                    <span class="fw-semibold text-primary">{{ $m->toRegion->name ?? ($m->toRegion->desa ?? 'Desa Tujuan') }}</span>
                                </td>
                                <td>
                                    @if($m->status == 'approved')
                                    <span class="badge bg-success"><i class='bx bx-check me-1'></i>Disetujui</span>
                                    @elseif($m->status == 'rejected')
                                    <span class="badge bg-danger"><i class='bx bx-x me-1'></i>Ditolak</span>
                                    @if($m->rejection_reason)
                                    <br><small class="text-muted">{{ Str::limit($m->rejection_reason, 30) }}</small>
                                    @endif
                                    @else
                                        @if($isKeluar)
                                        <span class="badge bg-warning text-dark"><i class='bx bx-time-five me-1'></i>Menunggu Pelepasan</span>
                                        @else
                                        <span class="badge bg-info"><i class='bx bx-time-five me-1'></i>Menunggu Persetujuan</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($m->ktp_image_path)
                                        <a href="{{ route('admin.warga.mutasi.ktp', $m->id) }}" target="_blank" class="btn btn-xs btn-outline-primary" title="Lihat KTP">
                                            <i class="bx bx-id-card me-1"></i> KTP
                                        </a>
                                        @endif
                                        @if($m->status == 'pending')
                                            @if($isKeluar && $m->requested_by != 'admin_asal')
                                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="$('button[data-bs-target=\'#navs-keluar\']').tab('show')">
                                                Kelola
                                            </button>
                                            @elseif(!$isKeluar && $m->requested_by == 'admin_asal')
                                            <button type="button" class="btn btn-xs btn-outline-success" onclick="$('button[data-bs-target=\'#navs-masuk\']').tab('show')">
                                                Tinjau
                                            </button>
                                            @else
                                            <span class="badge bg-light text-muted border">Menunggu Proses</span>
                                            @endif
                                        @else
                                            <small class="text-muted">{{ $m->updated_at ? $m->updated_at->format('d M Y') : '-' }}</small>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (< 768px) -->
                <div class="d-block d-md-none mt-2">
                    <div class="d-flex flex-column gap-3">
                        @foreach($semuaMutasi as $m)
                        @php
                            $currentAdminRegId = auth()->user()->region_id ?? 0;
                            $isKeluar = ($m->from_region_id == $currentAdminRegId);
                        @endphp
                        <div class="card border shadow-none bg-white rounded-3 p-3" style="border-color: #e7e7e8 !important;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="fw-bold text-dark fs-6 d-block">{{ $m->user->name ?? 'Warga #' . $m->user_id }}</span>
                                    <small class="text-muted">NIK: {{ $m->user->nik ?? '-' }}</small>
                                </div>
                                <div>
                                    @if($m->status == 'approved')
                                    <span class="badge bg-success">Disetujui</span>
                                    @elseif($m->status == 'rejected')
                                    <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        @if($isKeluar)
                                        <span class="badge bg-warning text-dark">Pelepasan</span>
                                        @else
                                        <span class="badge bg-info">Persetujuan</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="bg-light p-2 rounded-2 mb-2" style="font-size: 0.82rem;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Jenis:</span>
                                    <span>
                                        @if($isKeluar)
                                        <span class="badge bg-label-warning py-1 px-2">Mutasi Keluar</span>
                                        @else
                                        <span class="badge bg-label-primary py-1 px-2">Mutasi Masuk</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Tanggal:</span>
                                    <span class="fw-semibold text-dark">{{ $m->created_at ? $m->created_at->format('d M Y') : '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Rute:</span>
                                    <span class="fw-semibold text-primary text-end">
                                        {{ $m->fromRegion->name ?? ($m->fromRegion->desa ?? 'Asal') }} 
                                        <i class='bx bx-right-arrow-alt'></i> 
                                        {{ $m->toRegion->name ?? ($m->toRegion->desa ?? 'Tujuan') }}
                                    </span>
                                </div>
                                @if($m->reason)
                                <div class="pt-1 border-top mt-1">
                                    <span class="text-muted small d-block">Alasan:</span>
                                    <span class="text-dark small">{{ $m->reason }}</span>
                                </div>
                                @endif
                                @if($m->status == 'rejected' && $m->rejection_reason)
                                <div class="pt-1 border-top mt-1">
                                    <span class="text-danger small fw-semibold">Alasan Ditolak:</span>
                                    <span class="text-muted small d-block">{{ $m->rejection_reason }}</span>
                                </div>
                                @endif
                                @if($m->ktp_image_path)
                                <div class="mt-2 pt-1 border-top">
                                    <a href="{{ route('admin.warga.mutasi.ktp', $m->id) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="bx bx-id-card me-1"></i> Lihat KTP</a>
                                </div>
                                @endif
                            </div>
                            @if($m->status == 'pending')
                            <div class="mt-2">
                                @if($isKeluar && $m->requested_by != 'admin_asal')
                                <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="$('button[data-bs-target=\'#navs-keluar\']').tab('show')">
                                    <i class='bx bx-export me-1'></i> Kelola di Tab Pelepasan
                                </button>
                                @elseif(!$isKeluar && $m->requested_by == 'admin_asal')
                                <button type="button" class="btn btn-sm btn-outline-success w-100" onclick="$('button[data-bs-target=\'#navs-masuk\']').tab('show')">
                                    <i class='bx bx-import me-1'></i> Tinjau di Tab Persetujuan
                                </button>
                                @endif
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-3">{{ $semuaMutasi->links() }}</div>
                @endif
            </div>

            <!-- TAB: PENGAJUAN KELUAR -->
            <div class="tab-pane fade" id="navs-keluar" role="tabpanel">
                @if($pengajuanKeluar->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="bg-white p-3 rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-3 border" style="width: 68px; height: 68px;">
                        <i class="bx bx-export fs-1 text-muted"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Tidak Ada Pengajuan Keluar</h6>
                    <p class="text-muted small mb-0">Belum ada data warga yang sedang menunggu pelepasan mutasi keluar.</p>
                </div>
                @else
                <!-- Desktop Table View (>= 768px) -->
                <div class="table-responsive text-nowrap d-none d-md-block mt-2">
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
                            @foreach($pengajuanKeluar as $p)
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
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (< 768px) -->
                <div class="d-block d-md-none mt-2">
                    <div class="d-flex flex-column gap-3">
                        @foreach($pengajuanKeluar as $p)
                        <div class="card border shadow-none bg-white rounded-3 p-3" style="border-color: #e7e7e8 !important;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="fw-bold text-dark fs-6 d-block">{{ $p->user->name }}</span>
                                    <small class="text-muted">NIK: {{ $p->user->nik ?? '-' }}</small>
                                </div>
                                <div>
                                    @if($p->requested_by == 'user')
                                    <span class="badge bg-label-info">Warga Sendiri</span>
                                    @elseif($p->requested_by == 'admin_asal')
                                    <span class="badge bg-label-warning">Anda (Ekspor)</span>
                                    @else
                                    <span class="badge bg-label-primary">Desa Tujuan</span>
                                    @endif
                                </div>
                            </div>
                            <div class="bg-light p-2 rounded-2 mb-3" style="font-size: 0.82rem;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Desa Tujuan:</span>
                                    <span class="fw-semibold text-dark">{{ $p->toRegion->kecamatan }} - {{ $p->toRegion->desa }}</span>
                                </div>
                                @if($p->reason)
                                <div class="pt-1 border-top mt-1">
                                    <span class="text-muted d-block mb-1">Alasan:</span>
                                    <span class="text-dark">{{ $p->reason }}</span>
                                </div>
                                @endif
                                @if($p->ktp_image_path)
                                <div class="mt-2">
                                    <a href="{{ route('admin.warga.mutasi.ktp', $p->id) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="bx bx-id-card me-1"></i> Lihat KTP</a>
                                </div>
                                @endif
                            </div>
                            <div>
                                @if($p->requested_by != 'admin_asal')
                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.warga.mutasi.approve', $p->id) }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Anda yakin melepaskan warga ini? NIK akan dipindah ke desa tujuan.')">
                                            Lepaskan
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger px-3" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $p->id }}">Tahan</button>
                                </div>
                                @else
                                <span class="badge bg-label-warning w-100 py-2 text-center d-block"><i class='bx bx-time me-1'></i> Menunggu Desa Tujuan</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- TAB: PENGAJUAN MASUK -->
            <div class="tab-pane fade" id="navs-masuk" role="tabpanel">
                @if($pengajuanMasuk->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="bg-white p-3 rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-3 border" style="width: 68px; height: 68px;">
                        <i class="bx bx-import fs-1 text-muted"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Tidak Ada Pengajuan Masuk</h6>
                    <p class="text-muted small mb-0">Tidak ada pengajuan mutasi masuk yang sedang menunggu persetujuan.</p>
                </div>
                @else
                <!-- Desktop Table View (>= 768px) -->
                <div class="table-responsive text-nowrap d-none d-md-block mt-2">
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
                            @foreach($pengajuanMasuk as $p)
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
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (< 768px) -->
                <div class="d-block d-md-none mt-2">
                    <div class="d-flex flex-column gap-3">
                        @foreach($pengajuanMasuk as $p)
                        <div class="card border shadow-none bg-white rounded-3 p-3" style="border-color: #e7e7e8 !important;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="fw-bold text-dark fs-6 d-block">{{ $p->user->name }}</span>
                                    <small class="text-muted">NIK: {{ $p->user->nik ?? '-' }}</small>
                                </div>
                                <div>
                                    @if($p->requested_by == 'user')
                                    <span class="badge bg-label-info">Warga Sendiri</span>
                                    @elseif($p->requested_by == 'admin_asal')
                                    <span class="badge bg-label-warning">Desa Asal</span>
                                    @else
                                    <span class="badge bg-label-primary">Anda (Ditarik)</span>
                                    @endif
                                </div>
                            </div>
                            <div class="bg-light p-2 rounded-2 mb-3" style="font-size: 0.82rem;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted">Desa Asal:</span>
                                    <span class="fw-semibold text-dark">{{ $p->fromRegion->kecamatan }} - {{ $p->fromRegion->desa }}</span>
                                </div>
                                @if($p->reason)
                                <div class="pt-1 border-top mt-1">
                                    <span class="text-muted d-block mb-1">Alasan:</span>
                                    <span class="text-dark">{{ $p->reason }}</span>
                                </div>
                                @endif
                                @if($p->ktp_image_path)
                                <div class="mt-2">
                                    <a href="{{ route('admin.warga.mutasi.ktp', $p->id) }}" target="_blank" class="btn btn-xs btn-outline-primary"><i class="bx bx-id-card me-1"></i> Lihat KTP</a>
                                </div>
                                @endif
                            </div>
                            <div>
                                @if($p->requested_by == 'admin_asal')
                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.warga.mutasi.approve', $p->id) }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Anda yakin menerima warga ini?')">
                                            Terima Warga
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-danger px-3" data-bs-toggle="modal" data-bs-target="#rejectModalMasuk{{ $p->id }}">Tolak</button>
                                </div>
                                @else
                                <div class="text-center p-2 rounded bg-label-warning d-flex align-items-center justify-content-center gap-2">
                                    <span class="spinner-border spinner-border-sm text-warning" role="status"></span>
                                    <span class="text-warning fw-bold small">Menunggu Desa Asal</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- TAB: RIWAYAT -->
            <div class="tab-pane fade" id="navs-riwayat" role="tabpanel">
                @if($riwayat->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="bg-white p-3 rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm mb-3 border" style="width: 68px; height: 68px;">
                        <i class="bx bx-history fs-1 text-muted"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Belum Ada Riwayat</h6>
                    <p class="text-muted small mb-0">Belum ada data riwayat mutasi penduduk yang tercatat.</p>
                </div>
                @else
                <!-- Desktop Table View (>= 768px) -->
                <div class="table-responsive text-nowrap d-none d-md-block mt-3">
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
                            @foreach($riwayat as $r)
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
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View (< 768px) -->
                <div class="d-block d-md-none mt-3">
                    <div class="d-flex flex-column gap-3">
                        @foreach($riwayat as $r)
                        <div class="card border shadow-none bg-white rounded-3 p-3" style="border-color: #e7e7e8 !important;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="fw-bold text-dark d-block">{{ $r->user->name }}</span>
                                    <small class="text-muted">NIK: {{ $r->user->nik }}</small>
                                </div>
                                <div>
                                    @if($r->status == 'approved')
                                    <span class="badge bg-success">Disetujui</span>
                                    @else
                                    <span class="badge bg-danger">Ditolak</span>
                                    @endif
                                </div>
                            </div>
                            <div class="bg-light p-2 rounded-2 mb-2" style="font-size: 0.82rem;">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Tanggal:</span>
                                    <span class="fw-semibold text-dark">{{ $r->updated_at->format('d M Y') }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Rute:</span>
                                    <span class="fw-semibold text-primary">{{ $r->fromRegion->desa }} <i class='bx bx-right-arrow-alt'></i> {{ $r->toRegion->desa }}</span>
                                </div>
                                @if($r->status != 'approved' && $r->rejection_reason)
                                <div class="pt-1 border-top mt-1">
                                    <span class="text-danger small fw-semibold">Alasan Penolakan:</span>
                                    <small class="text-muted d-block">{{ $r->rejection_reason }}</small>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-3">{{ $riwayat->links() }}</div>
                @endif
            </div>
        </div>
    </div>
    </div>
</div>

<!-- Modal Tarik Warga -->
@push('modals')
<div class="modal fade" id="tarikWargaModal" aria-hidden="true">
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
                            <p class="mb-0 text-primary" style="font-size: 0.85rem;">Pilih kecamatan dan desa asal warga terlebih dahulu, lalu cari warga berdasarkan nama atau NIK. Akun warga tidak akan langsung berpindah sampai Kepala Desa asal <b>menyetujui pelepasannya</b>.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Dari Kecamatan <span class="text-danger">*</span></label>
                            <select id="selectKecamatanTarik" class="form-select" style="width: 100%;" required>
                                <option value="">Pilih Kecamatan Asal...</option>
                                @foreach(\App\Models\Region::where('type', 'kecamatan')->orderBy('name')->get() as $k)
                                    <option value="{{ $k->id }}">{{ $k->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Dari Desa <span class="text-danger">*</span></label>
                            <select id="selectDesaTarik" class="form-select" style="width: 100%;" required disabled>
                                <option value="">Pilih Kecamatan Terlebih Dahulu</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Pilih Warga (Nama/NIK) <span class="text-danger">*</span></label>
                        <div class="position-relative">
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted border-end-0"><i class='bx bx-search'></i></span>
                                <input type="text" id="inputCariWargaTarik" class="form-control border-start-0 ps-0" placeholder="Ketik nama atau NIK warga..." autocomplete="off">
                                <button class="btn btn-outline-secondary d-none" type="button" id="btnClearWargaTarik" title="Hapus"><i class='bx bx-x'></i></button>
                            </div>
                            <input type="hidden" name="nik" id="selectedNikTarik" required>
                            
                            <!-- Dropdown hasil pencarian live -->
                            <div id="dropdownHasilTarik" class="dropdown-search-results shadow-lg border rounded-3 position-absolute w-100 bg-white d-none mt-1">
                                <div id="listWargaTarik" class="list-group list-group-flush"></div>
                            </div>
                        </div>
                        
                        <!-- Badge Warga Terpilih -->
                        <div id="badgeWargaTarikTerpilih" class="mt-2 d-none">
                            <div class="alert alert-primary py-2 px-3 mb-0 rounded-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class='bx bx-check-circle fs-4 me-2 text-primary'></i>
                                    <div>
                                        <span class="small text-muted d-block" style="font-size: 0.75rem;">Warga Terpilih:</span>
                                        <strong class="text-primary" id="namaWargaTarikTerpilih">-</strong>
                                        <span class="badge bg-label-primary ms-2" id="nikWargaTarikTerpilih">-</span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-semibold" id="btnGantiWargaTarik">Ubah</button>
                            </div>
                        </div>

                        <div class="form-text text-muted small mt-1">
                            <i class='bx bx-info-circle me-1'></i>Ketik nama atau NIK warga, lalu klik pada daftar warga yang muncul.
                        </div>
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
<div class="modal fade" id="dorongWargaModal" aria-hidden="true">
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
                        <div class="position-relative">
                            <div class="input-group">
                                <span class="input-group-text bg-white text-muted border-end-0"><i class='bx bx-search'></i></span>
                                <input type="text" id="inputCariWargaDorong" class="form-control border-start-0 ps-0" placeholder="Ketik nama atau NIK warga desa Anda..." autocomplete="off">
                                <button class="btn btn-outline-secondary d-none" type="button" id="btnClearWargaDorong" title="Hapus"><i class='bx bx-x'></i></button>
                            </div>
                            <input type="hidden" name="user_id" id="selectedUserDorongId" required>
                            
                            <!-- Dropdown hasil pencarian live -->
                            <div id="dropdownHasilDorong" class="dropdown-search-results shadow-lg border rounded-3 position-absolute w-100 bg-white d-none mt-1">
                                <div id="listWargaDorong" class="list-group list-group-flush"></div>
                            </div>
                        </div>
                        
                        <!-- Badge Warga Terpilih -->
                        <div id="badgeWargaDorongTerpilih" class="mt-2 d-none">
                            <div class="alert alert-warning py-2 px-3 mb-0 rounded-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class='bx bx-check-circle fs-4 me-2 text-warning'></i>
                                    <div>
                                        <span class="small text-muted d-block" style="font-size: 0.75rem;">Warga Terpilih:</span>
                                        <strong class="text-dark" id="namaWargaDorongTerpilih">-</strong>
                                        <span class="badge bg-label-dark ms-2" id="nikWargaDorongTerpilih">-</span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-link text-dark p-0 text-decoration-none fw-semibold" id="btnGantiWargaDorong">Ubah</button>
                            </div>
                        </div>

                        <div class="form-text text-muted small mt-1">
                            <i class='bx bx-info-circle me-1'></i>Ketik nama atau NIK untuk mencari warga Anda, lalu klik pada daftar.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Kecamatan Tujuan <span class="text-danger">*</span></label>
                            <select id="selectKecamatan" class="form-select" style="width: 100%;" required>
                                <option value="">Pilih Kecamatan...</option>
                                @foreach(\App\Models\Region::where('type', 'kecamatan')->orderBy('name')->get() as $k)
                                    <option value="{{ $k->id }}">{{ $k->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-semibold">Desa Tujuan <span class="text-danger">*</span></label>
                            <select name="to_region_id" id="selectDesa" class="form-select" style="width: 100%;" required disabled>
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
<style>
    .dropdown-search-results {
        z-index: 1060 !important;
        max-height: 240px;
        overflow-y: auto;
        top: 100%;
        left: 0;
        right: 0;
        border-color: #d9dee3;
    }
    .dropdown-search-results .list-group-item-action {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .dropdown-search-results .list-group-item-action:hover {
        background-color: #f5f5f9 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Data desa untuk filter kecamatan
        var allDesas = @json(\App\Models\Region::where('type', 'desa')->orderBy('name')->get());
        var currentAdminRegionId = {{ auth()->user()->region_id ?? 0 }};

        // Data warga lokal desa admin untuk mutasi keluar
        var wargaDesaData = @json(isset($wargaDesa) ? $wargaDesa->map(function($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'nik' => $u->nik ?: 'Tanpa NIK'
            ];
        })->values() : []);

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // ==========================================
        // 1. LOGIKA WARGA DORONG (MUTASI KELUAR)
        // ==========================================
        function renderWargaDorongList(filterText) {
            var filter = (filterText || '').toLowerCase().trim();
            var list = $('#listWargaDorong');
            list.empty();
            
            var matches = wargaDesaData.filter(function(w) {
                return (w.name && w.name.toLowerCase().indexOf(filter) !== -1) || 
                       (w.nik && w.nik.indexOf(filter) !== -1);
            });
            
            if (matches.length === 0) {
                list.append('<div class="p-3 text-center text-muted small"><i class="bx bx-info-circle me-1"></i>Warga desa tidak ditemukan.</div>');
            } else {
                matches.forEach(function(w) {
                    var item = $('<a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 border-0 border-bottom"></a>');
                    item.html('<div><strong class="d-block text-dark">' + escapeHtml(w.name) + '</strong><small class="text-muted">NIK: ' + escapeHtml(w.nik) + '</small></div><i class="bx bx-plus-circle text-warning fs-5"></i>');
                    item.on('click', function(e) {
                        e.preventDefault();
                        pilihWargaDorong(w);
                    });
                    list.append(item);
                });
            }
            $('#dropdownHasilDorong').removeClass('d-none');
        }

        function pilihWargaDorong(w) {
            $('#selectedUserDorongId').val(w.id);
            $('#inputCariWargaDorong').val(w.name + ' (' + w.nik + ')');
            $('#namaWargaDorongTerpilih').text(w.name);
            $('#nikWargaDorongTerpilih').text('NIK: ' + w.nik);
            $('#badgeWargaDorongTerpilih').removeClass('d-none');
            $('#btnClearWargaDorong').removeClass('d-none');
            $('#dropdownHasilDorong').addClass('d-none');
        }

        function resetWargaDorong() {
            $('#selectedUserDorongId').val('');
            $('#inputCariWargaDorong').val('').focus();
            $('#badgeWargaDorongTerpilih').addClass('d-none');
            $('#btnClearWargaDorong').addClass('d-none');
            renderWargaDorongList('');
        }

        $('#inputCariWargaDorong').on('focus input', function() {
            renderWargaDorongList($(this).val());
        });

        $('#btnClearWargaDorong, #btnGantiWargaDorong').on('click', function() {
            resetWargaDorong();
        });

        // Cascading Dropdown Ekspor (Kecamatan -> Desa)
        $('#selectKecamatan').on('change', function() {
            var kecId = $(this).val();
            var desaSelect = $('#selectDesa');
            
            desaSelect.empty().append('<option value="">Pilih Desa Tujuan...</option>');
            
            if (kecId) {
                var filtered = allDesas.filter(function(d) {
                    return d.parent_id == kecId && d.id != currentAdminRegionId;
                });
                filtered.forEach(function(d) {
                    desaSelect.append(new Option(d.name, d.id));
                });
                desaSelect.prop('disabled', false);
            } else {
                desaSelect.empty().append('<option value="">Pilih Kecamatan Terlebih Dahulu</option>');
                desaSelect.prop('disabled', true);
            }
        });

        $('#dorongWargaModal form').on('submit', function(e) {
            if (!$('#selectedUserDorongId').val()) {
                e.preventDefault();
                alert('Silakan pilih warga desa yang ingin diekspor terlebih dahulu.');
                $('#inputCariWargaDorong').focus();
            }
        });

        // ==========================================
        // 2. LOGIKA WARGA TARIK (MUTASI MASUK)
        // ==========================================
        var searchTimeoutTarik = null;

        function cariWargaTarik(term) {
            var desaId = $('#selectDesaTarik').val() || '';
            var list = $('#listWargaTarik');
            list.html('<div class="p-3 text-center text-muted small"><span class="spinner-border spinner-border-sm me-1"></span> Mencari data warga...</div>');
            $('#dropdownHasilTarik').removeClass('d-none');
            
            $.ajax({
                url: '{{ route("admin.warga.mutasi.search-global") }}',
                data: { q: term || '', region_id: desaId },
                dataType: 'json',
                success: function(data) {
                    list.empty();
                    var results = data.results || [];
                    if (results.length === 0) {
                        list.append('<div class="p-3 text-center text-muted small"><i class="bx bx-info-circle me-1"></i>Warga tidak ditemukan.</div>');
                    } else {
                        results.forEach(function(w) {
                            var item = $('<a href="javascript:void(0)" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 border-0 border-bottom"></a>');
                            var nama = w.name || w.text;
                            var nik = w.nik || w.id;
                            var infoWilayah = '';
                            if (w.desa_name) {
                                infoWilayah = '<small class="text-muted d-block mt-1"><i class="bx bx-map-pin me-1 text-primary"></i>' + escapeHtml(w.desa_name) + (w.kec_name ? ', ' + escapeHtml(w.kec_name) : '') + '</small>';
                            }
                            item.html('<div><strong class="d-block text-dark">' + escapeHtml(nama) + '</strong><small class="text-muted">NIK: ' + escapeHtml(nik) + '</small>' + infoWilayah + '</div><i class="bx bx-plus-circle text-primary fs-5 ms-2 flex-shrink-0"></i>');
                            item.on('click', function(e) {
                                e.preventDefault();
                                pilihWargaTarik(w);
                            });
                            list.append(item);
                        });
                    }
                },
                error: function() {
                    list.html('<div class="p-3 text-center text-danger small"><i class="bx bx-error me-1"></i>Gagal memuat data warga.</div>');
                }
            });
        }

        function pilihWargaTarik(w) {
            var nama = w.name || w.text;
            var nik = w.id || w.nik;
            $('#selectedNikTarik').val(nik);
            $('#inputCariWargaTarik').val(nama + ' (' + nik + ')');
            $('#namaWargaTarikTerpilih').text(nama);
            $('#nikWargaTarikTerpilih').text('NIK: ' + nik);
            $('#badgeWargaTarikTerpilih').removeClass('d-none');
            $('#btnClearWargaTarik').removeClass('d-none');
            $('#dropdownHasilTarik').addClass('d-none');

            // Otomatis sinkronkan pilihan Kecamatan & Desa asal jika tersedia
            if (w.kec_id) {
                $('#selectKecamatanTarik').val(w.kec_id);
                var desaSelect = $('#selectDesaTarik');
                desaSelect.empty().append('<option value="">Pilih Desa Asal...</option>');
                var filtered = allDesas.filter(function(d) {
                    return d.parent_id == w.kec_id && d.id != currentAdminRegionId;
                });
                filtered.forEach(function(d) {
                    desaSelect.append(new Option(d.name, d.id));
                });
                desaSelect.prop('disabled', false);
                if (w.desa_id) {
                    desaSelect.val(w.desa_id);
                }
            }
        }

        function resetWargaTarik() {
            $('#selectedNikTarik').val('');
            $('#inputCariWargaTarik').val('').focus();
            $('#badgeWargaTarikTerpilih').addClass('d-none');
            $('#btnClearWargaTarik').addClass('d-none');
            cariWargaTarik('');
        }

        // Cascading Dropdown Tarik (Kecamatan -> Desa)
        $('#selectKecamatanTarik').on('change', function() {
            var kecId = $(this).val();
            var desaSelect = $('#selectDesaTarik');
            
            desaSelect.empty().append('<option value="">Pilih Desa Asal...</option>');
            
            if (kecId) {
                var filtered = allDesas.filter(function(d) {
                    return d.parent_id == kecId && d.id != currentAdminRegionId;
                });
                filtered.forEach(function(d) {
                    desaSelect.append(new Option(d.name, d.id));
                });
                desaSelect.prop('disabled', false);
            } else {
                desaSelect.empty().append('<option value="">Pilih Kecamatan Terlebih Dahulu</option>');
                desaSelect.prop('disabled', true);
            }

            // Jika dropdown pencarian warga sedang terbuka, perbarui hasil
            if ($('#dropdownHasilTarik').is(':visible') || $('#inputCariWargaTarik').val()) {
                cariWargaTarik($('#inputCariWargaTarik').val());
            }
        });

        $('#selectDesaTarik').on('change', function() {
            // Perbarui pencarian jika input memiliki teks atau dropdown terbuka
            if ($('#dropdownHasilTarik').is(':visible') || $('#inputCariWargaTarik').val()) {
                cariWargaTarik($('#inputCariWargaTarik').val());
            }
        });

        $('#inputCariWargaTarik').on('focus', function() {
            cariWargaTarik($(this).val());
        });

        $('#inputCariWargaTarik').on('input', function() {
            clearTimeout(searchTimeoutTarik);
            var val = $(this).val();
            searchTimeoutTarik = setTimeout(function() {
                cariWargaTarik(val);
            }, 250);
        });

        $('#btnClearWargaTarik, #btnGantiWargaTarik').on('click', function() {
            resetWargaTarik();
        });

        $('#tarikWargaModal form').on('submit', function(e) {
            if (!$('#selectedNikTarik').val()) {
                e.preventDefault();
                alert('Silakan pilih warga yang ingin ditarik terlebih dahulu.');
                $('#inputCariWargaTarik').focus();
            }
        });

        // Fokus otomatis saat modal ditampilkan
        $('#dorongWargaModal').on('shown.bs.modal', function() {
            $('#inputCariWargaDorong').focus();
        });
        $('#tarikWargaModal').on('shown.bs.modal', function() {
            $('#inputCariWargaTarik').focus();
        });

        $('#dorongWargaModal').on('hidden.bs.modal', function() {
            $('#dropdownHasilDorong').addClass('d-none');
        });
        $('#tarikWargaModal').on('hidden.bs.modal', function() {
            $('#dropdownHasilTarik').addClass('d-none');
        });

        // Tutup dropdown jika klik di luar
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#dropdownHasilDorong, #inputCariWargaDorong').length) {
                $('#dropdownHasilDorong').addClass('d-none');
            }
            if (!$(e.target).closest('#dropdownHasilTarik, #inputCariWargaTarik').length) {
                $('#dropdownHasilTarik').addClass('d-none');
            }
        });

        // Tab retention based on URL query param (e.g. pagination)
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('riwayat_page')) {
            var tabBtn = document.querySelector('button[data-bs-target="#navs-riwayat"]');
            if (tabBtn) {
                var tab = new bootstrap.Tab(tabBtn);
                tab.show();
            }
        } else if (urlParams.has('semua_page')) {
            var tabBtn = document.querySelector('button[data-bs-target="#navs-semua"]');
            if (tabBtn) {
                var tab = new bootstrap.Tab(tabBtn);
                tab.show();
            }
        }
    });
</script>
@endpush
@endsection















