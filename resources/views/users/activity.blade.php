@extends('layouts.user')

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
                    Aktivitas
                </h1>
            </div>

            <!-- Menu Pilihan -->
            <div class="flex flex-col sm:flex-row justify-center gap-6 mb-10 items-center">
                <!-- Penyewaan Card -->
                <div class="activity-menu-card active cursor-pointer" data-type="rental">
                    <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 w-48 text-center border-4 border-transparent">
                        <div class="mb-3 flex justify-center">
                            <img src="{{ asset('User/img/elemen/F1.png') }}" alt="Penyewaan" class="w-16 h-16 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800">Penyewaan</p>
                    </div>
                </div>

                <!-- Pesanan Gas Card -->
                <div class="activity-menu-card cursor-pointer" data-type="gas">
                    <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 w-48 text-center border-4 border-transparent">
                        <div class="mb-3 flex justify-center">
                            <img src="{{ asset('User/img/elemen/F2.png') }}" alt="Pesanan Gas" class="w-16 h-16 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800">Pesanan Gas</p>
                    </div>
                </div>
            </div>

            <!-- Clear History Buttons (Moved to Top) -->
            <div class="flex justify-center mt-4 mb-8">
                <button type="button" 
                        class="clear-history-btn bg-red-100 text-red-600 px-6 py-2 rounded-full font-semibold hover:bg-red-200 transition-colors"
                        id="clear-rental-btn"
                        data-type="rental"
                        style="display: block;">
                    <i class="fas fa-trash-alt mr-2"></i>Bersihkan Riwayat Penyewaan
                </button>
                <button type="button" 
                        class="clear-history-btn bg-red-100 text-red-600 px-6 py-2 rounded-full font-semibold hover:bg-red-200 transition-colors hidden"
                        id="clear-gas-btn"
                        data-type="gas">
                    <i class="fas fa-trash-alt mr-2"></i>Bersihkan Riwayat Pesanan Gas
                </button>
            </div>

            <!-- Bagian Pesanan Sewa -->
            <div id="rental-section" class="activity-section space-y-6">
                @forelse($rentalBookings as $booking)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <!-- Konten Kartu Utama -->
                    <div class="p-6">
                        <div class="flex gap-6">
                            <!-- Gambar Produk -->
                            @if($booking->barang && $booking->barang->foto)
                            <img src="{{ asset('storage/' . $booking->barang->foto) }}" 
                                 alt="{{ $booking->barang->nama_barang }}" 
                                 class="w-32 h-32 object-cover rounded-lg flex-shrink-0"
                                 onerror="this.src='{{ asset('User/img/elemen/F1.png') }}'">
                            @else
                            <div class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <img src="{{ asset('User/img/elemen/F1.png') }}" alt="Rental" class="w-16 h-16 object-contain">
                            </div>
                            @endif
                            
                            <div class="flex-1">
                                <!-- Nama Produk -->
                                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $booking->barang->nama_barang }}</h3>
                                
                                <!-- Date and Time -->
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ \Carbon\Carbon::parse($booking->created_at)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                </p>
                                
                                <!-- Total Units -->
                                <p class="text-sm text-gray-600 mb-2">Total {{ $booking->quantity }} Unit</p>
                                
                                <!-- Location -->
                                @if($setting && $setting->location_name)
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                    <a href="https://maps.app.goo.gl/LE5JRcccSP6EjpZ37" 
                                       target="_blank" 
                                       class="text-sm text-red-600 hover:underline">
                                        {{ $setting->location_name }}
                                    </a>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Right Side: Status and Payment -->
                            <div class="text-right">
                                <!-- Status Badge -->
                                <div class="flex items-center justify-end gap-2 mb-3">
                                    <span class="text-sm font-semibold">Status Penyewaan</span>
                                    @php
                                        $statusConfig = [
                                            'completed' => ['text' => 'Selesai', 'color' => 'text-green-600', 'dot' => 'bg-green-600'],
                                            'resolved' => ['text' => 'Selesai', 'color' => 'text-green-600', 'dot' => 'bg-green-600'],
                                            'pending' => ['text' => 'Di Proses', 'color' => 'text-yellow-600', 'dot' => 'bg-yellow-600'],
                                            'paid' => ['text' => 'Sudah Bayar', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'confirmed' => ['text' => 'Dikonfirmasi', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'approved' => ['text' => 'Disetujui', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'being_prepared' => ['text' => 'Dipersiapkan', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'in_delivery' => ['text' => 'Dalam Pengiriman', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'arrived' => ['text' => 'Sedang Disewa', 'color' => 'text-green-600', 'dot' => 'bg-green-600'],
                                            'cancelled' => ['text' => 'Dibatalkan', 'color' => 'text-red-600', 'dot' => 'bg-red-600'],
                                            'rejected' => ['text' => 'Ditolak', 'color' => 'text-red-600', 'dot' => 'bg-red-600'],
                                            'returned' => ['text' => 'Dikembalikan', 'color' => 'text-green-600', 'dot' => 'bg-green-600'],
                                        ];
                                        $status = $statusConfig[$booking->status] ?? ['text' => ucfirst($booking->status), 'color' => 'text-gray-600', 'dot' => 'bg-gray-600'];
                                        
                                        // Override status if cancellation is pending
                                        if ($booking->cancellation_status === 'pending') {
                                            $status = ['text' => 'Permintaan Pembatalan', 'color' => 'text-yellow-600', 'dot' => 'bg-yellow-600'];
                                        }
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $status['dot'] }}"></span>
                                        <span class="{{ $status['color'] }} font-semibold">{{ $status['text'] }}</span>
                                    </div>
                                </div>
                                
                                <!-- Payment Method -->
                                <p class="text-sm text-gray-600 mb-2">
                                    @if($booking->payment_method == 'tunai')
                                        Pembayaran Tunai
                                    @else
                                        Transfer - {{ $setting->bank_name ?? 'Bank' }}
                                    @endif
                                </p>
                                
                                <!-- Amount -->
                                <p class="text-2xl font-bold text-red-600 mb-4">{{ $booking->formatted_total }}</p>
                                
                                <!-- View Details Button -->
                                <button type="button" 
                                        class="toggle-detail-btn px-6 py-2 border-2 border-blue-500 text-blue-500 rounded-lg font-semibold hover:bg-blue-50 transition-colors"
                                        data-target="rental-detail-{{ $booking->id }}">
                                    Lihat Selengkapnya
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Bagian Detail yang Dapat Diperluas -->
                    <div id="rental-detail-{{ $booking->id }}" class="detail-section hidden border-t border-gray-200">
                        <div class="p-6 bg-gray-50">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Left Column -->
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">No. Pesanan</p>
                                        <p class="font-semibold text-gray-800">{{ $booking->order_number ?? '-' }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Waktu Pemesanan</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($booking->created_at)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                        </p>
                                    </div>
                                    
                                    @if($booking->delivery_time)
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Waktu Pengiriman</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($booking->delivery_time)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                        </p>
                                    </div>
                                    @endif
                                    
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Waktu Penyewaan</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($booking->start_date)->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}
                                        </p>
                                    </div>
                                    
                                    @if($booking->return_time)
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Waktu Pengembalian</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($booking->return_time)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                        </p>
                                    </div>
                                    @endif
                                    
                                    @if($booking->completion_time)
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Waktu Pemesanan Selesai</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($booking->completion_time)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                        </p>
                                    </div>
                                    @endif
                                </div>



                                <!-- Right Column -->
                                <div class="space-y-4">
                                    <!-- Bukti Transaksi -->
                                    @if($booking->payment_proof)
                                    <div>
                                        <p class="text-sm text-gray-500 mb-2">Bukti Transaksi</p>
                                        <div class="flex gap-2">
                                            <a href="{{ asset('storage/' . $booking->payment_proof) }}" 
                                               target="_blank"
                                               class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-colors">
                                                Lihat Bukti Transaksi
                                            </a>
                                            <a href="{{ asset('storage/' . $booking->payment_proof) }}" 
                                               download
                                               class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-semibold hover:bg-blue-600 transition-colors">
                                                Unduh Bukti Transaksi
                                            </a>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Auto-Generated Receipt -->
                                    <div>
                                        <p class="text-sm text-gray-500 mb-2">Bukti Transaksi Resmi</p>
                                        @if($booking->receipt_path && Storage::disk('public')->exists($booking->receipt_path))
                                        <div class="flex gap-2">
                                            <a href="{{ route('receipt.rental.view', $booking->id) }}" 
                                               target="_blank"
                                               class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg text-sm font-semibold hover:from-blue-600 hover:to-blue-700 transition-all shadow-md hover:shadow-lg">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Lihat Bukti
                                            </a>
                                            <a href="{{ route('receipt.rental.download', $booking->id) }}" 
                                               class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-semibold hover:from-green-600 hover:to-green-700 transition-all shadow-md hover:shadow-lg">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                Unduh Bukti
                                            </a>
                                        </div>
                                        @else
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                            <p class="text-xs text-yellow-700">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                                Bukti transaksi sedang diproses...
                                            </p>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Delivery Status Timeline -->
                                    @if($booking->status != 'pending' && $booking->status != 'cancelled')
                                    <div>
                                        <p class="text-sm text-gray-500 mb-3">Kondisi Pesanan Sewa Tiba</p>
                                        <div class="space-y-3">
                                            @php
                                                if ($booking->delivery_method == 'jemput') {
                                                    $timeline = [
                                                        ['status' => 'confirmed', 'label' => 'Pesanan dikonfirmasi', 'time' => $booking->confirmed_at],
                                                        ['status' => 'arrived', 'label' => 'Pesanan sudah di ambil oleh penyewa', 'time' => $booking->arrival_time],
                                                    ];
                                                } else {
                                                    $timeline = [
                                                        ['status' => 'confirmed', 'label' => 'Pesanan dikonfirmasi', 'time' => $booking->confirmed_at],
                                                        ['status' => 'being_prepared', 'label' => 'Pesanan sedang dipersiapkan', 'time' => null],
                                                        ['status' => 'in_delivery', 'label' => 'Pesanan dalam proses pengantaran', 'time' => $booking->delivery_time],
                                                        ['status' => 'arrived', 'label' => 'Pesanan tiba di alamat tujuan', 'time' => $booking->arrival_time],
                                                    ];
                                                }
                                                
                                                $currentStatusIndex = array_search($booking->status, array_column($timeline, 'status'));
                                                if ($booking->status === 'completed') {
                                                    $currentStatusIndex = count($timeline) - 1;
                                                }
                                                // Handle false return if search fails (and not completed)
                                                if ($currentStatusIndex === false) $currentStatusIndex = 0;
                                            @endphp
                                            
                                            @foreach($timeline as $index => $item)
                                            <div class="flex items-start gap-3">
                                                <div class="flex flex-col items-center">
                                                    @if($index <= $currentStatusIndex)
                                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                                    @else
                                                    <div class="w-3 h-3 rounded-full bg-gray-300"></div>
                                                    @endif
                                                    @if($index < count($timeline) - 1)
                                                    <div class="w-0.5 h-8 {{ $index < $currentStatusIndex ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 {{ $index <= $currentStatusIndex ? 'text-gray-800' : 'text-gray-400' }}">
                                                    <p class="text-sm font-semibold">{{ $item['label'] }}</p>
                                                    @if($item['time'])
                                                    <p class="text-xs text-gray-500">
                                                        {{ \Carbon\Carbon::parse($item['time'])->locale('id')->isoFormat('DD MMM YYYY HH:mm') }} WIB
                                                    </p>
                                                    @endif
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Delivery Proof -->
                                    @if($booking->delivery_proof_image)
                                    <div>
                                        <p class="text-sm text-gray-500 mb-2">{{ $booking->delivery_method == 'jemput' ? 'Bukti Penjemputan' : 'Bukti Pengiriman' }}</p>
                                        <a href="{{ asset('storage/' . $booking->delivery_proof_image) }}" 
                                           target="_blank"
                                           class="inline-block px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-semibold hover:bg-green-600 transition-colors">
                                            {{ $booking->delivery_method == 'jemput' ? 'Lihat Bukti Penjemputan' : 'Lihat Bukti Pengiriman' }}
                                        </a>
                                    </div>
                                    @endif

                                    <!-- Cancellation Request -->
                                    @if($booking->canBeCancelled())
                                    <div class="pt-4 border-t border-gray-200">
                                        <button type="button" 
                                                class="cancel-order-btn w-full px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-semibold hover:bg-red-600 transition-colors"
                                                data-type="rental"
                                                data-id="{{ $booking->id }}">
                                            Batalkan Pesanan
                                        </button>
                                    </div>
                                    @elseif($booking->hasCancellationRequest())
                                    <div class="pt-4 border-t border-gray-200">
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                            <p class="text-sm font-semibold text-yellow-800 mb-1">Permintaan Pembatalan Diajukan</p>
                                            <p class="text-xs text-yellow-700">Menunggu konfirmasi admin</p>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Delete History Function -->
                                    @if(in_array($booking->status, ['cancelled', 'rejected']))
                                    <div class="pt-4 border-t border-gray-200">
                                        <button type="button" 
                                                class="delete-order-btn w-full px-4 py-2 border-2 border-red-500 text-red-500 rounded-lg text-sm font-semibold hover:bg-red-50 transition-colors"
                                                data-type="rental"
                                                data-id="{{ $booking->id }}">
                                            <i class="fas fa-trash-alt mr-2"></i>Hapus Riwayat
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                    <p class="text-gray-500">Belum ada riwayat penyewaan</p>
                </div>
                @endforelse
            </div>

            <!-- Gas Orders Section -->
            <div id="gas-section" class="activity-section space-y-6 hidden">
                @forelse($gasOrders as $order)
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <!-- Main Card Content -->
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row gap-6">
                            <!-- Product Image -->
                            @if($order->gas && $order->gas->foto)
                            <img src="{{ asset('storage/' . $order->gas->foto) }}" 
                                 alt="{{ $order->item_name }}" 
                                 class="w-full sm:w-32 h-48 sm:h-32 object-cover rounded-lg flex-shrink-0"
                                 onerror="this.src='{{ asset('User/img/elemen/F2.png') }}'">
                            @else
                            <div class="w-full sm:w-32 h-48 sm:h-32 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <img src="{{ asset('User/img/elemen/F2.png') }}" alt="Gas" class="w-16 h-16 object-contain">
                            </div>
                            @endif
                            
                            <div class="flex-1">
                                <!-- Product Name -->
                                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $order->item_name }}</h3>
                                
                                <!-- Date and Time -->
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ \Carbon\Carbon::parse($order->created_at)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                </p>
                                
                                <!-- Total Units -->
                                <p class="text-sm text-gray-600 mb-2">Total {{ $order->quantity }} Unit</p>
                                
                                <!-- Location -->
                                @if($setting && $setting->location_name)
                                <div class="flex items-center gap-2 mb-3">
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                    <a href="https://maps.app.goo.gl/LE5JRcccSP6EjpZ37" 
                                       target="_blank" 
                                       class="text-sm text-red-600 hover:underline">
                                        {{ $setting->location_name }}
                                    </a>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Right Side: Status and Payment -->
                            <div class="text-left sm:text-right mt-4 sm:mt-0">
                                <!-- Status Badge -->
                                <div class="flex items-center justify-start sm:justify-end gap-2 mb-3">
                                    <span class="text-sm font-semibold">Status Pembelian</span>
                                    @php
                                        $statusConfig = [
                                            'completed' => ['text' => 'Selesai', 'color' => 'text-green-600', 'dot' => 'bg-green-600'],
                                            'resolved' => ['text' => 'Selesai', 'color' => 'text-green-600', 'dot' => 'bg-green-600'],
                                            'pending' => ['text' => 'Di Proses', 'color' => 'text-yellow-600', 'dot' => 'bg-yellow-600'],
                                            'paid' => ['text' => 'Sudah Bayar', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'confirmed' => ['text' => 'Dikonfirmasi', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'approved' => ['text' => 'Disetujui', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'being_prepared' => ['text' => 'Dipersiapkan', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'in_delivery' => ['text' => 'Dalam Pengiriman', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'arrived' => ['text' => 'Tiba', 'color' => 'text-green-600', 'dot' => 'bg-green-600'],
                                            'cancelled' => ['text' => 'Dibatalkan', 'color' => 'text-red-600', 'dot' => 'bg-red-600'],
                                            'rejected' => ['text' => 'Ditolak', 'color' => 'text-red-600', 'dot' => 'bg-red-600'],
                                        ];
                                        $status = $statusConfig[$order->status] ?? ['text' => ucfirst($order->status), 'color' => 'text-gray-600', 'dot' => 'bg-gray-600'];
                                        
                                        // Override status if cancellation is pending
                                        if ($order->cancellation_status === 'pending') {
                                            $status = ['text' => 'Permintaan Pembatalan', 'color' => 'text-yellow-600', 'dot' => 'bg-yellow-600'];
                                        }
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $status['dot'] }}"></span>
                                        <span class="{{ $status['color'] }} font-semibold">{{ $status['text'] }}</span>
                                    </div>
                                </div>
                                
                                <!-- Payment Method -->
                                <p class="text-sm text-gray-600 mb-2">
                                    @if($order->payment_method == 'Tunai')
                                        Pembayaran Tunai
                                    @else
                                        Transfer - {{ $setting->bank_name ?? 'Bank' }}
                                    @endif
                                </p>
                                
                                <!-- Amount -->
                                <p class="text-2xl font-bold text-red-600 mb-4">{{ $order->formatted_total }}</p>
                                
                                <!-- View Details Button -->
                                <button type="button" 
                                        class="toggle-detail-btn px-6 py-2 border-2 border-blue-500 text-blue-500 rounded-lg font-semibold hover:bg-blue-50 transition-colors"
                                        data-target="gas-detail-{{ $order->id }}">
                                    Lihat Selengkapnya
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Expandable Detail Section -->
                    <div id="gas-detail-{{ $order->id }}" class="detail-section hidden border-t border-gray-200">
                        <div class="p-6 bg-gray-50">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Left Column -->
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">No. Pesanan</p>
                                        <p class="font-semibold text-gray-800">{{ $order->order_number ?? '-' }}</p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Waktu Pemesanan</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($order->created_at)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                        </p>
                                    </div>
                                    
                                    @if($order->completion_time)
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Waktu Pengambilan</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($order->completion_time)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                        </p>
                                    </div>
                                    @endif
                                    
                                    @if($order->confirmed_at)
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Waktu Pembayaran</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($order->confirmed_at)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                        </p>
                                    </div>
                                    @endif
                                    
                                    @if($order->completion_time)
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Waktu Pemesanan Selesai</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ \Carbon\Carbon::parse($order->completion_time)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                        </p>
                                    </div>
                                    @endif
                                </div>



                                <!-- Right Column -->
                                <div class="space-y-4">
                                    <!-- Transaction Receipt -->
                                    @if($order->proof_of_payment)
                                    <div>
                                        <p class="text-sm text-gray-500 mb-2">Bukti Transaksi</p>
                                        <div class="flex gap-2">
                                            <a href="{{ asset('storage/' . $order->proof_of_payment) }}" 
                                               target="_blank"
                                               class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold hover:bg-gray-50 transition-colors">
                                                Lihat Bukti Transaksi
                                            </a>
                                            <a href="{{ asset('storage/' . $order->proof_of_payment) }}" 
                                               download
                                               class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-semibold hover:bg-blue-600 transition-colors">
                                                Unduh Bukti Transaksi
                                            </a>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Auto-Generated Receipt -->
                                    <div>
                                        <p class="text-sm text-gray-500 mb-2">Bukti Transaksi Resmi</p>
                                        @if($order->receipt_path && Storage::disk('public')->exists($order->receipt_path))
                                        <div class="flex gap-2">
                                            <a href="{{ route('receipt.gas.view', $order->id) }}" 
                                               target="_blank"
                                               class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg text-sm font-semibold hover:from-blue-600 hover:to-blue-700 transition-all shadow-md hover:shadow-lg">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                Lihat Bukti
                                            </a>
                                            <a href="{{ route('receipt.gas.download', $order->id) }}" 
                                               class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-semibold hover:from-green-600 hover:to-green-700 transition-all shadow-md hover:shadow-lg">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                Unduh Bukti
                                            </a>
                                        </div>
                                        @else
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                            <p class="text-xs text-yellow-700">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                                Bukti transaksi sedang diproses...
                                            </p>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Cancellation Request -->
                                    @if($order->canBeCancelled())
                                    <div class="pt-4 border-t border-gray-200">
                                        <button type="button" 
                                                class="cancel-order-btn w-full px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-semibold hover:bg-red-600 transition-colors"
                                                data-type="gas"
                                                data-id="{{ $order->id }}">
                                            Batalkan Pesanan
                                        </button>
                                    </div>
                                    @elseif($order->hasCancellationRequest())
                                    <div class="pt-4 border-t border-gray-200">
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                            <p class="text-sm font-semibold text-yellow-800 mb-1">Permintaan Pembatalan Diajukan</p>
                                            <p class="text-xs text-yellow-700">Menunggu konfirmasi admin</p>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Delete History Function -->
                                    @if(in_array($order->status, ['cancelled', 'rejected']))
                                    <div class="pt-4 border-t border-gray-200">
                                        <button type="button" 
                                                class="delete-order-btn w-full px-4 py-2 border-2 border-red-500 text-red-500 rounded-lg text-sm font-semibold hover:bg-red-50 transition-colors"
                                                data-type="gas"
                                                data-id="{{ $order->id }}">
                                            <i class="fas fa-trash-alt mr-2"></i>Hapus Riwayat
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                    <p class="text-gray-500">Belum ada riwayat pembelian gas</p>
                </div>
                @endforelse
            </div>

</main>
@endsection

@push('styles')
<style>
    * {
        font-family: 'Inter', sans-serif;
    }

    /* Activity Menu Cards */
    .activity-menu-card.active > div {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Detail Section Animation */
    .detail-section {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }

    .detail-section.show {
        max-height: 2000px;
        transition: max-height 0.5s ease-in;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';

        // Activity Menu Toggle
        const menuCards = document.querySelectorAll('.activity-menu-card');
        const rentalSection = document.getElementById('rental-section');
        const gasSection = document.getElementById('gas-section');
        const clearRentalBtn = document.getElementById('clear-rental-btn');
        const clearGasBtn = document.getElementById('clear-gas-btn');

        // Initial State
        const activeCard = document.querySelector('.activity-menu-card.active');
        // No complex logic needed here because HTML sets default state correctly (Rental visible, Gas hidden)
        // Just verify if we are on Gas tab (edge case)
        if (activeCard && activeCard.dataset.type === 'gas') {
             if(clearRentalBtn) {
                 clearRentalBtn.classList.add('hidden');
                 clearRentalBtn.style.display = 'none'; // Override inline style
             }
             if(clearGasBtn) {
                 clearGasBtn.classList.remove('hidden');
                 clearGasBtn.style.display = 'block';
             }
        }

        menuCards.forEach(card => {
            card.addEventListener('click', () => {
                const type = card.dataset.type;
                
                // Update active state
                menuCards.forEach(c => c.classList.remove('active'));
                card.classList.add('active');
                
                // Toggle sections & buttons
                if (type === 'rental') {
                    rentalSection.classList.remove('hidden');
                    gasSection.classList.add('hidden');
                    if(clearRentalBtn) {
                        clearRentalBtn.classList.remove('hidden');
                        clearRentalBtn.style.display = 'block';
                    }
                    if(clearGasBtn) {
                        clearGasBtn.classList.add('hidden');
                        clearGasBtn.style.display = 'none';
                    }
                } else {
                    rentalSection.classList.add('hidden');
                    gasSection.classList.remove('hidden');
                    if(clearRentalBtn) {
                        clearRentalBtn.classList.add('hidden');
                        clearRentalBtn.style.display = 'none';
                    }
                    if(clearGasBtn) {
                        clearGasBtn.classList.remove('hidden');
                        clearGasBtn.style.display = 'block';
                    }
                }
            });
        });

        // Toggle Detail Dropdown
        const toggleButtons = document.querySelectorAll('.toggle-detail-btn');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.dataset.target;
                const detailSection = document.getElementById(targetId);
                
                if (detailSection.classList.contains('hidden')) {
                    // Show detail
                    detailSection.classList.remove('hidden');
                    setTimeout(() => {
                        detailSection.classList.add('show');
                    }, 10);
                    button.textContent = 'Tutup';
                } else {
                    // Hide detail
                    detailSection.classList.remove('show');
                    setTimeout(() => {
                        detailSection.classList.add('hidden');
                    }, 300);
                    button.textContent = 'Lihat Selengkapnya';
                }
            });
        });

        // Cancel Order
        const cancelButtons = document.querySelectorAll('.cancel-order-btn');
        
        cancelButtons.forEach(button => {
            button.addEventListener('click', async () => {
                const type = button.dataset.type;
                const id = button.dataset.id;
                
                const { value: reason } = await Swal.fire({
                    title: 'Batalkan Pesanan',
                    html: '<p class="mb-3">Berikan alasan pembatalan pesanan:</p>',
                    input: 'textarea',
                    inputPlaceholder: 'Masukkan alasan pembatalan...',
                    inputAttributes: {
                        'aria-label': 'Alasan pembatalan',
                        'rows': 4
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Kirim Permintaan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Alasan pembatalan harus diisi!';
                        }
                        if (value.length < 10) {
                            return 'Alasan minimal 10 karakter!';
                        }
                    }
                });

                if (reason) {
                    // Show Loading
                    Swal.fire({
                        title: 'Sedang Memproses...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                             Swal.showLoading();
                        }
                    });

                    try {
                        const response = await fetch(`/aktivitas/${type}/${id}/cancel`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ reason })
                        });

                        const data = await response.json();

                        if (data.success) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#3b82f6'
                            });
                            location.reload();
                        } else {
                            await Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    } catch (error) {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan. Silakan coba lagi.',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                }
            });
        });

        // Delete Single Order History (Existing)
        const deleteButtons = document.querySelectorAll('.delete-order-btn');
        deleteButtons.forEach(button => {
             button.addEventListener('click', async () => {
                const type = button.dataset.type;
                const id = button.dataset.id;
                
                const result = await Swal.fire({
                    title: 'Hapus Riwayat?',
                    text: "Riwayat pesanan ini akan dihapus dari daftar.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280'
                });

                if (result.isConfirmed) {
                    // Show Loading
                    Swal.fire({
                        title: 'Sedang Menghapus...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await fetch(`/aktivitas/${type}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#3b82f6'
                            });
                            location.reload();
                        } else {
                            await Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: data.message,
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    } catch (error) {
                         await Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat menghapus.',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                }
            });
        });

        // Clear All History (New Feature)
        const clearHistoryButtons = document.querySelectorAll('.clear-history-btn');
        clearHistoryButtons.forEach(button => {
            button.addEventListener('click', async () => {
                const type = button.dataset.type;
                const typeText = type === 'rental' ? 'Penyewaan' : 'Gas';

                const result = await Swal.fire({
                    title: `Bersihkan Riwayat ${typeText}?`,
                    text: "Semua riwayat dengan status Selesai, Dibatalkan, atau Ditolak akan dihapus.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Bersihkan',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#6b7280'
                });

                if (result.isConfirmed) {
                     Swal.fire({
                        title: 'Sedang Membersihkan...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await fetch(`/aktivitas/clear-all/${type}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#3b82f6'
                            });
                            location.reload();
                        } else {
                             await Swal.fire({
                                icon: 'info',
                                title: 'Info',
                                text: data.message,
                                confirmButtonColor: '#3b82f6'
                            });
                        }
                    } catch (error) {
                        await Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Terjadi kesalahan saat membersihkan riwayat.',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                }
            });
        });
    });
</script>
@endpush
