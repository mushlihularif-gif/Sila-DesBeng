@extends('layouts.user')

@section('title', 'Rincian Pesanan Gas')

@section('page')
<main class="flex-grow w-full bg-slate-50 py-28 px-4">
    <section class="max-w-2xl mx-auto">
        <div class="rounded-3xl bg-white shadow-lg border border-slate-100 p-6 sm:p-10">
            <div class="text-center mb-8">
                @if($order->status === 'pending')
                    <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-full bg-amber-50 text-amber-600">
                        <i class="bx bx-time-five text-4xl"></i>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900">Pesanan Menunggu Konfirmasi</h1>
                    <p class="mt-2 text-sm text-slate-500">Nomor pesanan {{ $order->order_number }}</p>
                @else
                    <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-full bg-emerald-50 text-emerald-600">
                        <i class="bx bx-check-circle text-4xl"></i>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900">Status Pesanan Diperbarui</h1>
                    <p class="mt-2 text-sm text-slate-500">Nomor pesanan {{ $order->order_number }}</p>
                @endif
            </div>

            <div class="rounded-2xl bg-slate-50 p-5 space-y-3">
                <div class="flex justify-between gap-4 text-sm">
                    <span class="text-slate-500">Produk</span>
                    <span class="font-semibold text-slate-900 text-right">{{ $order->item_name }}</span>
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
                    <span class="font-black text-slate-900">Rp {{ number_format($order->price * $order->quantity, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50 p-5 text-sm text-blue-900">
                @if(strtolower($order->payment_method) === 'tunai')
                    Bayar langsung kepada petugas sesuai petunjuk pengelola layanan.
                @else
                    Pembayaran manual atau bukti pembayaran akan diperiksa oleh petugas sebelum pesanan diproses.
                @endif
                @if($order->proof_of_payment)
                    <p class="mt-3"><a class="font-bold underline" href="{{ Storage::url($order->proof_of_payment) }}" target="_blank" rel="noopener">Lihat bukti pembayaran yang dikirim</a></p>
                @endif
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('user.activity') }}" class="rounded-xl bg-blue-600 px-6 py-3 text-center font-bold text-white hover:bg-blue-700">Riwayat Pesanan</a>
                @if($order->receipt_path)
                    <a href="{{ route('receipt.gas.view', $order->id) }}" target="_blank" rel="noopener" class="rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-700 hover:bg-slate-50">Lihat Bukti Transaksi</a>
                @endif
            </div>

            @if($order->status === 'pending' && app()->environment(['local', 'testing']))
                <form action="{{ route('user.gas.payment.simulate', $order->id) }}" method="POST" class="mt-5 text-center">
                    @csrf
                    <button class="text-sm font-semibold text-emerald-700 underline" type="submit">Simulasikan konfirmasi untuk pengujian lokal</button>
                </form>
            @endif
        </div>
    </section>
</main>
@endsection
