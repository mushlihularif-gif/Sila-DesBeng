@extends('admin.layouts.admin')

@section('title', 'Detail Pesanan Pasar Daerah')

@section('content')
<div class="container-fluid py-4">
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .5; }
        }
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }
    </style>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary fw-bold mb-1">Detail Pesanan Pasar Daerah</h2>
                    <p class="text-muted">Kelola status dan informasi pesanan</p>
                </div>
                <a href="{{ route('admin.unit.pasar_daerah.pesanan') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bx bx-arrow-back me-2"></i> Kembali
                </a>
            </div>

            <!-- ALERT CANCELLATION -->
            @if(isset($pesanan->cancellation_status) && $pesanan->cancellation_status === 'pending')
            <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center" role="alert">
                <i class="bx bx-error-circle fs-1 me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading fw-bold mb-1">Permintaan Pembatalan Diajukan</h5>
                    <p class="mb-0">User mengajukan pembatalan dengan alasan: <strong>"{{ $pesanan->cancellation_reason ?? $pesanan->cancellation_reason_user }}"</strong></p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-danger" onclick="handleCancellation({{ $pesanan->id }}, 'pasar_daerah', 'approve')">
                        Setujui Pembatalan
                    </button>
                    <button class="btn btn-secondary" onclick="showCancellationRejectModal({{ $pesanan->id }}, 'pasar_daerah')">
                        Tolak Pembatalan
                    </button>
                </div>
            </div>
            @endif

            <div class="row g-4">
                <!-- LEFT COLUMN -->
                <div class="col-lg-8">
                    <!-- MAIN CARD -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-bold text-dark">
                                    <i class="bx bx-package me-2 text-primary"></i>Informasi Pesanan
                                </h5>
                                <div>
                                    <span class="badge bg-light text-dark border rounded-pill px-3 me-2">
                                        {{ $pesanan->order_number }}
                                    </span>
                                    @if($pesanan->handled_by && $pesanan->handler)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3" data-bs-toggle="tooltip" title="Ditangani Oleh">
                                            <i class="bx bx-user-check me-1"></i> {{ $pesanan->handler->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <!-- User Details -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1 text-uppercase small ls-1">Pelanggan</p>
                                    <p class="fw-semibold mb-0">{{ $pesanan->user->name ?? 'Anonim' }}</p>
                                    <small class="text-muted"><i class="bx bx-phone me-1"></i>{{ $pesanan->phone }}</small>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1 text-uppercase small ls-1">Tanggal Pesanan</p>
                                    <p class="fw-semibold mb-0">{{ $pesanan->created_at->isoFormat('D MMMM Y, HH:mm') }} WIB</p>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-12">
                                    <p class="text-muted mb-1 text-uppercase small ls-1">Alamat Pengiriman</p>
                                    <p class="fw-semibold mb-1">{{ $pesanan->address ?? '-' }}</p>
                                    @if($pesanan->latitude && $pesanan->longitude)
                                        <small><a href="https://www.google.com/maps/search/?api=1&query={{ $pesanan->latitude }},{{ $pesanan->longitude }}" target="_blank" class="text-primary text-decoration-none"><i class="bx bx-map me-1"></i>Lihat di Peta</a></small>
                                    @endif
                                </div>
                            </div>

                            @if($pesanan->notes)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <p class="text-muted mb-1 text-uppercase small ls-1">Catatan</p>
                                    <div class="p-3 bg-light rounded-3 border">
                                        <p class="mb-0 text-dark">{{ $pesanan->notes }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <hr class="my-4 border-light">

                            <h6 class="fw-bold mb-3">Daftar Produk</h6>
                            
                            @foreach($pesanan->items as $item)
                            <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 mb-2 shadow-sm bg-white">
                                <div class="d-flex align-items-center gap-3">
                                    @php
                                        $imgSrc = null;
                                        if($item->produk && $item->produk->foto) {
                                            $imgSrc = asset('storage/' . $item->produk->foto);
                                        }
                                    @endphp
                                    @if($imgSrc)
                                        <img src="{{ $imgSrc }}" alt="Product" class="rounded-3 object-fit-cover shadow-sm" style="width: 60px; height: 60px;">
                                    @else
                                        <div class="rounded-3 shadow-sm bg-light border d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                            <i class="bx bx-image text-muted fs-4"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $item->produk->nama_produk ?? $item->product_name }}</h6>
                                        <small class="text-muted">Rp {{ number_format($item->price, 0, ',', '.') }} x {{ $item->quantity }}</small>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-primary mb-0">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                            @endforeach
                            
                            <!-- Totals Box (moved to sidebar) -->
                        </div>
                    </div>

                    <!-- DELIVERY STATUS WORKFLOW -->
                    @if(in_array($pesanan->status, ['confirmed', 'in_delivery', 'completed']))
                    <div class="card shadow-sm border-0 rounded-4 mb-4 animate-fade-in-up">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold text-dark"><i class="bx bx-map-alt me-2 text-primary"></i>Status Pengiriman</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex flex-column gap-0">
                                <!-- Step 1: Confirmed -->
                                <div class="d-flex gap-3 position-relative pb-4">
                                    <div class="d-flex flex-column align-items-center" style="width: 40px; min-width: 40px;">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; z-index: 2;">
                                            <i class="bx bx-check fs-5"></i>
                                        </div>
                                        <div class="h-100 border-start border-2 border-primary-subtle position-absolute" style="left: 19px; top: 32px; bottom: 0;"></div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="card border-0 bg-white shadow-sm rounded-3 hover-shadow transition-all">
                                            <div class="card-body p-3">
                                                <h6 class="fw-bold text-dark mb-1">Pesanan Diproses</h6>
                                                <small class="text-muted d-block">
                                                    <i class="bx bx-time-five me-1"></i>Pesanan sudah dikonfirmasi dan sedang disiapkan.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: In Delivery -->
                                <div class="d-flex gap-3 position-relative pb-4">
                                    <div class="d-flex flex-column align-items-center" style="width: 40px; min-width: 40px;">
                                        <div class="rounded-circle {{ in_array($pesanan->status, ['in_delivery', 'completed']) ? 'bg-success text-white' : ($pesanan->status == 'confirmed' ? 'bg-primary text-white animate-pulse' : 'bg-white border text-secondary') }} d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; z-index: 2;">
                                            <i class="bx bx-car fs-4"></i>
                                        </div>
                                        <div class="h-100 border-start border-2 border-primary-subtle position-absolute" style="left: 19px; top: 32px; bottom: 0;"></div>
                                    </div>
                                    <div class="flex-grow-1 pb-4">
                                        <div class="card border-0 {{ in_array($pesanan->status, ['in_delivery', 'completed']) ? 'bg-success-subtle bg-opacity-10' : ($pesanan->status == 'confirmed' ? 'bg-white border border-primary border-2 shadow-sm' : 'bg-light') }} rounded-3">
                                            <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                                <div>
                                                    <h6 class="fw-bold {{ in_array($pesanan->status, ['in_delivery', 'completed']) ? 'text-success' : 'text-dark' }} mb-1">Dalam Pengiriman</h6>
                                                </div>
                                                @if($pesanan->status == 'confirmed')
                                                    <form action="{{ route('admin.unit.pasar_daerah.pesanan.update', $pesanan->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="in_delivery">
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                                                            <i class="bx bx-navigation me-2"></i>Mulai Kirim
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 3: Completed -->
                                <div class="d-flex gap-3 position-relative">
                                    <div class="d-flex flex-column align-items-center" style="width: 40px; min-width: 40px;">
                                        <div class="rounded-circle {{ $pesanan->status == 'completed' ? 'bg-success text-white' : ($pesanan->status == 'in_delivery' ? 'bg-primary text-white animate-pulse' : 'bg-white border text-secondary') }} d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; z-index: 2;">
                                            <i class="bx bx-check-double fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="card border-0 {{ $pesanan->status == 'completed' ? 'bg-success-subtle bg-opacity-10' : ($pesanan->status == 'in_delivery' ? 'bg-white border border-primary border-2 shadow-sm' : 'bg-light') }} rounded-3">
                                            <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                                <div>
                                                    <h6 class="fw-bold {{ $pesanan->status == 'completed' ? 'text-success' : 'text-dark' }} mb-1">Pesanan Selesai</h6>
                                                </div>
                                                @if($pesanan->status == 'in_delivery')
                                                    <form action="{{ route('admin.unit.pasar_daerah.pesanan.update', $pesanan->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="completed">
                                                        <button type="submit" class="btn btn-success rounded-pill px-4">
                                                            <i class="bx bx-check-double me-2"></i>Selesai
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- RIGHT COLUMN -->
                <div class="col-lg-4">
                    <!-- STATUS UPDATE WIDGET (For non-workflow states) -->
                    @if(!in_array($pesanan->status, ['confirmed', 'in_delivery', 'completed']))
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold">Update Status Pesanan</h6>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.unit.pasar_daerah.pesanan.update', $pesanan->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                @if($pesanan->status === 'pending')
                                    <div class="alert alert-warning mb-3 py-2 border-0 bg-warning-subtle text-warning-emphasis">
                                        <small><i class="bx bx-time-five me-1"></i>Menunggu Pembayaran</small>
                                    </div>
                                    <input type="hidden" name="status" value="paid">
                                    <button type="submit" class="btn btn-info w-100 rounded-pill mb-2">Konfirmasi Pembayaran (Tandai Paid)</button>
                                    
                                @elseif($pesanan->status === 'paid')
                                    <div class="alert alert-info mb-3 py-2 border-0 bg-info-subtle text-info-emphasis">
                                        <small><i class="bx bx-check-circle me-1"></i>Sudah Dibayar</small>
                                    </div>
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-primary w-100 rounded-pill mb-2">Konfirmasi & Mulai Proses</button>
                                    
                                @elseif($pesanan->status === 'cancelled')
                                    <div class="alert alert-danger mb-0 py-2 border-0 bg-danger-subtle text-danger-emphasis">
                                        <small><i class="bx bx-x-circle me-1"></i>Pesanan Dibatalkan</small>
                                    </div>
                                    
                                @elseif($pesanan->status === 'rejected')
                                    <div class="alert alert-danger mb-0 py-2 border-0 bg-danger-subtle text-danger-emphasis">
                                        <small><i class="bx bx-x-circle me-1"></i>Pesanan Ditolak</small>
                                    </div>
                                @endif
                                
                                @if(in_array($pesanan->status, ['pending', 'paid']))
                                    <button type="button" class="btn btn-outline-danger w-100 rounded-pill mt-2" onclick="document.getElementById('reject-form').submit()">Tolak / Batalkan Pesanan</button>
                                @endif
                            </form>
                            @if(in_array($pesanan->status, ['pending', 'paid']))
                            <form id="reject-form" action="{{ route('admin.unit.pasar_daerah.pesanan.update', $pesanan->id) }}" method="POST" class="d-none">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="rejected">
                            </form>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- PAYMENT INFO CARD -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold">Ringkasan Tagihan</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Belanja</span>
                                <span class="fw-semibold">Rp {{ number_format($pesanan->total_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Ongkos Kirim</span>
                                <span class="fw-semibold">Rp {{ number_format($pesanan->shipping_cost, 0, ',', '.') }}</span>
                            </div>
                            <hr class="border-light mb-3">
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold">Grand Total</span>
                                <span class="fw-bold text-primary fs-5">Rp {{ number_format($pesanan->grand_total, 0, ',', '.') }}</span>
                            </div>

                            <div class="mb-3">
                                <p class="text-muted mb-1 text-uppercase small ls-1">Metode Pembayaran</p>
                                @if(strtolower($pesanan->payment_method) == 'tunai')
                                    <span class="badge bg-secondary rounded-pill px-3 py-2"><i class="bx bx-money me-1"></i> COD / Tunai</span>
                                @else
                                    <span class="badge bg-info rounded-pill px-3 py-2"><i class="bx bx-credit-card me-1"></i> {{ str_replace('_', ' ', strtoupper($pesanan->payment_method)) }}</span>
                                @endif
                            </div>

                            <div>
                                <p class="text-muted mb-1 text-uppercase small ls-1">Status Pembayaran</p>
                                @if(in_array($pesanan->status, ['paid', 'confirmed', 'in_delivery', 'completed']))
                                    <span class="text-success fw-bold d-flex align-items-center"><i class="bx bx-check-circle fs-5 me-1"></i> Lunas</span>
                                @elseif(in_array($pesanan->status, ['cancelled', 'rejected']))
                                    <span class="text-danger fw-bold d-flex align-items-center"><i class="bx bx-x-circle fs-5 me-1"></i> Dibatalkan</span>
                                @else
                                    <span class="text-warning fw-bold d-flex align-items-center"><i class="bx bx-time fs-5 me-1"></i> Belum Lunas</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    @if($pesanan->payment_proof)
                    <!-- PAYMENT PROOF CARD -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold">Bukti Pembayaran</h6>
                        </div>
                        <div class="card-body p-4 text-center">
                            <a href="{{ asset('storage/' . $pesanan->payment_proof) }}" target="_blank" class="d-block border rounded-3 overflow-hidden shadow-sm hover-shadow transition-all mb-3">
                                <img src="{{ asset('storage/' . $pesanan->payment_proof) }}" alt="Bukti Pembayaran" class="img-fluid w-100" style="object-fit: cover; max-height: 250px;">
                            </a>
                            <a href="{{ asset('storage/' . $pesanan->payment_proof) }}" download class="btn btn-outline-primary btn-sm rounded-pill px-4">
                                <i class="bx bx-download me-1"></i> Unduh Bukti
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- RECEIPT CARD -->
                    @if(in_array($pesanan->status, ['paid', 'confirmed', 'in_delivery', 'completed']))
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="mb-0 fw-bold">Bukti Transaksi (Nota)</h6>
                        </div>
                        <div class="card-body p-4 text-center">
                            <i class="bx bx-receipt text-primary opacity-50 mb-3" style="font-size: 3rem;"></i>
                            <p class="text-muted small mb-3">Bukti transaksi berisi rincian pesanan dan status pembayaran (Lunas) beserta QR Code validasi.</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('receipt.pasar.view', $pesanan->id) }}" target="_blank" class="btn btn-primary rounded-pill">
                                    <i class="bx bx-show me-1"></i> Lihat Nota
                                </a>
                                <a href="{{ route('receipt.pasar.download', $pesanan->id) }}" class="btn btn-outline-primary rounded-pill">
                                    <i class="bx bx-download me-1"></i> Unduh Nota
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showCancellationRejectModal(id, type) {
    showSiladesBengToast('warning', 'Perhatian', "Fitur penolakan pembatalan belum diimplementasikan di view ini.");
}
function handleCancellation(id, type, action) {
    konfirmasi({
        judul: 'Setujui Pembatalan',
        pesan: 'Apakah Anda yakin ingin menyetujui pembatalan pesanan ini?',
        jenis: 'peringatan',
        tombolYa: 'Ya, Setujui'
    }).then(function (setuju) {
        if (! setuju) return;
        // Fungsi ini masih rintisan: belum ada pemanggilan ke server.
        showSiladesBengToast('warning', 'Belum Tersedia',
            'Persetujuan pembatalan belum terhubung ke server.');
    });
}
</script>
@endsection
