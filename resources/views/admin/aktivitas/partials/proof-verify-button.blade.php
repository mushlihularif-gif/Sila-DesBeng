@php
    $bType = $verifyType;
    $bSubtotal = (int) round($payment->subtotalAmount());
    $bPaid = (int) round($payment->paidAmount());
    $bRemaining = (int) round($payment->remainingAmount());
    $bIsPaid = $payment->paymentReceived();
    $bMethod = strtolower(trim((string) ($payment->payment_method ?? 'tunai')));
    $bIsCash = in_array($bMethod, ['tunai', 'cash'], true);
    $bProof = $payment->paymentProofPath();
    $bProofUrl = $bProof ? asset('storage/' . $bProof) : '';
    $bReceiptRoute = match ($bType) {
        'rental' => route('receipt.rental.view', $payment->id),
        'gas' => route('receipt.gas.view', $payment->id),
        'mobil' => route('receipt.mobil.view', $payment->id),
        'fasilitas' => route('receipt.fasilitas.view', $payment->id),
        default => '',
    };
    $bScan = $payment->proofScanSummary();
    $bClosed = in_array($payment->status, ['completed', 'cancelled', 'rejected'], true) || ($payment->cancellation_status ?? '') === 'pending';
@endphp

@if($bSubtotal > 0 && !$bClosed)
    @if($bIsPaid)
        <span class="badge bg-label-success rounded-pill"><i class="bx bx-check-circle me-1"></i>Lunas</span>
    @elseif($bIsCash || $bProof)
        <button type="button"
                class="btn btn-sm btn-success rounded-pill px-3 shadow-sm open-verify-modal-btn"
                data-id="{{ $payment->id }}"
                data-type="{{ $bType }}"
                data-buyer="{{ $buyerName }}"
                data-item="{{ $itemLabel }}"
                data-order-number="{{ $payment->order_number ?? ('#' . $payment->id) }}"
                data-subtotal="{{ $bSubtotal }}"
                data-paid="{{ $bPaid }}"
                data-remaining="{{ $bRemaining }}"
                data-method="{{ $bMethod }}"
                data-is-cash="{{ $bIsCash ? '1' : '0' }}"
                data-can-reject="{{ (!$bIsCash && $bProof && $payment->status === 'pending') ? '1' : '0' }}"
                data-proof-url="{{ $bProofUrl }}"
                data-receipt-url="{{ $bReceiptRoute }}"
                data-scan-status="{{ $bScan['status'] ?? '' }}"
                data-scan-amount="{{ isset($bScan['detected_amount']) && $bScan['detected_amount'] !== null ? (int) round($bScan['detected_amount']) : '' }}"
                data-scan-message="{{ $bScan['message'] ?? '' }}"
                onclick="openVerifyPaymentModal(this)">
            <i class="bx bx-check-shield me-1"></i>Verifikasi Bayar
        </button>
        @if(!$bIsCash && $bProof && $payment->status === 'pending')
            <button type="button"
                    class="btn btn-sm btn-outline-warning rounded-pill px-3"
                    onclick="openRejectProofModal({{ $payment->id }}, '{{ $bType }}', '{{ addslashes($buyerName) }}', {{ $bSubtotal }}, {{ $bPaid }}, '')">
                <i class="bx bx-error-circle me-1"></i>Tolak / Selisih
            </button>
        @endif
    @else
        <span class="badge bg-label-secondary rounded-pill">Menunggu bukti</span>
    @endif
@elseif($bSubtotal <= 0)
    <span class="badge bg-label-info rounded-pill">Gratis</span>
@endif
