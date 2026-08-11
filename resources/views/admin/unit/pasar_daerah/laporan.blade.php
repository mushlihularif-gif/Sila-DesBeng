@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Unit Layanan / Pasar Daerah /</span> Laporan Transaksi</h4>

        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.unit.pasar_daerah.index') }}"><i class="bx bx-box me-1"></i> Daftar Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.unit.pasar_daerah.pesanan') }}"><i class="bx bx-cart me-1"></i> Daftar Pesanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('admin.unit.pasar_daerah.laporan') }}"><i class="bx bx-line-chart me-1"></i> Laporan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.unit.pasar_daerah.index') }}"><i class="bx bx-cog me-1"></i> Pengaturan & SOP</a>
                </li>
            </ul>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="navs-top-laporan" role="tabpanel">
                    
                    <!-- Filter and Summary -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card bg-label-primary">
                                <div class="card-body d-flex align-items-center justify-content-between py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar avatar-md bg-primary rounded p-2 text-white">
                                            <i class="bx bx-wallet fs-3"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-primary">Total Pendapatan</h6>
                                            <small>Dari {{ count($laporans) }} transaksi yang selesai</small>
                                        </div>
                                    </div>
                                    <h4 class="mb-0 text-primary fw-bold">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body p-3">
                                    <form action="{{ route('admin.unit.pasar_daerah.laporan') }}" method="GET">
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                            <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}" placeholder="Mulai">
                                        </div>
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                            <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}" placeholder="Sampai">
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-sm btn-primary w-100">Filter</button>
                                            <a href="{{ route('admin.unit.pasar_daerah.laporan') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Laporan -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Daftar Transaksi Selesai</h5>
                            <button onclick="window.print()" class="btn btn-sm btn-secondary"><i class="bx bx-printer me-1"></i> Cetak</button>
                        </div>
                        <div class="table-responsive text-nowrap">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>No. Pesanan</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Pelanggan</th>
                                        <th>Metode Bayar</th>
                                        <th class="text-end">Total Belanja</th>
                                        <th class="text-end">Ongkos Kirim</th>
                                        <th class="text-end text-primary">Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($laporans as $laporan)
                                    <tr>
                                        <td><a href="{{ route('admin.unit.pasar_daerah.pesanan.show', $laporan->id) }}" class="fw-bold">#{{ $laporan->order_number }}</a></td>
                                        <td>{{ $laporan->updated_at->format('d M Y H:i') }}</td>
                                        <td>{{ $laporan->user->name ?? 'Anonim' }}</td>
                                        <td>
                                            @if(strtolower($laporan->payment_method) == 'tunai')
                                                COD
                                            @else
                                                {{ str_replace('_', ' ', strtoupper($laporan->payment_method)) }}
                                            @endif
                                        </td>
                                        <td class="text-end">Rp {{ number_format($laporan->total_price, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format($laporan->shipping_cost, 0, ',', '.') }}</td>
                                        <td class="text-end fw-bold text-primary">Rp {{ number_format($laporan->grand_total, 0, ',', '.') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Belum ada transaksi selesai pada rentang waktu ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    @media print {
        .navbar, .layout-menu, .footer, .btn, form, .nav-tabs {
            display: none !important;
        }
        .layout-page {
            padding: 0 !important;
            margin: 0 !important;
        }
        .container-xxl {
            max-width: 100% !important;
            padding: 0 !important;
        }
        .card {
            box-shadow: none !important;
            border: none !important;
        }
        body {
            background-color: white !important;
        }
    }
</style>
@endpush
