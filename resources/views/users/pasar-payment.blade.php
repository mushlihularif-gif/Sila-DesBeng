@extends('layouts.app')

@section('title', 'Pembayaran Pasar Daerah - SilaDesBeng')

@section('content')
<div class="min-h-screen bg-gray-50 pt-24 pb-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="bg-green-50 text-green-600 p-4 rounded-xl mb-6 flex items-center shadow-sm">
                <i class="fas fa-check-circle mr-3"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-600 to-green-700 p-6 text-white text-center">
                <p class="text-green-100 mb-1">Total Pembayaran</p>
                <h1 class="text-4xl font-extrabold tracking-tight mb-2">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</h1>
                <p class="text-sm bg-white/20 inline-block px-3 py-1 rounded-full backdrop-blur-sm">
                    Order ID: <strong>{{ $order->order_number }}</strong>
                </p>
            </div>

            <div class="p-8">
                <!-- Status & Instruction -->
                @if($order->status === 'pending')
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 text-amber-500 mb-4">
                            <i class="fas fa-clock text-3xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Menunggu Pembayaran</h2>
                        <p class="text-gray-500">Selesaikan pembayaran Anda sebelum <strong class="text-gray-900">{{ $order->payment_expiry_time ? $order->payment_expiry_time->format('d M Y, H:i') : '-' }}</strong></p>
                    </div>

                    <!-- Payment Details -->
                    <div class="bg-gray-50 rounded-xl p-6 mb-8 border border-gray-100">
                        <h3 class="font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Instruksi Pembayaran</h3>
                        
                        @if($order->payment_method === 'Tunai' || $order->payment_method === 'tunai')
                            <div class="flex items-start">
                                <i class="fas fa-money-bill-wave text-green-600 mt-1 mr-3 text-xl"></i>
                                <div>
                                    <p class="font-bold text-gray-900">Pembayaran Tunai (COD / Di Tempat)</p>
                                    <p class="text-sm text-gray-600 mt-1">Silakan siapkan uang pas sebesar <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong> saat menerima pesanan atau saat mengambil pesanan di lokasi penjual.</p>
                                </div>
                            </div>
                        @elseif($order->payment_method === 'Transfer Manual')
                            <div class="flex items-start">
                                <i class="fas fa-exchange-alt text-blue-600 mt-1 mr-3 text-xl"></i>
                                <div>
                                    <p class="font-bold text-gray-900">Transfer Manual ke Pengelola Layanan</p>
                                    <p class="text-sm text-gray-600 mt-1">Anda telah mengunggah bukti transfer. Admin Pusat Layanan akan memverifikasi pembayaran Anda dalam waktu 1x24 jam kerja.</p>
                                </div>
                            </div>
                        @else
                            <!-- Midtrans VA / QRIS -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-1">Metode</p>
                                <p class="font-bold text-gray-900 uppercase">{{ str_replace('_', ' ', $order->payment_method) }}</p>
                            </div>

                            @if($order->payment_va_number)
                                <div class="mb-4">
                                    <p class="text-sm text-gray-500 mb-1">Nomor Virtual Account</p>
                                    <div class="flex items-center justify-between bg-white border border-gray-200 rounded-lg p-3">
                                        <span class="font-mono text-xl tracking-wider text-gray-900 font-bold" id="vaNumber">{{ $order->payment_va_number }}</span>
                                        <button onclick="navigator.clipboard.writeText('{{ $order->payment_va_number }}'); Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Berhasil disalin!', showConfirmButton: false, timer: 1500})" class="text-green-600 hover:text-green-700 text-sm font-bold flex items-center">
                                            <i class="far fa-copy mr-1"></i> Salin
                                        </button>
                                    </div>
                                </div>
                            @elseif($order->payment_qr_url)
                                <div class="mb-4 text-center">
                                    <p class="text-sm text-gray-500 mb-3">Scan QRIS Berikut</p>
                                    <div class="inline-block p-4 bg-white border border-gray-200 rounded-xl">
                                        @if($order->payment_qr_url === 'DUMMY_QR_CODE')
                                            <div class="w-48 h-48 bg-gray-200 flex items-center justify-center text-gray-500 font-mono text-xs text-center border-4 border-dashed border-gray-300">
                                                [SIMULASI QRIS]<br>Dummy Image
                                            </div>
                                        @else
                                            <img src="{{ $order->payment_qr_url }}" alt="QRIS" class="w-48 h-48">
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Sandbox Simulator Actions -->
                    @if(config('services.midtrans.is_production') == false && !in_array(strtolower($order->payment_method), ['tunai', 'transfer manual']))
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8 text-center">
                            <h4 class="font-bold text-blue-900 mb-2"><i class="fas fa-flask mr-2"></i> Mode Sandbox (Testing)</h4>
                            <p class="text-sm text-blue-700 mb-4">Gunakan tombol di bawah ini untuk mensimulasikan pembayaran berhasil pada environment testing.</p>
                            <form action="{{ route('pasar.payment.simulate', $order->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition shadow-sm">
                                    Simulasikan Pembayaran Berhasil
                                </button>
                            </form>
                        </div>
                    @endif

                @else
                    <!-- Success State -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 text-green-500 mb-4">
                            <i class="fas fa-check text-3xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Pembayaran Berhasil!</h2>
                        <p class="text-gray-500 mb-6">Pesanan Anda saat ini sedang diproses oleh penjual.</p>
                        <a href="{{ route('user.activity') }}" class="inline-flex bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition">
                            Lihat Status Pesanan
                        </a>
                    </div>
                @endif
                
                <div class="border-t border-gray-100 pt-6 mt-6">
                    <h3 class="font-bold text-gray-900 mb-4">Detail Pesanan</h3>
                    <div class="space-y-3 text-sm">
                        @foreach($order->items as $item)
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                                <p class="text-gray-500">{{ $item->quantity }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</p>
                            </div>
                            <span class="font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>
            
            <div class="bg-gray-50 p-6 border-t border-gray-100 flex justify-between items-center">
                <a href="{{ route('pasar.index') }}" class="text-gray-600 hover:text-gray-900 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Katalog
                </a>
                <a href="{{ route('user.activity') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-bold py-2 px-4 rounded-lg transition">
                    Cek Aktivitas
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
