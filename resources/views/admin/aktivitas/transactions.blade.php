@extends('admin.layouts.admin')

@section('title', 'Bukti Transaksi')

@section('content')
<style>
    .table-modern { border-collapse: separate; border-spacing: 0 8px; }
    .table-modern thead th { border: none; background-color: transparent !important; color: #6c757d; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; padding-bottom: 0; }
    .table-modern tbody tr { background-color: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.02); transition: all 0.3s ease; }
    .table-modern tbody tr:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .table-modern td { border: none; padding: 1rem; vertical-align: middle; }
    .table-modern td:first-child { border-radius: 8px 0 0 8px; }
    .table-modern td:last-child { border-radius: 0 8px 8px 0; }
</style>

<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    @php
    $activeServices = $activeServices ?? [];
    $isRentalActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'alat'));
    $isGasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'gas'));
    $isMobilActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'mobil'));
    $isFasilitasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'fasilitas'));
    $isPasarActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'pasar'));
    $totalActive = collect([$isRentalActive, $isGasActive, $isMobilActive, $isFasilitasActive, $isPasarActive])->filter()->count();
    @endphp

    @if($totalActive === 0)
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Sistem / Aktivitas /</span> Bukti Transaksi
                </h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-white border shadow-sm rounded-pill px-4" onclick="location.reload()">
                        <i class="bx bx-refresh me-2"></i>Refresh
                    </button>
                </div>
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
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Sistem / Aktivitas /</span> Bukti Transaksi
                </h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-white border shadow-sm rounded-pill px-4" onclick="location.reload()">
                        <i class="bx bx-refresh me-2"></i>Refresh
                    </button>
                </div>
            </div>
        </div>

        <!-- Panduan -->
        <div class="card bg-label-primary border-0 shadow-none mb-4" style="border-radius: 12px;">
            <div class="card-body d-flex align-items-center p-4">
                <div class="me-3">
                    <div class="bg-primary p-3 rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 56px; height: 56px;">
                        <i class="bx bx-receipt fs-3"></i>
                    </div>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-primary">Kelola Bukti Transaksi</h5>
                    <p class="mb-0 text-primary" style="opacity: 0.85;">
                        Validasi pembayaran yang masuk dari warga. Klik tombol pada tabel untuk meninjau detail bukti atau melakukan persetujuan secara cepat.
                    </p>
                </div>
            </div>
        </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Kategori Layanan -->
        <div class="col-12 col-xl-7">
            <h6 class="fw-bold text-secondary mb-3 text-uppercase small ls-1">Berdasarkan Kategori Layanan</h6>
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-md bg-primary-subtle text-primary rounded-3 p-2 me-3">
                                    <i class="bx bx-receipt fs-3"></i>
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Total Bukti</small>
                                    <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['total'] }}">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if($isRentalActive)
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-md bg-info-subtle rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                                    <img src="{{ asset('User/img/elemen/F1.png') }}" style="width: 44px; height: 44px; object-fit: contain;">
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Penyewaan Alat</small>
                                    <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['rental_total'] }}">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if($isGasActive)
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-md bg-success-subtle rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                                    <img src="{{ asset('User/img/elemen/F2.png') }}" style="width: 44px; height: 44px; object-fit: contain;">
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Pembelian Gas</small>
                                    <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['gas_total'] }}">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if($isMobilActive)
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-md bg-primary-subtle rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                                    <img src="{{ asset('User/img/elemen/mobil.png') }}" style="width: 44px; height: 44px; object-fit: contain;">
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Penyewaan Mobil</small>
                                    <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['mobil_total'] }}">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if($isFasilitasActive)
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-md bg-warning-subtle rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                                    <img src="{{ asset('User/img/elemen/fasilitas.png') }}" style="width: 44px; height: 44px; object-fit: contain;">
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Fasilitas Umum</small>
                                    <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['fasilitas_total'] }}">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if($isPasarActive)
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-md bg-secondary-subtle rounded-3 p-2 me-3 d-flex align-items-center justify-content-center">
                                    <img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" style="width: 44px; height: 44px; object-fit: contain;">
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Pasar Daerah</small>
                                    <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['pasar_total'] }}">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Metode Pembayaran -->
        <div class="col-12 col-xl-5">
            <h6 class="fw-bold text-secondary mb-3 text-uppercase small ls-1">Berdasarkan Metode Pembayaran</h6>
            <div class="row g-3">
                <div class="col-6 col-md-4 col-xl-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-md bg-success-subtle text-success rounded-3 p-2 me-3">
                                    <i class="bx bx-money fs-3"></i>
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Tunai / Cash</small>
                                    <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['cash_total'] }}">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-6">
                    <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-md bg-info-subtle text-info rounded-3 p-2 me-3">
                                    <i class="bx bx-transfer fs-3"></i>
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Transfer Manual</small>
                                    <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['transfer_total'] }}">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-12">
                    <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-md bg-primary-subtle text-primary rounded-3 p-2 me-3">
                                    <i class="bx bx-credit-card-front fs-3"></i>
                                </div>
                                <div>
                                    <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Pembayaran Digital / Gateway</small>
                                    <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['digital_total'] }}">0</span></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4">
             <ul class="nav nav-pills card-header-pills gap-2" id="proofTabs" role="tablist">
                @if($isRentalActive)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $category == 'rental' || $category == 'all' || (!$isRentalActive && $totalActive > 0) ? 'active' : '' }} rounded-pill px-4 fw-semibold" id="rental-tab" data-bs-toggle="tab" data-bs-target="#rental-pane" type="button" role="tab">
                        <img src="{{ asset('User/img/elemen/F1.png') }}" class="me-2" style="width: 28px; height: 28px; object-fit: contain;">Penyewaan Alat
                        <span class="badge {{ $category == 'rental' || $category == 'all' ? 'bg-white text-primary' : 'bg-primary text-white' }} ms-2 shadow-sm">{{ $rentalPayments->count() }}</span>
                    </button>
                </li>
                @endif
                @if($isGasActive)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $category == 'gas' || (!$isRentalActive && ($category == 'rental' || $category == 'all')) ? 'active' : '' }} rounded-pill px-4 fw-semibold" id="gas-tab" data-bs-toggle="tab" data-bs-target="#gas-pane" type="button" role="tab">
                        <img src="{{ asset('User/img/elemen/F2.png') }}" class="me-2" style="width: 28px; height: 28px; object-fit: contain;">Pembelian Gas
                        <span class="badge {{ $category == 'gas' ? 'bg-white text-primary' : 'bg-primary text-white' }} ms-2 shadow-sm">{{ $gasPayments->count() }}</span>
                    </button>
                </li>
                @endif
                @if($isMobilActive)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $category == 'mobil' || (!$isRentalActive && !$isGasActive && ($category == 'rental' || $category == 'all')) ? 'active' : '' }} rounded-pill px-4 fw-semibold" id="mobil-tab" data-bs-toggle="tab" data-bs-target="#mobil-pane" type="button" role="tab">
                        <img src="{{ asset('User/img/elemen/mobil.png') }}" class="me-2" style="width: 28px; height: 28px; object-fit: contain;">Penyewaan Mobil
                        <span class="badge {{ $category == 'mobil' ? 'bg-white text-primary' : 'bg-primary text-white' }} ms-2 shadow-sm">{{ $mobilPayments->count() }}</span>
                    </button>
                </li>
                @endif
                @if($isFasilitasActive)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $category == 'fasilitas' || (!$isRentalActive && !$isGasActive && !$isMobilActive && ($category == 'rental' || $category == 'all')) ? 'active' : '' }} rounded-pill px-4 fw-semibold" id="fasilitas-tab" data-bs-toggle="tab" data-bs-target="#fasilitas-pane" type="button" role="tab">
                        <img src="{{ asset('User/img/elemen/fasilitas.png') }}" class="me-2" style="width: 28px; height: 28px; object-fit: contain;">Fasilitas Umum
                        <span class="badge {{ $category == 'fasilitas' ? 'bg-white text-primary' : 'bg-primary text-white' }} ms-2 shadow-sm">{{ $fasilitasPayments->count() }}</span>
                    </button>
                </li>
                @endif
                @if($isPasarActive)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $category == 'pasar' || (!$isRentalActive && !$isGasActive && !$isMobilActive && !$isFasilitasActive && ($category == 'rental' || $category == 'all')) ? 'active' : '' }} rounded-pill px-4 fw-semibold" id="pasar-tab" data-bs-toggle="tab" data-bs-target="#pasar-pane" type="button" role="tab">
                        <img src="{{ asset('Admin/img/pasardaerah/PasarDaerah2.png') }}" class="me-2" style="width: 28px; height: 28px; object-fit: contain;">Pasar Daerah
                        <span class="badge {{ $category == 'pasar' ? 'bg-white text-primary' : 'bg-primary text-white' }} ms-2 shadow-sm">{{ $pasarPayments->count() }}</span>
                    </button>
                </li>
                @endif
            </ul>
        </div>
        
        <div class="card-body p-0">
             <div class="tab-content p-4" id="proofTabsContent">
                
                <!-- RENTAL TAB -->
                @if($isRentalActive)
                <div class="tab-pane fade {{ $category == 'rental' || $category == 'all' || (!$isRentalActive && $totalActive > 0) ? 'show active' : '' }}" id="rental-pane" role="tabpanel">
                    @if($rentalPayments->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-receipt fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Belum ada bukti pembayaran penyewaan</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-modern align-middle w-100">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Pengguna</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Item Sewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Total Bayar</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Metode</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rentalPayments as $payment)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm border rounded-circle p-1 me-3">
                                                    @if($payment->user && $payment->user->avatar)
                                                        <img src="{{ asset('storage/' . $payment->user->avatar) }}" alt="Av" class="rounded-circle w-100 h-100 object-fit-cover">
                                                    @else
                                                        <span class="avatar-initial rounded-circle bg-primary-subtle text-primary fw-bold">
                                                            {{ strtoupper(substr($payment->user->name ?? 'U', 0, 1)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold text-dark">{{ $payment->full_name ?? $payment->recipient_name ?? $payment->user->name }}</h6>
                                                    <small class="text-muted">{{ $payment->user->email ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $payment->barang->nama_barang ?? 'Alat' }}</div>
                                            <small class="text-muted">{{ $payment->quantity ?? 1 }} Unit</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">Rp {{ number_format($payment->total_amount ?? 0, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @if($payment->payment_method == 'tunai')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Tunai</span>
                                            @elseif($payment->payment_method == 'transfer')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">Transfer</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">{{ ucfirst($payment->payment_method) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $payment->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                {{-- Action Buttons --}}
                                                {{-- Action Buttons Removed --}}


                                                @if($payment->payment_proof)
                                                    <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'rental']) }}" 
                                                       class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 text-primary hover-primary" 
                                                       title="Lihat Bukti" target="_blank">
                                                        <i class="bx bx-image fs-5"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('receipt.rental.view', $payment->id) }}" 
                                                       class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 text-info hover-primary" 
                                                       title="Lihat Struk System" target="_blank">
                                                        <i class="bx bx-receipt fs-5"></i>
                                                    </a>
                                                @endif
                                                
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @endif

                <!-- GAS TAB -->
                @if($isGasActive)
                <div class="tab-pane fade {{ $category == 'gas' || (!$isRentalActive && ($category == 'rental' || $category == 'all')) ? 'show active' : '' }}" id="gas-pane" role="tabpanel">
                      @if($gasPayments->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-receipt fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Belum ada bukti pembayaran gas</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-modern align-middle w-100">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Pembeli</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Produk Gas</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Total Bayar</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Metode</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gasPayments as $payment)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm border rounded-circle p-1 me-3">
                                                    @if($payment->user && $payment->user->avatar)
                                                        <img src="{{ asset('storage/' . $payment->user->avatar) }}" alt="Av" class="rounded-circle w-100 h-100 object-fit-cover">
                                                    @else
                                                        <span class="avatar-initial rounded-circle bg-info-subtle text-info fw-bold">
                                                            {{ strtoupper(substr($payment->full_name ?? $payment->user->name ?? 'U', 0, 1)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold text-dark">{{ $payment->full_name ?? $payment->user->name }}</h6>
                                                    <small class="text-muted">{{ $payment->address ?? $payment->user->address ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $payment->item_name ?? 'Gas LPG' }}</div>
                                            <small class="text-muted">{{ $payment->quantity }} Tabung</small>
                                        </td>
                                        <td>
                                             <span class="fw-bold text-dark">Rp {{ number_format(($payment->price ?? 0) * ($payment->quantity ?? 1), 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @if($payment->payment_method == 'tunai')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Tunai</span>
                                            @elseif($payment->payment_method == 'transfer')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">Transfer</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">{{ ucfirst($payment->payment_method) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $payment->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                {{-- Action Buttons --}}
                                                {{-- Action Buttons Removed --}}

                                                 @if($payment->proof_of_payment)
                                                    <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'gas']) }}" 
                                                       class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 text-primary hover-primary" 
                                                       title="Lihat Bukti" target="_blank">
                                                        <i class="bx bx-image fs-5"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('receipt.gas.view', $payment->id) }}" 
                                                       class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 text-info hover-primary" 
                                                       title="Lihat Struk System" target="_blank">
                                                        <i class="bx bx-receipt fs-5"></i>
                                                    </a>
                                                @endif
                                                
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @endif

                <!-- MOBIL TAB -->
                @if($isMobilActive)
                <div class="tab-pane fade {{ $category == 'mobil' || (!$isRentalActive && !$isGasActive && ($category == 'rental' || $category == 'all')) ? 'show active' : '' }}" id="mobil-pane" role="tabpanel">
                      @if($mobilPayments->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-receipt fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Belum ada bukti pembayaran penyewaan mobil</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-modern align-middle w-100">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Penyewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Mobil Sewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Total Bayar</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Metode</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mobilPayments as $payment)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm border rounded-circle p-1 me-3">
                                                    @if($payment->user && $payment->user->avatar)
                                                        <img src="{{ asset('storage/' . $payment->user->avatar) }}" alt="Av" class="rounded-circle w-100 h-100 object-fit-cover">
                                                    @else
                                                        <span class="avatar-initial rounded-circle bg-primary-subtle text-primary fw-bold">
                                                            {{ strtoupper(substr($payment->user->name ?? 'U', 0, 1)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold text-dark">{{ $payment->user->name }}</h6>
                                                    <small class="text-muted">{{ $payment->user->email ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $payment->mobil->nama_mobil ?? 'Mobil' }}</div>
                                            <small class="text-muted">{{ $payment->lama_sewa }} Hari</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">Rp {{ number_format($payment->total_harga ?? 0, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @if($payment->payment_method == 'tunai')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Tunai</span>
                                            @elseif($payment->payment_method == 'transfer')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">Transfer</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">{{ ucfirst($payment->payment_method ?? 'tunai') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $payment->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                @if($payment->payment_proof)
                                                    <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'mobil']) }}" 
                                                       class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 text-primary hover-primary" 
                                                       title="Lihat Bukti" target="_blank">
                                                        <i class="bx bx-image fs-5"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('receipt.mobil.view', $payment->id) }}" 
                                                       class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 text-info hover-primary" 
                                                       title="Lihat Struk System" target="_blank">
                                                        <i class="bx bx-receipt fs-5"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @endif

                <!-- FASILITAS TAB -->
                @if($isFasilitasActive)
                <div class="tab-pane fade {{ $category == 'fasilitas' || (!$isRentalActive && !$isGasActive && !$isMobilActive && ($category == 'rental' || $category == 'all')) ? 'show active' : '' }}" id="fasilitas-pane" role="tabpanel">
                      @if($fasilitasPayments->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-receipt fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Belum ada bukti pembayaran fasilitas umum</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-modern align-middle w-100">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Penyewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Fasilitas</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Total Bayar</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Metode</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fasilitasPayments as $payment)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm border rounded-circle p-1 me-3">
                                                    @if($payment->user && $payment->user->avatar)
                                                        <img src="{{ asset('storage/' . $payment->user->avatar) }}" alt="Av" class="rounded-circle w-100 h-100 object-fit-cover">
                                                    @else
                                                        <span class="avatar-initial rounded-circle bg-primary-subtle text-primary fw-bold">
                                                            {{ strtoupper(substr($payment->user->name ?? 'U', 0, 1)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold text-dark">{{ $payment->user->name }}</h6>
                                                    <small class="text-muted">{{ $payment->user->email ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $payment->fasilitas->nama_fasilitas ?? 'Fasilitas' }}</div>
                                            <small class="text-muted">{{ $payment->lama_sewa }} Hari</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">Rp {{ number_format($payment->total_harga ?? 0, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @if($payment->payment_method == 'tunai')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Tunai</span>
                                            @elseif($payment->payment_method == 'transfer')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">Transfer</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">{{ ucfirst($payment->payment_method ?? 'tunai') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $payment->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                @if($payment->payment_proof)
                                                    <a href="{{ route('admin.aktivitas.bukti-transaksi.download', [$payment->id, 'fasilitas']) }}" 
                                                       class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 text-primary hover-primary" 
                                                       title="Lihat Bukti" target="_blank">
                                                        <i class="bx bx-image fs-5"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('receipt.fasilitas.view', $payment->id) }}" 
                                                       class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 text-info hover-primary" 
                                                       title="Lihat Struk System" target="_blank">
                                                        <i class="bx bx-receipt fs-5"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @endif

                <!-- PASAR DAERAH TAB -->
                @if($isPasarActive)
                <div class="tab-pane fade {{ $category == 'pasar' || (!$isRentalActive && !$isGasActive && !$isMobilActive && !$isFasilitasActive && ($category == 'rental' || $category == 'all')) ? 'show active' : '' }}" id="pasar-pane" role="tabpanel">
                    @if($pasarPayments->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-store fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Belum ada transaksi Pasar Daerah</h6>
                            <p class="text-muted small mb-0">Transaksi pembayaran akan muncul di sini.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-modern align-middle w-100">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary">Tanggal Pembayaran</th>
                                        <th class="py-3 text-secondary">Pemesan</th>
                                        <th class="py-3 text-secondary">No. Pesanan</th>
                                        <th class="py-3 text-secondary">Total Tagihan</th>
                                        <th class="py-3 text-secondary">Metode</th>
                                        <th class="py-3 text-secondary">Status</th>
                                        <th class="pe-4 py-3 text-secondary text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pasarPayments as $payment)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $payment->updated_at->format('d M Y') }}</div>
                                            <small class="text-muted"><i class="bx bx-time me-1"></i>{{ $payment->updated_at->format('H:i') }} WIB</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm border bg-light rounded-circle p-1 me-2 text-center">
                                                    <i class="bx bx-user text-secondary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">{{ $payment->user->name ?? $payment->full_name }}</h6>
                                                    <small class="text-muted">{{ $payment->user->phone ?? $payment->phone }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $payment->order_number }}</div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">Rp {{ number_format($payment->grand_total ?? 0, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $method = strtolower($payment->payment_method ?? 'tunai');
                                                $methodStr = ucfirst($method);
                                                $badgeClass = 'bg-secondary-subtle text-secondary border-secondary-subtle';
                                                
                                                if (str_contains($method, 'tunai') || str_contains($method, 'cash')) {
                                                    $badgeClass = 'bg-success-subtle text-success border-success-subtle';
                                                    $methodStr = 'Tunai';
                                                } elseif (str_contains($method, 'transfer') || str_contains($method, 'bank')) {
                                                    $badgeClass = 'bg-primary-subtle text-primary border-primary-subtle';
                                                } elseif ($method != 'tunai') {
                                                    $badgeClass = 'bg-info-subtle text-info border-info-subtle';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }} border rounded-pill px-3">{{ $methodStr }}</span>
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $payment->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                @if($payment->proof_of_payment)
                                                    <a href="{{ Storage::url($payment->proof_of_payment) }}" 
                                                       class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 text-primary hover-primary" 
                                                       title="Lihat Bukti" target="_blank">
                                                        <i class="bx bx-image fs-5"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted small"><em>Sistem</em></span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

@endsection
