@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16">
        <!-- Static Background Wrapper -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute inset-0 bg-cover bg-top bg-no-repeat" 
                 style="background-image: url('{{ asset('Admin/img/elements/background1.png') }}');">
            </div>
            <!-- White Overlay -->
            <div class="absolute inset-0 bg-white/25"></div>
        </div>

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

                <!-- Penyewaan Mobil Card -->
                <div class="activity-menu-card cursor-pointer" data-type="mobil">
                    <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 w-48 text-center border-4 border-transparent">
                        <div class="mb-3 flex justify-center">
                            <img src="{{ asset('User/img/elemen/mobil.png') }}" alt="Sewa Mobil" class="w-16 h-16 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800">Sewa Mobil</p>
                    </div>
                </div>

                <!-- Fasilitas Umum Card -->
                <div class="activity-menu-card cursor-pointer" data-type="fasilitas">
                    <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 w-48 text-center border-4 border-transparent">
                        <div class="mb-3 flex justify-center">
                            <img src="{{ asset('User/img/elemen/fasilitas.png') }}" alt="Fasilitas Umum" class="w-16 h-16 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800">Fasilitas Umum</p>
                    </div>
                </div>
                
                <!-- Pasar Daerah Card -->
                <div class="activity-menu-card cursor-pointer" data-type="pasar">
                    <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 w-48 text-center border-4 border-transparent">
                        <div class="mb-3 flex justify-center">
                            <i class="fas fa-store text-green-600 text-5xl mb-2 mt-2"></i>
                        </div>
                        <p class="font-bold text-lg text-gray-800">Pasar Daerah</p>
                    </div>
                </div>

                <!-- Pelaporan Warga Card -->
                <div class="activity-menu-card cursor-pointer" data-type="laporan">
                    <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 w-48 text-center border-4 border-transparent">
                        <div class="mb-3 flex justify-center">
                            <img src="{{ asset('User/img/elemen/lapor.png') }}" alt="Laporan Warga" class="w-16 h-16 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800">Laporan Warga</p>
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
                <button type="button" 
                        class="clear-history-btn bg-red-100 text-red-600 px-6 py-2 rounded-full font-semibold hover:bg-red-200 transition-colors hidden"
                        id="clear-mobil-btn"
                        data-type="mobil">
                    <i class="fas fa-trash-alt mr-2"></i>Bersihkan Riwayat Sewa Mobil
                </button>
                <button type="button" 
                        class="clear-history-btn bg-red-100 text-red-600 px-6 py-2 rounded-full font-semibold hover:bg-red-200 transition-colors hidden"
                        id="clear-fasilitas-btn"
                        data-type="fasilitas">
                    <i class="fas fa-trash-alt mr-2"></i>Bersihkan Riwayat Fasilitas Umum
                </button>
                <button type="button" 
                        class="clear-history-btn bg-red-100 text-red-600 px-6 py-2 rounded-full font-semibold hover:bg-red-200 transition-colors hidden"
                        id="clear-pasar-btn"
                        data-type="pasar">
                    <i class="fas fa-trash-alt mr-2"></i>Bersihkan Riwayat Pasar Daerah
                </button>
                <button type="button" 
                        class="clear-history-btn bg-red-100 text-red-600 px-6 py-2 rounded-full font-semibold hover:bg-red-200 transition-colors hidden"
                        id="clear-laporan-btn"
                        data-type="laporan">
                    <i class="fas fa-trash-alt mr-2"></i>Bersihkan Riwayat Laporan Warga
                </button>
            </div>

            <div class="flex flex-wrap justify-center gap-2 lg:gap-3 mt-2 mb-6" id="status-filters">
                <button class="filter-btn active bg-blue-50 text-blue-700 border border-blue-500 px-4 py-1.5 rounded-lg font-bold shadow-md transition-all text-sm" data-filter="all">Semua</button>
                <button class="filter-btn bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-900 px-4 py-1.5 rounded-lg font-semibold transition-all text-sm shadow-sm" data-filter="pending">Menunggu</button>
                <button class="filter-btn bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-900 px-4 py-1.5 rounded-lg font-semibold transition-all text-sm shadow-sm" data-filter="confirmed">Dikonfirmasi</button>
                <button class="filter-btn bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-900 px-4 py-1.5 rounded-lg font-semibold transition-all text-sm shadow-sm" data-filter="completed">Selesai</button>
                <button class="filter-btn bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-900 px-4 py-1.5 rounded-lg font-semibold transition-all text-sm shadow-sm" data-filter="cancelled">Batal</button>
            </div>

            <!-- Bagian Pesanan Sewa -->
            <div id="rental-section" class="activity-section space-y-6">
                @include("users.partials.activity-rental")
            </div>

            <!-- Gas Orders Section -->
            <div id="gas-section" class="activity-section space-y-6 hidden">
                @include("users.partials.activity-gas")
            </div>

            <!-- Mobil Orders Section -->
            <div id="mobil-section" class="activity-section space-y-6 hidden">
                @include("users.partials.activity-mobil")
            </div>

            <!-- Fasilitas Umum Orders Section -->
            <div id="fasilitas-section" class="activity-section space-y-6 hidden">
                @include("users.partials.activity-fasilitas")
            </div>

            <!-- Laporan Warga Section -->
            <!-- Pasar Daerah Section -->
            <div id="pasar-section" class="activity-section space-y-6 hidden">
                @include("users.partials.activity-pasar")
            </div>

            <div id="laporan-section" class="activity-section space-y-6 hidden">
                @include("users.partials.activity-laporan")
            </div>

        </div>
    </section>
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
    (() => {
        const initActivityPage = function() {
            'use strict';

            // Activity Menu Toggle
            const menuCards = document.querySelectorAll('.activity-menu-card');
            const rentalSection = document.getElementById('rental-section');
            const gasSection = document.getElementById('gas-section');
            const clearRentalBtn = document.getElementById('clear-rental-btn');
            const clearGasBtn = document.getElementById('clear-gas-btn');
            const filterBtns = document.querySelectorAll('.filter-btn');

            // Apply filters function
            function applyFilter(filterValue) {
                const activeCard = document.querySelector('.activity-menu-card.active');
                if(!activeCard) return;
                const activeTab = activeCard.dataset.type;
                const activeSection = document.getElementById(`${activeTab}-section`);
                if(!activeSection) return;
                const cards = activeSection.querySelectorAll('.transaction-card, .activity-item');
                
                cards.forEach(card => {
                    if (filterValue === 'all') {
                        card.style.display = 'block';
                    } else {
                        const rawStatus = (card.dataset.status || '').toLowerCase();
                        let mappedStatus = rawStatus;
                        
                        // Map statuses to the main filter categories
                        if (['pending', 'proses', 'dilanjutkan'].includes(rawStatus)) {
                            mappedStatus = 'pending';
                        } else if (['confirmed', 'in_delivery', 'process', 'delivering', 'arrived', 'disewa', 'approved'].includes(rawStatus)) {
                            mappedStatus = 'confirmed';
                        } else if (['completed', 'selesai', 'resolved', 'returned', 'lunas', 'paid'].includes(rawStatus)) {
                            mappedStatus = 'completed';
                        } else if (['cancelled', 'rejected', 'ditolak', 'macet'].includes(rawStatus)) {
                            mappedStatus = 'cancelled';
                        }
                        
                        if (mappedStatus === filterValue) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    }
                });
            }

            // Filter button clicks
            if(filterBtns) {
                filterBtns.forEach(btn => {
                    btn.onclick = function() {
                        // Update active button styling
                        filterBtns.forEach(b => {
                            b.className = "filter-btn bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-900 px-4 py-1.5 rounded-lg font-semibold transition-all text-sm shadow-sm";
                        });
                        
                        this.className = "filter-btn active bg-blue-50 text-blue-700 border border-blue-500 px-4 py-1.5 rounded-lg font-bold shadow-md transition-all text-sm";
                        
                        applyFilter(this.dataset.filter);
                    };
                });
            }

            // Restore active tab from localStorage if exists
            const savedTab = localStorage.getItem('active_activity_tab');
            if (savedTab) {
                menuCards.forEach(c => c.classList.remove('active'));
                const targetCard = Array.from(menuCards).find(c => c.dataset.type === savedTab);
                if (targetCard) targetCard.classList.add('active');
            }

            // Initial State
            const activeCard = document.querySelector('.activity-menu-card.active');
            if (activeCard && activeCard.dataset.type === 'gas') {
                 if(rentalSection) rentalSection.classList.add('hidden');
                 if(gasSection) gasSection.classList.remove('hidden');
                 if(clearRentalBtn) {
                     clearRentalBtn.classList.add('hidden');
                     clearRentalBtn.style.display = 'none';
                 }
                 if(clearGasBtn) {
                     clearGasBtn.classList.remove('hidden');
                     clearGasBtn.style.display = 'block';
                 }
            } else {
                 if(rentalSection) rentalSection.classList.remove('hidden');
                 if(gasSection) gasSection.classList.add('hidden');
            }

            menuCards.forEach(card => {
                card.onclick = () => {
                    const type = card.dataset.type;
                    
                    // Save to localStorage
                    localStorage.setItem('active_activity_tab', type);
                    
                    // Update active state
                    menuCards.forEach(c => c.classList.remove('active'));
                    card.classList.add('active');
                    
                    // Hide all sections
                    document.querySelectorAll('.activity-section').forEach(s => s.classList.add('hidden'));
                    
                    // Show target section
                    const targetSection = document.getElementById(`${type}-section`);
                    if (targetSection) targetSection.classList.remove('hidden');
                    
                    // Hide all clear buttons
                    document.querySelectorAll('.clear-history-btn').forEach(btn => {
                        btn.classList.add('hidden');
                        btn.style.display = 'none';
                    });
                    
                    // Show target clear button
                    const targetClearBtn = document.getElementById(`clear-${type}-btn`);
                    if (targetClearBtn) {
                        targetClearBtn.classList.remove('hidden');
                        targetClearBtn.style.display = 'block';
                    }
                    
                    // Re-apply current filter for the newly active section
                    const activeFilterBtn = document.querySelector('.filter-btn.active');
                    if (activeFilterBtn) {
                        applyFilter(activeFilterBtn.dataset.filter);
                    }
                };
            });

            // Toggle Detail Dropdown Function
            const bindDetailToggles = () => {
                const toggleButtons = document.querySelectorAll('.toggle-detail-btn');
                toggleButtons.forEach(button => {
                    button.onclick = () => {
                        const targetId = button.dataset.target;
                        const detailSection = document.getElementById(targetId);
                        if (!detailSection) return;
                        
                        if (detailSection.classList.contains('hidden')) {
                            detailSection.classList.remove('hidden');
                            setTimeout(() => {
                                detailSection.classList.add('show');
                            }, 10);
                            button.textContent = 'Tutup';
                        } else {
                            detailSection.classList.remove('show');
                            setTimeout(() => {
                                detailSection.classList.add('hidden');
                            }, 300);
                            button.textContent = 'Lihat Selengkapnya';
                        }
                    };
                });
            };

            // Cancel Order Function
            const bindCancelButtons = () => {
                const cancelButtons = document.querySelectorAll('.cancel-order-btn');
                cancelButtons.forEach(button => {
                    button.onclick = async () => {
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
                                    const btn = document.querySelector(`.cancel-order-btn[data-id="${id}"][data-type="${type}"]`);
                                    if(btn) {
                                        btn.outerHTML = '<div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3"><p class="text-sm font-semibold text-yellow-800 mb-1">Permintaan Pembatalan Diajukan</p><p class="text-xs text-yellow-700">Menunggu konfirmasi admin</p></div>';
                                    }
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
                    };
                });
            };

            // Delete Single Order History Function
            const bindDeleteButtons = () => {
                const deleteButtons = document.querySelectorAll('.delete-order-btn');
                deleteButtons.forEach(button => {
                    button.onclick = async () => {
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
                                    const btn = document.querySelector(`.delete-order-btn[data-id="${id}"][data-type="${type}"]`);
                                    if(btn) {
                                        const card = btn.closest('.bg-white.rounded-2xl.shadow-lg.overflow-hidden');
                                        if(card) card.remove();
                                    }
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
                    };
                });
            };

            // Initial Bind
            bindDetailToggles();
            bindCancelButtons();
            bindDeleteButtons();

            // Clear All History
            const clearHistoryButtons = document.querySelectorAll('.clear-history-btn');
            clearHistoryButtons.forEach(button => {
                 button.onclick = async () => {
                    const type = button.dataset.type;
                    let typeText = 'Penyewaan';
                    if(type === 'gas') typeText = 'Pesanan Gas';
                    else if(type === 'mobil') typeText = 'Sewa Mobil';
                    else if(type === 'fasilitas') typeText = 'Fasilitas Umum';
                    else if(type === 'laporan') typeText = 'Laporan Warga';
                    else if(type === 'pasar') typeText = 'Pasar Daerah';

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
                                const section = document.getElementById(`${type}-section`);
                                if(section) {
                                    section.innerHTML = '<div class="bg-white rounded-2xl shadow-lg p-8 text-center"><p class="text-gray-500">Belum ada riwayat aktivitas</p></div>';
                                }
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
                };
            });

            // Infinite Scroll Implementation
            let isLoading = false;
            let currentPages = {
                rental: 1,
                gas: 1,
                mobil: 1,
                fasilitas: 1,
                pasar: 1,
                laporan: 1
            };
            let hasMorePages = {
                rental: true,
                gas: true,
                mobil: true,
                fasilitas: true,
                pasar: true,
                laporan: true
            };

            function loadMoreActivity() {
                if (isLoading) return;
                
                const activeCard = document.querySelector('.activity-menu-card.active');
                if (!activeCard) return;
                
                const type = activeCard.dataset.type;
                if (!hasMorePages[type]) return;

                const activeSection = document.getElementById(`${type}-section`);
                if (!activeSection) return;

                // Check scroll position
                if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 400) {
                    isLoading = true;
                    currentPages[type]++;
                    
                    const loadingIndicator = document.createElement('div');
                    loadingIndicator.className = 'text-center py-6 text-blue-500 loading-indicator font-semibold';
                    loadingIndicator.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memuat lebih banyak...';
                    activeSection.appendChild(loadingIndicator);

                    fetch(`?tab=${type}&page=${currentPages[type]}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        loadingIndicator.remove();
                        if (data.html) {
                            activeSection.insertAdjacentHTML('beforeend', data.html);
                            
                            // Re-apply filters if any
                            const activeFilterBtn = document.querySelector('.filter-btn.active');
                            if (activeFilterBtn) {
                                applyFilter(activeFilterBtn.dataset.filter);
                            }
                            
                            // Re-bind events to new elements
                            bindDetailToggles();
                            bindCancelButtons();
                            bindDeleteButtons();
                        }
                        hasMorePages[type] = data.hasMore;
                        isLoading = false;
                    })
                    .catch(error => {
                        loadingIndicator.remove();
                        isLoading = false;
                        console.error('Error loading more:', error);
                    });
                }
            }

            if (window.activityScrollHandler) {
                window.removeEventListener('scroll', window.activityScrollHandler);
            }
            window.activityScrollHandler = loadMoreActivity;
            window.addEventListener('scroll', window.activityScrollHandler);
        };

        if (!window.activityTurboBound) {
            window.activityTurboBound = true;
            document.addEventListener('turbo:load', initActivityPage);
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initActivityPage);
            }
        }
        
        // Execute immediately if document is already loaded (handles direct navigation & turbo injected script)
        if (document.readyState !== 'loading') {
            initActivityPage();
        }
    })();

    // Change Payment Method Logic
    async function openChangeMethodModal(orderId, currentMethod) {
        const banks = [
            { id: 'bank_transfer_bca', name: 'BCA Virtual Account', img: '{{ asset('admin/img/banks/bca.png') }}' },
            { id: 'bank_transfer_mandiri', name: 'Mandiri Virtual Account', img: '{{ asset('admin/img/banks/mandiri.png') }}' },
            { id: 'bank_transfer_bni', name: 'BNI Virtual Account', img: '{{ asset('admin/img/banks/bni.png') }}' },
            { id: 'bank_transfer_bri', name: 'BRI Virtual Account', img: '{{ asset('admin/img/banks/bri.png') }}' },
            { id: 'qris', name: 'QRIS (All E-Wallet)', img: '{{ asset('admin/img/banks/qris.svg') }}', fallback: '{{ asset('admin/img/banks/dana.png') }}' }
        ];

        let optionsHtml = `
        <style>
            .swal-pm-input:checked + .swal-pm-card {
                border-color: #3b82f6 !important;
                background-color: #eff6ff !important;
            }
            .swal-pm-input:checked + .swal-pm-card .swal-pm-circle {
                border-color: #3b82f6 !important;
            }
            .swal-pm-input:checked + .swal-pm-card .swal-pm-dot {
                opacity: 1 !important;
            }
            .swal-pm-input:disabled + .swal-pm-card {
                opacity: 0.5;
                cursor: not-allowed;
            }
        </style>
        <div class="flex flex-col gap-3 mt-4 text-left max-h-[60vh] overflow-y-auto px-1">`;
        
        banks.forEach(bank => {
            const isCurrent = currentMethod === bank.id;
            const imgHtml = bank.fallback 
                ? `<img src="${bank.img}" onerror="this.src='${bank.fallback}'" class="h-6 object-contain w-14">`
                : `<img src="${bank.img}" class="h-6 object-contain w-14">`;
            
            optionsHtml += `
                <label class="cursor-pointer group relative block">
                    <input type="radio" name="swal_payment_method" value="${bank.id}" class="swal-pm-input absolute opacity-0 w-0 h-0" ${isCurrent ? 'disabled' : ''}>
                    <div class="swal-pm-card flex items-center gap-4 p-4 border-2 border-gray-100 rounded-2xl group-hover:bg-gray-50 transition-all">
                        <div class="swal-pm-circle w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center bg-white group-hover:border-blue-400">
                            <div class="swal-pm-dot w-2.5 h-2.5 rounded-full bg-blue-500 opacity-0 transition-opacity"></div>
                        </div>
                        ${imgHtml}
                        <span class="font-semibold text-gray-800">${bank.name} ${isCurrent ? '<span class="text-xs text-blue-500 font-bold ml-1">(Saat Ini)</span>' : ''}</span>
                    </div>
                </label>
            `;
        });
        optionsHtml += '</div>';

        const { value: paymentMethod } = await Swal.fire({
            title: 'Ubah Metode Pembayaran',
            icon: 'question',
            html: optionsHtml,
            showCancelButton: true,
            confirmButtonText: 'Simpan Perubahan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#9ca3af',
            preConfirm: () => {
                const selected = document.querySelector('input[name="swal_payment_method"]:checked');
                if (!selected) {
                    Swal.showValidationMessage('Silakan pilih metode pembayaran baru');
                    return false;
                }
                return selected.value;
            }
        });

        if (paymentMethod) {
            // Create and submit a form programmatically
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/gas/payment/${orderId}/change-method`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = 'payment_method';
            methodInput.value = paymentMethod;
            
            form.appendChild(csrfToken);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            
            form.submit();
        }
    }
</script>
@endpush

