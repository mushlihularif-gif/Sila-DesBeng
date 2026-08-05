@extends('admin.layouts.admin')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Unit Layanan / Pasar Daerah / <a href="{{ route('admin.unit.pasar_daerah.pesanan') }}">Pesanan</a> /</span> Detail #{{ $pesanan->order_number }}
        </h4>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <!-- Order Details -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Pesanan</h5>
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
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Nomor Pesanan</div>
                            <div class="col-sm-8">#{{ $pesanan->order_number }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Tanggal Pemesanan</div>
                            <div class="col-sm-8">{{ $pesanan->created_at->format('d M Y, H:i') }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Pelanggan</div>
                            <div class="col-sm-8">
                                {{ $pesanan->user->name ?? 'Anonim' }}<br>
                                <small class="text-muted"><i class="bx bx-phone"></i> {{ $pesanan->phone }}</small>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Alamat Pengiriman</div>
                            <div class="col-sm-8">
                                {{ $pesanan->address ?? '-' }}<br>
                                @if($pesanan->latitude && $pesanan->longitude)
                                    <small><a href="https://www.google.com/maps/search/?api=1&query={{ $pesanan->latitude }},{{ $pesanan->longitude }}" target="_blank" class="text-primary"><i class="bx bx-map"></i> Lihat di Peta</a></small>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4 fw-bold">Catatan</div>
                            <div class="col-sm-8">{{ $pesanan->notes ?: '-' }}</div>
                        </div>

                        <hr>
                        <h6 class="fw-bold mt-4 mb-3">Daftar Produk</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-center">Harga</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pesanan->items as $item)
                                    <tr>
                                        <td>{{ $item->produk->nama_produk ?? $item->product_name }}</td>
                                        <td class="text-center">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total Belanja</td>
                                        <td class="text-end fw-bold">Rp {{ number_format($pesanan->total_price, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Ongkos Kirim</td>
                                        <td class="text-end fw-bold">Rp {{ number_format($pesanan->shipping_cost, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold text-primary">Grand Total</td>
                                        <td class="text-end fw-bold text-primary">Rp {{ number_format($pesanan->grand_total, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action & Payment Details -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Perbarui Status</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.unit.pasar_daerah.pesanan.update', $pesanan->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="status" class="form-label">Status Pesanan</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="pending" {{ $pesanan->status == 'pending' ? 'selected' : '' }}>Pending (Menunggu Bayar)</option>
                                    <option value="paid" {{ $pesanan->status == 'paid' ? 'selected' : '' }}>Paid (Sudah Bayar)</option>
                                    <option value="confirmed" {{ $pesanan->status == 'confirmed' ? 'selected' : '' }}>Confirmed (Diproses)</option>
                                    <option value="in_delivery" {{ $pesanan->status == 'in_delivery' ? 'selected' : '' }}>In Delivery (Dikirim)</option>
                                    <option value="completed" {{ $pesanan->status == 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                                    <option value="cancelled" {{ $pesanan->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="rejected" {{ $pesanan->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="fw-bold d-block text-muted">Metode Pembayaran</label>
                            @if(strtolower($pesanan->payment_method) == 'tunai')
                                <span class="badge bg-label-secondary">COD / Tunai</span>
                            @else
                                <span class="badge bg-label-info">{{ str_replace('_', ' ', strtoupper($pesanan->payment_method)) }}</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold d-block text-muted">Status Pembayaran</label>
                            @if(in_array($pesanan->status, ['paid', 'confirmed', 'in_delivery', 'completed']))
                                <span class="text-success fw-bold"><i class="bx bx-check-circle"></i> Lunas</span>
                            @else
                                <span class="text-warning fw-bold"><i class="bx bx-time"></i> Belum Lunas</span>
                            @endif
                        </div>
                        @if($pesanan->payment_proof)
                        <div class="mb-3">
                            <label class="fw-bold d-block text-muted mb-2">Bukti Pembayaran</label>
                            <a href="{{ asset('storage/' . $pesanan->payment_proof) }}" target="_blank">
                                <img src="{{ asset('storage/' . $pesanan->payment_proof) }}" alt="Bukti Pembayaran" class="img-fluid rounded" style="max-height: 200px">
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
