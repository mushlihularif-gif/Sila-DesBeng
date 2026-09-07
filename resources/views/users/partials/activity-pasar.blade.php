@forelse($pasarOrders as $order)
    <div class="transaction-card bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 mb-6" data-status="{{ $order->status }}">
        <div class="p-6">
            <div class="flex flex-col sm:flex-row gap-6">
                <!-- Product Image (First item) -->
                @php 
                    $firstItem = $order->items->first(); 
                    $complaint = $order->complaint ?? null;
                @endphp
                @if($firstItem && $firstItem->produk && $firstItem->produk->foto)
                    <img src="{{ asset('storage/' . $firstItem->produk->foto) }}" 
                         alt="{{ $firstItem->product_name }}" 
                         class="w-full sm:w-32 h-48 sm:h-32 object-cover rounded-xl flex-shrink-0 shadow-sm"
                         onerror="this.src='{{ asset('User/img/elemen/pasar.png') }}'">
                @else
                    <div class="w-full sm:w-32 h-48 sm:h-32 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-store text-4xl text-gray-400"></i>
                    </div>
                @endif
                
                <div class="flex-1">
                    <!-- Info -->
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 bg-blue-50 text-blue-600 rounded-md text-xs font-mono font-semibold">
                            #{{ $order->order_number }}
                        </span>
                        @if($order->region)
                            <span class="text-xs text-gray-500 font-medium">
                                <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>BUMDes {{ $order->region->name }}
                            </span>
                        @endif
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">
                        {{ $firstItem ? $firstItem->product_name : 'Paket Belanjaan Pasar' }}
                        @if($order->items->count() > 1)
                            <span class="text-sm font-normal text-gray-500">(+{{ $order->items->count() - 1 }} produk lainnya)</span>
                        @endif
                    </h3>
                    <p class="text-xs text-gray-500 mb-2">
                        {{ \Carbon\Carbon::parse($order->created_at)->locale('id')->isoFormat('dddd, DD MMMM YYYY HH:mm') }} WIB
                    </p>
                    <p class="text-sm text-gray-600">
                        Total <strong>{{ $order->items->sum('quantity') }}</strong> Produk • Metode: <strong>{{ $order->delivery_method ?? 'Diantar Kurir Lokal' }}</strong>
                    </p>

                    <!-- Complaint Banner if exists -->
                    @if($complaint)
                    <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-2.5">
                        <i class="fas fa-exclamation-triangle text-amber-600 mt-0.5"></i>
                        <div class="text-xs">
                            <span class="font-bold text-amber-800">Komplain: {{ $complaint->reason }}</span>
                            <span class="ml-1 px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $complaint->status === 'pending' ? 'bg-amber-200 text-amber-900' : 'bg-green-200 text-green-900' }}">
                                {{ ucfirst($complaint->status) }}
                            </span>
                            @if($complaint->admin_response)
                                <p class="text-gray-600 mt-1 italic">Respon Admin: "{{ $complaint->admin_response }}"</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Right Side -->
                <div class="text-left sm:text-right mt-4 sm:mt-0 flex flex-col justify-between items-start sm:items-end">
                    <div>
                        <div class="flex items-center justify-start sm:justify-end gap-2 mb-2">
                            @php
                                $statusConfig = [
                                    'completed' => ['text' => 'Selesai', 'color' => 'text-green-600', 'bg' => 'bg-green-50', 'border' => 'border-green-200'],
                                    'pending' => ['text' => 'Menunggu Pembayaran', 'color' => 'text-yellow-600', 'bg' => 'bg-yellow-50', 'border' => 'border-yellow-200'],
                                    'paid' => ['text' => 'Sudah Dibayar', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                                    'confirmed' => ['text' => 'Diproses Toko', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                                    'in_delivery' => ['text' => 'Dalam Pengiriman', 'color' => 'text-sky-600', 'bg' => 'bg-sky-50', 'border' => 'border-sky-200'],
                                    'cancelled' => ['text' => 'Dibatalkan', 'color' => 'text-red-600', 'bg' => 'bg-red-50', 'border' => 'border-red-200'],
                                    'rejected' => ['text' => 'Ditolak', 'color' => 'text-red-600', 'bg' => 'bg-red-50', 'border' => 'border-red-200'],
                                ];
                                $status = $statusConfig[$order->status] ?? ['text' => ucfirst($order->status), 'color' => 'text-gray-600', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200'];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $status['bg'] }} {{ $status['color'] }} {{ $status['border'] }}">
                                {{ $status['text'] }}
                            </span>
                        </div>
                        
                        <p class="text-xs text-gray-500 mb-1">
                            @if(strtolower($order->payment_method) == 'tunai')
                                Pembayaran Tunai (COD)
                            @else
                                {{ str_replace('_', ' ', strtoupper($order->payment_method)) }}
                            @endif
                        </p>
                        
                        <p class="text-2xl font-bold text-sky-600 mb-3">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                    </div>

                    <button type="button" 
                            class="toggle-detail-btn px-5 py-2 border border-gray-300 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 active:scale-95 transition-all w-full sm:w-auto"
                            data-target="pasar-detail-{{ $order->id }}">
                        Rincian Pesanan <i class="fas fa-chevron-down ml-1 text-xs transition-transform"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Detail Section -->
        <div id="pasar-detail-{{ $order->id }}" class="detail-section hidden border-t border-gray-100 bg-gray-50/70 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column: Items List -->
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-4">
                    <h4 class="font-bold text-gray-800 text-sm border-b pb-2 flex items-center gap-2">
                        <i class="fas fa-shopping-basket text-sky-500"></i> Rincian Produk Belanja
                    </h4>
                    <ul class="space-y-3 divide-y divide-gray-100">
                        @foreach($order->items as $item)
                            <li class="flex justify-between items-center text-sm pt-2">
                                <div class="flex items-center gap-3">
                                    @if($item->produk && $item->produk->foto)
                                        <img src="{{ asset('storage/' . $item->produk->foto) }}" class="w-10 h-10 object-cover rounded-lg border">
                                    @else
                                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                                            <i class="fas fa-box"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <span class="font-semibold text-gray-800">{{ $item->product_name }}</span>
                                        <div class="text-xs text-gray-500">{{ $item->quantity }} {{ $item->produk->satuan ?? 'pcs' }} x Rp {{ number_format($item->product_price, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                <span class="font-semibold text-gray-800">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="border-t border-gray-200 pt-3 space-y-1.5 text-xs text-gray-600">
                        <div class="flex justify-between">
                            <span>Subtotal Produk</span>
                            <span class="font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Biaya Pengantaran</span>
                            <span class="font-semibold text-green-600">{{ $order->shipping_cost == 0 ? 'Gratis' : 'Rp ' . number_format($order->shipping_cost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-gray-800 pt-2 border-t">
                            <span>Total Tagihan</span>
                            <span class="text-sky-600">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Delivery, Proof & Actions -->
                <div class="space-y-4">
                    <!-- Delivery Info Card -->
                    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm space-y-3">
                        <h4 class="font-bold text-gray-800 text-sm border-b pb-2 flex items-center gap-2">
                            <i class="fas fa-truck text-emerald-500"></i> Informasi Pengiriman
                        </h4>
                        <div class="text-xs space-y-1.5 text-gray-600">
                            <div><span class="text-gray-400">Penerima:</span> <strong class="text-gray-700">{{ $order->user->name ?? '-' }}</strong> ({{ $order->phone ?? '-' }})</div>
                            <div><span class="text-gray-400">Alamat:</span> <span class="text-gray-700">{{ $order->address ?? 'Alamat sesuai akun' }}</span></div>
                            @if($order->notes)
                                <div><span class="text-gray-400">Catatan:</span> <span class="italic text-gray-700">"{{ $order->notes }}"</span></div>
                            @endif
                        </div>

                        <!-- Delivery Proof Photo if completed -->
                        @if($order->delivery_proof_image)
                            <div class="pt-3 border-t border-gray-100">
                                <span class="text-xs font-semibold text-gray-700 block mb-1.5"><i class="fas fa-camera text-emerald-600 mr-1"></i>Foto Bukti Barang Diterima</span>
                                <a href="{{ asset('storage/' . $order->delivery_proof_image) }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200 hover:opacity-90 transition-opacity">
                                    <img src="{{ asset('storage/' . $order->delivery_proof_image) }}" alt="Foto Bukti" class="w-full h-32 object-cover">
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
                        <h4 class="font-bold text-gray-800 text-sm mb-3">Aksi Pesanan</h4>
                        <div class="flex flex-wrap gap-2.5">
                            @if($order->status === 'pending')
                                <a href="{{ route('pasar.payment', $order->id) }}" class="flex-1 text-center px-4 py-2.5 bg-green-500 text-white rounded-xl text-sm font-semibold hover:bg-green-600 transition-colors shadow-sm">
                                    <i class="fas fa-wallet mr-1.5"></i> Selesaikan Pembayaran
                                </a>
                            @elseif(in_array($order->status, ['paid', 'confirmed', 'in_delivery']))
                                <!-- Tombol Komplain -->
                                @if(!$complaint)
                                    <button type="button" 
                                            onclick="openPasarComplaintModal({{ $order->id }}, '{{ $order->order_number }}', '{{ $firstItem ? addslashes($firstItem->product_name) : '' }}')"
                                            class="px-4 py-2.5 border border-red-300 text-red-600 rounded-xl text-sm font-semibold hover:bg-red-50 transition-colors">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Ajukan Komplain
                                    </button>
                                @endif

                                <!-- Tombol Konfirmasi Terima -->
                                <button type="button" 
                                        onclick="openPasarConfirmModal({{ $order->id }}, '{{ $order->order_number }}')"
                                        class="flex-1 px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors shadow-sm">
                                    <i class="fas fa-check-circle mr-1.5"></i> Pesanan Diterima
                                </button>
                            @elseif($order->status === 'completed')
                                <!-- Tombol Unduh Struk -->
                                <a href="{{ route('receipt.pasar.view', $order->id) }}" target="_blank" class="px-4 py-2.5 border border-sky-300 text-sky-600 rounded-xl text-sm font-semibold hover:bg-sky-50 transition-colors">
                                    <i class="fas fa-receipt mr-1"></i> Lihat Struk
                                </a>

                                <!-- Tombol Beri Ulasan -->
                                @if($firstItem && $firstItem->produk)
                                    <button type="button" 
                                            onclick="openPasarReviewModal({{ $firstItem->produk->id }}, '{{ addslashes($firstItem->product_name) }}', '{{ $firstItem->produk->foto ? asset('storage/' . $firstItem->produk->foto) : '' }}')"
                                            class="flex-1 px-4 py-2.5 bg-amber-500 text-white rounded-xl text-sm font-semibold hover:bg-amber-600 transition-colors shadow-sm">
                                        <i class="fas fa-star mr-1"></i> Beri Ulasan
                                    </button>
                                @endif
                            @elseif(in_array($order->status, ['cancelled', 'rejected']))
                                <button type="button" 
                                        class="delete-order-btn w-full px-4 py-2.5 border border-red-300 text-red-600 rounded-xl text-sm font-semibold hover:bg-red-50 transition-colors"
                                        data-type="pasar"
                                        data-id="{{ $order->id }}">
                                    <i class="fas fa-trash-alt mr-2"></i> Hapus Riwayat
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@empty
    @if($pasarOrders->currentPage() == 1)
    <div class="bg-white rounded-2xl shadow-lg p-12 text-center border border-gray-100">
        <div class="w-16 h-16 bg-sky-50 text-sky-500 rounded-full flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-shopping-bag text-2xl"></i>
        </div>
        <h4 class="text-lg font-bold text-gray-800 mb-1">Belum Ada Riwayat Belanja</h4>
        <p class="text-sm text-gray-500 mb-4">Yuk jelajahi produk lokal segar dan berkualitas dari Pasar Daerah BUMDes!</p>
        <a href="{{ route('pasar.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-sky-500 text-white rounded-xl font-semibold hover:bg-sky-600 transition-colors shadow-sm">
            <i class="fas fa-store"></i> Belanja di Pasar Daerah
        </a>
    </div>
    @endif
@endforelse

<!-- ========================================================================= -->
<!-- MODAL 1: KONFIRMASI PESANAN DITERIMA (WEB) -->
<!-- ========================================================================= -->
<div id="modalConfirmReceived" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-check-circle text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-base">Konfirmasi Pesanan Diterima</h3>
                    <p class="text-xs text-gray-500" id="modalConfirmOrderNo">Pesanan #PSR-...</p>
                </div>
            </div>
            <button type="button" onclick="closePasarModal('modalConfirmReceived')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formConfirmReceived" method="POST" enctype="multipart/form-data">
            @csrf
            <p class="text-xs text-gray-600 mb-4">
                Pastikan seluruh barang belanjaan telah Anda terima dalam keadaan baik dan lengkap.
            </p>
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Foto Bukti Penerimaan (Opsional)</label>
                <input type="file" name="proof_image" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 border border-gray-200 rounded-xl p-1.5">
                <small class="text-[11px] text-gray-400 mt-1 block">Bisa foto barang bersama kurir atau paket belanjaan.</small>
            </div>
            <div class="flex items-start gap-2 mb-5">
                <input type="checkbox" id="agreeConfirm" required class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                <label for="agreeConfirm" class="text-xs text-gray-600 leading-tight">
                    Saya menyatakan barang belanjaan telah diterima sesuai pesanan.
                </label>
            </div>
            <div class="flex gap-2.5">
                <button type="button" onclick="closePasarModal('modalConfirmReceived')" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 shadow-sm">
                    Selesaikan Pesanan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: AJUKAN KOMPLAIN / RETUR (WEB) -->
<!-- ========================================================================= -->
<div id="modalComplaint" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-base">Ajukan Komplain / Retur</h3>
                    <p class="text-xs text-gray-500" id="modalComplaintOrderNo">Pesanan #PSR-...</p>
                </div>
            </div>
            <button type="button" onclick="closePasarModal('modalComplaint')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formComplaint" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Komplain</label>
                <select name="reason" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none">
                    <option value="Barang rusak / busuk / basi">Barang rusak / busuk / basi</option>
                    <option value="Jumlah barang kurang / tidak lengkap">Jumlah barang kurang / tidak lengkap</option>
                    <option value="Barang salah kirim / berbeda varian">Barang salah kirim / berbeda varian</option>
                    <option value="Kadaluarsa / kemasan cacat">Kadaluarsa / kemasan cacat</option>
                    <option value="Barang tidak sampai / salah antar">Barang tidak sampai / salah antar</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Solusi yang Diinginkan</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="border rounded-xl p-3 flex items-center gap-2 cursor-pointer hover:bg-gray-50 text-xs">
                        <input type="radio" name="solution_requested" value="refund" checked onchange="toggleRefundFields(true)">
                        <div>
                            <strong class="block text-gray-800">Refund Dana</strong>
                            <span class="text-gray-400 text-[11px]">Uang dikembalikan</span>
                        </div>
                    </label>
                    <label class="border rounded-xl p-3 flex items-center gap-2 cursor-pointer hover:bg-gray-50 text-xs">
                        <input type="radio" name="solution_requested" value="replacement" onchange="toggleRefundFields(false)">
                        <div>
                            <strong class="block text-gray-800">Ganti Barang</strong>
                            <span class="text-gray-400 text-[11px]">Kirim ulang barang</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Rekening Fields (Muncul jika refund) -->
            <div id="refundBankSection" class="p-3 bg-gray-50 rounded-xl border border-gray-200 space-y-2">
                <span class="text-xs font-bold text-gray-700 block">Informasi Rekening / E-Wallet Pengembalian</span>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    <input type="text" name="bank_name" placeholder="Nama Bank/E-Wallet (BRI, DANA)" class="border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs bg-white">
                    <input type="text" name="bank_account_number" placeholder="Nomor Rekening/HP" class="border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs bg-white">
                    <input type="text" name="bank_account_name" placeholder="Atas Nama" class="border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs bg-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Jelaskan Masalah</label>
                <textarea name="description" rows="3" placeholder="Ceritakan detail kendala yang Anda alami..." class="w-full border border-gray-200 rounded-xl p-3 text-xs focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Foto Bukti Kerusakan / Unboxing (Maksimal 3 Foto)</label>
                <div class="space-y-2">
                    <input type="file" name="evidence_1" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 hover:file:bg-gray-200 border border-gray-200 rounded-xl p-1">
                    <input type="file" name="evidence_2" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 hover:file:bg-gray-200 border border-gray-200 rounded-xl p-1">
                    <input type="file" name="evidence_3" accept="image/*" class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 hover:file:bg-gray-200 border border-gray-200 rounded-xl p-1">
                </div>
            </div>

            <div class="flex gap-2.5 pt-2">
                <button type="button" onclick="closePasarModal('modalComplaint')" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 shadow-sm">
                    Kirim Komplain
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: BERI ULASAN & RATING (WEB) -->
<!-- ========================================================================= -->
<div id="modalReview" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                    <i class="fas fa-star text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 text-base">Beri Ulasan Produk</h3>
                    <p class="text-xs text-gray-500" id="modalReviewProductName">Nama Produk</p>
                </div>
            </div>
            <button type="button" onclick="closePasarModal('modalReview')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formReview" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2 text-center">Berapa bintang untuk produk ini?</label>
                <div class="flex justify-center gap-3 text-3xl text-gray-300" id="starRatingGroup">
                    <i class="fas fa-star cursor-pointer text-amber-400 hover:scale-110 transition-transform" data-star="1" onclick="setWebRating(1)"></i>
                    <i class="fas fa-star cursor-pointer text-amber-400 hover:scale-110 transition-transform" data-star="2" onclick="setWebRating(2)"></i>
                    <i class="fas fa-star cursor-pointer text-amber-400 hover:scale-110 transition-transform" data-star="3" onclick="setWebRating(3)"></i>
                    <i class="fas fa-star cursor-pointer text-amber-400 hover:scale-110 transition-transform" data-star="4" onclick="setWebRating(4)"></i>
                    <i class="fas fa-star cursor-pointer text-amber-400 hover:scale-110 transition-transform" data-star="5" onclick="setWebRating(5)"></i>
                </div>
                <input type="hidden" name="rating" id="inputRating" value="5">
                <p class="text-center text-xs text-amber-600 font-semibold mt-1" id="ratingLabel">Sangat Puas & Rekomended!</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1">Ulasan / Pengalaman Belanja</label>
                <textarea name="comment" rows="3" placeholder="Tuliskan ulasan Anda mengenai kesegaran, kemasan, atau kecepatan pengiriman..." class="w-full border border-gray-200 rounded-xl p-3 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
            </div>

            <div class="flex gap-2.5">
                <button type="button" onclick="closePasarModal('modalReview')" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-600 rounded-xl text-sm font-semibold hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="flex-1 px-4 py-2.5 bg-amber-500 text-white rounded-xl text-sm font-semibold hover:bg-amber-600 shadow-sm">
                    Kirim Ulasan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function closePasarModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

function openPasarConfirmModal(orderId, orderNo) {
    document.getElementById('modalConfirmOrderNo').innerText = 'Pesanan #' + orderNo;
    document.getElementById('formConfirmReceived').action = '/pasar-daerah/order/' + orderId + '/confirm-received';
    document.getElementById('modalConfirmReceived').classList.remove('hidden');
}

function openPasarComplaintModal(orderId, orderNo, productName) {
    document.getElementById('modalComplaintOrderNo').innerText = 'Pesanan #' + orderNo + ' (' + productName + ')';
    document.getElementById('formComplaint').action = '/pasar-daerah/order/' + orderId + '/complaint';
    document.getElementById('modalComplaint').classList.remove('hidden');
}

function toggleRefundFields(isRefund) {
    const el = document.getElementById('refundBankSection');
    if (isRefund) {
        el.classList.remove('hidden');
    } else {
        el.classList.add('hidden');
    }
}

function openPasarReviewModal(productId, productName, productImg) {
    document.getElementById('modalReviewProductName').innerText = productName;
    document.getElementById('formReview').action = '/pasar-daerah/' + productId + '/review';
    setWebRating(5);
    document.getElementById('modalReview').classList.remove('hidden');
}

const ratingDescriptions = {
    1: 'Sangat Kecewa',
    2: 'Kurang Puas',
    3: 'Cukup Baik',
    4: 'Puas & Bagus',
    5: 'Sangat Puas & Rekomended!'
};

function setWebRating(stars) {
    document.getElementById('inputRating').value = stars;
    document.getElementById('ratingLabel').innerText = ratingDescriptions[stars];
    const starIcons = document.querySelectorAll('#starRatingGroup i');
    starIcons.forEach((icon, index) => {
        if (index < stars) {
            icon.classList.remove('text-gray-300');
            icon.classList.add('text-amber-400');
        } else {
            icon.classList.remove('text-amber-400');
            icon.classList.add('text-gray-300');
        }
    });
}
</script>
