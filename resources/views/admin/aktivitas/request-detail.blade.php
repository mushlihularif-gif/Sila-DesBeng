@extends('admin.layouts.admin')

@section('title', 'Detail Pengajuan')

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
                    <h2 class="text-primary fw-bold mb-1">Detail Pengajuan</h2>
                    <p class="text-muted">Kelola status dan informasi pesanan</p>
                </div>
                <a href="{{ route('admin.aktivitas.permintaan-pengajuan.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bx bx-arrow-back me-2"></i> Kembali
                </a>
            </div>

            <!-- ALERT CANCELLATION -->
            @if($request->cancellation_status === 'pending')
            <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center" role="alert">
                <i class="bx bx-error-circle fs-1 me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading fw-bold mb-1">Permintaan Pembatalan Diajukan</h5>
                    <p class="mb-0">User mengajukan pembatalan dengan alasan: <strong>"{{ $request->cancellation_reason ?? $request->cancellation_reason_user }}"</strong></p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-danger" onclick="handleCancellation({{ $request->id }}, '{{ $type }}', 'approve')">
                        Setujui Pembatalan
                    </button>
                    <button class="btn btn-secondary" onclick="showCancellationRejectModal({{ $request->id }}, '{{ $type }}')">
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
                                <span class="badge bg-light text-dark border rounded-pill px-3">
                                    {{ $request->order_number }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-4">
    <div class="d-flex align-items-start gap-4 mb-4">
                                @php
                                    $imgSrc = null;
                                    if($type === 'rental' && $request->barang && $request->barang->foto) {
                                        $imgSrc = asset('storage/' . $request->barang->foto);
                                    } elseif($type === 'gas' && $request->gas && $request->gas->foto) {
                                        $imgSrc = asset('storage/' . $request->gas->foto);
                                    }
                                @endphp
                                
                                @if($imgSrc)
                                    <img src="{{ $imgSrc }}" alt="Product" class="rounded-3 object-fit-cover shadow-sm" style="width: 100px; height: 100px;">
                                @else
                                    <div class="rounded-3 shadow-sm bg-light border d-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                        <i class="bx bx-image text-muted fs-1"></i>
                                    </div>
                                @endif
                                <div>
                                    <h4 class="fw-bold mb-1">{{ $type === 'rental' ? ($request->barang->nama_barang ?? 'Alat') : ($request->item_name ?? 'Gas') }}</h4>
                                    <p class="text-muted mb-2">{{ $type === 'rental' ? 'Penyewaan Alat' : 'Pembelian Gas' }}</p>
                                    <h5 class="text-primary fw-bold">Rp {{ number_format($request->total_amount ?? $request->price, 0, ',', '.') }}</h5>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1 text-uppercase small ls-1">Tanggal Pemesanan</p>
                                    <p class="fw-semibold">{{ $request->created_at->isoFormat('D MMMM Y, HH:mm') }} WIB</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1 text-uppercase small ls-1">Jumlah</p>
                                    <p class="fw-semibold">{{ $request->quantity }} Unit</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1 text-uppercase small ls-1">Metode Pembayaran</p>
                                    <p class="fw-semibold">
                                        <span class="badge bg-success-subtle text-success">Tunai</span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1 text-uppercase small ls-1">Metode Pengiriman</p>
                                    <p class="fw-semibold">
                                        @if($request->delivery_method == 'antar')
                                            <span class="badge bg-primary-subtle text-primary">Diantar</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">Jemput Sendiri</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <hr class="my-4 border-light">

                            <h6 class="fw-bold mb-3">Jadwal Sewa</h6>
                            <div class="row g-3">
                                <div class="col-md-{{ $request->return_time ? '4' : '6' }}">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block mb-1">Mulai</small>
                                        <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($request->start_date)->isoFormat('D MMMM Y') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-{{ $request->return_time ? '4' : '6' }}">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block mb-1">Selesai</small>
                                        <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($request->end_date)->isoFormat('D MMMM Y') }}</span>
                                    </div>
                                </div>
                                @if($request->return_time)
                                <div class="col-md-4">
                                    <div class="p-3 bg-success-subtle rounded-3 border border-success-subtle">
                                        <small class="text-success-emphasis d-block mb-1">Dikembalikan</small>
                                        <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($request->return_time)->isoFormat('D MMMM Y HH:mm') }}</span>
                                    </div>
                                </div>
                                @endif
                            </div>

                        </div>
                    </div>

                    <!-- DELIVERY STATUS WORKFLOW (RENTAL ONLY) - MOVED HERE -->
                    @if($type === 'rental' && in_array($request->status, ['confirmed', 'being_prepared', 'in_delivery', 'arrived', 'completed']))
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
                                                <h6 class="fw-bold text-dark mb-1">Pesanan Dikonfirmasi</h6>
                                                <small class="text-muted d-block">
                                                    <i class="bx bx-time-five me-1"></i>{{ $request->confirmed_at ? \Carbon\Carbon::parse($request->confirmed_at)->format('d M Y, H:i') : '-' }} WIB
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($request->delivery_method == 'antar')
                                    <!-- Step 2: Being Prepared (ANTAR ONLY) -->
                                    <div class="d-flex gap-3 position-relative pb-4">
                                        <div class="d-flex flex-column align-items-center" style="width: 40px; min-width: 40px;">
                                            <div class="rounded-circle {{ in_array($request->status, ['being_prepared', 'in_delivery', 'arrived', 'completed']) ? 'bg-success text-white' : ($request->status == 'confirmed' ? 'bg-primary text-white animate-pulse' : 'bg-light text-secondary border') }} d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; z-index: 2;">
                                                <i class="bx bx-package fs-5"></i>
                                            </div>
                                            <div class="h-100 border-start border-2 border-primary-subtle position-absolute" style="left: 19px; top: 32px; bottom: 0;"></div>
                                        </div>
                                        <div class="flex-grow-1 pb-4">
                                            <div class="card border-0 {{ in_array($request->status, ['being_prepared', 'in_delivery', 'arrived', 'completed']) ? 'bg-success-subtle bg-opacity-10' : ($request->status == 'confirmed' ? 'bg-white border border-primary border-2 shadow-sm' : 'bg-light') }} rounded-3">
                                                <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                                    <div>
                                                        <h6 class="fw-bold {{ in_array($request->status, ['being_prepared', 'in_delivery', 'arrived', 'completed']) ? 'text-success' : 'text-dark' }} mb-1">Sedang Dipersiapkan</h6>
                                                        <small class="text-muted">Tim sedang menyiapkan barang pesanan</small>
                                                    </div>
                                                    @if($request->status == 'confirmed')
                                                        <button class="btn btn-primary rounded-pill px-4" onclick="updateStatus('being_prepared')">
                                                            <i class="bx bx-check me-2"></i>Update Status
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Step 3: In Delivery (ANTAR ONLY) -->
                                    <div class="d-flex gap-3 position-relative pb-4">
                                        <div class="d-flex flex-column align-items-center" style="width: 40px; min-width: 40px;">
                                            <div class="rounded-circle {{ in_array($request->status, ['in_delivery', 'arrived', 'completed']) ? 'bg-success text-white' : ($request->status == 'being_prepared' ? 'bg-primary text-white animate-pulse' : 'bg-white border text-secondary') }} d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; z-index: 2;">
                                                <i class="bx bx-car fs-4"></i>
                                            </div>
                                            <div class="h-100 border-start border-2 border-primary-subtle position-absolute" style="left: 19px; top: 32px; bottom: 0;"></div>
                                        </div>
                                        <div class="flex-grow-1 pb-4">
                                            <div class="card border-0 {{ in_array($request->status, ['in_delivery', 'arrived', 'completed']) ? 'bg-success-subtle bg-opacity-10' : ($request->status == 'being_prepared' ? 'bg-white border border-primary border-2 shadow-sm' : 'bg-light') }} rounded-3">
                                                <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                                    <div>
                                                        <h6 class="fw-bold {{ in_array($request->status, ['in_delivery', 'arrived', 'completed']) ? 'text-success' : 'text-dark' }} mb-1">Dalam Pengiriman</h6>
                                                        @if($request->delivery_time)
                                                            <small class="text-muted"><i class="bx bx-time me-1"></i>{{ \Carbon\Carbon::parse($request->delivery_time)->format('d M Y H:i') }}</small>
                                                        @endif
                                                    </div>
                                                    @if($request->status == 'being_prepared')
                                                        <button class="btn btn-primary rounded-pill px-4" onclick="updateStatus('in_delivery')">
                                                            <i class="bx bx-navigation me-2"></i>Mulai Pengiriman
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Step 4/2: Arrived/Picked Up -->
                                <div class="d-flex gap-3 position-relative pb-4">
                                    <div class="d-flex flex-column align-items-center" style="width: 40px; min-width: 40px;">
                                        <div class="rounded-circle {{ in_array($request->status, ['arrived', 'completed']) ? 'bg-success text-white' : ($request->status == 'in_delivery' || ($request->delivery_method != 'antar' && $request->status == 'confirmed') ? 'bg-primary text-white animate-pulse' : 'bg-white border text-secondary') }} d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; z-index: 2;">
                                            <i class="bx {{ $request->delivery_method == 'antar' ? 'bx-map-pin' : 'bx-package' }} fs-4"></i>
                                        </div>
                                        <div class="h-100 border-start border-2 border-primary-subtle position-absolute" style="left: 19px; top: 32px; bottom: 0;"></div>
                                    </div>
                                    <div class="flex-grow-1 pb-4">
                                        <div class="card border-0 {{ in_array($request->status, ['arrived', 'completed']) ? 'bg-success-subtle bg-opacity-10' : ($request->status == 'in_delivery' || ($request->delivery_method != 'antar' && $request->status == 'confirmed') ? 'bg-white border border-primary border-2 shadow-sm' : 'bg-light') }} rounded-3">
                                            <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                                <div>
                                                    <h6 class="fw-bold {{ in_array($request->status, ['arrived', 'completed']) ? 'text-success' : 'text-dark' }} mb-1">
                                                        {{ $request->delivery_method == 'antar' ? 'Tiba di Lokasi' : 'Pesanan sudah di ambil oleh penyewa' }}
                                                    </h6>
                                                    @if($request->arrival_time)
                                                        <small class="text-muted"><i class="bx bx-time me-1"></i>{{ \Carbon\Carbon::parse($request->arrival_time)->format('d M Y H:i') }}</small>
                                                    @endif
                                                    @if($request->delivery_proof_image)
                                                        <div class="mt-2">
                                                            <a href="{{ asset('storage/' . $request->delivery_proof_image) }}" target="_blank" class="badge bg-primary-subtle text-primary border border-primary-subtle p-2 text-decoration-none">
                                                                <i class="bx bx-image me-1"></i>{{ $request->delivery_method == 'antar' ? 'Lihat Bukti Pengiriman' : 'Lihat Bukti Penjemputan' }}
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                                @if($request->status == 'in_delivery' || ($request->delivery_method != 'antar' && $request->status == 'confirmed'))
                                                    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#uploadProofModal">
                                                        <i class="bx bx-camera me-2"></i>{{ $request->delivery_method == 'antar' ? 'Pesanan Tiba' : 'Konfirmasi Pengambilan' }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 5/3: Completed -->
                                <div class="d-flex gap-3 position-relative">
                                    <div class="d-flex flex-column align-items-center" style="width: 40px; min-width: 40px;">
                                        <div class="rounded-circle {{ $request->status == 'completed' ? 'bg-success text-white' : ($request->status == 'arrived' ? 'bg-primary text-white animate-pulse' : 'bg-white border text-secondary') }} d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; z-index: 2;">
                                            <i class="bx bx-check-double fs-4"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="card border-0 {{ $request->status == 'completed' ? 'bg-success-subtle bg-opacity-10' : ($request->status == 'arrived' ? 'bg-white border border-primary border-2 shadow-sm' : 'bg-light') }} rounded-3">
                                            <div class="card-body p-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                                <div>
                                                    <h6 class="fw-bold {{ $request->status == 'completed' ? 'text-success' : 'text-dark' }} mb-1">Pesanan Selesai</h6>
                                                    @if($request->completion_time)
                                                        <small class="text-muted"><i class="bx bx-time me-1"></i>{{ \Carbon\Carbon::parse($request->completion_time)->format('d M Y H:i') }}</small>
                                                    @endif
                                                </div>
                                                @if($request->status == 'arrived')
                                                    @if($type === 'rental')
                                                        <button class="btn btn-warning rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#returnModal">
                                                            <i class="bx bx-time-five me-2"></i>Input Pengembalian
                                                        </button>
                                                    @else
                                                        <button class="btn btn-success rounded-pill px-4" onclick="updateStatus('completed')">
                                                            <i class="bx bx-trophy me-2"></i>Selesaikan
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- PROOF OF PAYMENT -->
                    @if($request->proof_of_payment || $request->payment_proof)
                    <div class="card shadow-sm border-0 rounded-4">

                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="bx bx-receipt me-2 text-primary"></i>Bukti Pembayaran
                            </h5>
                        </div>
                        <div class="card-body p-4 text-center">
                            @php
                                $proof = $request->proof_of_payment ?? $request->payment_proof;
                            @endphp
                            <div class="position-relative">
                                <img src="{{ asset('storage/' . $proof) }}" 
                                     alt="Bukti Pembayaran" 
                                     class="img-fluid rounded shadow-sm mb-3" 
                                     style="max-height: 500px; width: 100%; object-fit: contain;">
                                <div class="mt-2">
                                     <a href="{{ asset('storage/' . $proof) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4">
                                        <i class="bx bx-fullscreen me-2"></i>Lihat Ukuran Penuh
                                    </a>
                                    <a href="{{ asset('storage/' . $proof) }}" download class="btn btn-primary rounded-pill px-4 ms-2">
                                        <i class="bx bx-download me-2"></i>Unduh
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                            @endif
                    </div>

                <!-- RIGHT COLUMN -->
                <div class="col-lg-4">
                    <!-- CUSTOMER INFO CARD -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold text-dark">Data Pemesan</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-primary-subtle text-primary rounded-circle p-3 me-3">
                                    <i class="bx bx-user fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0">{{ $request->full_name ?? $request->recipient_name ?? $request->user->name }}</h6>
                                    <small class="text-muted">Pelanggan</small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block uppercase ls-1 mb-1">Email</small>
                                <span class="fw-medium text-dark">{{ $request->email ?? $request->user->email }}</span>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted d-block uppercase ls-1 mb-1">Alamat</small>
                                <p class="fw-medium text-dark mb-0">{{ $request->address ?? $request->delivery_address }}</p>
                            </div>

                            @if($request->notes)
                            <div class="mt-4 p-3 bg-light rounded-3 border border-warning-subtle">
                                <small class="text-warning-emphasis fw-bold d-block mb-1">
                                    <i class="bx bx-note me-1"></i>Catatan
                                </small>
                                <p class="mb-0 text-dark small">{{ $request->notes }}</p>
                            </div>
                            @endif

                            @if($type === 'rental' && $request->rental_purpose)
                            <div class="mt-4 p-3 bg-light rounded-3 border border-primary-subtle">
                                <small class="text-primary-emphasis fw-bold d-block mb-1">
                                    <i class="bx bx-info-circle me-1"></i>Tujuan Penyewaan
                                </small>
                                <p class="mb-0 text-dark small">{{ $request->rental_purpose }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- STATUS CARD -->
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold text-dark">Status Pesanan</h5>
                        </div>
                        <div class="card-body p-4">
                            @php
                                $statusLabels = [
                                    'pending' => ['Menunggu', 'bg-warning'],
                                    'approved' => ['Disetujui', 'bg-success'],
                                    'rejected' => ['Ditolak', 'bg-danger'],
                                    'cancelled' => ['Dibatalkan', 'bg-dark'],
                                    'confirmed' => ['Dikonfirmasi', 'bg-info'],
                                    'being_prepared' => ['Sedang Dipersiapkan', 'bg-info'],
                                    'in_delivery' => ['Dalam Pengiriman', 'bg-primary'],
                                    'arrived' => ['Tiba di Lokasi', 'bg-primary'],
                                    'completed' => ['Selesai', 'bg-success'],
                                ];
                                $currentStatus = $statusLabels[$request->status] ?? [$request->status, 'bg-secondary'];
                            @endphp
                            <div class="text-center mb-4">
                                <span class="badge {{ $currentStatus[1] }} fs-6 px-4 py-2 rounded-pill">
                                    {{ $currentStatus[0] }}
                                </span>
                            </div>

                            @if($request->cancellation_status === 'pending')
                                <div class="d-grid gap-2">
                                    <button class="btn btn-danger rounded-pill py-2" onclick="handleCancellation({{ $request->id }}, '{{ $type }}', 'approve')">
                                        <i class="bx bx-check-double me-2"></i>Setujui Pembatalan
                                    </button>
                                    <button class="btn btn-secondary rounded-pill py-2" onclick="showCancellationRejectModal({{ $request->id }}, '{{ $type }}')">
                                        <i class="bx bx-x-circle me-2"></i>Tolak Pembatalan
                                    </button>
                                </div>
                            @elseif($request->status === 'pending')
                                <div class="d-grid gap-2">
                                    <button class="btn btn-success rounded-pill py-2" onclick="confirmApprove({{ $request->id }}, '{{ $type }}')">
                                        <i class="bx bx-check me-2"></i>Setujui Pengajuan
                                    </button>
                                    <button class="btn btn-outline-danger rounded-pill py-2" onclick="showRejectModal({{ $request->id }}, '{{ $type }}')">
                                        <i class="bx bx-x me-2"></i>Tolak Pengajuan
                                    </button>
                                </div>
                            @elseif($type === 'gas' && in_array($request->status, ['confirmed', 'approved', 'processed']))
                                <div class="d-grid gap-2">
                                    <button class="btn btn-success rounded-pill py-2" onclick="updateStatus('completed')">
                                        <i class="bx bx-check-double me-2"></i>Selesaikan Pesanan
                                    </button>
                                </div>
                            @elseif($request->status === 'rejected')
                                <div class="alert alert-danger mb-0">
                                    <small class="fw-bold d-block mb-1">Alasan Penolakan:</small>
                                    {{ $request->rejection_reason }}
                                </div>
                            @elseif($request->status === 'cancelled')
                                <div class="alert alert-secondary mb-0">
                                    <small class="fw-bold d-block mb-1">Dibatalkan karena:</small>
                                    {{ $request->cancellation_reason ?? $request->cancellation_reason_user }}
                                </div>
                            @endif
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="rejectModalLabel">Tolak Permintaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="id" id="rejectId">
                    <input type="hidden" name="type" id="rejectType">
                    <div class="mb-3">
                        <label for="reason" class="form-label text-muted">Berikan alasan penolakan</label>
                        <textarea name="reason" id="reason" class="form-control bg-light border-0 py-3" rows="4" placeholder="Contoh: Stok barang habis atau pembayaran tidak valid..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4">Tolak Permintaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancellation Reject Modal -->
<div class="modal fade" id="cancellationRejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Tolak Pembatalan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cancelRejectId">
                <input type="hidden" id="cancelRejectType">
                <div class="mb-3">
                    <label class="form-label text-muted">Mengapa Anda menolak pembatalan ini?</label>
                    <textarea id="cancelRejectReason" class="form-control bg-light border-0 py-3" rows="4" placeholder="Berikan alasan kepada user..." required></textarea>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" onclick="submitCancellationReject()">Kirim Penolakan</button>
            </div>
        </div>
    </div>
</div>

<!-- Upload Proof Modal -->
<div class="modal fade" id="uploadProofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">{{ $request->delivery_method == 'jemput' ? 'Upload Bukti Penjemputan' : 'Upload Bukti Pengiriman' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadProofForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">{{ $request->delivery_method == 'jemput' ? 'Foto Bukti Pengambilan Barang' : 'Foto Bukti Penerimaan Barang' }}</label>
                        <input type="file" name="delivery_proof" class="form-control" accept="image/*" required>
                        <div class="form-text">Upload foto saat barang {{ $request->delivery_method == 'jemput' ? 'diambil' : 'diterima' }} oleh pelanggan</div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Upload & Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Pengembalian Alat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="returnForm">
                <div class="modal-body">
                    <div class="alert alert-info border-0 d-flex align-items-center mb-3">
                        <i class="bx bx-info-circle fs-4 me-2"></i>
                        <div>
                            Stok akan otomatis <strong>bertambah</strong> setelah dikonfirmasi.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Waktu Pengembalian</label>
                        <input type="datetime-local" name="return_time" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-link text-secondary text-decoration-none" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4">Konfirmasi Pengembalian</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmApprove(id, type) {
    Swal.fire({
        title: 'Setujui Pesanan?',
        text: "Pesanan akan diproses ke tahap selanjutnya",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show Loading
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                     Swal.showLoading();
                }
            });

            // Note: We use the blade variables directly for the URL as this is a detail page
            const url = `{{ route('admin.aktivitas.permintaan-pengajuan.approve', ['id' => $request->id, 'type' => $type]) }}`;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            })
            .then(async response => {
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await response.json() : null;

                if (!response.ok) {
                    const errorMessage = (data && data.message) || response.statusText || 'Terjadi kesalahan sistem';
                    throw new Error(errorMessage);
                }
                
                return data;
            })
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: data.message || 'Permintaan berhasil disetujui',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal menyetujui permintaan', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Gagal', error.message, 'error');
            });
        }
    });
}

function showRejectModal(id, type) {
    document.getElementById('rejectId').value = id;
    document.getElementById('rejectType').value = type;
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const url = `{{ route('admin.aktivitas.permintaan-pengajuan.reject', ['id' => ':id', 'type' => ':type']) }}`
        .replace(':id', formData.get('id'))
        .replace(':type', formData.get('type'));

    // Show Loading
    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        didOpen: () => {
             Swal.showLoading();
        }
    });

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            reason: formData.get('reason')
        })
    })
    .then(async response => {
        const isJson = response.headers.get('content-type')?.includes('application/json');
        const data = isJson ? await response.json() : null;

        if (!response.ok) {
            const errorMessage = (data && data.message) || response.statusText || 'Terjadi kesalahan sistem';
            throw new Error(errorMessage);
        }
        
        return data;
    })
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Berhasil',
                text: data.message || 'Permintaan berhasil ditolak',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Gagal', data.message || 'Gagal menolak permintaan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Gagal', error.message, 'error');
    });
});

// Cancellation Logic
function handleCancellation(id, type, action) {
    const title = action === 'approve' ? 'Setujui Pembatalan?' : 'Tolak Pembatalan?';
    const text = action === 'approve' ? 'Pesanan akan dibatalkan permanen.' : 'Pesanan akan tetap dilanjutkan.';
    
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: action === 'approve' ? '#dc3545' : '#6c757d',
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            submitCancellation(id, type, action);
        }
    });
}

function showCancellationRejectModal(id, type) {
    document.getElementById('cancelRejectId').value = id;
    document.getElementById('cancelRejectType').value = type;
    new bootstrap.Modal(document.getElementById('cancellationRejectModal')).show();
}

function submitCancellationReject() {
    const id = document.getElementById('cancelRejectId').value;
    const type = document.getElementById('cancelRejectType').value;
    const reason = document.getElementById('cancelRejectReason').value;

    if (!reason) {
        Swal.fire('Error', 'Mohon isi alasan penolakan', 'error');
        return;
    }

    submitCancellation(id, type, 'reject', reason);
}

function submitCancellation(id, type, action, reason = null) {
    const url = `{{ url('admin/aktivitas/permintaan-pengajuan') }}/${type}/${id}/cancellation/${action}`;

    // Show Loading
    Swal.fire({
        title: 'Memproses...',
        allowOutsideClick: false,
        didOpen: () => {
             Swal.showLoading();
        }
    });

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ admin_response: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Berhasil',
                text: data.message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    })
    .catch(err => Swal.fire('Error', 'Terjadi kesalahan sistem', 'error'));
}

function updateStatus(newStatus) {
    const url = `{{ route('admin.aktivitas.update-status', ['type' => $type, 'id' => $request->id]) }}`;
    
    Swal.fire({
        title: 'Update Status?',
        text: 'Anda yakin ingin mengubah status pesanan?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Update'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show Loading
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => {
                     Swal.showLoading();
                }
            });

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        title: 'Berhasil',
                        text: 'Status berhasil diperbarui',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    })
                    .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Gagal update status', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            });
        }
    });
}

document.getElementById('uploadProofForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const url = `{{ route('admin.aktivitas.delivery-proof', ['type' => $type, 'id' => $request->id]) }}`;
    
    // Show loading
    Swal.fire({
        title: 'Mengupload...',
        didOpen: () => {
             Swal.showLoading();
        }
    });

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire('Berhasil', data.message, 'success')
            .then(() => location.reload());
        } else {
            Swal.fire('Error', data.message || 'Gagal upload bukti', 'error');
        }
    })
    .catch(err => {
        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
    });
});
const returnForm = document.getElementById('returnForm');
if (returnForm) {
    returnForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const url = `{{ route('admin.aktivitas.permintaan-pengajuan.return', ['id' => $request->id]) }}`;
        
        // Show confirmation
        Swal.fire({
            title: 'Konfirmasi Pengembalian?',
            text: "Pastikan alat sudah diterima dengan baik. Stok akan ditambahkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Konfirmasi'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show Loading
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => {
                         Swal.showLoading();
                    }
                });

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        return_time: formData.get('return_time')
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Berhasil', data.message, 'success')
                        .then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message || 'Gagal memproses pengembalian', 'error');
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                });
            }
        });
    });
}
</script>
@endsection
