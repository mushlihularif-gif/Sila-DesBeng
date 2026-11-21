@php
$activeServices = $activeServices ?? [];
$isRentalActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'alat'));
$isGasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'gas'));
$isMobilActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'mobil') || str_contains(strtolower($name), 'transportasi'));
$isFasilitasActive = collect($activeServices)->contains(fn($name) => str_contains(strtolower($name), 'fasilitas'));
$totalActive = collect([$isRentalActive, $isGasActive, $isMobilActive, $isFasilitasActive])->filter()->count();
@endphp

@if($totalActive === 0)
    <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
        <div class="avatar avatar-lg bg-label-warning text-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
            <i class="bx bx-info-circle fs-1"></i>
        </div>
        <h5 class="fw-bold text-dark mb-1">Belum Ada Unit Layanan Aktif</h5>
        <p class="text-muted small mb-0">Silakan aktifkan minimal satu unit layanan pada menu Aktivasi &amp; Katalog Unit.</p>
    </div>
@else
    <!-- Statistik Pesanan (Konsisten dengan Dashboard Admin) -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl">
            <div class="req-stat-card p-3 h-100 shadow-sm d-flex align-items-center gap-3">
                <div class="avatar avatar-md bg-label-primary text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bx bx-shopping-bag fs-4"></i>
                </div>
                <div class="min-w-0">
                    <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Total Pesanan</small>
                    <h4 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['total'] }}">{{ $stats['total'] }}</span></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-xl">
            <div class="req-stat-card p-3 h-100 shadow-sm d-flex align-items-center gap-3">
                <div class="avatar avatar-md bg-label-warning text-warning rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bx bx-time-five fs-4"></i>
                </div>
                <div class="min-w-0">
                    <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Menunggu</small>
                    <h4 class="fw-bold mb-0 text-warning"><span class="count-up" data-value="{{ $stats['pending'] }}">{{ $stats['pending'] }}</span></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-xl">
            <div class="req-stat-card p-3 h-100 shadow-sm d-flex align-items-center gap-3">
                <div class="avatar avatar-md bg-label-info text-info rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bx bx-package fs-4"></i>
                </div>
                <div class="min-w-0">
                    <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Sedang Proses</small>
                    <h4 class="fw-bold mb-0 text-info"><span class="count-up" data-value="{{ $stats['active_rental_count'] }}">{{ $stats['active_rental_count'] }}</span></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-xl">
            <div class="req-stat-card p-3 h-100 shadow-sm d-flex align-items-center gap-3">
                <div class="avatar avatar-md bg-label-secondary text-secondary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bx bx-x-circle fs-4"></i>
                </div>
                <div class="min-w-0">
                    <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Ditolak / Batal</small>
                    <h4 class="fw-bold mb-0 text-secondary"><span class="count-up" data-value="{{ $stats['rejected'] }}">{{ $stats['rejected'] }}</span></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-xl">
            <div class="req-stat-card p-3 h-100 shadow-sm d-flex align-items-center gap-3">
                <div class="avatar avatar-md bg-label-danger text-danger rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="bx bx-error-circle fs-4"></i>
                </div>
                <div class="min-w-0">
                    <small class="text-muted text-uppercase fw-bold d-block text-truncate" style="font-size: 0.68rem;">Minta Batal</small>
                    <h4 class="fw-bold mb-0 text-danger"><span class="count-up" data-value="{{ $stats['cancellation_pending'] }}">{{ $stats['cancellation_pending'] }}</span></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar Filter Status + Pencarian Cepat -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-3">
            <ul class="nav nav-pills d-flex flex-nowrap overflow-x-auto no-scrollbar gap-2 mb-0 pb-1 pb-lg-0" style="-webkit-overflow-scrolling: touch;">
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link filter-btn filter-btn-primary filter-status-btn {{ request('status', 'all') == 'all' ? 'active' : '' }}" data-status-filter="all">
                        <i class="bx bx-list-ul me-1"></i> Semua
                    </button>
                </li>
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link filter-btn filter-btn-warning filter-status-btn {{ request('status') == 'pending' ? 'active' : '' }}" data-status-filter="pending">
                        <i class="bx bx-time me-1"></i> Menunggu
                    </button>
                </li>
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link filter-btn filter-btn-info filter-status-btn {{ request('status') == 'in_process' ? 'active' : '' }}" data-status-filter="in_process">
                        <i class="bx bx-loader-circle me-1"></i> Sedang Proses
                    </button>
                </li>
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link filter-btn filter-btn-success filter-status-btn {{ request('status') == 'completed' ? 'active' : '' }}" data-status-filter="completed">
                        <i class="bx bx-check-circle me-1"></i> Selesai
                    </button>
                </li>
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link filter-btn filter-btn-danger filter-status-btn {{ request('status') == 'rejected' ? 'active' : '' }}" data-status-filter="rejected">
                        <i class="bx bx-x-circle me-1"></i> Ditolak
                    </button>
                </li>
                <li class="nav-item flex-shrink-0">
                    <button type="button" class="nav-link filter-btn filter-btn-danger filter-status-btn {{ request('status') == 'cancellation_pending' ? 'active' : '' }}" data-status-filter="cancellation_pending">
                        <i class="bx bx-shield-x me-1"></i> Minta Batal
                    </button>
                </li>
            </ul>

            <div class="input-group input-group-merge input-group-sm" style="max-width: 320px;">
                <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                <input type="text" id="searchOrderInput" class="form-control bg-light border-0" placeholder="Cari nama warga, item, atau tanggal...">
            </div>
        </div>
    </div>

    <!-- Tab Konten Utama -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2">
            <!-- Order Tabs Nav -->
            <ul class="nav nav-pills d-flex flex-nowrap overflow-x-auto no-scrollbar gap-2 mb-0 pb-1 pb-md-0" id="orderTabs" role="tablist" style="-webkit-overflow-scrolling: touch;">
                @if($isRentalActive)
                <li class="nav-item flex-shrink-0" role="presentation">
                    <button class="nav-link {{ $activeTab == 'rental' || (!$isRentalActive && $totalActive > 0) ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center text-nowrap" id="rental-tab" data-bs-toggle="tab" data-bs-target="#rental-pane" type="button" role="tab">
                        <img src="{{ asset('User/img/elemen/F1.png') }}" class="me-2" style="width: 22px; height: 22px; object-fit: contain;">
                        <span>Penyewaan Alat</span>
                        @php $rentalTotal = $notificationCounts['rental']['total'] ?? 0; @endphp
                        @php $rentalCount = $rentalTotal > 0 ? $rentalTotal : $rentalRequests->count(); @endphp
                        <span id="rental-badge" 
                              class="badge {{ $rentalTotal > 0 ? 'bg-danger text-white' : 'bg-white text-primary' }} ms-2 shadow-sm"
                              data-default-count="{{ $rentalRequests->count() }}">
                            {{ $rentalCount }}
                        </span>
                    </button>
                </li>
                @endif
                @if($isGasActive)
                <li class="nav-item flex-shrink-0" role="presentation">
                    <button class="nav-link {{ $activeTab == 'gas' || (!$isRentalActive && $activeTab == 'rental') ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center text-nowrap" id="gas-tab" data-bs-toggle="tab" data-bs-target="#gas-pane" type="button" role="tab">
                        <img src="{{ asset('User/img/elemen/F2.png') }}" class="me-2" style="width: 22px; height: 22px; object-fit: contain;">
                        <span>Pembelian Gas</span>
                        @php $gasTotal = $notificationCounts['gas']['total'] ?? 0; @endphp
                        @php $gasCount = $gasTotal > 0 ? $gasTotal : $gasOrders->count(); @endphp
                        <span id="gas-badge" 
                              class="badge {{ $gasTotal > 0 ? 'bg-danger text-white' : 'bg-white text-primary' }} ms-2 shadow-sm"
                              data-default-count="{{ $gasOrders->count() }}">
                            {{ $gasCount }}
                        </span>
                    </button>
                </li>
                @endif
                @if($isMobilActive)
                <li class="nav-item flex-shrink-0" role="presentation">
                    <button class="nav-link {{ $activeTab == 'mobil' || (!$isRentalActive && !$isGasActive && $activeTab == 'rental') ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center text-nowrap" id="mobil-tab" data-bs-toggle="tab" data-bs-target="#mobil-pane" type="button" role="tab">
                        <img src="{{ asset('User/img/elemen/mobil.png') }}" class="me-2" style="width: 22px; height: 22px; object-fit: contain;">
                        <span>Transportasi</span>
                        @php $mobilTotal = $notificationCounts['mobil']['total'] ?? 0; @endphp
                        @php $mobilCount = $mobilTotal > 0 ? $mobilTotal : $mobilRequests->count(); @endphp
                        <span id="mobil-badge" 
                              class="badge {{ $mobilTotal > 0 ? 'bg-danger text-white' : 'bg-white text-primary' }} ms-2 shadow-sm"
                              data-default-count="{{ $mobilRequests->count() }}">
                            {{ $mobilCount }}
                        </span>
                    </button>
                </li>
                @endif
                @if($isFasilitasActive)
                <li class="nav-item flex-shrink-0" role="presentation">
                    <button class="nav-link {{ $activeTab == 'fasilitas' || (!$isRentalActive && !$isGasActive && !$isMobilActive && $activeTab == 'rental') ? 'active' : '' }} rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center text-nowrap" id="fasilitas-tab" data-bs-toggle="tab" data-bs-target="#fasilitas-pane" type="button" role="tab">
                        <img src="{{ asset('User/img/elemen/fasilitas.png') }}" class="me-2" style="width: 22px; height: 22px; object-fit: contain;">
                        <span>Fasilitas Umum</span>
                        @php $fasilitasTotal = $notificationCounts['fasilitas']['total'] ?? 0; @endphp
                        @php $fasilitasCount = $fasilitasTotal > 0 ? $fasilitasTotal : $fasilitasRequests->count(); @endphp
                        <span id="fasilitas-badge" 
                              class="badge {{ $fasilitasTotal > 0 ? 'bg-danger text-white' : 'bg-white text-primary' }} ms-2 shadow-sm"
                              data-default-count="{{ $fasilitasRequests->count() }}">
                            {{ $fasilitasCount }}
                        </span>
                    </button>
                </li>
                @endif
            </ul>

            {{-- Area Teks Notifikasi --}}
            @php
                $messages = [];
                if(($notificationCounts['rental']['pending'] ?? 0) > 0) $messages[] = $notificationCounts['rental']['pending'] . " Pesanan Alat Baru";
                if(($notificationCounts['rental']['cancellation'] ?? 0) > 0) $messages[] = $notificationCounts['rental']['cancellation'] . " Pembatalan Alat";
                if(($notificationCounts['gas']['pending'] ?? 0) > 0) $messages[] = $notificationCounts['gas']['pending'] . " Pesanan Gas Baru";
                if(($notificationCounts['gas']['cancellation'] ?? 0) > 0) $messages[] = $notificationCounts['gas']['cancellation'] . " Pembatalan Gas";
                if(($notificationCounts['mobil']['pending'] ?? 0) > 0) $messages[] = $notificationCounts['mobil']['pending'] . " Pesanan Transportasi Baru";
                if(($notificationCounts['mobil']['cancellation'] ?? 0) > 0) $messages[] = $notificationCounts['mobil']['cancellation'] . " Pembatalan Transportasi";
                if(($notificationCounts['fasilitas']['pending'] ?? 0) > 0) $messages[] = $notificationCounts['fasilitas']['pending'] . " Pesanan Fasilitas Baru";
                if(($notificationCounts['fasilitas']['cancellation'] ?? 0) > 0) $messages[] = $notificationCounts['fasilitas']['cancellation'] . " Pembatalan Fasilitas";
                $finalMessage = !empty($messages) ? '<i class="bx bx-bell bx-tada me-2"></i>Anda memiliki: ' . implode(', ', $messages) : '';
            @endphp
            <div id="notification-text" class="text-danger fw-semibold small d-flex align-items-center mt-1 mt-md-0 flex-shrink-0 {{ empty($messages) ? 'd-none' : '' }}">
                {!! strip_tags($finalMessage, '<i><b><br><span>') !!}
            </div>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="orderTabsContent">

                <!-- 1. RENTAL TAB -->
                @if($isRentalActive)
                <div class="tab-pane fade {{ $activeTab == 'rental' || (!$isRentalActive && $totalActive > 0) ? 'show active' : '' }}" id="rental-pane" role="tabpanel">
                    @if($rentalRequests->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="avatar avatar-lg bg-label-secondary text-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bx bx-folder-open fs-2"></i>
                            </div>
                            <h6 class="text-dark fw-bold mb-1">Belum Ada Permintaan Penyewaan Alat</h6>
                            <p class="text-muted small mb-0">Pesanan penyewaan alat dari warga akan muncul di sini.</p>
                        </div>
                    @else
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table req-table table-modern align-middle mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Nama Penyewa</th>
                                        <th>Alat &amp; Jumlah</th>
                                        <th>Tanggal Sewa</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rentalRequests as $req)
                                    @php
                                        $rName = $req->recipient_name ?? $req->user->name ?? 'Warga';
                                    @endphp
                                    <tr class="order-filter-item" data-status="{{ $req->status }}" data-cancel-status="{{ $req->cancellation_status }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar avatar-sm flex-shrink-0">
                                                    <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                                        {{ strtoupper(substr($rName, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ $rName }}</h6>
                                                    <small class="text-muted">{{ $req->user->email ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $req->barang->nama_barang ?? 'Alat' }}</div>
                                            <small class="text-muted">{{ $req->quantity }} Unit &bull; {{ ucfirst($req->delivery_method ?? 'jemput') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium text-dark">{{ \Carbon\Carbon::parse($req->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</span>
                                                <small class="text-primary fw-semibold">{{ $req->days_count }} Hari</small>
                                            </div>
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $req->status, 'cancelStatus' => $req->cancellation_status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end align-items-center flex-wrap">
                                                @if($req->status != 'pending' && $req->cancellation_status != 'pending')
                                                    @if($req->delivery_method == 'antar')
                                                        @if($req->status == 'confirmed')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'rental', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="being_prepared">
                                                                <button type="submit" class="btn btn-sm btn-outline-warning shadow-sm rounded-pill px-3" data-konfirmasi="Mulai siapkan pesanan ini?">
                                                                    <i class="bx bx-package me-1"></i>Proses
                                                                </button>
                                                            </form>
                                                        @elseif($req->status == 'being_prepared')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'rental', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="in_delivery">
                                                                <button type="submit" class="btn btn-sm btn-outline-info shadow-sm rounded-pill px-3" data-konfirmasi="Mulai kirim pesanan ini?">
                                                                    <i class="bx bx-truck me-1"></i>Kirim
                                                                </button>
                                                            </form>
                                                        @elseif($req->status == 'in_delivery')
                                                            <button type="button" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3" 
                                                                    onclick="prepareUpload({{ $req->id }}, 'rental', 'antar')"
                                                                    data-bs-toggle="modal" data-bs-target="#uploadProofModal">
                                                                <i class="bx bx-camera me-1"></i>Tiba
                                                            </button>
                                                        @elseif($req->status == 'arrived')
                                                            <button type="button" class="btn btn-sm btn-outline-success shadow-sm rounded-pill px-3" 
                                                                    onclick="prepareReturn({{ $req->id }}, {{ (int) $req->quantity }})"
                                                                    data-bs-toggle="modal" data-bs-target="#returnModal">
                                                                <i class="bx bx-check-circle me-1"></i>Selesai
                                                            </button>
                                                        @endif
                                                    @else
                                                        @if(in_array($req->status, ['confirmed', 'approved', 'being_prepared']))
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'rental', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="arrived">
                                                                <button type="submit" class="btn btn-sm btn-outline-warning shadow-sm rounded-pill px-3" data-konfirmasi="Tandai alat sudah diserahkan ke penyewa?">
                                                                    <i class="bx bx-package me-1"></i>Serahkan Alat
                                                                </button>
                                                            </form>
                                                        @elseif($req->status == 'arrived')
                                                            <button type="button" class="btn btn-sm btn-outline-success shadow-sm rounded-pill px-3" 
                                                                    onclick="prepareReturn({{ $req->id }}, {{ (int) $req->quantity }})"
                                                                    data-bs-toggle="modal" data-bs-target="#returnModal">
                                                                <i class="bx bx-check-circle me-1"></i>Selesai
                                                            </button>
                                                        @endif
                                                    @endif
                                                @endif

                                                <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$req->id, 'rental']) }}" 
                                                   class="btn btn-sm btn-icon btn-outline-primary" 
                                                   data-bs-toggle="tooltip" title="Lihat Detail">
                                                    <i class="bx bx-show"></i>
                                                </a>

                                                @if($req->cancellation_status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-success" 
                                                            onclick="handleCancellation({{ $req->id }}, 'rental', 'approve')"
                                                            data-bs-toggle="tooltip" title="Setujui Pembatalan">
                                                        <i class="bx bx-check-double"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" 
                                                            onclick="handleCancellation({{ $req->id }}, 'rental', 'reject')"
                                                            data-bs-toggle="tooltip" title="Tolak Pembatalan">
                                                        <i class="bx bx-x-circle"></i>
                                                    </button>
                                                @elseif($req->status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-success" 
                                                            onclick="approveRequest({{ $req->id }}, 'rental')"
                                                            data-bs-toggle="tooltip" title="Setujui">
                                                        <i class="bx bx-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" 
                                                            onclick="rejectRequest({{ $req->id }}, 'rental')"
                                                            data-bs-toggle="tooltip" title="Tolak">
                                                        <i class="bx bx-x"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View (< 768px) -->
                        <div class="d-block d-md-none p-3 bg-light bg-opacity-50">
                            <div class="d-flex flex-column gap-3">
                                @foreach($rentalRequests as $req)
                                    @php
                                        $rName = $req->recipient_name ?? $req->user->name ?? 'Warga';
                                    @endphp
                                    <div class="order-mobile-card p-3 shadow-sm order-filter-item" data-status="{{ $req->status }}" data-cancel-status="{{ $req->cancellation_status }}">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <div class="avatar avatar-sm flex-shrink-0">
                                                    <span class="avatar-initial rounded-circle bg-label-primary fw-bold">{{ strtoupper(substr($rName, 0, 1)) }}</span>
                                                </div>
                                                <div class="min-w-0">
                                                    <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $rName }}</h6>
                                                    <small class="text-muted d-block text-truncate">{{ $req->barang->nama_barang ?? 'Alat' }} ({{ $req->quantity }} Unit)</small>
                                                </div>
                                            </div>
                                            @include('admin.partials.status-badge', ['status' => $req->status, 'cancelStatus' => $req->cancellation_status])
                                        </div>
                                        <div class="bg-light rounded-3 p-2.5 mb-2.5 d-flex justify-content-between align-items-center small">
                                            <div>
                                                <span class="text-muted d-block" style="font-size: 0.72rem;">Jadwal Sewa</span>
                                                <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($req->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</span>
                                            </div>
                                            <span class="badge bg-label-primary rounded-pill">{{ $req->days_count }} Hari</span>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$req->id, 'rental']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bx bx-show me-1"></i>Detail
                                            </a>
                                            <div class="d-flex flex-wrap gap-1.5 ms-auto">
                                                @if($req->cancellation_status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5" onclick="handleCancellation({{ $req->id }}, 'rental', 'approve')">Setujui Batal</button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" onclick="handleCancellation({{ $req->id }}, 'rental', 'reject')">Tolak</button>
                                                @elseif($req->status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" onclick="rejectRequest({{ $req->id }}, 'rental')">Tolak</button>
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="approveRequest({{ $req->id }}, 'rental')"><i class="bx bx-check me-1"></i>Setujui</button>
                                                @elseif($req->delivery_method == 'antar')
                                                    @if($req->status == 'confirmed')
                                                        <form action="{{ route('admin.aktivitas.update-status', ['type' => 'rental', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="being_prepared">
                                                            <button type="submit" class="btn btn-sm btn-warning rounded-pill px-3" data-konfirmasi="Mulai siapkan pesanan ini?">Proses</button>
                                                        </form>
                                                    @elseif($req->status == 'being_prepared')
                                                        <form action="{{ route('admin.aktivitas.update-status', ['type' => 'rental', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="in_delivery">
                                                            <button type="submit" class="btn btn-sm btn-info text-white rounded-pill px-3" data-konfirmasi="Mulai kirim pesanan ini?">Kirim</button>
                                                        </form>
                                                    @elseif($req->status == 'in_delivery')
                                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="prepareUpload({{ $req->id }}, 'rental', 'antar')" data-bs-toggle="modal" data-bs-target="#uploadProofModal">Tiba</button>
                                                    @elseif($req->status == 'arrived')
                                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="prepareReturn({{ $req->id }}, {{ (int) $req->quantity }})" data-bs-toggle="modal" data-bs-target="#returnModal">Selesai</button>
                                                    @endif
                                                @else
                                                    @if(in_array($req->status, ['confirmed', 'approved', 'being_prepared']))
                                                        <form action="{{ route('admin.aktivitas.update-status', ['type' => 'rental', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="arrived">
                                                            <button type="submit" class="btn btn-sm btn-warning rounded-pill px-3" data-konfirmasi="Tandai alat sudah diserahkan ke penyewa?">Serahkan Alat</button>
                                                        </form>
                                                    @elseif($req->status == 'arrived')
                                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="prepareReturn({{ $req->id }}, {{ (int) $req->quantity }})" data-bs-toggle="modal" data-bs-target="#returnModal">Selesai</button>
                                                    @endif
                                                @endif
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
                <div class="tab-pane fade {{ $activeTab == 'gas' || (!$isRentalActive && $activeTab == 'rental') ? 'show active' : '' }}" id="gas-pane" role="tabpanel">
                    @if($gasOrders->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="avatar avatar-lg bg-label-secondary text-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bx bx-cylinder fs-2"></i>
                            </div>
                            <h6 class="text-dark fw-bold mb-1">Belum Ada Permintaan Pembelian Gas</h6>
                            <p class="text-muted small mb-0">Pesanan pembelian gas LPG warga akan muncul di sini.</p>
                        </div>
                    @else
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table req-table table-modern align-middle mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Nama Pembeli</th>
                                        <th>Produk Gas</th>
                                        <th>Tanggal Order</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gasOrders as $order)
                                    @php
                                        $gName = $order->full_name ?? $order->user->name ?? 'Warga';
                                    @endphp
                                    <tr class="order-filter-item" data-status="{{ $order->status }}" data-cancel-status="{{ $order->cancellation_status }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar avatar-sm flex-shrink-0">
                                                    <span class="avatar-initial rounded-circle bg-label-success fw-bold">
                                                        {{ strtoupper(substr($gName, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ $gName }}</h6>
                                                    <small class="text-muted d-block text-truncate" style="max-width: 220px;">{{ $order->address ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $order->item_name }}</div>
                                            <small class="text-muted">{{ $order->quantity }} Tabung</small>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $order->created_at->isoFormat('D MMM Y') }}</div>
                                            <small class="text-muted">{{ $order->created_at->format('H:i') }} WIB</small>
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $order->status, 'cancelStatus' => $order->cancellation_status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end align-items-center flex-wrap">
                                                @if(!in_array($order->status, ['completed', 'cancelled', 'rejected']))
                                                    <a class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'gas']) }}">
                                                        Proses Pesanan
                                                    </a>
                                                @endif

                                                <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'gas']) }}" 
                                                   class="btn btn-sm btn-icon btn-outline-primary" 
                                                   data-bs-toggle="tooltip" title="Lihat Detail">
                                                    <i class="bx bx-show"></i>
                                                </a>

                                                @if($order->cancellation_status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-success" 
                                                            onclick="handleCancellation({{ $order->id }}, 'gas', 'approve')"
                                                            data-bs-toggle="tooltip" title="Setujui Pembatalan">
                                                        <i class="bx bx-check-double"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" 
                                                            onclick="handleCancellation({{ $order->id }}, 'gas', 'reject')"
                                                            data-bs-toggle="tooltip" title="Tolak Pembatalan">
                                                        <i class="bx bx-x-circle"></i>
                                                    </button>
                                                @elseif($order->status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-success" 
                                                            onclick="approveRequest({{ $order->id }}, 'gas')"
                                                            data-bs-toggle="tooltip" title="Setujui">
                                                        <i class="bx bx-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" 
                                                            onclick="rejectRequest({{ $order->id }}, 'gas')"
                                                            data-bs-toggle="tooltip" title="Tolak">
                                                        <i class="bx bx-x"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View (< 768px) -->
                        <div class="d-block d-md-none p-3 bg-light bg-opacity-50">
                            <div class="d-flex flex-column gap-3">
                                @foreach($gasOrders as $order)
                                    @php
                                        $gName = $order->full_name ?? $order->user->name ?? 'Warga';
                                    @endphp
                                    <div class="order-mobile-card p-3 shadow-sm order-filter-item" data-status="{{ $order->status }}" data-cancel-status="{{ $order->cancellation_status }}">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <div class="avatar avatar-sm flex-shrink-0">
                                                    <span class="avatar-initial rounded-circle bg-label-success fw-bold">{{ strtoupper(substr($gName, 0, 1)) }}</span>
                                                </div>
                                                <div class="min-w-0">
                                                    <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $gName }}</h6>
                                                    <small class="text-muted d-block text-truncate">{{ $order->item_name }} ({{ $order->quantity }} Tabung)</small>
                                                </div>
                                            </div>
                                            @include('admin.partials.status-badge', ['status' => $order->status, 'cancelStatus' => $order->cancellation_status])
                                        </div>
                                        <div class="bg-light rounded-3 p-2.5 mb-2.5 small">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted">Waktu Order:</span>
                                                <span class="fw-semibold text-dark">{{ $order->created_at->isoFormat('D MMM Y') }}, {{ $order->created_at->format('H:i') }}</span>
                                            </div>
                                            @if($order->address)
                                            <div class="text-muted text-truncate">
                                                <i class="bx bx-map-pin me-1 text-primary"></i>{{ $order->address }}
                                            </div>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'gas']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bx bx-show me-1"></i>Detail
                                            </a>
                                            <div class="d-flex flex-wrap gap-1.5 ms-auto">
                                                @if($order->cancellation_status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5" onclick="handleCancellation({{ $order->id }}, 'gas', 'approve')">Setujui Batal</button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" onclick="handleCancellation({{ $order->id }}, 'gas', 'reject')">Tolak</button>
                                                @elseif($order->status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" onclick="rejectRequest({{ $order->id }}, 'gas')">Tolak</button>
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="approveRequest({{ $order->id }}, 'gas')"><i class="bx bx-check me-1"></i>Setujui</button>
                                                @elseif(!in_array($order->status, ['completed', 'cancelled', 'rejected']))
                                                    <a class="btn btn-sm btn-primary rounded-pill px-3" href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'gas']) }}">Proses Pesanan</a>
                                                @endif
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
                <div class="tab-pane fade {{ $activeTab == 'mobil' || (!$isRentalActive && !$isGasActive && $activeTab == 'rental') ? 'show active' : '' }}" id="mobil-pane" role="tabpanel">
                    @if($mobilRequests->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="avatar avatar-lg bg-label-secondary text-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bx bx-car fs-2"></i>
                            </div>
                            <h6 class="text-dark fw-bold mb-1">Belum Ada Permintaan Penyewaan Transportasi</h6>
                            <p class="text-muted small mb-0">Pesanan transportasi warga akan muncul di sini.</p>
                        </div>
                    @else
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table req-table table-modern align-middle mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Penyewa</th>
                                        <th>Mobil &amp; Opsi</th>
                                        <th>Waktu Sewa</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mobilRequests as $req)
                                    @php
                                        $mName = $req->recipient_name ?? $req->user->name ?? 'Warga';
                                    @endphp
                                    <tr class="order-filter-item" data-status="{{ $req->status }}" data-cancel-status="{{ $req->cancellation_status }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar avatar-sm flex-shrink-0">
                                                    <span class="avatar-initial rounded-circle bg-label-primary fw-bold">
                                                        {{ strtoupper(substr($mName, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ $mName }}</h6>
                                                    <small class="text-muted">{{ $req->user->phone ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $req->mobil->nama_mobil ?? 'Transportasi' }}</div>
                                            <small class="text-muted">Supir: {{ $req->dengan_supir ? 'Ya' : 'Tidak' }} | BBM: {{ $req->mobil->bbm_ditanggung ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-medium text-dark">{{ \Carbon\Carbon::parse($req->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</span>
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $req->status, 'cancelStatus' => $req->cancellation_status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end align-items-center flex-wrap">
                                                @if($req->status != 'pending' && $req->cancellation_status != 'pending')
                                                    @if($req->dengan_supir)
                                                        @if(in_array($req->status, ['confirmed', 'approved']))
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="being_prepared">
                                                                <button type="submit" class="btn btn-sm btn-outline-warning shadow-sm rounded-pill px-3" data-konfirmasi="Mulai siapkan transportasi ini?">
                                                                    <i class="bx bx-package me-1"></i>Proses
                                                                </button>
                                                            </form>
                                                        @elseif(in_array($req->status, ['being_prepared', 'process']))
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="in_delivery">
                                                                <button type="submit" class="btn btn-sm btn-outline-info shadow-sm rounded-pill px-3" data-konfirmasi="Mulai jalan / kirim transportasi?">
                                                                    <i class="bx bx-car me-1"></i>Kirim
                                                                </button>
                                                            </form>
                                                        @elseif(in_array($req->status, ['in_delivery', 'delivering']))
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="arrived">
                                                                <button type="submit" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3" data-konfirmasi="Konfirmasi tiba?">
                                                                    <i class="bx bx-check me-1"></i>Tiba
                                                                </button>
                                                            </form>
                                                        @elseif(in_array($req->status, ['arrived', 'ongoing']))
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="completed">
                                                                <button type="submit" class="btn btn-sm btn-outline-success shadow-sm rounded-pill px-3" data-konfirmasi="Selesaikan penyewaan ini?">
                                                                    <i class="bx bx-check-circle me-1"></i>Selesai
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        @if(in_array($req->status, ['confirmed', 'approved', 'arrived', 'ongoing']))
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="completed">
                                                                <button type="submit" class="btn btn-sm btn-outline-success shadow-sm rounded-pill px-3" data-konfirmasi="Serahkan Kunci / Selesaikan penyewaan ini?">
                                                                    <i class="bx bx-key me-1"></i>Serahkan &amp; Selesai
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                @endif

                                                <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$req->id, 'mobil']) }}" 
                                                   class="btn btn-sm btn-icon btn-outline-primary" 
                                                   data-bs-toggle="tooltip" title="Lihat Detail">
                                                    <i class="bx bx-show"></i>
                                                </a>

                                                @if($req->cancellation_status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-success" onclick="handleCancellation({{ $req->id }}, 'mobil', 'approve')"><i class="bx bx-check-double"></i></button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="handleCancellation({{ $req->id }}, 'mobil', 'reject')"><i class="bx bx-x-circle"></i></button>
                                                @elseif($req->status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-success" onclick="approveRequest({{ $req->id }}, 'mobil')"><i class="bx bx-check"></i></button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="rejectRequest({{ $req->id }}, 'mobil')"><i class="bx bx-x"></i></button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View (< 768px) -->
                        <div class="d-block d-md-none p-3 bg-light bg-opacity-50">
                            <div class="d-flex flex-column gap-3">
                                @foreach($mobilRequests as $req)
                                    @php
                                        $mName = $req->recipient_name ?? $req->user->name ?? 'Warga';
                                    @endphp
                                    <div class="order-mobile-card p-3 shadow-sm order-filter-item" data-status="{{ $req->status }}" data-cancel-status="{{ $req->cancellation_status }}">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <div class="avatar avatar-sm flex-shrink-0">
                                                    <span class="avatar-initial rounded-circle bg-label-primary fw-bold">{{ strtoupper(substr($mName, 0, 1)) }}</span>
                                                </div>
                                                <div class="min-w-0">
                                                    <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $mName }}</h6>
                                                    <small class="text-muted d-block text-truncate">{{ $req->mobil->nama_mobil ?? 'Transportasi' }} (Supir: {{ $req->dengan_supir ? 'Ya' : 'Tidak' }})</small>
                                                </div>
                                            </div>
                                            @include('admin.partials.status-badge', ['status' => $req->status, 'cancelStatus' => $req->cancellation_status])
                                        </div>
                                        <div class="bg-light rounded-3 p-2.5 mb-2.5 d-flex justify-content-between align-items-center small">
                                            <span class="text-muted">Waktu Sewa:</span>
                                            <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($req->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$req->id, 'mobil']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bx bx-show me-1"></i>Detail
                                            </a>
                                            <div class="d-flex flex-wrap gap-1.5 ms-auto">
                                                @if($req->cancellation_status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5" onclick="handleCancellation({{ $req->id }}, 'mobil', 'approve')">Setujui Batal</button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" onclick="handleCancellation({{ $req->id }}, 'mobil', 'reject')">Tolak</button>
                                                @elseif($req->status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" onclick="rejectRequest({{ $req->id }}, 'mobil')">Tolak</button>
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="approveRequest({{ $req->id }}, 'mobil')"><i class="bx bx-check me-1"></i>Setujui</button>
                                                @elseif($req->dengan_supir)
                                                    @if(in_array($req->status, ['confirmed', 'approved']))
                                                        <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="being_prepared">
                                                            <button type="submit" class="btn btn-sm btn-warning rounded-pill px-3" data-konfirmasi="Mulai siapkan transportasi ini?">Proses</button>
                                                        </form>
                                                    @elseif(in_array($req->status, ['being_prepared', 'process']))
                                                        <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="in_delivery">
                                                            <button type="submit" class="btn btn-sm btn-info text-white rounded-pill px-3" data-konfirmasi="Mulai jalan / kirim transportasi?">Kirim</button>
                                                        </form>
                                                    @elseif(in_array($req->status, ['in_delivery', 'delivering']))
                                                        <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="arrived">
                                                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3" data-konfirmasi="Konfirmasi tiba?">Tiba</button>
                                                        </form>
                                                    @elseif(in_array($req->status, ['arrived', 'ongoing']))
                                                        <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" data-konfirmasi="Selesaikan penyewaan ini?">Selesai</button>
                                                        </form>
                                                    @endif
                                                @elseif(in_array($req->status, ['confirmed', 'approved', 'arrived', 'ongoing']))
                                                    <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" data-konfirmasi="Serahkan Kunci / Selesaikan penyewaan ini?">Selesai</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                @endif

                <!-- 4. FASILITAS UMUM TAB -->
                @if($isFasilitasActive)
                <div class="tab-pane fade {{ $activeTab == 'fasilitas' || (!$isRentalActive && !$isGasActive && !$isMobilActive && $activeTab == 'rental') ? 'show active' : '' }}" id="fasilitas-pane" role="tabpanel">
                    @if($fasilitasRequests->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="avatar avatar-lg bg-label-secondary text-secondary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="bx bx-building-house fs-2"></i>
                            </div>
                            <h6 class="text-dark fw-bold mb-1">Belum Ada Permintaan Fasilitas Umum</h6>
                            <p class="text-muted small mb-0">Permohonan peminjaman atau sewa fasilitas umum warga akan muncul di sini.</p>
                        </div>
                    @else
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table req-table table-modern align-middle mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Penyewa</th>
                                        <th>Fasilitas &amp; Opsi</th>
                                        <th>Waktu Sewa</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fasilitasRequests as $req)
                                    @php
                                        $fName = $req->recipient_name ?? $req->user->name ?? 'Warga';
                                    @endphp
                                    <tr class="order-filter-item" data-status="{{ $req->status }}" data-cancel-status="{{ $req->cancellation_status }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar avatar-sm flex-shrink-0">
                                                    <span class="avatar-initial rounded-circle bg-label-info fw-bold">
                                                        {{ strtoupper(substr($fName, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ $fName }}</h6>
                                                    <small class="text-muted">{{ $req->user->phone ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $req->fasilitas->nama_fasilitas ?? 'Fasilitas' }}</div>
                                            @if(($req->fasilitas->kategori ?? '') == 'Kendaraan' || ($req->fasilitas->opsi_supir ?? false))
                                                <small class="text-muted">Supir: {{ $req->dengan_supir ? 'Ya' : 'Tidak' }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-medium text-dark">{{ \Carbon\Carbon::parse($req->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</span>
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $req->status, 'cancelStatus' => $req->cancellation_status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end align-items-center flex-wrap">
                                                @if($req->status != 'pending' && $req->cancellation_status != 'pending')
                                                    @if($req->delivery_method == 'antar' || $req->dengan_supir)
                                                        @if(in_array($req->status, ['confirmed', 'approved', 'being_prepared']))
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'fasilitas', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="in_delivery">
                                                                <button type="submit" class="btn btn-sm btn-outline-info shadow-sm rounded-pill px-3" data-konfirmasi="Mulai jalan / kirim?">
                                                                    <i class="bx bx-truck me-1"></i>Kirim/Berangkat
                                                                </button>
                                                            </form>
                                                        @elseif(in_array($req->status, ['in_delivery', 'delivering']))
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'fasilitas', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="arrived">
                                                                <button type="submit" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3" data-konfirmasi="Konfirmasi tiba/digunakan?">
                                                                    <i class="bx bx-check me-1"></i>Tiba
                                                                </button>
                                                            </form>
                                                        @elseif(in_array($req->status, ['arrived', 'ongoing']))
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'fasilitas', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="completed">
                                                                <button type="submit" class="btn btn-sm btn-outline-success shadow-sm rounded-pill px-3" data-konfirmasi="Selesaikan penyewaan ini?">
                                                                    <i class="bx bx-check-circle me-1"></i>Selesai
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        @if(in_array($req->status, ['confirmed', 'approved', 'arrived', 'ongoing']))
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'fasilitas', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="completed">
                                                                <button type="submit" class="btn btn-sm btn-outline-success shadow-sm rounded-pill px-3" data-konfirmasi="Selesaikan peminjaman ini?">
                                                                    <i class="bx bx-check-circle me-1"></i>Selesai
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                @endif

                                                <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$req->id, 'fasilitas']) }}" 
                                                   class="btn btn-sm btn-icon btn-outline-primary" 
                                                   data-bs-toggle="tooltip" title="Lihat Detail">
                                                    <i class="bx bx-show"></i>
                                                </a>

                                                @if($req->cancellation_status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-success" onclick="handleCancellation({{ $req->id }}, 'fasilitas', 'approve')"><i class="bx bx-check-double"></i></button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="handleCancellation({{ $req->id }}, 'fasilitas', 'reject')"><i class="bx bx-x-circle"></i></button>
                                                @elseif($req->status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-success" onclick="approveRequest({{ $req->id }}, 'fasilitas')"><i class="bx bx-check"></i></button>
                                                    <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="rejectRequest({{ $req->id }}, 'fasilitas')"><i class="bx bx-x"></i></button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View (< 768px) -->
                        <div class="d-block d-md-none p-3 bg-light bg-opacity-50">
                            <div class="d-flex flex-column gap-3">
                                @foreach($fasilitasRequests as $req)
                                    @php
                                        $fName = $req->recipient_name ?? $req->user->name ?? 'Warga';
                                    @endphp
                                    <div class="order-mobile-card p-3 shadow-sm order-filter-item" data-status="{{ $req->status }}" data-cancel-status="{{ $req->cancellation_status }}">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2 pb-2 border-bottom">
                                            <div class="d-flex align-items-center gap-2 min-w-0">
                                                <div class="avatar avatar-sm flex-shrink-0">
                                                    <span class="avatar-initial rounded-circle bg-label-info fw-bold">{{ strtoupper(substr($fName, 0, 1)) }}</span>
                                                </div>
                                                <div class="min-w-0">
                                                    <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.9rem;">{{ $fName }}</h6>
                                                    <small class="text-muted d-block text-truncate">{{ $req->fasilitas->nama_fasilitas ?? 'Fasilitas' }}</small>
                                                </div>
                                            </div>
                                            @include('admin.partials.status-badge', ['status' => $req->status, 'cancelStatus' => $req->cancellation_status])
                                        </div>
                                        <div class="bg-light rounded-3 p-2.5 mb-2.5 d-flex justify-content-between align-items-center small">
                                            <span class="text-muted">Waktu Sewa:</span>
                                            <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($req->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$req->id, 'fasilitas']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                <i class="bx bx-show me-1"></i>Detail
                                            </a>
                                            <div class="d-flex flex-wrap gap-1.5 ms-auto">
                                                @if($req->cancellation_status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5" onclick="handleCancellation({{ $req->id }}, 'fasilitas', 'approve')">Setujui Batal</button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" onclick="handleCancellation({{ $req->id }}, 'fasilitas', 'reject')">Tolak</button>
                                                @elseif($req->status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5" onclick="rejectRequest({{ $req->id }}, 'fasilitas')">Tolak</button>
                                                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="approveRequest({{ $req->id }}, 'fasilitas')"><i class="bx bx-check me-1"></i>Setujui</button>
                                                @elseif($req->delivery_method == 'antar' || $req->dengan_supir)
                                                    @if(in_array($req->status, ['confirmed', 'approved', 'being_prepared']))
                                                        <form action="{{ route('admin.aktivitas.update-status', ['type' => 'fasilitas', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="in_delivery">
                                                            <button type="submit" class="btn btn-sm btn-info text-white rounded-pill px-3" data-konfirmasi="Mulai jalan / kirim?">Berangkat</button>
                                                        </form>
                                                    @elseif(in_array($req->status, ['in_delivery', 'delivering']))
                                                        <form action="{{ route('admin.aktivitas.update-status', ['type' => 'fasilitas', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="arrived">
                                                            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3" data-konfirmasi="Konfirmasi tiba/digunakan?">Tiba</button>
                                                        </form>
                                                    @elseif(in_array($req->status, ['arrived', 'ongoing']))
                                                        <form action="{{ route('admin.aktivitas.update-status', ['type' => 'fasilitas', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" data-konfirmasi="Selesaikan peminjaman ini?">Selesai</button>
                                                        </form>
                                                    @endif
                                                @elseif(in_array($req->status, ['confirmed', 'approved', 'arrived', 'ongoing']))
                                                    <form action="{{ route('admin.aktivitas.update-status', ['type' => 'fasilitas', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3" data-konfirmasi="Selesaikan peminjaman ini?">Selesai</button>
                                                    </form>
                                                @endif
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
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-danger"><i class="bx bx-x-circle me-1"></i>Tolak Permintaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-2">
                        <label class="form-label fw-semibold small text-dark">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control rounded-3" rows="3" placeholder="Jelaskan alasan penolakan permintaan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">
                        Tolak Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Proof Modal -->
<div class="modal fade" id="uploadProofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-primary" id="uploadProofTitle">Upload Bukti Pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadProofForm" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" id="uploadId" name="id">
                    <input type="hidden" id="uploadType" name="type">
                    <div class="mb-2">
                        <label class="form-label fw-semibold small text-dark" id="uploadLabel">Foto Bukti Penerimaan Barang</label>
                        <input type="file" name="delivery_proof" class="form-control" accept="image/*" required>
                        <div class="form-text small" id="uploadHelp">Upload foto saat barang diterima oleh pelanggan</div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Upload dan Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-success"><i class="bx bx-check-circle me-1"></i>Pengembalian Alat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="returnForm">
                <input type="hidden" id="returnId" name="id">
                <div class="modal-body p-4">
                    <div class="alert bg-label-info border-0 d-flex align-items-center mb-3 rounded-3">
                        <i class="bx bx-info-circle fs-4 me-2 text-info"></i>
                        <div class="small text-dark">
                            Stok alat akan otomatis <strong>bertambah kembali</strong> setelah pengembalian dikonfirmasi.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark" for="listReturnedQuantity">Jumlah Kembali Layak Pakai</label>
                        <input type="number" id="listReturnedQuantity" name="returned_quantity" min="0" value="1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark" for="listReturnNotes">Catatan Kondisi Alat</label>
                        <textarea id="listReturnNotes" name="return_notes" class="form-control" rows="2" maxlength="1000" placeholder="Wajib diisi bila ada alat rusak/hilang"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold small text-dark">Waktu Pengembalian</label>
                        <input type="datetime-local" name="return_time" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top px-4 py-3 rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">Konfirmasi Pengembalian</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function approveRequest(id, type) {
        Swal.fire({
            title: 'Setujui Pesanan?',
            text: "Pastikan stok tersedia. Pesanan akan diproses.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
                
                fetch(`{{ url('admin/aktivitas/permintaan-pengajuan') }}/${id}/${type}/approve`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', data.message, 'error');
                    }
                })
                .catch(err => Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'));
            }
        });
    }

    function handleCancellation(id, type, action) {
        let title, text, confirmBtn, icon;
        
        if (action === 'approve') {
            title = 'Setujui Pembatalan?';
            text = "Pesanan akan dibatalkan sesuai permintaan pengguna.";
            confirmBtn = 'Ya, Setujui Pembatalan';
            icon = 'warning';
        } else {
            Swal.fire({
                title: 'Tolak Pembatalan',
                input: 'textarea',
                inputLabel: 'Alasan Penolakan',
                inputPlaceholder: 'Jelaskan kenapa pembatalan ditolak...',
                inputAttributes: {
                    'aria-label': 'Jelaskan kenapa pembatalan ditolak'
                },
                showCancelButton: true,
                confirmButtonText: 'Tolak Pembatalan',
                cancelButtonText: 'Batal',
                inputValidator: (value) => {
                    if (!value) {
                        return 'Anda harus menuliskan alasan penolakan!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitCancellationResponse(id, type, action, result.value);
                }
            });
            return;
        }

        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: action === 'approve' ? '#198754' : '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: confirmBtn,
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                submitCancellationResponse(id, type, action, null);
            }
        });
    }

    function submitCancellationResponse(id, type, action, reason) {
        Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
        
        let url = `{{ url('admin/aktivitas/permintaan-pengajuan') }}/${type}/${id}/cancellation/${action}`;
        let body = { 
            _token: '{{ csrf_token() }}' 
        };
        
        if (reason) {
            body.admin_response = reason;
        }

        fetch(url, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json' 
            },
            body: JSON.stringify(body)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'));
    }

    function rejectRequest(id, type) {
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        document.getElementById('rejectForm').action = `{{ url('admin/aktivitas/permintaan-pengajuan') }}/${id}/${type}/reject`;
        modal.show();
    }

    function prepareUpload(id, type, deliveryMethod) {
        document.getElementById('uploadId').value = id;
        document.getElementById('uploadType').value = type;
        
        const titleEl = document.getElementById('uploadProofTitle');
        const labelEl = document.getElementById('uploadLabel');
        const helpEl = document.getElementById('uploadHelp');

        if (deliveryMethod === 'jemput') {
            titleEl.innerText = 'Upload Bukti Penjemputan';
            labelEl.innerText = 'Foto Bukti Pengambilan Barang';
            helpEl.innerText = 'Upload foto saat barang diambil oleh pelanggan';
        } else {
            titleEl.innerText = 'Upload Bukti Pengiriman';
            labelEl.innerText = 'Foto Bukti Penerimaan Barang';
            helpEl.innerText = 'Upload foto saat barang diterima oleh pelanggan';
        }
    }

    document.getElementById('uploadProofForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('uploadId').value;
        const type = document.getElementById('uploadType').value;
        
        const url = `{{ url('admin/aktivitas/permintaan-pengajuan') }}/${type}/${id}/delivery-proof`;
        
        Swal.fire({
            title: 'Mengupload...',
            allowOutsideClick: false,
            didOpen: () => {
                 Swal.showLoading();
            }
        });

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                Swal.fire('Berhasil', data.message, 'success')
                .then(() => location.reload());
            } else {
                Swal.fire('Error', data.message || 'Gagal upload bukti', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Terjadi kesalahan sistem: ' + err.message, 'error');
        });
    });

    function prepareReturn(id, maxQty = 1) {
        document.getElementById('returnId').value = id;
        const qtyInput = document.getElementById('listReturnedQuantity');
        if (qtyInput) {
            const safeMax = Math.max(1, parseInt(maxQty, 10) || 1);
            qtyInput.max = safeMax;
            qtyInput.value = safeMax;
        }
        const notesInput = document.getElementById('listReturnNotes');
        if (notesInput) notesInput.value = '';
    }

    document.getElementById('returnForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('returnId').value;
        const url = `{{ url('admin/aktivitas/permintaan-pengajuan/rental') }}/${id}/return`;
        
        Swal.fire({
            title: 'Konfirmasi Pengembalian?',
            text: "Pastikan alat sudah diterima dengan baik. Stok akan ditambahkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Konfirmasi'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                         Swal.showLoading();
                    }
                });

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        return_time: formData.get('return_time'),
                        returned_quantity: formData.get('returned_quantity'),
                        return_notes: formData.get('return_notes')
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Berhasil', data.message, 'success')
                        .then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message || 'Gagal memproses pengembalian', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                });
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        let currentStatusFilter = '{{ request('status', 'all') }}';
        const searchInput = document.getElementById('searchOrderInput');

        function applyOrderFilters() {
            const q = (searchInput ? searchInput.value : '').toLowerCase().trim();
            document.querySelectorAll('.order-filter-item').forEach(item => {
                const rowStatus = item.getAttribute('data-status');
                const rowCancel = item.getAttribute('data-cancel-status');
                const text = (item.textContent || '').toLowerCase();

                let statusMatch = true;
                if (currentStatusFilter && currentStatusFilter !== 'all') {
                    statusMatch = false;
                    if (currentStatusFilter === 'cancellation_pending' && rowCancel === 'pending') statusMatch = true;
                    else if (currentStatusFilter === 'pending' && rowStatus === 'pending') statusMatch = true;
                    else if (currentStatusFilter === 'in_process' && ['confirmed', 'approved', 'being_prepared', 'in_delivery', 'arrived', 'ongoing', 'process', 'delivering'].includes(rowStatus)) statusMatch = true;
                    else if (currentStatusFilter === 'completed' && ['completed', 'resolved', 'returned'].includes(rowStatus)) statusMatch = true;
                    else if (currentStatusFilter === 'rejected' && ['cancelled', 'rejected'].includes(rowStatus)) statusMatch = true;
                }

                const searchMatch = !q || text.includes(q);
                item.style.display = (statusMatch && searchMatch) ? '' : 'none';
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyOrderFilters);
        }

        const statusFilters = document.querySelectorAll('.filter-status-btn');
        statusFilters.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                statusFilters.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filterValue = this.getAttribute('data-status-filter');
                currentStatusFilter = filterValue;
                
                let url = new URL(window.location.href);
                if (filterValue === 'all') {
                    url.searchParams.delete('status');
                } else {
                    url.searchParams.set('status', filterValue);
                }
                window.history.replaceState({}, '', url);

                const initialServerStatus = '{{ request('status', 'all') }}';
                if (initialServerStatus !== 'all' && initialServerStatus !== '') {
                    window.location.href = url.toString();
                    return;
                }

                applyOrderFilters();
            });
        });

        function updateFilterLinks(type) {
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('tab', type);
            window.history.replaceState({}, '', currentUrl);
        }

        let activeTab = '{{ $activeTab }}';
        updateFilterLinks(activeTab);

        const triggerTabList = document.querySelectorAll('button[data-bs-toggle="tab"]');
        triggerTabList.forEach(triggerEl => {
            triggerEl.addEventListener('shown.bs.tab', event => {
                let targetId = event.target.getAttribute('id');
                let type = targetId ? targetId.replace('-tab', '') : 'rental';
                updateFilterLinks(type);
            });
        });

        function checkNotificationCounts() {
            fetch('{{ route("admin.aktivitas.permintaan-pengajuan.counts") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.rental) updateBadge('rental', data.rental.total);
                    if (data.gas) updateBadge('gas', data.gas.total);
                    if (data.mobil) updateBadge('mobil', data.mobil.total);
                    if (data.fasilitas) updateBadge('fasilitas', data.fasilitas.total);
                    updateNotificationText(data);
                })
                .catch(error => console.error('Error fetching counts:', error));
        }

        function updateBadge(type, count) {
            const badge = document.getElementById(type + '-badge');
            if (!badge) return;
            if (count > 0) {
                badge.className = 'badge bg-danger text-white ms-2 shadow-sm';
                badge.innerText = count;
            } else {
                const defaultCount = badge.getAttribute('data-default-count');
                badge.className = 'badge bg-white text-primary ms-2 shadow-sm';
                badge.innerText = defaultCount;
            }
        }

        function updateNotificationText(data) {
            const container = document.getElementById('notification-text');
            if (!container) return;
            let messages = [];

            if (data.rental?.pending > 0) messages.push(data.rental.pending + " Pesanan Alat Baru");
            if (data.rental?.cancellation > 0) messages.push(data.rental.cancellation + " Pembatalan Alat");
            if (data.gas?.pending > 0) messages.push(data.gas.pending + " Pesanan Gas Baru");
            if (data.gas?.cancellation > 0) messages.push(data.gas.cancellation + " Pembatalan Gas");
            if (data.mobil?.pending > 0) messages.push(data.mobil.pending + " Pesanan Transportasi Baru");
            if (data.mobil?.cancellation > 0) messages.push(data.mobil.cancellation + " Pembatalan Transportasi");
            if (data.fasilitas?.pending > 0) messages.push(data.fasilitas.pending + " Pesanan Fasilitas Baru");
            if (data.fasilitas?.cancellation > 0) messages.push(data.fasilitas.cancellation + " Pembatalan Fasilitas");

            if (messages.length > 0) {
                container.innerHTML = '<i class="bx bx-bell bx-tada me-2"></i>Anda memiliki: ' + messages.join(', ');
                container.classList.remove('d-none');
            } else {
                container.innerHTML = '';
                container.classList.add('d-none');
            }
        }

        setInterval(checkNotificationCounts, 15000);
    });
</script>
