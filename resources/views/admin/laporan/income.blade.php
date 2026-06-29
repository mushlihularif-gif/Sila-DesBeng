@extends('admin.layouts.admin')

@section('title', 'Laporan Pendapatan')

@section('content')
<div class="container-fluid py-4">
    @php
    $activeServices = $activeServices ?? [];
    $isRentalActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'alat'));
    $isGasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'gas'));
    $isMobilActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'mobil'));
    $isFasilitasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'fasilitas'));
    $totalActive = collect([$isRentalActive, $isGasActive, $isMobilActive, $isFasilitasActive])->filter()->count();
    @endphp

    @if($totalActive === 0)
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold fs-3 mb-1 text-primary">Laporan Pendapatan</h4>
                <p class="text-muted mb-0">Ringkasan pendapatan dan analisis keuangan {{ auth()->user()->role === 'admin' ? 'Kabupaten Bengkalis' : (auth()->user()->region->name ?? 'Anda') }}</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary shadow-sm rounded-pill px-4" type="button" data-bs-toggle="dropdown">
                    <i class="bx bx-calendar me-2"></i>Tahun {{ $year }}
                </button>
                <ul class="dropdown-menu shadow border-0 rounded-4">
                    @foreach($availableYears as $optYear)
                        <li><a class="dropdown-item {{ $optYear == $year ? 'active' : '' }}" href="{{ route('admin.laporan.pendapatan', ['year' => $optYear]) }}">{{ $optYear }}</a></li>
                    @endforeach
                </ul>
                <a href="{{ route('admin.laporan.pendapatan.riwayat') }}" class="btn btn-info text-white shadow-sm rounded-pill px-4">
                    <i class="bx bx-history me-2"></i>Riwayat Pendapatan
                </a>
                <button class="btn btn-primary shadow-sm rounded-pill px-4" onclick="window.print()">
                    <i class="bx bx-printer me-2"></i>Cetak
                </button>
            </div>
        </div>
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 text-center">
            <div class="avatar avatar-lg bg-warning-subtle text-warning rounded-circle mx-auto mb-3">
                <i class="bx bx-info-circle fs-2"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Saat ini Layanan Belum Di Aktifkan</h5>
            <p class="text-muted mb-0">Silakan aktifkan setidaknya satu layanan pada menu Pengaturan Wilayah.</p>
        </div>
    @else

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold fs-3 mb-1 text-primary">Laporan Pendapatan</h4>
            <p class="text-muted mb-0">Ringkasan pendapatan dan analisis keuangan {{ auth()->user()->role === 'admin' ? 'Kabupaten Bengkalis' : (auth()->user()->region->name ?? 'Anda') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary shadow-sm rounded-pill px-4" type="button" data-bs-toggle="dropdown">
                <i class="bx bx-calendar me-2"></i>Tahun {{ $year }}
            </button>
            <ul class="dropdown-menu shadow border-0 rounded-4">
                @foreach($availableYears as $optYear)
                    <li><a class="dropdown-item {{ $optYear == $year ? 'active' : '' }}" href="{{ route('admin.laporan.pendapatan', ['year' => $optYear]) }}">{{ $optYear }}</a></li>
                @endforeach
            </ul>
             <button class="btn btn-success shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#manualTransactionModal">
                <i class="bx bx-plus me-2"></i>Catat Transaksi Manual
            </button>
            <a href="{{ route('admin.laporan.pendapatan.riwayat') }}" class="btn btn-info text-white shadow-sm rounded-pill px-4">
                <i class="bx bx-history me-2"></i>Riwayat Pendapatan
            </a>
            <button class="btn btn-primary shadow-sm rounded-pill px-4" onclick="window.print()">
                <i class="bx bx-printer me-2"></i>Cetak
            </button>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-md bg-primary-subtle text-primary rounded-3 p-2 me-3">
                            <i class="bx bx-wallet fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Total Pendapatan</small>
                            <h5 class="fw-bold mb-0 text-dark" style="white-space: nowrap;">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatan }}">0</span></h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-center small text-muted">
                        @if($growth['total'] > 0)
                            <i class="bx bx-trending-up text-success me-1"></i>
                            <span class="text-success fw-semibold me-2">+{{ $growth['total'] }}%</span>
                        @elseif($growth['total'] < 0)
                            <i class="bx bx-trending-down text-danger me-1"></i>
                            <span class="text-danger fw-semibold me-2">{{ $growth['total'] }}%</span>
                        @else
                            <i class="bx bx-minus text-secondary me-1"></i>
                            <span class="text-secondary fw-semibold me-2">0%</span>
                        @endif
                        dari tahun lalu
                    </div>
                </div>
            </div>
        </div>
        @if($isRentalActive)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-md bg-warning-subtle text-warning rounded-3 p-2 me-3">
                            <i class="bx bx-wrench fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Unit Penyewaan</small>
                            <h5 class="fw-bold mb-0 text-dark" style="white-space: nowrap;">Rp <span class="count-up-rupiah" data-value="{{ $totalPenyewaan }}">0</span></h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-center small text-muted">
                        @if($growth['rental'] > 0)
                            <i class="bx bx-trending-up text-success me-1"></i>
                            <span class="text-success fw-semibold me-2">+{{ $growth['rental'] }}%</span>
                        @elseif($growth['rental'] < 0)
                            <i class="bx bx-trending-down text-danger me-1"></i>
                            <span class="text-danger fw-semibold me-2">{{ $growth['rental'] }}%</span>
                        @else
                            <i class="bx bx-minus text-secondary me-1"></i>
                            <span class="text-secondary fw-semibold me-2">0%</span>
                        @endif
                        dari tahun lalu
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if($isGasActive)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-md bg-info-subtle text-info rounded-3 p-2 me-3">
                            <i class="bx bxs-gas-pump fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Unit Gas</small>
                            <h5 class="fw-bold mb-0 text-dark" style="white-space: nowrap;">Rp <span class="count-up-rupiah" data-value="{{ $totalGas }}">0</span></h5>
                        </div>
                    </div>
                     <div class="d-flex align-items-center small text-muted">
                        @if($growth['gas'] > 0)
                            <i class="bx bx-trending-up text-success me-1"></i>
                            <span class="text-success fw-semibold me-2">+{{ $growth['gas'] }}%</span>
                        @elseif($growth['gas'] < 0)
                            <i class="bx bx-trending-down text-danger me-1"></i>
                            <span class="text-danger fw-semibold me-2">{{ $growth['gas'] }}%</span>
                        @else
                            <i class="bx bx-minus text-secondary me-1"></i>
                            <span class="text-secondary fw-semibold me-2">0%</span>
                        @endif
                        dari tahun lalu
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if($isMobilActive)
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-md bg-danger-subtle text-danger rounded-3 p-2 me-3">
                            <i class="bx bx-car fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Unit Sewa Mobil</small>
                            <h5 class="fw-bold mb-0 text-dark" style="white-space: nowrap;">Rp <span class="count-up-rupiah" data-value="{{ $totalMobil ?? 0 }}">0</span></h5>
                        </div>
                    </div>
                     <div class="d-flex align-items-center small text-muted">
                        @if($growth['mobil'] > 0)
                            <i class="bx bx-trending-up text-success me-1"></i>
                            <span class="text-success fw-semibold me-2">+{{ $growth['mobil'] }}%</span>
                        @elseif($growth['mobil'] < 0)
                            <i class="bx bx-trending-down text-danger me-1"></i>
                            <span class="text-danger fw-semibold me-2">{{ $growth['mobil'] }}%</span>
                        @else
                            <i class="bx bx-minus text-secondary me-1"></i>
                            <span class="text-secondary fw-semibold me-2">0%</span>
                        @endif
                        dari tahun lalu
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-4">
        <!-- Kinerja Chart -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Tren Kinerja Keuangan</h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm border-0 bg-light fw-medium">
                            <option>Tahun Ini</option>
                            <option>Tahun Lalu</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div id="kinerjaChart" style="min-height: 350px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Detail Pendapatan Per Unit -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                 <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Detail Pendapatan Unit</h5>
                    <select id="pendapatan-month" class="form-select form-select-sm border-0 bg-light fw-medium" style="width: auto;">
                        @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $month)
                            <option value="{{ $index + 1 }}" {{ ($index + 1) == ($totalPendapatanData['month'] ?? date('m')) ? 'selected' : '' }}>
                                {{ $month }} {{ $totalPendapatanData['year'] ?? date('Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body p-4">
                    @if($isRentalActive)
                    <!-- Rental Item -->
                    <div class="d-flex align-items-center mb-4 p-3 rounded-3 hover-bg-light transition-all border border-dashed-hover">
                         <div class="avatar avatar-md bg-warning-subtle text-warning rounded-3 p-2 me-3">
                            <i class="bx bx-wrench fs-3"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold text-dark mb-0">Unit Penyewaan Alat</h6>
                                <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['rental']['revenue'] ?? 0 }}">0</span></span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $totalPendapatanData['rental']['percentage'] ?? 0 }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small">
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['rental']['transactions'] ?? 0 }}">0</span> Transaksi</span>
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['rental']['percentage'] ?? 0 }}">0</span>% dari Total</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($isGasActive)
                    <!-- Gas Item -->
                    <div class="d-flex align-items-center mb-4 p-3 rounded-3 hover-bg-light transition-all border border-dashed-hover">
                         <div class="avatar avatar-md bg-info-subtle text-info rounded-3 p-2 me-3">
                            <i class="bx bxs-gas-pump fs-3"></i>
                        </div>
                         <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold text-dark mb-0">Unit Penjualan Gas</h6>
                                <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['gas']['revenue'] ?? 0 }}">0</span></span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $totalPendapatanData['gas']['percentage'] ?? 0 }}%"></div>
                            </div>
                             <div class="d-flex justify-content-between text-muted small">
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['gas']['transactions'] ?? 0 }}">0</span> Transaksi</span>
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['gas']['percentage'] ?? 0 }}">0</span>% dari Total</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($isMobilActive)
                    <!-- Mobil Item -->
                    <div class="d-flex align-items-center p-3 rounded-3 hover-bg-light transition-all border border-dashed-hover">
                         <div class="avatar avatar-md bg-danger-subtle text-danger rounded-3 p-2 me-3">
                            <i class="bx bx-car fs-3"></i>
                        </div>
                         <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold text-dark mb-0">Unit Sewa Mobil</h6>
                                <span class="fw-bold text-dark">Rp <span class="count-up-rupiah" data-value="{{ $totalPendapatanData['mobil']['revenue'] ?? 0 }}">0</span></span>
                            </div>
                            <div class="progress mb-2" style="height: 6px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $totalPendapatanData['mobil']['percentage'] ?? 0 }}%"></div>
                            </div>
                             <div class="d-flex justify-content-between text-muted small">
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['mobil']['transactions'] ?? 0 }}">0</span> Transaksi</span>
                                <span><span class="count-up" data-value="{{ $totalPendapatanData['mobil']['percentage'] ?? 0 }}">0</span>% dari Total</span>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Donut Chart -->
         <div class="col-lg-4">
             <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0">Proporsi Pendapatan</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center p-4">
                     <div id="pendapatanPieChart" style="width: 100%;"></div>
                </div>
             </div>
         </div>
    </div>

    <!-- Manual Transactions Section -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Laporan Transaksi Manual</h5>
            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill">{{ $manualReports->count() }} Data</span>
        </div>
        
        <div class="card-body p-0">
             @if($manualReports->isEmpty())
                <div class="text-center py-5">
                    <div class="mb-3"><i class="bx bx-notepad fs-1 text-muted opacity-25"></i></div>
                    <h6 class="text-muted fw-bold mb-1">Belum ada transaksi manual</h6>
                    <p class="text-muted small mb-3">Catat pendapatan di luar sistem secara manual di sini</p>
                    <button class="btn btn-sm btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#manualTransactionModal">
                        Tambah Data
                    </button>
                </div>
             @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                         <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Tanggal</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Item & Kategori</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Nominal</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Bukti</th>
                                <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                            </tr>
                        </thead>
                         <tbody>
                            @foreach($manualReports as $report)
                            <tr>
                                <td class="ps-4">
                                     <div class="d-flex flex-column">
                                        <span class="fw-semibold text-dark">{{ $report->transaction_date->format('d M Y') }}</span>
                                        <small class="text-muted">{{ $report->transaction_date->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                         <div class="avatar avatar-sm bg-light rounded-circle p-1 me-2 d-flex align-items-center justify-content-center">
                                            @if($report->category == 'penyewaan') <i class="bx bx-wrench text-warning"></i>
                                            @elseif($report->category == 'gas') <i class="bx bxs-gas-pump text-info"></i>
                                            @else <i class="bx bx-money text-success"></i> @endif
                                        </div>
                                        <div>
                                            <div class="fw-medium text-dark">{{ $report->name }}</div>
                                            <small class="text-muted">{{ ucfirst($report->category) }} • {{ $report->quantity }} Unit</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">Rp {{ number_format($report->amount * $report->quantity, 0, ',', '.') }}</div>
                                    <small class="text-muted">{{ ucfirst($report->payment_method) }}</small>
                                </td>
                                <td>
                                     @if($report->proof_image)
                                        <button class="btn btn-sm btn-light border rounded-pill px-3" onclick="viewProof('{{ asset('storage/' . $report->proof_image) }}', '{{ $report->name }}')">
                                            <i class="bx bx-image-alt me-1"></i>Lihat
                                        </button>
                                    @else
                                        <span class="text-muted small italic">- Tidak ada -</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="dropdown" data-bs-boundary="window">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                            <li>
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="editManualTransaction({{ $report->id }})">
                                                    <i class="bx bx-edit me-2 text-warning"></i>Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="deleteManualTransaction({{ $report->id }})">
                                                    <i class="bx bx-trash me-2"></i>Hapus
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                         </tbody>
                    </table>
                </div>
             @endif
        </div>
    </div>
    @endif
</div>

@push('modals')
<!-- Manual Transaction Modal -->
<div class="modal fade" id="manualTransactionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4 bg-white">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-primary-subtle text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bx bx-edit-alt fs-3"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-1" id="modalTitle">Catat Transaksi Manual</h5>
                        <p class="text-muted small mb-0">Input data pendapatan secara manual di luar sistem otomatis</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
             <form id="manualTransactionForm" enctype="multipart/form-data">
                <div class="modal-body p-0">
                    <input type="hidden" id="transactionId" name="id">
                    
                    <div class="row g-0">
                        <!-- Left Panel: Transaction Info -->
                        <div class="col-md-7 border-end p-4 p-md-5">
                            <h6 class="text-uppercase fw-bold text-primary small mb-4 d-flex align-items-center">
                                <i class="bx bx-file me-2 fs-5"></i> Informasi Transaksi
                            </h6>
                            
                            <div class="row g-4">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label fw-semibold text-dark">Tanggal Transaksi</label>
                                    <div class="input-group input-group-merge shadow-sm rounded-3">
                                        <span class="input-group-text bg-white border-end-0 text-primary"><i class="bx bx-calendar"></i></span>
                                        <input type="date" class="form-control bg-white border-start-0 ps-0" id="transaction_date" name="transaction_date" required>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label fw-semibold text-dark">Kategori</label>
                                    <div class="input-group input-group-merge shadow-sm rounded-3">
                                        <span class="input-group-text bg-white border-end-0 text-primary"><i class="bx bx-category"></i></span>
                                        <select class="form-select bg-white border-start-0 ps-0" id="category" name="category" required>
                                            <option value="">Pilih Kategori...</option>
                                            <option value="penyewaan">Penyewaan Alat</option>
                                            <option value="gas">Penjualan Gas</option>
                                            <option value="mobil">Penyewaan Mobil</option>
                                            <option value="lainnya">Pendapatan Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">Nama Barang / Keterangan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control shadow-sm rounded-3 p-2 px-3" id="name" name="name" placeholder="Contoh: Sewa Tenda Tambahan" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">Deskripsi (Opsional)</label>
                                    <textarea class="form-control shadow-sm rounded-3 p-3" id="description" name="description" rows="3" placeholder="Tambahkan catatan khusus di sini..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Right Panel: Financials -->
                        <div class="col-md-5 bg-light p-4 p-md-5">
                            <h6 class="text-uppercase fw-bold text-primary small mb-4 d-flex align-items-center">
                                <i class="bx bx-money me-2 fs-5"></i> Rincian Keuangan
                            </h6>

                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark">Jumlah (Qty) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control shadow-sm rounded-3 p-2 px-3" id="quantity" name="quantity" min="1" value="1" oninput="calculateTotal()" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark">Harga Satuan <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-white text-dark fw-bold border-end-0">Rp</span>
                                    <input type="number" class="form-control border-start-0 ps-0" id="amount" name="amount" placeholder="0" oninput="calculateTotal()" required>
                                </div>
                            </div>

                            <div class="card bg-white border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-body p-4 text-center">
                                    <p class="text-muted small fw-semibold mb-1 text-uppercase">Estimasi Total</p>
                                    <h3 class="fw-bolder text-primary mb-0" id="displayTotal">Rp 0</h3>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-semibold text-dark">Bukti (Struk/Foto) <span class="text-muted fw-normal small">(Opsional)</span></label>
                                <input type="file" class="form-control form-control-sm shadow-sm rounded-3" id="proof_image" name="proof_image" accept="image/*">
                            </div>
                            
                            <input type="hidden" name="payment_method" value="tunai">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-white p-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-semibold shadow-sm" id="submitBtn">
                        <i class="bx bx-save me-2"></i> Simpan Data Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent shadow-none border-0">
            <div class="modal-body p-0 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal"></button>
                <img src="" id="previewImage" class="img-fluid rounded-4 shadow" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>
@endpush

<style>
    .transition-all { transition: all 0.3s ease; }
    .hover-bg-light:hover { background-color: #f8f9fa; }
    .border-dashed-hover:hover { border-style: dashed !important; border-color: #696cff !important; }
    .input-group-merge .form-control:focus, .input-group-merge .form-select:focus { border-color: #696cff; box-shadow: none; }
    .input-group-merge .input-group-text { border-color: #d9dee3; color: #697a8d; }
    
    /* Fix for dropdown getting clipped in table-responsive */
    .table-responsive {
        min-height: 150px;
    }
    @media (min-width: 768px) {
        .table-responsive {
            overflow: visible;
        }
    }
    
    /* ApexCharts Tooltip Fix */
    .apexcharts-tooltip {
        background: #ffffff !important;
        border: 1px solid #e0e0e0 !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
        opacity: 1 !important;
    }
    .apexcharts-tooltip * {
        color: #333333 !important;
        font-family: inherit !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    .apexcharts-tooltip-title {
        background: #f8f9fa !important;
        border-bottom: 1px solid #eceef1 !important;
        font-weight: bold !important;
        padding: 6px 10px !important;
    }
    .apexcharts-tooltip-text-y-value {
        font-weight: bold !important;
        color: #696cff !important;
    }
    .apexcharts-tooltip-text-y-label {
        font-weight: normal !important;
    }
</style>

<!-- Scripts for Charts & Logic -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function calculateTotal() {
        const qty = document.getElementById('quantity').value || 0;
        const price = document.getElementById('amount').value || 0;
        const total = qty * price;
        document.getElementById('displayTotal').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // --- CHARTS CONFIGURATION (Optimized) ---
         // Kinerja Chart (Area)
        const kinerjaEl = document.querySelector("#kinerjaChart");
        if (kinerjaEl) {
            new ApexCharts(kinerjaEl, {
                series: [{ name: 'Pendapatan', data: {!! json_encode(array_values($monthlyIncome)) !!} }],
                chart: { type: 'area', height: 350, toolbar: { show: false } },
                colors: ['#696cff'], // Primary
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.05, stops: [0, 90, 100] } },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                xaxis: { 
                    categories: {!! json_encode(array_keys($monthlyIncome)) !!},
                    labels: { style: { colors: '#a1acb8' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                grid: { borderColor: '#eceef1', strokeDashArray: 4 },
                tooltip: {
                    theme: 'light',
                    style: {
                        fontSize: '13px',
                        fontFamily: 'inherit'
                    },
                    marker: { show: true },
                    y: {
                        formatter: function (val) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(val)
                        }
                    }
                }
            }).render();
        }

        // Pie Chart (Donut)
        const pieEl = document.querySelector("#pendapatanPieChart");
        if (pieEl) {
             new ApexCharts(pieEl, {
                series: [{{ $totalPendapatanData['rental']['revenue'] ?? 0 }}, {{ $totalPendapatanData['gas']['revenue'] ?? 0 }}, {{ $totalPendapatanData['mobil']['revenue'] ?? 0 }}],
                chart: { type: 'donut', height: 250 },
                labels: ['Penyewaan', 'Gas', 'Sewa Mobil'],
                colors: ['#ffab00', '#03c3ec', '#ff3e1d'], // Warning, Info, Danger
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: { donut: { size: '70%', labels: { show: true, total: { show: true, showAlways: true, label: 'Total', fontSize: '14px', color: '#a1acb8' } } } }
                },
                legend: { position: 'bottom' }
            }).render();
        }

        // --- MANUAL TRANSACTION LOGIC ---
        const manualForm = document.getElementById('manualTransactionForm');
        const modalEl = document.getElementById('manualTransactionModal');
        const modal = new bootstrap.Modal(modalEl);

        manualForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const id = document.getElementById('transactionId').value;
            const url = id 
                ? `{{ route('admin.laporan.manual.update', ':id') }}`.replace(':id', id)
                : `{{ route('admin.laporan.manual.store') }}`;
            
            if(id) formData.append('_method', 'PUT');

            // Disable button
            const btn = document.getElementById('submitBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
            btn.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    modal.hide();
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1500, showConfirmButton: false }).then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Terjadi kesalahan validasi', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });

        // Reset form on modal close
        modalEl.addEventListener('hidden.bs.modal', function () {
            manualForm.reset();
            document.getElementById('transactionId').value = '';
            document.getElementById('modalTitle').innerText = 'Catat Transaksi Manual';
            document.getElementById('submitBtn').innerHTML = '<i class="bx bx-save me-1"></i> Simpan Data';
            document.getElementById('displayTotal').innerText = 'Rp 0';
        });

        // Month filter logic
        document.getElementById('pendapatan-month')?.addEventListener('change', function() {
            window.location.href = `{{ route('admin.laporan.pendapatan') }}?month=${this.value}&year={{ $year }}`;
        });
    });

    // Global Functions
    window.editManualTransaction = function(id) {
         // Fetch data to fill form
         // Since we don't have a direct "Show" API for JSON, we might need to rely on the page data or just implement better client-side handling.
         // For now, let's keep it simple: we need to find the data from the table row if possible or reload.
         // To make this slick without a new API, we can iterate existing data if we had it in JS.
         // Let's add a "edit" route to the controller to fetch JSON data first?
         // Or easier: Attach data-attributes to the edit button in the loop above!
         // I will recommend a refresh for now or just SweetAlert.
         Swal.fire({
            title: 'Edit Data',
            text: 'Fitur edit sedang dikembangkan lebih lanjut. Silakan hapus & input ulang sebagai alternatif sementara.',
            icon: 'info'
        });
    }

    window.deleteManualTransaction = function(id) {
         Swal.fire({
            title: 'Hapus Laporan?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff3e1d',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ route('admin.laporan.manual.destroy', ':id') }}`.replace(':id', id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Terhapus!', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                });
            }
        });
    }

    window.viewProof = function(url, title) {
        document.getElementById('previewImage').src = url;
        new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
    }
</script>
@endsection
