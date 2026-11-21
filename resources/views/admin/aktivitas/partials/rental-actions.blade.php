@php($rentalPaid = $request->paymentReceived())
<p class="small mb-2">Pembayaran: <strong>{{ $rentalPaid ? 'Diterima (Lunas)' : $request->paymentStatusLabel() }}</strong>. Penyerahan alat dan pembayaran diperiksa terpisah.</p>
@if($request->cancellation_status === 'pending')
    <button class="btn btn-danger mb-2" onclick="handleCancellation({{ $request->id }}, 'rental', 'approve')">Setujui Pembatalan</button>
    <button class="btn btn-secondary" onclick="showCancellationRejectModal({{ $request->id }}, 'rental')">Tolak Pembatalan</button>
@elseif(!in_array($request->status, ['completed','cancelled','rejected']))
    @include('admin.aktivitas.partials.payment-verify-controls', ['verifyType' => 'rental'])
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
