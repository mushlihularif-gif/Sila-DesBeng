@extends('admin.layouts.admin')

@section('title', 'Riwayat Pendapatan')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y animate-fade-up">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.laporan.pendapatan') }}" class="btn btn-sm btn-icon btn-outline-secondary rounded-circle" title="Kembali">
                    <i class="bx bx-arrow-back"></i>
                </a>
                <h4 class="fw-bold fs-3 mb-0 text-primary">Riwayat Pendapatan</h4>
            </div>
            <p class="text-muted mb-0">Rincian seluruh transaksi pemasukan</p>
        </div>
        <div class="d-flex gap-2 w-100 w-sm-auto">
            <button class="btn btn-outline-secondary shadow-sm rounded-pill px-4 flex-grow-1 flex-sm-grow-0" type="button" data-bs-toggle="dropdown">
                <i class="bx bx-filter me-2"></i>Filter: {{ str_replace('_', ' ', ucwords($filter)) }}
            </button>
            <ul class="dropdown-menu shadow border-0 rounded-4">
                <li><a class="dropdown-item {{ $filter == 'minggu_ini' ? 'active' : '' }}" href="{{ route('admin.laporan.pendapatan.riwayat', ['filter' => 'minggu_ini']) }}">Minggu Ini</a></li>
                <li><a class="dropdown-item {{ $filter == 'bulan_ini' ? 'active' : '' }}" href="{{ route('admin.laporan.pendapatan.riwayat', ['filter' => 'bulan_ini']) }}">Bulan Ini</a></li>
                <li><a class="dropdown-item {{ $filter == 'tahun_ini' ? 'active' : '' }}" href="{{ route('admin.laporan.pendapatan.riwayat', ['filter' => 'tahun_ini']) }}">Tahun Ini</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item {{ $filter == 'semua' ? 'active' : '' }}" href="{{ route('admin.laporan.pendapatan.riwayat', ['filter' => 'semua']) }}">Semua Waktu</a></li>
            </ul>
        </div>
    </div>

    <!-- Data Table / Empty State -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            @if($history->isEmpty())
                <div class="text-center py-5 px-3">
                    <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="bx bx-history fs-1 opacity-50"></i>
                    </div>
                    <h6 class="text-muted fw-bold mb-1">Belum Ada Riwayat Pendapatan</h6>
                    <p class="text-muted small mb-0">Belum ada riwayat transaksi pendapatan pada rentang waktu ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 rounded-top-start-3 py-3">Tanggal & Waktu</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3">Pemesan & Lokasi</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3">Unit Layanan & Item</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3">Metode Bayar</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3">Nominal Masuk</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3">Status</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 rounded-top-end-3 py-3">Bukti / Struk</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($history as $item)
                            @php
                                $typeColor = 'primary';
                                $sLower = strtolower($item->type);
                                if (str_contains($sLower, 'gas')) $typeColor = 'warning';
                                elseif (str_contains($sLower, 'mobil')) $typeColor = 'info';
                                elseif (str_contains($sLower, 'fasilitas')) $typeColor = 'danger';
                                elseif (str_contains($sLower, 'pasar')) $typeColor = 'success';
                                elseif (str_contains($sLower, 'manual')) $typeColor = 'secondary';

                                $payMethod = strtolower($item->payment_method ?? 'transfer');
                                $payBadge = 'bg-label-primary';
                                $payIcon = 'bx-credit-card';
                                $payLabel = ucfirst($item->payment_method ?? 'Transfer');
                                if (str_contains($payMethod, 'tunai') || str_contains($payMethod, 'cash')) {
                                    $payBadge = 'bg-label-success';
                                    $payIcon = 'bx-money';
                                    $payLabel = 'Tunai / Cash';
                                } elseif (str_contains($payMethod, 'cod')) {
                                    $payBadge = 'bg-label-warning';
                                    $payIcon = 'bx-package';
                                    $payLabel = 'COD (Bayar di Tempat)';
                                } elseif (str_contains($payMethod, 'qris')) {
                                    $payBadge = 'bg-label-info';
                                    $payIcon = 'bx-qr';
                                    $payLabel = 'QRIS';
                                }
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex flex-column">
                                        <span class="text-dark fw-bold" style="font-size: 0.88rem;">{{ \Carbon\Carbon::parse($item->date)->translatedFormat('l, d M Y') }}</span>
                                        <span class="text-muted small" style="font-size: 0.78rem;">
                                            <i class="bx bx-time-five me-1 text-secondary"></i>{{ \Carbon\Carbon::parse($item->date)->format('H:i') }} WIB
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3 flex-shrink-0">
                                            @if($item->user_photo)
                                                <img src="{{ $item->user_photo }}" alt="Avatar" class="rounded-circle" style="width:36px; height:36px; object-fit:cover;">
                                            @else
                                                <div class="avatar-initial rounded-circle bg-label-primary text-primary fw-bold d-flex align-items-center justify-content-center shadow-xs" style="width:36px; height:36px; font-size: 0.85rem;">
                                                    {{ strtoupper(substr($item->user_name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-column overflow-hidden" style="max-width: 180px;">
                                            <span class="text-dark fw-semibold text-truncate" style="font-size: 0.88rem;" title="{{ $item->user_name }}">{{ $item->user_name }}</span>
                                            <span class="text-muted small text-truncate" style="font-size: 0.75rem;" title="{{ $item->location }}">
                                                <i class="bx bx-map text-danger me-1"></i>{{ $item->location }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge bg-label-{{ $typeColor }} rounded-pill px-2.5 py-1 mb-1 d-inline-block fw-bold" style="width: max-content; font-size: 0.75rem;">
                                            {{ $item->type }}
                                        </span>
                                        <span class="text-dark small fw-medium text-truncate" style="max-width: 220px; font-size: 0.8rem;" title="{{ $item->item_name }}">
                                            {{ $item->item_name }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $payBadge }} rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.75rem;">
                                        <i class="bx {{ $payIcon }} me-1"></i>{{ $payLabel }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-success fw-bold" style="font-size: 0.95rem;">
                                        +Rp {{ number_format($item->amount, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    @include('admin.partials.status-badge', ['status' => $item->status])
                                </td>
                                <td class="text-center pe-4">
                                    @if($item->proof)
                                        @if($item->type == 'Laporan Manual')
                                            <a href="{{ $item->proof_route }}" target="_blank" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 py-1 text-primary d-inline-flex align-items-center gap-1" title="Lihat Bukti Lampiran">
                                                <i class="bx bx-image fs-6"></i>
                                                <span class="small fw-semibold">Bukti</span>
                                            </a>
                                        @else
                                            <a href="{{ $item->proof_download ?? $item->proof_route }}" target="_blank" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 py-1 text-primary d-inline-flex align-items-center gap-1" title="Lihat Bukti Transfer">
                                                <i class="bx bx-image fs-6"></i>
                                                <span class="small fw-semibold">Bukti</span>
                                            </a>
                                        @endif
                                    @else
                                        @if($item->proof_route && !str_contains($item->type, 'Laporan Manual'))
                                            <a href="{{ $item->proof_route }}" target="_blank" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 py-1 text-info d-inline-flex align-items-center gap-1" title="Lihat Struk Sistem">
                                                <i class="bx bx-receipt fs-6"></i>
                                                <span class="small fw-semibold">Struk</span>
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    @endif
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
@endsection
