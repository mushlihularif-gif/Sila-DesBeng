@extends('admin.layouts.admin')
@section('content')
<div class="container-xxl py-4">
    <h3>Pengembalian Dana Warga</h3>
    <p>Daftar pembayaran manual yang sudah diterima, lalu pesanannya dibatalkan. Kembalikan dana melalui transfer atau tunai terlebih dahulu, kemudian simpan buktinya di sini. Tombol ini tidak mentransfer uang.</p>
    <div class="alert alert-warning">Belum dicatat dikembalikan: <strong>Rp {{ number_format($pendingTotal, 0, ',', '.') }}</strong></div>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    @forelse($refunds as $refund)
    <div class="card p-4 mb-3">
        <h5>{{ ['gas'=>'Gas Daerah','rental'=>'Sewa Alat','mobil'=>'Transportasi','fasilitas'=>'Fasilitas Umum'][$refund->reference_type] ?? 'Arsip transaksi' }} #{{ $refund->reference_id }} — Rp {{ number_format($refund->amount, 0, ',', '.') }}</h5>
        <p>Pemesan: <strong>{{ $refund->recipient_name }}</strong></p>
        <p>{{ $refund->notes }}</p>
        @if($refund->refund_status === 'pending')
        <form method="post" enctype="multipart/form-data" action="{{ route('admin.refunds.complete', $refund->id) }}">@csrf
            <label class="d-block mb-2">Bukti transfer atau tanda terima pengembalian (JPG, PNG, WebP; maksimal 5 MB)<input class="form-control mt-2" type="file" name="proof" accept="image/jpeg,image/png,image/webp" required></label>
            <label class="d-block mb-3"><input type="checkbox" name="confirmed" value="1" required> Saya sudah mengembalikan seluruh nominal di atas kepada pemesan.</label>
            <button class="btn btn-primary">Catat Dana Sudah Dikembalikan</button>
        </form>
        @else
        <p>Dikembalikan menurut catatan pengelola: {{ $refund->refunded_at?->format('d M Y H:i') }}</p>
        <a href="{{ route('admin.refunds.proof', $refund->id) }}" class="btn btn-outline-primary" target="_blank" rel="noopener">Lihat Bukti Pengembalian</a>
        @endif
    </div>
    @empty<p>Belum ada catatan pengembalian dana manual.</p>@endforelse
    {{ $refunds->links() }}
</div>
@endsection
