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
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Desa di {{ $kecamatan->name }}</span>
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
                            <a href="{{ request()->has('redirect') && Route::has(request('redirect')) ? route(request('redirect')) . '?region_id=' . $desa->id : route('bumdes.detail', ['slug' => \Illuminate\Support\Str::slug($desa->name)]) . '?id=' . $desa->id }}"
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
                <button id="toggleBtn" onclick="toggleCards()" translate="no" class="inline-flex items-center gap-1.5 text-gray-600 font-semibold hover:text-blue-600 text-sm transition-colors">
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

            <!-- Pemerintahan Section -->
            <div class="mb-16 mt-24">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold">
                        <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pemerintah {{ $kecamatan->name }}</span>
                    </h2>
                </div>

                <!-- WhatsApp Contact Button -->
                <div class="text-center mb-16">
                    <a href="{{ $whatsappLink }}" 
                       target="_blank"
                       class="inline-flex items-center gap-3 px-8 py-4 bg-[#25D366] text-white font-semibold rounded-full hover:bg-[#20BA5A] hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <span>Halo Layanan</span>
                    </a>
                </div>
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
