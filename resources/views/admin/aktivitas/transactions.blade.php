@extends('admin.layouts.admin')

@section('title', 'Bukti Transaksi')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold fs-3 mb-1 text-primary">Bukti Transaksi</h4>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-white border shadow-sm rounded-pill px-4" onclick="location.reload()">
                <i class="bx bx-refresh me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-md bg-primary-subtle text-primary rounded-3 p-2 me-3">
                            <i class="bx bx-receipt fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Total Bukti</small>
                            <h4 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-md bg-info-subtle text-info rounded-3 p-2 me-3">
                            <i class="bx bx-wrench fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Penyewaan</small>
                            <h4 class="fw-bold mb-0 text-dark">{{ $stats['rental_total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-md bg-success-subtle text-success rounded-3 p-2 me-3">
                            <i class="bx bxs-gas-pump fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Gas</small>
                            <h4 class="fw-bold mb-0 text-dark">{{ $stats['gas_total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 rounded-4 position-relative overflow-hidden">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar avatar-md bg-warning-subtle text-warning rounded-3 p-2 me-3">
                            <i class="bx bx-money fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Tunai</small>
                            <h4 class="fw-bold mb-0 text-dark">{{ $stats['cash_total'] }}</h4>
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
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill px-4 fw-semibold" id="rental-tab" data-bs-toggle="tab" data-bs-target="#rental-pane" type="button" role="tab">
                        <i class="bx bx-wrench me-2"></i>Penyewaan Alat
                        <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $rentalPayments->count() }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill px-4 fw-semibold" id="gas-tab" data-bs-toggle="tab" data-bs-target="#gas-pane" type="button" role="tab">
                        <i class="bx bxs-gas-pump me-2"></i>Pembelian Gas
                        <span class="badge bg-white text-primary ms-2 shadow-sm">{{ $gasPayments->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
             <div class="tab-content" id="proofTabsContent">
                
                <!-- RENTAL TAB -->
                <div class="tab-pane fade show active" id="rental-pane" role="tabpanel">
                    @if($rentalPayments->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-receipt fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Belum ada bukti pembayaran penyewaan</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
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

                <!-- GAS TAB -->
                <div class="tab-pane fade" id="gas-pane" role="tabpanel">
                      @if($gasPayments->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3"><i class="bx bx-receipt fs-1 text-muted opacity-25"></i></div>
                            <h6 class="text-muted fw-bold">Belum ada bukti pembayaran gas</h6>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
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
            </div>
        </div>
    </div>
</div>

@endsection