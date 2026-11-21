@php
    $cSubtotal = $payment->subtotalAmount();
    $cPaid = $payment->paidAmount();
    $cRemaining = $payment->remainingAmount();
    $cLabel = $payment->paymentStatusLabel();
    $cBadge = $payment->paymentStatusBadgeClass();
    $cScan = $payment->proofScanSummary();
    $cHasProof = (bool) $payment->paymentProofPath();
@endphp

@if($cSubtotal > 0)
    <div class="fw-bold text-dark">Rp {{ number_format($cSubtotal, 0, ',', '.') }}</div>
    <div class="small mt-1 d-flex flex-wrap align-items-center gap-1">
        <span class="text-muted">Dibayar:</span>
        <span class="fw-semibold {{ $cPaid >= $cSubtotal ? 'text-success' : ($cPaid > 0 ? 'text-danger' : 'text-muted') }}">
            Rp {{ number_format($cPaid, 0, ',', '.') }}
        </span>
        <span class="badge {{ $cBadge }} rounded-pill" style="font-size: 0.65rem;">{{ $cLabel }}</span>
    </div>
    @if($cRemaining > 0.01 && $cPaid > 0 && !in_array($payment->status, ['cancelled', 'rejected']))
        <div class="small text-danger fw-semibold" style="font-size: 0.72rem;">
            Kurang Rp {{ number_format($cRemaining, 0, ',', '.') }}
        </div>
    @endif
    @if($cHasProof && !$payment->paymentReceived() && $cScan && ($cScan['status'] ?? '') === 'mismatch')
        <div class="mt-1">
            <span class="badge bg-danger rounded-pill text-wrap text-start" style="font-size: 0.68rem;">
                <i class="bx bx-error-alt me-1"></i>Bukti terbaca Rp {{ number_format((float) ($cScan['detected_amount'] ?? 0), 0, ',', '.') }}
                @if(($cScan['shortfall'] ?? 0) > 0)
                    (Kurang Rp {{ number_format((float) $cScan['shortfall'], 0, ',', '.') }})
                @endif
            </span>
        </div>
    @elseif($cHasProof && !$payment->paymentReceived() && $cScan && ($cScan['status'] ?? '') === 'matched')
        <div class="mt-1">
            <span class="badge bg-label-success rounded-pill" style="font-size: 0.66rem;">
                <i class="bx bx-check-shield me-1"></i>Pindai cocok: Rp {{ number_format((float) ($cScan['detected_amount'] ?? 0), 0, ',', '.') }}
            </span>
        </div>
    @endif
@else
    <span class="badge bg-label-info rounded-pill">Gratis</span>
@endif
