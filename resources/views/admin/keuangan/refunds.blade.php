@extends('admin.layouts.admin')

@section('title', 'Pengembalian Dana Warga')

@section('content')
<style>
    .refund-card {
        border-radius: 16px;
        border: 1px solid #eef1f6;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .refund-card:hover {
        box-shadow: 0 6px 18px rgba(67, 89, 113, 0.08) !important;
    }
    .refund-card.is-pending {
        border-left: 4px solid #ffab00;
    }
    .refund-card.is-completed {
        border-left: 4px solid #71dd37;
    }
    .refund-upload-box {
        border: 2px dashed #d5dce6;
        border-radius: 12px;
        background: #f9fafc;
        padding: 1rem;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }
    .refund-upload-box:hover {
        border-color: #696cff;
        background: rgba(105, 108, 255, 0.03);
    }
</style>

@php
    $serviceMeta = [
        'gas' => ['label' => 'Gas Daerah', 'icon' => 'bx-gas-pump', 'color' => 'danger'],
        'rental' => ['label' => 'Sewa Alat', 'icon' => 'bx-wrench', 'color' => 'warning'],
        'mobil' => ['label' => 'Transportasi', 'icon' => 'bx-car', 'color' => 'info'],
        'fasilitas' => ['label' => 'Fasilitas Umum', 'icon' => 'bx-buildings', 'color' => 'success'],
    ];
    $pendingCount = $refunds->getCollection()->where('refund_status', 'pending')->count();
@endphp

<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <span class="text-muted fw-light">Tata Kelola Desa /</span> Pengembalian Dana Warga
            </h4>
            <p class="text-muted small mb-0">Pencatatan dan bukti pengembalian dana manual untuk pesanan yang dibatalkan setelah dibayar.</p>
        </div>
        <a href="{{ route('admin.laporan.pendapatan') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
            <i class="bx bx-line-chart"></i>
            <span>Laporan Pendapatan</span>
        </a>
    </div>

    <!-- Flash & Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible border-0 shadow-sm rounded-4 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-check-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible border-0 shadow-sm rounded-4 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-error-circle fs-4 me-2 flex-shrink-0"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible border-0 shadow-sm rounded-4 d-flex align-items-start mb-4" role="alert">
            <i class="bx bx-error-circle fs-4 me-2 mt-1 flex-shrink-0"></i>
            <div>
                <div class="fw-bold mb-1">Gagal menyimpan catatan pengembalian:</div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Ringkasan & Panduan -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card bg-label-primary border-0 shadow-none rounded-4 h-100">
                <div class="card-body p-3 p-md-4 d-flex align-items-start gap-3">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bx bx-info-circle fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Prosedur Pengembalian Manual</h6>
                        <p class="mb-0 text-primary small" style="opacity: 0.9; line-height: 1.5;">
                            Daftar ini berisi pembayaran transfer atau tunai yang sudah diterima desa, lalu pesanannya dibatalkan.
                            <strong>Kembalikan dana ke warga terlebih dahulu</strong> melalui transfer bank atau tunai langsung, kemudian unggah bukti pengembaliannya di bawah ini. Tombol ini hanya untuk pencatatan dan tidak mentransfer uang otomatis.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 {{ $pendingTotal > 0 ? 'bg-label-warning' : 'bg-label-success' }}">
                <div class="card-body p-3 p-md-4 d-flex flex-column justify-content-center">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-uppercase {{ $pendingTotal > 0 ? 'text-warning' : 'text-success' }}" style="letter-spacing: 0.5px;">
                            Belum Dicatat Dikembalikan
                        </span>
                        <i class="bx {{ $pendingTotal > 0 ? 'bx-time-five text-warning' : 'bx-check-double text-success' }} fs-4"></i>
                    </div>
                    <h3 class="fw-bold mb-1 {{ $pendingTotal > 0 ? 'text-dark' : 'text-success' }}">
                        Rp {{ number_format($pendingTotal, 0, ',', '.') }}
                    </h3>
                    <small class="text-muted">
                        @if($pendingTotal > 0)
                            Segera proses pengembalian &amp; unggah buktinya
                        @else
                            Semua pengembalian dana sudah tercatat selesai
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Pengembalian -->
    @forelse($refunds as $refund)
        @php
            $meta = $serviceMeta[$refund->reference_type] ?? ['label' => 'Arsip Transaksi', 'icon' => 'bx-receipt', 'color' => 'secondary'];
            $isPending = $refund->refund_status === 'pending';
        @endphp
        <div class="card refund-card shadow-sm mb-3 {{ $isPending ? 'is-pending' : 'is-completed' }}">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 pb-3 border-bottom">
                    <div class="d-flex align-items-start gap-3">
                        <div class="avatar bg-label-{{ $meta['color'] }} rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="bx {{ $meta['icon'] }} fs-4"></i>
                        </div>
                        <div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <h6 class="fw-bold text-dark mb-0">{{ $meta['label'] }} #{{ $refund->reference_id }}</h6>
                                @if($isPending)
                                    <span class="badge bg-label-warning rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                        <i class="bx bx-time-five me-1"></i>Menunggu Pengembalian
                                    </span>
                                @else
                                    <span class="badge bg-label-success rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                        <i class="bx bx-check-circle me-1"></i>Sudah Dikembalikan
                                    </span>
                                @endif
                            </div>
                            <div class="small text-muted">
                                <i class="bx bx-user me-1"></i>Pemesan: <strong class="text-dark">{{ $refund->recipient_name }}</strong>
                            </div>
                            @if($refund->notes)
                                <div class="small text-muted mt-1">
                                    <i class="bx bx-note me-1"></i>{{ $refund->notes }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="text-start text-md-end flex-shrink-0">
                        <small class="text-muted d-block" style="font-size: 0.72rem;">Nominal Dikembalikan</small>
                        <h5 class="fw-bold text-primary mb-0">Rp {{ number_format($refund->amount, 0, ',', '.') }}</h5>
                    </div>
                </div>

                <div class="pt-3">
                    @if($isPending)
                        <form method="post" enctype="multipart/form-data" action="{{ route('admin.refunds.complete', $refund->id) }}">
                            @csrf
                            <div class="refund-upload-box mb-3">
                                <label class="form-label fw-semibold small text-dark d-block mb-1" for="proof_{{ $refund->id }}">
                                    <i class="bx bx-upload me-1 text-primary"></i>
                                    Unggah Bukti Transfer atau Tanda Terima Pengembalian <span class="text-danger">*</span>
                                </label>
                                <small class="text-muted d-block mb-2">Format JPG, PNG, atau WebP (maksimal 5 MB).</small>
                                <input id="proof_{{ $refund->id }}" class="form-control bg-white" type="file" name="proof" accept="image/jpeg,image/png,image/webp" required>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="confirmed" value="1" id="confirm_{{ $refund->id }}" required>
                                <label class="form-check-label small fw-medium text-dark" for="confirm_{{ $refund->id }}">
                                    Saya sudah mengembalikan seluruh nominal <strong>Rp {{ number_format($refund->amount, 0, ',', '.') }}</strong> di atas kepada pemesan.
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm w-100 w-sm-auto">
                                <i class="bx bx-check-double me-1"></i> Catat Dana Sudah Dikembalikan
                            </button>
                        </form>
                    @else
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                            <div class="small text-muted">
                                <i class="bx bx-calendar-check text-success me-1"></i>
                                Dikembalikan menurut catatan pengelola:
                                <strong class="text-dark">{{ $refund->refunded_at?->format('d M Y, H:i') }} WIB</strong>
                            </div>
                            <div class="d-flex gap-2 w-100 w-sm-auto">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 flex-grow-1 flex-sm-grow-0"
                                        onclick="previewRefundProof('{{ route('admin.refunds.proof', $refund->id) }}', '{{ $meta['label'] }} #{{ $refund->reference_id }}')">
                                    <i class="bx bx-image-alt me-1"></i> Lihat Bukti
                                </button>
                                <a href="{{ route('admin.refunds.proof', $refund->id) }}" class="btn btn-sm btn-light border rounded-pill px-3 flex-grow-1 flex-sm-grow-0" target="_blank" rel="noopener">
                                    <i class="bx bx-link-external me-1"></i> Tab Baru
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body text-center py-5 px-3">
                <div class="avatar avatar-lg bg-label-success text-success rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                    <i class="bx bx-check-shield fs-1"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">Belum Ada Catatan Pengembalian Dana</h6>
                <p class="text-muted small mb-0">Tidak ada pesanan lunas yang dibatalkan dan memerlukan pengembalian dana manual saat ini.</p>
            </div>
        </div>
    @endforelse

    @if($refunds->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $refunds->links() }}
        </div>
    @endif
</div>

@push('modals')
<div class="modal fade" id="refundProofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-bottom bg-white py-3 px-4">
                <h6 class="modal-title fw-bold mb-0" id="refundProofTitle">Bukti Pengembalian Dana</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-3 bg-light text-center">
                <img src="" id="refundProofImg" class="img-fluid rounded-3 shadow-sm" style="max-height: 72vh;" alt="Bukti Pengembalian">
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function previewRefundProof(url, title) {
        const img = document.getElementById('refundProofImg');
        const label = document.getElementById('refundProofTitle');
        const modalEl = document.getElementById('refundProofModal');
        if (!img || !modalEl) return;
        img.src = url;
        if (label) label.textContent = 'Bukti Pengembalian — ' + title;
        (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
    }
</script>
@endpush
@endsection
