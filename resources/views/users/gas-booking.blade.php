@extends('layouts.user')

@php
    // Logika styling kartu (sama seperti penyewaan alat)
    $cardStyle = 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);'; // default blue
    $amountColor = 'text-yellow-300'; // Default amount color
    $cardTextColor = 'text-white';
    $buttonClass = 'bg-white/20 backdrop-blur-sm border border-white/40 text-white hover:bg-white/30';
    $borderClass = 'border-white/30';
    
    if ($setting && $setting->card_gradient_style) {
        $gradients = [
            'white' => 'linear-gradient(135deg, #ffffff 0%, #f0f0f0 100%)',
            'silver' => 'linear-gradient(135deg, #e0e0e0 0%, #c0c0c0 100%)',
            'gold' => 'linear-gradient(135deg, #ffd700 0%, #fdb931 100%)',
            'transparent' => 'rgba(59, 130, 246, 0.3)',
            'blue' => 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)',
            'green' => 'linear-gradient(135deg, #00a884 0%, #005c4b 100%)',
            'purple' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'dark' => 'linear-gradient(135deg, #232526 0%, #414345 100%)',
            'orange' => 'linear-gradient(135deg, #f7971e 0%, #ffd200 100%)',
            'red' => 'linear-gradient(135deg, #eb3349 0%, #f45c43 100%)',
        ];
        
        $style = $setting->card_gradient_style;
        $cardStyle = 'background: ' . ($gradients[$style] ?? $gradients['blue']) . ';';
        
        // Tentukan warna berdasarkan latar belakang (SAMA SEPERTI PENYEWAAN ALAT)
        if (in_array($style, ['white', 'silver', 'gold', 'transparent'])) {
            $amountColor = 'text-red-600';
            $cardTextColor = 'text-gray-800';
            $buttonClass = 'bg-gray-200 hover:bg-gray-300 text-gray-800 border border-gray-400';
            $borderClass = 'border-gray-300';
        } elseif ($style == 'red') {
            $amountColor = 'text-white';
            $cardTextColor = 'text-white';
        } else {
            $amountColor = 'text-yellow-300'; // Blue, Green, Purple, Dark
            $cardTextColor = 'text-white';
        }
    }
    
    // Dapatkan deskripsi pembayaran tunai
    $cashDescription = $setting->cash_payment_description ?? 'Bendahara Layanan';

    // Bank Logo Mapping
    $bankLogos = [
        'Bank Syariah Indonesia' => 'Admin/img/banks/bsi.png',
        'BSI' => 'Admin/img/banks/bsi.png',
        'BRI' => 'Admin/img/banks/bri.png',
        'BRIMO' => 'Admin/img/banks/bri.png',
        'Mandiri' => 'Admin/img/banks/mandiri.png',
        'BNI' => 'Admin/img/banks/bni.png',
        'BCA' => 'Admin/img/banks/bca.png',
        'Bank Riau Kepri Syariah' => 'Admin/img/banks/brk.png',
        'BRK' => 'Admin/img/banks/brk.png',
        'Bank Mega' => 'Admin/img/banks/mega.png',
    ];
    $bankName = strtoupper($setting->bank_name ?? '');
    $bankLogoPath = 'Admin/img/banks/bsi.png';
    foreach ($bankLogos as $key => $path) {
        if (str_contains($bankName, strtoupper($key))) {
            $bankLogoPath = $path;
            break;
        }
    }

    // E-Wallet Logo Mapping.
    // Berkasnya ada di assets/img/payment_logos/ — folder yang sama yang dibaca
    // halaman Pengaturan Pembayaran Wilayah saat kepala desa memilih dompetnya.
    $ewalletLogos = [
        'DANA' => 'assets/img/payment_logos/dana.png',
        'OVO' => 'assets/img/payment_logos/ovo.png',
        'GOPAY' => 'assets/img/payment_logos/gopay.png',
        'SHOPEEPAY' => 'assets/img/payment_logos/shopeepay.png',
    ];
    $ewalletName = strtoupper($setting->ewallet_name ?? '');
    $ewalletLogoPath = null;
    foreach ($ewalletLogos as $key => $path) {
        if (str_contains($ewalletName, strtoupper($key))) {
            $ewalletLogoPath = $path;
            break;
        }
    }

    // Wilayah boleh mengetik nama dompet di luar keempat pilihan itu. Tanpa
    // pemeriksaan ini tombolnya menampilkan gambar rusak, dan warga justru
    // curiga pada halaman pembayarannya sendiri.
    $adaLogoEwallet = $ewalletLogoPath !== null && is_file(public_path($ewalletLogoPath));

    // Tentukan metode pembayaran manual yang benar-benar diaktifkan wilayah
    $methods = is_array($setting?->payment_methods) ? $setting->payment_methods : [];
    $hasTransfer = in_array('transfer', $methods, true);
    $hasTunai = in_array('tunai', $methods, true);

    // E-Wallet menuruti sakelarnya di Pengaturan Pembayaran Wilayah, bukan
    // sekadar "kolomnya terisi". Wilayah yang pernah mengisi nomornya lalu
    // mematikan sakelarnya tidak boleh tetap menerima kiriman ke situ.
    $hasEwallet = in_array('ewallet', $methods, true)
        && !empty($setting->ewallet_name) && !empty($setting->ewallet_number);

    $hasAnyPayment = $hasTransfer || $hasTunai || $hasEwallet;

    // Tentukan metode aktif default mengikuti urutan yang tersedia
    $defaultMethod = $hasTransfer ? 'transfer' : ($hasEwallet ? 'ewallet' : ($hasTunai ? 'tunai' : ''));

@endphp

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16 bg-cover bg-center bg-no-repeat bg-fixed" 
             style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
        
        <!-- White Overlay -->
        <div class="absolute inset-0 bg-white/25 pointer-events-none"></div>

        <div class="max-w-5xl mx-auto px-6 relative z-20">
            <!-- Header (Konsisten dengan Metode Antar Jemput Alat Sewa) -->
            <div class="text-center mb-10 mt-8">
                <h1 class="text-3xl md:text-4xl font-bold">
                    <span class="text-gray-800">Metode </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Antar Jemput Pembelian Gas</span>
                </h1>
            </div>

            <form id="gas-booking-form" action="#" method="POST" enctype="multipart/form-data" data-turbo="false">
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                <input type="hidden" name="gas_id" value="{{ $item->id }}">
                <input type="hidden" name="quantity" id="hidden-quantity" value="{{ $quantity }}">

                @php
                    $antarActive = !isset($setting->payment_info['gas_delivery_antar_active']) || $setting->payment_info['gas_delivery_antar_active'];
                    $jemputActive = !isset($setting->payment_info['gas_delivery_jemput_active']) || $setting->payment_info['gas_delivery_jemput_active'];
                    $defaultDelivery = $antarActive ? 'antar' : 'jemput';
                @endphp
                <input type="hidden" name="delivery_method" id="delivery-method-input" value="{{ $defaultDelivery }}">

                <!-- Pilihan Metode Pengiriman (Rata Tengah & Rapi Seperti Sewa Alat) -->
                <div class="segi-delivery-choices flex justify-center gap-4 sm:gap-6 mb-6 sm:mb-10">
                    @if($antarActive)
                    <!-- Antar Card -->
                    <div class="delivery-method-card {{ $defaultDelivery == 'antar' ? 'active' : '' }} cursor-pointer bg-white rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition-all duration-300 w-44 sm:w-48 text-center border-4 border-transparent" data-method="antar">
                        <div class="mb-4 flex justify-center">
                            <img src="{{ asset('Admin/img/elements/antar.png') }}" alt="Diantar" class="w-20 h-20 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800">Diantar</p>
                    </div>
                    @endif

                    @if($jemputActive)
                    <!-- Jemput Card -->
                    <div class="delivery-method-card {{ $defaultDelivery == 'jemput' ? 'active' : '' }} cursor-pointer bg-white rounded-2xl p-6 sm:p-8 shadow-lg hover:shadow-xl transition-all duration-300 w-44 sm:w-48 text-center border-4 border-transparent" data-method="jemput">
                        <div class="mb-4 flex justify-center">
                            <img src="{{ asset('Admin/img/elements/jemput.png') }}" alt="Ambil Sendiri" class="w-20 h-20 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800">Ambil Sendiri</p>
                    </div>
                    @endif
                </div>

                <!-- Info Note -->
                <div id="antar-note" class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg {{ $defaultDelivery == 'antar' ? '' : 'hidden' }}">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <span class="font-bold">Info:</span> Pesanan Gas akan diantar oleh pihak pengelola ke alamat Anda.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="jemput-note" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg {{ $defaultDelivery == 'jemput' ? '' : 'hidden' }}">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <span class="font-bold">NB:</span> Pengambilan Gas dilakukan secara mandiri oleh Anda di lokasi layanan.
                            </p>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="buyer_name" id="buyer-name" value="{{ Auth::user()->name }}">
                <input type="hidden" id="gas-pickup-address-default" value="{{ \App\Support\ServiceLocation::pickupAddressText($item) }}">

                <!-- Kartu Alamat Pengantaran (Hanya tampil jika metode Diantar) -->
                <div id="gas-antar-address-card" class="bg-white rounded-2xl shadow-lg p-6 mb-6 {{ $defaultDelivery == 'antar' ? '' : 'hidden' }}">
                    <div class="flex items-center gap-3 mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <h3 class="text-lg font-bold text-gray-800">Alamat Pengantaran</h3>
                    </div>
                    
                    <div class="space-y-4">
                        @include('partials.pilih-alamat', [
                            'alamatTersimpan' => $alamatTersimpan ?? collect(),
                            'idNama'   => 'buyer-name',
                            'idAlamat' => 'buyer-address',
                        ])
                        <textarea name="buyer_address" 
                                  id="buyer-address"
                                  rows="3" 
                                  placeholder="Alamat Lengkap" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  {{ $defaultDelivery == 'antar' ? 'required' : '' }}></textarea>
                    </div>
                </div>

                <!-- Kartu Lokasi Pengambilan (Hanya tampil jika metode Ambil Sendiri) -->
                <div id="gas-jemput-location-card" class="bg-white rounded-2xl shadow-lg p-6 mb-6 {{ $defaultDelivery == 'jemput' ? '' : 'hidden' }}">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="bx bx-store-alt text-2xl text-blue-600"></i>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Lokasi Pengambilan Gas (Penyedia Layanan)</h3>
                            <p class="text-xs text-gray-500">Pengambilan tabung gas dilakukan langsung di pangkalan / kantor penyedia layanan desa berikut.</p>
                        </div>
                    </div>
                    <x-service-location :item="$item" />
                    @if($item->nama_pengecer || $item->kontak_pengecer)
                    <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-600 flex flex-wrap gap-x-4 gap-y-1">
                        @if($item->nama_pengecer)<span><strong>Pengecer:</strong> {{ $item->nama_pengecer }}</span>@endif
                        @if($item->kontak_pengecer)<span><strong>Kontak:</strong> {{ $item->kontak_pengecer }}</span>@endif
                    </div>
                    @endif
                </div>

                <!-- Kartu Produk -->
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <div class="segi-product-summary flex flex-col sm:flex-row gap-6">
                        <!-- Product Image -->
                        <img src="{{ asset('storage/' . $item->foto) }}" 
                             alt="{{ $item->jenis_gas }}" 
                             class="w-32 h-32 object-contain p-2 drop-shadow-md rounded-lg flex-shrink-0">
                        
                        <div class="flex-1">
                            <!-- Product Name -->
                            <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $item->jenis_gas }}</h3>
                            
                            <!-- Price -->
                            <div class="mb-3">
                                <p class="text-sm text-gray-600">Harga Satuan</p>
                                <p class="text-lg font-bold text-gray-800">Rp. {{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                            </div>
                            
                            <!-- Quantity Selector -->
                            <div class="flex items-center gap-3 mb-3">
                                <label class="text-sm text-gray-600 font-medium">Jumlah</label>
                                <div class="flex items-center gap-2 border border-gray-300 rounded-lg px-3 py-1">
                                    <button type="button" id="decrease-qty" class="text-gray-600 hover:text-gray-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <input type="number" 
                                           id="quantity-display" 
                                           value="{{ $quantity }}" 
                                           min="1" 
                                           max="{{ $item->stok }}"
                                           class="w-12 text-center border-0 focus:outline-none focus:ring-0">
                                    <button type="button" id="increase-qty" class="text-gray-600 hover:text-gray-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Side: Category and Subtotal -->
                        <div class="text-right">
                            <p class="text-sm text-gray-600 mb-1">{{ str_replace('&', 'dan', $item->kategori) }}</p>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-1">Subtotal</p>
                                <p class="text-xl font-bold text-gray-800" id="subtotal">Rp. {{ number_format($item->harga_satuan * $quantity, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Method.
                     Dibungkus kartu putih seperti kartu lain di halaman ini.
                     Sebelumnya tombol-tombolnya mengambang langsung di atas latar
                     halaman yang bergradasi, jadi warnanya bercampur dengan latar
                     saat digulir dan bagian ini tidak terbaca sebagai satu kesatuan. --}}
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Metode Pembayaran</h3>

                    {{-- Dulu seluruh blok ini dibungkus @if($hasTransfer && $hasTunai),
                         sehingga wilayah yang hanya menyalakan salah satunya tidak
                         memunculkan tombol pembayaran sama sekali — pembeli melihat
                         judul "Metode Pembayaran" dengan ruang kosong di bawahnya. --}}
                    @if($hasAnyPayment)

                    {{-- Bayar langsung ke desa (Transfer Bank / E-Wallet / Tunai sesuai sakelar wilayah) --}}
                    <div class="mb-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Bayar Langsung ke Pengelola</span>
                            <span class="flex-1 h-px bg-gray-200"></span>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @if($hasTransfer)
                        <button type="button"
                                onclick="setPaymentMethod('transfer')"
                                id="btn-transfer"
                                class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 {{ $defaultMethod === 'transfer' ? 'active ring-2 ring-blue-500 bg-blue-50 shadow-md transform scale-105' : 'bg-white text-gray-600 shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md' }}">
                            <div class="flex flex-col items-center justify-center gap-2 text-center relative z-10">
                                <div class="w-10 h-10 rounded-full {{ $defaultMethod === 'transfer' ? 'bg-blue-500 text-white' : 'bg-indigo-100 text-indigo-600 group-hover:bg-blue-500 group-hover:text-white' }} flex items-center justify-center transition-colors shadow-inner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                </div>
                                <span class="text-[11px] uppercase tracking-wider {{ $defaultMethod === 'transfer' ? 'text-blue-700' : 'text-gray-600' }}">Transfer Bank</span>
                            </div>
                        </button>
                        @endif
                        @if($hasTunai)
                        <!-- Tunai -->
                        <button type="button" 
                                onclick="setPaymentMethod('tunai')"
                                id="btn-tunai"
                                class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 {{ $defaultMethod === 'tunai' ? 'active ring-2 ring-blue-500 bg-blue-50 shadow-md transform scale-105' : 'bg-white text-gray-600 shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md' }}">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="flex flex-col items-center justify-center gap-2 text-center relative z-10">
                                <div class="w-10 h-10 rounded-full {{ $defaultMethod === 'tunai' ? 'bg-blue-500 text-white' : 'bg-green-100 text-green-600 group-hover:bg-blue-500 group-hover:text-white' }} flex items-center justify-center transition-colors shadow-inner">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <span class="text-[11px] uppercase tracking-wider {{ $defaultMethod === 'tunai' ? 'text-blue-700' : 'text-gray-600' }}">Bayar Tunai</span>
                            </div>
                        </button>
                        @endif
                        @if($hasEwallet)
                        <!-- E-Wallet milik wilayah -->
                        <button type="button"
                                onclick="setPaymentMethod('ewallet')"
                                id="btn-ewallet"
                                class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 {{ $defaultMethod === 'ewallet' ? 'active ring-2 ring-blue-500 bg-blue-50 shadow-md transform scale-105' : 'bg-white text-gray-600 shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md' }}">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="flex flex-col items-center justify-center gap-2 text-center relative z-10">
                                <div class="w-10 h-10 rounded-full {{ $adaLogoEwallet ? 'bg-white border border-gray-100' : ($defaultMethod === 'ewallet' ? 'bg-blue-500 text-white' : 'bg-sky-100 text-sky-600 group-hover:bg-blue-500 group-hover:text-white') }} flex items-center justify-center overflow-hidden shadow-inner transition-colors">
                                    @if($adaLogoEwallet)
                                        <img src="{{ asset($ewalletLogoPath) }}" alt="{{ $setting->ewallet_name }}" class="h-6 w-6 object-contain">
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a2 2 0 00-2-2h-3a2 2 0 100 4h3a2 2 0 002-2zm0 0V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2v-4z"/>
                                        </svg>
                                    @endif
                                </div>
                                <span class="text-[11px] uppercase tracking-wider {{ $defaultMethod === 'ewallet' ? 'text-blue-700' : 'text-gray-600' }}">{{ $setting->ewallet_name }}</span>
                            </div>
                        </button>
                        @endif
                        </div>
                    </div>

                    <!-- Transfer Manual ke Rekening Wilayah -->
                    {{-- Hanya dirender kalau transfer memang tersedia. Menyembunyikannya
                         dengan CSS saja tidak cukup: nomor rekening tetap terbaca di
                         sumber halaman untuk wilayah yang sengaja mematikan transfer. --}}
                    @if($hasTransfer)
                    <div id="transfer-payment" class="payment-content {{ $defaultMethod === 'transfer' ? '' : 'hidden' }}">
                        <div class="bg-white border-2 border-blue-50 rounded-2xl shadow-sm p-6 sm:p-8 mb-6">
                            <h4 class="font-bold text-gray-800 mb-4">Transfer ke Rekening Berikut</h4>

                            <div class="flex items-center gap-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-5 mb-4">
                                @if($bankLogoPath)
                                    <img src="{{ asset($bankLogoPath) }}" alt="{{ $setting->bank_name }}" class="h-8 object-contain">
                                @endif
                                <div class="min-w-0">
                                    <div class="text-xs uppercase tracking-wider text-gray-500">{{ $setting->bank_name ?: 'Bank' }}</div>
                                    <div class="text-xl font-black tracking-wide text-gray-900 select-all break-all">
                                        {{ $setting->bank_account_number ?: 'Belum diatur' }}
                                    </div>
                                    <div class="text-sm text-gray-600">a.n. {{ $setting->bank_account_holder ?: '-' }}</div>
                                </div>
                            </div>

                            {{-- Warga sering rugi biaya admin antarbank tanpa sadar. --}}
                            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-xl mb-5">
                                <p class="text-sm text-amber-800">
                                    <strong>Gunakan bank yang sama ({{ $setting->bank_name ?: 'bank tujuan' }})</strong>
                                    agar tidak dikenai biaya admin antarbank. Transfer dari bank lain tetap diterima,
                                    tetapi biayanya ditanggung Anda.
                                </p>
                            </div>

                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Unggah Bukti Transfer <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf"
                                   class="block w-full text-sm text-gray-600 border-2 border-dashed border-blue-200 rounded-xl p-3
                                          file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                          file:bg-blue-500 file:text-white file:font-semibold hover:file:bg-blue-600">
                            <p class="text-xs text-gray-500 mt-2">
                                Format JPG, PNG, atau PDF. Maksimal 5 MB. Bukti Anda akan diperiksa petugas
                                sebelum pesanan diproses.
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- E-Wallet Manual ke Nomor Wilayah -->
                    {{-- Wilayah sudah lama bisa mengisi nomor DANA/OVO-nya di
                         Pengaturan Pembayaran Wilayah, tetapi nomornya tidak pernah
                         dirender di mana pun — jadi warga tidak punya cara memakainya.
                         Sama seperti transfer: hanya dirender kalau memang menyala,
                         supaya nomor pribadi desa tidak bocor ke sumber halaman
                         wilayah yang sengaja mematikannya. --}}
                    @if($hasEwallet)
                    <div id="ewallet-payment" class="payment-content {{ $defaultMethod === 'ewallet' ? '' : 'hidden' }}">
                        <div class="bg-white border-2 border-blue-50 rounded-2xl shadow-sm p-6 sm:p-8 mb-6">
                            <h4 class="font-bold text-gray-800 mb-4">Kirim ke E-Wallet Berikut</h4>

                            <div class="flex items-center gap-4 bg-gradient-to-br from-sky-50 to-cyan-50 rounded-2xl p-5 mb-4">
                                @if($adaLogoEwallet)
                                    <img src="{{ asset($ewalletLogoPath) }}" alt="{{ $setting->ewallet_name }}" class="h-8 object-contain">
                                @endif
                                <div class="min-w-0">
                                    <div class="text-xs uppercase tracking-wider text-gray-500">{{ $setting->ewallet_name }}</div>
                                    <div class="text-xl font-black tracking-wide text-gray-900 select-all break-all">
                                        {{ $setting->ewallet_number }}
                                    </div>
                                    <div class="text-sm text-gray-600">a.n. {{ $setting->ewallet_account_holder ?: '-' }}</div>
                                </div>
                            </div>

                            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-xl mb-5">
                                <p class="text-sm text-amber-800">
                                    <strong>Kirim dari sesama {{ $setting->ewallet_name }}</strong>
                                    agar tidak dikenai biaya transfer. Pengiriman dari aplikasi lain tetap diterima,
                                    tetapi biayanya ditanggung Anda.
                                </p>
                            </div>

                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Unggah Bukti Pengiriman <span class="text-red-500">*</span>
                            </label>
                            <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf"
                                   class="block w-full text-sm text-gray-600 border-2 border-dashed border-sky-200 rounded-xl p-3
                                          file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                          file:bg-sky-500 file:text-white file:font-semibold hover:file:bg-sky-600">
                            <p class="text-xs text-gray-500 mt-2">
                                Format JPG, PNG, atau PDF. Maksimal 5 MB. Bukti Anda akan diperiksa petugas
                                sebelum pesanan diproses.
                            </p>
                        </div>
                    </div>
                    @endif
                    <!-- Cash Payment Card -->
                    @if($hasTunai)
                    <div id="cash-payment" class="payment-content {{ $defaultMethod === 'tunai' ? '' : 'hidden' }}">
                        <div class="bg-white border-2 border-blue-50 rounded-2xl shadow-sm p-6 sm:p-8 mb-6">
                            <div class="flex flex-col items-center sm:flex-row sm:justify-between gap-6">
                                <!-- Left Section -->
                                <div class="flex items-center gap-4 text-center sm:text-left">
                                    <div class="hidden sm:flex flex-shrink-0 w-12 h-12 bg-blue-50 text-blue-500 rounded-full items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-800 mb-1">Bayar Di Tempat (COD)</h4>
                                        <p class="text-sm text-gray-500">{{ $cashDescription }}</p>
                                    </div>
                                </div>
                                
                                <!-- Right Section -->
                                <div class="text-center sm:text-right">
                                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider mb-1">Total Pembayaran</p>
                                    <p class="text-2xl sm:text-3xl font-black text-blue-600" id="total-amount-cash">Rp. {{ number_format($item->harga_satuan * $quantity, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @else
                        <div class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                            Wilayah ini belum mengaktifkan metode pembayaran apa pun.
                            Silakan hubungi pengelola layanan.
                        </div>
                    @endif {{-- ada metode --}}
                    <input type="hidden" name="payment_method" id="payment-method-hidden" value="{{ $defaultMethod }}">


                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="button" 
                            id="confirm-gas-booking-btn"
                            class="px-8 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                        Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Confirmation Modal -->
    <div id="gas-confirmation-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl transform transition-all">
            <div class="text-center">
                <img src="{{ asset('Admin/img/illustrations/isewalogo.webp') }}" alt="SegiDaerah" class="w-40 mx-auto mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Anda akan Melakukan Pembelian Gas</h2>
                <p class="text-gray-600 mb-2">Pesanan Anda Akan Diproses</p>
                <p class="text-gray-500 text-sm mb-6">Apakah anda yakin?</p>
                
                <div class="flex gap-4">
                    <button type="button" 
                            id="cancel-gas-confirmation"
                            class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-full transition-colors">
                        Tidak
                    </button>
                    <button type="button" 
                            id="proceed-gas-confirmation"
                            class="flex-1 px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-full transition-colors">
                        Ya
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="gas-success-modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 backdrop-blur-sm transition-all duration-300" style="display: none;">
        <div class="bg-white rounded-[2rem] p-10 max-w-lg w-full mx-4 shadow-2xl transform transition-all scale-100 relative">
            <button type="button" id="close-gas-success-modal" class="absolute top-6 right-6 text-gray-400 hover:text-gray-800 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            
            <div class="text-center">
                <div class="mb-8 mt-4">
                    <div class="checkmark-circle mx-auto">
                        <svg class="checkmark" viewBox="0 0 52 52">
                            <circle class="checkmark-circle-path" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>
                </div>
                
                <h2 class="text-3xl font-extrabold text-gray-900 mb-3 tracking-tight">Pesanan Berhasil Dibuat</h2>
                
                <div class="mb-8">
                    <p class="text-gray-800 font-bold text-lg mb-1">Pesanan Anda sedang Diproses</p>
                    <p class="text-gray-500 text-sm">Silahkan klik untuk menuju halaman selanjutnya</p>
                </div>
                
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" 
                                id="view-gas-receipt-btn"
                                class="px-4 py-3 bg-gray-50 text-blue-600 font-bold rounded-xl hover:bg-blue-50 transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm hover:shadow-md text-sm">
                            Lihat Bukti Transaksi
                        </button>
                        <button type="button" 
                                id="download-gas-receipt-btn"
                                class="px-4 py-3 bg-gray-50 text-blue-600 font-bold rounded-xl hover:bg-blue-50 transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm hover:shadow-md text-sm">
                            Unduh Bukti Transaksi
                        </button>
                    </div>
                    <button type="button" 
                            id="view-gas-activity-btn"
                            class="w-full px-6 py-4 bg-[#2395FF] hover:bg-blue-600 text-white font-extrabold rounded-2xl shadow-lg hover:shadow-blue-200/50 transition-all duration-300 transform hover:scale-[1.02] text-lg">
                        Lihat Aktivitas
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('styles')
<style>
    * {
        font-family: 'Inter', sans-serif;
    }

    /* Payment Method Buttons */
    .payment-method-btn.active {
        background-color: #f8fafc !important; /* Very light grayish blue */
        border-color: #cbd5e1 !important; /* Soft grayish blue border (slate-300) */
        border-width: 2px !important;
        font-weight: 700 !important;
    }
    
    .payment-method-btn.active span {
        color: #1e293b !important; /* Dark slate text */
        font-weight: 700;
    }

    /* Remove spinner from number input */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }

    /* Checkmark Animation */
    .checkmark-circle {
        width: 150px;
        height: 150px;
        position: relative;
        display: inline-block;
    }

    .checkmark {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        display: block;
        stroke-width: 3;
        stroke: #4ade80;
        stroke-miterlimit: 10;
        box-shadow: inset 0px 0px 0px #4ade80;
        animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    }

    .checkmark-circle-path {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 3;
        stroke-miterlimit: 10;
        stroke: #4ade80;
        animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .checkmark-check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        stroke: #fff;
        animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }

    @keyframes scale {
        0%, 100% {
            transform: none;
        }
        50% {
            transform: scale3d(1.1, 1.1, 1);
        }
    }

    @keyframes fill {
        100% {
            box-shadow: inset 0px 0px 0px 75px #4ade80;
        }
    }
    
    @keyframes scan {
        0% { top: 0; }
        50% { top: 100%; }
        100% { top: 0; }
    }

    /* Delivery Method Cards */
    .delivery-method-card {
        border: 2px solid transparent;
        background-color: white;
    }
    .delivery-method-card.active {
        border-color: #3b82f6;
        background-color: #eff6ff;
        transform: scale(1.05);
    }
    .delivery-method-card:hover:not(.active) {
        border-color: #93c5fd;
        transform: translateY(-2px);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (() => {
        function initGasBooking() {
            'use strict';
            const form = document.getElementById('gas-booking-form');
            if (!form || form.dataset.initialized === 'true') return;
            form.dataset.initialized = 'true';

            const pricePerUnit = {{ $item->harga_satuan }};
            const maxStock = {{ $item->stok }};

        // Delivery Method Logic
        const deliveryCards = document.querySelectorAll('.delivery-method-card');
        const deliveryMethodInput = document.getElementById('delivery-method-input');
        const antarNote = document.getElementById('antar-note');
        const jemputNote = document.getElementById('jemput-note');
        const gasAntarAddressCard = document.getElementById('gas-antar-address-card');
        const gasJemputLocationCard = document.getElementById('gas-jemput-location-card');
        const buyerAddressInput = document.getElementById('buyer-address');

        if (deliveryCards.length > 0) {
            deliveryCards.forEach(card => {
                card.addEventListener('click', function() {
                    const method = this.dataset.method;
                    
                    // Update active state on cards
                    deliveryCards.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update hidden input
                    deliveryMethodInput.value = method;
                    
                    // Toggle notes & location/address cards
                    if (method === 'antar') {
                        if(antarNote) antarNote.classList.remove('hidden');
                        if(jemputNote) jemputNote.classList.add('hidden');
                        if(gasAntarAddressCard) gasAntarAddressCard.classList.remove('hidden');
                        if(gasJemputLocationCard) gasJemputLocationCard.classList.add('hidden');
                        if(buyerAddressInput) buyerAddressInput.required = true;
                    } else {
                        if(antarNote) antarNote.classList.add('hidden');
                        if(jemputNote) jemputNote.classList.remove('hidden');
                        if(gasAntarAddressCard) gasAntarAddressCard.classList.add('hidden');
                        if(gasJemputLocationCard) gasJemputLocationCard.classList.remove('hidden');
                        if(buyerAddressInput) buyerAddressInput.required = false;
                    }
                });
            });
        }

        // Quantity Selector
        const qtyInput = document.getElementById('quantity-display');
        const hiddenQty = document.getElementById('hidden-quantity');
        const decreaseBtn = document.getElementById('decrease-qty');
        const increaseBtn = document.getElementById('increase-qty');
        const subtotalEl = document.getElementById('subtotal');
        const totalCashEl = document.getElementById('total-amount-cash');

        function updateTotals() {
            const qty = parseInt(qtyInput.value) || 1;
            const total = pricePerUnit * qty;
            const formattedTotal = 'Rp. ' + total.toLocaleString('id-ID');
            
            subtotalEl.textContent = formattedTotal;
            if (totalCashEl) totalCashEl.textContent = formattedTotal;
            hiddenQty.value = qty;
        }

        if (decreaseBtn && increaseBtn) {
            decreaseBtn.addEventListener('click', () => {
                let currentValue = parseInt(qtyInput.value) || 1;
                if (currentValue > 1) {
                    qtyInput.value = currentValue - 1;
                    updateTotals();
                }
            });

            increaseBtn.addEventListener('click', () => {
                let currentValue = parseInt(qtyInput.value) || 1;
                if (currentValue < maxStock) {
                    qtyInput.value = currentValue + 1;
                    updateTotals();
                }
            });

            qtyInput.addEventListener('change', () => {
                let value = parseInt(qtyInput.value) || 1;
                if (value < 1) qtyInput.value = 1;
                if (value > maxStock) qtyInput.value = maxStock;
                updateTotals();
            });
        }

        window.setPaymentMethod = function(method) {
            const paymentMethodInput = document.getElementById('payment-method-hidden');
            if (paymentMethodInput) paymentMethodInput.value = method;
            
            // Update active class
            document.querySelectorAll('.payment-method-btn').forEach(btn => {
                btn.classList.remove('active', 'shadow-md', 'transform', 'scale-105');
                btn.classList.add('bg-white', 'shadow-sm');
            });
            
            const selectedBtn = document.getElementById('btn-' + method);
            if (selectedBtn) {
                selectedBtn.classList.remove('bg-white', 'shadow-sm');
                selectedBtn.classList.add('active', 'shadow-md', 'transform', 'scale-105');
            }

            const cashPaymentCard = document.getElementById('cash-payment');
            const transferPaymentCard = document.getElementById('transfer-payment');
            const ewalletPaymentCard = document.getElementById('ewallet-payment');

            [cashPaymentCard, transferPaymentCard, ewalletPaymentCard].forEach(function (el) {
                if (el) el.classList.add('hidden');
            });

            if (method === 'tunai') {
                if (cashPaymentCard) cashPaymentCard.classList.remove('hidden');
            } else if (method === 'transfer') {
                if (transferPaymentCard) transferPaymentCard.classList.remove('hidden');
            } else if (method === 'ewallet') {
                if (ewalletPaymentCard) ewalletPaymentCard.classList.remove('hidden');
            }

            // Blok transfer dan e-wallet sama-sama punya input bernama
            // payment_proof. Kalau keduanya ikut terkirim, yang belakangan —
            // meski kosong — menimpa yang berisi, dan bukti bayar warga hilang
            // dalam perjalanan. Yang sedang tersembunyi dimatikan supaya tidak
            // ikut terkirim sama sekali.
            document.querySelectorAll('.payment-content').forEach(function (kartu) {
                const tersembunyi = kartu.classList.contains('hidden');
                kartu.querySelectorAll('input[type="file"]').forEach(function (input) {
                    input.disabled = tersembunyi;
                    if (tersembunyi) input.value = '';
                });
            });
        };

        // Initialize display based on default value
        const initialMethod = document.getElementById('payment-method-hidden')?.value || '';
        if (initialMethod) setPaymentMethod(initialMethod);

        // File Upload Display
        const fileInput = document.getElementById('payment-proof');
        const fileNameDisplay = document.getElementById('file-name');

        if (fileInput && fileNameDisplay) {
            fileInput.addEventListener('change', (e) => {
                const fileName = e.target.files[0]?.name || 'Belum ada file dipilih';
                fileNameDisplay.textContent = fileName;
                fileNameDisplay.classList.remove('italic');
            });
        }

        // ============================================
        // GAS BOOKING CONFIRMATION SYSTEM
        // ============================================
        
        const gasConfirmationModal = document.getElementById('gas-confirmation-modal');
        const gasSuccessModal = document.getElementById('gas-success-modal');
        const confirmGasBookingBtn = document.getElementById('confirm-gas-booking-btn');
        const cancelGasConfirmation = document.getElementById('cancel-gas-confirmation');
        const proceedGasConfirmation = document.getElementById('proceed-gas-confirmation');
        const closeGasSuccessModal = document.getElementById('close-gas-success-modal');
        const viewGasReceiptBtn = document.getElementById('view-gas-receipt-btn');
        const downloadGasReceiptBtn = document.getElementById('download-gas-receipt-btn');
        const viewGasActivityBtn = document.getElementById('view-gas-activity-btn');
        const gasBookingForm = document.getElementById('gas-booking-form');

        let gasReceiptId = null;

        // Show confirmation modal
        if (confirmGasBookingBtn) {
            confirmGasBookingBtn.addEventListener('click', function() {
                const deliveryMethod = deliveryMethodInput ? deliveryMethodInput.value : 'antar';
                const buyerName = document.getElementById('buyer-name')?.value?.trim();
                const buyerAddress = document.getElementById('buyer-address')?.value?.trim();

                if (!buyerName || (deliveryMethod === 'antar' && !buyerAddress)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: deliveryMethod === 'antar'
                            ? 'Mohon lengkapi Nama dan Alamat Pengantaran'
                            : 'Nama pembeli pada akun tidak tersedia.',
                        confirmButtonColor: '#3085d6',
                    });
                    return;
                }



                const selectedPayment = document.getElementById('payment-method-hidden')?.value || '';
                if (!selectedPayment) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Pembayaran Belum Tersedia',
                        text: 'Wilayah ini belum mengaktifkan metode pembayaran apa pun. Silakan hubungi pengelola.',
                        confirmButtonColor: '#3085d6',
                    });
                    return;
                }

                gasConfirmationModal.style.display = 'flex';
                gasConfirmationModal.classList.remove('hidden');

            });
        }

        // Cancel confirmation
        if (cancelGasConfirmation) {
            cancelGasConfirmation.addEventListener('click', function() {
                gasConfirmationModal.style.display = 'none';
                gasConfirmationModal.classList.add('hidden');
            });
        }

        // Proceed with booking
        if (proceedGasConfirmation) {
            proceedGasConfirmation.addEventListener('click', function() {
                if (proceedGasConfirmation.disabled) return;
                proceedGasConfirmation.disabled = true;
                gasConfirmationModal.style.display = 'none';
                gasConfirmationModal.classList.add('hidden');

                // Show Loading with SweetAlert2
                Swal.fire({
                    title: 'Sedang Memproses...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData(gasBookingForm);
                if ((deliveryMethodInput?.value || 'antar') === 'jemput') {
                    formData.set('buyer_address', document.getElementById('gas-pickup-address-default')?.value || 'Ambil di lokasi layanan');
                    formData.delete('latitude');
                    formData.delete('longitude');
                }

                fetch('{{ route("gas.booking.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    Swal.close(); // Close loading
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = data.payment_url;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: Object.values(data.errors || {}).flat()[0] || data.message || 'Gagal memproses pesanan',
                            confirmButtonColor: '#d33',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Koneksi terputus. Periksa Riwayat Pesanan sebelum mencoba lagi. Data formulir tetap tersimpan di halaman ini.',
                        confirmButtonColor: '#d33',
                    });
                }).finally(() => { proceedGasConfirmation.disabled = false; });
            });
        }

        // Close success modal
        if (closeGasSuccessModal) {
            closeGasSuccessModal.addEventListener('click', function() {
                gasSuccessModal.style.display = 'none';
                gasSuccessModal.classList.add('hidden');
            });
        }

        // View receipt
        if (viewGasReceiptBtn) {
            viewGasReceiptBtn.addEventListener('click', function() {
                if (gasReceiptId) {
                    window.open(`/receipt/gas/${gasReceiptId}/view`, '_blank');
                }
            });
        }

        // Download receipt
        if (downloadGasReceiptBtn) {
            downloadGasReceiptBtn.addEventListener('click', function() {
                if (gasReceiptId) {
                    window.location.href = `/receipt/gas/${gasReceiptId}/download`;
                }
            });
        }

        // View activity
        if (viewGasActivityBtn) {
            viewGasActivityBtn.addEventListener('click', function() {
                window.location.href = '{{ route("user.activity") }}';
            });
        }


        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initGasBooking);
        } else {
            initGasBooking();
        }
        document.addEventListener('turbo:load', initGasBooking);
    })();
</script>

@endpush
