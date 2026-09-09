@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    {{-- Custom Vector Abstract Background (Gelombang Wave Pasar Daerah) --}}
    @include('partials.abstract-bg')

    <section class="relative z-10 min-h-screen pt-40 pb-16">
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            {{-- Header Section --}}
            <div class="text-center mb-16 animate-section">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Pilih </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Layanan</span>
                </h1>
                <p class="text-gray-700 text-lg mt-2">
                    Ayo Pilih Daerah mu, Dukung dan Gunakan Unit Layanannya!
                </p>
            </div>

            {{-- Search Bar (dari beranda) --}}
            <div class="max-w-2xl mx-auto mb-8 sm:mb-10 animate-section">
                <div class="relative group">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 via-blue-400 to-amber-400 rounded-full opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative flex items-center bg-white rounded-full overflow-hidden">
                        <input type="text" id="searchInput" placeholder="Cari"
                            class="flex-1 px-4 sm:px-8 py-2.5 sm:py-3.5 text-gray-700 text-sm sm:text-[15px] focus:outline-none bg-transparent text-center placeholder:text-center">
                        <div class="flex-shrink-0 px-4 sm:px-6 py-2.5 sm:py-3.5 text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kabupaten & Kecamatan Cards --}}
            <div class="max-w-3xl mx-auto mb-8 animate-section" id="card-container">
                

                @foreach($kecamatans as $index => $kecamatan)
                <div class="kecamatan-card mb-3 sm:mb-4 {{ $index >= 3 ? 'extra-card is-collapsed' : '' }}"
                     data-name="{{ strtolower($kecamatan->name) }}">
                    <div class="backdrop-blur-sm bg-white/70 rounded-xl sm:rounded-2xl p-3.5 sm:p-6 border border-white/80 shadow-md sm:shadow-lg hover:shadow-xl transition-all duration-300">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base sm:text-xl font-bold text-gray-800">{{ $kecamatan->name }}</h3>
                            <a href="{{ route('bumdes.profil.desa', $kecamatan->id) }}{{ request()->has('redirect') ? '?redirect=' . request('redirect') : '' }}"
                               class="px-5 sm:px-8 py-1.5 sm:py-2.5 bg-white text-[#0099ff] font-semibold text-xs sm:text-base rounded-full border-2 border-gray-300 hover:bg-gray-50 hover:shadow-lg transition-all duration-300">
                                Pilih
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- Empty state --}}
                <div id="empty-state" class="hidden text-center py-10">
                    <p class="text-gray-500 font-medium text-lg">Tidak tersedia atau Kecamatan belum bergabung dengan SiladesBeng</p>
                </div>
            </div>

            {{-- Tampilkan / Sembunyikan --}}
            @if($kecamatans->count() > 3)
            <div class="text-center mb-16 animate-section">
                <button id="toggleBtn" translate="no" class="inline-flex items-center gap-1.5 text-gray-600 font-semibold hover:text-blue-600 text-sm transition-colors">
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

            <!-- Unit Pelayanan Section -->
            @include('users.partials.unit-carousel')

            <!-- Pemerintahan Section -->
            <div class="mb-16 mt-16 animate-section">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold">
                        <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pemerintah Kabupaten Bengkalis</span>
                    </h2>
                    <p class="text-gray-500 text-sm mt-2">Bagan Struktur Organisasi dan Tata Kerja Pemerintahan Kabupaten</p>
                </div>

                @php
                    $level1 = $members->where('level', 1);
                    $level2 = $members->where('level', 2);
                    $level3 = $members->where('level', 3);
                    $level4 = $members->where('level', 4);
                    $unclassified = $members->whereNotIn('level', [1, 2, 3, 4]);
                @endphp

                <div class="hierarchy-container max-w-6xl mx-auto mb-16">
                    {{-- TINGKAT 1: PIMPINAN UTAMA (BUPATI) --}}
                    @if($level1->count() > 0)
                        <div class="hierarchy-tier hierarchy-tier-1">
                            <div class="flex flex-wrap justify-center gap-10">
                                @foreach($level1 as $member)
                                <div class="member-card transition-all duration-300 text-center w-72">
                                    <div class="relative mx-auto mb-6" style="width: 190px; height: 230px;">
                                        <div class="absolute inset-0 opacity-90" style="
                                            border-radius: 0 50px 0 50px; 
                                            transform: translate(6px, 6px); 
                                            padding: 3px; 
                                            background: linear-gradient(135deg, #115789, #3b82f6); 
                                            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); 
                                            -webkit-mask-composite: xor; 
                                            mask-composite: exclude;
                                        "></div>
                                        <div class="absolute inset-0 overflow-hidden bg-gray-50 animate-float z-10" style="border-radius: 0 50px 0 50px; box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.18);">
                                            <img src="{{ $member->photo_url }}" 
                                                 alt="{{ $member->name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                    <span class="inline-block px-3 py-1 mb-2 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                        Pimpinan Daerah
                                    </span>
                                    <h3 class="text-lg font-bold mb-1" style="color: #000000;">{{ $member->name }}</h3>
                                    <p class="text-sm font-semibold text-blue-900">{{ $member->position }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- PENGHUBUNG HIERARKI TINGKAT 1 KE 2 --}}
                    @if($level1->count() > 0 && ($level2->count() > 0 || $level3->count() > 0 || $level4->count() > 0))
                        <div class="flex justify-center my-3">
                            <div class="h-8 w-0.5 bg-gradient-to-b from-[#115789] to-[#3b82f6]"></div>
                        </div>
                    @endif

                    {{-- TINGKAT 2: WAKIL BUPATI / SEKDA --}}
                    @if($level2->count() > 0)
                        <div class="hierarchy-tier hierarchy-tier-2">
                            <div class="flex flex-wrap justify-center gap-10">
                                @foreach($level2 as $member)
                                <div class="member-card transition-all duration-300 text-center w-72">
                                    <div class="relative mx-auto mb-6" style="width: 180px; height: 220px;">
                                        <div class="absolute inset-0 opacity-90" style="
                                            border-radius: 0 50px 0 50px; 
                                            transform: translate(6px, 6px); 
                                            padding: 3px; 
                                            background: linear-gradient(135deg, #0284c7, #38bdf8); 
                                            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); 
                                            -webkit-mask-composite: xor; 
                                            mask-composite: exclude;
                                        "></div>
                                        <div class="absolute inset-0 overflow-hidden bg-gray-50 animate-float z-10" style="border-radius: 0 50px 0 50px; box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.15);">
                                            <img src="{{ $member->photo_url }}" 
                                                 alt="{{ $member->name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                    <span class="inline-block px-3 py-1 mb-2 rounded-full text-xs font-semibold bg-sky-100 text-sky-800 border border-sky-200">
                                        Wakil / Sekretaris Daerah
                                    </span>
                                    <h3 class="text-lg font-bold mb-1" style="color: #000000;">{{ $member->name }}</h3>
                                    <p class="text-sm font-semibold text-sky-900">{{ $member->position }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- PENGHUBUNG HIERARKI TINGKAT 2 KE 3 --}}
                    @if($level2->count() > 0 && ($level3->count() > 0 || $level4->count() > 0))
                        <div class="flex justify-center my-3">
                            <div class="h-8 w-0.5 bg-gradient-to-b from-[#38bdf8] to-[#10b981]"></div>
                        </div>
                    @endif

                    {{-- TINGKAT 3: KEPALA DINAS / BADAN / BAGIAN --}}
                    @if($level3->count() > 0)
                        <div class="hierarchy-tier hierarchy-tier-3">
                            <div class="flex flex-wrap justify-center gap-8">
                                @foreach($level3 as $member)
                                <div class="member-card transition-all duration-300 text-center w-72">
                                    <div class="relative mx-auto mb-6" style="width: 170px; height: 210px;">
                                        <div class="absolute inset-0 opacity-90" style="
                                            border-radius: 0 50px 0 50px; 
                                            transform: translate(6px, 6px); 
                                            padding: 3px; 
                                            background: linear-gradient(135deg, #059669, #34d399); 
                                            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); 
                                            -webkit-mask-composite: xor; 
                                            mask-composite: exclude;
                                        "></div>
                                        <div class="absolute inset-0 overflow-hidden bg-gray-50 animate-float z-10" style="border-radius: 0 50px 0 50px; box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.15);">
                                            <img src="{{ $member->photo_url }}" 
                                                 alt="{{ $member->name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                    <span class="inline-block px-3 py-1 mb-2 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Kepala Dinas / Badan
                                    </span>
                                    <h3 class="text-base font-bold mb-1" style="color: #000000;">{{ $member->name }}</h3>
                                    <p class="text-sm font-medium text-gray-700">{{ $member->position }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- PENGHUBUNG HIERARKI TINGKAT 3 KE 4 --}}
                    @if($level3->count() > 0 && ($level4->count() > 0 || $unclassified->count() > 0))
                        <div class="flex justify-center my-3">
                            <div class="h-8 w-0.5 bg-gradient-to-b from-[#34d399] to-[#94a3b8]"></div>
                        </div>
                    @endif

                    {{-- TINGKAT 4: STAF PELAKSANA / APARATUR --}}
                    @if($level4->count() > 0 || $unclassified->count() > 0)
                        <div class="hierarchy-tier hierarchy-tier-4">
                            <div class="flex flex-wrap justify-center gap-8">
                                @foreach($level4->concat($unclassified) as $member)
                                <div class="member-card transition-all duration-300 text-center w-72">
                                    <div class="relative mx-auto mb-6" style="width: 165px; height: 205px;">
                                        <div class="absolute inset-0 opacity-90" style="
                                            border-radius: 0 50px 0 50px; 
                                            transform: translate(6px, 6px); 
                                            padding: 3px; 
                                            background: linear-gradient(135deg, #64748b, #94a3b8); 
                                            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); 
                                            -webkit-mask-composite: xor; 
                                            mask-composite: exclude;
                                        "></div>
                                        <div class="absolute inset-0 overflow-hidden bg-gray-50 animate-float z-10" style="border-radius: 0 50px 0 50px; box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.15);">
                                            <img src="{{ $member->photo_url }}" 
                                                 alt="{{ $member->name }}"
                                                 class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                    <span class="inline-block px-3 py-1 mb-2 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        Staf / Aparatur
                                    </span>
                                    <h3 class="text-base font-bold mb-1" style="color: #000000;">{{ $member->name }}</h3>
                                    <p class="text-sm font-medium text-gray-600">{{ $member->position }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- WhatsApp Contact Button -->
                @if($isWhatsappActive)
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
                @endif
            </div>

            {{-- BUMDes Description Section --}}
            <div class="mb-16 animate-section">
                <div class="text-left mb-8">
                    <h2 class="text-3xl md:text-4xl font-bold">
                        <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Kabupaten Bengkalis</span>
                    </h2>
                </div>

                <div class="backdrop-blur-sm bg-white/70 rounded-3xl p-8 md:p-12 border border-white/80 shadow-xl">
                    <div class="text-gray-700 text-base leading-relaxed text-justify space-y-4">
                        <p>
                            Kabupaten Bengkalis adalah salah satu kabupaten yang terletak di Provinsi Riau, Indonesia. Daerah ini dikenal dengan kekayaan alamnya serta posisinya yang strategis di pesisir timur Pulau Sumatera. Seiring dengan pesatnya perkembangan teknologi informasi untuk memajukan kesejahteraan masyarakat, kini hadir inovasi digitalisasi daerah Kabupaten Bengkalis melalui aplikasi website bernama <strong>SiladesBeng</strong> (Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis).
                        </p>
                        <p>
                            Kehadiran platform digital <strong>SiladesBeng</strong> bertujuan untuk memudahkan masyarakat dalam mengakses berbagai layanan daerah yang dikelola oleh berbagai entitas masyarakat dan unit usaha, seperti penyewaan alat, penjualan gas, peminjaman fasilitas umum, hingga pelaporan warga secara transparan dan efisien. Melalui inisiatif inovatif ini, diharapkan dapat terwujud tata kelola layanan daerah yang modern, mandiri, dan terintegrasi secara digital menuju pembangunan daerah yang berkelanjutan.
                        </p>
                    </div>
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

    /* Member Card Styles */
    .member-card {
        text-align: center;
        width: 280px;
        padding: 1rem;
        transition: all 0.3s ease;
    }

    .member-card:hover {
        transform: translateY(-4px);
    }

    /* Float Animation */
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
        100% { transform: translateY(0px); }
    }
    
    .animate-float {
        animation: float 4s ease-in-out infinite;
    }

</style>
    @include('users.partials.unit-carousel-styles')
@endpush

@push('scripts')
    @include('users.partials.unit-carousel-scripts')
<script>
(function() {
    function initKecamatanSearch() {
        var isExpanded = false;
        var searchInput = document.getElementById('searchInput');
        var cardContainer = document.getElementById('card-container');

        if (!searchInput || !cardContainer || !cardContainer.querySelector('.kecamatan-card')) return;

        // Remove old listener to avoid duplicates
        var newInput = searchInput.cloneNode(true);
        searchInput.parentNode.replaceChild(newInput, searchInput);
        searchInput = newInput;

        searchInput.addEventListener('input', function() {
            var query = this.value.toLowerCase();
            var cards = document.querySelectorAll('.kecamatan-card');
            var emptyState = document.getElementById('empty-state');
            var toggleBtn = document.getElementById('toggleBtn');
            var visibleCount = 0;

            if (toggleBtn) {
                toggleBtn.style.display = query.length > 0 ? 'none' : '';
            }

            cards.forEach(function(card) {
                var name = card.getAttribute('data-name') || '';
                var fullName = name.toLowerCase();

                if (fullName.includes(query)) {
                    card.classList.remove('search-hidden');

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
        });

        // Toggle cards
        var toggleBtn = document.getElementById('toggleBtn');
        if (toggleBtn) {
            var newToggle = toggleBtn.cloneNode(true);
            toggleBtn.parentNode.replaceChild(newToggle, toggleBtn);
            newToggle.addEventListener('click', function() {
                var extraCards = document.querySelectorAll('.extra-card');
                var toggleText = document.getElementById('toggleText');
                var arrowDown = document.getElementById('toggleArrowDown');
                var arrowUp = document.getElementById('toggleArrowUp');

                isExpanded = !isExpanded;

                extraCards.forEach(function(card) {
                    card.style.overflow = 'hidden';
                    if (isExpanded) {
                        card.classList.remove('is-collapsed');
                        card.classList.add('is-expanded');
                        setTimeout(function() {
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
            });
        }
    }

    // Run on both full page load and Turbo navigation
    document.addEventListener('DOMContentLoaded', initKecamatanSearch);
    document.addEventListener('turbo:load', initKecamatanSearch);

    // Also run immediately in case DOM is already ready
    if (document.readyState !== 'loading') {
        initKecamatanSearch();
    }
})();
</script>
@endpush

