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
    $cashDescription = $setting->cash_payment_description ?? 'Yani - Bendahara BUMDes';

    // Bank Logo Mapping
    $bankLogos = [
        'Bank Syariah Indonesia' => 'admin/img/banks/bsi.png',
        'BSI' => 'admin/img/banks/bsi.png',
        'BRI' => 'admin/img/banks/bri.png',
        'BRIMO' => 'admin/img/banks/bri.png',
        'Mandiri' => 'admin/img/banks/mandiri.png',
        'BNI' => 'admin/img/banks/bni.png',
        'BCA' => 'admin/img/banks/bca.png',
        'Bank Riau Kepri Syariah' => 'admin/img/banks/brk.png',
        'Bank Mega' => 'admin/img/banks/mega.png',
    ];
    $bankName = strtoupper($setting->bank_name ?? '');
    $bankLogoPath = 'admin/img/banks/bsi.png';
    foreach ($bankLogos as $key => $path) {
        if (str_contains($bankName, strtoupper($key))) {
            $bankLogoPath = $path;
            break;
        }
    }

    // E-Wallet Logo Mapping
    $ewalletLogos = [
        'DANA' => 'admin/img/banks/dana.png',
        'OVO' => 'admin/img/banks/ovo.png',
        'GOPAY' => 'admin/img/banks/gopay.png',
        'SHOPEEPAY' => 'admin/img/banks/shopeepay.png',
        'LINKAJA' => 'admin/img/banks/linkaja.png',
    ];
    $ewalletName = strtoupper($setting->ewallet_name ?? '');
    $ewalletLogoPath = 'admin/img/banks/dana.png';
    foreach ($ewalletLogos as $key => $path) {
        if (str_contains($ewalletName, strtoupper($key))) {
            $ewalletLogoPath = $path;
            break;
        }
    }

    $hasEwallet = !empty($setting->ewallet_name) && !empty($setting->ewallet_number);
    
    // Tentukan metode pembayaran yang tersedia dengan fallback yang lebih baik
    $methods = $setting?->payment_methods ?? ['transfer', 'tunai'];
    if (!is_array($methods) || empty($methods)) {
        $methods = ['transfer', 'tunai'];
    }
    $hasTransfer = in_array('transfer', $methods);
    $hasTunai = in_array('tunai', $methods);
    
    // Pastikan setidaknya satu metode tersedia
    if (!$hasTransfer && !$hasTunai) {
        $hasTransfer = true;
        $hasTunai = true;
    }
    
    // Tentukan metode aktif default
    $defaultMethod = $hasTransfer ? 'transfer' : 'tunai';
@endphp

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16 bg-cover bg-center bg-no-repeat bg-fixed" 
             style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
        
        <!-- White Overlay -->
        <div class="absolute inset-0 bg-white/25 pointer-events-none"></div>

        <div class="max-w-5xl mx-auto px-6 relative z-20">
            <!-- Header dengan Teks Gradien (Tengah) -->
            <div class="text-center mb-12 mt-8">
                <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">
                    Pembelian Gas
                </h1>
            </div>

            <form id="gas-booking-form" action="#" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="gas_id" value="{{ $item->id }}">
                <input type="hidden" name="quantity" id="hidden-quantity" value="{{ $quantity }}">

                <!-- Alamat BUMDes Card -->
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Alamat Bumdes</h3>
                        <a href="{{ ($setting && $setting->latitude && $setting->longitude) ? 'https://www.google.com/maps?q=' . $setting->latitude . ',' . $setting->longitude : 'https://maps.app.goo.gl/LE5JRcccSP6EjpZ37' }}" target="_blank" class="ml-auto flex items-center gap-2 text-blue-500 hover:text-blue-600 transition-colors group">
                            <span class="text-sm font-medium group-hover:underline">Lihat lokasi</span>
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                        </a>
                    </div>
                    
                    <!-- Nama Lokasi -->
                    @if($setting && $setting->location_name)
                    <div class="mb-4 animate-fade-in">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-blue-100 rounded-lg mt-1">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-500 mb-1">Nama Lokasi</p>
                                <p class="text-base font-bold text-gray-800">{{ $setting->location_name }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Alamat Lengkap -->
                    @if($setting && $setting->address)
                    <div class="mb-4 animate-fade-in" style="animation-delay: 0.1s">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-green-100 rounded-lg mt-1">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-500 mb-1">Alamat Lengkap</p>
                                <p class="text-base text-gray-700 leading-relaxed">{{ $setting->address }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Operational Hours -->
                    @if($setting && $setting->operating_hours)
                    <div class="mb-4 animate-fade-in" style="animation-delay: 0.2s">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-orange-100 rounded-lg mt-1">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-500 mb-1">JAM OPERASIONAL</p>
                                <p class="text-base text-gray-700 font-medium">{{ $setting->operating_hours }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- WhatsApp Contact -->
                    @if($setting && $setting->whatsapp_number)
                    <div class="animate-fade-in" style="animation-delay: 0.3s">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $setting->whatsapp_number) }}" target="_blank" class="flex items-start gap-3 group hover:bg-gray-50 p-2 rounded-xl transition-colors -mx-2">
                            <div class="p-2 bg-green-100 rounded-lg mt-1 group-hover:bg-green-200 transition-colors">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-500 mb-1">Hubungi Kami</p>
                                <p class="text-base font-bold text-gray-800 group-hover:text-green-600 transition-colors">Halo BUMDes</p>
                            </div>
                        </a>
                    </div>
                    @endif
                </div>

                @php
                    $antarActive = !isset($setting->payment_info['gas_delivery_antar_active']) || $setting->payment_info['gas_delivery_antar_active'];
                    $jemputActive = !isset($setting->payment_info['gas_delivery_jemput_active']) || $setting->payment_info['gas_delivery_jemput_active'];
                    $defaultMethod = $antarActive ? 'antar' : 'jemput';
                @endphp
                <input type="hidden" name="delivery_method" id="delivery-method-input" value="{{ $defaultMethod }}">

                <!-- Pilihan Metode Pengiriman -->
                <div class="flex flex-col sm:flex-row justify-center gap-6 mb-10 items-center">
                    @if($antarActive)
                    <!-- Antar Card -->
                    <div class="delivery-method-card {{ $defaultMethod == 'antar' ? 'active' : '' }} cursor-pointer bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 w-48 text-center border-4 border-transparent" data-method="antar">
                        <div class="mb-4 flex justify-center">
                            <img src="{{ asset('Admin/img/elements/antar.png') }}" alt="Antar" class="w-20 h-20 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800">Diantar</p>
                    </div>
                    @endif

                    @if($jemputActive)
                    <!-- Jemput Card -->
                    <div class="delivery-method-card {{ $defaultMethod == 'jemput' ? 'active' : '' }} cursor-pointer bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 w-48 text-center border-4 border-transparent" data-method="jemput">
                        <div class="mb-4 flex justify-center">
                            <img src="{{ asset('Admin/img/elements/jemput.png') }}" alt="Jemput" class="w-20 h-20 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800">Ambil Sendiri</p>
                    </div>
                    @endif
                </div>

                <!-- Info Note -->
                <div id="antar-note" class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg {{ $defaultMethod == 'antar' ? '' : 'hidden' }}">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <span class="font-bold">Info:</span> Pesanan Gas akan diantar oleh pihak BUMDes ke alamat Anda.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="jemput-note" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg {{ $defaultMethod == 'jemput' ? '' : 'hidden' }}">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <span class="font-bold">NB:</span> Pengambilan Gas dilakukan secara mandiri oleh Anda di lokasi BUMDes.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Kartu Informasi Pembeli -->
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex items-center gap-3 mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                        <h3 class="text-lg font-bold text-gray-800">Nama dan Alamat Lengkap</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <input type="text" 
                               name="buyer_name" 
                               id="buyer-name"
                               placeholder="Nama Lengkap" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                        <textarea name="buyer_address" 
                                  id="buyer-address"
                                  rows="3" 
                                  placeholder="Alamat Lengkap" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  required></textarea>
                    </div>
                </div>

                <!-- Kartu Produk -->
                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex flex-col sm:flex-row gap-6">
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
                            <p class="text-sm text-gray-600 mb-1">{{ $item->kategori }}</p>
                            <div class="mt-4">
                                <p class="text-sm text-gray-600 mb-1">Subtotal</p>
                                <p class="text-xl font-bold text-gray-800" id="subtotal">Rp. {{ number_format($item->harga_satuan * $quantity, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Metode Pembayaran</h3>
                    
                    @if($hasTransfer && $hasTunai)
                    <!-- Payment Methods -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
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

                        @if($hasTransfer)
                        <!-- Bank BCA -->
                        <button type="button" 
                                onclick="setPaymentMethod('bank_transfer_bca')"
                                id="btn-bank_transfer_bca"
                                class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 bg-white shadow-sm border border-gray-100 hover:border-blue-300 hover:shadow-md hover:-translate-y-1">
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="flex flex-col items-center justify-center gap-3 text-center h-full relative z-10">
                                <img src="{{ asset('admin/img/banks/bca.png') }}" alt="BCA" class="h-6 object-contain transform group-hover:scale-110 transition-transform">
                                <span class="text-[10px] uppercase tracking-widest text-gray-700 group-hover:text-blue-600 font-bold">Virtual Account</span>
                            </div>
                        </button>

                        <!-- Bank BRI -->
                        <button type="button" 
                                onclick="setPaymentMethod('bank_transfer_bri')"
                                id="btn-bank_transfer_bri"
                                class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 bg-white shadow-sm border border-gray-100 hover:border-orange-300 hover:shadow-md hover:-translate-y-1">
                            <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="flex flex-col items-center justify-center gap-3 text-center h-full relative z-10">
                                <img src="{{ asset('admin/img/banks/bri.png') }}" alt="BRI" class="h-6 object-contain transform group-hover:scale-110 transition-transform">
                                <span class="text-[10px] uppercase tracking-widest text-gray-700 group-hover:text-orange-600 font-bold">Virtual Account</span>
                            </div>
                        </button>

                        <!-- Bank Mandiri -->
                        <button type="button" 
                                onclick="setPaymentMethod('bank_transfer_mandiri')"
                                id="btn-bank_transfer_mandiri"
                                class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 bg-white shadow-sm border border-gray-100 hover:border-yellow-400 hover:shadow-md hover:-translate-y-1">
                            <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="flex flex-col items-center justify-center gap-3 text-center h-full relative z-10">
                                <img src="{{ asset('admin/img/banks/mandiri.png') }}" alt="Mandiri" class="h-6 object-contain transform group-hover:scale-110 transition-transform">
                                <span class="text-[10px] uppercase tracking-widest text-gray-700 group-hover:text-yellow-600 font-bold">Virtual Account</span>
                            </div>
                        </button>

                        <!-- Bank BNI -->
                        <button type="button" 
                                onclick="setPaymentMethod('bank_transfer_bni')"
                                id="btn-bank_transfer_bni"
                                class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 bg-white shadow-sm border border-gray-100 hover:border-orange-500 hover:shadow-md hover:-translate-y-1">
                            <div class="absolute inset-0 bg-gradient-to-br from-orange-500/5 to-transparent rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <div class="flex flex-col items-center justify-center gap-3 text-center h-full relative z-10">
                                <img src="{{ asset('admin/img/banks/bni.png') }}" alt="BNI" class="h-6 object-contain transform group-hover:scale-110 transition-transform">
                                <span class="text-[10px] uppercase tracking-widest text-gray-700 group-hover:text-orange-600 font-bold">Virtual Account</span>
                            </div>
                        </button>

                        <!-- QRIS -->
                        <button type="button" 
                                onclick="setPaymentMethod('qris')"
                                id="btn-qris"
                                class="payment-method-btn group relative py-4 px-2 rounded-2xl font-bold transition-all duration-300 bg-white shadow-sm border border-gray-100 hover:border-red-500 hover:shadow-md hover:-translate-y-1 overflow-hidden">
                            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(239,68,68,0.08)_0%,transparent_70%)] opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            <!-- Mini scanning line effect on hover -->
                            <div class="absolute top-0 left-0 right-0 h-0.5 bg-red-500 opacity-0 group-hover:opacity-100 group-hover:animate-[scan_1.5s_ease-in-out_infinite] blur-[1px]"></div>
                            
                            <div class="flex flex-col items-center justify-center gap-3 text-center h-full relative z-10">
                                <div class="bg-white p-1 rounded-lg shadow-sm group-hover:shadow border border-gray-50 transform group-hover:scale-110 transition-all">
                                    <img src="{{ asset('admin/img/banks/qris.svg') }}" alt="QRIS" class="h-6 object-contain" onerror="this.src='{{ asset('admin/img/banks/dana.png') }}'">
                                </div>
                                <span class="text-[10px] uppercase tracking-widest text-gray-700 group-hover:text-red-600 font-black">All E-Wallet</span>
                            </div>
                        </button>
                        @endif
                    </div>
                    @endif
                    <input type="hidden" name="payment_method" id="payment-method-hidden" value="{{ $defaultMethod }}">

                    <!-- Midtrans Payment Card -->
                    <div id="midtrans-payment" class="payment-content hidden">
                    </div>

                    <!-- Cash Payment Card -->
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
                <img src="{{ asset('Admin/img/illustrations/isewalogo.png') }}" alt="SiladesBeng Logo" class="w-40 mx-auto mb-6">
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
        'use strict';

        const pricePerUnit = {{ $item->harga_satuan }};
        const maxStock = {{ $item->stok }};

        // Delivery Method Logic
        const deliveryCards = document.querySelectorAll('.delivery-method-card');
        const deliveryMethodInput = document.getElementById('delivery-method-input');
        const antarNote = document.getElementById('antar-note');
        const jemputNote = document.getElementById('jemput-note');

        if (deliveryCards.length > 0) {
            deliveryCards.forEach(card => {
                card.addEventListener('click', function() {
                    const method = this.dataset.method;
                    
                    // Update active state on cards
                    deliveryCards.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update hidden input
                    deliveryMethodInput.value = method;
                    
                    // Toggle notes
                    if (method === 'antar') {
                        if(antarNote) antarNote.classList.remove('hidden');
                        if(jemputNote) jemputNote.classList.add('hidden');
                    } else {
                        if(antarNote) antarNote.classList.add('hidden');
                        if(jemputNote) jemputNote.classList.remove('hidden');
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

            const midtransPaymentCard = document.getElementById('midtrans-payment');
            const cashPaymentCard = document.getElementById('cash-payment');
            
            if (midtransPaymentCard) midtransPaymentCard.classList.add('hidden');
            if (cashPaymentCard) cashPaymentCard.classList.add('hidden');

            if (method === 'tunai') {
                if(cashPaymentCard) cashPaymentCard.classList.remove('hidden');
            } else {
                if(midtransPaymentCard) midtransPaymentCard.classList.remove('hidden');
            }
        };

        // Initialize display based on default value
        const initialMethod = document.getElementById('payment-method-hidden')?.value || 'tunai';
        if(initialMethod) setPaymentMethod(initialMethod);

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
                const buyerName = document.getElementById('buyer-name')?.value;
                const buyerAddress = document.getElementById('buyer-address')?.value;

                if (!buyerName || !buyerAddress) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: 'Mohon lengkapi Nama dan Alamat Pembeli',
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
                        if (document.getElementById('payment-method-hidden').value === 'tunai') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Pesanan Berhasil Dibuat',
                                text: 'Pesanan COD Anda sedang diproses. Mohon siapkan uang pas saat BUMDes tiba di alamat Anda.',
                                confirmButtonColor: '#3b82f6',
                            }).then(() => {
                                window.location.href = '{{ route("user.activity") }}';
                            });
                        } else {
                            // Redirect to beautiful payment instructions page
                            window.location.href = '/gas/payment/' + data.order_id;
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Gagal memproses pesanan',
                            confirmButtonColor: '#d33',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Error detail: ' + error.message,
                        confirmButtonColor: '#d33',
                    });
                });
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


    })();
</script>

<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=places"></script>
<script>
    // Initialize Places Autocomplete
    document.addEventListener('DOMContentLoaded', function() {
        const addressInput = document.getElementById('buyer-address');
        if (addressInput && typeof google === 'object' && typeof google.maps === 'object' && google.maps.places) {
            const autocomplete = new google.maps.places.Autocomplete(addressInput, {
                types: ['geocode'],
                componentRestrictions: { country: "id" }
            });
        }
    });
</script>
@endpush
