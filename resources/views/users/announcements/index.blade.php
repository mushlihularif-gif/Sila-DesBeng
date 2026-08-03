@extends('layouts.user')

@section('title', 'Kabar dan Informasi Daerah - SiladesBeng')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16">
        {{-- Custom Vector Abstract Background --}}
        <div class="fixed inset-0 overflow-hidden z-0" id="premium-bg">
            <canvas id="abstract-canvas" class="w-full h-full absolute inset-0"></canvas>
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            {{-- Header Section --}}
            <div class="text-center mb-10 animate-section">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Kabar dan Informasi </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Daerah</span>
                </h1>
                <p class="text-gray-700 text-lg mt-2">
                    Dapatkan informasi terbaru, berita kegiatan, dan pengumuman untuk seluruh wilayah daerah.
                </p>
            </div>

            {{-- Tabs (Cards Style) --}}
            <div class="flex flex-col sm:flex-row justify-center gap-6 mb-12 items-center animate-section">
                <!-- Berita Terbaru Card -->
                <div class="tab-btn focus:outline-none cursor-pointer" data-tab="berita">
                    <div id="tab-card-berita" class="tab-card bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 w-64 h-48 flex flex-col justify-center items-center text-center border-4 border-transparent {{ $activeTab === 'berita' ? 'active' : '' }}">
                        <div class="mb-4 flex justify-center">
                            <img src="{{ asset('Admin/img/kabardaerah/Berita.png') }}" alt="Berita Daerah" class="w-20 h-20 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800 w-full whitespace-nowrap">Berita Daerah</p>
                    </div>
                </div>
                
                <!-- Pengumuman Warga Card -->
                <div class="tab-btn focus:outline-none cursor-pointer" data-tab="pengumuman">
                    <div id="tab-card-pengumuman" class="tab-card bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 w-64 h-48 flex flex-col justify-center items-center text-center border-4 border-transparent {{ $activeTab === 'pengumuman' ? 'active' : '' }}">
                        <div class="mb-4 flex justify-center">
                            <img src="{{ asset('Admin/img/kabardaerah/Pengumuman1.png') }}" alt="Pengumuman" class="w-20 h-20 object-contain">
                        </div>
                        <p class="font-bold text-lg text-gray-800 w-full whitespace-nowrap">Pengumuman</p>
                    </div>
                </div>
            </div>

            {{-- Content Container --}}
            <div id="kabar-list-container" class="transition-all duration-300">
                
                {{-- TAB BERITA --}}
                <div id="tab-content-berita" class="tab-content" style="display: {{ $activeTab === 'berita' ? 'block' : 'none' }};">
                    
                    {{-- Search Berita --}}
                    <div class="max-w-md mx-auto mb-10">
                        <form action="{{ route('announcements.index') }}" method="GET" class="relative group">
                            <input type="hidden" name="tab" value="berita">
                            <!-- Gradient Border -->
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 via-blue-400 to-amber-400 rounded-full opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <!-- Search Input -->
                            <div class="relative flex items-center bg-white rounded-full overflow-hidden">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berita..." 
                                    class="flex-1 px-6 pr-4 py-3.5 text-gray-700 text-sm focus:outline-none bg-transparent">
                                <button type="submit" class="flex-shrink-0 px-4 py-3.5 text-blue-600 hover:text-blue-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="berita-results" class="transition-opacity duration-300">
                    @if($beritas->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            @foreach($beritas as $item)
                                <a href="{{ route('announcements.show', $item->id) }}" class="group flex flex-col backdrop-blur-md bg-white/60 rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                                    {{-- Image Slider Header --}}
                                    <div class="h-56 relative overflow-hidden bg-gray-100">
                                        @if($item->images && $item->images->count() > 0)
                                            <div class="flex transition-transform duration-500 ease-in-out h-full" id="slider-{{ $item->id }}">
                                                @foreach($item->images as $img)
                                                    <img src="{{ Storage::url($img->image_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover flex-shrink-0">
                                                @endforeach
                                            </div>
                                            @if($item->images->count() > 1)
                                                <div class="absolute bottom-2 right-2 bg-black/50 text-white text-xs px-2 py-1 rounded-md backdrop-blur-sm flex items-center gap-1">
                                                    <i class='bx bx-images'></i> {{ $item->images->count() }} Foto
                                                </div>
                                            @endif
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-6xl opacity-50 bg-gradient-to-br from-blue-50 to-blue-100">📰</div>
                                        @endif
                                        <div class="absolute top-4 left-4">
                                            <span class="px-4 py-1.5 bg-blue-500 text-white rounded-full text-xs font-bold shadow-md flex items-center gap-1.5"><i class="bx bx-news"></i> Berita Daerah</span>
                                        </div>
                                    </div>

                                    {{-- Content --}}
                                    <div class="p-6 flex-1 flex flex-col">
                                        <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-3 uppercase tracking-wider">
                                            <span class="text-blue-500 flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                {{ $item->created_at->format('d M Y') }}
                                            </span>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">{{ $item->title }}</h3>
                                        <p class="text-gray-600 line-clamp-3 text-sm leading-relaxed">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($item->description), 120) }}
                                        </p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-12 flex justify-center">
                            {{ $beritas->links() }}
                        </div>
                    @else
                        <div class="backdrop-blur-sm bg-white/70 rounded-3xl text-center py-20 border border-white/80 shadow-lg">
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Belum Ada Berita</h3>
                            <p class="text-gray-600">Belum ada berita daerah yang dipublikasikan saat ini.</p>
                        </div>
                    @endif
                    </div>
                </div>

                {{-- TAB PENGUMUMAN --}}
                <div id="tab-content-pengumuman" class="tab-content" style="display: {{ $activeTab === 'pengumuman' ? 'block' : 'none' }};">
                    
                    {{-- Filter & Search Pengumuman --}}
                    <div class="max-w-4xl mx-auto mb-10">
                        <div class="backdrop-blur-md bg-white/60 rounded-2xl p-4 md:p-6 border border-gray-100 shadow-sm flex flex-col md:flex-row gap-6 justify-between items-center">
                            <div class="flex flex-wrap gap-2 justify-center items-center">
                                @php $currentType = request('type', ''); @endphp
                                <a href="{{ route('announcements.index', ['tab' => 'pengumuman', 'search' => request('search')]) }}" 
                                   class="{{ !$currentType ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200' }} px-5 py-2 rounded-full font-semibold text-sm transition-all border-2">Semua</a>
                                <a href="{{ route('announcements.index', ['tab' => 'pengumuman', 'type' => 'Pengumuman', 'search' => request('search')]) }}" 
                                   class="{{ $currentType === 'Pengumuman' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200' }} px-5 py-2 rounded-full font-semibold text-sm transition-all border-2">Pengumuman</a>
                                <a href="{{ route('announcements.index', ['tab' => 'pengumuman', 'type' => 'Event', 'search' => request('search')]) }}" 
                                   class="{{ $currentType === 'Event' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200' }} px-5 py-2 rounded-full font-semibold text-sm transition-all border-2">Event</a>
                                <a href="{{ route('announcements.index', ['tab' => 'pengumuman', 'type' => 'Gotong Royong', 'search' => request('search')]) }}" 
                                   class="{{ $currentType === 'Gotong Royong' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200' }} px-5 py-2 rounded-full font-semibold text-sm transition-all border-2">Gotong Royong</a>
                            </div>
                            <div class="flex-1 max-w-xs relative group w-full">
                                <form action="{{ route('announcements.index') }}" method="GET" class="w-full h-full">
                                    <input type="hidden" name="tab" value="pengumuman">
                                    @if(request()->has('type'))
                                        <input type="hidden" name="type" value="{{ request('type') }}">
                                    @endif
                                    <!-- Gradient Border -->
                                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 via-blue-400 to-amber-400 rounded-full opacity-80 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    
                                    <!-- Search Input -->
                                    <div class="relative flex items-center bg-white rounded-full overflow-hidden h-full">
                                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pengumuman..." 
                                            class="flex-1 pl-6 pr-4 py-2 text-gray-700 text-sm focus:outline-none bg-transparent">
                                        <button type="submit" class="flex-shrink-0 px-4 text-blue-600 hover:text-blue-700 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div id="pengumuman-results" class="transition-opacity duration-300">
                    @if($pengumumans->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            @foreach($pengumumans as $item)
                                <a href="{{ route('announcements.show', $item->id) }}" class="group flex flex-col backdrop-blur-md bg-white/60 rounded-2xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                                    
                                    {{-- Image Header --}}
                                    <div class="h-56 relative overflow-hidden bg-gray-100">
                                        @if($item->image_path)
                                            <img src="{{ Storage::url($item->image_path) }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-6xl opacity-50 bg-gradient-to-br from-blue-50 to-blue-100">
                                                @if($item->type == 'Pengumuman') 📢 
                                                @elseif($item->type == 'Event') 🎉
                                                @else 🤝
                                                @endif
                                            </div>
                                        @endif
                                        
                                        <div class="absolute top-4 left-4">
                                            @if($item->type == 'Gotong Royong')
                                                <span class="px-4 py-1.5 bg-emerald-500 text-white rounded-full text-xs font-bold shadow-md flex items-center gap-1.5">🤝 Gotong Royong</span>
                                            @elseif($item->type == 'Event')
                                                <span class="px-4 py-1.5 bg-purple-500 text-white rounded-full text-xs font-bold shadow-md flex items-center gap-1.5">🎉 Event</span>
                                            @else
                                                <span class="px-4 py-1.5 bg-blue-500 text-white rounded-full text-xs font-bold shadow-md flex items-center gap-1.5">📢 Pengumuman</span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Content --}}
                                    <div class="p-6 flex-1 flex flex-col">
                                        <div class="flex items-center justify-between text-xs font-semibold text-gray-500 mb-3 uppercase tracking-wider">
                                            <span class="text-blue-500 flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                {{ $item->created_at->format('d M Y') }}
                                            </span>
                                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-md">
                                                @if($item->target_audience_type === 'all')
                                                    Global
                                                @else
                                                    {{ $item->targetRegion->name ?? 'Wilayah' }}
                                                @endif
                                            </span>
                                        </div>
                                        
                                        <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">{{ $item->title }}</h3>
                                        
                                        <p class="text-gray-600 line-clamp-3 mb-5 flex-1 text-sm leading-relaxed">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($item->description), 120) }}
                                        </p>

                                        @if($item->event_date)
                                        <div class="bg-blue-50/80 rounded-xl p-4 mt-auto border border-blue-100">
                                            <div class="flex items-center gap-2 text-blue-700 font-bold text-xs mb-1 uppercase tracking-wider">🗓️ Pelaksanaan:</div>
                                            <div class="text-gray-800 font-medium text-sm">{{ $item->event_date->format('d M Y, H:i') }} WIB</div>
                                            @if($item->location)
                                            <div class="text-gray-600 text-xs mt-1.5 flex items-start gap-1">
                                                <span class="mt-0.5">📍</span>
                                                <span class="truncate">{{ $item->location }}</span>
                                            </div>
                                            @endif
                                        </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <div class="mt-12 flex justify-center">
                            {{ $pengumumans->links() }}
                        </div>
                    @else
                        <div class="backdrop-blur-sm bg-white/70 rounded-3xl text-center py-20 border border-white/80 shadow-lg">
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">Belum Ada Pengumuman</h3>
                            <p class="text-gray-600">Belum ada pengumuman yang sesuai dengan kategori atau pencarian Anda saat ini.</p>
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    // Tab Navigation Logic
    function initKabarTabs() {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        if (tabBtns.length === 0) return;
        
        tabBtns.forEach(btn => {
            if (btn.dataset.tabInitialized) return;
            btn.dataset.tabInitialized = 'true';
            
            btn.addEventListener('click', function(e) {
                // Jika user mengklik elemen di dalam btn (seperti icon/text), closest menangani dengan benar
                const targetTab = this.getAttribute('data-tab');
                
                // Hide all contents
                tabContents.forEach(content => {
                    content.style.display = 'none';
                });
                
                // Remove active classes from all cards
                document.querySelectorAll('.tab-card').forEach(card => {
                    card.classList.remove('active');
                });
                
                // Show target content
                const targetContent = document.getElementById('tab-content-' + targetTab);
                if (targetContent) {
                    targetContent.style.display = 'block';
                }
                
                // Add active class to clicked card
                const targetCard = document.getElementById('tab-card-' + targetTab);
                if (targetCard) {
                    targetCard.classList.add('active');
                }
                
                // Update URL for tab switch safely without reload
                try {
                    const url = new URL(window.location);
                    url.searchParams.set('tab', targetTab);
                    window.history.pushState({}, '', url);
                } catch(err) {}
            });
        });
    }

    // PJAX Logic untuk form pencarian dan filter
    function initPjax() {
        ['berita', 'pengumuman'].forEach(tab => {
            const wrapper = document.getElementById('tab-content-' + tab);
            if (!wrapper) return;
            
            // Cegah duplicate listener jika Turbo jalan berkali-kali
            if (wrapper.dataset.pjaxInitialized) return;
            wrapper.dataset.pjaxInitialized = 'true';
            
            // Tangkap klik pada link (Filter / Paginasi)
            wrapper.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link && link.href && link.href.startsWith('http')) {
                    const path = new URL(link.href).pathname;
                    
                    // Kalau path mengarah ke detail (/kabar-daerah/{id}), biarkan navigasi normal
                    const isDetailRegex = /\/kabar-daerah\/[a-zA-Z0-9_-]+$/;
                    if (isDetailRegex.test(path)) return; 
                    
                    // Jika path adalah /kabar-daerah (index)
                    if (path.includes('/kabar-daerah')) {
                        e.preventDefault();
                        performAjax(link.href, tab);
                    }
                }
            });
            
            // Tangkap submit pada form pencarian
            wrapper.addEventListener('submit', function(e) {
                const form = e.target.closest('form');
                if (form) {
                    e.preventDefault();
                    
                    // Bangun URL dengan query params dari form
                    const url = new URL(form.action);
                    const formData = new FormData(form);
                    
                    // Karena form kita punya method GET, masukkan data ke query string
                    for (const [key, value] of formData.entries()) {
                        url.searchParams.set(key, value);
                    }
                    
                    performAjax(url.toString(), tab);
                }
            });
        });
    }

    // Fungsi Fetch HTML
    async function performAjax(url, tab) {
        const wrapper = document.getElementById('tab-content-' + tab);
        if (!wrapper) return;
        
        // Cari container hasil (pengumuman-results atau berita-results)
        // Jika tidak ada, pakai wrapper langsung
        const resultsId = tab === 'pengumuman' ? 'pengumuman-results' : 'berita-results';
        let resultsContainer = wrapper.querySelector('#' + resultsId);
        if (!resultsContainer) {
            // Fallback: cari div terakhir yang berisi grid atau pesan kosong
            resultsContainer = wrapper.querySelector('.grid, .backdrop-blur-sm');
            if (resultsContainer) resultsContainer = resultsContainer.parentElement;
        }
        
        // Tampilkan loading skeleton di dalam kotak
        const loadingHtml = `
            <div class="backdrop-blur-sm bg-white/70 rounded-3xl text-center py-20 border border-white/80 shadow-lg">
                <div class="pjax-dots">
                    <span></span><span></span><span></span>
                </div>
                <p class="pjax-loading-label">Memuat data...</p>
            </div>
        `;
        
        if (resultsContainer) {
            resultsContainer.innerHTML = loadingHtml;
        }
        
        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            const html = await response.text();
            
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            const newContent = doc.getElementById('tab-content-' + tab);
            if (newContent) {
                wrapper.innerHTML = newContent.innerHTML;
                
                // Efek fade-in slide-up hanya pada area hasil (results container)
                const newResultsId = tab === 'pengumuman' ? 'pengumuman-results' : 'berita-results';
                const newResults = wrapper.querySelector('#' + newResultsId);
                if (newResults) {
                    // Set awal: sembunyikan dulu
                    newResults.style.opacity = '0';
                    newResults.style.transform = 'translateY(20px)';
                    
                    // Tunggu 1 frame agar browser render, lalu animasikan masuk
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            newResults.style.transition = 'opacity 0.4s ease-out, transform 0.4s ease-out';
                            newResults.style.opacity = '1';
                            newResults.style.transform = 'translateY(0)';
                        });
                    });
                }
                
                initSlider();
            }
            
            window.history.pushState({}, '', url);
            
        } catch(err) {
            console.error('AJAX Error:', err);
            window.location.href = url;
        }
    }

    function initSlider() {
        document.querySelectorAll('[id^="slider-"]').forEach(slider => {
            if (slider.children.length > 1 && !slider.dataset.initialized) {
                slider.dataset.initialized = 'true';
                // Timer global akan menangkap ini
            }
        });
    }

    function initAll() {
        initKabarTabs();
        initPjax();
        initSlider();
    }

    // Jalankan inisiasi saat DOM siap
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
    
    // Support untuk Turbo/Livewire jika ada
    document.addEventListener('turbo:load', initAll);
</script>
@endpush

@push('styles')
<style>
    * { font-family: 'Inter', sans-serif; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    .animate-section { animation: fadeIn 0.6s ease-out forwards; opacity: 0; }
    .animate-section:nth-child(1) { animation-delay: 0.1s; }
    .animate-section:nth-child(2) { animation-delay: 0.2s; }
    .animate-section:nth-child(3) { animation-delay: 0.3s; }
    
    /* Custom Active Style for Tabs */
    .tab-card.active {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }
    
    /* Bouncing Dots */
    .pjax-dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-bottom: 16px;
    }
    .pjax-dots span {
        width: 12px; height: 12px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        animation: pjaxBounce 1.4s ease-in-out infinite;
    }
    .pjax-dots span:nth-child(2) { animation-delay: 0.2s; }
    .pjax-dots span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes pjaxBounce {
        0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
        40% { transform: scale(1.2); opacity: 1; }
    }
    
    .pjax-loading-label {
        font-size: 15px;
        font-weight: 600;
        color: #6b7280;
        margin: 0;
    }
    
    /* Fade-in slide-up untuk konten baru setelah AJAX */
    .pjax-fade-in {
        animation: pjaxSlideUp 0.45s ease-out both;
    }
    @keyframes pjaxSlideUp {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto Slider for images
    setInterval(() => {
        document.querySelectorAll('[id^="slider-"]').forEach(slider => {
            if(slider.children.length > 1) {
                let first = slider.firstElementChild;
                slider.style.transition = 'transform 0.5s ease-in-out';
                slider.style.transform = `translateX(-100%)`;
                setTimeout(() => {
                    slider.style.transition = 'none';
                    slider.appendChild(first);
                    slider.style.transform = 'translateX(0)';
                }, 500);
            }
        });
    }, 3000);
</script>

<script>
// Background Parallax Script (Keep it as it was)
(function() {
    function initCanvas() {
        const canvas = document.getElementById('abstract-canvas');
        if(!canvas) return;
        const ctx = canvas.getContext('2d');
        let width, height;
        let waves = [];
        
        let mouse = { x: 0, y: 0 };
        let targetMouse = { x: 0, y: 0 };
        let scrollY = 0;

        function resize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
            initWaves();
        }

        window.addEventListener('resize', resize);
        
        window.addEventListener('mousemove', (e) => {
            targetMouse.x = e.clientX;
            targetMouse.y = e.clientY;
        });

        window.addEventListener('scroll', () => {
            scrollY = window.scrollY;
        });

        class Wave {
            constructor(colorFunc, heightPercent, amplitude, speed, offset) {
                this.colorFunc = colorFunc;
                this.heightPercent = heightPercent;
                this.amplitude = amplitude;
                this.speed = speed;
                this.offset = offset;
                this.points = [];
                this.time = 0;
            }
            init() {
                this.points = [];
                for(let i = 0; i <= width + 50; i += 50) {
                    this.points.push(i);
                }
            }
            update() {
                this.time += this.speed;
            }
            draw() {
                ctx.beginPath();
                ctx.moveTo(0, height);
                for(let i = 0; i < this.points.length; i++) {
                    let x = this.points[i];
                    let dx = x - mouse.x;
                    let dy = (height * this.heightPercent) - mouse.y;
                    let dist = Math.sqrt(dx*dx + dy*dy);
                    let repel = 0;
                    
                    if(dist < 400) {
                        repel = (400 - dist) * 0.15;
                    }

                    let y = height * this.heightPercent 
                          + Math.sin(this.time + (x * 0.005) + this.offset) * this.amplitude
                          + repel;
                    ctx.lineTo(x, y);
                }
                ctx.lineTo(width, height);
                ctx.fillStyle = this.colorFunc(ctx, width, height);
                ctx.fill();
            }
        }

        function initWaves() {
            waves = [
                new Wave((ctx, w, h) => {
                    let grad = ctx.createLinearGradient(0, h*0.5, 0, h);
                    grad.addColorStop(0, 'rgba(230, 240, 255, 1)');
                    grad.addColorStop(1, 'rgba(255, 255, 255, 1)');
                    return grad;
                }, 0.65, 20, 0.005, 0),

                new Wave((ctx, w, h) => {
                    let grad = ctx.createLinearGradient(0, h*0.6, 0, h*1.2);
                    grad.addColorStop(0, 'rgba(255, 255, 255, 1)');
                    grad.addColorStop(1, 'rgba(245, 250, 255, 0.5)');
                    return grad;
                }, 0.75, 30, 0.003, 500),

                new Wave((ctx, w, h) => {
                    let grad = ctx.createLinearGradient(0, h*0.7, 0, h*1.1);
                    grad.addColorStop(0, 'rgba(245, 225, 130, 0.5)');
                    grad.addColorStop(1, 'rgba(255, 255, 255, 0)');
                    return grad;
                }, 0.85, 45, 0.007, 700)
            ];
            waves.forEach(w => w.init());
        }

        function animate() {
            mouse.x += (targetMouse.x - mouse.x) * 0.1;
            mouse.y += (targetMouse.y - mouse.y) * 0.1;
            ctx.fillStyle = '#e8eff5'; 
            ctx.fillRect(0, 0, width, height);
            ctx.save();
            ctx.translate(0, -scrollY * 0.4); 
            let glowX = width * 0.15;
            let glowY = height * 0.4;
            let gradGlow = ctx.createRadialGradient(glowX, glowY, 0, glowX, glowY, width * 0.3);
            gradGlow.addColorStop(0, 'rgba(245, 235, 150, 0.15)');
            gradGlow.addColorStop(1, 'rgba(245, 235, 150, 0)');
            ctx.fillStyle = gradGlow;
            ctx.beginPath();
            ctx.arc(glowX, glowY, width * 0.3, 0, Math.PI*2);
            ctx.fill();
            waves.forEach(w => { w.update(); w.draw(); });
            ctx.restore();
            requestAnimationFrame(animate);
        }
        resize();
        animate();
    };
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initCanvas); } else { initCanvas(); }
})();
</script>
@endpush
