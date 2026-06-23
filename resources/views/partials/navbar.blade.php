{{-- ================================================ --}}
{{-- NAVBAR UTAMA --}}
{{-- ================================================ --}}
@push('styles')
    <style>
        /* ============ SILA DESBENG NAVBAR ============ */
        .sd-navbar {
            font-family: 'Inter', sans-serif;
            position: fixed; top: 0; left: 0; right: 0; z-index: 997;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            padding: 0 !important; 
            transition: background-color 0.3s, backdrop-filter 0.3s, transform 0.3s ease-in-out;
            will-change: transform, backdrop-filter, background-color;
            transform: translateZ(0);
        }
        .sd-navbar.scrolled {
            background: rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        }
        .sd-navbar.hidden-nav {
            transform: translateY(-100%) translateZ(0);
        }
        .sd-navbar-toggle {
            position: absolute; bottom: -28px; right: 32px;
            width: 40px; height: 28px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: #38bdf8; z-index: 51;
            transition: all 0.3s;
        }
        .sd-navbar-toggle:hover { color: #0284c7; }
        .sd-navbar-toggle svg { width: 32px; height: 32px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1)); }
        
        .sd-nav-container {
            max-width: 1536px; margin: 0 auto; padding: 0 20px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .sd-nav-logo img { height: 80px; width: auto; object-fit: contain; padding: 8px 0; }
        @media (min-width: 640px) { .sd-nav-logo img { height: 96px; } }
        
        .sd-nav-links { display: flex; align-items: center; gap: 32px; margin-left: auto; margin-right: 32px; }
        .sd-nav-link {
            font-size: 15px; font-weight: 500; color: #111827;
            text-decoration: none; transition: color 0.2s;
            display: flex; align-items: center; justify-content: center;
            height: 100%; position: relative;
        }
        .sd-nav-link:hover { color: #2563eb; }
        .sd-nav-link.active { color: #2563eb; }
        .sd-nav-link.active::after {
            content: ''; position: absolute; bottom: -4px; left: 0;
            width: 100%; height: 2px; background-color: #3b82f6;
            border-radius: 2px;
        }
        
        .sd-nav-auth { display: flex; align-items: center; gap: 12px; }
        
        /* Auth Buttons */
        .sd-btn-login-wrapper { position: relative; display: inline-block; }
        .sd-btn-login-wrapper::before {
            content: ""; position: absolute; inset: -2px;
            background: linear-gradient(to right, #60a5fa, #f59e0b);
            border-radius: 9999px; opacity: 0.8; filter: blur(2px);
            transition: all 0.3s; z-index: -1;
        }
        .sd-btn-login-wrapper:hover::before { opacity: 1; filter: blur(3px); }
        .sd-btn-login {
            position: relative; display: inline-block; padding: 10px 40px;
            color: #2563eb; border-radius: 9999px; font-size: 15px; font-weight: 500;
            background: #ffffff; text-decoration: none; transition: all 0.3s; border: none; outline: none; cursor: pointer;
        }
        .sd-btn-login:hover { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .sd-btn-register {
            display: inline-block; padding: 12px 40px; color: #ffffff;
            border-radius: 9999px; font-size: 15px; font-weight: 500;
            text-decoration: none; transition: all 0.3s; border: none; outline: none; cursor: pointer;
            background: linear-gradient(to right, #7dc8f0 0%, #45aaf2 100%);
        }
        .sd-btn-register:hover { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        
        .sd-nav-mobile { display: none; }
        @media (max-width: 1023px) {
            .sd-nav-links, .sd-nav-auth { display: none !important; }
            .sd-nav-mobile { display: block !important; }
        }
    </style>
@endpush

<!-- ==================== SILA DESBENG NAVBAR ==================== -->
<nav class="sd-navbar" id="master-navbar">
    <div class="sd-nav-container">
        <!-- Logo -->
        <a href="{{ route('beranda') }}" class="sd-nav-logo">
            <img src="{{ asset('User/img/logo/iSewa.png') }}" alt="SiladesBeng Logo">
        </a>

        <!-- Menu Desktop -->
        <div class="sd-nav-links">
            <a href="{{ route('beranda') }}" class="sd-nav-link {{ request()->routeIs('beranda') ? 'active' : '' }}">Beranda</a>
            <div class="relative group flex items-center">
                <button class="sd-nav-link gap-1 p-0 bg-transparent border-none outline-none cursor-pointer {{ request()->routeIs('pelayanan') || request()->routeIs('bumdes.profil') || request()->routeIs('bumdes.laporan') ? 'active' : '' }}">
                    Layanan
                    <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                
                <!-- Wrapper padding transparan untuk jembatan hover agar tidak hilang -->
                <div class="absolute top-full left-1/2 -translate-x-1/2 pt-2 z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                    <div class="min-w-[200px] bg-white rounded-lg shadow-[0_4px_12px_rgba(0,0,0,0.1)] border border-gray-200 overflow-hidden">
                        <div class="py-1.5">
                            <a href="{{ route('bumdes.profil') }}" class="block px-4 py-2.5 text-gray-800 hover:bg-blue-50 hover:border-l-[3px] hover:border-l-blue-500 transition-all duration-150 whitespace-nowrap {{ request()->routeIs('bumdes.profil') ? 'bg-blue-50 border-l-[3px] border-l-blue-500 font-medium' : 'border-l-[3px] border-l-transparent' }}">
                                <span class="text-[15px] font-normal text-center block">Profil dan Layanan</span>
                            </a>
                            <div class="h-px bg-gray-100 mx-3 my-1"></div>
                            <a href="{{ route('pelayanan') }}" class="block px-4 py-2.5 text-gray-800 hover:bg-blue-50 hover:border-l-[3px] hover:border-l-blue-500 transition-all duration-150 whitespace-nowrap {{ request()->routeIs('pelayanan') ? 'bg-blue-50 border-l-[3px] border-l-blue-500 font-medium' : 'border-l-[3px] border-l-transparent' }}">
                                <span class="text-[15px] font-normal text-center block">Tentang Layanan</span>
                            </a>
                            <div class="h-px bg-gray-100 mx-3 my-1"></div>
                            <a href="{{ route('bumdes.laporan') }}" class="block px-4 py-2.5 text-gray-800 hover:bg-blue-50 hover:border-l-[3px] hover:border-l-blue-500 transition-all duration-150 whitespace-nowrap {{ request()->routeIs('bumdes.laporan') ? 'bg-blue-50 border-l-[3px] border-l-blue-500 font-medium' : 'border-l-[3px] border-l-transparent' }}">
                                <span class="text-[15px] font-normal text-center block">Grafik Layanan</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <a href="{{ route('announcements.index') }}" class="sd-nav-link {{ request()->routeIs('announcements.*') ? 'active' : '' }}">Kabar Daerah</a>
            <a href="{{ route('siladesbeng.profile') }}" class="sd-nav-link {{ request()->routeIs('siladesbeng.profile') ? 'active' : '' }}">Profil SiladesBeng</a>
            <a href="{{ route('kemitraan.create') }}" class="sd-nav-link {{ request()->routeIs('kemitraan.*') ? 'active' : '' }}">Gabung Kemitraan</a>
        </div>

        <!-- Auth Buttons / User Profile -->
        <div class="sd-nav-auth">
            @auth
                <div class="relative group">
                    <button class="flex items-center gap-2.5 hover:opacity-90 transition bg-transparent border-none outline-none cursor-pointer">
                        <span class="text-gray-900 font-bold text-[15px] group-hover:border-b-2 group-hover:border-blue-500 pb-0.5">{{ auth()->user()->name }}</span>
                        <div class="w-11 h-11 rounded-full overflow-hidden bg-[#D1D5DB] flex-shrink-0 shadow-md">
                            @if (auth()->user()->file)
                                <img src="{{ auth()->user()->file->file_stream }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                </div>
                            @endif
                        </div>
                    </button>

                    <!-- Wrapper padding transparan untuk jembatan hover agar tidak hilang -->
                    <div class="absolute top-full left-1/2 -translate-x-1/2 pt-2 z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                        <div class="w-48 bg-white rounded-lg shadow-[0_4px_12px_rgba(0,0,0,0.1)] border border-gray-200 overflow-hidden">
                            <div class="py-1.5">
                                <a href="{{ route('profile') }}" class="block px-4 py-2.5 text-gray-800 hover:bg-blue-50 hover:border-l-[3px] hover:border-l-blue-500 transition-all duration-150 border-l-[3px] border-l-transparent">
                                    <span class="text-[15px] font-normal text-center block">Profil</span>
                                </a>
                                <div class="h-px bg-gray-100 mx-3 my-1"></div>
                                <a href="{{ route('user.activity') }}" class="block px-4 py-2.5 text-gray-800 hover:bg-blue-50 hover:border-l-[3px] hover:border-l-blue-500 transition-all duration-150 border-l-[3px] border-l-transparent">
                                    <span class="text-[15px] font-normal text-center block">Aktivitas</span>
                                </a>
                                <a href="{{ route('user.notifications') }}" class="block px-4 py-2.5 text-gray-800 hover:bg-blue-50 hover:border-l-[3px] hover:border-l-blue-500 transition-all duration-150 border-l-[3px] border-l-transparent">
                                    <span class="text-[15px] font-normal text-center block">Notifikasi</span>
                                </a>
                                <div class="h-px bg-gray-100 mx-3 my-1"></div>
                                <button type="button" id="btn-open-logout" class="block w-full px-4 py-2.5 text-red-600 hover:bg-red-50 hover:border-l-[3px] hover:border-l-red-500 transition-all duration-150 bg-transparent border-none outline-none cursor-pointer border-l-[3px] border-l-transparent">
                                    <span class="text-[15px] font-normal text-center block">Keluar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="sd-btn-login-wrapper">
                    <button id="btn-open-login" type="button" class="sd-btn-login">Masuk</button>
                </div>
                <button id="btn-open-register" type="button" class="sd-btn-register">Daftar</button>
            @endauth
        </div>

        {{-- Hamburger Button - Mobile Only --}}
        <div class="sd-nav-mobile relative z-50">
            <button id="mobile-menu-btn" type="button" class="p-2 text-gray-700 hover:text-blue-600 focus:outline-none transition-all duration-200 active:bg-gray-100 rounded-lg active:scale-95 bg-transparent border-none cursor-pointer">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Toggle Button -->
    <div class="sd-navbar-toggle" id="master-navbar-toggle">
        <svg id="master-icon-up" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 8l-6 6h12l-6-6z"/>
        </svg>
        <svg id="master-icon-down" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
            <path d="M12 16l6-6H6l6 6z"/>
        </svg>
    </div>
</nav>

<script>
    const initMasterNavbar = function() {
        const masterToggle = document.getElementById('master-navbar-toggle');

        if(masterToggle && !masterToggle.dataset.initialized) {
            masterToggle.dataset.initialized = 'true';
            masterToggle.addEventListener('click', () => {
                const navbar = document.getElementById('master-navbar');
                const iconUp = document.getElementById('master-icon-up');
                const iconDown = document.getElementById('master-icon-down');
                
                if(!navbar) return;
                navbar.classList.toggle('hidden-nav');
                if(navbar.classList.contains('hidden-nav')) {
                    if(iconUp) iconUp.style.display = 'none';
                    if(iconDown) iconDown.style.display = 'block';
                } else {
                    if(iconUp) iconUp.style.display = 'block';
                    if(iconDown) iconDown.style.display = 'none';
                }
            });
        }

        if (!window.masterNavbarScrollInitialized) {
            window.masterNavbarScrollInitialized = true;
            let lastScrollY = window.scrollY;
            let ticking = false;

            window.addEventListener('scroll', () => {
                if (!ticking) {
                    window.requestAnimationFrame(() => {
                        const navbar = document.getElementById('master-navbar');
                        const iconUp = document.getElementById('master-icon-up');
                        const iconDown = document.getElementById('master-icon-down');
                        
                        if (!navbar) {
                            ticking = false;
                            return;
                        }
                        
                        const currentScrollY = window.scrollY;
                        
                        if (currentScrollY > lastScrollY && currentScrollY > 50) {
                            // Scroll down: Hide navbar
                            if (!navbar.classList.contains('hidden-nav')) {
                                navbar.classList.add('hidden-nav');
                                if(iconUp) iconUp.style.display = 'none';
                                if(iconDown) iconDown.style.display = 'block';
                            }
                        } else if (currentScrollY < lastScrollY) {
                            // Scroll up: Show navbar
                            if (navbar.classList.contains('hidden-nav')) {
                                navbar.classList.remove('hidden-nav');
                                if(iconUp) iconUp.style.display = 'block';
                                if(iconDown) iconDown.style.display = 'none';
                            }
                        }
                        lastScrollY = currentScrollY;
                        ticking = false;
                    });
                    ticking = true;
                }
            });
        }
    };
    
    document.addEventListener('DOMContentLoaded', initMasterNavbar);
    document.addEventListener('turbo:load', initMasterNavbar);
</script>

{{-- ================================================ --}}
{{-- OVERLAY - z-[998] agar di bawah sidebar z-[999] --}}
{{-- ================================================ --}}
<div id="mobile-overlay" class="fixed inset-0 bg-black/50 hidden opacity-0 z-[998] transition-opacity duration-300"></div>

{{-- ================================================ --}}
{{-- SIDEBAR MOBILE --}}
{{-- ================================================ --}}
<div id="mobile-sidebar" class="fixed inset-y-0 left-0 w-72 bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 z-[999] overflow-y-auto">
    
    {{-- Header Sidebar --}}
    <div class="py-5 px-5 flex items-center justify-between border-b bg-white">
        <img src="{{ asset('User/img/logo/iSewa.png') }}" class="h-10" alt="SiladesBeng">
        <button id="sidebar-close" type="button" class="p-2 hover:bg-gray-100 rounded-lg transition">
            <svg class="w-6 h-6 text-gray-600" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Menu Navigation --}}
    <nav class="py-4">
        <a href="{{ route('beranda') }}" class="block px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium transition {{ request()->routeIs('beranda') ? 'text-blue-600 bg-blue-50 border-l-4 border-blue-500' : '' }}">
            Beranda
        </a>
        <div class="px-6 py-3 text-gray-700 font-bold bg-gray-50 border-y text-sm">Layanan</div>
        <a href="{{ route('bumdes.profil') }}" class="block pl-10 pr-6 py-2.5 text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition {{ request()->routeIs('bumdes.profil') ? 'text-blue-600 font-medium border-l-4 border-blue-500' : '' }}">
            Profil dan Layanan
        </a>
        <a href="{{ route('pelayanan') }}" class="block pl-10 pr-6 py-2.5 text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition {{ request()->routeIs('pelayanan') ? 'text-blue-600 font-medium border-l-4 border-blue-500' : '' }}">
            Tentang Layanan
        </a>
        <a href="{{ route('bumdes.laporan') }}" class="block pl-10 pr-6 py-2.5 text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition {{ request()->routeIs('bumdes.laporan') ? 'text-blue-600 font-medium border-l-4 border-blue-500' : '' }}">
            Grafik Layanan
        </a>
        <a href="{{ route('siladesbeng.profile') }}" class="block px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium transition {{ request()->routeIs('siladesbeng.profile') ? 'text-blue-600 bg-blue-50 border-l-4 border-blue-500' : '' }}">
            Profil SiladesBeng
        </a>
        <a href="{{ route('kemitraan.create') }}" class="block px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 font-medium transition {{ request()->routeIs('kemitraan.*') ? 'text-blue-600 bg-blue-50 border-l-4 border-blue-500' : '' }}">
            Gabung Kemitraan
        </a>
    </nav>

    {{-- Auth Section --}}
    <div class="px-5 py-6 border-t mt-2">
        @auth
            <div class="flex items-center gap-3 mb-4 pb-4 border-b">
                <div class="w-12 h-12 rounded-full overflow-hidden bg-[#D1D5DB] flex-shrink-0">
                    @if (auth()->user()->file)
                        <img src="{{ auth()->user()->file->file_stream }}" alt="Avatar" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        </div>
                    @endif
                </div>
                <div>
                    <p class="font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                    <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <div class="space-y-2">
                <a href="{{ route('profile') }}" class="block w-full text-center px-4 py-2.5 rounded-lg font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                    Profil Saya
                </a>
                <a href="{{ route('user.activity') }}" class="block w-full text-center px-4 py-2.5 rounded-lg font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                    Aktivitas
                </a>
                <button type="button" id="btn-open-logout-mobile" class="block w-full text-center px-4 py-2.5 rounded-lg font-medium bg-red-50 text-red-600 hover:bg-red-100 transition border-none outline-none cursor-pointer">
                    Keluar
                </button>
            </div>
        @else
            {{-- Tombol Masuk/Daftar sama seperti desktop --}}
            <div class="space-y-3">
                {{-- Tombol Masuk dengan Gradient Border --}}
                <div class="relative group">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-400 to-amber-500 rounded-full opacity-70 group-hover:opacity-100 group-hover:blur-[2px] transition-all duration-300"></div>
                    <button id="btn-open-login-mobile" type="button" class="relative w-full px-6 py-2.5 text-blue-600 rounded-full text-[15px] font-medium bg-white hover:shadow-lg transition-all duration-300">
                        Masuk
                    </button>
                </div>

                {{-- Tombol Daftar dengan Gradient Background --}}
                <button id="btn-open-register-mobile" type="button"
                    class="w-full px-6 py-3 text-white rounded-full text-[15px] font-medium hover:shadow-lg transition-all duration-300"
                    style="background: linear-gradient(to right, #7dc8f0 0%, #78c7f0 3%, #73c6f0 6%, #6ec5f0 9%, #69c4f0 12%, #64c3f0 15%, #5fc2f0 18%, #5ac1f0 21%, #55c0f0 24%, #50bff0 27%, #4bbef0 30%, #4abdf1 33%, #49bcf1 36%, #48bbf1 39%, #47baf1 42%, #46b9f1 45%, #45b8f2 48%, #45b7f2 51%, #45b6f2 54%, #45b5f2 57%, #45b4f2 60%, #45b3f2 63%, #45b2f2 66%, #45b1f2 69%, #45b0f2 72%, #45aff2 75%, #45aef2 78%, #45adf2 81%, #45acf2 84%, #45abf2 87%, #45aaf2 90%, #45aaf2 93%, #45aaf2 96%, #45aaf2 100%);">
                    Daftar
                </button>
            </div>
        @endauth
    </div>
</div>