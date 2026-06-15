@extends('layouts.user')

@section('title', 'Kabar Daerah & Event - SiladesBeng')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-40 pb-16">
        {{-- Custom Vector Abstract Background --}}
        <div class="fixed inset-0 overflow-hidden z-0" id="premium-bg">
            <canvas id="abstract-canvas" class="w-full h-full absolute inset-0"></canvas>
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10" x-data="kabarDaerah()">
            {{-- Header Section --}}
            <div class="text-center mb-12 animate-section">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Kabar </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Daerah & Event</span>
                </h1>
                <p class="text-gray-700 text-lg mt-2">
                    Dapatkan informasi terbaru seputar desa, pengumuman, dan acara terdekat.
                </p>
            </div>

            {{-- Filter & Search Bar --}}
            <div class="max-w-4xl mx-auto mb-12 animate-section">
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl p-4 md:p-6 border border-white/80 shadow-lg">
                    <div class="flex flex-col md:flex-row gap-6 justify-between items-center">
                        
                        {{-- Filter Pills --}}
                        <div class="flex flex-wrap gap-2 w-full md:w-auto justify-center md:justify-start">
                            <button type="button" @click.prevent="updateFilter('')" 
                               :class="!type ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-5 py-2 rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none">
                               Semua
                            </button>
                            <button type="button" @click.prevent="updateFilter('Pengumuman')" 
                               :class="type === 'Pengumuman' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-5 py-2 rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none">
                               Pengumuman
                            </button>
                            <button type="button" @click.prevent="updateFilter('Event')" 
                               :class="type === 'Event' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-5 py-2 rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none">
                               Event
                            </button>
                            <button type="button" @click.prevent="updateFilter('Gotong Royong')" 
                               :class="type === 'Gotong Royong' ? 'bg-blue-500 text-white border-blue-500 shadow-md' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-500'"
                               class="px-5 py-2 rounded-full font-semibold text-sm transition-all duration-300 border-2 focus:outline-none">
                               Gotong Royong
                            </button>
                        </div>

                        {{-- Search Input (Style gradient dari beranda) --}}
                        <div class="w-full md:w-auto flex-1 md:max-w-xs relative group">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 via-blue-400 to-amber-400 rounded-full opacity-70 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="relative flex items-center bg-white rounded-full overflow-hidden">
                                <input type="text" x-model="search" @input.debounce.500ms="fetchData()" placeholder="Cari kabar..." 
                                    class="w-full pl-6 pr-4 py-3 text-gray-700 text-sm focus:outline-none bg-transparent">
                                
                                <div class="flex-shrink-0 px-4" :class="loading ? 'text-amber-500 animate-spin' : 'text-blue-500'">
                                    <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    <svg x-show="loading" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Grid Kabar --}}
            <div id="kabar-list-container" class="transition-all duration-300" :class="{ 'opacity-50 pointer-events-none scale-[0.98]': loading }">
            @if($announcements->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-section">
                    @foreach($announcements as $item)
                        <a href="{{ route('announcements.show', $item->id) }}" class="group flex flex-col backdrop-blur-sm bg-white/70 rounded-3xl overflow-hidden border border-white/80 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                            
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
                                
                                {{-- Type Badge --}}
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
                                <div class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-3 uppercase tracking-wider">
                                    <span class="text-blue-500 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ $item->created_at->format('d M Y') }}
                                    </span>
                                    <span>•</span>
                                    <span class="text-gray-500 truncate flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        {{ $item->region->name ?? 'Pusat' }}
                                    </span>
                                </div>
                                
                                <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">{{ $item->title }}</h3>
                                
                                <p class="text-gray-600 line-clamp-3 mb-5 flex-1 text-sm leading-relaxed">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($item->description), 120) }}
                                </p>

                                @if($item->event_date)
                                <div class="bg-blue-50/80 rounded-xl p-4 mt-auto border border-blue-100">
                                    <div class="flex items-center gap-2 text-blue-700 font-bold text-xs mb-1 uppercase tracking-wider">
                                        🗓️ Pelaksanaan:
                                    </div>
                                    <div class="text-gray-800 font-medium text-sm">
                                        {{ $item->event_date->format('d M Y, H:i') }} WIB
                                    </div>
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

                {{-- Pagination --}}
                <div class="mt-12 flex justify-center animate-section">
                    {{ $announcements->links() }}
                </div>
            @else
                <div class="backdrop-blur-sm bg-white/70 rounded-3xl p-16 text-center border border-white/80 shadow-lg animate-section">
                    <div class="text-6xl mb-4">📭</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Belum Ada Kabar</h3>
                    <p class="text-gray-600">Belum ada pengumuman atau event yang diterbitkan saat ini.</p>
                </div>
            @endif
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

    .animate-section {
        animation: fadeIn 0.6s ease-out forwards;
        opacity: 0;
    }

    .animate-section:nth-child(1) { animation-delay: 0.1s; }
    .animate-section:nth-child(2) { animation-delay: 0.2s; }
    .animate-section:nth-child(3) { animation-delay: 0.3s; }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('kabarDaerah', () => ({
            type: '{{ request('type', '') }}',
            search: '{{ request('search', '') }}',
            loading: false,

            init() {
                // Intercept pagination clicks inside the container
                document.getElementById('kabar-list-container').addEventListener('click', (e) => {
                    let link = e.target.closest('a');
                    // Check if it's a pagination link by seeing if href contains 'page='
                    if (link && link.href && link.href.includes('page=')) {
                        e.preventDefault();
                        this.fetchData(link.href);
                        window.scrollTo({ top: 100, behavior: 'smooth' });
                    }
                });
            },

            updateFilter(newType) {
                this.type = newType;
                this.fetchData();
            },

            fetchData(urlOverride = null) {
                this.loading = true;
                
                let url;
                if (urlOverride) {
                    url = new URL(urlOverride);
                } else {
                    url = new URL(window.location.origin + window.location.pathname);
                    if (this.type) url.searchParams.append('type', this.type);
                    if (this.search) url.searchParams.append('search', this.search);
                }

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    
                    let newContent = doc.querySelector('#kabar-list-container').innerHTML;
                    document.querySelector('#kabar-list-container').innerHTML = newContent;
                    
                    window.history.pushState({}, '', url);
                    this.loading = false;
                })
                .catch(err => {
                    console.error(err);
                    this.loading = false;
                });
            }
        }));
    });

    // Canvas Vector Abstract Background Script
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('abstract-canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        
        let width, height;
        let mouse = { x: -1000, y: -1000 };
        let targetMouse = { x: -1000, y: -1000 };

        function resize() {
            if (width !== window.innerWidth || height !== window.innerHeight) {
                width = window.innerWidth;
                height = window.innerHeight;
                canvas.width = width;
                canvas.height = height;
                initWaves();
            }
        }

        window.addEventListener('resize', resize);

        window.addEventListener('mousemove', (e) => {
            targetMouse.x = e.clientX;
            targetMouse.y = e.clientY;
        });
        window.addEventListener('mouseout', () => {
            targetMouse.x = -1000;
            targetMouse.y = -1000;
        });

        let scrollY = window.scrollY;
        window.addEventListener('scroll', () => {
            scrollY = window.scrollY;
        });

        class Wave {
            constructor(getGradient, yOffset, amplitude, speed, wavelength) {
                this.getGradient = getGradient;
                this.yOffset = yOffset; 
                this.amplitude = amplitude; 
                this.speed = speed; 
                this.wavelength = wavelength; 
                this.points = [];
                this.time = Math.random() * 100;
            }

            init() {
                this.points = [];
                let numPoints = Math.ceil(width / 25) + 2; // Resolusi tinggi agar kursor presisi
                for(let i = 0; i < numPoints; i++) {
                    let startX = (i - 1) * 25;
                    let startBaseY = height * this.yOffset;
                    let startY = startBaseY + Math.sin(this.time + startX / this.wavelength) * this.amplitude;
                    this.points.push({
                        x: startX,
                        baseY: startBaseY,
                        y: startY,
                        vy: 0,
                        spring: 0.05, 
                        friction: 0.90 
                    });
                }
            }

            update() {
                this.time += this.speed;
                for(let i = 0; i < this.points.length; i++) {
                    let pt = this.points[i];
                    
                    // Gerakan gelombang natural
                    let targetY = pt.baseY + Math.sin(this.time + pt.x / this.wavelength) * this.amplitude;
                    
                    // Interaksi Kursor: Menyebar saat disentuh
                    let dx = mouse.x - pt.x;
                    let dy = mouse.y - targetY;
                    let distance = Math.sqrt(dx*dx + dy*dy);
                    
                    if (distance < 200) {
                        let force = Math.pow((200 - distance) / 200, 2); 
                        let pushDir = (dy > 0) ? -1 : 1; 
                        targetY += pushDir * force * 60; // Dorongan diperhalus agar tidak terlalu liar
                    }
                    
                    let forceY = (targetY - pt.y) * pt.spring;
                    pt.vy += forceY;
                    pt.vy *= pt.friction;
                    pt.y += pt.vy;
                }
            }

            draw() {
                ctx.beginPath();
                ctx.moveTo(this.points[0].x, this.points[0].y);
                
                for(let i = 0; i < this.points.length - 1; i++) {
                    let cx = (this.points[i].x + this.points[i+1].x) / 2;
                    let cy = (this.points[i].y + this.points[i+1].y) / 2;
                    ctx.quadraticCurveTo(this.points[i].x, this.points[i].y, cx, cy);
                }
                
                let last = this.points[this.points.length - 1];
                ctx.lineTo(last.x, last.y);
                // Gambar ekstra jauh ke bawah agar saat di-scroll ke atas tidak terpotong bolong
                ctx.lineTo(width, height * 2 + scrollY);
                ctx.lineTo(0, height * 2 + scrollY);
                ctx.closePath();
                
                ctx.fillStyle = this.getGradient(ctx, width, height);
                ctx.fill();
            }
        }

        let waves = [];

        function initWaves() {
            waves = [
                // 1. Biru Muda (Diturunkan dan diperlambat agar lebih tenang)
                new Wave((ctx, w, h) => {
                    let grad = ctx.createLinearGradient(0, h*0.5, 0, h*1.2);
                    grad.addColorStop(0, 'rgba(140, 190, 250, 0.7)');
                    grad.addColorStop(1, 'rgba(180, 215, 255, 0.1)');
                    return grad;
                }, 0.65, 40, 0.005, 600),

                // 2. Putih Solid (Pemisah)
                new Wave((ctx, w, h) => {
                    let grad = ctx.createLinearGradient(0, h*0.6, 0, h*1.2);
                    grad.addColorStop(0, 'rgba(255, 255, 255, 1)');
                    grad.addColorStop(1, 'rgba(245, 250, 255, 0.5)');
                    return grad;
                }, 0.75, 30, 0.003, 500),

                // 3. Kuning Amber (Lebih pudar dan gradasi halus ke putih transparan)
                new Wave((ctx, w, h) => {
                    let grad = ctx.createLinearGradient(0, h*0.7, 0, h*1.1);
                    grad.addColorStop(0, 'rgba(245, 225, 130, 0.5)'); // Agak pudar di puncak
                    grad.addColorStop(1, 'rgba(255, 255, 255, 0)'); // Pudar sempurna ke transparan
                    return grad;
                }, 0.85, 45, 0.007, 700)
            ];
            waves.forEach(w => w.init());
        }

        function animate() {
            // Lerp mouse
            mouse.x += (targetMouse.x - mouse.x) * 0.1;
            mouse.y += (targetMouse.y - mouse.y) * 0.1;

            // Background layer solid (agar saat parallax tidak bolong)
            ctx.fillStyle = '#e8eff5'; 
            ctx.fillRect(0, 0, width, height);

            ctx.save();
            // Terapkan Parallax Scrolling (Background bergerak 40% kecepatan scroll content)
            ctx.translate(0, -scrollY * 0.4); 

            // Cahaya Matahari Halus (Kiri) - Diperhalus
            let glowX = width * 0.15;
            let glowY = height * 0.4;
            let gradGlow = ctx.createRadialGradient(glowX, glowY, 0, glowX, glowY, width * 0.3);
            gradGlow.addColorStop(0, 'rgba(245, 235, 150, 0.15)'); // Opasitas diturunkan
            gradGlow.addColorStop(1, 'rgba(245, 235, 150, 0)');
            ctx.fillStyle = gradGlow;
            ctx.beginPath();
            ctx.arc(glowX, glowY, width * 0.3, 0, Math.PI*2);
            ctx.fill();

            // Gambar ombak-ombak
            waves.forEach(w => {
                w.update();
                w.draw();
            });

            // Ikon Wajik (Kanan Atas) - Dibuat lebih kecil & pudar agar tidak mendominasi
            ctx.save();
            ctx.translate(width * 0.9, height * 0.08);
            
            // Parallax menjauh dari kursor
            let dxD = mouse.x - (width * 0.9);
            let dyD = mouse.y - (height * 0.08);
            let distD = Math.sqrt(dxD*dxD + dyD*dyD);
            if(distD < 300) {
                let f = (300 - distD)/300;
                ctx.translate(-(dxD/distD)*f*20, -(dyD/distD)*f*20);
            }

            ctx.rotate(Math.PI / 4);
            
            ctx.fillStyle = 'rgba(74, 144, 226, 0.4)';
            ctx.fillRect(-15, -15, 30, 30);
            
            ctx.fillStyle = 'rgba(120, 175, 240, 0.3)';
            ctx.fillRect(5, 5, 25, 25);
            
            ctx.strokeStyle = 'rgba(150, 190, 250, 0.4)';
            ctx.lineWidth = 1.5;
            ctx.strokeRect(20, 20, 15, 15);

            ctx.restore(); // Restore efek rotasi wajik
            ctx.restore(); // Restore efek Parallax Scroll

            requestAnimationFrame(animate);
        }

        resize();
        animate();
    });
</script>
@endpush
