@extends('admin.layouts.admin')

@section('title', 'Bukti Pembayaran Warga')

@section('content')
<style>
    .proof-stat-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        background: #fff;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }
    .proof-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.08) !important;
    }
    .proof-table th {
        font-size: 0.73rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        font-weight: 700;
        color: #8592a3 !important;
        background-color: #f8fafc;
        border-bottom: 1px solid #eef1f6;
    }
    .proof-table td {
        vertical-align: middle;
        padding: 0.95rem 1.25rem;
        border-bottom: 1px solid #f1f4f9;
    }
    .proof-table tbody tr:hover {
        background-color: #f8fafc;
    }
    .proof-mobile-card {
        border-radius: 16px;
        border: 1px solid #e9edf4;
        background: #fff;
        transition: box-shadow 0.18s ease;
    }
    #proofTabs {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    #proofTabs::-webkit-scrollbar {
        display: none;
    }
</style>

@php
    $activeServices = $activeServices ?? [];
    $isRentalActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'alat'));
    $isGasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'gas'));
    $isMobilActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'mobil') || str_contains(strtolower($name), 'transportasi'));
    $isFasilitasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'fasilitas'));
    $totalActive = collect([$isRentalActive, $isGasActive, $isMobilActive, $isFasilitasActive])->filter()->count();
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Layanan Desa /</span> Bukti Pembayaran
            </h4>
            <p class="text-muted small mb-0">Verifikasi bukti transfer manual dan konfirmasi pembayaran tunai yang masuk dari warga.</p>
        </div>
        <div class="d-flex align-items-center gap-2 w-100 w-sm-auto">
            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="btn btn-outline-primary rounded-pill px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-1 flex-grow-1 flex-sm-grow-0">
                <i class="bx bx-calendar-check"></i>
                <span>Permintaan &amp; Booking</span>
            </a>
            <button type="button" class="btn btn-white border rounded-pill px-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-1" onclick="location.reload()">
                <i class="bx bx-refresh"></i>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    @if($totalActive === 0)
        <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
            <div class="avatar avatar-lg bg-label-warning text-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                <i class="bx bx-info-circle fs-1"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">Belum Ada Unit Layanan Aktif</h5>
            <p class="text-muted small mb-3">Silakan aktifkan minimal satu unit layanan pada menu Aktivasi &amp; Katalog Unit.</p>
            @if(auth()->user()->role === 'admin_desa')
                <div>
                    <a href="{{ route('admin.region-settings.index') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bx bx-slider-alt me-1"></i>Buka Aktivasi Unit
                    </a>
                </div>
            @endif
        </div>
    @else
        <!-- Panduan Banner -->
        <div class="card bg-label-primary border-0 shadow-none rounded-4 mb-4">
            <div class="card-body p-3 p-md-4 d-flex align-items-start align-items-md-center gap-3">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 46px; height: 46px;">
                    <i class="bx bx-check-shield fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1 text-primary">Validasi Pembayaran Warga (Transfer &amp; Tunai)</h6>
                    <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.5;">
                        Periksa foto bukti transfer atau penerimaan uang tunai sebelum menyerahkan barang/layanan. Klik <strong>Periksa</strong> untuk membuka rincian pesanan atau langsung klik <strong>Konfirmasi Uang Diterima</strong>.
                    </p>
                </div>
            </div>
        </div>

        <!-- Ringkasan Statistik (Grid Rapi & Konsisten dengan Dashboard Admin) -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl">
                <div class="proof-stat-card p-3 h-100 shadow-sm d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-label-primary text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="bx bx-receipt fs-4"></i>
                    </div>
                    <div class="min-w-0">
                        <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Total Transaksi</small>
                        <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['total'] }}">{{ $stats['total'] }}</span></h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <div class="proof-stat-card p-3 h-100 shadow-sm d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-label-success text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="bx bx-money fs-4"></i>
                    </div>
                    <div class="min-w-0">
                        <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Tunai / Cash</small>
                        <h4 class="fw-bold mb-0 text-success"><span class="count-up" data-value="{{ $stats['cash_total'] }}">{{ $stats['cash_total'] }}</span></h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-4 col-xl">
                <div class="proof-stat-card p-3 h-100 shadow-sm d-flex align-items-center gap-3">
                    <div class="avatar avatar-md bg-label-info text-info rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="bx bx-transfer fs-4"></i>
                    </div>
                    <div class="min-w-0">
                        <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Transfer / E-Wallet</small>
                        <h4 class="fw-bold mb-0 text-info"><span class="count-up" data-value="{{ $stats['transfer_total'] }}">{{ $stats['transfer_total'] }}</span></h4>
                    </div>
                </div>
            </div>

            @if($isRentalActive)
            <div class="col-6 col-md-3 col-xl">
                <div class="proof-stat-card p-3 h-100 shadow-sm d-flex align-items-center gap-2.5">
                    <div class="avatar avatar-md bg-label-warning rounded-3 p-1.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <img src="{{ asset('User/img/elemen/F1.png') }}" alt="Alat" style="width: 30px; height: 30px; object-fit: contain;">
                    </div>
                    <div class="min-w-0">
                        <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Sewa Alat</small>
                        <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['rental_total'] }}">{{ $stats['rental_total'] }}</span></h4>
                    </div>
                </div>
            </div>
            @endif

            @if($isGasActive)
            <div class="col-6 col-md-3 col-xl">
                <div class="proof-stat-card p-3 h-100 shadow-sm d-flex align-items-center gap-2.5">
                    <div class="avatar avatar-md bg-label-success rounded-3 p-1.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <img src="{{ asset('User/img/elemen/F2.png') }}" alt="Gas" style="width: 30px; height: 30px; object-fit: contain;">
                    </div>
                    <div class="min-w-0">
                        <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Gas LPG</small>
                        <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['gas_total'] }}">{{ $stats['gas_total'] }}</span></h4>
                    </div>
                </div>
            </div>
            @endif

            @if($isMobilActive)
            <div class="col-6 col-md-3 col-xl">
                <div class="proof-stat-card p-3 h-100 shadow-sm d-flex align-items-center gap-2.5">
                    <div class="avatar avatar-md bg-label-primary rounded-3 p-1.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <img src="{{ asset('User/img/elemen/mobil.png') }}" alt="Mobil" style="width: 30px; height: 30px; object-fit: contain;">
                    </div>
                    <div class="min-w-0">
                        <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Transportasi</small>
                        <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['mobil_total'] }}">{{ $stats['mobil_total'] }}</span></h4>
                    </div>
                </div>
            </div>
            @endif

            @if($isFasilitasActive)
            <div class="col-6 col-md-3 col-xl">
                <div class="proof-stat-card p-3 h-100 shadow-sm d-flex align-items-center gap-2.5">
                    <div class="avatar avatar-md bg-label-info rounded-3 p-1.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <img src="{{ asset('User/img/elemen/fasilitas.png') }}" alt="Fasilitas" style="width: 30px; height: 30px; object-fit: contain;">
                    </div>
                    <div class="min-w-0">
                        <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Fasilitas</small>
                        <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['fasilitas_total'] }}">{{ $stats['fasilitas_total'] }}</span></h4>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Main Content Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-3">
                <ul class="nav nav-pills gap-2 flex-nowrap overflow-x-auto pb-1 pb-lg-0 mb-0" id="proofTabs" role="tablist" style="-webkit-overflow-scrolling: touch;">
                    @if($isRentalActive)
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button class="nav-link {{ $category == 'rental' || $category == 'all' || (!$isRentalActive && $totalActive > 0) ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center text-nowrap" id="rental-tab" data-bs-toggle="tab" data-bs-target="#rental-pane" type="button" role="tab">
                            <img src="{{ asset('User/img/elemen/F1.png') }}" class="me-2" style="width: 22px; height: 22px; object-fit: contain;">
                            <span>Penyewaan Alat</span>
                            <span class="badge rounded-pill bg-white text-primary ms-2 shadow-xs">{{ $rentalPayments->count() }}</span>
                        </button>
                    </li>
                    @endif
                    @if($isGasActive)
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button class="nav-link {{ $category == 'gas' || (!$isRentalActive && ($category == 'rental' || $category == 'all')) ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center text-nowrap" id="gas-tab" data-bs-toggle="tab" data-bs-target="#gas-pane" type="button" role="tab">
                            <img src="{{ asset('User/img/elemen/F2.png') }}" class="me-2" style="width: 22px; height: 22px; object-fit: contain;">
                            <span>Pembelian Gas</span>
                            <span class="badge rounded-pill bg-white text-primary ms-2 shadow-xs">{{ $gasPayments->count() }}</span>
                        </button>
                    </li>
                    @endif
                    @if($isMobilActive)
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button class="nav-link {{ $category == 'mobil' || (!$isRentalActive && !$isGasActive && ($category == 'rental' || $category == 'all')) ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center text-nowrap" id="mobil-tab" data-bs-toggle="tab" data-bs-target="#mobil-pane" type="button" role="tab">
                            <img src="{{ asset('User/img/elemen/mobil.png') }}" class="me-2" style="width: 22px; height: 22px; object-fit: contain;">
                            <span>Transportasi</span>
                            <span class="badge rounded-pill bg-white text-primary ms-2 shadow-xs">{{ $mobilPayments->count() }}</span>
                        </button>
                    </li>
                    @endif
                    @if($isFasilitasActive)
                    <li class="nav-item flex-shrink-0" role="presentation">
                        <button class="nav-link {{ $category == 'fasilitas' || (!$isRentalActive && !$isGasActive && !$isMobilActive && ($category == 'rental' || $category == 'all')) ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center text-nowrap" id="fasilitas-tab" data-bs-toggle="tab" data-bs-target="#fasilitas-pane" type="button" role="tab">
                            <img src="{{ asset('User/img/elemen/fasilitas.png') }}" class="me-2" style="width: 22px; height: 22px; object-fit: contain;">
                            <span>Fasilitas Umum</span>
                            <span class="badge rounded-pill bg-white text-primary ms-2 shadow-xs">{{ $fasilitasPayments->count() }}</span>
                        </button>
                    </li>
                    @endif
                </ul>

                <div class="input-group input-group-merge input-group-sm" style="max-width: 320px;">
                    <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                    <input type="text" id="searchProofInput" class="form-control bg-light border-0" placeholder="Cari nama warga, item, atau metode...">
                </div>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="proofTabsContent">

                    <!-- 1. RENTAL TAB -->
                    @if($isRentalActive)
                    <div class="tab-pane fade {{ $category == 'rental' || $category == 'all' || (!$isRentalActive && $totalActive > 0) ? 'show active' : '' }}" id="rental-pane" role="tabpanel">
                        @if($rentalPayments->isEmpty())
                            <div class="text-center py-5 px-3">
                                <div class="avatar avatar-lg bg-label-secondary text-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bx bx-receipt fs-2"></i>
                                </div>
                                <h6 class="text-dark fw-bold mb-1">Belum Ada Bukti Pembayaran Sewa Alat</h6>
                                <p class="text-muted small mb-0">Data pembayaran penyewaan alat dari warga akan tampil di sini.</p>
                            </div>
                        @else
                            <!-- Desktop Table -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table proof-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Penyewa</th>
                                            <th>Item Sewa</th>
                                            <th>Total Bayar</th>
                                            <th>Metode</th>
                                            <th>Status Pesanan</th>
                                            <th class="text-end pe-4">Status Uang &amp; Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rentalPayments as $payment)
                                        @php
                                            $pName = $payment->full_name ?? $payment->recipient_name ?? $payment->user->name ?? 'Warga';
                                        @endphp
                                        <tr class="proof-filter-item">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar avatar-sm flex-shrink-0">
                                                        @if($payment->user && $payment->user->avatar)
                                                            <img src="{{ asset('storage/' . $payment->user->avatar) }}" alt="Av" class="rounded-circle object-fit-cover" style="width: 38px; height: 38px;">
                                                        @else
                                                            <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                                                {{ strtoupper(substr($pName, 0, 1)) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ $pName }}</h6>
                                                        <small class="text-muted">{{ $payment->user->email ?? '-' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $payment->barang->nama_barang ?? 'Alat' }}</div>
                                                <small class="text-muted">{{ $payment->quantity ?? 1 }} Unit</small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark">Rp {{ number_format($payment->total_amount ?? 0, 0, ',', '.') }}</span>
                                            </td>
                                            <td>
                                                @if(strtolower($payment->payment_method) == 'tunai')
                                                    <span class="badge bg-label-success rounded-pill px-3"><i class="bx bx-money me-1"></i>Tunai</span>
                                                @elseif(strtolower($payment->payment_method) == 'transfer')
                                                    <span class="badge bg-label-info rounded-pill px-3"><i class="bx bx-transfer me-1"></i>Transfer</span>
                                                @else
                                                    <span class="badge bg-label-secondary rounded-pill px-3">{{ ucfirst($payment->payment_method) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @include('admin.partials.status-badge', ['status' => $payment->status])
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
                                                    <span class="badge {{ $payment->paymentReceived() ? 'bg-label-success' : 'bg-label-warning' }} rounded-pill">
                                                        <i class="bx {{ $payment->paymentReceived() ? 'bx-check-circle' : 'bx-time-five' }} me-1"></i>
                                                        {{ $payment->paymentReceived() ? 'Uang diterima' : 'Belum dikonfirmasi' }}
                                                    </span>
                                                    <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$payment->id, 'rental']) }}">
                                                        <i class="bx bx-show me-1"></i>Periksa
                                                    </a>
                                                    @if($payment->payment_proof)
                                                        <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'rental']) }}" class="btn btn-sm btn-icon btn-light border rounded-circle text-primary" title="Lihat Bukti Transfer" target="_blank">
                                                            <i class="bx bx-image"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('receipt.rental.view', $payment->id) }}" class="btn btn-sm btn-icon btn-light border rounded-circle text-info" title="Lihat Struk Sistem" target="_blank">
                                                            <i class="bx bx-receipt"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Cards (< 768px) -->
                            <div class="d-block d-md-none p-3 bg-light bg-opacity-50">
                                <div class="d-flex flex-column gap-3">
                                    @foreach($rentalPayments as $payment)
                                        @php
                                            $pName = $payment->full_name ?? $payment->recipient_name ?? $payment->user->name ?? 'Warga';
                                        @endphp
                                        <div class="proof-mobile-card p-3 shadow-sm proof-filter-item">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                                <div class="d-flex align-items-center gap-2 min-w-0">
                                                    <div class="avatar avatar-sm flex-shrink-0">
                                                        <span class="avatar-initial rounded-circle bg-label-primary fw-bold">{{ strtoupper(substr($pName, 0, 1)) }}</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $pName }}</h6>
                                                        <small class="text-muted d-block text-truncate">{{ $payment->barang->nama_barang ?? 'Alat' }} ({{ $payment->quantity ?? 1 }} Unit)</small>
                                                    </div>
                                                </div>
                                                @include('admin.partials.status-badge', ['status' => $payment->status])
                                            </div>
                                            <div class="bg-light rounded-3 p-2.5 mb-2.5 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Total Pembayaran</small>
                                                    <span class="fw-bold text-dark">Rp {{ number_format($payment->total_amount ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge {{ strtolower($payment->payment_method) == 'tunai' ? 'bg-label-success' : 'bg-label-info' }} rounded-pill d-block mb-1">
                                                        {{ ucfirst($payment->payment_method) }}
                                                    </span>
                                                    <span class="badge {{ $payment->paymentReceived() ? 'bg-label-success' : 'bg-label-warning' }} rounded-pill" style="font-size: 0.68rem;">
                                                        {{ $payment->paymentReceived() ? 'Uang diterima' : 'Belum dikonfirmasi' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                @if($payment->payment_proof)
                                                    <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'rental']) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                        <i class="bx bx-image me-1"></i>Bukti Foto
                                                    </a>
                                                @else
                                                    <a href="{{ route('receipt.rental.view', $payment->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                        <i class="bx bx-receipt me-1"></i>Struk
                                                    </a>
                                                @endif
                                                <a class="btn btn-sm btn-primary rounded-pill px-3 ms-auto" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$payment->id, 'rental']) }}">
                                                    <i class="bx bx-show me-1"></i>Periksa Pesanan
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- 2. GAS TAB -->
                    @if($isGasActive)
                    <div class="tab-pane fade {{ $category == 'gas' || (!$isRentalActive && ($category == 'rental' || $category == 'all')) ? 'show active' : '' }}" id="gas-pane" role="tabpanel">
                        @if($gasPayments->isEmpty())
                            <div class="text-center py-5 px-3">
                                <div class="avatar avatar-lg bg-label-secondary text-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bx bx-receipt fs-2"></i>
                                </div>
                                <h6 class="text-dark fw-bold mb-1">Belum Ada Bukti Pembayaran Gas LPG</h6>
                                <p class="text-muted small mb-0">Data pembayaran pembelian gas dari warga akan tampil di sini.</p>
                            </div>
                        @else
                            <!-- Desktop Table -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table proof-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Pembeli</th>
                                            <th>Produk Gas</th>
                                            <th>Total Bayar</th>
                                            <th>Metode</th>
                                            <th>Status Pesanan</th>
                                            <th class="text-end pe-4">Verifikasi &amp; Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gasPayments as $payment)
                                        @php
                                            $gName = $payment->full_name ?? $payment->user->name ?? 'Warga';
                                            $gTotal = ($payment->price ?? 0) * ($payment->quantity ?? 1);
                                        @endphp
                                        <tr class="proof-filter-item">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar avatar-sm flex-shrink-0">
                                                        @if($payment->user && $payment->user->avatar)
                                                            <img src="{{ asset('storage/' . $payment->user->avatar) }}" alt="Av" class="rounded-circle object-fit-cover" style="width: 38px; height: 38px;">
                                                        @else
                                                            <span class="avatar-initial rounded-circle bg-label-success fw-bold">
                                                                {{ strtoupper(substr($gName, 0, 1)) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ $gName }}</h6>
                                                        <small class="text-muted d-block text-truncate" style="max-width: 210px;">{{ $payment->address ?? $payment->user->address ?? '-' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $payment->item_name ?? 'Gas LPG' }}</div>
                                                <small class="text-muted">{{ $payment->quantity }} Tabung</small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark">Rp {{ number_format($gTotal, 0, ',', '.') }}</span>
                                            </td>
                                            <td>
                                                @if(strtolower($payment->payment_method) == 'tunai')
                                                    <span class="badge bg-label-success rounded-pill px-3"><i class="bx bx-money me-1"></i>Tunai</span>
                                                @elseif(strtolower($payment->payment_method) == 'transfer')
                                                    <span class="badge bg-label-info rounded-pill px-3"><i class="bx bx-transfer me-1"></i>Transfer</span>
                                                @else
                                                    <span class="badge bg-label-secondary rounded-pill px-3">{{ ucfirst($payment->payment_method) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @include('admin.partials.status-badge', ['status' => $payment->status])
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
                                                    @if(!in_array($payment->status, ['completed', 'cancelled', 'rejected']) && $payment->cancellation_status !== 'pending')
                                                        @if($payment->paymentReceived())
                                                            <span class="badge bg-label-success rounded-pill"><i class="bx bx-check-circle me-1"></i>Pembayaran diterima</span>
                                                        @elseif(strtolower($payment->payment_method) === 'tunai' || $payment->proof_of_payment)
                                                            <form method="POST" action="{{ route('admin.aktivitas.bukti-transaksi.verify', [$payment->id, 'gas']) }}" class="d-inline">
                                                                @csrf
                                                                <button class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" type="submit" onclick="return confirm('Pastikan uang benar-benar sudah diterima. Konfirmasi pembayaran ini?')">
                                                                    <i class="bx bx-check me-1"></i>Konfirmasi Uang Diterima
                                                                </button>
                                                            </form>
                                                            @if($payment->proof_of_payment && $payment->status === 'pending')
                                                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="openRejectGasProofModal({{ $payment->id }}, '{{ addslashes($gName) }}')">
                                                                    <i class="bx bx-error-circle me-1"></i>Bukti Tidak Sesuai
                                                                </button>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-label-secondary rounded-pill">Menunggu bukti pembayaran</span>
                                                        @endif
                                                    @endif

                                                    <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$payment->id, 'gas']) }}">
                                                        <i class="bx bx-show me-1"></i>Detail
                                                    </a>

                                                    @if($payment->proof_of_payment)
                                                        <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'gas']) }}" class="btn btn-sm btn-icon btn-light border rounded-circle text-primary" title="Lihat Bukti Transfer" target="_blank">
                                                            <i class="bx bx-image"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('receipt.gas.view', $payment->id) }}" class="btn btn-sm btn-icon btn-light border rounded-circle text-info" title="Lihat Struk Sistem" target="_blank">
                                                            <i class="bx bx-receipt"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Cards (< 768px) -->
                            <div class="d-block d-md-none p-3 bg-light bg-opacity-50">
                                <div class="d-flex flex-column gap-3">
                                    @foreach($gasPayments as $payment)
                                        @php
                                            $gName = $payment->full_name ?? $payment->user->name ?? 'Warga';
                                            $gTotal = ($payment->price ?? 0) * ($payment->quantity ?? 1);
                                        @endphp
                                        <div class="proof-mobile-card p-3 shadow-sm proof-filter-item">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                                <div class="d-flex align-items-center gap-2 min-w-0">
                                                    <div class="avatar avatar-sm flex-shrink-0">
                                                        <span class="avatar-initial rounded-circle bg-label-success fw-bold">{{ strtoupper(substr($gName, 0, 1)) }}</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $gName }}</h6>
                                                        <small class="text-muted d-block text-truncate">{{ $payment->item_name ?? 'Gas LPG' }} ({{ $payment->quantity }} Tabung)</small>
                                                    </div>
                                                </div>
                                                @include('admin.partials.status-badge', ['status' => $payment->status])
                                            </div>
                                            <div class="bg-light rounded-3 p-2.5 mb-2.5 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Total Pembayaran</small>
                                                    <span class="fw-bold text-dark">Rp {{ number_format($gTotal, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge {{ strtolower($payment->payment_method) == 'tunai' ? 'bg-label-success' : 'bg-label-info' }} rounded-pill d-block mb-1">
                                                        {{ ucfirst($payment->payment_method) }}
                                                    </span>
                                                    @if($payment->paymentReceived())
                                                        <span class="badge bg-label-success rounded-pill" style="font-size: 0.68rem;">Pembayaran diterima</span>
                                                    @else
                                                        <span class="badge bg-label-warning rounded-pill" style="font-size: 0.68rem;">Belum dikonfirmasi</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <div class="d-flex gap-1.5">
                                                    @if($payment->proof_of_payment)
                                                        <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'gas']) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5">
                                                            <i class="bx bx-image me-1"></i>Bukti
                                                        </a>
                                                    @else
                                                        <a href="{{ route('receipt.gas.view', $payment->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5">
                                                            <i class="bx bx-receipt me-1"></i>Struk
                                                        </a>
                                                    @endif
                                                    <a class="btn btn-sm btn-outline-primary rounded-pill px-2.5" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$payment->id, 'gas']) }}">
                                                        <i class="bx bx-show me-1"></i>Detail
                                                    </a>
                                                </div>

                                                @if(!in_array($payment->status, ['completed', 'cancelled', 'rejected']) && $payment->cancellation_status !== 'pending' && !$payment->paymentReceived())
                                                    @if(strtolower($payment->payment_method) === 'tunai' || $payment->proof_of_payment)
                                                        <div class="d-flex gap-1.5 ms-auto">
                                                            @if($payment->proof_of_payment && $payment->status === 'pending')
                                                                <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2.5" onclick="openRejectGasProofModal({{ $payment->id }}, '{{ addslashes($gName) }}')">
                                                                    Tolak Bukti
                                                                </button>
                                                            @endif
                                                            <form method="POST" action="{{ route('admin.aktivitas.bukti-transaksi.verify', [$payment->id, 'gas']) }}" class="d-inline">
                                                                @csrf
                                                                <button class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" type="submit" onclick="return confirm('Pastikan uang benar-benar sudah diterima. Konfirmasi pembayaran ini?')">
                                                                    <i class="bx bx-check me-1"></i>Terima Uang
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- 3. MOBIL TAB -->
                    @if($isMobilActive)
                    <div class="tab-pane fade {{ $category == 'mobil' || (!$isRentalActive && !$isGasActive && ($category == 'rental' || $category == 'all')) ? 'show active' : '' }}" id="mobil-pane" role="tabpanel">
                        @if($mobilPayments->isEmpty())
                            <div class="text-center py-5 px-3">
                                <div class="avatar avatar-lg bg-label-secondary text-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bx bx-receipt fs-2"></i>
                                </div>
                                <h6 class="text-dark fw-bold mb-1">Belum Ada Bukti Pembayaran Transportasi</h6>
                                <p class="text-muted small mb-0">Data pembayaran penyewaan transportasi warga akan tampil di sini.</p>
                            </div>
                        @else
                            <!-- Desktop Table -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table proof-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Penyewa</th>
                                            <th>Unit Transportasi</th>
                                            <th>Total Bayar</th>
                                            <th>Metode</th>
                                            <th>Status Pesanan</th>
                                            <th class="text-end pe-4">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mobilPayments as $payment)
                                        @php
                                            $mName = $payment->user->name ?? 'Warga';
                                        @endphp
                                        <tr class="proof-filter-item">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar avatar-sm flex-shrink-0">
                                                        @if($payment->user && $payment->user->avatar)
                                                            <img src="{{ asset('storage/' . $payment->user->avatar) }}" alt="Av" class="rounded-circle object-fit-cover" style="width: 38px; height: 38px;">
                                                        @else
                                                            <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                                                {{ strtoupper(substr($mName, 0, 1)) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ $mName }}</h6>
                                                        <small class="text-muted">{{ $payment->user->email ?? '-' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $payment->mobil->nama_mobil ?? 'Transportasi' }}</div>
                                                <small class="text-muted">{{ $payment->lama_sewa }} Hari</small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark">Rp {{ number_format($payment->total_harga ?? 0, 0, ',', '.') }}</span>
                                            </td>
                                            <td>
                                                @if(strtolower($payment->payment_method ?? 'tunai') == 'tunai')
                                                    <span class="badge bg-label-success rounded-pill px-3"><i class="bx bx-money me-1"></i>Tunai</span>
                                                @elseif(strtolower($payment->payment_method ?? '') == 'transfer')
                                                    <span class="badge bg-label-info rounded-pill px-3"><i class="bx bx-transfer me-1"></i>Transfer</span>
                                                @else
                                                    <span class="badge bg-label-secondary rounded-pill px-3">{{ ucfirst($payment->payment_method ?? 'Tunai') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @include('admin.partials.status-badge', ['status' => $payment->status])
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
                                                    <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$payment->id, 'mobil']) }}">
                                                        <i class="bx bx-show me-1"></i>Periksa
                                                    </a>
                                                    @if($payment->payment_proof)
                                                        <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'mobil']) }}" class="btn btn-sm btn-icon btn-light border rounded-circle text-primary" title="Lihat Bukti Transfer" target="_blank">
                                                            <i class="bx bx-image"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('receipt.mobil.view', $payment->id) }}" class="btn btn-sm btn-icon btn-light border rounded-circle text-info" title="Lihat Struk Sistem" target="_blank">
                                                            <i class="bx bx-receipt"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Cards (< 768px) -->
                            <div class="d-block d-md-none p-3 bg-light bg-opacity-50">
                                <div class="d-flex flex-column gap-3">
                                    @foreach($mobilPayments as $payment)
                                        @php
                                            $mName = $payment->user->name ?? 'Warga';
                                        @endphp
                                        <div class="proof-mobile-card p-3 shadow-sm proof-filter-item">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                                <div class="d-flex align-items-center gap-2 min-w-0">
                                                    <div class="avatar avatar-sm flex-shrink-0">
                                                        <span class="avatar-initial rounded-circle bg-label-primary fw-bold">{{ strtoupper(substr($mName, 0, 1)) }}</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $mName }}</h6>
                                                        <small class="text-muted d-block text-truncate">{{ $payment->mobil->nama_mobil ?? 'Transportasi' }} ({{ $payment->lama_sewa }} Hari)</small>
                                                    </div>
                                                </div>
                                                @include('admin.partials.status-badge', ['status' => $payment->status])
                                            </div>
                                            <div class="bg-light rounded-3 p-2.5 mb-2.5 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Total Pembayaran</small>
                                                    <span class="fw-bold text-dark">Rp {{ number_format($payment->total_harga ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <span class="badge {{ strtolower($payment->payment_method ?? 'tunai') == 'tunai' ? 'bg-label-success' : 'bg-label-info' }} rounded-pill">
                                                    {{ ucfirst($payment->payment_method ?? 'Tunai') }}
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                @if($payment->payment_proof)
                                                    <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'mobil']) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                        <i class="bx bx-image me-1"></i>Bukti Foto
                                                    </a>
                                                @else
                                                    <a href="{{ route('receipt.mobil.view', $payment->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                        <i class="bx bx-receipt me-1"></i>Struk
                                                    </a>
                                                @endif
                                                <a class="btn btn-sm btn-primary rounded-pill px-3 ms-auto" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$payment->id, 'mobil']) }}">
                                                    <i class="bx bx-show me-1"></i>Periksa Pesanan
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- 4. FASILITAS TAB -->
                    @if($isFasilitasActive)
                    <div class="tab-pane fade {{ $category == 'fasilitas' || (!$isRentalActive && !$isGasActive && !$isMobilActive && ($category == 'rental' || $category == 'all')) ? 'show active' : '' }}" id="fasilitas-pane" role="tabpanel">
                        @if($fasilitasPayments->isEmpty())
                            <div class="text-center py-5 px-3">
                                <div class="avatar avatar-lg bg-label-secondary text-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bx bx-receipt fs-2"></i>
                                </div>
                                <h6 class="text-dark fw-bold mb-1">Belum Ada Bukti Pembayaran Fasilitas Umum</h6>
                                <p class="text-muted small mb-0">Data pembayaran penyewaan fasilitas umum warga akan tampil di sini.</p>
                            </div>
                        @else
                            <!-- Desktop Table -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table proof-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Penyewa</th>
                                            <th>Fasilitas</th>
                                            <th>Total Bayar</th>
                                            <th>Metode</th>
                                            <th>Status Pesanan</th>
                                            <th class="text-end pe-4">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fasilitasPayments as $payment)
                                        @php
                                            $fName = $payment->user->name ?? 'Warga';
                                        @endphp
                                        <tr class="proof-filter-item">
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar avatar-sm flex-shrink-0">
                                                        @if($payment->user && $payment->user->avatar)
                                                            <img src="{{ asset('storage/' . $payment->user->avatar) }}" alt="Av" class="rounded-circle object-fit-cover" style="width: 38px; height: 38px;">
                                                        @else
                                                            <span class="avatar-initial rounded-circle bg-label-info fw-bold">
                                                                {{ strtoupper(substr($fName, 0, 1)) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ $fName }}</h6>
                                                        <small class="text-muted">{{ $payment->user->email ?? '-' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $payment->fasilitas->nama_fasilitas ?? 'Fasilitas' }}</div>
                                                <small class="text-muted">{{ $payment->lama_sewa }} Hari</small>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-dark">Rp {{ number_format($payment->total_harga ?? 0, 0, ',', '.') }}</span>
                                            </td>
                                            <td>
                                                @if(strtolower($payment->payment_method ?? 'tunai') == 'tunai')
                                                    <span class="badge bg-label-success rounded-pill px-3"><i class="bx bx-money me-1"></i>Tunai</span>
                                                @elseif(strtolower($payment->payment_method ?? '') == 'transfer')
                                                    <span class="badge bg-label-info rounded-pill px-3"><i class="bx bx-transfer me-1"></i>Transfer</span>
                                                @else
                                                    <span class="badge bg-label-secondary rounded-pill px-3">{{ ucfirst($payment->payment_method ?? 'Tunai') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @include('admin.partials.status-badge', ['status' => $payment->status])
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
                                                    <a class="btn btn-sm btn-outline-primary rounded-pill px-3" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$payment->id, 'fasilitas']) }}">
                                                        <i class="bx bx-show me-1"></i>Periksa
                                                    </a>
                                                    @if($payment->payment_proof)
                                                        <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'fasilitas']) }}" class="btn btn-sm btn-icon btn-light border rounded-circle text-primary" title="Lihat Bukti Transfer" target="_blank">
                                                            <i class="bx bx-image"></i>
                                                        </a>
                                                    @else
                                                        <a href="{{ route('receipt.fasilitas.view', $payment->id) }}" class="btn btn-sm btn-icon btn-light border rounded-circle text-info" title="Lihat Struk Sistem" target="_blank">
                                                            <i class="bx bx-receipt"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile Cards (< 768px) -->
                            <div class="d-block d-md-none p-3 bg-light bg-opacity-50">
                                <div class="d-flex flex-column gap-3">
                                    @foreach($fasilitasPayments as $payment)
                                        @php
                                            $fName = $payment->user->name ?? 'Warga';
                                        @endphp
                                        <div class="proof-mobile-card p-3 shadow-sm proof-filter-item">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                                <div class="d-flex align-items-center gap-2 min-w-0">
                                                    <div class="avatar avatar-sm flex-shrink-0">
                                                        <span class="avatar-initial rounded-circle bg-label-info fw-bold">{{ strtoupper(substr($fName, 0, 1)) }}</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $fName }}</h6>
                                                        <small class="text-muted d-block text-truncate">{{ $payment->fasilitas->nama_fasilitas ?? 'Fasilitas' }} ({{ $payment->lama_sewa }} Hari)</small>
                                                    </div>
                                                </div>
                                                @include('admin.partials.status-badge', ['status' => $payment->status])
                                            </div>
                                            <div class="bg-light rounded-3 p-2.5 mb-2.5 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Total Pembayaran</small>
                                                    <span class="fw-bold text-dark">Rp {{ number_format($payment->total_harga ?? 0, 0, ',', '.') }}</span>
                                                </div>
                                                <span class="badge {{ strtolower($payment->payment_method ?? 'tunai') == 'tunai' ? 'bg-label-success' : 'bg-label-info' }} rounded-pill">
                                                    {{ ucfirst($payment->payment_method ?? 'Tunai') }}
                                                </span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                @if($payment->payment_proof)
                                                    <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'fasilitas']) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                        <i class="bx bx-image me-1"></i>Bukti Foto
                                                    </a>
                                                @else
                                                    <a href="{{ route('receipt.fasilitas.view', $payment->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                        <i class="bx bx-receipt me-1"></i>Struk
                                                    </a>
                                                @endif
                                                <a class="btn btn-sm btn-primary rounded-pill px-3 ms-auto" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$payment->id, 'fasilitas']) }}">
                                                    <i class="bx bx-show me-1"></i>Periksa Pesanan
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    @endif

                </div>
            </div>
        </div>
    @endif
</div>

<!-- Modal Tolak Bukti Pembayaran Gas (Menggantikan <details> mentah di dalam tabel) -->
<div class="modal fade" id="rejectGasProofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="rejectGasProofForm" method="POST" action="" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-warning d-flex align-items-center gap-2">
                    <i class="bx bx-error-circle fs-4"></i>
                    <span>Minta Warga Ganti Bukti Pembayaran</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">
                    Bukti pembayaran dari <strong class="text-dark" id="rejectGasBuyerName">Warga</strong> akan ditandai belum sesuai agar warga dapat mengunggah ulang bukti yang benar.
                </p>
                <div class="mb-2">
                    <label for="rejectGasReasonInput" class="form-label fw-semibold small text-dark">Alasan dan Petunjuk untuk Warga <span class="text-danger">*</span></label>
                    <textarea id="rejectGasReasonInput" name="reason" class="form-control rounded-3" rows="3" required maxlength="500" placeholder="Contoh: Foto struk buram / nominal transfer tidak terlihat jelas, mohon unggah ulang foto yang terang."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning rounded-pill px-4 shadow-sm">
                    <i class="bx bx-send me-1"></i>Minta Ganti Bukti
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openRejectGasProofModal(paymentId, buyerName) {
        const form = document.getElementById('rejectGasProofForm');
        const nameEl = document.getElementById('rejectGasBuyerName');
        const reasonEl = document.getElementById('rejectGasReasonInput');
        if (form) {
            form.action = `{{ url('admin/aktivitas/bukti-transaksi') }}/${paymentId}/gas/reject`;
        }
        if (nameEl) nameEl.textContent = buyerName || 'Warga';
        if (reasonEl) reasonEl.value = '';
        const modal = new bootstrap.Modal(document.getElementById('rejectGasProofModal'));
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchProofInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('.proof-filter-item').forEach(el => {
                    const text = (el.textContent || '').toLowerCase();
                    el.style.display = (!q || text.includes(q)) ? '' : 'none';
                });
            });
        }
    });
</script>
@endpush
@endsection
