@extends('layouts.user')

@section('title', 'Katalog Pasar Daerah - SilaDesBeng')

@section('page')
<main class="flex-grow relative w-full bg-gray-50/50">
    <section class="relative z-10 min-h-screen pt-32 pb-16">
        {{-- Custom Vector Abstract Background (Kabar Daerah Style) --}}
        <div class="fixed inset-0 overflow-hidden z-0" id="premium-bg">
            <canvas id="abstract-canvas" class="w-full h-full absolute inset-0"></canvas>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 relative z-10">
            {{-- Header Section --}}
            <div class="text-center mb-8 animate-section mt-4">
                <h1 class="text-3xl md:text-4xl font-bold mb-3">
                    <span class="text-gray-800">Katalog </span>
                    <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pasar Daerah</span>
                </h1>
                <p class="text-gray-600 text-sm md:text-base max-w-2xl mx-auto">
                    Temukan dan dukung produk asli karya masyarakat Kabupaten Bengkalis.
                </p>
            </div>

            <!-- E-Commerce Style Search & Filter (Clean, Floating, No Bulky Box) -->
            <form action="{{ route('pasar.index') }}" method="GET" id="filterForm" class="max-w-4xl mx-auto mb-10 animate-section px-2">
                
                <!-- Floating Search Bar -->
                <div class="relative w-full mb-6 group">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-[#115789] via-blue-400 to-[#60a5fa] rounded-full opacity-0 group-hover:opacity-40 transition-opacity duration-300 blur"></div>
                    <div class="relative flex items-center bg-white rounded-full shadow-md hover:shadow-lg transition-shadow overflow-hidden border border-gray-100">
                        <i class="fas fa-search text-gray-400 ml-5 text-lg"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="flex-1 border-none focus:ring-0 px-4 py-3.5 text-gray-700 bg-transparent w-full text-base placeholder-gray-400" 
                               placeholder="Cari produk di Pasar Daerah...">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3.5 font-bold transition-colors text-sm">
                            Cari
                        </button>
                    </div>
                </div>

                <!-- Categories (Pills ala Sewa Alat) -->
                <div class="flex flex-wrap justify-center gap-2 sm:gap-3 mb-5">
                    <button type="button" onclick="setCategory('all')" class="px-5 py-2 rounded-full font-semibold text-[13px] transition-all duration-300 border shadow-sm {{ request('kategori', 'all') == 'all' ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-600 border-gray-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200' }}">
                        Semua
                    </button>
                    @foreach(['Hasil Tani & Bumi', 'Pangan & Olahan', 'Material & Bangunan', 'Kerajinan & Kesenian', 'Lainnya'] as $cat)
                        <button type="button" onclick="setCategory('{{ $cat }}')" class="px-5 py-2 rounded-full font-semibold text-[13px] transition-all duration-300 border shadow-sm {{ request('kategori') == $cat ? 'bg-blue-500 text-white border-blue-500' : 'bg-white text-gray-600 border-gray-200 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200' }}">
                            {{ $cat }}
                        </button>
                    @endforeach
                    <input type="hidden" name="kategori" id="kategoriInput" value="{{ request('kategori', 'all') }}">
                </div>

                <!-- Location Selectors -->
                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    <div class="relative w-full sm:w-48">
                        <select name="kecamatan_id" id="kecamatanSelect" class="w-full bg-white border border-gray-200 rounded-full pl-4 pr-8 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors text-gray-700 cursor-pointer appearance-none" onchange="document.getElementById('filterForm').submit()">
                            <option value="all">Semua Kecamatan</option>
                            @foreach($kecamatans as $kec)
                                <option value="{{ $kec->id }}" {{ request('kecamatan_id') == $kec->id ? 'selected' : '' }}>{{ $kec->name }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-3 text-xs text-gray-400 pointer-events-none"></i>
                    </div>
                    
                    <div class="relative w-full sm:w-48">
                        <select name="desa_id" id="desaSelect" class="w-full bg-white border border-gray-200 rounded-full pl-4 pr-8 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 shadow-sm transition-colors text-gray-700 cursor-pointer appearance-none disabled:bg-gray-100 disabled:text-gray-400" {{ !request('kecamatan_id') || request('kecamatan_id') == 'all' ? 'disabled' : '' }} onchange="document.getElementById('filterForm').submit()">
                            <option value="all">Semua Desa</option>
                            @foreach($desas as $desa)
                                <option value="{{ $desa->id }}" data-parent="{{ $desa->parent_id }}" class="desa-option" style="{{ (request('kecamatan_id') != $desa->parent_id && request('desa_id') != $desa->id) ? 'display:none;' : '' }}" {{ request('desa_id') == $desa->id ? 'selected' : '' }}>{{ $desa->name }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-3 text-xs text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
            </form>

            <!-- Alerts -->
            @if(session('error'))
            <div class="max-w-4xl mx-auto bg-red-50 text-red-600 p-4 rounded-2xl mb-6 flex items-center shadow-sm border border-red-100 animate-section">
                <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                {{ session('error') }}
            </div>
            @endif
            @if(session('success'))
            <div class="max-w-4xl mx-auto bg-green-50 text-green-600 p-4 rounded-2xl mb-6 flex items-center shadow-sm border border-green-100 animate-section">
                <i class="fas fa-check-circle mr-3 text-xl"></i>
                {{ session('success') }}
            </div>
            @endif

            <!-- Info Bar -->
            <div class="flex flex-col sm:flex-row justify-between items-center mb-6 animate-section">
                <h2 class="text-gray-800 font-semibold mb-4 sm:mb-0 text-sm sm:text-base"><span class="text-blue-600 font-bold">{{ $produks->count() }}</span> Produk Ditemukan</h2>
                
                @auth
                <a href="{{ route('pasar.cart') }}" class="relative inline-flex items-center px-6 py-2 bg-white border border-blue-200 rounded-full text-sm font-bold text-blue-700 hover:bg-blue-50 transition-all duration-300 shadow-sm hover:shadow">
                    <i class="fas fa-shopping-cart mr-2 text-blue-600"></i> Keranjang
                    @php $cartCount = \App\Models\PasarCart::where('user_id', Auth::id())->sum('quantity'); @endphp
                    @if($cartCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white shadow">{{ $cartCount }}</span>
                    @endif
                </a>
                @else
                <a href="{{ route('auth.login') }}" class="inline-flex items-center px-6 py-2 bg-white border border-gray-200 rounded-full text-sm font-bold text-gray-600 hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow">
                    <i class="fas fa-shopping-cart mr-2"></i> Keranjang
                </a>
                @endauth
            </div>

            <!-- Product Grid -->
            <div class="w-full animate-section">
                @if($produks->isEmpty())
                <div class="bg-white/80 backdrop-blur-md rounded-3xl p-16 text-center border border-gray-100 shadow-sm max-w-4xl mx-auto">
                    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-5">
                        <i class="fas fa-box-open text-5xl text-gray-300"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Produk Tidak Ditemukan</h3>
                    <p class="text-gray-500 text-sm">Coba ubah kata kunci atau pilih kategori lain.</p>
                </div>
                @else
                
                <!-- TRUE E-COMMERCE GRID: Full width, No Image Padding -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 lg:gap-5">
                    @foreach($produks as $produk)
                    <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden flex flex-col h-full group border border-gray-100 hover:border-blue-200 hover:-translate-y-1">
                        
                        <!-- Image Container (Touches edges) -->
                        <a href="{{ route('pasar.show', $produk->id) }}" class="block relative aspect-square bg-gray-100 overflow-hidden">
                            @if($produk->foto)
                                <img src="{{ Storage::url($produk->foto) }}" alt="{{ $produk->nama_produk }}" class="object-cover w-full h-full group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <i class="fas fa-image text-3xl"></i>
                                </div>
                            @endif
                            
                            <!-- Kategori Badge Floating on Image -->
                            <div class="absolute bottom-2 left-2 pointer-events-none">
                                <span class="bg-white/95 backdrop-blur-sm shadow-sm text-gray-700 text-[9px] font-bold px-2 py-1 rounded line-clamp-1 border border-gray-100">
                                    {{ $produk->kategori }}
                                </span>
                            </div>
                        </a>

                        <!-- Content Below Image -->
                        <div class="p-3 sm:p-4 flex flex-col flex-grow relative">
                            <!-- Title -->
                            <a href="{{ route('pasar.show', $produk->id) }}" class="text-[13px] sm:text-[14px] font-medium text-gray-800 hover:text-blue-600 mb-1.5 line-clamp-2 leading-tight transition-colors">{{ $produk->nama_produk }}</a>
                            
                            <!-- Price -->
                            <div class="text-base sm:text-lg font-bold text-gray-900 mb-2">
                                Rp {{ number_format($produk->harga, 0, ',', '.') }}
                                <span class="text-[10px] text-gray-400 font-normal ml-0.5">/ {{ $produk->satuan ?? 'pcs' }}</span>
                            </div>
                            
                            <div class="mt-auto pt-2">
                                <!-- Location -->
                                <div class="flex items-center text-[10px] sm:text-[11px] text-gray-500 mb-3">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-1.5"></i> 
                                    <span class="line-clamp-1">{{ $produk->region->name ?? 'Bengkalis' }}</span>
                                </div>

                                <!-- Footer (Stok & Cart Button) -->
                                <div class="flex justify-between items-center border-t border-gray-50 pt-3">
                                    <div class="flex items-center">
                                        <span class="text-[10px] text-gray-500 mr-1">Stok:</span>
                                        <span class="text-[11px] font-bold {{ $produk->stok > 0 ? 'text-gray-700' : 'text-red-500' }}">{{ $produk->stok > 0 ? $produk->stok : 'Habis' }}</span>
                                    </div>
                                    
                                    @auth
                                        @if($produk->stok > 0)
                                            <button type="button" onclick="addToCart({{ $produk->id }})" class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-colors duration-300" title="Tambah ke Keranjang">
                                                <i class="fas fa-cart-plus text-xs"></i>
                                            </button>
                                        @else
                                            <button type="button" disabled class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center cursor-not-allowed">
                                                <i class="fas fa-cart-plus text-xs"></i>
                                            </button>
                                        @endif
                                    @else
                                        <a href="{{ route('auth.login') }}" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center hover:bg-gray-200 transition-colors duration-300" title="Login untuk Belanja">
                                            <i class="fas fa-cart-plus text-xs"></i>
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-10">
                    {{ $produks->links() }}
                </div>
                @endif
            </div>
        </div>
    </section>
</main>
@endsection

@push('styles')
<style>
    * { font-family: 'Inter', sans-serif; }
</style>
@endpush

@push('scripts')
<script>
    function setCategory(category) {
        document.getElementById('kategoriInput').value = category;
        document.getElementById('filterForm').submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const kecSelect = document.getElementById('kecamatanSelect');
        const desaSelect = document.getElementById('desaSelect');
        const desaOptions = document.querySelectorAll('.desa-option');

        kecSelect.addEventListener('change', function() {
            const kecId = this.value;
            if (kecId === 'all') {
                desaSelect.value = 'all';
                desaSelect.disabled = true;
                desaOptions.forEach(opt => opt.style.display = 'none');
            } else {
                desaSelect.disabled = false;
                desaSelect.value = 'all';
                let hasDesa = false;
                desaOptions.forEach(opt => {
                    if (opt.dataset.parent === kecId) {
                        opt.style.display = '';
                        hasDesa = true;
                    } else {
                        opt.style.display = 'none';
                    }
                });
            }
        });
    });

    @auth
    function addToCart(produkId) {
        fetch('{{ route('pasar.cart.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                pasar_produk_id: produkId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    toast: true, 
                    position: 'top-end', 
                    icon: 'success', 
                    title: 'Berhasil', 
                    text: 'Produk ditambahkan ke keranjang!', 
                    showConfirmButton: false, 
                    timer: 1500
                }).then(() => {
                    location.reload(); 
                });
            } else {
                Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Gagal', text: data.message || 'Gagal menambahkan produk', showConfirmButton: false, timer: 3000});
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({toast: true, position: 'top-end', icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem', showConfirmButton: false, timer: 3000});
        });
    }
    @endauth

    // Canvas Background Animation Script (Identical to Kabar Daerah)
    (function() {
        const initCanvas = function() {
            const canvas = document.getElementById('abstract-canvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            let width, height;
            let waves = [];
            let scrollY = window.scrollY;
            let mouse = { x: 0, y: 0 };
            let targetMouse = { x: 0, y: 0 };

            function resize() {
                width = window.innerWidth;
                height = window.innerHeight;
                canvas.width = width;
                canvas.height = height;
                initWaves();
            }

            window.addEventListener('resize', resize);
            
            document.addEventListener('mousemove', (e) => {
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
