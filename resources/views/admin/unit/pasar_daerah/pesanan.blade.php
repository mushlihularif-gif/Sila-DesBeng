@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Unit Layanan /</span> Pasar Daerah</h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.unit.pasar_daerah.index') }}"><i class="bx bx-box me-1"></i> Daftar Produk</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('admin.unit.pasar_daerah.pesanan') }}"><i class="bx bx-cart me-1"></i> Daftar Pesanan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.unit.pasar_daerah.laporan') }}"><i class="bx bx-line-chart me-1"></i> Laporan</a>
                </li>
                <li class="nav-item">
                    <!-- Note: Pengaturan SOP link moved to index for simplicity or can be linked back to index#pengaturan -->
                    <a class="nav-link" href="{{ route('admin.unit.pasar_daerah.index') }}"><i class="bx bx-cog me-1"></i> Pengaturan & SOP</a>
                </li>
            </ul>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="navs-top-pesanan" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-0">Daftar Pesanan Pasar Daerah</h5>
                            <small class="text-muted">Kelola pesanan dari warga</small>
                        </div>
                        <div>
                            <form action="{{ route('admin.unit.pasar_daerah.pesanan') }}" method="GET" class="d-flex gap-2">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua Status</option>
                                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending (Menunggu Bayar)</option>
                                    <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid (Sudah Bayar)</option>
                                    <option value="confirmed" {{ $status == 'confirmed' ? 'selected' : '' }}>Confirmed (Diproses)</option>
                                    <option value="in_delivery" {{ $status == 'in_delivery' ? 'selected' : '' }}>In Delivery (Dikirim)</option>
                                    <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Waktu</th>
                                    <th>Pelanggan</th>
                                    <th>Total Tagihan</th>
                                    <th>Pembayaran</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="table-border-bottom-0">
                                @forelse($pesanans as $pesanan)
                                <tr>
                                    <td><span class="fw-bold text-primary">#{{ $pesanan->order_number }}</span></td>
                                    <td>{{ $pesanan->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold">{{ $pesanan->user->name ?? 'Anonim' }}</span>
                                            <small class="text-muted">{{ $pesanan->phone }}</small>
                                        </div>
                                    </td>
                                    <td>Rp {{ number_format($pesanan->grand_total, 0, ',', '.') }}</td>
                                    <td>
                                        @if(strtolower($pesanan->payment_method) == 'tunai')
                                            <span class="badge bg-label-secondary">COD / Tunai</span>
                                        @else
                                            <span class="badge bg-label-info">{{ str_replace('_', ' ', strtoupper($pesanan->payment_method)) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badges = [
                                                'pending' => 'bg-warning',
                                                'paid' => 'bg-info',
                                                'confirmed' => 'bg-primary',
                                                'in_delivery' => 'bg-primary',
                                                'completed' => 'bg-success',
                                                'cancelled' => 'bg-danger',
                                                'rejected' => 'bg-danger',
                                            ];
                                            $labels = [
                                                'pending' => 'Menunggu Pembayaran',
                                                'paid' => 'Sudah Dibayar',
                                                'confirmed' => 'Diproses',
                                                'in_delivery' => 'Dikirim',
                                                'completed' => 'Selesai',
                                                'cancelled' => 'Dibatalkan',
                                                'rejected' => 'Ditolak',
                                            ];
                                        @endphp
                                        <span class="badge {{ $badges[$pesanan->status] ?? 'bg-secondary' }}">
                                            {{ $labels[$pesanan->status] ?? ucfirst($pesanan->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.unit.pasar_daerah.pesanan.show', $pesanan->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-search-alt me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data pesanan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
