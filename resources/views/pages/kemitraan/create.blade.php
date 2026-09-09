@extends('layouts.user')

@section('title', 'Gabung Kemitraan - SiladesBeng')

@push('styles')
<style>
    * { font-family: 'Inter', sans-serif; }
    .btn-outline {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 16px 36px; font-size: 1.15rem; font-weight: 700;
        color: #2563eb; background: transparent;
        border: 2.5px solid #2563eb; border-radius: 9999px;
        cursor: pointer; transition: all 0.5s ease;
        position: relative; overflow: hidden;
        text-decoration: none;
    }
    .btn-outline::before {
        content: ''; position: absolute; inset: 0;
        background: #2563eb; transform: translateY(100%);
        transition: transform 0.5s ease;
    }
    .btn-outline:hover { color: #fff !important; }
    .btn-outline:hover::before { transform: translateY(0); }
    .btn-outline span { position: relative; z-index: 1; display: flex; align-items: center; gap: 8px; }

    @media (max-width: 640px) {
        .btn-outline {
            padding: 12px 24px !important;
            font-size: 0.95rem !important;
            border-width: 2px !important;
            max-width: 100% !important;
        }
    }
</style>
@endpush

@section('page')
<main class="flex-grow relative w-full">
    {{-- Custom Vector Abstract Background --}}
    @include('partials.abstract-bg')


    @php
        $isJoined = $isJoined ?? false;
    @endphp

    {{-- Hero Section --}}
    <section class="relative z-10 pt-28 sm:pt-48 pb-12 sm:pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 text-center animate-section">
            <h1 class="text-2xl sm:text-4xl md:text-5xl font-bold mb-3 sm:mb-4 animate-fade-in-up">
                <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Peta Kemitraan </span>
                <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">SiladesBeng</span>
            </h1>

            @if(!$isJoined)
                <p class="text-gray-700 text-sm sm:text-lg max-w-2xl mx-auto mb-4 sm:mb-6 animate-fade-in-up" style="animation-delay: 100ms;">
                    Wilayah desa Anda belum bergabung? Daftarkan sekarang!
                </p>
                
                <p class="text-gray-500 text-xs sm:text-sm max-w-2xl mx-auto mb-6 sm:mb-10 animate-fade-in-up bg-blue-50/50 p-2.5 sm:p-3 rounded-xl border border-blue-100" style="animation-delay: 150ms;">
                    <span class="font-bold text-blue-600">Info:</span> Anda yang menjabat sebagai <strong>Ketua RT</strong> atau <strong>Ketua RW</strong> juga dapat menggunakan form ini untuk mengeklaim hak akses Admin di wilayah Anda.
                </p>
            @else
                <p class="text-gray-700 text-lg max-w-2xl mx-auto mb-6 animate-fade-in-up" style="animation-delay: 100ms;">
                    Pemerintah Desa Anda sudah berpartisipasi dalam SiladesBeng!
                </p>
                
                <p class="text-gray-500 text-sm max-w-2xl mx-auto mb-10 animate-fade-in-up bg-green-50/50 p-3 rounded-xl border border-green-200" style="animation-delay: 150ms;">
                    <span class="font-bold text-green-700">Pemberitahuan Khusus Pejabat:</span> Jika Anda adalah <strong>Ketua RT</strong> atau <strong>Ketua RW</strong>, silakan daftar untuk mendapatkan hak akses pengelolaan warga di wilayah Anda.
                </p>
            @endif
            
            <div class="animate-fade-in-up" style="animation-delay: 200ms;">
                @guest
                    <button onclick="
                        const t = document.createElement('div');
                        t.style.position = 'fixed'; t.style.top = '70px'; t.style.right = '24px'; t.style.zIndex = '2147483647';
                        t.style.transform = 'translateX(50px) scale(0.95)'; t.style.opacity = '0'; t.style.transition = 'all 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
                        t.className = 'flex items-center p-4 rounded-xl shadow-2xl border-l-4 bg-white border-red-500 max-w-sm w-full';
                        t.innerHTML = `
                            <div class='flex-shrink-0'>
                                <svg class='w-6 h-6 text-red-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>
                            </div>
                            <div class='ml-3'>
                                <p class='text-sm font-semibold text-gray-900'>Peringatan</p>
                                <p class='text-sm text-gray-600 mt-1'>Anda harus login atau mendaftar terlebih dahulu!</p>
                            </div>
                            <button class='ml-auto -mx-1.5 -my-1.5 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex items-center justify-center h-8 w-8 text-gray-500 cursor-pointer'>
                                <svg class='w-5 h-5' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'></path></svg>
                            </button>`;
                        document.body.appendChild(t);
                        t.querySelector('button').addEventListener('click', () => { t.style.transform = 'translateX(50px) scale(0.95)'; t.style.opacity = '0'; setTimeout(() => t.remove(), 400); });
                        t.offsetHeight;
                        t.style.transform = 'translateX(0) scale(1)'; t.style.opacity = '1';
                        setTimeout(() => { t.style.transform = 'translateX(50px) scale(0.95)'; t.style.opacity = '0'; setTimeout(() => t.remove(), 400); }, 5000);

                        if(document.getElementById('btn-open-login')) {
                            document.getElementById('btn-open-login').click();
                        } else if(document.getElementById('btn-open-login-mobile')) {
                            document.getElementById('btn-open-login-mobile').click();
                        }" class="btn-outline shadow-sm hover:shadow-lg">
                        <span>{{ $isJoined ? 'Daftarkan Jabatan Pengurus RT/RW' : 'Daftarkan Kemitraan Desa' }}</span>
                    </button>
                @else
                    @if(auth()->user()->verification_status !== 'verified')
                        <button onclick="
                            const t = document.createElement('div');
                            t.style.position = 'fixed'; t.style.top = '24px'; t.style.right = '30px'; t.style.zIndex = '2147483647';
                            t.style.transform = 'translateX(150%)'; t.style.opacity = '0'; t.style.transition = 'all 0.5s ease';
                            t.className = 'px-5 py-3.5 rounded-xl shadow-2xl flex items-center gap-3 bg-amber-500 text-white font-medium';
                            t.innerHTML = `<svg class='w-6 h-6 flex-shrink-0' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'></path></svg><span>Anda harus verifikasi KTP Warga terlebih dahulu!</span>`;
                            document.body.appendChild(t);
                            t.offsetHeight;
                            t.style.transform = 'translateX(0)'; t.style.opacity = '1';
                            setTimeout(() => { t.style.transform = 'translateX(150%)'; t.style.opacity = '0'; setTimeout(() => t.remove(), 500); }, 3000);
                            setTimeout(() => { window.location.href = '{{ route('user.verifikasi.index') }}'; }, 1500);
                        " class="btn-outline shadow-sm hover:shadow-lg">
                            <span>{{ $isJoined ? 'Daftarkan Jabatan Pengurus RT/RW' : 'Daftarkan Kemitraan Desa' }}</span>
                        </button>
                    @else
                        <button onclick="openModal()" class="btn-outline shadow-sm hover:shadow-lg">
                            <span>{{ $isJoined ? 'Daftarkan Jabatan Pengurus RT/RW' : 'Daftarkan Kemitraan Desa' }}</span>
                        </button>
                    @endif
                @endguest
            </div>
        </div>
    </section>

    {{-- Session Alerts --}}
    <div class="max-w-7xl mx-auto px-6 relative z-10">
        @if(session('success'))
            <div class="mb-8 p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg shadow-sm">
                <div class="flex">
                    <svg class="w-6 h-6 text-red-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <ul class="text-red-700 list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>

    <script>
    (() => {
        const initKecamatan = function() {
            const selector = document.getElementById('kecamatan-selector');
            const badgeDesktop = document.getElementById('kecamatan-badge');
            const badgeMobile = document.getElementById('kecamatan-badge-mobile');
            const panels = document.querySelectorAll('.kecamatan-panel');

            function updateKecamatan(id) {
                panels.forEach(panel => {
                    if (panel.id === 'kecamatan-content-' + id) {
                        panel.classList.remove('hidden', 'opacity-0');
                        panel.classList.add('block', 'opacity-100');
                        
                        const joinedCount = panel.getAttribute('data-joined');
                        if(badgeDesktop) badgeDesktop.textContent = joinedCount;
                        if(badgeMobile) badgeMobile.textContent = joinedCount;
                    } else {
                        panel.classList.remove('block', 'opacity-100');
                        panel.classList.add('hidden', 'opacity-0');
                    }
                });
            }

            if (selector) {
                selector.addEventListener('change', function() {
                    updateKecamatan(this.value);
                });
                // Init first state
                updateKecamatan(selector.value);
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initKecamatan);
        } else {
            initKecamatan();
        }

        // Handle scroll for animation on cards
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };
    })();
    </script>

    {{-- Direktori Section --}}
    <section class="relative z-10 pt-4 sm:pt-10 pb-16 sm:pb-32 mt-2 sm:mt-4 mb-8 sm:mb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            @php
                $totalJoined = 0;
                foreach($kecamatans as $kecamatan) {
                    $totalJoined += $kecamatan->children->filter(function($desa) {
                        return $desa->users->filter(function($user) {
                            return in_array($user->role, ['admin_desa', 'admin']);
                        })->count() > 0;
                    })->count();
                }
            @endphp
            
            {{-- Statistik Total Kabupaten --}}
            <div class="mb-6 sm:mb-8 bg-white/60 backdrop-blur-md rounded-2xl border border-gray-100 p-4 sm:p-6 flex flex-row items-center justify-between animate-section shadow-sm gap-4">
                <div class="flex flex-col justify-center min-w-0 flex-1">
                    <h2 class="text-lg sm:text-2xl font-bold text-black mb-1 sm:mb-2 truncate" style="font-family: 'Poppins', sans-serif;">
                        Kabupaten Bengkalis
                    </h2>
                    <p class="text-gray-600 text-xs sm:text-sm font-semibold">
                        Total Desa / Kelurahan Bergabung
                    </p>
                </div>
                
                <div class="bg-white/60 backdrop-blur-sm px-4 sm:px-6 py-2 sm:py-3 rounded-xl border shadow-sm flex items-center justify-center shrink-0" style="border-color: #bfdbfe;">
                    <span class="text-2xl sm:text-3xl font-bold text-blue-600" style="font-family: 'Poppins', sans-serif;">
                        {{ $totalJoined }}
                    </span>
                </div>
            </div>

            <div class="space-y-6 sm:space-y-12">
                <div class="bg-white/60 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-section">
                    {{-- Header with Dropdown --}}
                    <div class="bg-gradient-to-r from-gray-50 to-white px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-stretch sm:items-center relative gap-2.5 sm:gap-4">
                        <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0 w-full">
                            <svg class="w-5 h-5 sm:w-7 sm:h-7 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <div class="relative flex-1 min-w-0 w-full">
                                <select id="kecamatan-selector" class="block w-full pl-3 sm:pl-6 pr-8 sm:pr-10 py-2 sm:py-3 text-xs sm:text-base md:text-lg font-bold text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#115789] focus:border-[#115789] cursor-pointer transition-colors truncate">
                                    @foreach($kecamatans as $kecamatan)
                                        <option value="{{ $kecamatan->id }}" class="text-gray-900 font-medium py-1">{{ $kecamatan->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end sm:justify-start shrink-0">
                            <div class="text-xs sm:text-sm font-medium px-3 sm:px-4 py-1 sm:py-1.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 shadow-sm whitespace-nowrap">
                                <span id="kecamatan-badge" class="font-bold text-sm sm:text-base">0</span> Bergabung
                            </div>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 relative min-h-[300px]">
                        @foreach($kecamatans as $index => $kecamatan)
                            @php
                                $joinedCount = $kecamatan->children->filter(function($desa) {
                                    return $desa->users->filter(function($user) {
                                        return in_array($user->role, ['admin_desa', 'admin']);
                                    })->count() > 0;
                                })->count();
                            @endphp
                            <div id="kecamatan-content-{{ $kecamatan->id }}" class="kecamatan-panel transition-opacity duration-300 {{ $index === 0 ? 'block opacity-100' : 'hidden opacity-0' }}" data-joined="{{ $joinedCount }}">
                        @if($joinedCount > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-8">
                                @foreach($kecamatan->children as $desa)
                                @if($desa->users->filter(function($user) { return in_array($user->role, ['admin_desa', 'admin']); })->count() > 0)
                                <div class="bg-white/40 backdrop-blur-sm border border-gray-100 shadow-sm rounded-xl p-5 hover:shadow-md transition-all animate-section">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="font-bold text-gray-800">{{ $desa->name }}</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Bergabung
                                        </span>
                                    </div>
                                    
                                    <div class="flex flex-col gap-3 mt-4">
                                        @if($desa->services->count() > 0)
                                            @foreach($desa->services as $service)
                                                @php
                                                    $nameLower = strtolower($service->name);
                                                    $iconColor = 'color: #6b7280;';
                                                    $bgColor = 'background-color: #f9fafb;';
                                                    
                                                    if (strpos($nameLower, 'alat') !== false) {
                                                        $iconColor = 'color: #f97316;';
                                                        $bgColor = 'background-color: #fff7ed;';
                                                    } elseif (strpos($nameLower, 'gas') !== false) {
                                                    $iconColor = 'color: #3b82f6;';
                                                    $bgColor = 'background-color: #eff6ff;';
                                                } elseif (strpos($nameLower, 'mobil') !== false || strpos($nameLower, 'kendaraan') !== false) {
                                                    $iconColor = 'color: #10b981;';
                                                    $bgColor = 'background-color: #ecfdf5;';
                                                } elseif (strpos($nameLower, 'fasilitas') !== false) {
                                                    $iconColor = 'color: #a855f7;';
                                                    $bgColor = 'background-color: #faf5ff;';
                                                } elseif (strpos($nameLower, 'lapor') !== false) {
                                                    $iconColor = 'color: #ef4444;';
                                                    $bgColor = 'background-color: #fef2f2;';
                                                } elseif (strpos($nameLower, 'pengumuman') !== false || strpos($nameLower, 'event') !== false) {
                                                    $iconColor = 'color: #06b6d4;';
                                                    $bgColor = 'background-color: #ecfeff;';
                                                }
                                            @endphp
                                            <div class="flex items-center gap-3">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-full shrink-0" style="{{ $bgColor }} {{ $iconColor }}">
                                                    @if(strpos($nameLower, 'alat') !== false)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    @elseif(strpos($nameLower, 'gas') !== false)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
                                                    @elseif(strpos($nameLower, 'mobil') !== false || strpos($nameLower, 'kendaraan') !== false)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                                    @elseif(strpos($nameLower, 'fasilitas') !== false)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                    @elseif(strpos($nameLower, 'lapor') !== false)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
                                                    @elseif(strpos($nameLower, 'pengumuman') !== false || strpos($nameLower, 'event') !== false || strpos($nameLower, 'berita') !== false || strpos($nameLower, 'kabar') !== false)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                                                    @elseif(strpos($nameLower, 'pasar') !== false || strpos($nameLower, 'toko') !== false || strpos($nameLower, 'jual') !== false)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    @endif
                                                </div>
                                                <span class="text-sm font-semibold text-gray-800">
                                                    {{ (strpos(strtolower($service->name), 'pengumuman') !== false || strpos(strtolower($service->name), 'event') !== false) ? 'Kabar dan Informasi Daerah' : $service->name }}
                                                </span>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="flex items-center gap-2 mt-1">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            <span class="text-xs text-gray-500 italic">Belum ada layanan aktif</span>
                                        </div>
                                    @endif
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12 bg-white/40 backdrop-blur-sm border border-gray-100 shadow-sm rounded-xl animate-section">
                                <div class="mx-auto w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <p class="text-gray-500 font-medium">Belum ada desa yang bergabung di kecamatan ini.</p>
                            </div>
                        @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Modal Form Pengajuan (Format Surat Resmi) --}}
    <div id="application-modal" class="fixed inset-0 hidden" style="z-index: 10000;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div id="modal-backdrop" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity duration-300 opacity-0" onclick="closeModal()"></div>

        <div class="fixed inset-0 overflow-y-auto" style="z-index: 10001;">
            {{-- Posisi diturunkan dengan items-start dan pt-24 (sebelumnya items-center) --}}
            <div class="flex min-h-full items-start justify-center pt-24 pb-12 px-4 sm:px-6 lg:px-8">
                {{-- Diperbesar menjadi max-w-6xl --}}
                <div id="modal-content" class="relative transform rounded-3xl shadow-2xl transition-all duration-300 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 w-full max-w-6xl">
                    <div class="relative overflow-hidden rounded-3xl bg-white text-left w-full h-full">
                    
                    {{-- Tombol Close --}}
                    <button type="button" onclick="closeModal()" class="absolute z-20 bg-gray-100 hover:bg-gray-200 rounded-full p-2 text-gray-500 hover:text-gray-700 focus:outline-none transition-all" style="top: 1.5rem; right: 1.5rem;">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>

                    {{-- Kop Surat: Logo Kiri - Judul Tengah - Logo Kanan --}}
                    <div style="padding: 3.5rem 3rem 1.5rem;">
                        <div class="flex items-center justify-between">
                            {{-- Logo Kabupaten (Kiri) --}}
                            <div class="flex-shrink-0 flex justify-center pl-4" style="width: 110px;">
                                <img src="{{ asset('Admin/img/illustrations/logokab.png') }}" alt="Logo Kabupaten Bengkalis" class="h-20 w-20 object-contain">
                            </div>

                            {{-- Judul Tengah --}}
                            <div class="text-center flex-1 px-4">
                                <h3 class="text-2xl font-bold text-gray-900 uppercase tracking-wide" id="modal-title">
                                    {{ $isJoined ? 'FORM PENGAJUAN KEMITRAAN WILAYAH' : 'FORM PENGAJUAN KEMITRAAN DESA' }}
                                </h3>
                                <p class="text-base text-gray-500 mt-2">
                                    {{ $isJoined ? 'Daftarkan wilayah Anda untuk bergabung' : 'Daftarkan desa Anda untuk bergabung' }}
                                </p>
                                <p class="text-sm text-gray-400 mt-1">Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis</p>
                            </div>

                            {{-- Logo SiladesBeng (Kanan) --}}
                            <div class="flex-shrink-0 flex justify-center pr-4" style="width: 110px;">
                                <img src="{{ asset('Admin/img/illustrations/logodomain.webp') }}" alt="Logo SiladesBeng" class="object-contain" style="width: 100px; height: 100px;">
                            </div>
                        </div>

                        {{-- Garis pemisah kop surat --}}
                        <div class="mt-6 border-b-2 border-gray-800"></div>
                        <div class="mt-1 border-b border-gray-400"></div>
                    </div>

                    @if(!empty($pendingApplication))
                        <div class="mx-12 mb-2 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-3">
                            <svg class="w-6 h-6 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <h5 class="text-sm font-bold text-amber-900">Pengajuan Anda Sedang Ditinjau</h5>
                                <p class="text-xs text-amber-700 mt-1">Anda sudah memiliki pengajuan kemitraan sebagai <strong>{{ $pendingApplication->position }} ({{ $pendingApplication->region_name }})</strong> yang saat ini masih dalam proses peninjauan oleh Pemerintah Desa.</p>
                            </div>
                        </div>
                    @endif

                    {{-- Body Form --}}
                    <div class="bg-white" style="padding: 1rem 3rem 3.5rem;">
                        <form action="{{ route('kemitraan.store') }}" method="POST" enctype="multipart/form-data" id="partner-form">
                            @csrf
                            
                            {{-- Baris 1: Data Pribadi & Jabatan (4 kolom) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
                                <div>
                                    <label for="applicant_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap (Sesuai KTP)</label>
                                    <input type="text" name="applicant_name" id="applicant_name" value="{{ auth()->check() ? auth()->user()->name : old('applicant_name') }}" class="py-2 px-3 block w-full border border-gray-200 rounded-lg bg-gray-200 text-gray-900 text-sm focus:outline-none" readonly>
                                </div>
                                <div>
                                    <label for="region_type" class="block text-sm font-semibold text-gray-700 mb-1">Tingkat Jabatan</label>
                                    <select id="region_type" name="region_type" class="py-2 px-3 block w-full border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-[#115789]/30 focus:border-[#115789]" required onchange="updateJabatanOptions()">
                                        <option value="" disabled selected>Pilih Tingkat</option>
                                        @if(!$isJoined)
                                            <option value="desa">Pemerintah Desa / Kelurahan</option>
                                        @else
                                            <option value="rw">Pengurus RW</option>
                                            <option value="rt">Pengurus RT</option>
                                        @endif
                                    </select>
                                </div>
                                <div>
                                    <label for="position" class="block text-sm font-semibold text-gray-700 mb-1">Jabatan Spesifik</label>
                                    <select id="position" name="position" class="py-2 px-3 block w-full border border-gray-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-[#115789]/30 focus:border-[#115789]" required>
                                        <option value="" disabled selected>Pilih Tingkat Jabatan Dulu</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="contact_phone" class="block text-sm font-semibold text-gray-700 mb-1">Nomor WhatsApp Aktif</label>
                                    <input type="text" name="contact_phone" id="contact_phone" value="{{ auth()->check() ? auth()->user()->phone : old('contact_phone') }}" class="py-2 px-3 block w-full border border-gray-200 rounded-lg bg-gray-50 text-gray-900 text-sm focus:ring-2 focus:ring-[#115789]/30 focus:border-[#115789]" required placeholder="0812...">
                                </div>
                            </div>
                            
                            {{-- Email Alert (Baris 1.5) --}}
                            <div class="mb-5 bg-blue-50/50 p-3 rounded-lg border border-blue-100 flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <p class="text-xs text-gray-700">Email pemberitahuan akan dikirimkan ke: <span class="font-bold text-blue-700">{{ auth()->check() ? auth()->user()->email : '' }}</span>. Pastikan akun ini adalah akun permanen Anda.</p>
                                    <input type="hidden" name="contact_email" value="{{ auth()->check() ? auth()->user()->email : old('contact_email') }}">
                                </div>
                            </div>

                            {{-- Divider Informasi Wilayah --}}
                            <div class="flex items-center gap-2 mb-4">
                                <svg class="w-6 h-6 shrink-0" style="color: #2f80ed;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <h4 class="font-bold text-gray-800 text-sm ml-1">
                                    {{ $isJoined ? 'Informasi Wilayah Domisili Anda' : 'Informasi Wilayah Anda' }}
                                </h4>
                                <div class="flex-1 border-b border-gray-200"></div>
                            </div>

                            @if($isJoined)
                                {{-- Wilayah Domisili Terkunci (3 Kolom) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-4">
                                    {{-- Kabupaten --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Kabupaten</label>
                                        <div class="py-2.5 px-4 w-full border border-gray-300 rounded-lg bg-gray-100 text-gray-700 text-sm font-bold shadow-sm flex items-center justify-between">
                                            <span>Bengkalis</span>
                                            <span class="text-[11px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded font-normal">Terkunci</span>
                                        </div>
                                    </div>
                                    
                                    {{-- Kecamatan --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Kecamatan</label>
                                        <div class="py-2.5 px-4 w-full border border-gray-300 rounded-lg bg-gray-100 text-gray-700 text-sm font-bold shadow-sm flex items-center justify-between">
                                            <span>{{ $userKecamatan->name ?? '-' }}</span>
                                            <span class="text-[11px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded font-normal">Domisili Akun</span>
                                        </div>
                                    </div>

                                    {{-- Desa --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Desa / Kelurahan</label>
                                        <div class="py-2.5 px-4 w-full border border-gray-300 rounded-lg bg-gray-100 text-gray-700 text-sm font-bold shadow-sm flex items-center justify-between">
                                            <span>{{ $userDesa->name ?? '-' }}</span>
                                            <span class="text-[11px] bg-green-100 text-green-700 px-2 py-0.5 rounded font-normal">Terdaftar</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Notice Penguncian Wilayah --}}
                                <div class="mb-5 px-3.5 py-2.5 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-600 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    <span>Wilayah Kabupaten, Kecamatan, dan Desa dikunci otomatis sesuai identitas akun terverifikasi Anda untuk menjaga keamanan serta mencegah pendaftaran lintas desa.</span>
                                </div>

                                {{-- Box Pilihan / Input Wilayah RW atau RT --}}
                                <div id="section_wilayah_rtrw" class="p-4 rounded-xl border border-blue-100 bg-blue-50/40 mb-6" style="display: none;">
                                    {{-- Form Wilayah Khusus Pengurus RW --}}
                                    <div id="box_input_rw" style="display: none;">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Pilih / Masukkan Nomor RW</label>
                                                @if(!empty($existingRwsData))
                                                    <div class="mb-2">
                                                        <select id="sel_rw_choice" class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg bg-white text-gray-900 text-sm font-semibold shadow-sm focus:ring-2 focus:ring-[#115789]/30" onchange="onRwChoiceChange()">
                                                            <option value="" disabled selected>Pilih RW Terdaftar</option>
                                                            @foreach($existingRwsData as $erw)
                                                                <option value="{{ $erw['name'] }}" data-id="{{ $erw['id'] }}" {{ $erw['has_admin'] ? 'disabled class="bg-gray-100 text-gray-400 font-normal"' : '' }}
                                                                    {{ (!$erw['has_admin'] && auth()->user()->rw && (int)auth()->user()->rw == (int)filter_var($erw['name'], FILTER_SANITIZE_NUMBER_INT)) ? 'selected' : '' }}>
                                                                    {{ $erw['name'] }} {{ $erw['has_admin'] ? '(Sudah Ada Admin: ' . $erw['admin_name'] . ')' : '(Tersedia)' }}
                                                                </option>
                                                            @endforeach
                                                            <option value="__manual__">+ Tulis Nomor RW Lainnya</option>
                                                        </select>
                                                    </div>
                                                @endif
                                                <div id="input_rw_manual_wrapper" class="{{ !empty($existingRwsData) ? 'hidden' : '' }}">
                                                    <div class="relative rounded-lg shadow-sm">
                                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 font-bold text-sm">RW</span>
                                                        <input type="number" id="input_rw_number" min="1" max="99" placeholder="Contoh: 01" value="" class="py-2.5 pl-12 pr-3 block w-full border border-gray-300 rounded-lg bg-white text-gray-900 text-sm font-semibold focus:ring-2 focus:ring-[#115789]/30" oninput="onRwManualInput()">
                                                    </div>
                                                </div>

                                                {{-- Pesan Validasi Status RW --}}
                                                <div id="rw_status_message" class="mt-2 text-xs font-medium" style="display: none;"></div>
                                                <p class="text-xs text-gray-500 mt-1">Pilih nomor RW yang tersedia atau ketik nomor RW baru jika belum terdata.</p>
                                            </div>
                                            <div class="text-xs text-gray-600 bg-white p-3.5 rounded-lg border border-blue-100">
                                                <p class="font-bold text-blue-700 mb-1">Ketentuan Pengurus RW</p>
                                                <p class="leading-relaxed">Setiap wilayah RW hanya dapat dikelola oleh satu akun Admin RW. Jika nomor RW telah memiliki admin aktif, Anda tidak dapat mendaftar untuk nomor tersebut dan opsi akan otomatis dinonaktifkan.</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Form Wilayah Khusus Pengurus RT --}}
                                    <div id="box_input_rt" style="display: none;">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">1. Tentukan RW Induk</label>
                                                @if(!empty($existingRwsData))
                                                    <select id="sel_rt_parent_rw" class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg bg-white text-gray-900 text-sm font-semibold shadow-sm focus:ring-2 focus:ring-[#115789]/30" onchange="onRtParentRwChange()">
                                                        <option value="" disabled selected>Pilih RW Induk</option>
                                                        @foreach($existingRwsData as $erw)
                                                            <option value="{{ $erw['name'] }}" data-id="{{ $erw['id'] }}" {{ (auth()->user()->rw && (int)auth()->user()->rw == (int)filter_var($erw['name'], FILTER_SANITIZE_NUMBER_INT)) ? 'selected' : '' }}>
                                                                {{ $erw['name'] }}
                                                            </option>
                                                        @endforeach
                                                        <option value="__manual__">+ Tulis Nomor RW Induk Baru</option>
                                                    </select>
                                                @endif
                                                <div id="input_rt_parent_rw_manual_wrapper" class="{{ !empty($existingRwsData) ? 'hidden' : '' }} mt-2">
                                                    <div class="relative rounded-lg shadow-sm">
                                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 font-bold text-sm">RW</span>
                                                        <input type="number" id="input_rt_parent_rw_manual" min="1" max="99" placeholder="Contoh: 01" value="" class="py-2.5 pl-12 pr-3 block w-full border border-gray-300 rounded-lg bg-white text-gray-900 text-sm font-semibold focus:ring-2 focus:ring-[#115789]/30" oninput="onRtParentRwManualInput()">
                                                    </div>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">RW yang menaungi RT yang akan Anda ajukan.</p>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">2. Masukkan / Pilih Nomor RT</label>
                                                {{-- Dropdown pilihan RT terdaftar --}}
                                                <div id="rt_dropdown_wrapper" style="display: none;" class="mb-2">
                                                    <select id="sel_rt_choice" class="py-2.5 px-3 block w-full border border-gray-300 rounded-lg bg-white text-gray-900 text-sm font-semibold shadow-sm focus:ring-2 focus:ring-[#115789]/30" onchange="onRtChoiceChange()">
                                                        <option value="" disabled selected>Pilih RT di RW ini</option>
                                                    </select>
                                                </div>

                                                {{-- Input RT Manual --}}
                                                <div id="input_rt_number_wrapper">
                                                    <div class="relative rounded-lg shadow-sm">
                                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 font-bold text-sm">RT</span>
                                                        <input type="number" id="input_rt_number" min="1" max="99" placeholder="Contoh: 01" value="" class="py-2.5 pl-12 pr-3 block w-full border border-gray-300 rounded-lg bg-white text-gray-900 text-sm font-semibold focus:ring-2 focus:ring-[#115789]/30" oninput="onRtNumberInput()">
                                                    </div>
                                                </div>

                                                {{-- Pesan Validasi Status RT --}}
                                                <div id="rt_status_message" class="mt-2 text-xs font-medium" style="display: none;"></div>
                                                <p class="text-xs text-gray-500 mt-1">Nomor Rukun Tetangga (RT) yang Anda ajukan.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Baris 2: Pemilihan Wilayah Desa Baru (Jika Desa Belum Terdaftar) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6" id="region_selectors">
                                    {{-- Kabupaten (Fixed) --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Kabupaten</label>
                                        <div class="py-2.5 px-4 w-full border border-gray-300 rounded-lg bg-gray-100 text-gray-600 text-[15px] font-bold shadow-sm">
                                            Bengkalis
                                        </div>
                                    </div>
                                    
                                    {{-- Kecamatan --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Kecamatan</label>
                                        <select id="sel_kecamatan" class="py-2 px-3 block w-full border border-gray-300 rounded-lg bg-white text-[#1f2937] text-sm font-semibold shadow-sm focus:ring-2 focus:ring-[#115789]/30" required onchange="onKecamatanChange()">
                                            <option value="" disabled selected>Pilih Kecamatan</option>
                                            @foreach($kecamatans as $kecamatan)
                                                <option value="{{ $kecamatan->id }}">{{ $kecamatan->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Kelurahan/Desa --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Desa / Kelurahan</label>
                                        <select id="sel_desa" class="py-2 px-3 block w-full border border-gray-300 rounded-lg bg-gray-100 text-gray-500 text-sm font-semibold shadow-sm focus:ring-2 focus:ring-[#115789]/30" disabled onchange="onDesaChange()">
                                            <option value="" disabled selected>Pilih Desa</option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            {{-- Hidden Inputs untuk Submit ke Backend --}}
                            <input type="hidden" name="parent_region_id" id="form_parent_region_id" value="{{ $isJoined && $userDesa ? $userDesa->id : '' }}">
                            <input type="hidden" name="region_name" id="form_region_name">
                            <input type="hidden" name="parent_rw_name" id="form_parent_rw_name">

                            {{-- Baris 3: Upload & Pesan (2 kolom sejajar) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                                {{-- Upload Dokumen --}}
                                <div>
                                    <label for="document" class="block text-sm font-semibold text-gray-700 mb-1">
                                        {{ $isJoined ? 'Unggah Surat Pendukung / Bukti Mandat (Maks 5MB)' : 'Unggah SK / Surat Tugas Resmi (Maks 5MB)' }}
                                    </label>
                                    <label for="document" class="flex items-center gap-3 rounded-lg border-2 border-dashed border-gray-300 px-4 py-4 bg-gray-50 hover:border-blue-500 hover:bg-blue-50/30 transition-all cursor-pointer group">
                                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-100 transition-colors">
                                            <svg class="h-5 w-5" style="color: #2f80ed;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1 text-sm">
                                                <span class="font-semibold group-hover:text-blue-600 transition-colors" style="color: #2f80ed;">Pilih file</span>
                                                <input id="document" name="document" type="file" style="display: none;" required accept=".pdf,.jpg,.jpeg,.png">
                                                <span class="text-gray-500">atau seret dan lepas</span>
                                            </div>
                                            <p class="text-xs text-gray-400 mt-0.5" id="file-name-display">
                                                {{ $isJoined ? 'SK, Surat Tugas, Surat Pengantar Desa, atau Berita Acara Mandat (PDF, PNG, JPG maks 5MB)' : 'SK Jabatan atau Surat Tugas resmi Pemerintah Desa (PDF, PNG, JPG maks 5MB)' }}
                                            </p>
                                        </div>
                                    </label>
                                </div>

                                {{-- Pesan Tambahan --}}
                                <div>
                                    <label for="reason" class="block text-sm font-semibold text-gray-700 mb-1">
                                        {{ $isJoined ? 'Catatan / Alasan Pengajuan Mandat' : 'Pesan Tambahan' }}
                                    </label>
                                    <textarea id="reason" name="reason" rows="3" class="py-2 px-3 block w-full border border-gray-200 rounded-lg bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#115789]/30 focus:border-[#115789] transition-colors" style="outline: none;" required placeholder="{{ $isJoined ? 'Tuliskan catatan singkat pengajuan mandat atau alasan mengajukan kepengurusan RT/RW...' : 'Alasan mengapa wilayah desa Anda ingin bergabung...' }}">{{ old('reason') }}</textarea>
                                </div>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="flex items-center justify-between border-t border-gray-100 pt-5 mt-2">
                                <p class="text-xs text-gray-400 italic">* Semua kolom wajib diisi</p>
                                <div class="flex items-center gap-3">
                                    <button type="button" onclick="closeModal()" class="inline-flex justify-center rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-200 hover:bg-gray-50 transition-colors">Batal</button>
                                    <button type="submit" id="btn-submit-partner" class="inline-flex justify-center items-center gap-2 rounded-full px-8 py-2.5 text-sm font-bold text-white shadow-sm hover:shadow transition-all hover:bg-blue-600 {{ !empty($pendingApplication) ? 'opacity-50 cursor-not-allowed' : '' }}" style="background-color: #2f80ed;" {{ !empty($pendingApplication) ? 'disabled' : '' }}>
                                        Kirim Pengajuan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    </div> {{-- Penutup div inner bg-white --}}
                </div>
            </div>
        </div>
    </div>
</main>

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    @keyframes fade-in-up {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fade-in-up 0.8s ease-out forwards; opacity: 0; }

    .hero-title {
        font-family: 'Inter', sans-serif;
        font-size: clamp(2rem, 4vw, 3.5rem); 
        font-weight: 800;
        line-height: 1.2; 
        margin-bottom: 24px;
    }
    .hero-title-gold {
        background: linear-gradient(to right, #1e3a5f, #2563eb, #1e3a5f);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text;
        background-size: 200% 200%; animation: gradient-anim 3s ease infinite;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
    }
    @keyframes gradient-anim { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }

    /* Button Styling */
    .btn-gradient-wrapper {
        position: relative; display: inline-block;
    }
    .btn-gradient-wrapper::before {
        content: ""; position: absolute; inset: -3px;
        background: linear-gradient(to right, #60a5fa, #f59e0b);
        border-radius: 9999px; opacity: 0.8; filter: blur(3px);
        transition: all 0.3s ease; z-index: -1;
    }
    .btn-gradient-wrapper:hover::before { opacity: 1; filter: blur(4px); inset: -4px; }
    .btn-gradient {
        position: relative; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 9999px; font-weight: 700;
        color: #2563eb !important; background: #fff !important; text-decoration: none !important;
        transition: all 0.3s ease; border: none; outline: none; cursor: pointer;
    }
    .btn-gradient:hover { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
</style>
@endpush

<script>
    const isJoined = @json($isJoined);
    const allRegions = @json($regions);
    const existingRwsData = @json($existingRwsData ?? []);
    const userDesaId = {{ $userDesa ? $userDesa->id : 'null' }};
    const userDesaName = @json($userDesa ? $userDesa->name : '');
    
    let isRwBlocked = false;
    let isRtBlocked = false;

    function getChildren(parentId) {
        return allRegions.filter(r => r.parent_id == parentId);
    }

    const selKec = document.getElementById('sel_kecamatan');
    const selDesa = document.getElementById('sel_desa');
    const selRegionType = document.getElementById('region_type');
    const positionSel = document.getElementById('position');
    
    // Elements for isJoined mode
    const sectionWilayahRtRw = document.getElementById('section_wilayah_rtrw');
    const boxInputRw = document.getElementById('box_input_rw');
    const boxInputRt = document.getElementById('box_input_rt');
    const selRwChoice = document.getElementById('sel_rw_choice');
    const inputRwManualWrapper = document.getElementById('input_rw_manual_wrapper');
    const inputRwNumber = document.getElementById('input_rw_number');
    const selRtParentRw = document.getElementById('sel_rt_parent_rw');
    const inputRtParentRwManualWrapper = document.getElementById('input_rt_parent_rw_manual_wrapper');
    const inputRtParentRwManual = document.getElementById('input_rt_parent_rw_manual');
    const selRtChoice = document.getElementById('sel_rt_choice');
    const rtDropdownWrapper = document.getElementById('rt_dropdown_wrapper');
    const inputRtNumberWrapper = document.getElementById('input_rt_number_wrapper');
    const inputRtNumber = document.getElementById('input_rt_number');

    const hiddenParent = document.getElementById('form_parent_region_id');
    const hiddenName = document.getElementById('form_region_name');
    const hiddenParentRwName = document.getElementById('form_parent_rw_name');

    function updateJabatanOptions() {
        const type = selRegionType.value;
        positionSel.innerHTML = '<option value="" disabled selected>Pilih Jabatan</option>';
        
        let options = [];
        if (type === 'desa') {
            options = ['Kepala Desa', 'Sekretaris Desa', 'BPD', 'Perangkat Desa', 'Pengelola Layanan Desa', 'Lainnya'];
        } else if (type === 'rw') {
            options = ['Ketua RW', 'Sekretaris RW', 'Pengurus RW Lainnya'];
        } else if (type === 'rt') {
            options = ['Ketua RT', 'Sekretaris RT', 'Pengurus RT Lainnya'];
        }
        
        options.forEach(opt => {
            const el = document.createElement('option');
            el.value = opt; el.textContent = opt;
            positionSel.appendChild(el);
        });

        if (isJoined) {
            if (sectionWilayahRtRw) sectionWilayahRtRw.style.display = 'block';
            if (type === 'rw') {
                if (boxInputRw) boxInputRw.style.display = 'block';
                if (boxInputRt) boxInputRt.style.display = 'none';
                if (selRwChoice && selRwChoice.value) {
                    onRwChoiceChange();
                } else {
                    syncRwValues();
                }
            } else if (type === 'rt') {
                if (boxInputRw) boxInputRw.style.display = 'none';
                if (boxInputRt) boxInputRt.style.display = 'block';
                if (selRtParentRw && selRtParentRw.value) {
                    onRtParentRwChange();
                } else {
                    syncRtValues();
                }
            } else {
                if (sectionWilayahRtRw) sectionWilayahRtRw.style.display = 'none';
            }
        } else {
            // Mode Desa Belum Terdaftar
            if (selDesa) {
                selDesa.disabled = true;
                selDesa.innerHTML = '<option value="" disabled selected>Pilih Desa</option>';
            }
            if (selKec && selKec.value) onKecamatanChange();
        }
    }

    // --- LOGIKA PENGURUS RW ---
    function onRwChoiceChange() {
        if (!selRwChoice) return;
        const val = selRwChoice.value;
        const msgEl = document.getElementById('rw_status_message');
        
        if (val === '__manual__') {
            if (inputRwManualWrapper) inputRwManualWrapper.classList.remove('hidden');
            if (inputRwNumber) {
                inputRwNumber.focus();
                onRwManualInput();
            }
        } else {
            if (inputRwManualWrapper) inputRwManualWrapper.classList.add('hidden');
            const selectedRw = existingRwsData.find(r => r.name === val);
            if (selectedRw && selectedRw.has_admin) {
                isRwBlocked = true;
                showStatusMessage(msgEl, 'error', `${selectedRw.name} sudah memiliki Admin aktif (${selectedRw.admin_name}). Anda tidak dapat mendaftar untuk RW ini.`);
            } else if (selectedRw) {
                isRwBlocked = false;
                showStatusMessage(msgEl, 'success', `${selectedRw.name} tersedia untuk diajukan.`);
            } else {
                isRwBlocked = false;
                if (msgEl) msgEl.style.display = 'none';
            }
            syncRwValues();
        }
    }

    function onRwManualInput() {
        const msgEl = document.getElementById('rw_status_message');
        if (!inputRwNumber) return;
        const numVal = inputRwNumber.value.trim();
        if (!numVal) {
            isRwBlocked = false;
            if (msgEl) msgEl.style.display = 'none';
            syncRwValues();
            return;
        }

        const formatted = 'RW ' + numVal.padStart(2, '0');
        const numInt = parseInt(numVal, 10);
        const existing = existingRwsData.find(r => r.name === formatted || r.name === 'RW ' + numInt);

        if (existing && existing.has_admin) {
            isRwBlocked = true;
            showStatusMessage(msgEl, 'error', `${existing.name} sudah memiliki Admin aktif (${existing.admin_name}). Silakan tentukan nomor RW lain.`);
        } else if (existing) {
            isRwBlocked = false;
            showStatusMessage(msgEl, 'success', `${existing.name} terdaftar dan tersedia untuk diajukan.`);
        } else {
            isRwBlocked = false;
            showStatusMessage(msgEl, 'success', `${formatted} (wilayah baru) tersedia untuk didaftarkan.`);
        }
        syncRwValues();
    }

    function syncRwValues() {
        if (!isJoined) return;
        hiddenParent.value = userDesaId;
        hiddenParentRwName.value = '';

        let rwName = '';
        if (selRwChoice && selRwChoice.value && selRwChoice.value !== '__manual__') {
            rwName = selRwChoice.value;
        } else if (inputRwNumber && inputRwNumber.value) {
            let num = inputRwNumber.value.trim().padStart(2, '0');
            rwName = 'RW ' + num;
        }
        hiddenName.value = rwName;
        checkSubmitButtonState();
    }

    // --- LOGIKA PENGURUS RT ---
    function onRtParentRwChange() {
        const parentRwVal = selRtParentRw ? selRtParentRw.value : '';
        const manualWrapper = document.getElementById('input_rt_parent_rw_manual_wrapper');
        const rtDropWrap = document.getElementById('rt_dropdown_wrapper');
        const rtChoiceEl = document.getElementById('sel_rt_choice');
        const inputRtEl = document.getElementById('input_rt_number');
        const inputRtWrap = document.getElementById('input_rt_number_wrapper');
        const rtStatusMsg = document.getElementById('rt_status_message');

        if (parentRwVal === '__manual__') {
            if (manualWrapper) manualWrapper.classList.remove('hidden');
            if (rtDropWrap) rtDropWrap.style.display = 'none';
            if (inputRtWrap) inputRtWrap.style.display = 'block';
            if (inputRtEl) inputRtEl.value = '';
            onRtParentRwManualInput();
        } else {
            if (manualWrapper) manualWrapper.classList.add('hidden');
            const selectedRw = existingRwsData.find(r => r.name === parentRwVal);
            
            if (selectedRw && selectedRw.rts && selectedRw.rts.length > 0) {
                // Tampilkan dropdown RT terdaftar
                rtChoiceEl.innerHTML = `<option value="" disabled selected>Pilih RT di ${selectedRw.name}</option>`;
                selectedRw.rts.forEach(rt => {
                    const opt = document.createElement('option');
                    opt.value = rt.name;
                    opt.setAttribute('data-id', rt.id);
                    if (rt.has_admin) {
                        opt.disabled = true;
                        opt.className = 'bg-gray-100 text-gray-400 font-normal';
                        opt.textContent = `${rt.name} (Sudah Ada Admin: ${rt.admin_name})`;
                    } else {
                        opt.textContent = `${rt.name} (Tersedia)`;
                    }
                    rtChoiceEl.appendChild(opt);
                });
                
                const optManual = document.createElement('option');
                optManual.value = '__manual__';
                optManual.textContent = '+ Tulis Nomor RT Baru';
                rtChoiceEl.appendChild(optManual);

                if (rtDropWrap) rtDropWrap.style.display = 'block';
                if (inputRtWrap) inputRtWrap.style.display = 'none';
            } else {
                // RW belum punya anak RT terdaftar, langsung input manual
                if (rtDropWrap) rtDropWrap.style.display = 'none';
                if (inputRtWrap) inputRtWrap.style.display = 'block';
            }
            if (rtStatusMsg) rtStatusMsg.style.display = 'none';
            isRtBlocked = false;
            syncRtValues();
        }
    }

    function onRtParentRwManualInput() {
        onRtNumberInput();
    }

    function onRtChoiceChange() {
        const rtChoiceEl = document.getElementById('sel_rt_choice');
        const inputRtWrap = document.getElementById('input_rt_number_wrapper');
        const inputRtEl = document.getElementById('input_rt_number');
        const msgEl = document.getElementById('rt_status_message');
        if (!rtChoiceEl) return;

        if (rtChoiceEl.value === '__manual__') {
            if (inputRtWrap) inputRtWrap.style.display = 'block';
            if (inputRtEl) {
                inputRtEl.value = '';
                inputRtEl.focus();
            }
            if (msgEl) msgEl.style.display = 'none';
            isRtBlocked = false;
        } else {
            if (inputRtWrap) inputRtWrap.style.display = 'none';
            const currentParentRw = selRtParentRw ? selRtParentRw.value : '';
            const rwObj = existingRwsData.find(r => r.name === currentParentRw);
            const rtObj = rwObj?.rts?.find(t => t.name === rtChoiceEl.value);

            if (rtObj && rtObj.has_admin) {
                isRtBlocked = true;
                showStatusMessage(msgEl, 'error', `${rtObj.name} di ${currentParentRw} sudah memiliki Admin aktif (${rtObj.admin_name}). Anda tidak dapat mendaftar untuk RT ini.`);
            } else if (rtObj) {
                isRtBlocked = false;
                showStatusMessage(msgEl, 'success', `${rtObj.name} di ${currentParentRw} tersedia untuk diajukan.`);
            }
        }
        syncRtValues();
    }

    function onRtNumberInput() {
        const inputRtEl = document.getElementById('input_rt_number');
        const msgEl = document.getElementById('rt_status_message');
        if (!inputRtEl) return;
        const numVal = inputRtEl.value.trim();
        if (!numVal) {
            isRtBlocked = false;
            if (msgEl) msgEl.style.display = 'none';
            syncRtValues();
            return;
        }

        const formattedRt = 'RT ' + numVal.padStart(2, '0');
        const numInt = parseInt(numVal, 10);
        const currentParentRw = (selRtParentRw && selRtParentRw.value !== '__manual__') 
            ? selRtParentRw.value 
            : ('RW ' + (inputRtParentRwManual?.value?.trim()?.padStart(2, '0') || ''));

        const rwObj = existingRwsData.find(r => r.name === currentParentRw);
        const existingRt = rwObj?.rts?.find(t => t.name === formattedRt || t.name === 'RT ' + numInt);

        if (existingRt && existingRt.has_admin) {
            isRtBlocked = true;
            showStatusMessage(msgEl, 'error', `${existingRt.name} di lingkungan ${currentParentRw} sudah memiliki Admin aktif (${existingRt.admin_name}). Silakan tentukan nomor RT lain.`);
        } else if (existingRt) {
            isRtBlocked = false;
            showStatusMessage(msgEl, 'success', `${existingRt.name} di lingkungan ${currentParentRw} terdaftar dan tersedia untuk diajukan.`);
        } else {
            isRtBlocked = false;
            showStatusMessage(msgEl, 'success', `${formattedRt} di lingkungan ${currentParentRw} (wilayah baru) tersedia untuk didaftarkan.`);
        }
        syncRtValues();
    }

    function syncRtValues() {
        if (!isJoined) return;

        // Tentukan RW Induk
        if (selRtParentRw && selRtParentRw.value && selRtParentRw.value !== '__manual__') {
            const selectedOpt = selRtParentRw.options[selRtParentRw.selectedIndex];
            hiddenParent.value = selectedOpt.getAttribute('data-id') || '';
            hiddenParentRwName.value = selectedOpt.value;
        } else if (inputRtParentRwManual && inputRtParentRwManual.value) {
            let num = inputRtParentRwManual.value.trim().padStart(2, '0');
            hiddenParent.value = userDesaId;
            hiddenParentRwName.value = 'RW ' + num;
        } else {
            hiddenParent.value = '';
            hiddenParentRwName.value = '';
        }

        // Tentukan RT Name
        const rtChoiceEl = document.getElementById('sel_rt_choice');
        const inputRtWrap = document.getElementById('input_rt_number_wrapper');
        const inputRtEl = document.getElementById('input_rt_number');

        let rtName = '';
        if (rtChoiceEl && rtChoiceEl.value && rtChoiceEl.value !== '__manual__' && inputRtWrap && inputRtWrap.style.display === 'none') {
            rtName = rtChoiceEl.value;
        } else if (inputRtEl && inputRtEl.value) {
            let num = inputRtEl.value.trim().padStart(2, '0');
            rtName = 'RT ' + num;
        }
        hiddenName.value = rtName;
        checkSubmitButtonState();
    }

    function showStatusMessage(element, type, message) {
        if (!element) return;
        element.style.display = 'flex';
        element.className = type === 'error' 
            ? 'mt-2 p-2.5 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2 font-semibold'
            : 'mt-2 p-2.5 rounded-lg bg-green-50 border border-green-200 text-xs text-green-700 flex items-center gap-2 font-semibold';
        
        const iconSvg = type === 'error'
            ? `<svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`
            : `<svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;

        element.innerHTML = `${iconSvg}<span>${message}</span>`;
    }

    function checkSubmitButtonState() {
        const btn = document.getElementById('btn-submit-partner');
        if (!btn) return;
        const type = selRegionType ? selRegionType.value : '';
        if (isJoined) {
            if ((type === 'rw' && isRwBlocked) || (type === 'rt' && isRtBlocked)) {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }
        }
        btn.disabled = false;
        btn.classList.remove('opacity-50', 'cursor-not-allowed');
    }

    // Handler untuk Mode Desa Belum Bergabung (isJoined == false)
    function onKecamatanChange() {
        if (!selKec || !selDesa) return;
        const kecId = selKec.value;
        selDesa.innerHTML = '<option value="" disabled selected>Pilih Desa/Kelurahan</option>';
        selDesa.disabled = !kecId;
        
        if (kecId) {
            const desas = getChildren(kecId).filter(r => r.type === 'desa');
            desas.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id; opt.textContent = d.name;
                selDesa.appendChild(opt);
            });
        }
        updateFormHiddenValuesDesa();
    }

    function onDesaChange() {
        updateFormHiddenValuesDesa();
    }

    function updateFormHiddenValuesDesa() {
        if (isJoined) return;
        if (selKec && selDesa) {
            hiddenParent.value = selKec.value;
            hiddenName.value = selDesa.options[selDesa.selectedIndex]?.text || '';
        }
    }

    // Validation before submit
    const partnerForm = document.getElementById('partner-form');
    if (partnerForm) {
        partnerForm.addEventListener('submit', function(e) {
            const type = selRegionType.value;
            if (isJoined) {
                if (type === 'rw') {
                    syncRwValues();
                    if (isRwBlocked) {
                        e.preventDefault();
                        alert('Nomor RW yang Anda pilih/ketik sudah memiliki Admin aktif. Silakan pilih nomor RW lain.');
                        return false;
                    }
                    if (!hiddenName.value) {
                        e.preventDefault();
                        alert('Silakan tentukan nomor RW yang valid terlebih dahulu.');
                        return false;
                    }
                } else if (type === 'rt') {
                    syncRtValues();
                    if (isRtBlocked) {
                        e.preventDefault();
                        alert('Nomor RT yang Anda pilih/ketik sudah memiliki Admin aktif. Silakan pilih nomor RT lain.');
                        return false;
                    }
                    if (!hiddenParentRwName.value && !hiddenParent.value) {
                        e.preventDefault();
                        alert('Silakan tentukan RW Induk terlebih dahulu.');
                        return false;
                    }
                    if (!hiddenName.value) {
                        e.preventDefault();
                        alert('Silakan tentukan nomor RT yang valid terlebih dahulu.');
                        return false;
                    }
                }
            } else {
                updateFormHiddenValuesDesa();
                if (!hiddenParent.value || !hiddenName.value) {
                    e.preventDefault();
                    alert('Silakan pilih Kecamatan dan Desa/Kelurahan terlebih dahulu.');
                    return false;
                }
            }
        });
    }

    // Modal logic
    function openModal() {
        const modal = document.getElementById('application-modal');
        const backdrop = document.getElementById('modal-backdrop');
        const content = document.getElementById('modal-content');
        
        modal.classList.remove('hidden');
        void modal.offsetWidth;
        
        // Animate in
        backdrop.classList.remove('opacity-0');
        backdrop.classList.add('opacity-100');
        
        content.classList.remove('opacity-0', 'translate-y-4', 'sm:scale-95');
        content.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');

        // Trigger update jika opsi sudah terpilih
        if (selRegionType && selRegionType.value) {
            updateJabatanOptions();
        }
    }
    
    function closeModal() {
        const modal = document.getElementById('application-modal');
        const backdrop = document.getElementById('modal-backdrop');
        const content = document.getElementById('modal-content');
        
        // Animate out
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        
        content.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        content.classList.add('opacity-0', 'translate-y-4', 'sm:scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // File name display logic
    const docInput = document.getElementById('document');
    if (docInput) {
        docInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                var fileName = e.target.files[0].name;
                const display = document.getElementById('file-name-display');
                if (display) {
                    display.textContent = 'File terpilih: ' + fileName;
                    display.classList.add('text-[#115789]', 'font-medium');
                }
            }
        });
    }
</script>

@if(session('success_modal'))
<!-- Success Modal -->
<div id="success-modal" class="fixed inset-0 z-[2147483647] flex items-center justify-center bg-gray-900 bg-opacity-50 transition-opacity" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="bg-white rounded-3xl overflow-hidden shadow-2xl transform transition-all sm:max-w-md w-full mx-4 relative animate-fade-in-up">
        <div class="px-6 pt-10 pb-8 text-center">
            <!-- Icon Success (Animated Checkmark) -->
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-50 mb-6 relative">
                <svg class="h-10 w-10 text-green-500 animate-[pulse_2s_ease-in-out_infinite]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" class="animate-[dash_1s_ease-out_forwards]" style="stroke-dasharray: 50; stroke-dashoffset: 50;"></path>
                </svg>
            </div>
            
            <h3 class="text-2xl leading-6 font-bold text-gray-900 mb-4" id="modal-title">
                Pengajuan Terkirim!
            </h3>
            <div class="mt-2">
                <p class="text-sm text-gray-500 leading-relaxed">
                    {{ session('success_modal') }}
                </p>
            </div>
        </div>
        <div class="px-6 py-5 bg-gray-50 flex justify-center border-t border-gray-100">
            <button type="button" onclick="document.getElementById('success-modal').style.display='none'" class="inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-10 py-3 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#115789] transition-colors sm:text-sm">
                Kembali
            </button>
        </div>
    </div>
</div>
<style>
    @keyframes dash {
        to { stroke-dashoffset: 0; }
    }
</style>
@endif

@endsection

