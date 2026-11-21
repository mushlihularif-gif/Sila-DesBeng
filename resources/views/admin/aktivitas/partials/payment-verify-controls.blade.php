@php
    $pType = $verifyType ?? $type ?? 'rental';
    $pSubtotal = $request->subtotalAmount();
    $pPaid = $request->paidAmount();
    $pRemaining = $request->remainingAmount();
    $pIsPaid = $request->paymentReceived();
    $pStatusLabel = $request->paymentStatusLabel();
    $pStatusBadge = $request->paymentStatusBadgeClass();
    $pMethod = strtolower(trim((string) ($request->payment_method ?? '')));
    $pIsCash = $pMethod === 'tunai' || $pMethod === 'cash';
    $pProof = $request->paymentProofPath();
    $pScan = $request->proofScanSummary();
    $pUid = $pType . '-' . $request->id;
@endphp

@if($pSubtotal > 0)
<div class="rounded-3 border bg-light p-3 mb-3">
    <div class="d-flex justify-content-between align-items-center mb-1 small">
        <span class="text-muted">Subtotal Pesanan</span>
        <strong class="text-dark">Rp {{ number_format($pSubtotal, 0, ',', '.') }}</strong>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-1 small">
        <span class="text-muted">Nominal Dibayarkan</span>
        <strong class="{{ $pIsPaid ? 'text-success' : ($pPaid > 0 ? 'text-danger' : 'text-dark') }}">
            Rp {{ number_format($pPaid, 0, ',', '.') }}
        </strong>
    </div>
    <div class="d-flex justify-content-between align-items-center pt-1 border-top small">
        <span class="text-muted">Sisa Tagihan</span>
        <div class="d-flex align-items-center gap-1">
            <strong class="{{ $pRemaining > 0.01 ? 'text-danger' : 'text-success' }}">
                Rp {{ number_format($pRemaining, 0, ',', '.') }}
            </strong>
            <span class="badge {{ $pStatusBadge }} rounded-pill" style="font-size: 0.65rem;">{{ $pStatusLabel }}</span>
        </div>
    </div>
</div>

@if(!$pIsPaid && !in_array($request->status, ['completed', 'cancelled', 'rejected']) && $request->cancellation_status !== 'pending')
    @if($pProof && $pScan)
        @if(($pScan['status'] ?? '') === 'mismatch')
            <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
                <div class="fw-bold mb-1"><i class="bx bx-error-alt me-1"></i>Peringatan Hasil Pindai Bukti</div>
                <div>{{ $pScan['message'] }}</div>
            </div>
        @elseif(($pScan['status'] ?? '') === 'matched')
            <div class="alert alert-success py-2 px-3 small rounded-3 mb-3">
                <div class="fw-bold mb-1"><i class="bx bx-check-shield me-1"></i>Hasil Pindai Bukti Cocok</div>
                <div>{{ $pScan['message'] }}</div>
            </div>
        @endif
    @endif

    @if($pIsCash || $pProof)
        <div class="border rounded-3 p-3 mb-3 bg-white" data-payment-verify-box
             data-subtotal="{{ (int) round($pSubtotal) }}"
             data-already-paid="{{ (int) round($pPaid) }}"
             data-remaining="{{ (int) round($pRemaining) }}"
             data-uid="{{ $pUid }}">
            <form method="POST" action="{{ route('admin.aktivitas.bukti-transaksi.verify', [$request->id, $pType]) }}" class="verify-amount-form">
                @csrf
                <input type="hidden" name="amount_mode" value="{{ $pPaid > 0 ? 'delta' : 'total' }}">
                <label for="confirmed-amount-{{ $pUid }}" class="form-label small fw-bold text-dark mb-1">
                    {{ $pPaid > 0 ? 'Nominal Pelunasan Diterima (Rp)' : 'Nominal Uang Diterima Nyata (Rp)' }} <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text bg-light fw-bold">Rp</span>
                    <input type="text"
                           inputmode="numeric"
                           id="confirmed-amount-{{ $pUid }}"
                           name="confirmed_amount"
                           class="form-control fw-bold verify-amount-input"
                           value="{{ $pIsCash ? number_format($pRemaining, 0, ',', '.') : (($pScan['detected_amount'] ?? null) !== null ? number_format((float) $pScan['detected_amount'], 0, ',', '.') : '') }}"
                           placeholder="Ketik nominal sesuai bukti / mutasi ({{ number_format($pRemaining, 0, ',', '.') }})"
                           required>
                </div>
                <div class="verify-amount-feedback small mb-2 text-muted">
                    Ketik nominal uang yang benar-benar masuk ke rekening/kas. Tagihan yang harus masuk: <strong>Rp {{ number_format($pRemaining, 0, ',', '.') }}</strong>.
                </div>
                <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill py-2 fw-semibold verify-submit-btn">
                    <i class="bx bx-check-circle me-1"></i>Konfirmasi Uang Diterima
                </button>
            </form>

            @if($pProof && !$pIsCash && $request->status === 'pending')
                <div class="mt-2 d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.aktivitas.bukti-transaksi.scan-proof', [$request->id, $pType]) }}" class="flex-grow-1">
                        @csrf
                        <button type="submit" class="btn btn-outline-info btn-sm w-100 rounded-pill">
                            <i class="bx bx-scan me-1"></i>Pindai Nominal Bukti
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @else
        <div class="alert alert-warning py-2 px-3 small rounded-3 mb-3">
            <i class="bx bx-info-circle me-1"></i>Warga belum mengirim bukti pembayaran.
        </div>
    @endif

    @if($pProof && !$pIsCash && $request->status === 'pending')
        <details class="mb-3 border rounded-3 p-2.5 bg-white verify-reject-details" id="reject-details-{{ $pUid }}">
            <summary class="small fw-semibold text-warning cursor-pointer" style="cursor: pointer;">
                <i class="bx bx-error-circle me-1"></i>Bukti tidak sesuai / kurang bayar? Tolak atau catat selisih
            </summary>
            <form method="POST" action="{{ route('admin.aktivitas.bukti-transaksi.reject', [$request->id, $pType]) }}" class="mt-2 pt-2 border-top">
                @csrf
                <div class="mb-2">
                    <label for="received-partial-{{ $pUid }}" class="form-label small fw-semibold text-dark mb-1">
                        Total Uang yang Sudah Benar-Benar Masuk (Rp) <span class="text-muted fw-normal">(Opsional)</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">Rp</span>
                        <input type="text"
                               inputmode="numeric"
                               id="received-partial-{{ $pUid }}"
                               name="received_amount"
                               class="form-control verify-partial-input"
                               value="{{ $pPaid > 0 ? number_format($pPaid, 0, ',', '.') : '' }}"
                               placeholder="Kosongkan atau 0 jika belum ada uang masuk">
                    </div>
                    <div class="form-text" style="font-size: 0.72rem;">
                        Contoh: jika warga baru transfer Rp 20.000 dari tagihan Rp 200.000, isi <strong>20000</strong> agar tercatat kurang bayar Rp 180.000 dan warga diminta melunasi sisanya.
                    </div>
                </div>
                <div class="mb-2">
                    <label for="proof-reason-{{ $pUid }}" class="form-label small fw-semibold text-dark mb-1">
                        Alasan dan Petunjuk untuk Warga <span class="text-danger">*</span>
                    </label>
                    <textarea id="proof-reason-{{ $pUid }}" name="reason" required maxlength="500" rows="2" class="form-control form-control-sm verify-reason-input" placeholder="Contoh: Nominal transfer Rp 20.000 kurang Rp 180.000 dari tagihan Rp 200.000."></textarea>
                </div>
                <button type="submit" class="btn btn-warning btn-sm w-100 rounded-pill fw-semibold">
                    <i class="bx bx-send me-1"></i>Minta Ganti Bukti / Pelunasan
                </button>
            </form>
        </details>
    @endif
@endif

@once
<script>
document.addEventListener('DOMContentLoaded', function () {
    function parseRupiah(val) {
        if (!val) return null;
        const clean = String(val).replace(/[^\d]/g, '');
        if (!clean) return null;
        return parseInt(clean, 10);
    }
    function fmtRp(num) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.max(0, Math.round(num || 0)));
    }

    document.querySelectorAll('[data-payment-verify-box]').forEach(function (box) {
        const subtotal = parseInt(box.dataset.subtotal || '0', 10);
        const alreadyPaid = parseInt(box.dataset.alreadyPaid || '0', 10);
        const remaining = parseInt(box.dataset.remaining || '0', 10);
        const uid = box.dataset.uid;
        const input = box.querySelector('.verify-amount-input');
        const feedback = box.querySelector('.verify-amount-feedback');
        const submitBtn = box.querySelector('.verify-submit-btn');
        const rejectDetails = document.getElementById('reject-details-' + uid);

        function evaluate() {
            const val = parseRupiah(input.value);
            if (val === null) {
                feedback.className = 'verify-amount-feedback small mb-2 text-muted';
                feedback.innerHTML = 'Ketik nominal uang yang benar-benar masuk ke rekening/kas. Sisa tagihan: <strong>' + fmtRp(remaining) + '</strong>.';
                submitBtn.disabled = false;
                return;
            }
            const totalReceived = alreadyPaid > 0 ? (alreadyPaid + val) : val;
            if (totalReceived < subtotal) {
                const kurang = subtotal - totalReceived;
                feedback.className = 'verify-amount-feedback alert alert-danger py-2 px-2.5 small mb-2 rounded-3';
                feedback.innerHTML = '<strong><i class="bx bx-x-circle me-1"></i>KURANG BAYAR ' + fmtRp(kurang) + '!</strong><br>' +
                    'Diterima ' + fmtRp(totalReceived) + ' dari subtotal ' + fmtRp(subtotal) + '. Konfirmasi lunas dikunci.' +
                    (rejectDetails ? ' <button type="button" class="btn btn-xs btn-warning rounded-pill mt-1 fw-semibold fill-shortfall-btn">Catat Kurang Bayar &amp; Minta Pelunasan</button>' : '');
                submitBtn.disabled = true;

                const fillBtn = feedback.querySelector('.fill-shortfall-btn');
                if (fillBtn && rejectDetails) {
                    fillBtn.addEventListener('click', function () {
                        rejectDetails.open = true;
                        const partialInput = rejectDetails.querySelector('.verify-partial-input');
                        const reasonInput = rejectDetails.querySelector('.verify-reason-input');
                        if (partialInput) partialInput.value = totalReceived;
                        if (reasonInput) {
                            reasonInput.value = 'Nominal transfer baru diterima ' + fmtRp(totalReceived) + ' dari subtotal ' + fmtRp(subtotal) + ' (kurang ' + fmtRp(kurang) + '). Mohon transfer kekurangannya lalu unggah bukti pelunasan.';
                        }
                        rejectDetails.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                }
            } else if (totalReceived > subtotal) {
                const lebih = totalReceived - subtotal;
                feedback.className = 'verify-amount-feedback alert alert-warning py-2 px-2.5 small mb-2 rounded-3';
                feedback.innerHTML = '<strong>Nominal melebihi tagihan sebesar ' + fmtRp(lebih) + '.</strong> Periksa kembali nominal yang diketik (Subtotal: ' + fmtRp(subtotal) + ').';
                submitBtn.disabled = true;
            } else {
                feedback.className = 'verify-amount-feedback alert alert-success py-2 px-2.5 small mb-2 rounded-3';
                feedback.innerHTML = '<i class="bx bx-check-circle me-1"></i><strong>Cocok!</strong> Total diterima ' + fmtRp(totalReceived) + ' sesuai subtotal tagihan (Lunas).';
                submitBtn.disabled = false;
            }
        }

        if (input) {
            input.addEventListener('input', evaluate);
            evaluate();
        }
    });
});
</script>
@endonce
@else
<div class="alert alert-info py-2 px-3 small rounded-3 mb-3">
    <i class="bx bx-gift me-1"></i>Layanan ini <strong>Gratis</strong> (tidak ada tagihan pembayaran).
</div>
@endif
