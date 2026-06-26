@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16 bg-cover bg-center bg-no-repeat bg-fixed" 
             style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
        
        <!-- White Overlay -->
        <div class="absolute inset-0 bg-white/25 pointer-events-none"></div>

        <div class="max-w-3xl mx-auto px-6 relative z-20">
            <!-- Header -->
            <div class="text-center mb-12 mt-8">
                <h1 class="text-3xl md:text-4xl font-bold mb-2">
                    <span class="text-gray-800">Form </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Peminjaman Fasilitas Umum</span>
                </h1>
                <p class="text-gray-600">Peminjaman fasilitas ini tidak dipungut biaya (Gratis).</p>
            </div>

            <form id="booking-form" action="#" method="POST" onsubmit="return false;">
                @csrf
                <input type="hidden" name="fasilitas_id" value="{{ $item->id }}">

                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex items-center gap-3 mb-6 border-b pb-4">
                        <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->nama_fasilitas }}" class="w-20 h-20 object-cover rounded-lg">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">{{ $item->nama_fasilitas }}</h3>
                            <span class="text-sm text-gray-600">{{ $item->kategori }} | Stok: {{ $item->stok }} {{ $item->satuan }}</span>
                        </div>
                    </div>

                    <!-- Keterangan / Tujuan Penyewaan -->
                    @if($item->kategori == 'Kendaraan')
                    <div class="mb-6 bg-blue-50 border border-blue-200 p-5 rounded-xl">
                        <h4 class="font-bold text-gray-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Detail Tujuan Kendaraan
                        </h4>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Jangkauan Lokasi <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <label class="border border-gray-300 rounded-lg p-3 cursor-pointer bg-white hover:bg-blue-50 hover:border-blue-300 flex items-center gap-2 transition-colors">
                                    <input type="radio" name="zona_kendaraan" value="Dalam Desa" class="w-4 h-4 text-blue-600 focus:ring-blue-500" required>
                                    <span class="text-sm font-semibold text-gray-700">Dalam Desa</span>
                                </label>
                                <label class="border border-gray-300 rounded-lg p-3 cursor-pointer bg-white hover:bg-blue-50 hover:border-blue-300 flex items-center gap-2 transition-colors">
                                    <input type="radio" name="zona_kendaraan" value="Luar Desa" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm font-semibold text-gray-700">Luar Desa</span>
                                </label>
                                <label class="border border-gray-300 rounded-lg p-3 cursor-pointer bg-white hover:bg-blue-50 hover:border-blue-300 flex items-center gap-2 transition-colors">
                                    <input type="radio" name="zona_kendaraan" value="Luar Kota" class="w-4 h-4 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm font-semibold text-gray-700">Luar Kota</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Lokasi Spesifik & Keperluan <span class="text-red-500">*</span></label>
                            <textarea id="keperluan_kendaraan" rows="3" placeholder="Contoh: Ke RSUD Kabupaten untuk mengantarkan warga yang sakit..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                            <input type="hidden" name="rental_purpose" id="rental-purpose">
                        </div>
                    </div>
                    @else
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Keterangan / Tujuan Peminjaman <span class="text-red-500">*</span></label>
                        <textarea name="rental_purpose" id="rental-purpose" rows="3" placeholder="Contoh: Untuk acara RT, rapat desa, dll." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                    @endif

                    <!-- Jumlah -->
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah Pinjam <span class="text-red-500">*</span></label>
                        <div class="flex items-center gap-2 border border-gray-300 rounded-lg px-4 py-2 w-32">
                            <button type="button" id="decrease-qty" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            </button>
                            <input type="number" name="quantity" id="quantity" value="{{ $quantity }}" min="1" max="{{ $item->stok }}" class="w-full text-center border-0 focus:outline-none focus:ring-0 p-0 text-lg font-semibold bg-transparent" readonly required>
                            <button type="button" id="increase-qty" class="text-gray-600 hover:text-gray-800 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Waktu Penyewaan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Mulai <span class="text-red-500">*</span></label>
                            <input type="date" name="start_date" id="start-date" min="{{ date('Y-m-d') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Selesai <span class="text-red-500">*</span></label>
                            <input type="date" name="end_date" id="end-date" min="{{ date('Y-m-d') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    </div>

                    @php
                        $paymentInfo = $region->payment_info ?? [];
                        $antarActive = !isset($paymentInfo['fasilitas_delivery_antar_active']) || $paymentInfo['fasilitas_delivery_antar_active'];
                        $jemputActive = !isset($paymentInfo['fasilitas_delivery_jemput_active']) || $paymentInfo['fasilitas_delivery_jemput_active'];
                        $defaultMethod = $antarActive ? 'antar' : 'jemput';
                    @endphp
                    <input type="hidden" name="delivery_method" id="delivery-method-input" value="{{ $defaultMethod }}">

                    <!-- Pilihan Metode Pengiriman -->
                    <div class="flex flex-col sm:flex-row justify-center gap-6 mb-10 items-center mt-8">
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

                    <!-- Form Alamat Pengiriman (Hanya tampil jika Antar dipilih) -->
                    <div id="delivery-address-form" class="{{ $defaultMethod == 'antar' ? 'block' : 'hidden' }} animate-fade-in bg-blue-50/50 p-6 rounded-2xl mb-8 border border-blue-100">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Informasi Pengiriman
                        </h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Penerima <span class="text-red-500">*</span></label>
                                <input type="text" name="recipient_name" id="recipient-name" value="{{ Auth::user()->name }}" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Lengkap Tujuan <span class="text-red-500">*</span></label>
                                <textarea name="delivery_address" id="delivery-address" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all resize-none" placeholder="Masukkan alamat lengkap tujuan pengiriman (termasuk RT/RW, Dusun, dll)"></textarea>
                            </div>
                        </div>
                    </div>

                    <div id="jemput-note" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg {{ $defaultMethod == 'jemput' ? '' : 'hidden' }}">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <span class="font-bold">Info:</span> Anda harus mengambil dan mengembalikan sendiri fasilitas umum ke lokasi operasional.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Info BBM & Supir (Jika dikonfigurasi) -->
                    @if(isset($paymentInfo['fasilitas_bbm_default']) || isset($paymentInfo['fasilitas_supir_default']))
                    <div class="bg-gray-50 p-4 rounded-xl mb-6 border border-gray-200">
                        <h5 class="font-bold text-gray-700 mb-2">Informasi Tambahan (Jika Kendaraan)</h5>
                        <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                            @if(isset($paymentInfo['fasilitas_bbm_default']))
                            <li>BBM: <span class="font-semibold">{{ $paymentInfo['fasilitas_bbm_default'] }}</span></li>
                            @endif
                            @if(isset($paymentInfo['fasilitas_supir_default']))
                            <li>Supir: <span class="font-semibold">{{ $paymentInfo['fasilitas_supir_default'] }}</span></li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg mt-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Peminjaman ini <b>gratis</b>. Harap kembalikan fasilitas sesuai dengan tanggal selesai peminjaman dalam kondisi baik.
                                </p>
                            </div>
                        </div>
                    </div>

                    @if(!empty($sop_fasilitas_umum))
                    <!-- SOP Section -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <h3 class="text-lg font-bold text-gray-800">Ketentuan SOP</h3>
                        </div>
                        
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 text-sm text-gray-700 h-40 overflow-y-auto mb-4 whitespace-pre-wrap">
                            {{ $sop_fasilitas_umum }}
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="agree-sop" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="agree-sop" class="ml-2 text-sm text-gray-800 font-medium">Saya telah membaca dan menyetujui Ketentuan SOP</label>
                        </div>
                    </div>
                    @endif

                    <div class="flex justify-end">
                        <button type="button" class="confirm-action-btn px-8 py-3 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                            Konfirmasi Peminjaman
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
                <img src="{{ asset('Admin/img/illustrations/isewalogo.png') }}" alt="SiladesBeng Logo" class="w-40 mx-auto mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Konfirmasi Peminjaman</h2>
                <p class="text-gray-600 mb-6">Apakah Anda yakin ingin meminjam fasilitas ini?</p>
                
                <div class="flex gap-4">
                    <button type="button" id="cancel-confirmation" class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-full transition-colors">
                        Tidak
                    </button>
                    <button type="button" id="proceed-confirmation" class="flex-1 px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-full transition-colors">
                        Ya
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 backdrop-blur-sm transition-all duration-300" style="display: none;">
        <div class="bg-white rounded-[2rem] p-10 max-w-lg w-full mx-4 shadow-2xl transform transition-all relative">
            <button type="button" id="close-success-modal" class="absolute top-6 right-6 text-gray-400 hover:text-gray-800 transition-colors" onclick="window.location.href='{{ route('user.activity') }}'">
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
                
                <h2 class="text-3xl font-extrabold text-gray-900 mb-3 tracking-tight">Peminjaman Berhasil</h2>
                
                <div class="mb-8">
                    <p class="text-gray-800 font-bold text-lg mb-1">Permintaan Anda sedang Diproses</p>
                    <p class="text-gray-500 text-sm">Silahkan cek riwayat aktivitas Anda.</p>
                </div>
                
                <div class="space-y-4">
                    <button type="button" onclick="window.location.href='{{ route('user.activity') }}'" class="w-full px-6 py-4 bg-[#2395FF] hover:bg-blue-600 text-white font-extrabold rounded-2xl shadow-lg hover:shadow-blue-200/50 transition-all duration-300 transform hover:scale-[1.02] text-lg">
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
    * { font-family: 'Inter', sans-serif; }
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    input[type="number"] { -moz-appearance: textfield; }

    /* Checkmark Animation */
    .checkmark-circle { width: 150px; height: 150px; position: relative; display: inline-block; }
    .checkmark { width: 150px; height: 150px; border-radius: 50%; display: block; stroke-width: 3; stroke: #4ade80; stroke-miterlimit: 10; box-shadow: inset 0px 0px 0px #4ade80; animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both; }
    .checkmark-circle-path { stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 3; stroke-miterlimit: 10; stroke: #4ade80; animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards; }
    .checkmark-check { transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48; stroke: #fff; animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards; }
    @keyframes stroke { 100% { stroke-dashoffset: 0; } }
    @keyframes scale { 0%, 100% { transform: none; } 50% { transform: scale3d(1.1, 1.1, 1); } }
    @keyframes fill { 100% { box-shadow: inset 0px 0px 0px 75px #4ade80; } }

    /* Delivery Method Card Styles */
    .delivery-method-card {
        border-color: #e5e7eb;
    }
    .delivery-method-card.active {
        border-color: #3b82f6;
        background-color: #eff6ff;
        transform: scale(1.05);
    }
    .delivery-method-card:hover:not(.active) {
        border-color: #93c5fd;
        transform: translateY(-5px);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const qtyInput = document.getElementById('quantity');
        const decreaseBtn = document.getElementById('decrease-qty');
        const increaseBtn = document.getElementById('increase-qty');
        const maxStock = {{ $item->stok }};
        
        // Delivery Method Logic
        const deliveryCards = document.querySelectorAll('.delivery-method-card');
        const deliveryMethodInput = document.getElementById('delivery-method-input');
        const deliveryAddressForm = document.getElementById('delivery-address-form');
        const antarNote = document.getElementById('antar-note');
        const jemputNote = document.getElementById('jemput-note');
        const recipientName = document.getElementById('recipient-name');
        const deliveryAddress = document.getElementById('delivery-address');

        deliveryCards.forEach(card => {
            card.addEventListener('click', function() {
                deliveryCards.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                
                const method = this.getAttribute('data-method');
                deliveryMethodInput.value = method;

                if (method === 'antar') {
                    if (deliveryAddressForm) {
                        deliveryAddressForm.classList.remove('hidden');
                        deliveryAddressForm.classList.add('block');
                        recipientName.required = true;
                        deliveryAddress.required = true;
                    }
                    if(antarNote) {
                        antarNote.classList.remove('hidden');
                        antarNote.classList.add('block');
                    }
                    if(jemputNote) {
                        jemputNote.classList.add('hidden');
                        jemputNote.classList.remove('block');
                    }
                } else {
                    if (deliveryAddressForm) {
                        deliveryAddressForm.classList.remove('block');
                        deliveryAddressForm.classList.add('hidden');
                        recipientName.required = false;
                        deliveryAddress.required = false;
                    }
                    if(antarNote) {
                        antarNote.classList.add('hidden');
                        antarNote.classList.remove('block');
                    }
                    if(jemputNote) {
                        jemputNote.classList.remove('hidden');
                        jemputNote.classList.add('block');
                    }
                }
            });
        });

        if (decreaseBtn && increaseBtn && qtyInput) {
            decreaseBtn.addEventListener('click', () => {
                let val = parseInt(qtyInput.value) || 1;
                if (val > 1) qtyInput.value = val - 1;
            });
            increaseBtn.addEventListener('click', () => {
                let val = parseInt(qtyInput.value) || 1;
                if (val < maxStock) qtyInput.value = val + 1;
            });
        }

        const confirmBtns = document.querySelectorAll('.confirm-action-btn');
        const modal = document.getElementById('confirmation-modal');
        const cancelBtn = document.getElementById('cancel-confirmation');
        const proceedBtn = document.getElementById('proceed-confirmation');
        const successModal = document.getElementById('success-modal');

        confirmBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                let purposeVal = '';
                if (document.getElementById('keperluan_kendaraan')) {
                    const zona = document.querySelector('input[name="zona_kendaraan"]:checked');
                    const detail = document.getElementById('keperluan_kendaraan').value;
                    if (!zona || !detail) {
                        Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Mohon pilih Jangkauan Lokasi dan isi Lokasi Spesifik.' });
                        return;
                    }
                    purposeVal = `[Tujuan: ${zona.value}] ${detail}`;
                    document.getElementById('rental-purpose').value = purposeVal;
                } else {
                    purposeVal = document.getElementById('rental-purpose').value;
                }

                const startDate = document.getElementById('start-date').value;
                const endDate = document.getElementById('end-date').value;
                
                const agreeSop = document.getElementById('agree-sop');

                if (!purposeVal || !startDate || !endDate) {
                    Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Mohon lengkapi Tujuan, Tanggal Mulai dan Tanggal Selesai.' });
                    return;
                }

                if (agreeSop && !agreeSop.checked) {
                    Swal.fire({ icon: 'warning', title: 'Ketentuan SOP', text: 'Anda harus menyetujui Ketentuan SOP terlebih dahulu sebelum melanjutkan pemesanan.' });
                    return;
                }
                modal.style.display = 'flex';
                modal.classList.remove('hidden');
            });
        });

        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                modal.classList.add('hidden');
            });
        }

        if (proceedBtn) {
            proceedBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                modal.classList.add('hidden');

                Swal.fire({
                    title: 'Memproses...', allowOutsideClick: false, showConfirmButton: false, didOpen: () => { Swal.showLoading(); }
                });

                const formData = new FormData(document.getElementById('booking-form'));
                fetch('{{ route("user.fasilitas-umum.book.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                })
                .then(response => {
                    Swal.close();
                    if (response.status === 401) {
                        throw new Error('Anda harus login terlebih dahulu');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        successModal.style.display = 'flex';
                        successModal.classList.remove('hidden');
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Terjadi kesalahan' });
                    }
                })
                .catch(error => {
                    Swal.fire({ icon: 'error', title: 'Error', text: error.message });
                });
            });
        }
    });
</script>
@endpush
