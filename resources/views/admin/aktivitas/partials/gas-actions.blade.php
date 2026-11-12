@php($gasPaid = $request->paymentReceived())
<p class="small">Pembayaran: <strong>{{ $gasPaid ? 'Diterima' : 'Belum dikonfirmasi' }}</strong>. Pengiriman dan pembayaran diperiksa terpisah.</p>
@if($request->cancellation_status === 'pending')
    <button class="btn btn-danger mb-2" onclick="handleCancellation({{ $request->id }}, 'gas', 'approve')">Setujui Pembatalan</button>
    <button class="btn btn-secondary" onclick="showCancellationRejectModal({{ $request->id }}, 'gas')">Tolak Pembatalan</button>
@elseif(!in_array($request->status, ['completed','cancelled','rejected']))
    @if(!$gasPaid)
        @if(strtolower($request->payment_method) === 'tunai' || $request->proof_of_payment)
            <form method="POST" action="{{ route('admin.aktivitas.bukti-transaksi.verify', [$request->id, 'gas']) }}" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Pastikan uang benar-benar sudah diterima. Konfirmasi pembayaran?')">Konfirmasi Uang Diterima</button>
            </form>
        @else
            <p class="alert alert-warning">Warga belum mengirim bukti pembayaran.</p>
        @endif
        @if($request->proof_of_payment && $request->status === 'pending')
            <details class="mb-3"><summary>Bukti tidak sesuai? Minta ganti bukti</summary>
                <form method="POST" action="{{ route('admin.aktivitas.bukti-transaksi.reject', [$request->id, 'gas']) }}">
                    @csrf
                    <label for="gas-proof-reason">Alasan dan petunjuk untuk warga</label>
                    <textarea id="gas-proof-reason" name="reason" required maxlength="500" class="form-control"></textarea>
                    <button type="submit" class="btn btn-warning mt-2">Minta Ganti Bukti</button>
                </form>
            </details>
        @endif
    @endif
    <div class="d-grid gap-2">
        @if($request->status === 'pending')
            @if($gasPaid || strtolower($request->payment_method) === 'tunai')
                <button class="btn btn-primary" onclick="confirmApprove({{ $request->id }}, 'gas')">Setujui Pesanan dan Siapkan Gas</button>
            @endif
            <button class="btn btn-outline-danger" onclick="showRejectModal({{ $request->id }}, 'gas')">Tolak Pesanan</button>
        @elseif(in_array($request->status, ['confirmed','approved','being_prepared']))
            @if($request->delivery_method === 'antar')
                <button class="btn btn-primary" onclick="updateStatus('in_delivery')">Mulai Antar Gas</button>
            @else
                <button class="btn btn-primary" onclick="updateStatus('arrived')">Gas Siap Diambil</button>
            @endif
        @elseif($request->status === 'in_delivery')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadProofModal">Gas Sudah Tiba — Unggah Bukti</button>
        @elseif($request->status === 'arrived')
            @if($gasPaid)
                <button class="btn btn-success" onclick="updateStatus('completed')">Gas Diterima — Selesaikan Pesanan</button>
            @else
                <p class="alert alert-warning">Konfirmasi uang diterima sebelum menyelesaikan pesanan.</p>
            @endif
        @endif
    </div>
@else
    <p>{{ $request->rejection_reason ?? $request->admin_cancellation_response }}</p>
@endif
