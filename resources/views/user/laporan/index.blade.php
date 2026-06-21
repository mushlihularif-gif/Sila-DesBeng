@extends('layouts.user')

@section('title', 'Laporan Saya')

@push('styles')
<style>
    .btn-outline {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 12px 28px; font-size: 1rem; font-weight: 700;
        color: #2563eb; background: transparent;
        border: 2px solid #2563eb; border-radius: 9999px;
        cursor: pointer; transition: all 0.4s ease;
        position: relative; overflow: hidden;
        text-decoration: none;
    }
    .btn-outline::before {
        content: ''; position: absolute; inset: 0;
        background: #2563eb; transform: translateY(100%);
        transition: transform 0.4s ease;
    }
    .btn-outline:hover { color: #fff !important; }
    .btn-outline:hover::before { transform: translateY(0); }
    .btn-outline span, .btn-outline svg { position: relative; z-index: 1; display: flex; align-items: center; gap: 8px; }
</style>
@endpush

@section('page')
<div class="min-h-screen bg-[#f0f4f8] pt-32 pb-20 text-gray-800 relative">
    {{-- Custom Vector Abstract Background --}}
    <div class="fixed inset-0 overflow-hidden z-0 pointer-events-none" id="premium-bg">
        <canvas id="abstract-canvas" class="w-full h-full absolute inset-0"></canvas>
    </div>
    <div class="max-w-7xl mx-auto px-6 relative z-10">
        
        <div class="text-center md:text-left mb-8 animate-section" data-aos="fade-down">
            <h1 class="hero-title animate-fade-in-up" style="font-size: 3rem;">
                <span class="hero-title-gold">Laporan Saya</span>
            </h1>
        </div>

        {{-- Header dengan Avatar --}}
        <div class="mb-10" data-aos="fade-down" data-aos-delay="100">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-6 bg-white/80 backdrop-blur-md p-6 rounded-2xl border border-gray-100 shadow-sm">
                {{-- User Info dengan Avatar --}}
                <div class="flex items-center gap-5">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset(Auth::user()->avatar) }}" 
                             alt="{{ Auth::user()->name }}"
                             class="w-16 h-16 rounded-full object-cover border-2 border-blue-400 shadow-md">
                    @else
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full flex items-center justify-center border-2 border-blue-200 shadow-md">
                            <span class="text-2xl font-bold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </div>
                    @endif
                    
                    <div>
                        <p class="text-xl md:text-2xl text-gray-800 mb-1">
                            <span class="font-bold text-[#115789]">{{ Auth::user()->name }}</span>
                        </p>
                        <p class="text-sm md:text-base text-gray-500">
                            {{ Auth::user()->email }}
                        </p>
                    </div>
                </div>
                
                {{-- Button Buat Laporan --}}
                <a href="{{ route('user.laporan.create') }}" class="btn-outline shadow-sm hover:shadow-lg mt-2 md:mt-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    <span>Buat Laporan Baru</span>
                </a>
            </div>
        </div>

        {{-- Alert Success --}}
        @if(session('success'))
            <div class="mb-8 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm" data-aos="fade-up">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Statistik Singkat --}}
        @php
            $totalLaporan = $laporans->total();
            $pending = \App\Models\Laporan::where('user_id', Auth::id())->where('status', 'Pending')->count();
            $proses = \App\Models\Laporan::where('user_id', Auth::id())->whereIn('status', ['Proses', 'Diproses'])->count();
            $selesai = \App\Models\Laporan::where('user_id', Auth::id())->where('status', 'Selesai')->count();
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white/80 backdrop-blur-md shadow-sm hover:shadow-md transition-shadow border border-gray-100 rounded-2xl p-6 text-gray-800" data-aos="fade-up" data-aos-delay="100">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <p class="text-gray-500 font-medium text-sm">Total Laporan</p>
                </div>
                <h3 class="text-3xl font-bold text-[#1e3a5f]">{{ $totalLaporan }}</h3>
            </div>
            
            <div class="bg-white/80 backdrop-blur-md shadow-sm hover:shadow-md transition-shadow border border-gray-100 rounded-2xl p-6 text-gray-800" data-aos="fade-up" data-aos-delay="200">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-yellow-50 rounded-lg text-yellow-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-gray-500 font-medium text-sm">Pending</p>
                </div>
                <h3 class="text-3xl font-bold text-yellow-500">{{ $pending }}</h3>
            </div>
            
            <div class="bg-white/80 backdrop-blur-md shadow-sm hover:shadow-md transition-shadow border border-gray-100 rounded-2xl p-6 text-gray-800" data-aos="fade-up" data-aos-delay="300">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </div>
                    <p class="text-gray-500 font-medium text-sm">Diproses</p>
                </div>
                <h3 class="text-3xl font-bold text-purple-500">{{ $proses }}</h3>
            </div>
            
            <div class="bg-white/80 backdrop-blur-md shadow-sm hover:shadow-md transition-shadow border border-gray-100 rounded-2xl p-6 text-gray-800" data-aos="fade-up" data-aos-delay="400">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-green-50 rounded-lg text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <p class="text-gray-500 font-medium text-sm">Selesai</p>
                </div>
                <h3 class="text-3xl font-bold text-green-500">{{ $selesai }}</h3>
            </div>
        </div>

        {{-- Tabel Laporan --}}
        <div class="bg-white/80 backdrop-blur-md shadow-sm border border-gray-100 rounded-2xl overflow-hidden" data-aos="fade-up">
            @if($laporans->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Lokasi</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-transparent">
                            @foreach($laporans as $laporan)
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium border border-blue-200">
                                            {{ $laporan->kategori }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <span class="flex items-start gap-1">
                                            <span class="text-gray-400 mt-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></span>
                                            {{ Str::limit($laporan->lokasi, 30) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ Str::limit($laporan->deskripsi, 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($laporan->status === 'Pending')
                                            <span class="inline-flex items-center justify-center px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium border border-yellow-200 min-w-[90px]">
                                                Pending
                                            </span>
                                        @elseif(in_array($laporan->status, ['Proses', 'Diproses']))
                                            <span class="inline-flex items-center justify-center px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium border border-purple-200 min-w-[90px]">
                                                Diproses
                                            </span>
                                        @elseif($laporan->status === 'Selesai')
                                            <span class="inline-flex items-center justify-center px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium border border-green-200 min-w-[90px]">
                                                Selesai
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium border border-red-200 min-w-[90px]">
                                                {{ $laporan->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $laporan->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <a href="{{ route('user.laporan.show', $laporan->id) }}" 
                                           class="inline-flex items-center gap-1 px-4 py-2 bg-white text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors font-medium text-xs">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 bg-gray-50/80 border-t border-gray-100">
                    {{ $laporans->links() }}
                </div>
            @else
                <div class="p-12 text-center bg-transparent">
                    <div class="inline-flex justify-center items-center w-20 h-20 rounded-full bg-blue-50 text-blue-500 mb-4">
                        <svg class="w-12 h-12 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-[#1e3a5f] mb-3">Belum Ada Laporan</h3>
                    <p class="text-gray-500 mb-8 max-w-md mx-auto">Anda belum membuat laporan apapun. Mari mulai berpartisipasi dengan membuat laporan pertama Anda.</p>
                    <a href="{{ route('user.laporan.create') }}" 
                       class="sd-btn-register hover:-translate-y-1 transition-transform duration-300" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: bold;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        <span>Buat Laporan Pertama</span>
                    </a>
                </div>
            @endif
        </div>

    </div>
</div>

<script>
// Auto-hide success alert
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.querySelector('.bg-green-50');
    if (alert) {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    }
});
</script>
@endsection<script>
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

