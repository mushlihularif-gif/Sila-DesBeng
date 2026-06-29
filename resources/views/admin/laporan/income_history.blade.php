@extends('admin.layouts.admin')

@section('title', 'Riwayat Pendapatan')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold fs-3 mb-1 text-primary d-inline-block align-middle">Riwayat Pendapatan</h4>
            <p class="text-muted mb-0">Rincian seluruh transaksi pemasukan</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary shadow-sm rounded-pill px-4" type="button" data-bs-toggle="dropdown">
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

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 rounded-top-start-3 py-3">Tanggal & Waktu</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3">Pemesan</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3">Jenis & Item</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3">Lokasi</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3">Nominal</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 py-3">Status</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 rounded-top-end-3 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $item)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex flex-column">
                                    <span class="text-dark fw-bold">{{ \Carbon\Carbon::parse($item->date)->translatedFormat('d M Y') }}</span>
                                    <span class="text-muted small">{{ \Carbon\Carbon::parse($item->date)->format('H:i') }} WIB</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                        @if($item->user_photo)
                                            <img src="{{ $item->user_photo }}" alt="Avatar" class="rounded-circle" style="width:32px; height:32px; object-fit:cover;">
                                        @else
                                            <div class="avatar-initial rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                                {{ substr($item->user_name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-dark fw-semibold">{{ $item->user_name }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 mb-1 d-inline-block" style="width: max-content;">{{ $item->type }}</span>
                                    <span class="text-muted small text-truncate" style="max-width: 200px;" title="{{ $item->item_name }}">{{ $item->item_name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center text-muted small" style="max-width: 150px;">
                                    <i class="bx bx-map me-1 text-danger"></i>
                                    <span class="text-truncate" title="{{ $item->location }}">{{ $item->location }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-dark fw-bold">Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                @include('admin.partials.status-badge', ['status' => $item->status])
                            </td>
                            <td class="text-center pe-4">
                                @if($item->proof)
                                    @if($item->type == 'Laporan Manual')
                                        <a href="{{ $item->proof_route }}" target="_blank" class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 text-primary" title="Lihat Bukti Lampiran">
                                            <i class="bx bx-image fs-5"></i>
                                        </a>
                                    @else
                                        <a href="{{ $item->proof_download }}" target="_blank" class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 text-primary" title="Lihat Bukti Transfer">
                                            <i class="bx bx-image fs-5"></i>
                                        </a>
                                    @endif
                                @else
                                    @if($item->proof_route && $item->type != 'Laporan Manual')
                                        <a href="{{ $item->proof_route }}" target="_blank" class="btn btn-sm btn-light border shadow-sm rounded-circle p-2 text-info" title="Lihat Struk System">
                                            <i class="bx bx-receipt fs-5"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">Tidak ada</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="mb-3"><i class="bx bx-history fs-1 text-muted opacity-25"></i></div>
                                <h6 class="text-muted fw-bold">Belum ada riwayat pendapatan pada rentang waktu ini.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
