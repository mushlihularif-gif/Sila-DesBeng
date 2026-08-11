@forelse($gasOrders as $order)
                <div class="transaction-card bg-white rounded-2xl shadow-lg overflow-hidden" data-status="{{ $order->status }}">
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
                                            'pending' => ['text' => 'Menunggu Konfirmasi', 'color' => 'text-yellow-600', 'dot' => 'bg-yellow-600'],
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
                                        class="toggle-detail-btn px-6 py-2.5 border-2 border-blue-500 text-blue-500 rounded-lg font-semibold hover:bg-blue-50 active:scale-95 hover:shadow-md transition-all relative z-10 cursor-pointer w-full sm:w-auto"
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
                                            @if($order->status !== 'pending')
                                            <a href="{{ route('receipt.gas.download', $order->id) }}" 
                                               class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg text-sm font-semibold hover:from-green-600 hover:to-green-700 transition-all shadow-md hover:shadow-lg">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                                Unduh Bukti
                                            </a>
                                            @endif
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

                                    <!-- Pending Payment Actions -->
                                    @if($order->status === 'pending' && strtolower($order->payment_method) !== 'tunai')
                                    <div class="pt-4 border-t border-gray-200 mb-2 space-y-2">
                                        <a href="{{ route('user.gas.payment', $order->id) }}" class="block w-full px-4 py-2 bg-orange-500 text-white text-center rounded-lg text-sm font-semibold hover:bg-orange-600 transition-colors shadow-sm">
                                            Bayar Sekarang
                                        </a>
                                        <button type="button" 
                                                class="w-full px-4 py-2 border border-blue-500 text-blue-500 rounded-lg text-sm font-semibold hover:bg-blue-50 transition-colors"
                                                onclick="openChangeMethodModal({{ $order->id }}, '{{ $order->payment_channel }}')">
                                            <i class="fas fa-exchange-alt mr-1"></i>Ubah Metode Pembayaran
                                        </button>
                                    </div>
                                    @endif

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
                @if($gasOrders->currentPage() == 1)
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                    <p class="text-gray-500">Belum ada riwayat pembelian gas</p>
                </div>
                @endif
                @endforelse
