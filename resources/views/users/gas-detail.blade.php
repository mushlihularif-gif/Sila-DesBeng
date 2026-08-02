@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    <section class="relative z-10 min-h-screen pt-32 pb-16">
        <!-- Animated Canvas Background -->
        <canvas id="gas-canvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none;"></canvas>

        <div class="max-w-6xl mx-auto px-6 relative z-10">
            <!-- Header Section with Gradient Text - LEFT ALIGNED -->
            <div class="mb-12 mt-12">
                <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">
                    Detail
                </h1>
            </div>

            <!-- Detail Card - HORIZONTAL LAYOUT -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-10">
                <div class="flex flex-col lg:flex-row gap-8">
                    <!-- Left Side: Product Image + Location -->
                    <div class="lg:w-5/12 flex-shrink-0">
                        <!-- Product Image Carousel -->
                        <div class="relative aspect-square overflow-hidden rounded-2xl shadow-lg mb-6 group w-full">
                            @php
                                $images = collect([$item->foto, $item->foto_2, $item->foto_3])->filter()->values();
                                $hasMultipleImages = $images->count() > 1;
                            @endphp

                            <!-- Images Container -->
                            <div id="product-carousel" class="flex w-full h-full transition-transform duration-500 ease-out">
                                @foreach($images as $index => $image)
                                <div class="w-full h-full flex-shrink-0 flex-grow-0">
                                    <img src="{{ asset('storage/' . $image) }}" 
                                         alt="{{ $item->jenis_gas }} - Image {{ $index + 1 }}"
                                         class="w-full h-full object-contain drop-shadow-xl product-image">
                                </div>
                                @endforeach
                            </div>

                            @if($hasMultipleImages)
                            <!-- Navigation Buttons -->
                            <button id="carousel-prev" type="button"
                                    class="absolute left-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 rounded-full p-2 shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>

                            <button id="carousel-next" type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white text-gray-800 rounded-full p-2 shadow-lg opacity-0 group-hover:opacity-100 transition-all duration-300 hover:scale-110 z-10">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            <!-- Indicators -->
                            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                                @foreach($images as $index => $image)
                                <button type="button"
                                        class="carousel-indicator {{ $index === 0 ? 'w-8 bg-white' : 'w-2.5 bg-white/50' }} h-2.5 rounded-full shadow-md transition-all duration-300 hover:bg-white/75"
                                        data-slide="{{ $index }}">
                                </button>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <!-- Location - Below Image -->
                        @if($item->latitude && $item->longitude)
                        {{-- Produk punya koordinat sendiri: tampilkan sebagai link ke Google Maps --}}
                        <a href="https://www.google.com/maps?q={{ $item->latitude }},{{ $item->longitude }}" 
                           target="_blank"
                           class="flex items-center gap-2 text-gray-700 hover:text-blue-600 transition-colors">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium text-base">{{ $item->lokasi ?? 'Lihat di Peta' }}</span>
                        </a>
                        @elseif($item->lokasi)
                        {{-- Produk punya teks lokasi tapi tanpa koordinat: tampilkan teks biasa (tidak bisa diklik) --}}
                        <div class="flex items-center gap-2 text-gray-600">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium text-base">{{ $item->lokasi }}</span>
                        </div>
                        @elseif($setting && $setting->latitude && $setting->longitude)
                        {{-- Fallback: gunakan lokasi pusat BUMDes dari SystemSetting --}}
                        <a href="https://www.google.com/maps?q={{ $setting->latitude }},{{ $setting->longitude }}" 
                           target="_blank"
                           class="flex items-center gap-2 text-gray-700 hover:text-blue-600 transition-colors">
                            <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="font-medium text-base">{{ $setting->location_name ?? 'Lokasi BUMDes' }}</span>
                        </a>
                        @endif
                    </div>

                    <!-- Right Side: Product Information -->
                    <div class="lg:w-7/12 flex flex-col">
                        <!-- Product Name -->
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">{{ $item->jenis_gas }}</h2>

                        <!-- Description -->
                        <p class="text-gray-600 text-justify mb-6 leading-relaxed text-sm">
                            {{ $item->deskripsi }}
                        </p>

                        <!-- Product Details Grid -->
                        <div class="space-y-2 mb-6">
                            <!-- Stock -->
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium text-sm">Stok Tersedia</span>
                                <span class="text-gray-800 font-semibold text-sm">{{ $item->stok }} {{ $item->satuan }}</span>
                            </div>

                            <!-- Status -->
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium text-sm">Status</span>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                    {{ $item->status == 'tersedia' ? 'bg-green-100 text-green-700' : 
                                       ($item->status == 'dipesan' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </div>

                            <!-- Category -->
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium text-sm">Kategori</span>
                                <span class="text-gray-800 font-semibold text-sm">{{ $item->kategori }}</span>
                            </div>
                        </div>

                        <!-- Price -->
                        <div class="mb-6">
                            <p class="text-3xl font-bold text-red-600">Rp. {{ number_format($item->harga_satuan, 0, ',', '.') }}</p>
                        </div>

                        <!-- Quantity Selector + Order Button -->
                        <div class="flex items-center gap-4 mt-auto">
                            <!-- Quantity Selector -->
                            <div class="flex items-center gap-3 border-2 border-gray-300 rounded-full px-4 py-2">
                                <button type="button" 
                                        onclick="let q = document.getElementById('quantity'); let v = parseInt(q.value) || 1; if(v > 1) q.value = v - 1;"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                </button>
                                
                                <input type="number" 
                                       id="quantity" 
                                       value="1" 
                                       min="1" 
                                       max="{{ $item->stok }}"
                                       class="w-12 text-center text-lg font-semibold border-0 focus:outline-none focus:ring-0 p-0 bg-transparent text-gray-900">
                                
                                <button type="button" 
                                        onclick="let q = document.getElementById('quantity'); let v = parseInt(q.value) || 1; let max = {{ $item->stok ?? 0 }}; if(v < max) q.value = v + 1;"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Order Button -->
                            <button type="button" 
                                    onclick="window.location.href='{{ route('gas.booking', ['id' => $item->id]) }}?quantity=' + (document.getElementById('quantity').value || 1)"
                                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-8 rounded-full transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 text-center">
                                Pesan
                            </button>
                        </div>
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

    /* Product Image Animation */
    .product-image {
        transition: transform 0.3s ease;
    }

    .product-image:hover {
        transform: scale(1.05);
    }

    /* Smooth animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Remove spinner from number input */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .lg\:w-5\/12 {
            width: 100%;
        }
        .lg\:w-7\/12 {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<!-- Modal Peringatan KYC -->
@if(session('show_kyc_modal'))
<div id="kyc-prompt-modal" class="fixed inset-0 flex items-center justify-center p-4" style="z-index: 999999;">
    <div class="absolute inset-0" style="background-color: rgba(17, 24, 39, 0.7); backdrop-filter: blur(4px);" onclick="document.getElementById('kyc-prompt-modal').remove()"></div>
    
    <!-- Balok mirip halaman KYC -->
    <div class="relative w-full max-w-lg z-10" style="animation: fadeInUp 0.3s ease-out;">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden relative">
            <button onclick="document.getElementById('kyc-prompt-modal').remove()" class="absolute top-4 right-4 z-50 p-2 bg-gray-100 rounded-full hover:bg-gray-200 transition-colors">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Verifikasi Identitas (KYC)</h2>
                <p class="text-gray-600 mb-8">Anda harus menyelesaikan verifikasi identitas terlebih dahulu untuk dapat melanjutkan penyewaan layanan ini.</p>
                <a href="{{ route('kyc.index') }}" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-bold rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 transition">
                    Mulai Verifikasi KYC
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<script>
(() => {
    // Prevent manual input outside range
    const qtyInput = document.getElementById('quantity');
    const maxStock = {{ $item->stok ?? 0 }};
    if (qtyInput) {
        qtyInput.addEventListener('change', () => {
            let value = parseInt(qtyInput.value) || 1;
            if (value < 1) qtyInput.value = 1;
            if (value > maxStock) qtyInput.value = maxStock;
        });
    }

    // Image Carousel
    const carousel = document.getElementById('product-carousel');
    const prevBtn = document.getElementById('carousel-prev');
    const nextBtn = document.getElementById('carousel-next');
    const indicators = document.querySelectorAll('.carousel-indicator');

    if (carousel && indicators.length > 1) {
        let currentSlide = 0;
        const totalSlides = indicators.length;
        let autoSlideInterval;
        const autoSlideDelay = 5000; // 5 seconds

        const goToSlide = (slideIndex) => {
            currentSlide = slideIndex;
            carousel.style.transform = `translateX(-${slideIndex * 100}%)`;

            // Update indicators
            indicators.forEach((indicator, index) => {
                if (index === slideIndex) {
                    indicator.classList.remove('w-2.5', 'bg-white/50');
                    indicator.classList.add('w-8', 'bg-white');
                } else {
                    indicator.classList.remove('w-8', 'bg-white');
                    indicator.classList.add('w-2.5', 'bg-white/50');
                }
            });
        };

        const nextSlide = () => {
            currentSlide = (currentSlide + 1) % totalSlides;
            goToSlide(currentSlide);
        };

        const prevSlide = () => {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            goToSlide(currentSlide);
        };

        const startAutoSlide = () => {
            clearInterval(autoSlideInterval);
            autoSlideInterval = setInterval(nextSlide, autoSlideDelay);
        };

        const resetAutoSlide = () => {
            clearInterval(autoSlideInterval);
            startAutoSlide();
        };

        // Navigation buttons
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                prevSlide();
                resetAutoSlide();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                nextSlide();
                resetAutoSlide();
            });
        }

        // Indicator buttons
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                goToSlide(index);
                resetAutoSlide();
            });
        });

        // Start auto-slide
        startAutoSlide();

        // Pause on hover
        const carouselContainer = carousel.parentElement;
        if (carouselContainer) {
            carouselContainer.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
            carouselContainer.addEventListener('mouseleave', startAutoSlide);
        }
    }

    // Order logic is now handled via inline onclick in the button html

    // Smooth scroll to top on page load
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Animated Canvas Background
    const initCanvas = () => {
        const canvas = document.getElementById('gas-canvas');
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
        window.addEventListener('mousemove', (e) => { targetMouse.x = e.clientX; targetMouse.y = e.clientY; });
        window.addEventListener('mouseout', () => { targetMouse.x = -1000; targetMouse.y = -1000; });

        let scrollY = window.scrollY;
        window.addEventListener('scroll', () => { scrollY = window.scrollY; });

        class Wave {
            constructor(getGradient, yOffset, amplitude, speed, wavelength) {
                this.getGradient = getGradient;
                this.yOffset = yOffset; this.amplitude = amplitude;
                this.speed = speed; this.wavelength = wavelength;
                this.points = []; this.time = Math.random() * 100;
            }
            init() {
                this.points = [];
                let n = Math.ceil(width / 25) + 2;
                for (let i = 0; i < n; i++) {
                    let x = (i - 1) * 25;
                    let baseY = height * this.yOffset;
                    this.points.push({ x, baseY, y: baseY + Math.sin(this.time + x / this.wavelength) * this.amplitude, vy: 0, spring: 0.05, friction: 0.90 });
                }
            }
            update() {
                this.time += this.speed;
                for (let p of this.points) {
                    let tY = p.baseY + Math.sin(this.time + p.x / this.wavelength) * this.amplitude;
                    let dx = mouse.x - p.x, dy = mouse.y - tY;
                    let dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < 200) { let f = Math.pow((200 - dist) / 200, 2); tY += (dy > 0 ? -1 : 1) * f * 60; }
                    p.vy += (tY - p.y) * p.spring; p.vy *= p.friction; p.y += p.vy;
                }
            }
            draw() {
                ctx.beginPath();
                ctx.moveTo(this.points[0].x, this.points[0].y);
                for (let i = 0; i < this.points.length - 1; i++) {
                    let cx = (this.points[i].x + this.points[i+1].x) / 2;
                    let cy = (this.points[i].y + this.points[i+1].y) / 2;
                    ctx.quadraticCurveTo(this.points[i].x, this.points[i].y, cx, cy);
                }
                let last = this.points[this.points.length - 1];
                ctx.lineTo(last.x, last.y);
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
                new Wave((ctx,w,h) => { let g = ctx.createLinearGradient(0,h*.5,0,h*1.2); g.addColorStop(0,'rgba(140,190,250,0.7)'); g.addColorStop(1,'rgba(180,215,255,0.1)'); return g; }, 0.65, 40, 0.005, 600),
                new Wave((ctx,w,h) => { let g = ctx.createLinearGradient(0,h*.6,0,h*1.2); g.addColorStop(0,'rgba(255,255,255,1)'); g.addColorStop(1,'rgba(245,250,255,0.5)'); return g; }, 0.75, 30, 0.003, 500),
                new Wave((ctx,w,h) => { let g = ctx.createLinearGradient(0,h*.7,0,h*1.1); g.addColorStop(0,'rgba(245,225,130,0.5)'); g.addColorStop(1,'rgba(255,255,255,0)'); return g; }, 0.85, 45, 0.007, 700),
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

            let gx = width * 0.15, gy = height * 0.4;
            let grad = ctx.createRadialGradient(gx, gy, 0, gx, gy, width * 0.3);
            grad.addColorStop(0, 'rgba(245,235,150,0.15)');
            grad.addColorStop(1, 'rgba(245,235,150,0)');
            ctx.fillStyle = grad;
            ctx.beginPath(); ctx.arc(gx, gy, width * 0.3, 0, Math.PI * 2); ctx.fill();

            waves.forEach(w => { w.update(); w.draw(); });

            ctx.save();
            ctx.translate(width * 0.9, height * 0.08);
            let dxD = mouse.x - width * 0.9, dyD = mouse.y - height * 0.08;
            let distD = Math.sqrt(dxD*dxD + dyD*dyD);
            if (distD < 300) { let f = (300-distD)/300; ctx.translate(-dxD/distD*f*20, -dyD/distD*f*20); }
            ctx.rotate(Math.PI / 4);
            ctx.fillStyle = 'rgba(74,144,226,0.4)'; ctx.fillRect(-15,-15,30,30);
            ctx.fillStyle = 'rgba(120,175,240,0.3)'; ctx.fillRect(5,5,25,25);
            ctx.restore();
            ctx.restore();

            requestAnimationFrame(animate);
        }

        resize();
        animate();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCanvas);
    } else {
        initCanvas();
    }
})();
</script>
@endpush
```
