@extends('admin.layouts.admin')

@section('title', 'Permintaan Pengajuan')

@section('content')
<style>
    .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
        background-color: #0095ff !important;
        color: #fff !important;
        box-shadow: 0 4px 10px rgba(0, 149, 255, 0.3) !important;
    }
    .nav-pills .nav-link {
        color: #64748b;
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link:hover {
        background-color: #eff6ff !important;
        color: #0095ff !important;
    }
    .nav-pills .nav-link.active .badge.bg-white {
        color: #0095ff !important;
    }
</style>
<div class="container-fluid py-4">
    @php
        $activeTab = request('tab', 'rental');
    @endphp
    
    <!-- Judul Header Halaman -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold fs-3 mb-1 text-primary">Permintaan Pengajuan</h4>
            <p class="text-muted mb-0">Kelola dan pantau seluruh aktivitas pesanan masuk</p>
        </div>
        <div class="d-flex gap-2 position-relative" style="z-index: 1050;">
            <button class="btn btn-white border shadow-sm rounded-pill px-4" onclick="location.reload()">
                <i class="bx bx-refresh me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <!-- Statistik Pesanan -->
    <div class="row g-3 mb-4 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5">
        <!-- Total Pesanan -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-primary-subtle text-primary rounded-circle p-2 mb-2">
                        <i class="bx bx-shopping-bag fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Total Pesanan</small>
                    <h3 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['total'] }}">0</span></h3>
                </div>
            </div>
        </div>
        <!-- Menunggu -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-warning-subtle text-warning rounded-circle p-2 mb-2">
                        <i class="bx bx-time fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Menunggu</small>
                    <h3 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['pending'] }}">0</span></h3>
                </div>
            </div>
        </div>
        <!-- Sedang Proses -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-info-subtle text-info rounded-circle p-2 mb-2">
                        <i class="bx bx-package fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Sedang Proses</small>
                    <h3 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['active_rental_count'] }}">0</span></h3>
                </div>
            </div>
        </div>
        <!-- Ditolak -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-secondary-subtle text-secondary rounded-circle p-2 mb-2">
                        <i class="bx bx-x-circle fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Ditolak</small>
                    <h3 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['rejected'] }}">0</span></h3>
                </div>
            </div>
        </div>
        <!-- Minta Batal -->
        <div class="col">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="avatar avatar-md bg-danger-subtle text-danger rounded-circle p-2 mb-2">
                        <i class="bx bx-error-circle fs-3"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.65rem;">Minta Batal</small>
                    <h3 class="fw-bold mb-0 text-dark"><span class="count-up" data-value="{{ $stats['cancellation_pending'] }}">0</span></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Status -->
    <div class="d-flex gap-2 mb-4 overflow-auto pb-2">
        <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index', ['status' => 'all'] + request()->except('status')) }}" 
           class="btn btn-sm rounded-pill px-3 {{ request('status', 'all') == 'all' ? 'btn-dark' : 'bg-white text-secondary border shadow-sm' }}">
            Semua
        </a>
        <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index', ['status' => 'pending'] + request()->except('status')) }}" 
           class="btn btn-sm rounded-pill px-3 {{ request('status') == 'pending' ? 'btn-warning text-white' : 'bg-white text-secondary border shadow-sm' }}">
            Menunggu
        </a>
        <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index', ['status' => 'in_process'] + request()->except('status')) }}" 
           class="btn btn-sm rounded-pill px-3 {{ request('status') == 'in_process' ? 'btn-info text-white' : 'bg-white text-secondary border shadow-sm' }}">
            Sedang Proses
        </a>
        <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index', ['status' => 'completed'] + request()->except('status')) }}" 
           class="btn btn-sm rounded-pill px-3 {{ request('status') == 'completed' ? 'btn-success text-white' : 'bg-white text-secondary border shadow-sm' }}">
            Selesai
        </a>
        <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index', ['status' => 'rejected'] + request()->except('status')) }}" 
           class="btn btn-sm rounded-pill px-3 {{ request('status') == 'rejected' ? 'btn-secondary text-white' : 'bg-white text-secondary border shadow-sm' }}">
            Ditolak
        </a>
        <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index', ['status' => 'cancellation_pending'] + request()->except('status')) }}" 
           class="btn btn-sm rounded-pill px-3 {{ request('status') == 'cancellation_pending' ? 'btn-danger text-white' : 'bg-white text-secondary border shadow-sm' }}">
            Minta Batal
        </a>
    </div>

    <!-- Tab Konten Utama -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <ul class="nav nav-pills card-header-pills gap-2" id="orderTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'rental' ? 'active' : '' }} rounded-pill px-4 fw-semibold" id="rental-tab" data-bs-toggle="tab" data-bs-target="#rental-pane" type="button" role="tab">
                        <i class="bx bx-wrench me-2"></i>Penyewaan Alat
                        @php $rentalTotal = $notificationCounts['rental']['total'] ?? 0; @endphp
                        @php $rentalCount = $rentalTotal > 0 ? $rentalTotal : $rentalRequests->count(); @endphp
                        <span id="rental-badge" 
                              class="badge {{ $rentalTotal > 0 ? 'bg-danger text-white' : 'bg-white text-primary' }} ms-2 shadow-sm"
                              data-default-count="{{ $rentalRequests->count() }}">
                            {{ $rentalCount }}
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'gas' ? 'active' : '' }} rounded-pill px-4 fw-semibold" id="gas-tab" data-bs-toggle="tab" data-bs-target="#gas-pane" type="button" role="tab">
                        <i class="bx bxs-gas-pump me-2"></i>Pembelian Gas
                        @php $gasTotal = $notificationCounts['gas']['total'] ?? 0; @endphp
                        @php $gasCount = $gasTotal > 0 ? $gasTotal : $gasOrders->count(); @endphp
                        <span id="gas-badge" 
                              class="badge {{ $gasTotal > 0 ? 'bg-danger text-white' : 'bg-white text-primary' }} ms-2 shadow-sm"
                              data-default-count="{{ $gasOrders->count() }}">
                            {{ $gasCount }}
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'mobil' ? 'active' : '' }} rounded-pill px-4 fw-semibold" id="mobil-tab" data-bs-toggle="tab" data-bs-target="#mobil-pane" type="button" role="tab">
                        <i class="bx bx-car me-2"></i>Penyewaan Mobil
                        @php $mobilTotal = $notificationCounts['mobil']['total'] ?? 0; @endphp
                        @php $mobilCount = $mobilTotal > 0 ? $mobilTotal : $mobilRequests->count(); @endphp
                        <span id="mobil-badge" 
                              class="badge {{ $mobilTotal > 0 ? 'bg-danger text-white' : 'bg-white text-primary' }} ms-2 shadow-sm"
                              data-default-count="{{ $mobilRequests->count() }}">
                            {{ $mobilCount }}
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'fasilitas' ? 'active' : '' }} rounded-pill px-4 fw-semibold" id="fasilitas-tab" data-bs-toggle="tab" data-bs-target="#fasilitas-pane" type="button" role="tab">
                        <i class="bx bx-building-house me-2"></i>Fasilitas Umum
                        @php $fasilitasTotal = $notificationCounts['fasilitas']['total'] ?? 0; @endphp
                        @php $fasilitasCount = $fasilitasTotal > 0 ? $fasilitasTotal : $fasilitasRequests->count(); @endphp
                        <span id="fasilitas-badge" 
                              class="badge {{ $fasilitasTotal > 0 ? 'bg-danger text-white' : 'bg-white text-primary' }} ms-2 shadow-sm"
                              data-default-count="{{ $fasilitasRequests->count() }}">
                            {{ $fasilitasCount }}
                        </span>
                    </button>
                </li>
            </ul>

            {{-- Area Teks Notifikasi --}}
            <div id="notification-text" class="text-danger fw-semibold small d-flex align-items-center mt-2 mt-md-0">
                {{-- Teks akan dimasukkan lewat JS --}}
                @php
                    $messages = [];
                    // Rental
                    if($notificationCounts['rental']['pending'] > 0) $messages[] = $notificationCounts['rental']['pending'] . " Pesanan Alat Baru";
                    if($notificationCounts['rental']['cancellation'] > 0) $messages[] = $notificationCounts['rental']['cancellation'] . " Pembatalan Alat";
                    // Gas
                    if($notificationCounts['gas']['pending'] > 0) $messages[] = $notificationCounts['gas']['pending'] . " Pesanan Gas Baru";
                    if($notificationCounts['gas']['cancellation'] > 0) $messages[] = $notificationCounts['gas']['cancellation'] . " Pembatalan Gas";
                    // Mobil
                    if($notificationCounts['mobil']['pending'] > 0) $messages[] = $notificationCounts['mobil']['pending'] . " Pesanan Mobil Baru";
                    if($notificationCounts['mobil']['cancellation'] > 0) $messages[] = $notificationCounts['mobil']['cancellation'] . " Pembatalan Mobil";
                    // Fasilitas
                    if($notificationCounts['fasilitas']['pending'] > 0) $messages[] = $notificationCounts['fasilitas']['pending'] . " Pesanan Fasilitas Baru";
                    if($notificationCounts['fasilitas']['cancellation'] > 0) $messages[] = $notificationCounts['fasilitas']['cancellation'] . " Pembatalan Fasilitas";

                    $finalMessage = !empty($messages) ? '<i class="bx bx-bell bx-tada me-2"></i>Anda memiliki: ' . implode(', ', $messages) : '';
                @endphp
                {!! strip_tags($finalMessage, '<i><b><br><span>') !!}
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="tab-content" id="orderTabsContent">
                
                <!-- RENTAL TAB -->
                <div class="tab-pane fade {{ $activeTab == 'rental' ? 'show active' : '' }}" id="rental-pane" role="tabpanel">
                    @if($rentalRequests->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-folder-open fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Belum ada permintaan penyewaan</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Nama Penyewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Alat</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Tanggal Sewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rentalRequests as $req)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm border rounded-circle p-1 me-3">
                                                    <span class="avatar-initial rounded-circle bg-primary-subtle text-primary fw-bold">
                                                        {{ strtoupper(substr($req->recipient_name ?? $req->user->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold text-dark">{{ $req->recipient_name ?? $req->user->name }}</h6>
                                                    <small class="text-muted">{{ $req->user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $req->barang->nama_barang ?? 'Unknown' }}</div>
                                            <small class="text-muted">{{ $req->quantity }} Unit</small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium text-dark">{{ \Carbon\Carbon::parse($req->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</span>
                                                <small class="text-primary">{{ $req->days_count }} Hari</small>
                                            </div>
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $req->status, 'cancelStatus' => $req->cancellation_status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end">
                                                {{-- Logic Action Buttons --}}
                                                @if($req->status != 'pending' && $req->cancellation_status != 'pending')
                                                    @if($req->delivery_method == 'antar')
                                                        {{-- Logic Antar --}}
                                                        @if($req->status == 'confirmed')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'rental', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="being_prepared">
                                                                <button type="submit" class="btn btn-sm btn-outline-warning shadow-sm rounded-pill px-3" onclick="return confirm('Mulai siapkan pesanan ini?')">
                                                                    <i class="bx bx-package me-1"></i>Proses
                                                                </button>
                                                            </form>
                                                        @elseif($req->status == 'being_prepared')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'rental', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="in_delivery">
                                                                <button type="submit" class="btn btn-sm btn-outline-info shadow-sm rounded-pill px-3" onclick="return confirm('Mulai kirim pesanan ini?')">
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
                                                                     onclick="prepareReturn({{ $req->id }})"
                                                                     data-bs-toggle="modal" data-bs-target="#returnModal">
                                                                 <i class="bx bx-check-circle me-1"></i>Selesai
                                                             </button>
                                                        @endif
                                                    @else
                                                        {{-- Logic Jemput --}}
                                                        @if($req->status == 'confirmed')
                                                             <button type="button" class="btn btn-sm btn-outline-warning shadow-sm rounded-pill px-3" 
                                                                     onclick="prepareUpload({{ $req->id }}, 'rental', 'jemput')"
                                                                     data-bs-toggle="modal" data-bs-target="#uploadProofModal">
                                                                 <i class="bx bx-package me-1"></i>Siapkan
                                                             </button>
                                                        @elseif($req->status == 'being_prepared' || $req->status == 'approved')
                                                             <button type="button" class="btn btn-sm btn-outline-success shadow-sm rounded-pill px-3" 
                                                                     onclick="prepareReturn({{ $req->id }})"
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
                                                    {{-- Tombol Aksi untuk Pembatalan --}}
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
                                                    {{-- Tombol Aksi untuk Pesanan Baru --}}
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
                    @endif
                </div>

                <!-- GAS TAB -->
                <!-- GAS TAB -->
                <div class="tab-pane fade {{ $activeTab == 'gas' ? 'show active' : '' }}" id="gas-pane" role="tabpanel">
                    @if($gasOrders->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-cylinder fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Belum ada permintaan gas</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Nama Pembeli</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Produk Gas</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Tanggal Order</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gasOrders as $order)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm border rounded-circle p-1 me-3">
                                                    <span class="avatar-initial rounded-circle bg-info-subtle text-info fw-bold">
                                                        {{ strtoupper(substr($order->full_name ?? $order->user->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold text-dark">{{ $order->full_name ?? $order->user->name }}</h6>
                                                    <small class="text-muted">{{ $order->address }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $order->item_name }}</div>
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
                                            <div class="d-flex gap-2 justify-content-end">
                                                {{-- Action Buttons --}}
                                                @if($order->status === 'confirmed' && $order->cancellation_status != 'pending')
                                                    {{-- Confirmed -> Selesaikan --}}
                                                     <form action="{{ route('admin.aktivitas.update-status', ['id' => $order->id, 'type' => 'gas']) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="btn btn-sm btn-outline-success shadow-sm rounded-pill px-3" onclick="return confirm('Apakah Anda yakin ingin menyelesaikan pesanan ini?')">
                                                            <i class="bx bx-check-circle me-1"></i>Selesaikan
                                                        </button>
                                                    </form>
                                                @endif

                                                <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'gas']) }}" 
                                                   class="btn btn-sm btn-icon btn-outline-primary" 
                                                   data-bs-toggle="tooltip" title="Lihat Detail">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                
                                                @if($order->cancellation_status == 'pending')
                                                    {{-- Tombol Aksi untuk Pembatalan --}}
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
                                                    {{-- Tombol Aksi untuk Pesanan Baru --}}
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
                    @endif
                </div>

                <!-- MOBIL TAB -->
                <div class="tab-pane fade {{ $activeTab == 'mobil' ? 'show active' : '' }}" id="mobil-pane" role="tabpanel">
                    @if($mobilRequests->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-car fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Belum ada permintaan penyewaan mobil</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Penyewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Mobil & Opsi</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Waktu Sewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mobilRequests as $req)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm border rounded-circle p-1 me-3">
                                                    <span class="avatar-initial rounded-circle bg-primary-subtle text-primary fw-bold">
                                                        {{ strtoupper(substr($req->recipient_name ?? $req->user->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold text-dark">{{ $req->recipient_name ?? $req->user->name }}</h6>
                                                    <small class="text-muted">{{ $req->user->phone ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $req->mobil->nama_mobil ?? 'Unknown' }}</div>
                                            <small class="text-muted">Supir: {{ $req->dengan_supir ? 'Ya' : 'Tidak' }} | BBM: {{ $req->mobil->bbm_ditanggung ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium text-dark">{{ \Carbon\Carbon::parse($req->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $req->status, 'cancelStatus' => $req->cancellation_status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end">
                                                @if($req->status != 'pending' && $req->cancellation_status != 'pending')
                                                    @if($req->dengan_supir)
                                                        @if($req->status == 'confirmed')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="process">
                                                                <button type="submit" class="btn btn-sm btn-outline-warning shadow-sm rounded-pill px-3" onclick="return confirm('Mulai siapkan mobil ini?')">
                                                                    <i class="bx bx-package me-1"></i>Proses
                                                                </button>
                                                            </form>
                                                        @elseif($req->status == 'process')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="delivering">
                                                                <button type="submit" class="btn btn-sm btn-outline-info shadow-sm rounded-pill px-3" onclick="return confirm('Mulai jalan / kirim mobil?')">
                                                                    <i class="bx bx-car me-1"></i>Kirim
                                                                </button>
                                                            </form>
                                                        @elseif($req->status == 'delivering')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="arrived">
                                                                <button type="submit" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3" onclick="return confirm('Konfirmasi tiba?')">
                                                                    <i class="bx bx-check me-1"></i>Tiba
                                                                </button>
                                                            </form>
                                                        @elseif($req->status == 'arrived')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="completed">
                                                                <button type="submit" class="btn btn-sm btn-outline-success shadow-sm rounded-pill px-3" onclick="return confirm('Selesaikan penyewaan ini?')">
                                                                    <i class="bx bx-check-circle me-1"></i>Selesai
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        @if($req->status == 'confirmed')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'mobil', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="completed">
                                                                <button type="submit" class="btn btn-sm btn-outline-success shadow-sm rounded-pill px-3" onclick="return confirm('Serahkan Kunci / Selesaikan penyewaan ini?')">
                                                                    <i class="bx bx-key me-1"></i>Serahkan & Selesai
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
                    @endif
                </div>

                <!-- FASILITAS UMUM TAB -->
                <div class="tab-pane fade {{ $activeTab == 'fasilitas' ? 'show active' : '' }}" id="fasilitas-pane" role="tabpanel">
                    @if($fasilitasRequests->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-building-house fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Belum ada permintaan fasilitas umum</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Penyewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Fasilitas & Opsi</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Waktu Sewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fasilitasRequests as $req)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm border rounded-circle p-1 me-3">
                                                    <span class="avatar-initial rounded-circle bg-primary-subtle text-primary fw-bold">
                                                        {{ strtoupper(substr($req->recipient_name ?? $req->user->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold text-dark">{{ $req->recipient_name ?? $req->user->name }}</h6>
                                                    <small class="text-muted">{{ $req->user->phone ?? '-' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $req->fasilitas->nama_fasilitas ?? 'Unknown' }}</div>
                                            @if($req->fasilitas->kategori == 'Kendaraan' || $req->fasilitas->opsi_supir)
                                                <small class="text-muted">Supir: {{ $req->dengan_supir ? 'Ya' : 'Tidak' }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium text-dark">{{ \Carbon\Carbon::parse($req->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($req->end_date)->format('d M Y') }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $req->status, 'cancelStatus' => $req->cancellation_status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex gap-2 justify-content-end">
                                                @if($req->status != 'pending' && $req->cancellation_status != 'pending')
                                                    @if($req->delivery_method == 'antar' || $req->dengan_supir)
                                                        @if($req->status == 'confirmed' || $req->status == 'approved')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'fasilitas', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="delivering">
                                                                <button type="submit" class="btn btn-sm btn-outline-info shadow-sm rounded-pill px-3" onclick="return confirm('Mulai jalan / kirim?')">
                                                                    <i class="bx bx-truck me-1"></i>Kirim/Berangkat
                                                                </button>
                                                            </form>
                                                        @elseif($req->status == 'delivering')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'fasilitas', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="arrived">
                                                                <button type="submit" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3" onclick="return confirm('Konfirmasi tiba/digunakan?')">
                                                                    <i class="bx bx-check me-1"></i>Tiba
                                                                </button>
                                                            </form>
                                                        @elseif($req->status == 'arrived' || $req->status == 'ongoing')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'fasilitas', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="completed">
                                                                <button type="submit" class="btn btn-sm btn-outline-success shadow-sm rounded-pill px-3" onclick="return confirm('Selesaikan penyewaan ini?')">
                                                                    <i class="bx bx-check-circle me-1"></i>Selesai
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        @if($req->status == 'confirmed' || $req->status == 'approved')
                                                            <form action="{{ route('admin.aktivitas.update-status', ['type' => 'fasilitas', 'id' => $req->id]) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="status" value="completed">
                                                                <button type="submit" class="btn btn-sm btn-outline-success shadow-sm rounded-pill px-3" onclick="return confirm('Selesaikan peminjaman ini?')">
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
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Tolak Permintaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control bg-light border-0 py-3" rows="4" placeholder="Jelaskan alasan penolakan permintaan..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
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
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="uploadProofTitle">Upload Bukti Pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadProofForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="uploadId" name="id">
                    <input type="hidden" id="uploadType" name="type">
                    <div class="mb-3">
                        <label class="form-label text-muted" id="uploadLabel">Foto Bukti Penerimaan Barang</label>
                        <input type="file" name="delivery_proof" class="form-control" accept="image/*" required>
                        <div class="form-text" id="uploadHelp">Upload foto saat barang diterima oleh pelanggan</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Upload & Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Pengembalian Alat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="returnForm">
                <input type="hidden" id="returnId" name="id">
                <div class="modal-body">
                    <div class="alert alert-info border-0 d-flex align-items-center mb-3">
                        <i class="bx bx-info-circle fs-4 me-2"></i>
                        <div>
                            Stok akan otomatis <strong>bertambah</strong> setelah dikonfirmasi.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Waktu Pengembalian</label>
                        <input type="datetime-local" name="return_time" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">Konfirmasi Pengembalian</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Custom Tab Styling */
    .nav-pills .nav-link {
        color: #6c757d;
        background-color: transparent;
        transition: all 0.3s ease;
    }
    .nav-pills .nav-link:hover {
        background-color: #f8f9fa;
        color: #0d6efd;
    }
    .nav-pills .nav-link.active {
        background-color: #0d6efd;
        color: #fff;
        box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
    }
    .progress-bar {
        transition: width 1s ease-in-out;
    }
</style>

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
                // Show loader
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
            // Untuk penolakan pembatalan, kita butuh input alasan
            // Gunakan SweetAlert dengan input
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
            return; // Hentikan eksekusi di sini, lanjut di submitCancellationResponse
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

    // Upload Proof Logic
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

    document.getElementById('uploadProofForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('uploadId').value;
        const type = document.getElementById('uploadType').value;
        
        const url = `{{ url('admin/aktivitas/permintaan-pengajuan') }}/${type}/${id}/delivery-proof`;
        
        // Show loading
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
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
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

    // Return Logic
    function prepareReturn(id) {
        document.getElementById('returnId').value = id;
    }

    document.getElementById('returnForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const id = document.getElementById('returnId').value;
        const url = `{{ url('admin/aktivitas/permintaan-pengajuan/rental') }}/${id}/return`;
        
        // Show confirmation
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
                // Show Loading
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
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        return_time: formData.get('return_time')
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

    // Force reload on back button (bfcache)
    window.onpageshow = function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        // Function to update filter links
        function updateFilterLinks(type) {
            const filterLinks = document.querySelectorAll('.d-flex.gap-2.mb-4.overflow-auto a');
            filterLinks.forEach(link => {
                let url = new URL(link.href);
                url.searchParams.set('tab', type);
                link.href = url.toString();
            });
            
            // Also update the browser URL state without reloading
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('tab', type);
            window.history.replaceState({}, '', currentUrl);
        }

        // Initialize links based on current active tab
        let activeTab = '{{ $activeTab }}';
        updateFilterLinks(activeTab);

        // Listen for tab changes
        const triggerTabList = document.querySelectorAll('button[data-bs-toggle="tab"]');
        triggerTabList.forEach(triggerEl => {
            triggerEl.addEventListener('shown.bs.tab', event => {
                let targetId = event.target.getAttribute('id');
                let type = (targetId === 'gas-tab') ? 'gas' : 'rental';
                updateFilterLinks(type);
            });
        });

        // Real-time Notification Polling
        function checkNotificationCounts() {
            fetch('{{ route("admin.aktivitas.permintaan-pengajuan.counts") }}')
                .then(response => response.json())
                .then(data => {
                    updateBadge('rental', data.rental.total);
                    updateBadge('gas', data.gas.total);
                    updateNotificationText(data);
                })
                .catch(error => console.error('Error fetching counts:', error));
        }

        function updateBadge(type, count) {
            const badge = document.getElementById(type + '-badge');
            if (count > 0) {
                // Show Notification Style
                badge.className = 'badge bg-danger text-white ms-2 shadow-sm';
                badge.innerText = count;
            } else {
                // Revert to Default Style (List Count)
                const defaultCount = badge.getAttribute('data-default-count');
                badge.className = 'badge bg-white text-primary ms-2 shadow-sm';
                badge.innerText = defaultCount;
            }
        }

        function updateNotificationText(data) {
            const container = document.getElementById('notification-text');
            let messages = [];

            if (data.rental.pending > 0) messages.push(data.rental.pending + " Pesanan Alat Baru");
            if (data.rental.cancellation > 0) messages.push(data.rental.cancellation + " Pembatalan Alat");
            
            if (data.gas.pending > 0) messages.push(data.gas.pending + " Pesanan Gas Baru");
            if (data.gas.cancellation > 0) messages.push(data.gas.cancellation + " Pembatalan Gas");

            if (messages.length > 0) {
                container.innerHTML = '<i class="bx bx-bell bx-tada me-2"></i>Anda memiliki: ' + messages.join(', ');
                container.classList.remove('d-none');
            } else {
                container.innerHTML = '';
                container.classList.add('d-none');
            }
        }

        // Check initially and poll every 15 seconds
        setInterval(checkNotificationCounts, 15000);
    });
</script>
@endsection
