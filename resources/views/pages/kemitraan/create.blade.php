@extends('layouts.user')

@section('title', 'Gabung Kemitraan - SilaDesBeng')

@push('styles')
<style>
    .btn-outline {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 20px 40px; font-size: 1.25rem; font-weight: 700;
        color: #2563eb; background: transparent;
        border: 3px solid #2563eb; border-radius: 9999px;
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
</style>
@endpush

@section('page')
<main class="flex-grow relative w-full">
    {{-- Custom Vector Abstract Background --}}
    @include('partials.abstract-bg')


    @php
        $isJoined = false;
        if (auth()->check() && auth()->user()->region) {
            $userRegionIds = array_merge([auth()->user()->region_id], \App\Models\Region::getAncestorIds(auth()->user()->region_id));
            foreach($kecamatans as $kecamatan) {
                foreach($kecamatan->children as $desa) {
                    if(in_array($desa->id, $userRegionIds) && $desa->services->count() > 0) {
                        $isJoined = true;
                        break 2;
                    }
                }
            }
        }
    @endphp

    {{-- Hero Section --}}
    <section class="relative z-10" style="padding-top: 12rem; padding-bottom: {{ $isJoined ? '2rem' : '8rem' }};">
        <div class="max-w-7xl mx-auto px-6 text-center animate-section">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 animate-fade-in-up">
                <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Peta Kemitraan </span>
                <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">SiladesBeng</span>
            </h1>

            @if(!$isJoined)
            <p class="text-gray-700 text-lg max-w-2xl mx-auto mb-10 animate-fade-in-up" style="animation-delay: 100ms;">
                Desa / Kelurahan Anda belum bergabung? Daftarkan sekarang!
            </p>
            
            <div class="animate-fade-in-up" style="animation-delay: 200ms;">
                @guest
                    <button onclick="
                        const t = document.createElement('div');
                        t.style.position = 'fixed'; t.style.top = '24px'; t.style.right = '30px'; t.style.zIndex = '2147483647';
                        t.style.transform = 'translateX(150%)'; t.style.opacity = '0'; t.style.transition = 'all 0.5s ease';
                        t.className = 'px-5 py-3.5 rounded-xl shadow-2xl flex items-center gap-3 bg-red-500 text-white font-medium';
                        t.innerHTML = `<svg class='w-6 h-6 flex-shrink-0' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg><span>Anda harus login atau mendaftar terlebih dahulu!</span>`;
                        document.body.appendChild(t);
                        t.offsetHeight;
                        t.style.transform = 'translateX(0)'; t.style.opacity = '1';
                        setTimeout(() => { t.style.transform = 'translateX(150%)'; t.style.opacity = '0'; setTimeout(() => t.remove(), 500); }, 3000);

                        if(document.getElementById('btn-open-login')) { 
                            document.getElementById('btn-open-login').click(); 
                        } else if(document.getElementById('btn-open-login-mobile')) { 
                            document.getElementById('btn-open-login-mobile').click(); 
                        }" class="btn-outline shadow-sm hover:shadow-lg">
                        <span>Daftarkan Desa / Kelurahan Anda</span>
                    </button>
                @else
                    <button onclick="openModal()" class="btn-outline shadow-sm hover:shadow-lg">
                        <span>Daftarkan Desa / Kelurahan Anda</span>
                    </button>
                @endguest
            </div>
            @endif
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
    <section class="relative z-10 pt-10 pb-32" style="margin-top: 1rem; margin-bottom: 5rem;">
        <div class="max-w-7xl mx-auto px-6">
            @php
                $totalJoined = 0;
                foreach($kecamatans as $kecamatan) {
                    $totalJoined += $kecamatan->children->filter(function($desa) {
                        return $desa->users->filter(function($user) {
                            return in_array($user->role, ['admin_desa', 'lurah', 'admin']);
                        })->count() > 0;
                    })->count();
                }
            @endphp
            
            {{-- Statistik Total Kabupaten --}}
            <div class="mb-8 bg-white/60 backdrop-blur-md rounded-2xl border border-gray-100 p-6 flex flex-row items-center justify-between animate-section shadow-sm">
                <div class="flex flex-col justify-center">
                    <h2 class="text-2xl font-bold text-black mb-2" style="font-family: 'Poppins', sans-serif;">
                        Kabupaten Bengkalis
                    </h2>
                    <p class="text-black text-sm font-semibold">
                        Total Desa / Kelurahan Bergabung
                    </p>
                </div>
                
                <div class="bg-white/60 backdrop-blur-sm px-6 py-3 rounded-lg border shadow-sm flex items-center justify-center" style="border-color: #bfdbfe;">
                    <span class="text-3xl font-bold text-blue-600" style="font-family: 'Poppins', sans-serif;">
                        {{ $totalJoined }}
                    </span>
                </div>
            </div>

            <div class="space-y-12">
                <div class="bg-white/60 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 overflow-hidden animate-section">
                    {{-- Header with Dropdown --}}
                    <div class="bg-gradient-to-r from-gray-50 to-white px-6 py-4 border-b border-gray-100 flex flex-wrap justify-between items-center relative gap-4">
                        <div class="flex items-center flex-1 min-w-0 max-w-lg">
                            <svg class="w-7 h-7 text-blue-600 shrink-0" style="margin-right: 1.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <div class="relative w-full">
                                <select id="kecamatan-selector" class="block w-full pl-6 pr-10 py-3 text-base md:text-lg font-bold text-gray-800 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#115789] focus:border-[#115789] cursor-pointer transition-colors" style="appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%234B5563%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem top 50%; background-size: 0.75rem auto; padding-left: 1.5rem;">
                                    @foreach($kecamatans as $kecamatan)
                                        <option value="{{ $kecamatan->id }}" class="text-gray-900 font-medium py-1">{{ $kecamatan->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex items-center shrink-0">
                            <div class="text-sm font-medium px-4 py-1.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100 shadow-sm">
                                <span id="kecamatan-badge" class="font-bold text-base">0</span> Bergabung
                            </div>
                        </div>
                    </div>

                    <div class="p-6 relative min-h-[300px]">
                        @foreach($kecamatans as $index => $kecamatan)
                            @php
                                $joinedCount = $kecamatan->children->filter(function($desa) {
                                    return $desa->users->filter(function($user) {
                                        return in_array($user->role, ['admin_desa', 'lurah', 'admin']);
                                    })->count() > 0;
                                })->count();
                            @endphp
                            <div id="kecamatan-content-{{ $kecamatan->id }}" class="kecamatan-panel transition-opacity duration-300 {{ $index === 0 ? 'block opacity-100' : 'hidden opacity-0' }}" data-joined="{{ $joinedCount }}">
                        @if($joinedCount > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" style="gap: 2rem;">
                                @foreach($kecamatan->children as $desa)
                                @if($desa->users->filter(function($user) { return in_array($user->role, ['admin_desa', 'lurah', 'admin']); })->count() > 0)
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
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    @endif
                                                </div>
                                                <span class="text-sm font-semibold text-gray-800">{{ $service->name }}</span>
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
                            {{-- Logo SiladesBeng (Kiri) --}}
                            <div class="flex-shrink-0 flex justify-center" style="width: 110px;">
                                <img src="{{ asset('Admin/img/illustrations/logodomain.png') }}" alt="Logo SiladesBeng" class="object-contain" style="width: 100px; height: 100px;">
                            </div>

                            {{-- Judul Tengah --}}
                            <div class="text-center flex-1 px-4">
                                <h3 class="text-2xl font-bold text-gray-900 uppercase tracking-wide" id="modal-title">Form Pengajuan Kemitraan</h3>
                                <p class="text-base text-gray-500 mt-2">Daftarkan desa/kelurahan Anda untuk bergabung</p>
                                <p class="text-sm text-gray-400 mt-1">Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis</p>
                            </div>

                            {{-- Logo Kabupaten (Kanan) --}}
                            <div class="flex-shrink-0 w-24 flex justify-center pr-4">
                                <img src="{{ asset('Admin/img/illustrations/logokab.png') }}" alt="Logo Kabupaten Bengkalis" class="h-20 w-20 object-contain">
                            </div>
                        </div>

                        {{-- Garis pemisah kop surat --}}
                        <div class="mt-6 border-b-2 border-gray-800"></div>
                        <div class="mt-1 border-b border-gray-400"></div>
                    </div>

                    {{-- Body Form --}}
                    <div class="bg-white" style="padding: 1rem 3rem 3.5rem;">
                        <form action="{{ route('kemitraan.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            {{-- Baris 1: Data Pribadi (4 kolom) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
                                <div>
                                    <label for="applicant_name" class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap Pendaftar</label>
                                    <input type="text" name="applicant_name" id="applicant_name" value="{{ old('applicant_name') }}" class="py-2 px-3 block w-full border border-gray-200 rounded-lg bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#115789]/30 focus:border-[#115789] transition-colors" style="outline: none;" required placeholder="Budi Santoso">
                                </div>
                                <div>
                                    <label for="position" class="block text-sm font-semibold text-gray-700 mb-1">Jabatan</label>
                                    <input type="text" name="position" id="position" value="{{ old('position') }}" class="py-2 px-3 block w-full border border-gray-200 rounded-lg bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#115789]/30 focus:border-[#115789] transition-colors" style="outline: none;" required placeholder="Kepala Desa">
                                </div>
                                <div>
                                    <label for="contact_phone" class="block text-sm font-semibold text-gray-700 mb-1">Nomor WhatsApp</label>
                                    <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}" class="py-2 px-3 block w-full border border-gray-200 rounded-lg bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#115789]/30 focus:border-[#115789] transition-colors" style="outline: none;" required placeholder="08123456789">
                                </div>
                                <div>
                                    <label for="contact_email" class="block text-sm font-semibold text-gray-700 mb-1">Email Kontak</label>
                                    <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email') }}" class="py-2 px-3 block w-full border border-gray-200 rounded-lg bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#115789]/30 focus:border-[#115789] transition-colors" style="outline: none;" required placeholder="email@desa.id">
                                    <p class="text-xs mt-1.5 italic font-medium" style="color: #2f80ed;">* Email dan Sandi akun Anda akan dikirim melalui email ini. Pastikan email aktif.</p>
                                </div>
                            </div>

                            {{-- Divider Informasi Wilayah --}}
                            <div class="flex items-center gap-2 mb-5">
                                <svg class="w-6 h-6 shrink-0" style="color: #2f80ed;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <h4 class="font-bold text-gray-800 text-sm ml-1">Informasi Wilayah</h4>
                                <div class="flex-1 border-b border-gray-200"></div>
                            </div>

                            {{-- Baris 2: Informasi Wilayah (3 kolom) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
                                {{-- Hidden Input for region_type --}}
                                <input type="hidden" name="region_type" value="desa">

                                {{-- Kabupaten (Fixed) --}}
                                <div>
                                    <div class="py-2.5 px-4 w-full border border-gray-300 rounded-lg bg-white text-[#1f2937] text-[15px] font-bold shadow-sm flex items-center h-full">
                                        Kabupaten Bengkalis
                                    </div>
                                </div>
                                
                                {{-- Kecamatan Dropdown --}}
                                <div>
                                    <select id="parent_region_id" name="parent_region_id" class="py-2.5 px-4 block w-full border border-gray-300 rounded-lg bg-white text-[#1f2937] text-[15px] font-bold shadow-sm focus:ring-2 focus:ring-[#115789]/30 focus:border-[#115789] transition-colors cursor-pointer h-full" required onchange="updateDesaDropdown()">
                                        <option value="" disabled selected>Semua Kecamatan</option>
                                        @foreach($kecamatans as $kecamatan)
                                            <option value="{{ $kecamatan->id }}" {{ old('parent_region_id') == $kecamatan->id ? 'selected' : '' }}>{{ $kecamatan->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Kelurahan/Desa Dropdown --}}
                                <div>
                                    <select id="region_name" name="region_name" class="py-2.5 px-4 block w-full border border-gray-300 rounded-lg bg-white text-[#1f2937] text-[15px] font-bold shadow-sm focus:ring-2 focus:ring-[#115789]/30 focus:border-[#115789] transition-colors cursor-pointer h-full" required>
                                        <option value="" disabled selected>Semua Kelurahan/Desa</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Baris 3: Upload & Pesan (2 kolom sejajar) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
                                {{-- Upload Dokumen --}}
                                <div>
                                    <label for="document" class="block text-sm font-semibold text-gray-700 mb-1">Unggah SK/Surat Tugas (Max 5MB)</label>
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
                                            <p class="text-xs text-gray-400 mt-0.5" id="file-name-display">PDF, PNG, JPG maksimal 5MB</p>
                                        </div>
                                    </label>
                                </div>

                                {{-- Pesan Tambahan --}}
                                <div>
                                    <label for="reason" class="block text-sm font-semibold text-gray-700 mb-1">Pesan Tambahan</label>
                                    <textarea id="reason" name="reason" rows="3" class="py-2 px-3 block w-full border border-gray-200 rounded-lg bg-gray-50 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#115789]/30 focus:border-[#115789] transition-colors" style="outline: none;" required placeholder="Alasan mengapa desa Anda ingin bergabung...">{{ old('reason') }}</textarea>
                                </div>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="flex items-center justify-between border-t border-gray-100 pt-5 mt-2">
                                <p class="text-xs text-gray-400 italic">* Semua kolom wajib diisi</p>
                                <div class="flex items-center gap-3">
                                    <button type="button" onclick="closeModal()" class="inline-flex justify-center rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-200 hover:bg-gray-50 transition-colors">Batal</button>
                                    <button type="submit" class="inline-flex justify-center items-center gap-2 rounded-full px-8 py-2.5 text-sm font-bold text-white shadow-sm hover:shadow transition-all hover:bg-blue-600" style="background-color: #2f80ed;">
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
    // Data Desa per Kecamatan
    const desaData = {
        @foreach($kecamatans as $kecamatan)
            "{{ $kecamatan->id }}": [
                @foreach($kecamatan->children as $desa)
                    { id: "{{ $desa->id }}", name: "{{ $desa->name }}" },
                @endforeach
            ],
        @endforeach
    };

    function updateDesaDropdown() {
        const kecamatanId = document.getElementById('parent_region_id').value;
        const desaDropdown = document.getElementById('region_name');
        
        desaDropdown.innerHTML = '<option value="" disabled selected>Semua Kelurahan/Desa</option>';
        
        if (kecamatanId && desaData[kecamatanId]) {
            desaData[kecamatanId].forEach(desa => {
                const option = document.createElement('option');
                option.value = desa.name;
                option.textContent = desa.name;
                desaDropdown.appendChild(option);
            });
        }
    }

    // Trigger on load if old value exists
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('parent_region_id').value) {
            updateDesaDropdown();
            // Need to set timeout so it sets value after dropdown is populated
            setTimeout(() => {
                @if(old('region_name'))
                    document.getElementById('region_name').value = "{{ old('region_name') }}";
                @endif
            }, 100);
        }
    });

    // Modal logic
    function openModal() {
        const modal = document.getElementById('application-modal');
        const backdrop = document.getElementById('modal-backdrop');
        const content = document.getElementById('modal-content');
        
        modal.classList.remove('hidden');
        // document.body.style.overflow = 'hidden'; // Removed to keep scrollbar visible
        
        // Trigger reflow
        void modal.offsetWidth;
        
        // Animate in
        backdrop.classList.remove('opacity-0');
        backdrop.classList.add('opacity-100');
        
        content.classList.remove('opacity-0', 'translate-y-4', 'sm:scale-95');
        content.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
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
        
        // Wait for transition to finish
        setTimeout(() => {
            modal.classList.add('hidden');
            // document.body.style.overflow = 'auto';
        }, 300); // 300ms matches duration-300
    }

    // File name display logic
    document.getElementById('document').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        document.getElementById('file-name-display').textContent = 'File terpilih: ' + fileName;
        document.getElementById('file-name-display').classList.add('text-[#115789]', 'font-medium');
    });
</script>

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
