@extends('layouts.app')

@section('content')
<!-- Push the background to fill the screen similar to Shopee's warning page -->
<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header Warning Section -->
    <div class="bg-orange-500 pt-12 pb-8 px-4 text-center">
        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg border-4 border-orange-300">
            <svg class="w-10 h-10 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h1 class="text-3xl font-black text-white mb-2 tracking-tight">Pesanan Diproses!</h1>
        <p class="text-orange-100 font-medium max-w-sm mx-auto">Kami sedang menunggu pembayaran Anda. Batas waktu: <span class="font-bold text-white">{{ \Carbon\Carbon::parse($order->payment_expiry_time)->format('d M Y H:i') }}</span></p>
    </div>

    <!-- Actions -->
    <div class="bg-white px-6 py-6 border-t border-gray-100">
        <div class="flex gap-4 max-w-sm mx-auto">
            <a href="{{ route('beranda') }}" class="flex-1 py-2 px-4 border border-orange-500 text-orange-500 rounded shadow hover:bg-orange-50 transition-colors font-semibold text-center">
                Ke Beranda
            </a>
            <a href="{{ route('user.activity') }}" class="flex-1 py-2 px-4 border border-orange-500 text-white bg-orange-500 rounded shadow hover:bg-orange-600 transition-colors font-semibold text-center">
                Aktivitas Saya
            </a>
        </div>
    </div>
    
    <!-- White space below similar to shopee -->
    <div class="flex-1 bg-white p-6 rounded-t-3xl -mt-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] relative z-10 flex flex-col items-center">
        <!-- Optional: Banner or Illustration can go here -->
        <div class="text-center mt-8">
            <img src="{{ asset('assets/img/illustrations/undraw_pending.svg') }}" alt="Pending" class="w-48 h-auto mx-auto mb-6 opacity-70" onerror="this.style.display='none'">
            <h2 class="text-lg font-bold text-gray-800 mb-2">Selesaikan Pembayaran Anda</h2>
            <p class="text-gray-500 max-w-sm mx-auto">Pesanan Anda telah berhasil dibuat namun belum dibayar. Segera lakukan pembayaran agar pesanan dapat diproses oleh BUMDes.</p>
        </div>
    </div>
</div>
@endsection
