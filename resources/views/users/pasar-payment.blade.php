@extends('layouts.user')

@section('title', 'Rincian Pesanan Pasar Daerah')

@section('page')
<main class="flex-grow w-full bg-slate-50 py-28 px-4">
    <section class="max-w-3xl mx-auto rounded-3xl bg-white shadow-lg border border-slate-100 p-6 sm:p-10">
        <header class="text-center mb-8">
            <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-full {{ $order->status === 'pending' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }}">
                <i class="bx {{ $order->status === 'pending' ? 'bx-time-five' : 'bx-check-circle' }} text-4xl"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900">
                {{ $order->status === 'pending' ? 'Pesanan Menunggu Konfirmasi' : 'Status Pesanan Diperbarui' }}
            </h1>
            <p class="mt-2 text-sm text-slate-500">Nomor pesanan {{ $order->order_number }}</p>
        </header>

        <div class="rounded-2xl bg-slate-50 p-5 space-y-3">
            <div class="flex justify-between gap-4 text-sm"><span class="text-slate-500">Metode</span><span class="font-semibold text-right">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span></div>
            <div class="flex justify-between gap-4 border-t border-slate-200 pt-3"><span class="font-bold">Total</span><span class="font-black">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span></div>
        </div>

        @if($order->status === 'pending')
            <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50 p-5 text-sm text-blue-950">
                @if(in_array(strtolower($order->payment_method), ['tunai', 'cash', 'cod']))
                    Siapkan pembayaran tunai saat menerima atau mengambil pesanan.
                @elseif(in_array(strtolower($order->payment_method), ['bank_transfer', 'transfer_bank', 'transfer manual', 'transfer_manual']))
                    @php
                        $regionSettings = $order->region ? $order->region->settings : [];
                        $bankName = $regionSettings['rekening_bank'] ?? 'Bank';
                        $bankNum = $regionSettings['rekening_nomor'] ?? '';
                        $bankHolder = $regionSettings['rekening_nama'] ?? ('BUMDes ' . ($order->region->name ?? 'Desa'));
                    @endphp
                    Transfer manual ke rekening {{ $bankName }}
                    @if($bankNum)
                        <strong>{{ $bankNum }}</strong> atas nama <strong>{{ $bankHolder }}</strong>.
                    @else
                        Hubungi pengelola toko untuk memperoleh nomor rekening.
                    @endif
                    Jika bukti pembayaran dilampirkan saat checkout, petugas akan memeriksanya.
                @elseif(strtolower($order->payment_method) === 'qris')
                    @php
                        $regionSettings = $order->region ? $order->region->settings : [];
                        $qrisImg = $regionSettings['qris_image'] ?? null;
                        $qrisNum = $regionSettings['qris_ewallet_number'] ?? '';
                    @endphp
                    Pindai QR pembayaran manual milik toko berikut untuk membayar.
                    @if($qrisImg)
                        <img src="{{ Storage::url($qrisImg) }}" alt="QR pembayaran toko" class="block w-48 h-48 mx-auto mt-4 object-contain rounded-xl bg-white p-2">
                    @endif
                    @if($qrisNum)
                        <p class="mt-3 text-center">Nomor E-Wallet: <strong>{{ $qrisNum }}</strong></p>
                    @endif
                @else
                    Silakan hubungi pengelola toko untuk menyelesaikan pembayaran manual.
                @endif
            </div>
        @endif

        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('user.activity') }}" class="rounded-xl bg-blue-600 px-6 py-3 text-center font-bold text-white hover:bg-blue-700">Riwayat Pesanan</a>
            <a href="{{ route('pasar.index') }}" class="rounded-xl border border-slate-200 px-6 py-3 text-center font-bold text-slate-700 hover:bg-slate-50">Kembali ke Pasar Daerah</a>
        </div>
    </section>
</main>
@endsection
