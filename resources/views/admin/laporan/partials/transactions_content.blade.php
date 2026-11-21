@php
$activeServices = $activeServices ?? [];
$isRentalActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'alat'));
$isGasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'gas'));
$isMobilActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'mobil') || str_contains(strtolower($name), 'transportasi'));
$isFasilitasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'fasilitas'));
$totalActive = collect([$isRentalActive, $isGasActive, $isMobilActive, $isFasilitasActive])->filter()->count();

$allTrxCount = ($isRentalActive ? $rentalRequests->count() : 0)
    + ($isGasActive ? $gasOrders->count() : 0)
    + ($isMobilActive ? $mobilBookings->count() : 0)
    + ($isFasilitasActive ? $fasilitasBookings->count() : 0);

$completedCount = ($isRentalActive ? $rentalRequests->where('status', 'completed')->count() : 0)
    + ($isGasActive ? $gasOrders->where('status', 'completed')->count() : 0)
    + ($isMobilActive ? $mobilBookings->where('status', 'completed')->count() : 0)
    + ($isFasilitasActive ? $fasilitasBookings->where('status', 'completed')->count() : 0);

$pendingCount = ($isRentalActive ? $rentalRequests->whereIn('status', ['pending', 'waiting'])->count() : 0)
    + ($isGasActive ? $gasOrders->whereIn('status', ['pending', 'waiting'])->count() : 0)
    + ($isMobilActive ? $mobilBookings->whereIn('status', ['pending', 'waiting'])->count() : 0)
    + ($isFasilitasActive ? $fasilitasBookings->whereIn('status', ['pending', 'waiting'])->count() : 0);

$firstTab = $isRentalActive ? 'rental' : ($isGasActive ? 'gas' : ($isMobilActive ? 'mobil' : ($isFasilitasActive ? 'fasilitas' : '')));
@endphp

@if($totalActive === 0)
    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center mt-2">
        <div class="avatar avatar-lg bg-label-warning text-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
            <i class="bx bx-info-circle fs-1"></i>
        </div>
        <h5 class="fw-bold text-dark mb-2">Belum Ada Layanan yang Diaktifkan</h5>
        <p class="text-muted mb-3">Aktifkan minimal satu unit layanan desa terlebih dahulu untuk melihat laporan transaksi.</p>
        @if(auth()->user()->role === 'admin_desa')
            <div>
                <a href="{{ route('admin.region-settings.index') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="bx bx-slider-alt me-1"></i>Buka Aktivasi Layanan
                </a>
            </div>
        @endif
    </div>
@else
    <!-- Ringkasan Utama -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="trx-stat-card shadow-sm h-100 p-3 d-flex align-items-center gap-3">
                <div class="trx-stat-icon bg-label-primary text-primary">
                    <i class="bx bx-receipt"></i>
                </div>
                <div class="min-w-0">
                    <small class="text-muted fw-semibold d-block text-truncate">Total Transaksi</small>
                    <h4 class="fw-bold text-dark mb-0">
                        <span class="count-up" data-value="{{ $allTrxCount }}">{{ $allTrxCount }}</span>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="trx-stat-card shadow-sm h-100 p-3 d-flex align-items-center gap-3">
                <div class="trx-stat-icon bg-label-success text-success">
                    <i class="bx bx-check-double"></i>
                </div>
                <div class="min-w-0">
                    <small class="text-muted fw-semibold d-block text-truncate">Selesai</small>
                    <h4 class="fw-bold text-success mb-0">
                        <span class="count-up" data-value="{{ $completedCount }}">{{ $completedCount }}</span>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="trx-stat-card shadow-sm h-100 p-3 d-flex align-items-center gap-3">
                <div class="trx-stat-icon bg-label-warning text-warning">
                    <i class="bx bx-time-five"></i>
                </div>
                <div class="min-w-0">
                    <small class="text-muted fw-semibold d-block text-truncate">Menunggu Proses</small>
                    <h4 class="fw-bold text-warning mb-0">
                        <span class="count-up" data-value="{{ $pendingCount }}">{{ $pendingCount }}</span>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4">
            <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-3">
                <!-- Service Tabs -->
                <div class="tabs-scroll-wrapper flex-grow-1">
                    <ul class="nav nav-pills flex-nowrap gap-2 mb-0" id="reportTabs" role="tablist">
                        @if($isRentalActive)
                        <li class="nav-item flex-shrink-0" role="presentation">
                            <button class="nav-link {{ $firstTab === 'rental' ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold text-nowrap d-inline-flex align-items-center" id="rental-tab" data-bs-toggle="tab" data-bs-target="#rental-pane" type="button" role="tab">
                                <img src="{{ asset('User/img/elemen/F1.png') }}" class="me-2" style="width: 18px; height: 18px; object-fit: contain;" alt="">
                                <span>Penyewaan Alat</span>
                                <span class="badge trx-tab-count bg-label-primary ms-2 rounded-pill">{{ $rentalRequests->count() }}</span>
                            </button>
                        </li>
                        @endif
                        @if($isGasActive)
                        <li class="nav-item flex-shrink-0" role="presentation">
                            <button class="nav-link {{ $firstTab === 'gas' ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold text-nowrap d-inline-flex align-items-center" id="gas-tab" data-bs-toggle="tab" data-bs-target="#gas-pane" type="button" role="tab">
                                <img src="{{ asset('User/img/elemen/F2.png') }}" class="me-2" style="width: 18px; height: 18px; object-fit: contain;" alt="">
                                <span>Penjualan Gas</span>
                                <span class="badge trx-tab-count bg-label-primary ms-2 rounded-pill">{{ $gasOrders->count() }}</span>
                            </button>
                        </li>
                        @endif
                        @if($isMobilActive)
                        <li class="nav-item flex-shrink-0" role="presentation">
                            <button class="nav-link {{ $firstTab === 'mobil' ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold text-nowrap d-inline-flex align-items-center" id="mobil-tab" data-bs-toggle="tab" data-bs-target="#mobil-pane" type="button" role="tab">
                                <img src="{{ asset('User/img/elemen/mobil.png') }}" class="me-2" style="width: 18px; height: 18px; object-fit: contain;" alt="">
                                <span>Transportasi</span>
                                <span class="badge trx-tab-count bg-label-primary ms-2 rounded-pill">{{ $mobilBookings->count() }}</span>
                            </button>
                        </li>
                        @endif
                        @if($isFasilitasActive)
                        <li class="nav-item flex-shrink-0" role="presentation">
                            <button class="nav-link {{ $firstTab === 'fasilitas' ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold text-nowrap d-inline-flex align-items-center" id="fasilitas-tab" data-bs-toggle="tab" data-bs-target="#fasilitas-pane" type="button" role="tab">
                                <img src="{{ asset('User/img/elemen/fasilitas.png') }}" class="me-2" style="width: 18px; height: 18px; object-fit: contain;" alt="">
                                <span>Fasilitas Umum</span>
                                <span class="badge trx-tab-count bg-label-primary ms-2 rounded-pill">{{ $fasilitasBookings->count() }}</span>
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- Quick Search -->
                <div class="no-print" style="min-width: 220px;">
                    <div class="input-group input-group-sm input-group-merge rounded-pill overflow-hidden border">
                        <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                        <input type="text" id="trx-quick-search" class="form-control bg-light border-0 ps-1" placeholder="Cari no. pesanan, nama, item...">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="reportTabsContent">

                {{-- 1. PENYEWAAN ALAT --}}
                @if($isRentalActive)
                <div class="tab-pane fade {{ $firstTab === 'rental' ? 'show active' : '' }}" id="rental-pane" role="tabpanel">
                    @if($rentalRequests->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bx bx-wrench fs-2 opacity-50"></i>
                            </div>
                            <h6 class="text-dark fw-bold mb-1">Tidak Ada Transaksi Penyewaan Alat</h6>
                            <p class="text-muted small mb-0">Belum ada pesanan yang sesuai dengan filter saat ini.</p>
                        </div>
                    @else
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Pesanan &amp; Tanggal</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Penyewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Alat</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Subtotal / Dibayar</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold no-print">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rentalRequests as $req)
                                    @php
                                        $custName = $req->recipient_name ?? $req->user->name ?? 'Warga';
                                        $itemLabel = $req->barang->nama_barang ?? $req->item_name ?? 'Alat';
                                        $subAmt = $req->subtotalAmount();
                                        $paidAmt = $req->paidAmount();
                                        $payLabel = $req->paymentStatusLabel();
                                        $payBadge = $req->paymentStatusBadgeClass();
                                        $searchKey = strtolower(($req->order_number ?? $req->id) . ' ' . $custName . ' ' . $itemLabel);
                                    @endphp
                                    <tr class="trx-searchable-item" data-search="{{ $searchKey }}">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">#{{ $req->order_number ?? $req->id }}</div>
                                            <small class="text-muted">{{ $req->created_at->format('d M Y, H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="avatar avatar-sm rounded-circle bg-label-primary text-primary fw-bold d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                                    {{ strtoupper(substr($custName, 0, 1)) }}
                                                </span>
                                                <span class="fw-medium text-dark">{{ $custName }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $itemLabel }}</div>
                                            <small class="text-muted">{{ $req->quantity }} Unit</small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">Rp {{ number_format($subAmt, 0, ',', '.') }}</div>
                                            <div class="small mt-1 d-flex flex-wrap align-items-center gap-1">
                                                <span class="text-muted">Dibayar:</span>
                                                <span class="fw-semibold {{ $paidAmt >= $subAmt && $subAmt > 0 ? 'text-success' : ($paidAmt > 0 ? 'text-danger' : 'text-muted') }}">
                                                    Rp {{ number_format($paidAmt, 0, ',', '.') }}
                                                </span>
                                                <span class="badge {{ $payBadge }} rounded-pill" style="font-size: 0.65rem;">{{ $payLabel }}</span>
                                            </div>
                                        </td>
                                        <td>@include('admin.partials.status-badge', ['status' => $req->status])</td>
                                        <td class="text-end pe-4 no-print">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$req->id, 'rental']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bx bx-show me-1"></i>Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Mobile Cards -->
                        <div class="d-md-none p-3">
                            @foreach($rentalRequests as $req)
                            @php
                                $custName = $req->recipient_name ?? $req->user->name ?? 'Warga';
                                $itemLabel = $req->barang->nama_barang ?? $req->item_name ?? 'Alat';
                                $subAmt = $req->subtotalAmount();
                                $paidAmt = $req->paidAmount();
                                $payLabel = $req->paymentStatusLabel();
                                $payBadge = $req->paymentStatusBadgeClass();
                                $searchKey = strtolower(($req->order_number ?? $req->id) . ' ' . $custName . ' ' . $itemLabel);
                            @endphp
                            <div class="trx-mobile-card shadow-xs trx-searchable-item" data-search="{{ $searchKey }}">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="fw-bold text-dark">#{{ $req->order_number ?? $req->id }}</div>
                                        <small class="text-muted">{{ $req->created_at->format('d M Y, H:i') }}</small>
                                    </div>
                                    <div>@include('admin.partials.status-badge', ['status' => $req->status])</div>
                                </div>
                                <div class="mb-2">
                                    <div class="fw-semibold text-dark">{{ $itemLabel }} <span class="text-muted fw-normal">({{ $req->quantity }} Unit)</span></div>
                                    <small class="text-muted"><i class="bx bx-user me-1"></i>{{ $custName }}</small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top gap-2">
                                    <div>
                                        <div class="fw-bold text-dark">Subtotal: Rp {{ number_format($subAmt, 0, ',', '.') }}</div>
                                        <small class="d-block">
                                            Dibayar: <strong class="{{ $paidAmt >= $subAmt && $subAmt > 0 ? 'text-success' : ($paidAmt > 0 ? 'text-danger' : 'text-muted') }}">Rp {{ number_format($paidAmt, 0, ',', '.') }}</strong>
                                            <span class="badge {{ $payBadge }} rounded-pill ms-1" style="font-size: 0.64rem;">{{ $payLabel }}</span>
                                        </small>
                                    </div>
                                    <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$req->id, 'rental']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 no-print flex-shrink-0">
                                        Detail <i class="bx bx-right-arrow-alt"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif

                {{-- 2. PENJUALAN GAS --}}
                @if($isGasActive)
                <div class="tab-pane fade {{ $firstTab === 'gas' ? 'show active' : '' }}" id="gas-pane" role="tabpanel">
                    @if($gasOrders->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bx bx-gas-pump fs-2 opacity-50"></i>
                            </div>
                            <h6 class="text-dark fw-bold mb-1">Tidak Ada Transaksi Penjualan Gas</h6>
                            <p class="text-muted small mb-0">Belum ada pesanan gas yang sesuai dengan filter saat ini.</p>
                        </div>
                    @else
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Pesanan &amp; Tanggal</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Pembeli</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Produk</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Subtotal / Dibayar</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold no-print">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gasOrders as $order)
                                    @php
                                        $custName = $order->full_name ?? $order->user->name ?? 'Warga';
                                        $subAmt = $order->subtotalAmount();
                                        $paidAmt = $order->paidAmount();
                                        $payLabel = $order->paymentStatusLabel();
                                        $payBadge = $order->paymentStatusBadgeClass();
                                        $searchKey = strtolower(($order->order_number ?? $order->id) . ' ' . $custName . ' ' . ($order->item_name ?? 'Gas LPG'));
                                    @endphp
                                    <tr class="trx-searchable-item" data-search="{{ $searchKey }}">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">#{{ $order->order_number ?? $order->id }}</div>
                                            <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="avatar avatar-sm rounded-circle bg-label-info text-info fw-bold d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                                    {{ strtoupper(substr($custName, 0, 1)) }}
                                                </span>
                                                <span class="fw-medium text-dark">{{ $custName }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $order->item_name ?? 'Gas LPG' }}</div>
                                            <small class="text-muted">{{ $order->quantity }} Tabung &times; Rp {{ number_format($order->price ?? 0, 0, ',', '.') }}</small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">Rp {{ number_format($subAmt, 0, ',', '.') }}</div>
                                            <div class="small mt-1 d-flex flex-wrap align-items-center gap-1">
                                                <span class="text-muted">Dibayar:</span>
                                                <span class="fw-semibold {{ $paidAmt >= $subAmt && $subAmt > 0 ? 'text-success' : ($paidAmt > 0 ? 'text-danger' : 'text-muted') }}">
                                                    Rp {{ number_format($paidAmt, 0, ',', '.') }}
                                                </span>
                                                <span class="badge {{ $payBadge }} rounded-pill" style="font-size: 0.65rem;">{{ $payLabel }}</span>
                                            </div>
                                        </td>
                                        <td>@include('admin.partials.status-badge', ['status' => $order->status])</td>
                                        <td class="text-end pe-4 no-print">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'gas']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bx bx-show me-1"></i>Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Mobile Cards -->
                        <div class="d-md-none p-3">
                            @foreach($gasOrders as $order)
                            @php
                                $custName = $order->full_name ?? $order->user->name ?? 'Warga';
                                $subAmt = $order->subtotalAmount();
                                $paidAmt = $order->paidAmount();
                                $payLabel = $order->paymentStatusLabel();
                                $payBadge = $order->paymentStatusBadgeClass();
                                $searchKey = strtolower(($order->order_number ?? $order->id) . ' ' . $custName . ' ' . ($order->item_name ?? 'Gas LPG'));
                            @endphp
                            <div class="trx-mobile-card shadow-xs trx-searchable-item" data-search="{{ $searchKey }}">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="fw-bold text-dark">#{{ $order->order_number ?? $order->id }}</div>
                                        <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                                    </div>
                                    <div>@include('admin.partials.status-badge', ['status' => $order->status])</div>
                                </div>
                                <div class="mb-2">
                                    <div class="fw-semibold text-dark">{{ $order->item_name ?? 'Gas LPG' }} <span class="text-muted fw-normal">({{ $order->quantity }} Tabung)</span></div>
                                    <small class="text-muted"><i class="bx bx-user me-1"></i>{{ $custName }}</small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top gap-2">
                                    <div>
                                        <div class="fw-bold text-dark">Subtotal: Rp {{ number_format($subAmt, 0, ',', '.') }}</div>
                                        <small class="d-block">
                                            Dibayar: <strong class="{{ $paidAmt >= $subAmt && $subAmt > 0 ? 'text-success' : ($paidAmt > 0 ? 'text-danger' : 'text-muted') }}">Rp {{ number_format($paidAmt, 0, ',', '.') }}</strong>
                                            <span class="badge {{ $payBadge }} rounded-pill ms-1" style="font-size: 0.64rem;">{{ $payLabel }}</span>
                                        </small>
                                    </div>
                                    <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'gas']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 no-print flex-shrink-0">
                                        Detail <i class="bx bx-right-arrow-alt"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif

                {{-- 3. TRANSPORTASI --}}
                @if($isMobilActive)
                <div class="tab-pane fade {{ $firstTab === 'mobil' ? 'show active' : '' }}" id="mobil-pane" role="tabpanel">
                    @if($mobilBookings->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bx bx-car fs-2 opacity-50"></i>
                            </div>
                            <h6 class="text-dark fw-bold mb-1">Tidak Ada Transaksi Sewa Transportasi</h6>
                            <p class="text-muted small mb-0">Belum ada booking kendaraan yang sesuai dengan filter saat ini.</p>
                        </div>
                    @else
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Pesanan &amp; Tanggal</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Penyewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Kendaraan</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Subtotal / Dibayar</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold no-print">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mobilBookings as $order)
                                    @php
                                        $custName = $order->recipient_name ?? $order->user->name ?? 'Warga';
                                        $subAmt = $order->subtotalAmount();
                                        $paidAmt = $order->paidAmount();
                                        $payLabel = $order->paymentStatusLabel();
                                        $payBadge = $order->paymentStatusBadgeClass();
                                        $searchKey = strtolower(($order->order_number ?? $order->id) . ' ' . $custName . ' ' . ($order->mobil->nama_mobil ?? 'Sewa Transportasi'));
                                    @endphp
                                    <tr class="trx-searchable-item" data-search="{{ $searchKey }}">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">#{{ $order->order_number ?? $order->id }}</div>
                                            <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="avatar avatar-sm rounded-circle bg-label-danger text-danger fw-bold d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                                    {{ strtoupper(substr($custName, 0, 1)) }}
                                                </span>
                                                <span class="fw-medium text-dark">{{ $custName }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $order->mobil->nama_mobil ?? 'Sewa Transportasi' }}</div>
                                            <small class="text-muted">{{ $order->lama_sewa }} Hari</small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">Rp {{ number_format($subAmt, 0, ',', '.') }}</div>
                                            <div class="small mt-1 d-flex flex-wrap align-items-center gap-1">
                                                <span class="text-muted">Dibayar:</span>
                                                <span class="fw-semibold {{ $paidAmt >= $subAmt && $subAmt > 0 ? 'text-success' : ($paidAmt > 0 ? 'text-danger' : 'text-muted') }}">
                                                    Rp {{ number_format($paidAmt, 0, ',', '.') }}
                                                </span>
                                                <span class="badge {{ $payBadge }} rounded-pill" style="font-size: 0.65rem;">{{ $payLabel }}</span>
                                            </div>
                                        </td>
                                        <td>@include('admin.partials.status-badge', ['status' => $order->status])</td>
                                        <td class="text-end pe-4 no-print">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'mobil']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bx bx-show me-1"></i>Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Mobile Cards -->
                        <div class="d-md-none p-3">
                            @foreach($mobilBookings as $order)
                            @php
                                $custName = $order->recipient_name ?? $order->user->name ?? 'Warga';
                                $subAmt = $order->subtotalAmount();
                                $paidAmt = $order->paidAmount();
                                $payLabel = $order->paymentStatusLabel();
                                $payBadge = $order->paymentStatusBadgeClass();
                                $searchKey = strtolower(($order->order_number ?? $order->id) . ' ' . $custName . ' ' . ($order->mobil->nama_mobil ?? 'Sewa Transportasi'));
                            @endphp
                            <div class="trx-mobile-card shadow-xs trx-searchable-item" data-search="{{ $searchKey }}">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="fw-bold text-dark">#{{ $order->order_number ?? $order->id }}</div>
                                        <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                                    </div>
                                    <div>@include('admin.partials.status-badge', ['status' => $order->status])</div>
                                </div>
                                <div class="mb-2">
                                    <div class="fw-semibold text-dark">{{ $order->mobil->nama_mobil ?? 'Sewa Transportasi' }} <span class="text-muted fw-normal">({{ $order->lama_sewa }} Hari)</span></div>
                                    <small class="text-muted"><i class="bx bx-user me-1"></i>{{ $custName }}</small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top gap-2">
                                    <div>
                                        <div class="fw-bold text-dark">Subtotal: Rp {{ number_format($subAmt, 0, ',', '.') }}</div>
                                        <small class="d-block">
                                            Dibayar: <strong class="{{ $paidAmt >= $subAmt && $subAmt > 0 ? 'text-success' : ($paidAmt > 0 ? 'text-danger' : 'text-muted') }}">Rp {{ number_format($paidAmt, 0, ',', '.') }}</strong>
                                            <span class="badge {{ $payBadge }} rounded-pill ms-1" style="font-size: 0.64rem;">{{ $payLabel }}</span>
                                        </small>
                                    </div>
                                    <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'mobil']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 no-print flex-shrink-0">
                                        Detail <i class="bx bx-right-arrow-alt"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif

                {{-- 4. FASILITAS UMUM --}}
                @if($isFasilitasActive)
                <div class="tab-pane fade {{ $firstTab === 'fasilitas' ? 'show active' : '' }}" id="fasilitas-pane" role="tabpanel">
                    @if($fasilitasBookings->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bx bx-buildings fs-2 opacity-50"></i>
                            </div>
                            <h6 class="text-dark fw-bold mb-1">Tidak Ada Transaksi Fasilitas Umum</h6>
                            <p class="text-muted small mb-0">Belum ada peminjaman fasilitas yang sesuai dengan filter saat ini.</p>
                        </div>
                    @else
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Pesanan &amp; Tanggal</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Peminjam</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Fasilitas</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Subtotal / Dibayar</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold no-print">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fasilitasBookings as $order)
                                    @php
                                        $custName = $order->recipient_name ?? $order->user->name ?? 'Warga';
                                        $subAmt = $order->subtotalAmount();
                                        $paidAmt = $order->paidAmount();
                                        $payLabel = $order->paymentStatusLabel();
                                        $payBadge = $order->paymentStatusBadgeClass();
                                        $searchKey = strtolower(($order->order_number ?? $order->id) . ' ' . $custName . ' ' . ($order->fasilitas->nama_fasilitas ?? 'Fasilitas Umum'));
                                    @endphp
                                    <tr class="trx-searchable-item" data-search="{{ $searchKey }}">
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">#{{ $order->order_number ?? $order->id }}</div>
                                            <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="avatar avatar-sm rounded-circle bg-label-success text-success fw-bold d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                                    {{ strtoupper(substr($custName, 0, 1)) }}
                                                </span>
                                                <span class="fw-medium text-dark">{{ $custName }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $order->fasilitas->nama_fasilitas ?? 'Fasilitas Umum' }}</div>
                                            <small class="text-muted">{{ $order->lama_sewa }} Hari</small>
                                        </td>
                                        <td>
                                            @if($subAmt > 0)
                                                <div class="fw-bold text-dark">Rp {{ number_format($subAmt, 0, ',', '.') }}</div>
                                                <div class="small mt-1 d-flex flex-wrap align-items-center gap-1">
                                                    <span class="text-muted">Dibayar:</span>
                                                    <span class="fw-semibold {{ $paidAmt >= $subAmt ? 'text-success' : ($paidAmt > 0 ? 'text-danger' : 'text-muted') }}">
                                                        Rp {{ number_format($paidAmt, 0, ',', '.') }}
                                                    </span>
                                                    <span class="badge {{ $payBadge }} rounded-pill" style="font-size: 0.65rem;">{{ $payLabel }}</span>
                                                </div>
                                            @else
                                                <span class="badge bg-label-info rounded-pill">Gratis</span>
                                            @endif
                                        </td>
                                        <td>@include('admin.partials.status-badge', ['status' => $order->status])</td>
                                        <td class="text-end pe-4 no-print">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'fasilitas']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bx bx-show me-1"></i>Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- Mobile Cards -->
                        <div class="d-md-none p-3">
                            @foreach($fasilitasBookings as $order)
                            @php
                                $custName = $order->recipient_name ?? $order->user->name ?? 'Warga';
                                $subAmt = $order->subtotalAmount();
                                $paidAmt = $order->paidAmount();
                                $payLabel = $order->paymentStatusLabel();
                                $payBadge = $order->paymentStatusBadgeClass();
                                $searchKey = strtolower(($order->order_number ?? $order->id) . ' ' . $custName . ' ' . ($order->fasilitas->nama_fasilitas ?? 'Fasilitas Umum'));
                            @endphp
                            <div class="trx-mobile-card shadow-xs trx-searchable-item" data-search="{{ $searchKey }}">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="fw-bold text-dark">#{{ $order->order_number ?? $order->id }}</div>
                                        <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                                    </div>
                                    <div>@include('admin.partials.status-badge', ['status' => $order->status])</div>
                                </div>
                                <div class="mb-2">
                                    <div class="fw-semibold text-dark">{{ $order->fasilitas->nama_fasilitas ?? 'Fasilitas Umum' }} <span class="text-muted fw-normal">({{ $order->lama_sewa }} Hari)</span></div>
                                    <small class="text-muted"><i class="bx bx-user me-1"></i>{{ $custName }}</small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top gap-2">
                                    <div>
                                        @if($subAmt > 0)
                                            <div class="fw-bold text-dark">Subtotal: Rp {{ number_format($subAmt, 0, ',', '.') }}</div>
                                            <small class="d-block">
                                                Dibayar: <strong class="{{ $paidAmt >= $subAmt ? 'text-success' : ($paidAmt > 0 ? 'text-danger' : 'text-muted') }}">Rp {{ number_format($paidAmt, 0, ',', '.') }}</strong>
                                                <span class="badge {{ $payBadge }} rounded-pill ms-1" style="font-size: 0.64rem;">{{ $payLabel }}</span>
                                            </small>
                                        @else
                                            <span class="badge bg-label-info rounded-pill">Gratis</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'fasilitas']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 no-print flex-shrink-0">
                                        Detail <i class="bx bx-right-arrow-alt"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif

            </div>
        </div>
    </div>
@endif
