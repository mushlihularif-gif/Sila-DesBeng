@php($rentalPaid = $request->paymentReceived())
<p class="small">Pembayaran: <strong>{{ $rentalPaid ? 'Diterima' : 'Belum dikonfirmasi' }}</strong>. Penyerahan alat dan pembayaran diperiksa terpisah.</p>
@if($request->cancellation_status === 'pending')
    <button class="btn btn-danger mb-2" onclick="handleCancellation({{ $request->id }}, 'rental', 'approve')">Setujui Pembatalan</button>
    <button class="btn btn-secondary" onclick="showCancellationRejectModal({{ $request->id }}, 'rental')">Tolak Pembatalan</button>
@elseif(!in_array($request->status, ['completed','cancelled','rejected']))
    @if(!$rentalPaid)
        @if(strtolower($request->payment_method) === 'tunai' || $request->payment_proof)
            <form method="POST" action="{{ route('admin.aktivitas.bukti-transaksi.verify', [$request->id, 'rental']) }}" class="mb-3">
                @csrf
                <button type="submit" class="btn btn-success w-100" onclick="return confirm('Pastikan uang benar-benar sudah diterima. Konfirmasi pembayaran?')">Konfirmasi Uang Diterima</button>
            </form>
        @else
            <p class="alert alert-warning">Warga belum mengirim bukti pembayaran.</p>
        @endif
        @if($request->payment_proof && $request->status === 'pending')
            <details class="mb-3"><summary>Bukti tidak sesuai? Minta ganti bukti</summary>
                <form method="POST" action="{{ route('admin.aktivitas.bukti-transaksi.reject', [$request->id, 'rental']) }}">
                    @csrf
                    <label for="rental-proof-reason">Alasan dan petunjuk untuk warga</label>
                    <textarea id="rental-proof-reason" name="reason" required maxlength="500" class="form-control"></textarea>
                    <button type="submit" class="btn btn-warning mt-2">Minta Ganti Bukti</button>
                </form>
            </details>
        @endif
    @endif
    <div class="d-grid gap-2">
        @if($request->status === 'pending')
            @if($rentalPaid || strtolower($request->payment_method) === 'tunai')
                <button class="btn btn-primary" onclick="confirmApprove({{ $request->id }}, 'rental')">Setujui Pesanan dan Siapkan Alat</button>
            @endif
            <button class="btn btn-outline-danger" onclick="showRejectModal({{ $request->id }}, 'rental')">Tolak Pesanan</button>
        @elseif(in_array($request->status, ['confirmed','approved','being_prepared']))
            @if($request->delivery_method === 'antar')
                <button class="btn btn-primary" onclick="updateStatus('in_delivery')">Mulai Antar Alat</button>
            @else
                <button class="btn btn-primary" onclick="updateStatus('arrived')">Alat Sudah Diserahkan ke Penyewa</button>
            @endif
        @elseif($request->status === 'in_delivery')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadProofModal">Alat Sudah Tiba — Unggah Bukti</button>
        @elseif($request->status === 'arrived')
            @if($rentalPaid)
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#returnModal">Catat Pengembalian Alat</button>
            @else
                <p class="alert alert-warning">Konfirmasi uang diterima sebelum menyelesaikan pesanan.</p>
            @endif
        @endif
    </div>
@else
    <p>{{ $request->admin_notes ?? $request->admin_cancellation_response }}</p>
@endif
