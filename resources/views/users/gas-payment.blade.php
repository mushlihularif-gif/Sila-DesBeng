@extends('layouts.user')

@section('title', 'Instruksi Pembayaran')

@push('styles')
<style>
    @keyframes scan {
        0% { top: 1.5rem; opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { top: calc(100% - 1.5rem); opacity: 0; }
    }
    .animate-scan {
        animation: scan 3s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    }
</style>
@endpush

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16 bg-cover bg-center bg-no-repeat bg-fixed" 
             style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
        
        <!-- White Overlay -->
        <div class="absolute inset-0 bg-white/25 pointer-events-none"></div>

        <div class="max-w-3xl mx-auto px-6 relative z-20">
            
            <!-- Header -->
            <div class="text-center mb-8 animate-fade-in-up">
                @if($order->status == 'pending')
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent mb-2">Selesaikan Pembayaran Anda</h1>
                    <p class="text-gray-700 font-medium">Pesanan <span class="font-bold text-[#115789]">#{{ $order->order_number }}</span> telah dibuat.</p>
                @else
                    <h1 class="text-3xl font-bold text-green-500 mb-2">Pembayaran Berhasil! 🎉</h1>
                    <p class="text-gray-700 font-medium">Pesanan <span class="font-bold text-green-600">#{{ $order->order_number }}</span> telah lunas dan sedang diproses.</p>
                @endif
            </div>

        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 mb-8 transform transition-all hover:shadow-2xl duration-300">
            
            @if($order->status == 'pending')
                @if(strtolower($order->payment_method) !== 'tunai')
                <!-- Countdown & Info Pembayaran -->
                <div class="bg-gray-50/50 p-8 text-center border-b border-gray-100 flex flex-col items-center justify-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(59,130,246,0.05)_0,transparent_100%)]"></div>
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-red-50/80 backdrop-blur-sm text-red-600 rounded-full mb-4 border border-red-100 shadow-sm">
                            <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-xs font-bold tracking-widest uppercase">Selesaikan Pembayaran Dalam</span>
                        </div>
                        <div id="countdown" class="text-5xl font-black text-gray-800 tracking-tight font-mono drop-shadow-sm">
                            ...
                        </div>
                    </div>
                    <input type="hidden" id="expiry-time" value="{{ $order->payment_expiry_time }}">
                </div>
                @else
                <!-- COD Info -->
                <div class="bg-blue-50/50 p-8 text-center border-b border-blue-100 flex flex-col items-center justify-center relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100/80 backdrop-blur-sm text-blue-700 rounded-full mb-4 border border-blue-200 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span class="text-xs font-bold tracking-widest uppercase">Pesanan Diterima BUMDes</span>
                        </div>
                        <h2 class="text-3xl font-black text-gray-800 tracking-tight">Menunggu Pengiriman</h2>
                        <p class="text-gray-600 mt-2">Pesanan Anda akan segera diantarkan oleh petugas BUMDes.</p>
                    </div>
                </div>
                @endif

                <!-- Bagian Pembayaran yang disembunyikan jika sudah lunas -->
                <div class="p-8 pb-0">
                    <div class="text-center mb-8">
                        <p class="text-gray-500 text-sm font-semibold uppercase tracking-widest mb-2">TOTAL TAGIHAN</p>
                        <h2 class="text-4xl font-black text-gray-800">Rp {{ number_format($order->price * $order->quantity, 0, ',', '.') }}</h2>
                    </div>

                    @if(strtolower($order->payment_method) === 'tunai')
                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6 text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 text-blue-600 rounded-full mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-xl text-gray-800 mb-2">Pembayaran Tunai</h3>
                        <p class="text-gray-600">Silakan lakukan pembayaran langsung ke admin/petugas saat menerima/mengambil gas.</p>
                    </div>
                    @elseif(in_array($order->payment_channel, ['bank_transfer_bca', 'bank_transfer_bri', 'bank_transfer_mandiri', 'bank_transfer_bni']))
                    @php
                        $bankLogos = [
                            'bank_transfer_bca' => ['name' => 'BCA', 'logo' => 'Admin/img/banks/bca.png', 'color' => 'text-blue-600'],
                            'bank_transfer_bri' => ['name' => 'BRI', 'logo' => 'Admin/img/banks/bri.png', 'color' => 'text-orange-600'],
                            'bank_transfer_mandiri' => ['name' => 'MANDIRI', 'logo' => 'Admin/img/banks/mandiri.png', 'color' => 'text-yellow-600'],
                            'bank_transfer_bni' => ['name' => 'BNI', 'logo' => 'Admin/img/banks/bni.png', 'color' => 'text-orange-500'],
                            'qris' => ['name' => 'QRIS', 'logo' => 'Admin/img/banks/qris.svg', 'color' => 'text-red-500'],
                        ];
                        $bank = $bankLogos[$order->payment_channel] ?? ['name' => strtoupper(str_replace('bank_transfer_', '', $order->payment_channel)), 'logo' => null, 'color' => 'text-gray-800'];
                    @endphp
                    
                    <div class="flex justify-between items-center mb-6 px-2">
                        <span class="text-gray-600 font-medium">Bank Tujuan</span>
                        <div class="flex items-center justify-center gap-3">
                            @if($bank['logo'])
                                <img src="{{ asset($bank['logo']) }}" alt="{{ $bank['name'] }}" class="{{ $order->payment_channel === 'bank_transfer_bri' ? 'h-12' : 'h-6' }} object-contain">
                            @else
                                <span class="font-extrabold text-lg tracking-widest {{ $bank['color'] }}">{{ $bank['name'] }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200 mb-8 relative group hover:border-blue-300 transition-colors cursor-pointer" onclick="copyVA()">
                        <p class="text-center text-sm font-semibold text-gray-500 mb-3">Nomor Virtual Account</p>
                        <div class="flex items-center justify-center gap-4">
                            <span id="va-number" class="text-3xl font-black text-gray-800 tracking-wider font-mono">{{ $order->payment_va_number }}</span>
                            <button class="p-2 bg-white rounded-lg shadow-sm border border-gray-200 text-blue-600 hover:bg-blue-50 transition-colors group-hover:scale-110 transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="mb-8 px-2">
                        <h4 class="font-bold text-gray-800 mb-3 text-sm">Cara Pembayaran:</h4>
                        <ol class="list-decimal list-inside text-sm text-gray-600 space-y-2 leading-relaxed">
                            <li>Buka aplikasi Mobile Banking atau pergi ke ATM terdekat.</li>
                            <li>Pilih menu Transfer > Virtual Account.</li>
                            <li>Masukkan Nomor Virtual Account di atas.</li>
                            <li>Pastikan nominal dan nama sesuai dengan pesanan Anda.</li>
                            <li>Selesaikan pembayaran.</li>
                        </ol>
                    </div>

                    @elseif(in_array($order->payment_channel, ['qris', 'gopay']))
                    <div class="text-center mb-4 flex flex-col items-center">
                        <div class="inline-flex items-center justify-center mb-8">
                            <img src="{{ asset('Admin/img/banks/qris.svg') }}" class="h-10" alt="QRIS">
                        </div>
                        
                        <div class="relative p-8 bg-white rounded-3xl shadow-[0_0_40px_rgba(0,0,0,0.04)] border border-gray-100 mb-8 max-w-xs w-full mx-auto group hover:shadow-[0_0_50px_rgba(59,130,246,0.1)] transition-all duration-500">
                            <!-- Corner brackets design -->
                            <div class="absolute top-0 left-0 w-10 h-10 border-t-4 border-l-4 border-blue-500 rounded-tl-2xl transition-all duration-300 group-hover:w-14 group-hover:h-14 group-hover:border-blue-600"></div>
                            <div class="absolute top-0 right-0 w-10 h-10 border-t-4 border-r-4 border-blue-500 rounded-tr-2xl transition-all duration-300 group-hover:w-14 group-hover:h-14 group-hover:border-blue-600"></div>
                            <div class="absolute bottom-0 left-0 w-10 h-10 border-b-4 border-l-4 border-blue-500 rounded-bl-2xl transition-all duration-300 group-hover:w-14 group-hover:h-14 group-hover:border-blue-600"></div>
                            <div class="absolute bottom-0 right-0 w-10 h-10 border-b-4 border-r-4 border-blue-500 rounded-br-2xl transition-all duration-300 group-hover:w-14 group-hover:h-14 group-hover:border-blue-600"></div>
                            
                            <!-- Scanning line animation -->
                            <div class="absolute left-6 right-6 h-0.5 bg-blue-500 shadow-[0_0_12px_rgba(59,130,246,0.9)] animate-scan z-20 pointer-events-none rounded-full"></div>
                            
                            <!-- The QR Code -->
                            <div class="relative z-10 bg-white p-2 rounded-xl">
                                @if($order->payment_qr_url)
                                    @if(str_starts_with($order->payment_qr_url, 'http'))
                                        <img src="{{ $order->payment_qr_url }}" alt="QR Code" class="w-full h-auto aspect-square object-contain" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMDAgMTAwIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2Y4ZmFmYyIvPjxwYXRoIGQ9Ik0yMCAyMGg2MHY2MEgyMHoiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2NiZDVlMSIgc3Ryb2tlLXdpZHRoPSI0IiBzdHJva2UtZGFzaGFycmF5PSI4IDQiLz48dGV4dCB4PSI1MCIgeT0iNTEiIGZvbnQtZmFtaWx5PSJzYW5zLXNlcmlmIiBmb250LXNpemU9IjgiIGZpbGw9IiM2NDc0OGIiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGRvbWluYW50LWJhc2VsaW5lPSJtaWRkbGUiPklNQUdFIEVSUk9SPC90ZXh0Pjwvc3ZnPg==';">
                                    @else
                                        <!-- Dummy QR Code SVG -->
                                        <svg class="w-full h-auto aspect-square text-gray-800" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M3 3h8v8H3zm2 2v4h4V5zm8-2h8v8h-8zm2 2v4h4V5zM3 13h8v8H3zm2 2v4h4v-4zm13-2h-2v2h2zm-2 2h-2v2h2zm2 2h-2v2h2zm-2 2h-2v2h2zm-2-6h-2v2h2zm-2 2h-2v2h2zm-2 2h-2v2h2zm0-6h2v2h-2zm-2 2h2v2h-2zm0 2h2v2h-2zm4-4h2v2h-2zm0 4h2v2h-2zm-6-2h2v2h-2z"/>
                                        </svg>
                                    @endif
                                @else
                                <div class="w-full aspect-square bg-gray-50 rounded-xl flex items-center justify-center text-gray-400">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-500 max-w-xs mx-auto leading-relaxed">Buka aplikasi E-Wallet Anda (GoPay, OVO, Dana, ShopeePay, dll) dan scan kode QRIS di atas untuk membayar.</p>
                    </div>
                    @endif
                </div>
                @else
                <!-- Success State Content -->
                <div class="p-12 text-center">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 text-green-500 rounded-full mb-6">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h2 class="text-3xl font-black text-gray-800 mb-4">Transaksi Lunas</h2>
                    <p class="text-gray-500 mb-8">Terima kasih, pembayaran Anda telah berhasil diverifikasi oleh sistem. Anda dapat melihat dan mengunduh struk resmi di bawah ini.</p>
                </div>
                @endif
                
                <!-- Rincian Pesanan -->
                <div class="px-8 pb-8">
                    <div class="bg-gray-50 rounded-2xl p-6">
                        <h4 class="font-bold text-gray-800 mb-4">Rincian Pesanan</h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Nama Gas</span>
                                <span class="font-semibold text-gray-900">{{ $order->item_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Jumlah</span>
                                <span class="font-semibold text-gray-900">{{ $order->quantity }} Tabung</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Harga Satuan</span>
                                <span class="font-semibold text-gray-900">Rp {{ number_format($order->price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between pt-3 border-t border-gray-200">
                                <span class="text-gray-800 font-bold">Total</span>
                                <span class="font-bold text-blue-600 text-base">Rp {{ number_format($order->price * $order->quantity, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-8 py-6 flex flex-col sm:flex-row gap-4 justify-center sm:justify-between items-center flex-wrap w-full">
                    <a href="{{ route('user.activity') }}" class="w-full sm:w-auto text-center px-6 py-3 bg-white border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors">
                        Lihat Riwayat Pesanan
                    </a>
                    
                    @if($order->status == 'pending')
                        <!-- Simulation Button -->
                        <form action="{{ route('user.gas.payment.simulate', $order->id) }}" method="POST" class="w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-xl shadow-lg shadow-green-500/30 transition-all active:scale-95 flex items-center justify-center">
                                <i class="fas fa-check-circle mr-2"></i>Simulasikan Pembayaran Lunas (Dev)
                            </button>
                        </form>
                    @endif

                    @if($order->receipt_path)
                    <a href="{{ route('receipt.gas.view', $order->id) }}" target="_blank" class="w-full sm:w-auto text-center px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Lihat Struk Resmi
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



    <script>
        function initGasPaymentPage() {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    confirmButtonColor: '#10b981',
                    timer: 3000
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#ef4444',
                });
            @endif

            @if(session('info'))
                Swal.fire({
                    icon: 'info',
                    title: 'Informasi',
                    text: '{{ session('info') }}',
                    confirmButtonColor: '#3b82f6',
                });
            @endif

            // Copy VA Function
            window.copyVA = function() {
                const vaNumber = document.getElementById('va-number').innerText;
                navigator.clipboard.writeText(vaNumber).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Disalin!',
                        text: 'Nomor Virtual Account telah disalin ke clipboard.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }

            @if($order->status == 'pending')
            // Countdown Timer Logic
            const countDownDate = new Date("{{ \Carbon\Carbon::parse($order->payment_expiry_time)->format('Y-m-d H:i:s') }}").getTime();
            let isCancelled = false;

            if (window.gasPaymentTimer) {
                clearInterval(window.gasPaymentTimer);
            }

            window.gasPaymentTimer = setInterval(function() {
                const timerElement = document.getElementById("countdown");
                if (!timerElement) {
                    clearInterval(window.gasPaymentTimer);
                    return;
                }

                // Get today's date and time
                const now = new Date().getTime();
                
                // Find the distance between now and the count down date
                const distance = countDownDate - now;

                if (distance < 0) {
                    clearInterval(window.gasPaymentTimer);
                    isCancelled = true;
                    document.getElementById("countdown").innerHTML = "00:00:00";
                    
                    // Trigger auto cancel
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Waktu pembayaran habis, membatalkan pesanan...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading()
                        }
                    });

                    fetch("{{ route('user.gas.payment.cancel', $order->id) }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Waktu Habis',
                            text: 'Pesanan Anda telah dibatalkan oleh sistem karena batas waktu pembayaran habis.',
                            confirmButtonText: 'Kembali ke Aktivitas',
                            allowOutsideClick: false
                        }).then((result) => {
                            window.location.href = "{{ route('user.activity') }}";
                        });
                    })
                    .catch(error => {
                        window.location.href = "{{ route('user.activity') }}";
                    });
                    
                    return;
                }

                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById("countdown").innerHTML = 
                    (hours < 10 ? "0" + hours : hours) + ":" + 
                    (minutes < 10 ? "0" + minutes : minutes) + ":" + 
                    (seconds < 10 ? "0" + seconds : seconds);
            }, 1000);
            @endif
        }

        // Run on both normal load and turbo load to cover all cases
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initGasPaymentPage);
        } else {
            initGasPaymentPage();
        }
        document.addEventListener('turbo:load', initGasPaymentPage);
    </script>
@endpush
