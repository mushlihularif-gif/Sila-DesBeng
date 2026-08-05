@extends('layouts.app')

@section('title', 'Checkout Pasar Daerah - SilaDesBeng')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 300px; z-index: 1; border-radius: 0.75rem; }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 pt-24 pb-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex items-center mb-8">
            <a href="{{ route('pasar.cart') }}" class="text-gray-500 hover:text-green-600 mr-4 transition">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Checkout Pemesanan</h1>
        </div>

        <form id="checkoutForm" action="{{ route('pasar.order.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Main Form Section -->
                <div class="w-full lg:w-2/3 space-y-6">
                    
                    <!-- Customer Details -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-user-circle text-green-600 mr-3 text-2xl"></i> Informasi Pembeli
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="full_name" value="{{ Auth::user()->name }}" required class="w-full border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500 bg-gray-50" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">No. WhatsApp <span class="text-red-500">*</span></label>
                                <input type="text" name="phone" value="{{ Auth::user()->phone }}" required class="w-full border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500">
                            </div>
                        </div>
                    </div>

                    <!-- Delivery Method -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-truck text-green-600 mr-3 text-2xl"></i> Metode Pengiriman
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <label class="relative border rounded-xl p-4 flex cursor-pointer hover:bg-gray-50 transition border-gray-200">
                                <input type="radio" name="delivery_method" value="ambil" class="peer sr-only" checked onchange="toggleDelivery(this.value)">
                                <div class="peer-checked:border-green-500 peer-checked:bg-green-50 absolute inset-0 rounded-xl border-2 border-transparent transition"></div>
                                <div class="relative flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-white border-2 border-gray-200 peer-checked:border-green-500 flex items-center justify-center mr-4 text-green-500 shadow-sm">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div>
                                        <span class="block text-gray-900 font-bold mb-1">Ambil Sendiri</span>
                                        <span class="block text-sm text-gray-500">Ambil pesanan di lokasi seller/toko (Gratis)</span>
                                    </div>
                                </div>
                            </label>

                            <label class="relative border rounded-xl p-4 flex cursor-pointer hover:bg-gray-50 transition border-gray-200">
                                <input type="radio" name="delivery_method" value="antar" class="peer sr-only" onchange="toggleDelivery(this.value)">
                                <div class="peer-checked:border-green-500 peer-checked:bg-green-50 absolute inset-0 rounded-xl border-2 border-transparent transition"></div>
                                <div class="relative flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-white border-2 border-gray-200 peer-checked:border-green-500 flex items-center justify-center mr-4 text-green-500 shadow-sm">
                                        <i class="fas fa-motorcycle"></i>
                                    </div>
                                    <div>
                                        <span class="block text-gray-900 font-bold mb-1">Diantar Kurir</span>
                                        <span class="block text-sm text-gray-500">Pesanan diantar ke rumah (Ada ongkir)</span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Form Pengantaran (Hidden by default) -->
                        <div id="deliveryForm" class="hidden space-y-6 mt-6 border-t border-gray-100 pt-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 flex justify-between">
                                    <span>Alamat Pengiriman Lengkap <span class="text-red-500">*</span></span>
                                    <button type="button" onclick="useProfileAddress()" class="text-green-600 hover:text-green-700 text-xs font-bold">Gunakan Alamat Profil</button>
                                </label>
                                <textarea name="delivery_address" id="delivery_address" rows="3" class="w-full border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500"></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tentukan Titik Lokasi Antar (Map) <span class="text-red-500">*</span></label>
                                <div class="bg-amber-50 text-amber-800 text-sm p-3 rounded-xl mb-3 flex items-start border border-amber-200">
                                    <i class="fas fa-info-circle mt-0.5 mr-2"></i>
                                    Geser marker merah pada peta ke titik lokasi pengantaran Anda yang akurat untuk perhitungan ongkos kirim.
                                </div>
                                <div id="map" class="shadow-inner border border-gray-200"></div>
                                <input type="hidden" name="delivery_latitude" id="delivery_latitude">
                                <input type="hidden" name="delivery_longitude" id="delivery_longitude">
                                <p class="text-xs text-gray-500 mt-2 text-right" id="distanceInfo">Jarak belum dihitung</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <i class="fas fa-wallet text-green-600 mr-3 text-2xl"></i> Metode Pembayaran
                        </h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="relative border rounded-xl p-4 flex cursor-pointer hover:bg-gray-50 transition border-gray-200">
                                <input type="radio" name="payment_method" value="tunai" class="peer sr-only" checked onchange="togglePayment(this.value)">
                                <div class="peer-checked:border-green-500 peer-checked:bg-green-50 absolute inset-0 rounded-xl border-2 border-transparent transition"></div>
                                <div class="relative flex items-center w-full">
                                    <i class="fas fa-money-bill-wave text-green-600 text-xl w-8"></i>
                                    <span class="text-gray-900 font-bold ml-2">Tunai / COD</span>
                                </div>
                            </label>
                            
                            <label class="relative border rounded-xl p-4 flex cursor-pointer hover:bg-gray-50 transition border-gray-200">
                                <input type="radio" name="payment_method" value="transfer_manual" class="peer sr-only" onchange="togglePayment(this.value)">
                                <div class="peer-checked:border-green-500 peer-checked:bg-green-50 absolute inset-0 rounded-xl border-2 border-transparent transition"></div>
                                <div class="relative flex items-center w-full">
                                    <i class="fas fa-exchange-alt text-blue-600 text-xl w-8"></i>
                                    <span class="text-gray-900 font-bold ml-2">Transfer Manual</span>
                                </div>
                            </label>

                            <label class="relative border rounded-xl p-4 flex cursor-pointer hover:bg-gray-50 transition border-gray-200">
                                <input type="radio" name="payment_method" value="qris" class="peer sr-only" onchange="togglePayment(this.value)">
                                <div class="peer-checked:border-green-500 peer-checked:bg-green-50 absolute inset-0 rounded-xl border-2 border-transparent transition"></div>
                                <div class="relative flex items-center w-full">
                                    <i class="fas fa-qrcode text-pink-600 text-xl w-8"></i>
                                    <span class="text-gray-900 font-bold ml-2">QRIS Otomatis</span>
                                </div>
                            </label>

                            <label class="relative border rounded-xl p-4 flex cursor-pointer hover:bg-gray-50 transition border-gray-200">
                                <input type="radio" name="payment_method" value="bank_transfer_bca" class="peer sr-only" onchange="togglePayment(this.value)">
                                <div class="peer-checked:border-green-500 peer-checked:bg-green-50 absolute inset-0 rounded-xl border-2 border-transparent transition"></div>
                                <div class="relative flex items-center w-full">
                                    <i class="fas fa-university text-blue-800 text-xl w-8"></i>
                                    <span class="text-gray-900 font-bold ml-2">BCA Virtual Account</span>
                                </div>
                            </label>
                        </div>

                        <!-- Manual Transfer Proof (Hidden by default) -->
                        <div id="manualTransferForm" class="hidden mt-6 border-t border-gray-100 pt-6">
                            <div class="bg-blue-50 text-blue-800 p-4 rounded-xl mb-4 text-sm border border-blue-100">
                                <p class="font-bold mb-1">Instruksi Transfer:</p>
                                <p>Silakan transfer sejumlah <strong id="transferAmount">Rp 0</strong> ke rekening berikut:</p>
                                <div class="mt-2 p-3 bg-white rounded border border-blue-200 flex justify-between items-center">
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $region->settings['rekening_bank'] ?? 'Bank Riau Kepri' }}</p>
                                        <p class="text-lg font-mono tracking-widest">{{ $region->settings['rekening_nomor'] ?? '123-456-7890' }}</p>
                                        <p class="text-sm text-gray-600">a.n {{ $region->settings['rekening_nama'] ?? 'Pusat Layanan Daerah ' . $region->name }}</p>
                                    </div>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $region->settings['rekening_nomor'] ?? '1234567890' }}'); Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Nomor rekening disalin!', showConfirmButton: false, timer: 1500})" class="text-blue-600 hover:text-blue-800 p-2">
                                        <i class="far fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Bukti Transfer <span class="text-red-500">*</span></label>
                            <input type="file" name="proof_of_payment" id="proof_of_payment" accept="image/*,.pdf" class="w-full border border-gray-300 rounded-xl p-2 focus:ring-green-500 focus:border-green-500">
                            <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, PDF. Maksimal 5MB.</p>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Pesanan (Opsional)</label>
                        <textarea name="notes" rows="2" class="w-full border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" placeholder="Cth: Tolong dibungkus rapi, jangan pedas, dll..."></textarea>
                    </div>

                </div>

                <!-- Summary Section -->
                <div class="w-full lg:w-1/3">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                        <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Ringkasan Pesanan</h3>
                        
                        <div class="space-y-4 mb-6 max-h-60 overflow-y-auto pr-2">
                            @php $totalAmount = 0; @endphp
                            @foreach($carts as $cart)
                                @php $totalAmount += ($cart->produk->harga * $cart->quantity); @endphp
                                <div class="flex items-start gap-3">
                                    <div class="w-12 h-12 rounded bg-gray-100 overflow-hidden flex-shrink-0">
                                        @if($cart->produk->foto)
                                            <img src="{{ Storage::url($cart->produk->foto) }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-gray-900 truncate">{{ $cart->produk->nama_produk }}</p>
                                        <p class="text-xs text-gray-500">{{ $cart->quantity }} x Rp {{ number_format($cart->produk->harga, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="text-sm font-bold text-gray-900 whitespace-nowrap">
                                        Rp {{ number_format($cart->produk->harga * $cart->quantity, 0, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="space-y-3 pt-4 border-t border-gray-100 mb-6 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Total Harga Barang</span>
                                <span class="font-bold text-gray-900" id="summaryTotal">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Ongkos Kirim <small class="text-gray-400" id="summaryDistance"></small></span>
                                <span class="font-bold text-gray-900" id="summaryOngkir">Rp 0</span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-900 font-bold">Total Pembayaran</span>
                                <span class="text-2xl font-black text-green-600" id="summaryGrandTotal">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <button type="button" onclick="submitOrder()" id="btnSubmit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-lg shadow-green-200 text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-300">
                            Buat Pesanan Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Constants
    const baseTotal = {{ $totalAmount }};
    const ongkirPerKm = {{ $region->settings['ongkir_per_km'] ?? 0 }};
    
    // Asumsi toko ada di lokasi Region ini. Jika produk pertama punya koordinat spesifik, kita pakai itu.
    // Untuk demo, kita pakai koordinat dummy atau koordinat produk pertama
    const storeLat = {{ $carts->first()->produk->latitude ?? '1.482755' }};
    const storeLon = {{ $carts->first()->produk->longitude ?? '102.138407' }};
    
    let currentOngkir = 0;
    let currentDistance = 0;
    
    // Map Setup
    let map, marker, circle;
    
    function initMap() {
        if(map) return; // already init
        
        const defaultLocation = [{{ Auth::user()->latitude ?? '1.482755' }}, {{ Auth::user()->longitude ?? '102.138407' }}];
        
        map = L.map('map').setView(defaultLocation, 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Marker Toko (Biru)
        L.marker([storeLat, storeLon], {
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: "<div style='background-color:#1d4ed8;width:15px;height:15px;border-radius:50%;border:2px solid white;box-shadow:0 0 5px rgba(0,0,0,0.5)'></div>",
                iconSize: [15, 15],
                iconAnchor: [7.5, 7.5]
            })
        }).addTo(map).bindPopup("Lokasi Toko");

        // Marker Pembeli (Merah & Draggable)
        marker = L.marker(defaultLocation, {
            draggable: true,
            icon: L.divIcon({
                className: 'custom-div-icon',
                html: "<div style='background-color:#ef4444;width:20px;height:20px;border-radius:50%;border:3px solid white;box-shadow:0 0 5px rgba(0,0,0,0.5)'></div>",
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            })
        }).addTo(map);

        updateCoordinates(marker.getLatLng());

        marker.on('dragend', function(e) {
            updateCoordinates(marker.getLatLng());
        });

        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateCoordinates(e.latlng);
        });
    }

    function updateCoordinates(latlng) {
        document.getElementById('delivery_latitude').value = latlng.lat;
        document.getElementById('delivery_longitude').value = latlng.lng;
        calculateDistance(latlng.lat, latlng.lng);
    }
    
    // Haversine JS
    function calculateDistance(lat, lon) {
        const R = 6371; // km
        const dLat = (lat - storeLat) * Math.PI / 180;
        const dLon = (lon - storeLon) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(storeLat * Math.PI / 180) * Math.cos(lat * Math.PI / 180) *
                  Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        currentDistance = (R * c).toFixed(2);
        
        currentOngkir = Math.round(currentDistance * ongkirPerKm);
        
        document.getElementById('distanceInfo').innerText = `Jarak perkiraan: ${currentDistance} km`;
        updateSummary();
    }

    function toggleDelivery(method) {
        const form = document.getElementById('deliveryForm');
        if (method === 'antar') {
            form.classList.remove('hidden');
            setTimeout(() => {
                initMap();
                map.invalidateSize();
            }, 100);
            updateSummary(); // recalculate with currentOngkir
        } else {
            form.classList.add('hidden');
            currentOngkir = 0;
            updateSummary();
        }
    }

    function togglePayment(method) {
        const form = document.getElementById('manualTransferForm');
        const input = document.getElementById('proof_of_payment');
        if (method === 'transfer_manual') {
            form.classList.remove('hidden');
            input.required = true;
        } else {
            form.classList.add('hidden');
            input.required = false;
        }
    }
    
    function formatMoney(amount) {
        return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function updateSummary() {
        const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked').value;
        const appliedOngkir = deliveryMethod === 'antar' ? currentOngkir : 0;
        const grandTotal = baseTotal + appliedOngkir;
        
        document.getElementById('summaryOngkir').innerText = formatMoney(appliedOngkir);
        document.getElementById('summaryDistance').innerText = deliveryMethod === 'antar' && currentDistance > 0 ? `(${currentDistance} km)` : '';
        document.getElementById('summaryGrandTotal').innerText = formatMoney(grandTotal);
        document.getElementById('transferAmount').innerText = formatMoney(grandTotal);
    }

    function useProfileAddress() {
        const address = `{{ Auth::user()->address }}`;
        document.getElementById('delivery_address').value = address;
        
        const lat = {{ Auth::user()->latitude ?? 'null' }};
        const lon = {{ Auth::user()->longitude ?? 'null' }};
        if(lat && lon && map) {
            const latlng = L.latLng(lat, lon);
            map.setView(latlng, 15);
            marker.setLatLng(latlng);
            updateCoordinates(latlng);
        }
    }

    function submitOrder() {
        const form = document.getElementById('checkoutForm');
        const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked').value;
        const formData = new FormData(form);
        
        if (deliveryMethod === 'antar') {
            if (!document.getElementById('delivery_address').value) {
                Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Silakan isi alamat pengiriman.', showConfirmButton: false, timer: 3000});
                return;
            }
            if (!formData.get('delivery_latitude') || !formData.get('delivery_longitude')) {
                Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Silakan tentukan titik lokasi pada peta.', showConfirmButton: false, timer: 3000});
                return;
            }
        }
        
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
        
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `/pasar-daerah/payment/${data.order_id}`;
            } else {
                Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan saat membuat pesanan.', showConfirmButton: false, timer: 3000});
                btn.disabled = false;
                btn.innerHTML = 'Buat Pesanan Sekarang';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Error', text: 'Terjadi kesalahan pada sistem.', showConfirmButton: false, timer: 3000});
            btn.disabled = false;
            btn.innerHTML = 'Buat Pesanan Sekarang';
        });
    }

    // Init state
    document.addEventListener('DOMContentLoaded', () => {
        toggleDelivery(document.querySelector('input[name="delivery_method"]:checked').value);
        togglePayment(document.querySelector('input[name="payment_method"]:checked').value);
        updateSummary();
    });
</script>
@endpush
@endsection
