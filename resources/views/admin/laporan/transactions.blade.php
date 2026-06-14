@extends('admin.layouts.admin')

@section('title', 'Laporan Transaksi')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold fs-3 mb-1 text-primary">Laporan Transaksi</h4>
            <p class="text-muted mb-0">Rekapitulasi lengkap seluruh transaksi yang tercatat</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary shadow-sm rounded-pill px-4" onclick="window.print()">
                <i class="bx bx-printer me-2"></i>Cetak Laporan
            </button>
            <button class="btn btn-outline-secondary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="bx bx-filter-alt me-2"></i>Filter
            </button>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-md bg-primary-subtle text-primary rounded-3 p-2 me-3">
                            <i class="bx bx-receipt fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Total Transaksi</small>
                            <h4 class="fw-bold mb-0 text-dark">{{ $rentalRequests->count() + $gasOrders->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-md bg-success-subtle text-success rounded-3 p-2 me-3">
                            <i class="bx bx-check-double fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Selesai</small>
                            <h4 class="fw-bold mb-0 text-dark">{{ $rentalRequests->where('status', 'completed')->count() + $gasOrders->where('status', 'completed')->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                         <div class="avatar avatar-md bg-info-subtle text-info rounded-3 p-2 me-3">
                            <i class="bx bx-wrench fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Penyewaan</small>
                            <h4 class="fw-bold mb-0 text-dark">{{ $rentalRequests->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
             <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden position-relative">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                         <div class="avatar avatar-md bg-warning-subtle text-warning rounded-3 p-2 me-3">
                            <i class="bx bxs-gas-pump fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Gas</small>
                            <h4 class="fw-bold mb-0 text-dark">{{ $gasOrders->count() }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Tabs -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4">
             <ul class="nav nav-pills card-header-pills gap-2" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 fw-semibold" id="rental-tab" data-bs-toggle="tab" data-bs-target="#rental-pane" type="button" role="tab">
                        <i class="bx bx-wrench me-2"></i>Penyewaan Alat
                        <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $rentalRequests->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-semibold" id="gas-tab" data-bs-toggle="tab" data-bs-target="#gas-pane" type="button" role="tab">
                        <i class="bx bxs-gas-pump me-2"></i>Pembelian Gas
                        <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $gasOrders->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
             <div class="tab-content" id="reportTabsContent">
                
                <!-- RENTAL RESULTS -->
                <div class="tab-pane fade show active" id="rental-pane" role="tabpanel">
                    @if($rentalRequests->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-bar-chart-alt-2 fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Tidak ada data transaksi penyewaan</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">ID & Tanggal</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Penyewa</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Alat</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Total</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rentalRequests as $req)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">#{{ $req->order_number ?? $req->id }}</div>
                                            <small class="text-muted">{{ $req->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm border rounded-circle p-1 me-2">
                                                    <span class="avatar-initial rounded-circle bg-primary-subtle text-primary fw-bold">
                                                        {{ strtoupper(substr($req->recipient_name ?? $req->user->name ?? 'U', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span class="fw-medium text-dark">{{ $req->recipient_name ?? $req->user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $req->item_name ?? 'Alat' }}</div>
                                            <small class="text-muted">{{ $req->quantity }} Unit</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">Rp {{ number_format($req->price ?? $req->total_amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $req->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$req->id, 'rental']) }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 text-primary">
                                                <i class="bx bx-show me-1"></i>Detail
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- GAS RESULTS -->
                <div class="tab-pane fade" id="gas-pane" role="tabpanel">
                    @if($gasOrders->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-bar-chart-alt-2 fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Tidak ada data transaksi gas</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">ID & Tanggal</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Pembeli</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Produk</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Total</th>
                                        <th class="py-3 text-secondary text-uppercase small fw-bold">Status</th>
                                        <th class="text-end pe-4 py-3 text-secondary text-uppercase small fw-bold">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gasOrders as $order)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">#{{ $order->order_number ?? $order->id }}</div>
                                            <small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                 <div class="avatar avatar-sm border rounded-circle p-1 me-2">
                                                    <span class="avatar-initial rounded-circle bg-info-subtle text-info fw-bold">
                                                        {{ strtoupper(substr($order->full_name ?? $order->user->name ?? 'U', 0, 1)) }}
                                                    </span>
                                                </div>
                                                <span class="fw-medium text-dark">{{ $order->full_name ?? $order->user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $order->item_name ?? 'Gas LPG' }}</div>
                                            <small class="text-muted">{{ $order->quantity }} Tabung</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">Rp {{ number_format($order->price ?? $order->total_amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td>
                                            @include('admin.partials.status-badge', ['status' => $order->status])
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('admin.aktivitas.permintaan-pengajuan.show', [$order->id, 'gas']) }}" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 text-primary">
                                                <i class="bx bx-show me-1"></i>Detail
                                            </a>
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

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Filter Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.laporan.transaksi') }}" method="GET">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small text-uppercase">Status</label>
                        <select name="status" class="form-select border-0 bg-light py-2">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control border-0 bg-light py-2" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control border-0 bg-light py-2" value="{{ request('end_date') }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light rounded-bottom-4">
                    <a href="{{ route('admin.laporan.transaksi') }}" class="btn btn-link text-secondary text-decoration-none">Reset</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Terapkan Filter</button>
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
</style>
@endsection