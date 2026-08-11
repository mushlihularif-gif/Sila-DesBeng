@forelse($pasarOrders as $order)
                <div class="transaction-card bg-white rounded-2xl shadow-lg overflow-hidden" data-status="{{ $order->status }}">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row gap-6">
                            <!-- Product Image (First item) -->
                            @php $firstItem = $order->items->first(); @endphp
                            @if($firstItem && $firstItem->produk && $firstItem->produk->foto)
                            <img src="{{ asset('storage/' . $firstItem->produk->foto) }}" 
                                 alt="{{ $firstItem->product_name }}" 
                                 class="w-full sm:w-32 h-48 sm:h-32 object-cover rounded-lg flex-shrink-0"
                                 onerror="this.src='{{ asset('User/img/elemen/pasar.png') }}'">
                            @else
                            <div class="w-full sm:w-32 h-48 sm:h-32 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-store text-4xl text-gray-400"></i>
                            </div>
                            @endif
                            
                            <div class="flex-1">
                                <!-- Info -->
                                <h3 class="text-xl font-bold text-gray-800 mb-2">Pesanan Pasar #{{ $order->order_number }}</h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    {{ \Carbon\Carbon::parse($order->created_at)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                                </p>
                                <p class="text-sm text-gray-600 mb-2">
                                    Total {{ $order->items->sum('quantity') }} Produk
                                </p>
                            </div>
                            
                            <!-- Right Side -->
                            <div class="text-left sm:text-right mt-4 sm:mt-0">
                                <div class="flex items-center justify-start sm:justify-end gap-2 mb-3">
                                    <span class="text-sm font-semibold">Status</span>
                                    @php
                                        $statusConfig = [
                                            'completed' => ['text' => 'Selesai', 'color' => 'text-green-600', 'dot' => 'bg-green-600'],
                                            'pending' => ['text' => 'Menunggu Pembayaran', 'color' => 'text-yellow-600', 'dot' => 'bg-yellow-600'],
                                            'paid' => ['text' => 'Sudah Bayar', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'confirmed' => ['text' => 'Dikonfirmasi / Diproses', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'in_delivery' => ['text' => 'Dalam Pengiriman', 'color' => 'text-blue-600', 'dot' => 'bg-blue-600'],
                                            'cancelled' => ['text' => 'Dibatalkan', 'color' => 'text-red-600', 'dot' => 'bg-red-600'],
                                        ];
                                        $status = $statusConfig[$order->status] ?? ['text' => ucfirst($order->status), 'color' => 'text-gray-600', 'dot' => 'bg-gray-600'];
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $status['dot'] }}"></span>
                                        <span class="{{ $status['color'] }} font-semibold">{{ $status['text'] }}</span>
                                    </div>
                                </div>
                                
                                <p class="text-sm text-gray-600 mb-2">
                                    @if(strtolower($order->payment_method) == 'tunai')
                                        Pembayaran Tunai (COD)
                                    @else
                                        {{ str_replace('_', ' ', strtoupper($order->payment_method)) }}
                                    @endif
                                </p>
                                
                                <p class="text-2xl font-bold text-red-600 mb-4">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                                
                                <button type="button" 
                                        class="toggle-detail-btn px-6 py-2.5 border-2 border-blue-500 text-blue-500 rounded-lg font-semibold hover:bg-blue-50 active:scale-95 hover:shadow-md transition-all relative z-10 cursor-pointer w-full sm:w-auto"
                                        data-target="pasar-detail-{{ $order->id }}">
                                    Lihat Selengkapnya
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Section -->
                    <div id="pasar-detail-{{ $order->id }}" class="detail-section hidden border-t border-gray-200">
                        <div class="p-6 bg-gray-50">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Left Column -->
                                <div class="space-y-4">
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Daftar Produk</p>
                                        <ul class="space-y-2 mt-2">
                                            @foreach($order->items as $item)
                                                <li class="flex justify-between items-start text-sm">
                                                    <div>
                                                        <span class="font-semibold text-gray-800">{{ $item->product_name }}</span>
                                                        <br><span class="text-gray-500">{{ $item->quantity }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</span>
                                                    </div>
                                                    <span class="font-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="border-t border-gray-200 pt-2 mt-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-500">Ongkos Kirim</span>
                                            <span class="font-semibold">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Right Column -->
                                <div class="space-y-4">
                                    @if($order->status === 'pending')
                                        <div>
                                            <a href="{{ route('pasar.payment', $order->id) }}" class="inline-block px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-semibold hover:bg-green-600 transition-colors">Selesaikan Pembayaran</a>
                                        </div>
                                    @endif
                                    @if(in_array($order->status, ['cancelled', 'rejected']))
                                        <div class="pt-4 border-t border-gray-200">
                                            <button type="button" 
                                                    class="delete-order-btn w-full px-4 py-2 border-2 border-red-500 text-red-500 rounded-lg text-sm font-semibold hover:bg-red-50 transition-colors"
                                                    data-type="pasar"
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
                @if($pasarOrders->currentPage() == 1)
                <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                    <p class="text-gray-500">Belum ada riwayat pesanan Pasar Daerah</p>
                </div>
                @endif
                @endforelse
