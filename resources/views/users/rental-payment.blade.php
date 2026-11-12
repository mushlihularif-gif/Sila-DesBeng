@extends('layouts.user')

@section('title', 'Rincian Sewa Alat')

@section('page')
<main class="flex-grow w-full bg-slate-50 py-28 px-4">
    <section class="max-w-2xl mx-auto">
        <div class="rounded-3xl bg-white shadow-lg border border-slate-100 p-6 sm:p-10">
            <div class="text-center mb-8">
                @php($statusLabel = ['pending'=>'Menunggu Konfirmasi', 'confirmed'=>'Pesanan Disetujui', 'approved'=>'Pesanan Disetujui', 'being_prepared'=>'Sedang Disiapkan', 'in_delivery'=>'Sedang Diantar', 'arrived'=>'Alat Diserahkan kepada Penyewa', 'completed'=>'Pesanan Selesai', 'rejected'=>'Pesanan Ditolak', 'cancelled'=>'Pesanan Dibatalkan'][$order->status] ?? 'Status Pesanan')
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900">{{ $statusLabel }}</h1>
                <p class="mt-2 text-sm text-slate-500">Nomor pesanan {{ $order->order_number }}</p>
            </div>
            @if(session('success'))<p role="status" class="mb-4 p-4 bg-green-50 text-green-800 rounded-xl">{{ session('success') }}</p>@endif
            @if($errors->any())<div role="alert" class="mb-4 p-4 bg-red-50 text-red-800 rounded-xl">{{ $errors->first() }}</div>@endif
            <div class="rounded-2xl bg-slate-50 p-5 space-y-3">
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">Produk</span>
                    <span class="font-semibold text-slate-900 text-right">{{ $order->barang?->nama_barang }}</span>
                </div>
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">Jumlah</span>
                    <span class="font-semibold text-slate-900">{{ $order->quantity }}</span>
                </div>
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">Pembayaran</span>
                    <span class="font-semibold text-slate-900">{{ ucfirst($order->payment_method) }}</span>
                </div>
                <div class="flex justify-between gap-4 border-t border-slate-200 pt-3">
                    <span class="font-bold text-slate-700">Total</span>
                    <span class="font-black text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>

            <p class="mt-4 text-sm">Jadwal: {{ $order->start_date->format('d/m/Y') }} – {{ $order->end_date->format('d/m/Y') }}. Total mengikuti nilai pesanan saat dibuat.</p>
            @if($order->return_time)
                <p class="mt-3 text-sm">Dikembalikan: {{ $order->return_time->format('d/m/Y H:i') }}. Layak pakai: {{ $order->returned_quantity ?? $order->quantity }} unit. {{ $order->return_notes }}</p>
            @endif
            <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50 p-5 text-sm text-blue-900">
                <p class="font-bold">Pembayaran: {{ $paid ? 'Sudah dikonfirmasi diterima' : (in_array($order->status, ['cancelled','rejected']) ? 'Pesanan tidak dilanjutkan' : 'Belum dikonfirmasi') }}</p>
                @if(in_array($order->status, ['cancelled','rejected']))
                    <p class="mt-2">{{ $order->admin_notes }} Jika sudah membayar manual, hubungi pengelola untuk memastikan pengembalian dana.</p>
                @elseif(strtolower($order->payment_method) === 'tunai')
                    <p class="mt-2">Bayar kepada petugas saat {{ $order->delivery_method === 'jemput' ? 'mengambil alat' : 'alat diterima' }}. Petugas akan mencatat penerimaan pembayaran.</p>
                @elseif(!$paid && !$order->payment_proof)
                    @if(strtolower($order->payment_method) === 'transfer')
                        <p class="mt-2">Transfer ke {{ $setting->bank_name }} — {{ $setting->bank_account_number }}, atas nama {{ $setting->bank_account_holder }}.</p>
                    @else
                        <p class="mt-2">Kirim ke {{ $setting->ewallet_name }} — {{ $setting->ewallet_number }}, atas nama {{ $setting->ewallet_account_holder }}.</p>
                    @endif
                    <p class="mt-2">Unggah bukti setelah membayar. Jika bukti sebelumnya tidak sesuai, periksa alasan pada notifikasi Anda lalu kirim bukti yang benar.</p>
                @elseif(!$paid)
                    <p class="mt-2">Bukti sudah dikirim dan menunggu pemeriksaan petugas.</p>
                @endif
                @if($order->payment_proof)
                    <p class="mt-3"><a class="font-bold underline" href="{{ Storage::url($order->payment_proof) }}" target="_blank" rel="noopener">Lihat bukti pembayaran yang dikirim</a></p>
                @endif
            </div>
            @if($order->status === 'pending' && $order->cancellation_status !== 'pending' && !$paid && !$order->payment_proof && strtolower($order->payment_method) !== 'tunai')
                <form action="{{ route('rental.payment.proof', $order->id) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-3">
                    @csrf
                    <label for="rental-proof" class="block font-semibold">Bukti pembayaran (JPG, PNG, atau PDF, maksimal 5 MB)</label>
                    <input id="rental-proof" type="file" name="payment_proof" accept="image/jpeg,image/png,application/pdf" required class="block w-full">
                    <button type="submit" class="rounded-xl bg-blue-600 text-white px-5 py-3 font-bold">Kirim Bukti Pembayaran</button>
                </form>
            @endif

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('user.activity') }}" class="rounded-xl bg-blue-600 px-6 py-3 text-center font-bold text-white hover:bg-blue-700">Riwayat Pesanan</a>
                @if($order->receipt_path)
                    <a href="{{ route('receipt.rental.view', $order->id) }}" target="_blank" rel="noopener" class="rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-700 hover:bg-slate-50">Lihat Bukti Transaksi</a>
                @endif
            </div>


        </div>
    </section>
</main>
@endsection
