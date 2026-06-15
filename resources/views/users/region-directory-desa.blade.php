@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16">
        {{-- Background Image --}}
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <img src="{{ asset('Admin/img/elements/background.png') }}" class="w-full h-full object-cover" alt="">
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            {{-- Header Section --}}
            <div class="text-center mb-16 animate-section">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Pilih Layanan </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Desa di Kecamatan {{ $kecamatan->name }}</span>
                </h1>
                <p class="text-gray-700 text-lg mt-2">
                    Ayo Pilih Daerah mu, Dukung dan Gunakan Unit Layanannya!
                </p>
            </div>

            {{-- Search Bar (dari beranda) --}}
            <div class="max-w-2xl mx-auto mb-10 animate-section">
                <div class="relative group">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 via-blue-400 to-amber-400 rounded-full opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative flex items-center bg-white rounded-full overflow-hidden">
                        <input type="text" id="searchInput" placeholder="Cari"
                            class="flex-1 px-8 py-3.5 text-gray-700 text-[15px] focus:outline-none bg-transparent text-center placeholder:text-center"
                            oninput="filterCards()">
                        <div class="flex-shrink-0 px-6 py-3.5 text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Desa Cards --}}
            <div class="max-w-3xl mx-auto mb-8 animate-section" id="card-container">
                @foreach($desas as $index => $desa)
                <div class="desa-card mb-4 {{ $index >= 3 ? 'extra-card is-collapsed' : '' }}"
                     data-name="{{ strtolower($desa->name) }}">
                    <div class="backdrop-blur-sm bg-white/70 rounded-2xl p-6 border border-white/80 shadow-lg hover:shadow-xl transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-800">{{ $desa->name }}</h3>
                            <a href="{{ route('bumdes.detail') }}?id={{ $desa->id }}"
                               class="px-8 py-2.5 bg-white text-[#0099ff] font-semibold rounded-full border-2 border-gray-300 hover:bg-gray-50 hover:shadow-lg transition-all duration-300">
                                Pilih
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- Empty state --}}
                <div id="empty-state" class="hidden text-center py-10">
                    <p class="text-gray-500 font-medium text-lg">Tidak tersedia atau Desa belum bergabung dengan SiladesBeng</p>
                </div>
            </div>

            {{-- Tampilkan / Sembunyikan --}}
            @if($desas->count() > 3)
            <div class="text-center mb-16 animate-section">
                <button id="toggleBtn" onclick="toggleCards()" class="inline-flex items-center gap-1.5 text-gray-600 font-semibold hover:text-blue-600 text-sm transition-colors">
                    <span id="toggleText">Tampilkan</span>
                    <svg id="toggleArrowDown" class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <svg id="toggleArrowUp" class="w-4 h-4 text-blue-500 hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
            @endif
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    * {
        font-family: 'Inter', sans-serif;
    }

    /* Smooth animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    section > div {
        animation: fadeIn 0.6s ease-out;
    }

    /* Expand/Collapse animations */
    .extra-card {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .extra-card.is-collapsed:not(.force-show) {
        max-height: 0;
        opacity: 0;
        margin-bottom: 0 !important;
        transform: translateY(-20px);
        pointer-events: none;
        overflow: hidden;
    }
    
    .extra-card.is-expanded, .extra-card.force-show {
        max-height: 150px; /* Cukup untuk satu card */
        opacity: 1;
        transform: translateY(0);
    }
    
    .search-hidden {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
<script>
    let isExpanded = false;

    function toggleCards() {
        const extraCards = document.querySelectorAll('.extra-card');
        const toggleText = document.getElementById('toggleText');
        const arrowDown = document.getElementById('toggleArrowDown');
        const arrowUp = document.getElementById('toggleArrowUp');

        isExpanded = !isExpanded;

        extraCards.forEach(card => {
            card.style.overflow = 'hidden'; // Ensure hidden during transition

            if (isExpanded) {
                card.classList.remove('is-collapsed');
                card.classList.add('is-expanded');
                // Remove overflow hidden after animation to allow shadow bleed
                setTimeout(() => {
                    if (card.classList.contains('is-expanded')) {
                        card.style.overflow = 'visible';
                    }
                }, 500);
            } else {
                card.classList.remove('is-expanded');
                card.classList.add('is-collapsed');
            }
        });

        if (isExpanded) {
            toggleText.textContent = 'Sembunyikan';
            arrowDown.classList.add('hidden');
            arrowUp.classList.remove('hidden');
        } else {
            toggleText.textContent = 'Tampilkan';
            arrowDown.classList.remove('hidden');
            arrowUp.classList.add('hidden');
        }
    }

    function filterCards() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        const cards = document.querySelectorAll('.desa-card');
        const emptyState = document.getElementById('empty-state');
        const toggleBtn = document.getElementById('toggleBtn');
        let visibleCount = 0;

        // Sembunyikan tombol toggle saat ada pencarian
        if (toggleBtn) {
            toggleBtn.style.display = query.length > 0 ? 'none' : '';
        }

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const fullName = name;
            
            if (fullName.includes(query)) {
                card.classList.remove('search-hidden');
                
                // Jika sedang mencari, paksa tampilkan semua (override is-collapsed)
                if (query.length > 0 && card.classList.contains('extra-card')) {
                    card.classList.add('force-show');
                    card.style.overflow = 'visible';
                } else {
                    card.classList.remove('force-show');
                    if (card.classList.contains('is-collapsed')) {
                        card.style.overflow = 'hidden';
                    }
                }
                
                visibleCount++;
            } else {
                card.classList.add('search-hidden');
            }
        });

        if (visibleCount === 0) {
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
        }
    }
</script>
@endpush
