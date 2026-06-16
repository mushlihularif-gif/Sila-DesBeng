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
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Keterangan / Tujuan Peminjaman <span class="text-red-500">*</span></label>
                        <textarea name="rental_purpose" id="rental-purpose" rows="3" placeholder="Contoh: Untuk acara RT, rapat desa, dll." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>

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

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg">
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
                const purpose = document.getElementById('rental-purpose').value;
                const startDate = document.getElementById('start-date').value;
                const endDate = document.getElementById('end-date').value;
                
                if (!purpose || !startDate || !endDate) {
                    Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Mohon lengkapi Tujuan, Tanggal Mulai dan Tanggal Selesai.' });
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
