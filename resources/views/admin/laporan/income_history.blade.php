@extends('admin.layouts.admin')

@section('title', 'Riwayat Pendapatan')

@section('content')
<style>
    .hist-mobile-card {
        border: 1px solid #eef1f6;
        border-radius: 14px;
        background: #fff;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }
</style>

@php
    $filterLabels = [
        'minggu_ini' => 'Minggu Ini',
        'bulan_ini' => 'Bulan Ini',
        'tahun_ini' => 'Tahun Ini',
        'semua' => 'Semua Waktu',
    ];
    $totalNominal = $history->sum('amount');
    $totalRows = $history->count();
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="mb-2">
                <a href="{{ route('admin.laporan.pendapatan') }}" class="segi-back no-print">
                    <i class="bx bx-arrow-back me-1"></i>Kembali ke Laporan Pendapatan
                </a>
            </div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Laporan Pendapatan /</span> Riwayat Pemasukan
            </h4>
            <p class="text-muted small mb-0">Rincian seluruh transaksi pemasukan terverifikasi dan catatan manual.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-start justify-content-md-end no-print">
            <div class="dropdown flex-grow-1 flex-md-grow-0">
                <button class="btn btn-white border shadow-sm rounded-pill px-4 w-100 d-inline-flex align-items-center justify-content-center gap-2" type="button" data-bs-toggle="dropdown">
                    <i class="bx bx-filter-alt text-primary"></i>
                    <span>Periode: {{ $filterLabels[$filter] ?? str_replace('_', ' ', ucwords($filter)) }}</span>
                    <i class="bx bx-chevron-down"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                    @foreach($filterLabels as $key => $label)
                        <li>
                            <a class="dropdown-item {{ $filter == $key ? 'active' : '' }}" href="{{ route('admin.laporan.pendapatan.riwayat', ['filter' => $key]) }}">
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn btn-primary rounded-pill px-3 shadow-sm" onclick="window.print()">
                <i class="bx bx-printer me-1"></i>Cetak
            </button>
        </div>
    </div>

    <!-- Summary Strip -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="avatar bg-label-success text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="bx bx-wallet fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Total Pemasukan ({{ $filterLabels[$filter] ?? 'Terpilih' }})</small>
                        <h4 class="fw-bold text-success mb-0">Rp {{ number_format($totalNominal, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="avatar bg-label-primary text-primary rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="bx bx-receipt fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Jumlah Transaksi Tercatat</small>
                        <h4 class="fw-bold text-dark mb-0">{{ $totalRows }} Transaksi</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        @if(!$history->isEmpty())
        <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2 no-print">
            <h6 class="fw-bold mb-0">Daftar Transaksi Masuk</h6>
            <div style="min-width: 220px;">
                <div class="input-group input-group-sm input-group-merge rounded-pill overflow-hidden border">
                    <span class="input-group-text bg-light border-0"><i class="bx bx-search text-muted"></i></span>
                    <input type="text" id="hist-search-input" class="form-control bg-light border-0 ps-1" placeholder="Cari layanan, item, metode...">
                </div>
            </div>
        </div>
        @endif

        <div class="card-body p-0">
            @if($history->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="bx bx-history fs-1 opacity-50"></i>
                    </div>
                    <h6 class="text-dark fw-bold mb-1">Belum Ada Riwayat Pendapatan</h6>
                    <p class="text-muted small mb-0">Belum ada pemasukan tercatat pada rentang waktu {{ strtolower($filterLabels[$filter] ?? 'ini') }}.</p>
                </div>
            @else
                <!-- Desktop Table -->
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase text-secondary small fw-bold">Tanggal &amp; Waktu</th>
                                <th class="py-3 text-uppercase text-secondary small fw-bold">Unit Layanan &amp; Item</th>
                                <th class="py-3 text-uppercase text-secondary small fw-bold">Metode Bayar</th>
                                <th class="py-3 text-uppercase text-secondary small fw-bold">Nominal Masuk</th>
                                <th class="py-3 text-uppercase text-secondary small fw-bold">Status</th>
                                <th class="text-center pe-4 py-3 text-uppercase text-secondary small fw-bold no-print">Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $item)
                            @php
                                $typeColor = 'primary';
                                $sLower = strtolower($item->type);
                                if (str_contains($sLower, 'gas')) $typeColor = 'warning';
                                elseif (str_contains($sLower, 'mobil') || str_contains($sLower, 'transportasi')) $typeColor = 'info';
                                elseif (str_contains($sLower, 'fasilitas')) $typeColor = 'success';
                                elseif (str_contains($sLower, 'manual')) $typeColor = 'secondary';

                                $payMethod = strtolower($item->payment_method ?? 'manual');
                                $payBadge = 'bg-label-primary';
                                $payIcon = 'bx-credit-card';
                                $payLabel = ucfirst($item->payment_method ?? 'Transfer / Tunai');
                                if (str_contains($payMethod, 'tunai') || str_contains($payMethod, 'cash')) {
                                    $payBadge = 'bg-label-success';
                                    $payIcon = 'bx-money';
                                    $payLabel = str_contains($payMethod, 'manual') ? 'Manual (Tunai/Transfer)' : 'Tunai / Cash';
                                }
                                $searchKey = strtolower($item->type . ' ' . $item->item_name . ' ' . $payLabel . ' ' . ($item->user_name ?? ''));
                            @endphp
                            <tr class="hist-searchable" data-search="{{ $searchKey }}">
                                <td class="ps-4">
                                    <div class="fw-bold text-dark small">{{ \Carbon\Carbon::parse($item->date)->translatedFormat('d M Y') }}</div>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($item->date)->format('H:i') }} WIB</small>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $typeColor }} rounded-pill px-2 py-1 mb-1 fw-semibold" style="font-size: 0.72rem;">
                                        {{ $item->type }}
                                    </span>
                                    <div class="text-dark small fw-medium">{{ $item->item_name }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $payBadge }} rounded-pill px-2 py-1 fw-medium" style="font-size: 0.74rem;">
                                        <i class="bx {{ $payIcon }} me-1"></i>{{ $payLabel }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-success fw-bold">+Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                                </td>
                                <td>@include('admin.partials.status-badge', ['status' => $item->status])</td>
                                <td class="text-center pe-4 no-print">
                                    @if($item->proof_route)
                                        <a href="{{ $item->proof_download ?? $item->proof_route }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bx bx-image-alt me-1"></i>Lihat
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="d-md-none p-3">
                    @foreach($history as $item)
                    @php
                        $typeColor = 'primary';
                        $sLower = strtolower($item->type);
                        if (str_contains($sLower, 'gas')) $typeColor = 'warning';
                        elseif (str_contains($sLower, 'mobil') || str_contains($sLower, 'transportasi')) $typeColor = 'info';
                        elseif (str_contains($sLower, 'fasilitas')) $typeColor = 'success';
                        elseif (str_contains($sLower, 'manual')) $typeColor = 'secondary';

                        $payMethod = strtolower($item->payment_method ?? 'manual');
                        $payLabel = str_contains($payMethod, 'manual') ? 'Manual (Tunai/Transfer)' : ucfirst($item->payment_method ?? 'Tunai / Transfer');
                        $searchKey = strtolower($item->type . ' ' . $item->item_name . ' ' . $payLabel . ' ' . ($item->user_name ?? ''));
                    @endphp
                    <div class="hist-mobile-card shadow-xs hist-searchable" data-search="{{ $searchKey }}">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <span class="badge bg-label-{{ $typeColor }} rounded-pill px-2 py-1 fw-semibold" style="font-size: 0.72rem;">
                                {{ $item->type }}
                            </span>
                            <div>@include('admin.partials.status-badge', ['status' => $item->status])</div>
                        </div>
                        <div class="fw-semibold text-dark mb-1">{{ $item->item_name }}</div>
                        <div class="small text-muted mb-2">
                            <i class="bx bx-calendar me-1"></i>{{ \Carbon\Carbon::parse($item->date)->translatedFormat('d M Y, H:i') }} WIB
                            &bull; {{ $payLabel }}
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="text-success fw-bold">+Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                            @if($item->proof_route)
                                <a href="{{ $item->proof_download ?? $item->proof_route }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary rounded-pill px-3 no-print">
                                    <i class="bx bx-image-alt me-1"></i>Bukti
                                </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('hist-search-input');
    if (!input) return;
    input.addEventListener('input', function() {
        const kw = this.value.trim().toLowerCase();
        document.querySelectorAll('.hist-searchable').forEach(function(el) {
            const hay = (el.getAttribute('data-search') || el.textContent || '').toLowerCase();
            el.style.display = kw === '' || hay.includes(kw) ? '' : 'none';
        });
    });
});
</script>
@endpush
@endsection
