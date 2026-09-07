@extends('admin.layouts.admin')

@section('title', 'Monitoring Transaksi')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Sistem Platform /</span> Monitoring Kesehatan Transaksi</h4>

    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="bx bx-info-circle me-2 fs-5"></i>
        <div>
            Halaman ini menampilkan <strong>kesehatan sistem</strong> (jumlah &amp; status transaksi lintas desa).
            Sejak Midtrans dipusatkan di akun Diskominfotik, dana gateway wilayah mendarat di sini dulu -
            rinciannya per wilayah ada di <a href="{{ route('admin.sistem-platform.penarikan.index') }}">Penarikan Saldo Wilayah</a>.
        </div>
    </div>

    <div class="row g-4 mb-2">
        <div class="col-md-4">
            <div class="d-flex align-items-center bg-light rounded-3 p-3 shadow-sm h-100">
                <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-warning">
                        <i class="bx bx-time-five fs-4"></i>
                    </span>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Rp {{ number_format($walletHealth['total_tertahan'], 0, ',', '.') }}</h6>
                    <small class="text-muted">Dana tertahan (belum dicairkan)</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="d-flex align-items-center bg-light rounded-3 p-3 shadow-sm h-100">
                <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-danger">
                        <i class="bx bx-x-circle fs-4"></i>
                    </span>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">{{ $walletHealth['total_gagal_verifikasi'] }}</h6>
                    <small class="text-muted">Verifikasi gagal/ditolak</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="d-flex align-items-center bg-light rounded-3 p-3 shadow-sm h-100">
                <div class="avatar flex-shrink-0 me-3">
                    <span class="avatar-initial rounded bg-label-primary">
                        <i class="bx bx-map-pin fs-4"></i>
                    </span>
                </div>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">{{ $walletHealth['jumlah_region_aktif'] }}</h6>
                    <small class="text-muted">Region dengan aktivitas keuangan</small>
                </div>
            </div>
        </div>
    </div>

    <div class="mb-3"></div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><i class="bx bx-bar-chart-alt-2 me-2 text-primary"></i>Ringkasan per Unit Layanan</h5>
                <small class="text-muted">Tahun {{ date('Y') }}</small>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Unit Layanan</th>
                        <th class="text-center">Total Pesanan</th>
                        <th class="text-center">Selesai</th>
                        <th class="text-center">Menunggu</th>
                        <th class="text-center">Gagal/Batal</th>
                        <th class="text-center">Tingkat Sukses</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach($stats as $label => $row)
                        @php
                            $successRate = $row['total'] > 0 ? round(($row['selesai'] / $row['total']) * 100, 1) : 0;
                            $badgeColor = $successRate >= 80 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger');
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $label }}</td>
                            <td class="text-center">{{ $row['total'] }}</td>
                            <td class="text-center">{{ $row['selesai'] }}</td>
                            <td class="text-center">{{ $row['menunggu'] }}</td>
                            <td class="text-center">{{ $row['gagal'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-label-{{ $badgeColor }} rounded-pill">{{ $successRate }}%</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
