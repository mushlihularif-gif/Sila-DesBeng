@extends('layouts.user')

@section('title', 'Validasi Dokumen - SegiDaerah')

@section('page')
<main class="flex-grow relative w-full">
    @include('partials.abstract-bg')
    
    <section class="relative z-10" style="padding-top: 12rem; padding-bottom: 6rem;">
        <div class="max-w-2xl mx-auto px-6">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
                
                <!-- Header -->
                <div class="px-8 py-6 {{ $valid ? 'bg-green-600' : 'bg-red-600' }} text-white text-center">
                    @if($valid)
                        <svg class="mx-auto h-16 w-16 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <h1 class="text-2xl font-bold">Transaksi Tervalidasi</h1>
                        <p class="text-green-100 mt-1 text-sm">Bukti transaksi ini tercatat di SegiDaerah</p>
                    @else
                        <svg class="mx-auto h-16 w-16 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <h1 class="text-2xl font-bold">Dokumen Tidak Valid</h1>
                        <p class="text-red-100 mt-1 text-sm">Token validasi tidak cocok atau dokumen telah dimanipulasi</p>
                    @endif
                </div>

                <!-- Content -->
                @if($valid && $transaksi)
                <div class="px-8 py-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">{{ $title }}</h3>
                    <dl class="space-y-4">
                        <div class="flex justify-between border-b border-gray-100 pb-3">
                            <dt class="text-sm font-medium text-gray-500">No. Pesanan</dt>
                            <dd class="text-sm font-bold text-gray-900">{{ $transaksi->order_number }}</dd>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Nama Pemesan</dt>
                            <dd class="text-sm text-gray-900">{{ $transaksi->recipient_name ?? $transaksi->full_name ?? $transaksi->user->name ?? '-' }}</dd>
                        </div>
                        
                        {{-- Penyesuaian Detail Barang / Jasa berdasarkan tipe --}}
                        <div class="flex justify-between border-b border-gray-100 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Keterangan</dt>
                            <dd class="text-sm text-gray-900 text-right">
                                @if($type === 'rental')
                                    {{ $transaksi->barang->nama_barang ?? '-' }}
                                @elseif($type === 'gas')
                                    {{ $transaksi->item_name ?? '-' }} ({{ $transaksi->quantity }} tabung)
                                @elseif($type === 'mobil')
                                    {{ $transaksi->mobil->nama_mobil ?? '-' }}
                                @elseif($type === 'fasilitas')
                                    {{ $transaksi->fasilitas->nama_fasilitas ?? '-' }}
                                @elseif($type === 'pasar-daerah' || $type === 'pasar')
                                    @if(isset($transaksi->items) && $transaksi->items->count() > 0)
                                        {{ $transaksi->items->map(fn($i) => $i->product_name . ' (' . $i->quantity . 'x)')->join(', ') }}
                                    @else
                                        Pesanan Produk Pasar Daerah
                                    @endif
                                @endif
                            </dd>
                        </div>

                        <div class="flex justify-between border-b border-gray-100 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Waktu Pemesanan</dt>
                            <dd class="text-sm text-gray-900">{{ $transaksi->created_at->format('d F Y, H:i') }} WIB</dd>
                        </div>

                        {{-- Rincian Subtotal & Nominal Dibayarkan --}}
                        @php
                            $vSubtotal = isset($subtotal) ? (float) $subtotal : (
                                $type === 'gas'
                                    ? (float) ($transaksi->price * $transaksi->quantity)
                                    : (float) ($transaksi->grand_total ?? $transaksi->total_amount ?? $transaksi->total_harga ?? 0)
                            );
                            $vPaid = isset($paidAmount) ? (float) $paidAmount : (method_exists($transaksi, 'paidAmount') ? $transaksi->paidAmount() : 0.0);
                            $vRemaining = isset($remainingAmount) ? (float) $remainingAmount : max(0.0, round($vSubtotal - $vPaid, 2));
                            $vPayStatus = $paymentStatusLabel ?? (method_exists($transaksi, 'paymentStatusLabel') ? $transaksi->paymentStatusLabel() : ($vSubtotal <= 0 ? 'Gratis' : ($vRemaining <= 0.01 ? 'Lunas' : 'Belum Lunas')));
                        @endphp

                        @if($vSubtotal > 0)
                        <div class="rounded-xl bg-gray-50 border border-gray-200 p-4 space-y-2.5">
                            <div class="flex justify-between text-sm">
                                <dt class="font-medium text-gray-500">Subtotal Pesanan</dt>
                                <dd class="font-bold text-gray-900">Rp {{ number_format($vSubtotal, 0, ',', '.') }}</dd>
                            </div>
                            <div class="flex justify-between text-sm">
                                <dt class="font-medium text-gray-500">Nominal Dibayarkan (Terverifikasi)</dt>
                                <dd class="font-bold {{ $vPaid >= $vSubtotal ? 'text-green-600' : ($vPaid > 0 ? 'text-amber-600' : 'text-gray-700') }}">
                                    Rp {{ number_format($vPaid, 0, ',', '.') }}
                                </dd>
                            </div>
                            @if($vRemaining > 0.01)
                            <div class="flex justify-between text-sm border-t border-gray-200 pt-2">
                                <dt class="font-semibold text-red-600">Sisa Tagihan</dt>
                                <dd class="font-bold text-red-600">Rp {{ number_format($vRemaining, 0, ',', '.') }}</dd>
                            </div>
                            @endif
                            <div class="flex justify-between items-center text-sm border-t border-gray-200 pt-2">
                                <dt class="font-medium text-gray-500">Status Pembayaran</dt>
                                <dd>
                                    @if($vPayStatus === 'Lunas')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">LUNAS</span>
                                    @elseif($vPayStatus === 'Kurang Bayar')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">KURANG BAYAR</span>
                                    @elseif($vPayStatus === 'Dibatalkan')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-200 text-gray-700">DIBATALKAN</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">BELUM LUNAS</span>
                                    @endif
                                </dd>
                            </div>
                        </div>

                        @if(!empty($transaksi->payment_method))
                        <div class="flex justify-between border-b border-gray-100 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Metode Pembayaran</dt>
                            <dd class="text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $transaksi->payment_method) }}</dd>
                        </div>
                        @endif
                        @else
                        <div class="flex justify-between border-b border-gray-100 pb-3">
                            <dt class="text-sm font-medium text-gray-500">Biaya Peminjaman</dt>
                            <dd class="text-sm font-bold text-green-700">Gratis (Acara Sosial)</dd>
                        </div>
                        @endif

                        <div class="flex justify-between bg-blue-50 p-3 rounded-md border border-blue-100">
                            <dt class="text-sm font-medium text-blue-800">Status Terkini</dt>
                            <dd class="text-sm font-bold text-blue-900 uppercase">{{ $transaksi->status }}</dd>
                        </div>
                    </dl>
                </div>
                @endif

                <!-- Footer -->
                <div class="px-8 py-4 bg-gray-50 text-center">
                    <p class="text-xs text-gray-400">SegiDaerah &copy; {{ date('Y') }} - Kecamatan Bengkalis</p>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

