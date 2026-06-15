@extends('layouts.user')

@php
    // Tentukan gaya latar belakang kartu berdasarkan pengaturan admin
    $cardStyle = 'background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);'; // default blue
    $amountColor = 'text-yellow-300'; // Default amount color
    $cardTextColor = 'text-white'; // Default card text color
    $buttonClass = 'bg-white/20 backdrop-blur-sm border border-white/40 text-white hover:bg-white/30'; // Default button style
    $borderClass = 'border-white/30'; // Default border style
    
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
        
        // Tentukan warna berdasarkan latar belakang
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
    
    // Dukungan legacy untuk gambar (jika diaktifkan kembali atau sudah ada)
    if ($setting && $setting->card_background_type === 'image' && $setting->card_background_image) {
        $cardStyle = "background-image: url('" . asset('storage/' . $setting->card_background_image) . "'); background-size: cover; background-position: center;";
        $amountColor = 'text-yellow-300';
        $cardTextColor = 'text-white';
    }
    
    // Dapatkan deskripsi pembayaran tunai
    $cashDescription = $setting->cash_payment_description ?? 'Yani - Bendahara BUMDes';

    // Bank Logo Mapping
    $bankLogos = [
        'Bank Syariah Indonesia' => 'admin/img/banks/bsi.png',
        'BRI' => 'admin/img/banks/bri.png',
        'Mandiri' => 'admin/img/banks/mandiri.png',
        'BNI' => 'admin/img/banks/bni.png',
        'BCA' => 'admin/img/banks/bca.png',
        'Bank Riau Kepri Syariah' => 'admin/img/banks/brk.png',
        'Bank Mega' => 'admin/img/banks/mega.png',
    ];
    $bankLogoPath = $bankLogos[$setting->bank_name ?? ''] ?? 'admin/img/banks/bsi.png';
    
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
        
        <!-- White Overlay (25% opacity / 75% transparent) to make background visible -->
        <div class="absolute inset-0 bg-white/25 pointer-events-none"></div>

        <!-- Elemen Dekoratif Latar Belakang (Dikomeli) -->
        {{-- 
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <!-- Top Left Blue Wave -->
            <svg class="absolute top-0 left-0 w-[500px] h-[400px] opacity-30" style="transform: translate(-20%, -10%);">
                <defs>
                    <linearGradient id="blueWave1" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#60a5fa;stop-opacity:0.6" />
                        <stop offset="100%" style="stop-color:#93c5fd;stop-opacity:0.3" />
                    </linearGradient>
                </defs>
                <path d="M0,100 Q150,50 300,100 T600,100 L600,0 L0,0 Z" fill="url(#blueWave1)" />
            </svg>

            <!-- Top Right Geometric Shape -->
            <div class="absolute top-20 right-0" style="transform: translateX(30%) rotate(15deg);">
                <svg width="300" height="300" viewBox="0 0 300 300" class="opacity-20">
                    <rect x="50" y="50" width="80" height="80" fill="#60a5fa" transform="rotate(45 90 90)" opacity="0.4"/>
                    <rect x="150" y="80" width="60" height="60" fill="#93c5fd" transform="rotate(30 180 110)" opacity="0.3"/>
                </svg>
            </div>

            <!-- Bottom Left Yellow Wave -->
            <svg class="absolute bottom-0 left-0 w-[600px] h-[400px] opacity-40" style="transform: translate(-15%, 20%);">
                <defs>
                    <linearGradient id="yellowWave" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#fbbf24;stop-opacity:0.5" />
                        <stop offset="100%" style="stop-color:#fde68a;stop-opacity:0.2" />
                    </linearGradient>
                </defs>
                <path d="M0,200 Q200,150 400,200 T800,200 L800,400 L0,400 Z" fill="url(#yellowWave)" />
            </svg>

            <!-- Bottom Right Blue Wave -->
            <svg class="absolute bottom-0 right-0 w-[500px] h-[350px] opacity-35" style="transform: translate(20%, 15%);">
                <defs>
                    <linearGradient id="blueWave2" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:0.4" />
                        <stop offset="100%" style="stop-color:#60a5fa;stop-opacity:0.2" />
                    </linearGradient>
                </defs>
                <path d="M0,150 Q150,100 300,150 T600,150 L600,400 L0,400 Z" fill="url(#blueWave2)" />
            </svg>
        </div>
        --}}

        <div class="max-w-5xl mx-auto px-6 relative z-20">
            <!-- Header dengan Teks Gradien -->
            <div class="text-center mb-12 mt-8">
                <h1 class="text-3xl md:text-4xl font-bold mb-2">
                    <span class="text-gray-800">Metode </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Antar Jemput Alat Sewa</span>
                </h1>
            </div>

            <form id="booking-form" action="#" method="POST" enctype="multipart/form-data" onsubmit="return false;">
                @csrf
                <input type="hidden" name="barang_id" value="{{ $item->id }}">
                <input type="hidden" name="quantity" id="hidden-quantity" value="{{ $quantity }}">
                <input type="hidden" name="delivery_method" id="delivery-method-input" value="antar">

                <!-- Pilihan Metode Pengiriman -->
                <div class="flex flex-col sm:flex-row justify-center gap-6 mb-10 items-center">
                    <!-- Antar Card -->
                    <div class="delivery-method-card active cursor-pointer bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 w-48 text-center border-4 border-transparent" data-method="antar">
                        <!-- Placeholder for Truck Icon -->
                        <div class="mb-4 flex justify-center">
                            <img src="{{ asset('Admin/img/elements/antar.png') }}" alt="Antar" class="w-20 h-20 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800">Antar</p>
                    </div>

                    <!-- Jemput Card -->
                    <div class="delivery-method-card cursor-pointer bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 w-48 text-center border-4 border-transparent" data-method="jemput">
                        <!-- Placeholder for Warehouse Icon -->
                        <div class="mb-4 flex justify-center">
                            <img src="{{ asset('Admin/img/elements/jemput.png') }}" alt="Jemput" class="w-20 h-20 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800">Jemput</p>
                    </div>
                </div>

                <!-- Antar Method Form -->
                <div id="antar-form" class="delivery-form-content">
                    <!-- Important Note -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <span class="font-bold">NB:</span> Pengembalian Alat Sewa akan dijemput oleh Pihak BUMDes setelah waktu penyewaan selesai.
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Alamat Pengiriman Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-lg font-bold text-gray-800">Alamat Pengiriman</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <input type="text" 
                                   name="recipient_name" 
                                   id="recipient-name"
                                   placeholder="Nama Lengkap" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            
                            <textarea name="delivery_address" 
                                      id="delivery-address"
                                      rows="3" 
                                      placeholder="Alamat Lengkap" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <!-- Keterangan / Tujuan Penyewaan Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-lg font-bold text-gray-800">Keterangan / Tujuan Penyewaan</h3>
                        </div>
                        
                        <textarea name="rental_purpose" 
                                  id="rental-purpose"
                                  rows="3" 
                                  placeholder="Contoh: Untuk acara pernikahan, acara keluarga, dll." 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <!-- Waktu Penyewaan Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-lg font-bold text-gray-800">Waktu Penyewaan</h3>
                            <div class="ml-auto text-right">
                                <p class="text-sm text-gray-600">Lama Hari Sewa</p>
                                <p class="text-2xl font-bold text-gray-800"><span id="days-count">0</span> Hari</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-2">Tanggal Mulai Sewa / Acara</label>
                                <input type="date" 
                                       name="start_date" 
                                       id="start-date"
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-2">Tanggal Selesai Sewa / Acara</label>
                                <input type="date" 
                                       name="end_date" 
                                       id="end-date"
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p>Pembayaran sewa bisa dilakukan hingga selesai acara</p>
                        </div>
                    </div>

                    <!-- Product Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                            </svg>
                            <h3 class="text-lg font-bold text-gray-800">{{ $item->nama_barang }}</h3>
                            <span class="ml-auto text-sm text-gray-600">{{ $item->kategori }}</span>
                        </div>
                        
                        <div class="flex gap-6">
                            <img src="{{ asset('storage/' . $item->foto) }}" 
                                 alt="{{ $item->nama_barang }}" 
                                 class="w-32 h-32 object-cover rounded-lg">
                            
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 mb-1">Harga Satuan</p>
                                <p class="text-xl font-bold text-gray-800 mb-4">Rp. {{ number_format($item->harga_sewa, 0, ',', '.') }}</p>
                                
                                <div class="flex items-center gap-3">
                                    <label class="text-sm text-gray-600">Jumlah</label>
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
                            
                            <div class="text-right">
                                <p class="text-sm text-gray-600 mb-1">Subtotal</p>
                                <p class="text-xl font-bold text-gray-800" id="subtotal">Rp. {{ number_format($item->harga_sewa * $quantity, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method (Fixed to Tunai) -->
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Metode Pembayaran</h3>
                        <input type="hidden" name="payment_method" id="payment-method-hidden" value="tunai">

                        <!-- Cash Payment Card -->
                        <div id="cash-payment" class="payment-content">
                            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl shadow-lg p-8">
                                <h4 class="text-2xl font-bold text-center text-gray-800 mb-6">Silahkan Lakukan Pembayaran Ditempat</h4>
                                
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-lg text-gray-700">{{ $cashDescription }}</p>
                                    </div>
                                    
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600 mb-1">Jumlah Yang Harus Dibayar</p>
                                        <p class="text-3xl font-bold text-red-600" id="total-amount-cash">Rp. {{ number_format($item->harga_sewa * $quantity, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="button" 
                                class="confirm-action-btn px-8 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                            Konfirmasi
                        </button>
                    </div>
                </div>

                <!-- Jemput Method Form -->
                <div id="jemput-form" class="delivery-form-content hidden">
                    <!-- Important Note -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <span class="font-bold">NB:</span> Anda akan melakukan pengantaran pengembalian alat sewa setelah waktu penyewaan selesai sesuai metode yang dipilih.
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Alamat BUMDes Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 hover:shadow-xl transition-all duration-300">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">Alamat Bumdes</h3>
                            <a href="https://maps.app.goo.gl/LE5JRcccSP6EjpZ37" target="_blank" class="ml-auto flex items-center gap-2 text-blue-500 hover:text-blue-600 transition-colors group">
                                <span class="text-sm font-medium group-hover:underline">Lihat lokasi</span>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                </svg>
                            </a>
                        </div>
                        
                        <!-- Location Name -->
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
                        
                        <!-- Full Address -->
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

                    <!-- Nama Penyewa Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 hover:shadow-xl transition-all duration-300">
                        <div class="flex items-center gap-3 mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-lg font-bold text-gray-800">Nama Penyewa</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <input type="text" 
                                   id="recipient-name-jemput"
                                   placeholder="Nama Lengkap" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            
                            <textarea id="delivery-address-jemput"
                                      rows="3" 
                                      placeholder="Alamat Lengkap" 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <!-- Keterangan / Tujuan Penyewaan Card -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 hover:shadow-xl transition-all duration-300">
                        <div class="flex items-center gap-3 mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-lg font-bold text-gray-800">Keterangan / Tujuan Penyewaan</h3>
                        </div>
                        
                        <textarea id="rental-purpose-jemput"
                                  rows="3" 
                                  placeholder="Contoh: Untuk acara pernikahan, acara keluarga, dll." 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>

                    <!-- Waktu Penyewaan Card (Same as Antar) -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-lg font-bold text-gray-800">Waktu Penyewaan</h3>
                            <div class="ml-auto text-right">
                                <p class="text-sm text-gray-600">Lama Hari Sewa</p>
                                <p class="text-2xl font-bold text-gray-800"><span id="days-count-jemput">0</span> Hari</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-2">Tanggal Mulai Sewa / Acara</label>
                                <input type="date" 
                                       id="start-date-jemput"
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-2">Tanggal Selesai Sewa / Acara</label>
                                <input type="date" 
                                       id="end-date-jemput"
                                       min="{{ date('Y-m-d') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p>Pembayaran sewa bisa dilakukan hingga selesai acara</p>
                        </div>
                    </div>

                    <!-- Product Card (Same as Antar) -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                            </svg>
                            <h3 class="text-lg font-bold text-gray-800">{{ $item->nama_barang }}</h3>
                            <span class="ml-auto text-sm text-gray-600">{{ $item->kategori }}</span>
                        </div>
                        
                        <div class="flex gap-6">
                            <img src="{{ asset('storage/' . $item->foto) }}" 
                                 alt="{{ $item->nama_barang }}" 
                                 class="w-32 h-32 object-cover rounded-lg">
                            
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 mb-1">Harga Satuan</p>
                                <p class="text-xl font-bold text-gray-800 mb-4">Rp. {{ number_format($item->harga_sewa, 0, ',', '.') }}</p>
                                
                                <div class="flex items-center gap-3">
                                    <label class="text-sm text-gray-600">Jumlah</label>
                                    <div class="flex items-center gap-2 border border-gray-300 rounded-lg px-3 py-1">
                                        <button type="button" id="decrease-qty-jemput" class="text-gray-600 hover:text-gray-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            </svg>
                                        </button>
                                        <input type="number" 
                                               id="quantity-display-jemput" 
                                               value="{{ $quantity }}" 
                                               min="1" 
                                               max="{{ $item->stok }}"
                                               class="w-12 text-center border-0 focus:outline-none focus:ring-0">
                                        <button type="button" id="increase-qty-jemput" class="text-gray-600 hover:text-gray-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <p class="text-sm text-gray-600 mb-1">Subtotal</p>
                                <p class="text-xl font-bold text-gray-800" id="subtotal-jemput">Rp. {{ number_format($item->harga_sewa * $quantity, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                    <!-- Payment Method for Jemput (Fixed to Tunai) -->
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Metode Pembayaran</h3>
                        <input type="hidden" name="payment_method_jemput" id="payment-method-jemput-hidden" value="tunai">

                        <!-- Cash Payment Card for Jemput -->
                        <div id="cash-payment-jemput" class="payment-content-jemput">
                            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl shadow-lg p-8">
                                <h4 class="text-2xl font-bold text-center text-gray-800 mb-6">Silahkan Lakukan Pembayaran Ditempat</h4>
                                
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-lg text-gray-700">{{ $cashDescription }}</p>
                                    </div>
                                    
                                    <div class="text-right">
                                        <p class="text-sm text-gray-600 mb-1">Jumlah Yang Harus Dibayar</p>
                                        <p class="text-3xl font-bold text-red-600" id="total-amount-cash-jemput">Rp. {{ number_format($item->harga_sewa * $quantity, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="button" 
                                class="confirm-action-btn px-8 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                            Konfirmasi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Confirmation Modal -->
    <div id="confirmation-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl transform transition-all">
            <div class="text-center">
                <img src="{{ asset('Admin/img/illustrations/isewalogo.png') }}" alt="SidesBeng Logo" class="w-40 mx-auto mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Anda akan Melakukan Penyewaan</h2>
                <p class="text-gray-600 mb-2">Pesanan Anda Akan Diproses</p>
                <p class="text-gray-500 text-sm mb-6">Apakah anda yakin?</p>
                
                <div class="flex gap-4">
                    <button type="button" 
                            id="cancel-confirmation"
                            class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-full transition-colors">
                        Tidak
                    </button>
                    <button type="button" 
                            id="proceed-confirmation"
                            class="flex-1 px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-full transition-colors">
                        Ya
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 backdrop-blur-sm transition-all duration-300" style="display: none;">
        <div class="bg-white rounded-[2rem] p-10 max-w-lg w-full mx-4 shadow-2xl transform transition-all scale-100 relative">
            <button type="button" id="close-success-modal" class="absolute top-6 right-6 text-gray-400 hover:text-gray-800 transition-colors">
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
                                id="view-receipt-btn"
                                class="px-4 py-3 bg-gray-50 text-blue-600 font-bold rounded-xl hover:bg-blue-50 transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm hover:shadow-md text-sm">
                            Lihat Bukti Transaksi
                        </button>
                        <button type="button" 
                                id="download-receipt-btn"
                                class="px-4 py-3 bg-gray-50 text-blue-600 font-bold rounded-xl hover:bg-blue-50 transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm hover:shadow-md text-sm">
                            Unduh Bukti Transaksi
                        </button>
                    </div>
                    <button type="button" 
                            id="view-activity-btn"
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

    /* Delivery Method Cards */
    .delivery-method-card {
        cursor: pointer !important;
        user-select: none;
    }
    
    .delivery-method-card.active {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .delivery-method-card:hover {
        transform: translateY(-2px);
    }

    /* Payment Method Buttons */
    .payment-method-btn.active,
    .payment-method-btn-jemput.active {
        background-color: #3b82f6;
        color: white;
    }

    .payment-method-btn:not(.active),
    .payment-method-btn-jemput:not(.active) {
        background-color: #e5e7eb;
        color: #374151;
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

    /* Fade-in Animation for Address Card */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fadeIn 0.6s ease-out forwards;
        opacity: 0;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        const pricePerUnit = {{ number_format($item->harga_sewa, 0, '.', '') }};
        const maxStock = {{ $item->stok }};
        
        // Helper to safely get element
        const getEl = (id) => document.getElementById(id);

        // Get all element references first
        const deliveryCards = document.querySelectorAll('.delivery-method-card');
        const deliveryMethodInput = getEl('delivery-method-input');
        const antarForm = getEl('antar-form');
        const jemputForm = getEl('jemput-form');
        const qtyDisplay = getEl('quantity-display');
        const hiddenQty = getEl('hidden-quantity');
        const decreaseBtn = getEl('decrease-qty');
        const increaseBtn = getEl('increase-qty');
        const qtyDisplayJemput = getEl('quantity-display-jemput');
        const decreaseBtnJemput = getEl('decrease-qty-jemput');
        const increaseBtnJemput = getEl('increase-qty-jemput');
        const startDate = getEl('start-date');
        const endDate = getEl('end-date');
        const daysCount = getEl('days-count');
        const startDateJemput = getEl('start-date-jemput');
        const endDateJemput = getEl('end-date-jemput');
        const daysCountJemput = getEl('days-count-jemput');

        // Define update functions FIRST before using them
        function updateTotals() {
            if (!qtyDisplay) return;
            const qty = parseInt(qtyDisplay.value) || 1;
            const subtotal = pricePerUnit * qty;
            const total = subtotal;
            
            const subtotalEl = getEl('subtotal');
            const totalTransferEl = getEl('total-amount-transfer');
            const totalCashEl = getEl('total-amount-cash');

            if (subtotalEl) subtotalEl.textContent = 'Rp. ' + subtotal.toLocaleString('id-ID');
            if (totalTransferEl) totalTransferEl.textContent = 'Rp. ' + total.toLocaleString('id-ID');
            if (totalCashEl) totalCashEl.textContent = 'Rp. ' + total.toLocaleString('id-ID');
        }

        function updateTotalsJemput() {
            if (!qtyDisplayJemput) return;
            const qty = parseInt(qtyDisplayJemput.value) || 1;
            const subtotal = pricePerUnit * qty;
            const total = subtotal;
            
            const subtotalEl = getEl('subtotal-jemput');
            const totalTransferEl = getEl('total-amount-transfer-jemput');
            const totalCashEl = getEl('total-amount-cash-jemput');

            if (subtotalEl) subtotalEl.textContent = 'Rp. ' + subtotal.toLocaleString('id-ID');
            if (totalTransferEl) totalTransferEl.textContent = 'Rp. ' + total.toLocaleString('id-ID');
            if (totalCashEl) totalCashEl.textContent = 'Rp. ' + total.toLocaleString('id-ID');
        }

        function calculateDays() {
            if (startDate && endDate && daysCount && startDate.value && endDate.value) {
                const start = new Date(startDate.value);
                const end = new Date(endDate.value);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                daysCount.textContent = diffDays;
                updateTotals();
            }
        }

        function calculateDaysJemput() {
            if (startDateJemput && endDateJemput && daysCountJemput && startDateJemput.value && endDateJemput.value) {
                const start = new Date(startDateJemput.value);
                const end = new Date(endDateJemput.value);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                daysCountJemput.textContent = diffDays;
                updateTotalsJemput();
            }
        }

        // NOW set up event listeners using the functions defined above
        // Delivery Method Toggle
        deliveryCards.forEach(card => {
            card.addEventListener('click', function() {
                const method = this.dataset.method;
                
                deliveryCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                deliveryMethodInput.value = method;
                
                if (method === 'antar') {
                    antarForm.classList.remove('hidden');
                    jemputForm.classList.add('hidden');
                    updateTotals();
                } else {
                    antarForm.classList.add('hidden');
                    jemputForm.classList.remove('hidden');
                    updateTotalsJemput();
                }
            });
        });

        // Quantity Controls for Antar
        if (qtyDisplay && hiddenQty && decreaseBtn && increaseBtn) {
            decreaseBtn.addEventListener('click', () => {
                let val = parseInt(qtyDisplay.value);
                if (val > 1) {
                    qtyDisplay.value = val - 1;
                    hiddenQty.value = val - 1;
                    updateTotals();
                }
            });

            increaseBtn.addEventListener('click', () => {
                let val = parseInt(qtyDisplay.value);
                if (val < maxStock) {
                    qtyDisplay.value = val + 1;
                    hiddenQty.value = val + 1;
                    updateTotals();
                }
            });

            qtyDisplay.addEventListener('change', () => {
                let val = parseInt(qtyDisplay.value) || 1;
                if (val < 1) val = 1;
                if (val > maxStock) val = maxStock;
                qtyDisplay.value = val;
                hiddenQty.value = val;
                updateTotals();
            });
        }

        // Quantity Controls for Jemput
        if (qtyDisplayJemput && decreaseBtnJemput && increaseBtnJemput) {
            decreaseBtnJemput.addEventListener('click', () => {
                let val = parseInt(qtyDisplayJemput.value);
                if (val > 1) {
                    qtyDisplayJemput.value = val - 1;
                    if (hiddenQty) hiddenQty.value = val - 1;
                    updateTotalsJemput();
                }
            });

            increaseBtnJemput.addEventListener('click', () => {
                let val = parseInt(qtyDisplayJemput.value);
                if (val < maxStock) {
                    qtyDisplayJemput.value = val + 1;
                    if (hiddenQty) hiddenQty.value = val + 1;
                    updateTotalsJemput();
                }
            });

            qtyDisplayJemput.addEventListener('change', () => {
                let val = parseInt(qtyDisplayJemput.value) || 1;
                if (val < 1) val = 1;
                if (val > maxStock) val = maxStock;
                qtyDisplayJemput.value = val;
                if (hiddenQty) hiddenQty.value = val;
                updateTotalsJemput();
            });
        }

        // Date Calculation for Antar
        if (startDate && endDate) {
            startDate.addEventListener('change', calculateDays);
            endDate.addEventListener('change', calculateDays);
        }

        // Date Calculation for Jemput
        if (startDateJemput && endDateJemput) {
            startDateJemput.addEventListener('change', calculateDaysJemput);
            endDateJemput.addEventListener('change', calculateDaysJemput);
        }

        // Payment Method Fixed to Tunai
        const paymentMethodHidden = getEl('payment-method-hidden');
        if(paymentMethodHidden) paymentMethodHidden.value = 'tunai';

        const cashPayment = getEl('cash-payment');
        if(cashPayment) cashPayment.classList.remove('hidden');

        // Removed Transfer Toggle and Logic


        // File Upload Preview
        const paymentProof = getEl('payment-proof');
        const fileName = getEl('file-name');

        if (paymentProof && fileName) {
            paymentProof.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileName.textContent = this.files[0].name;
                    fileName.classList.remove('italic');
                }
            });
        }

        const paymentProofJemput = getEl('payment-proof-jemput');
        const fileNameJemput = getEl('file-name-jemput');

        if (paymentProofJemput && fileNameJemput) {
            paymentProofJemput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileNameJemput.textContent = this.files[0].name;
                    fileNameJemput.classList.remove('italic');
                }
            });
        }

        // Smooth scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });

        // Initialize totals on load
        updateTotals();
        updateTotalsJemput();

        // ============================================
        // BOOKING CONFIRMATION SYSTEM
        // ============================================
        
        const confirmationModal = document.getElementById('confirmation-modal');
        const successModal = document.getElementById('success-modal');
        const confirmBookingBtns = document.querySelectorAll('.confirm-action-btn');
        const cancelConfirmation = document.getElementById('cancel-confirmation');
        const proceedConfirmation = document.getElementById('proceed-confirmation');
        const closeSuccessModal = document.getElementById('close-success-modal');
        const viewReceiptBtn = document.getElementById('view-receipt-btn');
        const downloadReceiptBtn = document.getElementById('download-receipt-btn');
        const viewActivityBtn = document.getElementById('view-activity-btn');
        const bookingForm = getEl('booking-form');

        let receiptId = null;

        // Show confirmation modal on button click
        if (confirmBookingBtns.length > 0) {
            confirmBookingBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Validate required fields
                    const deliveryMethod = deliveryMethodInput ? deliveryMethodInput.value : 'antar';
                    let isValid = true;
                    let errorMessage = '';

                    if (deliveryMethod === 'antar') {
                        const recipientName = getEl('recipient-name')?.value;
                        const deliveryAddress = getEl('delivery-address')?.value;
                        const startDateVal = startDate?.value;
                        const endDateVal = endDate?.value;
                        const rentalPurpose = getEl('rental-purpose')?.value;

                        if (!recipientName || !deliveryAddress || !startDateVal || !endDateVal || !rentalPurpose) {
                            isValid = false;
                            errorMessage = 'Mohon lengkapi semua field yang wajib diisi (Nama, Alamat, Tujuan Sewa, Tanggal)';
                        }
                    } else {
                        const startDateVal = startDateJemput?.value;
                        const endDateVal = endDateJemput?.value;
                        const rentalPurposeJemput = getEl('rental-purpose-jemput')?.value;
                        const recipientNameJemput = getEl('recipient-name-jemput')?.value;
                        const recipientAddressJemput = getEl('delivery-address-jemput')?.value;

                        if (!startDateVal || !endDateVal || !rentalPurposeJemput || !recipientNameJemput || !recipientAddressJemput) {
                            isValid = false;
                            errorMessage = 'Mohon lengkapi Nama Penyewa, Alamat, Tanggal Mulai, Tanggal Selesai, dan Tujuan Sewa';
                        }
                    }

                    if (!isValid) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Data Belum Lengkap',
                            text: errorMessage,
                            confirmButtonColor: '#3085d6',
                        });
                        return;
                    }

                    // Show confirmation modal
                    confirmationModal.style.display = 'flex';
                    confirmationModal.classList.remove('hidden');
                });
            });
        }

        // Cancel confirmation
        if (cancelConfirmation) {
            cancelConfirmation.addEventListener('click', function() {
                confirmationModal.style.display = 'none';
                confirmationModal.classList.add('hidden');
            });
        }

        // Proceed with booking
        if (proceedConfirmation) {
            proceedConfirmation.addEventListener('click', function() {
                // Hide confirmation modal
                confirmationModal.style.display = 'none';
                confirmationModal.classList.add('hidden');

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

                // Copy jemput data to main form if jemput method is selected
                const deliveryMethod = deliveryMethodInput ? deliveryMethodInput.value : 'antar';
                if (deliveryMethod === 'jemput') {
                    // Copy dates
                    if (startDateJemput && startDate) startDate.value = startDateJemput.value;
                    if (endDateJemput && endDate) endDate.value = endDateJemput.value;

                    // Copy Rental Purpose
                    const rentalPurpose = getEl('rental-purpose');
                    const rentalPurposeJemput = getEl('rental-purpose-jemput');
                    if (rentalPurposeJemput && rentalPurpose) rentalPurpose.value = rentalPurposeJemput.value;

                    // Copy Recipient Name (Nama Penyewa)
                    const recipientName = getEl('recipient-name');
                    const recipientNameJemput = getEl('recipient-name-jemput');
                    if (recipientNameJemput && recipientName) recipientName.value = recipientNameJemput.value;

                    // Copy Address (Alamat Lengkap)
                    const deliveryAddress = getEl('delivery-address');
                    const deliveryAddressJemput = getEl('delivery-address-jemput');
                    if (deliveryAddressJemput && deliveryAddress) deliveryAddress.value = deliveryAddressJemput.value;
                    
                    // Copy payment proof file if exists
                    const paymentProofJemput = getEl('payment-proof-jemput');
                    if (paymentProofJemput?.files[0] && paymentProof) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(paymentProofJemput.files[0]);
                        paymentProof.files = dataTransfer.files;
                    }
                }

                // Submit form via AJAX
                const formData = new FormData(bookingForm);

                fetch('{{ route("rental.booking.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    Swal.close(); // Close loading

                    // Handle 401 Unauthorized - trigger login modal
                    if (response.status === 401) {
                        return response.json().then(data => {
                            // Open login modal using the existing modal system
                            const overlay = document.getElementById('auth-modal-overlay');
                            const modalLogin = document.getElementById('modal-login');
                            
                            if (overlay && modalLogin) {
                                document.querySelectorAll('.modal-content').forEach(m => {
                                    m.classList.add('hidden');
                                    m.classList.remove('scale-100', 'opacity-100');
                                });

                                overlay.classList.remove('hidden');
                                setTimeout(() => {
                                    overlay.classList.add('show');
                                    modalLogin.classList.remove('hidden');
                                    setTimeout(() => {
                                        modalLogin.classList.add('scale-100', 'opacity-100');
                                    }, 50);
                                }, 10);
                            }
                            
                            throw new Error(data.message || 'Anda harus login terlebih dahulu');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        receiptId = data.receipt_id;
                        
                        successModal.style.display = 'flex';
                        successModal.classList.remove('hidden');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Terjadi kesalahan saat memproses pesanan',
                            confirmButtonColor: '#d33',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: 'Terjadi kesalahan sistem saat memproses pesanan',
                        confirmButtonColor: '#d33',
                    });
                });
            });
        }


        // View receipt
        if (viewReceiptBtn) {
            viewReceiptBtn.addEventListener('click', function() {
                if (receiptId) {
                    window.open(`/receipt/rental/${receiptId}/view`, '_blank');
                }
            });
        }

        // Download receipt
        if (downloadReceiptBtn) {
            downloadReceiptBtn.addEventListener('click', function() {
                if (receiptId) {
                    window.location.href = `/receipt/rental/${receiptId}/download`;
                }
            });
        }

        // View activity
        if (viewActivityBtn) {
            viewActivityBtn.addEventListener('click', function() {
                window.location.href = '{{ route("user.activity") }}';
            });
        }

    });
</script>

@endpush
