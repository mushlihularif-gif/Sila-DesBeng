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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bx bx-error-circle me-2"></i><strong>Verifikasi Ditolak:</strong> {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                        Cocokkan <strong>nominal pada foto bukti transfer &amp; mutasi rekening</strong> dengan <strong>Subtotal Tagihan</strong> sebelum mengesahkan pembayaran. Bila warga transfer kurang (misal Rp 20.000 dari Rp 200.000), sistem akan mengunci pengesahan lunas sehingga Anda dapat mencatat kurang bayar atau menolak bukti untuk meminta pelunasan.
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
                        @php
                            $nonCashCount = ($stats['transfer_total'] ?? 0) + ($stats['digital_total'] ?? 0);
                        @endphp
                        <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Transfer / E-Wallet</small>
                        <h4 class="fw-bold mb-0 text-info"><span class="count-up" data-value="{{ $nonCashCount }}">{{ $nonCashCount }}</span></h4>
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
                                            <th>Subtotal / Dibayar</th>
                                            <th>Metode</th>
                                            <th>Status Pesanan</th>
                                            <th class="text-end pe-4">Verifikasi &amp; Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rentalPayments as $payment)
                                        @php
                                            $pName = $payment->full_name ?? $payment->recipient_name ?? $payment->user->name ?? 'Warga';
                                            $pItem = $payment->barang->nama_barang ?? 'Alat';
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
                                                <div class="fw-semibold text-dark">{{ $pItem }}</div>
                                                <small class="text-muted">{{ $payment->quantity ?? 1 }} Unit</small>
                                            </td>
                                            <td>
                                                @include('admin.aktivitas.partials.proof-amount-cell', ['payment' => $payment])
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
                                                    @include('admin.aktivitas.partials.proof-verify-button', ['payment' => $payment, 'verifyType' => 'rental', 'buyerName' => $pName, 'itemLabel' => $pItem])
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
                                            $pItem = $payment->barang->nama_barang ?? 'Alat';
                                        @endphp
                                        <div class="proof-mobile-card p-3 shadow-sm proof-filter-item">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                                <div class="d-flex align-items-center gap-2 min-w-0">
                                                    <div class="avatar avatar-sm flex-shrink-0">
                                                        <span class="avatar-initial rounded-circle bg-label-primary fw-bold">{{ strtoupper(substr($pName, 0, 1)) }}</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $pName }}</h6>
                                                        <small class="text-muted d-block text-truncate">{{ $pItem }} ({{ $payment->quantity ?? 1 }} Unit)</small>
                                                    </div>
                                                </div>
                                                @include('admin.partials.status-badge', ['status' => $payment->status])
                                            </div>
                                            <div class="bg-light rounded-3 p-2.5 mb-2.5 d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Subtotal &amp; Dibayar</small>
                                                    @include('admin.aktivitas.partials.proof-amount-cell', ['payment' => $payment])
                                                </div>
                                                <div class="text-end flex-shrink-0">
                                                    <span class="badge {{ strtolower($payment->payment_method) == 'tunai' ? 'bg-label-success' : 'bg-label-info' }} rounded-pill d-block">
                                                        {{ ucfirst($payment->payment_method) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <div class="d-flex gap-1.5">
                                                    @if($payment->payment_proof)
                                                        <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'rental']) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5">
                                                            <i class="bx bx-image me-1"></i>Bukti
                                                        </a>
                                                    @else
                                                        <a href="{{ route('receipt.rental.view', $payment->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5">
                                                            <i class="bx bx-receipt me-1"></i>Struk
                                                        </a>
                                                    @endif
                                                    <a class="btn btn-sm btn-outline-primary rounded-pill px-2.5" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$payment->id, 'rental']) }}">
                                                        <i class="bx bx-show me-1"></i>Detail
                                                    </a>
                                                </div>
                                                <div class="d-flex flex-wrap gap-1.5 ms-auto">
                                                    @include('admin.aktivitas.partials.proof-verify-button', ['payment' => $payment, 'verifyType' => 'rental', 'buyerName' => $pName, 'itemLabel' => $pItem])
                                                </div>
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
                                            <th>Subtotal / Dibayar</th>
                                            <th>Metode</th>
                                            <th>Status Pesanan</th>
                                            <th class="text-end pe-4">Verifikasi &amp; Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($gasPayments as $payment)
                                        @php
                                            $gName = $payment->full_name ?? $payment->user->name ?? 'Warga';
                                            $gItem = ($payment->item_name ?? 'Gas LPG') . ' (' . ($payment->quantity ?? 1) . ' Tabung)';
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
                                                @include('admin.aktivitas.partials.proof-amount-cell', ['payment' => $payment])
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
                                                    @include('admin.aktivitas.partials.proof-verify-button', ['payment' => $payment, 'verifyType' => 'gas', 'buyerName' => $gName, 'itemLabel' => $gItem])

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
                                            $gItem = ($payment->item_name ?? 'Gas LPG') . ' (' . ($payment->quantity ?? 1) . ' Tabung)';
                                        @endphp
                                        <div class="proof-mobile-card p-3 shadow-sm proof-filter-item">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                                <div class="d-flex align-items-center gap-2 min-w-0">
                                                    <div class="avatar avatar-sm flex-shrink-0">
                                                        <span class="avatar-initial rounded-circle bg-label-success fw-bold">{{ strtoupper(substr($gName, 0, 1)) }}</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $gName }}</h6>
                                                        <small class="text-muted d-block text-truncate">{{ $gItem }}</small>
                                                    </div>
                                                </div>
                                                @include('admin.partials.status-badge', ['status' => $payment->status])
                                            </div>
                                            <div class="bg-light rounded-3 p-2.5 mb-2.5 d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Subtotal &amp; Dibayar</small>
                                                    @include('admin.aktivitas.partials.proof-amount-cell', ['payment' => $payment])
                                                </div>
                                                <div class="text-end flex-shrink-0">
                                                    <span class="badge {{ strtolower($payment->payment_method) == 'tunai' ? 'bg-label-success' : 'bg-label-info' }} rounded-pill d-block">
                                                        {{ ucfirst($payment->payment_method) }}
                                                    </span>
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
                                                <div class="d-flex flex-wrap gap-1.5 ms-auto">
                                                    @include('admin.aktivitas.partials.proof-verify-button', ['payment' => $payment, 'verifyType' => 'gas', 'buyerName' => $gName, 'itemLabel' => $gItem])
                                                </div>
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
                                            <th>Subtotal / Dibayar</th>
                                            <th>Metode</th>
                                            <th>Status Pesanan</th>
                                            <th class="text-end pe-4">Verifikasi &amp; Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($mobilPayments as $payment)
                                        @php
                                            $mName = $payment->recipient_name ?? $payment->user->name ?? 'Warga';
                                            $mItem = $payment->mobil->nama_mobil ?? 'Transportasi';
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
                                                <div class="fw-semibold text-dark">{{ $mItem }}</div>
                                                <small class="text-muted">{{ $payment->lama_sewa }} Hari</small>
                                            </td>
                                            <td>
                                                @include('admin.aktivitas.partials.proof-amount-cell', ['payment' => $payment])
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
                                                    @include('admin.aktivitas.partials.proof-verify-button', ['payment' => $payment, 'verifyType' => 'mobil', 'buyerName' => $mName, 'itemLabel' => $mItem])
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
                                            $mName = $payment->recipient_name ?? $payment->user->name ?? 'Warga';
                                            $mItem = $payment->mobil->nama_mobil ?? 'Transportasi';
                                        @endphp
                                        <div class="proof-mobile-card p-3 shadow-sm proof-filter-item">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                                <div class="d-flex align-items-center gap-2 min-w-0">
                                                    <div class="avatar avatar-sm flex-shrink-0">
                                                        <span class="avatar-initial rounded-circle bg-label-primary fw-bold">{{ strtoupper(substr($mName, 0, 1)) }}</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $mName }}</h6>
                                                        <small class="text-muted d-block text-truncate">{{ $mItem }} ({{ $payment->lama_sewa }} Hari)</small>
                                                    </div>
                                                </div>
                                                @include('admin.partials.status-badge', ['status' => $payment->status])
                                            </div>
                                            <div class="bg-light rounded-3 p-2.5 mb-2.5 d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Subtotal &amp; Dibayar</small>
                                                    @include('admin.aktivitas.partials.proof-amount-cell', ['payment' => $payment])
                                                </div>
                                                <span class="badge {{ strtolower($payment->payment_method ?? 'tunai') == 'tunai' ? 'bg-label-success' : 'bg-label-info' }} rounded-pill flex-shrink-0">
                                                    {{ ucfirst($payment->payment_method ?? 'Tunai') }}
                                                </span>
                                            </div>
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <div class="d-flex gap-1.5">
                                                    @if($payment->payment_proof)
                                                        <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'mobil']) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5">
                                                            <i class="bx bx-image me-1"></i>Bukti
                                                        </a>
                                                    @else
                                                        <a href="{{ route('receipt.mobil.view', $payment->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5">
                                                            <i class="bx bx-receipt me-1"></i>Struk
                                                        </a>
                                                    @endif
                                                    <a class="btn btn-sm btn-outline-primary rounded-pill px-2.5" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$payment->id, 'mobil']) }}">
                                                        <i class="bx bx-show me-1"></i>Detail
                                                    </a>
                                                </div>
                                                <div class="d-flex flex-wrap gap-1.5 ms-auto">
                                                    @include('admin.aktivitas.partials.proof-verify-button', ['payment' => $payment, 'verifyType' => 'mobil', 'buyerName' => $mName, 'itemLabel' => $mItem])
                                                </div>
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
                                            <th>Subtotal / Dibayar</th>
                                            <th>Metode</th>
                                            <th>Status Pesanan</th>
                                            <th class="text-end pe-4">Verifikasi &amp; Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fasilitasPayments as $payment)
                                        @php
                                            $fName = $payment->recipient_name ?? $payment->user->name ?? 'Warga';
                                            $fItem = $payment->fasilitas->nama_fasilitas ?? 'Fasilitas';
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
                                                <div class="fw-semibold text-dark">{{ $fItem }}</div>
                                                <small class="text-muted">{{ $payment->lama_sewa }} Hari</small>
                                            </td>
                                            <td>
                                                @include('admin.aktivitas.partials.proof-amount-cell', ['payment' => $payment])
                                            </td>
                                            <td>
                                                @if($payment->subtotalAmount() <= 0)
                                                    <span class="badge bg-label-info rounded-pill px-3">Gratis</span>
                                                @elseif(strtolower($payment->payment_method ?? 'tunai') == 'tunai')
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
                                                    @include('admin.aktivitas.partials.proof-verify-button', ['payment' => $payment, 'verifyType' => 'fasilitas', 'buyerName' => $fName, 'itemLabel' => $fItem])
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
                                            $fName = $payment->recipient_name ?? $payment->user->name ?? 'Warga';
                                            $fItem = $payment->fasilitas->nama_fasilitas ?? 'Fasilitas';
                                        @endphp
                                        <div class="proof-mobile-card p-3 shadow-sm proof-filter-item">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                                <div class="d-flex align-items-center gap-2 min-w-0">
                                                    <div class="avatar avatar-sm flex-shrink-0">
                                                        <span class="avatar-initial rounded-circle bg-label-info fw-bold">{{ strtoupper(substr($fName, 0, 1)) }}</span>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $fName }}</h6>
                                                        <small class="text-muted d-block text-truncate">{{ $fItem }} ({{ $payment->lama_sewa }} Hari)</small>
                                                    </div>
                                                </div>
                                                @include('admin.partials.status-badge', ['status' => $payment->status])
                                            </div>
                                            <div class="bg-light rounded-3 p-2.5 mb-2.5 d-flex justify-content-between align-items-start gap-2">
                                                <div>
                                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Subtotal &amp; Dibayar</small>
                                                    @include('admin.aktivitas.partials.proof-amount-cell', ['payment' => $payment])
                                                </div>
                                                <span class="badge {{ $payment->subtotalAmount() <= 0 ? 'bg-label-info' : (strtolower($payment->payment_method ?? 'tunai') == 'tunai' ? 'bg-label-success' : 'bg-label-info') }} rounded-pill flex-shrink-0">
                                                    {{ $payment->subtotalAmount() <= 0 ? 'Gratis' : ucfirst($payment->payment_method ?? 'Tunai') }}
                                                </span>
                                            </div>
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                                <div class="d-flex gap-1.5">
                                                    @if($payment->payment_proof)
                                                        <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'fasilitas']) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5">
                                                            <i class="bx bx-image me-1"></i>Bukti
                                                        </a>
                                                    @else
                                                        <a href="{{ route('receipt.fasilitas.view', $payment->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5">
                                                            <i class="bx bx-receipt me-1"></i>Struk
                                                        </a>
                                                    @endif
                                                    <a class="btn btn-sm btn-outline-primary rounded-pill px-2.5" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$payment->id, 'fasilitas']) }}">
                                                        <i class="bx bx-show me-1"></i>Detail
                                                    </a>
                                                </div>
                                                <div class="d-flex flex-wrap gap-1.5 ms-auto">
                                                    @include('admin.aktivitas.partials.proof-verify-button', ['payment' => $payment, 'verifyType' => 'fasilitas', 'buyerName' => $fName, 'itemLabel' => $fItem])
                                                </div>
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

<!-- Modal Verifikasi Nominal Pembayaran (Anti Kurang Bayar) -->
<div class="modal fade" id="verifyPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form id="verifyPaymentForm" method="POST" action="" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <input type="hidden" name="amount_mode" id="vpAmountMode" value="total">
            <div class="modal-header border-bottom px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0">
                        <i class="bx bx-check-shield text-success fs-4"></i>
                        <span>Verifikasi Pembayaran Nyata</span>
                    </h5>
                    <small class="text-muted" id="vpOrderMeta">Cocokkan nominal bukti transfer dengan subtotal tagihan</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <!-- Kolom Kiri: Foto Bukti -->
                    <div class="col-12 col-md-5" id="vpProofCol">
                        <div class="border rounded-4 p-2.5 bg-light text-center h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="small fw-bold text-muted text-uppercase mb-2">Foto Bukti Transfer</div>
                                <img id="vpProofImg" src="" alt="Bukti Transfer" class="img-fluid rounded-3 shadow-sm mb-2" style="max-height: 280px; object-fit: contain;">
                            </div>
                            <div class="d-flex flex-wrap justify-content-center gap-2 mt-2">
                                <a id="vpProofFullLink" href="#" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="bx bx-fullscreen me-1"></i>Perbesar
                                </a>
                                <button type="button" id="vpScanBtn" class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="triggerProofScanFromModal()">
                                    <i class="bx bx-scan me-1"></i>Pindai Nominal
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Rincian & Input Nominal Nyata -->
                    <div class="col-12 col-md-7" id="vpFormCol">
                        <div class="rounded-3 bg-light border p-3 mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Subtotal Pesanan</span>
                                <strong class="text-dark" id="vpSubtotalText">Rp 0</strong>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Sudah Diterima Sebelumnya</span>
                                <strong class="text-dark" id="vpPaidText">Rp 0</strong>
                            </div>
                            <div class="d-flex justify-content-between small pt-1 border-top">
                                <span class="fw-semibold text-dark">Sisa Tagihan Harus Masuk</span>
                                <strong class="text-primary fs-6" id="vpRemainingText">Rp 0</strong>
                            </div>
                        </div>

                        <div id="vpScanAlert" class="d-none mb-3 small rounded-3 p-2.5"></div>

                        <div class="mb-3">
                            <label for="vpConfirmedAmount" id="vpAmountLabel" class="form-label fw-bold text-dark small mb-1">
                                Nominal Uang Benar-Benar Diterima (Rp) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-white fw-bold">Rp</span>
                                <input type="text" inputmode="numeric" id="vpConfirmedAmount" name="confirmed_amount" class="form-control fw-bold" required placeholder="Ketik nominal sesuai bukti / mutasi">
                            </div>
                            <div class="form-text small">
                                Periksa mutasi rekening / uang tunai terlebih dahulu. Sistem mengunci pengesahan lunas jika nominal kurang dari subtotal.
                            </div>
                        </div>

                        <div id="vpLiveFeedback" class="small mb-2"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4 d-flex flex-wrap justify-content-between gap-2">
                <button type="button" id="vpSwitchShortfallBtn" class="btn btn-warning rounded-pill px-3 d-none" onclick="switchToShortfallModal()">
                    <i class="bx bx-error-circle me-1"></i>Catat Kurang Bayar &amp; Minta Pelunasan
                </button>
                <div class="d-flex gap-2 ms-auto">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="vpSubmitBtn" class="btn btn-success rounded-pill px-4 shadow-sm">
                        <i class="bx bx-check-circle me-1"></i>Konfirmasi Uang Diterima (Lunas)
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tolak Bukti / Catat Kurang Bayar (Semua Unit Layanan) -->
<div class="modal fade" id="rejectProofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="rejectProofForm" method="POST" action="" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-warning d-flex align-items-center gap-2">
                    <i class="bx bx-error-circle fs-4"></i>
                    <span>Tolak Bukti / Catat Kurang Bayar</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">
                    Bukti pembayaran dari <strong class="text-dark" id="rejectBuyerName">Warga</strong> akan ditandai belum sesuai agar warga dapat mengunggah bukti baru / bukti pelunasan.
                </p>
                <div class="mb-3">
                    <label for="rejectReceivedAmountInput" class="form-label fw-semibold small text-dark">
                        Total Nominal yang Benar-Benar Sudah Masuk (Rp) <span class="text-muted fw-normal">(Opsional)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">Rp</span>
                        <input type="text" inputmode="numeric" id="rejectReceivedAmountInput" name="received_amount" class="form-control" placeholder="0 jika belum ada uang masuk, atau ketik misal 20000">
                    </div>
                    <div class="form-text small" id="rejectSubtotalHint">
                        Bila warga baru transfer sebagian (misal Rp 20.000 dari Rp 200.000), ketik angka yang sudah masuk agar tercatat kurang bayar dan warga diminta melunasi sisanya.
                    </div>
                </div>
                <div class="mb-2">
                    <label for="rejectReasonInput" class="form-label fw-semibold small text-dark">Alasan dan Petunjuk untuk Warga <span class="text-danger">*</span></label>
                    <textarea id="rejectReasonInput" name="reason" class="form-control rounded-3" rows="3" required maxlength="500" placeholder="Contoh: Nominal transfer Rp 20.000 kurang Rp 180.000 dari subtotal tagihan Rp 200.000. Mohon transfer sisa kekurangan dan unggah bukti pelunasan."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning rounded-pill px-4 shadow-sm">
                    <i class="bx bx-send me-1"></i>Kirim ke Warga
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let currentVerifyCtx = null;

    function parseRupiahDigits(val) {
        if (val === null || val === undefined) return null;
        const digits = String(val).replace(/[^\d]/g, '');
        if (!digits) return null;
        return parseInt(digits, 10);
    }

    function fmtRupiah(num) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.max(0, Math.round(num || 0)));
    }

    function renderModalScanAlert(status, message) {
        const alertEl = document.getElementById('vpScanAlert');
        if (!alertEl) return;
        if (!status || !message) {
            alertEl.className = 'd-none mb-3 small rounded-3 p-2.5';
            alertEl.innerHTML = '';
            return;
        }
        if (status === 'mismatch') {
            alertEl.className = 'alert alert-danger mb-3 small rounded-3 p-2.5';
            alertEl.innerHTML = '<div class="fw-bold mb-1"><i class="bx bx-error-alt me-1"></i>Peringatan Pindai Bukti</div><div>' + message + '</div>';
        } else if (status === 'matched') {
            alertEl.className = 'alert alert-success mb-3 small rounded-3 p-2.5';
            alertEl.innerHTML = '<div class="fw-bold mb-1"><i class="bx bx-check-shield me-1"></i>Hasil Pindai Cocok</div><div>' + message + '</div>';
        } else {
            alertEl.className = 'alert alert-info mb-3 small rounded-3 p-2.5';
            alertEl.innerHTML = '<i class="bx bx-info-circle me-1"></i>' + message;
        }
    }

    function evaluateVerifyModalInput() {
        if (!currentVerifyCtx) return;
        const input = document.getElementById('vpConfirmedAmount');
        const fb = document.getElementById('vpLiveFeedback');
        const submitBtn = document.getElementById('vpSubmitBtn');
        const shortBtn = document.getElementById('vpSwitchShortfallBtn');
        const val = parseRupiahDigits(input.value);

        if (val === null) {
            fb.className = 'small text-muted mb-2';
            fb.innerHTML = 'Masukkan nominal uang nyata yang diterima (<strong>' + fmtRupiah(currentVerifyCtx.remaining) + '</strong>).';
            submitBtn.disabled = false;
            shortBtn.classList.add('d-none');
            return;
        }

        const totalReceived = currentVerifyCtx.paid > 0 ? (currentVerifyCtx.paid + val) : val;
        if (totalReceived < currentVerifyCtx.subtotal) {
            const kurang = currentVerifyCtx.subtotal - totalReceived;
            fb.className = 'alert alert-danger py-2 px-3 small rounded-3 mb-2';
            fb.innerHTML = '<strong><i class="bx bx-x-circle me-1"></i>KURANG BAYAR ' + fmtRupiah(kurang) + '!</strong><br>' +
                'Total diterima <strong>' + fmtRupiah(totalReceived) + '</strong> dari subtotal <strong>' + fmtRupiah(currentVerifyCtx.subtotal) + '</strong>. Tombol Lunas dikunci agar laporan pendapatan desa tetap akurat.';
            submitBtn.disabled = true;
            if (currentVerifyCtx.canReject) {
                shortBtn.classList.remove('d-none');
                shortBtn.dataset.totalReceived = totalReceived;
                shortBtn.dataset.shortfall = kurang;
            }
        } else if (totalReceived > currentVerifyCtx.subtotal) {
            const lebih = totalReceived - currentVerifyCtx.subtotal;
            fb.className = 'alert alert-warning py-2 px-3 small rounded-3 mb-2';
            fb.innerHTML = '<strong>Nominal melebihi tagihan sebesar ' + fmtRupiah(lebih) + '.</strong> Periksa kembali nominal yang diketik.';
            submitBtn.disabled = true;
            shortBtn.classList.add('d-none');
        } else {
            fb.className = 'alert alert-success py-2 px-3 small rounded-3 mb-2';
            fb.innerHTML = '<i class="bx bx-check-circle me-1"></i><strong>Cocok!</strong> Total diterima ' + fmtRupiah(totalReceived) + ' sesuai subtotal tagihan (Lunas).';
            submitBtn.disabled = false;
            shortBtn.classList.add('d-none');
        }
    }

    function openVerifyPaymentModal(btn) {
        const d = btn.dataset;
        currentVerifyCtx = {
            id: d.id,
            type: d.type,
            buyer: d.buyer || 'Warga',
            item: d.item || 'Layanan',
            orderNumber: d.orderNumber || '',
            subtotal: parseInt(d.subtotal || '0', 10),
            paid: parseInt(d.paid || '0', 10),
            remaining: parseInt(d.remaining || '0', 10),
            isCash: d.isCash === '1',
            canReject: d.canReject === '1',
            proofUrl: d.proofUrl || '',
            scanStatus: d.scanStatus || '',
            scanAmount: d.scanAmount || '',
            scanMessage: d.scanMessage || ''
        };

        const form = document.getElementById('verifyPaymentForm');
        form.action = `{{ url('admin/aktivitas/bukti-transaksi') }}/${currentVerifyCtx.id}/${currentVerifyCtx.type}/verify`;
        document.getElementById('vpAmountMode').value = currentVerifyCtx.paid > 0 ? 'delta' : 'total';
        document.getElementById('vpOrderMeta').textContent = `${currentVerifyCtx.buyer} • ${currentVerifyCtx.item} (${currentVerifyCtx.orderNumber})`;
        document.getElementById('vpSubtotalText').textContent = fmtRupiah(currentVerifyCtx.subtotal);
        document.getElementById('vpPaidText').textContent = fmtRupiah(currentVerifyCtx.paid);
        document.getElementById('vpRemainingText').textContent = fmtRupiah(currentVerifyCtx.remaining);
        document.getElementById('vpAmountLabel').innerHTML = (currentVerifyCtx.paid > 0
            ? 'Nominal Pelunasan yang Baru Masuk (Rp)'
            : 'Nominal Uang Benar-Benar Diterima (Rp)') + ' <span class="text-danger">*</span>';

        const proofCol = document.getElementById('vpProofCol');
        const formCol = document.getElementById('vpFormCol');
        if (currentVerifyCtx.proofUrl) {
            proofCol.classList.remove('d-none');
            formCol.className = 'col-12 col-md-7';
            document.getElementById('vpProofImg').src = currentVerifyCtx.proofUrl;
            document.getElementById('vpProofFullLink').href = currentVerifyCtx.proofUrl;
        } else {
            proofCol.classList.add('d-none');
            formCol.className = 'col-12';
        }

        const amountInput = document.getElementById('vpConfirmedAmount');
        if (currentVerifyCtx.isCash) {
            amountInput.value = new Intl.NumberFormat('id-ID').format(currentVerifyCtx.remaining);
        } else if (currentVerifyCtx.scanAmount) {
            amountInput.value = new Intl.NumberFormat('id-ID').format(parseInt(currentVerifyCtx.scanAmount, 10));
        } else {
            amountInput.value = '';
        }

        renderModalScanAlert(currentVerifyCtx.scanStatus, currentVerifyCtx.scanMessage);
        evaluateVerifyModalInput();

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('verifyPaymentModal'));
        modal.show();
    }

    async function triggerProofScanFromModal() {
        if (!currentVerifyCtx) return;
        const btn = document.getElementById('vpScanBtn');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memindai...';
        try {
            const res = await fetch(`{{ url('admin/aktivitas/bukti-transaksi') }}/${currentVerifyCtx.id}/${currentVerifyCtx.type}/scan-proof`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await res.json();
            if (data && data.scan) {
                renderModalScanAlert(data.scan.status, data.scan.message);
                if (data.scan.detected_amount !== null && data.scan.detected_amount !== undefined) {
                    const input = document.getElementById('vpConfirmedAmount');
                    input.value = new Intl.NumberFormat('id-ID').format(Math.round(Number(data.scan.detected_amount)));
                    evaluateVerifyModalInput();
                }
            }
        } catch (err) {
            renderModalScanAlert('unreadable', 'Pemindaian otomatis tidak tersedia saat ini. Periksa nominal bukti secara manual.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }

    function switchToShortfallModal() {
        if (!currentVerifyCtx) return;
        const shortBtn = document.getElementById('vpSwitchShortfallBtn');
        const totalRec = parseInt(shortBtn.dataset.totalReceived || '0', 10);
        const kurang = parseInt(shortBtn.dataset.shortfall || '0', 10);
        const reason = `Nominal transfer baru diterima ${fmtRupiah(totalRec)} dari subtotal ${fmtRupiah(currentVerifyCtx.subtotal)} (kurang ${fmtRupiah(kurang)}). Mohon transfer sisa kekurangan lalu unggah bukti pelunasan.`;

        bootstrap.Modal.getOrCreateInstance(document.getElementById('verifyPaymentModal')).hide();
        openRejectProofModal(currentVerifyCtx.id, currentVerifyCtx.type, currentVerifyCtx.buyer, currentVerifyCtx.subtotal, totalRec, reason);
    }

    function openRejectProofModal(paymentId, type, buyerName, subtotal, receivedSoFar, prefillReason) {
        const form = document.getElementById('rejectProofForm');
        const nameEl = document.getElementById('rejectBuyerName');
        const recEl = document.getElementById('rejectReceivedAmountInput');
        const reasonEl = document.getElementById('rejectReasonInput');
        const hintEl = document.getElementById('rejectSubtotalHint');

        if (form) form.action = `{{ url('admin/aktivitas/bukti-transaksi') }}/${paymentId}/${type}/reject`;
        if (nameEl) nameEl.textContent = buyerName || 'Warga';
        if (recEl) recEl.value = receivedSoFar > 0 ? receivedSoFar : '';
        if (reasonEl) reasonEl.value = prefillReason || '';
        if (hintEl && subtotal > 0) {
            hintEl.textContent = `Subtotal tagihan: ${fmtRupiah(subtotal)}. Isi angka yang sudah benar-benar masuk bila warga transfer sebagian, atau kosongkan bila Rp 0 masuk.`;
        }

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('rejectProofModal'));
        modal.show();
    }

    // Alias backward-compatibility
    function openRejectGasProofModal(paymentId, buyerName) {
        openRejectProofModal(paymentId, 'gas', buyerName, 0, 0, '');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const vpInput = document.getElementById('vpConfirmedAmount');
        if (vpInput) {
            vpInput.addEventListener('input', evaluateVerifyModalInput);
        }

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
