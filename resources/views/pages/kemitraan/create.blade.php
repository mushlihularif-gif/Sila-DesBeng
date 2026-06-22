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


    {{-- Hero Section --}}
    <section class="relative z-10" style="padding-top: 12rem; padding-bottom: 8rem;">
        <div class="max-w-7xl mx-auto px-6 text-center animate-section">
            <h1 class="hero-title animate-fade-in-up">
                <span class="hero-title-gold">Peta Kemitraan SiladesBeng</span>
            </h1>
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
                        return $desa->services->count() > 0;
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
                                    return $desa->services->count() > 0;
                                })->count();
                            @endphp
                            <div id="kecamatan-content-{{ $kecamatan->id }}" class="kecamatan-panel transition-opacity duration-300 {{ $index === 0 ? 'block opacity-100' : 'hidden opacity-0' }}" data-joined="{{ $joinedCount }}">
                        @if($joinedCount > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" style="gap: 2rem;">
                                @foreach($kecamatan->children as $desa)
                                @php
                                    // Desa is considered active if it has at least 1 active service
                                    $hasServices = $desa->services->count() > 0;
                                @endphp
                                @if($hasServices)
                                <div class="bg-white/40 backdrop-blur-sm border border-gray-100 shadow-sm rounded-xl p-5 hover:shadow-md transition-all animate-section">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="font-bold text-gray-800">{{ $desa->name }}</h3>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Bergabung
                                        </span>
                                    </div>
                                    
                                    <div class="flex flex-col gap-3 mt-4">
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

    {{-- Modal Form Pengajuan --}}
    <div id="application-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-5 pb-4 border-b">
                            <h3 class="text-2xl font-bold leading-6 text-gray-900" id="modal-title">Form Pengajuan Kemitraan</h3>
                            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        
                        <form action="{{ route('kemitraan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            
                            <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-6">
                                <div class="sm:col-span-2">
                                    <label for="applicant_name" class="block text-sm font-medium text-gray-700">Nama Lengkap Pendaftar</label>
                                    <input type="text" name="applicant_name" id="applicant_name" value="{{ old('applicant_name') }}" class="mt-1 py-2.5 px-3 block w-full shadow-sm focus:ring-[#115789] focus:border-[#115789] border-gray-300 rounded-md bg-gray-50" required placeholder="Budi Santoso">
                                </div>

                                <div>
                                    <label for="contact_phone" class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                                    <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone') }}" class="mt-1 py-2.5 px-3 block w-full shadow-sm focus:ring-[#115789] focus:border-[#115789] border-gray-300 rounded-md bg-gray-50" required placeholder="08123456789">
                                </div>

                                <div>
                                    <label for="contact_email" class="block text-sm font-medium text-gray-700">Email Kontak</label>
                                    <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email') }}" class="mt-1 py-2.5 px-3 block w-full shadow-sm focus:ring-[#115789] focus:border-[#115789] border-gray-300 rounded-md bg-gray-50" required placeholder="email@desa.id">
                                </div>

                                <div class="sm:col-span-2">
                                    <h4 class="font-semibold text-gray-800 mb-2 border-b pb-2">Informasi Wilayah</h4>
                                </div>

                                <div>
                                    <label for="region_type" class="block text-sm font-medium text-gray-700">Tingkat Wilayah</label>
                                    <select id="region_type" name="region_type" class="mt-1 py-2.5 px-3 block w-full shadow-sm focus:ring-[#115789] focus:border-[#115789] border-gray-300 rounded-md bg-gray-50" required>
                                        <option value="" disabled selected>Pilih Tingkat</option>
                                        <option value="desa" {{ old('region_type') == 'desa' ? 'selected' : '' }}>Desa / Kelurahan</option>
                                        <option value="kecamatan" {{ old('region_type') == 'kecamatan' ? 'selected' : '' }}>Kecamatan</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="region_name" class="block text-sm font-medium text-gray-700">Nama Wilayah</label>
                                    <input type="text" name="region_name" id="region_name" value="{{ old('region_name') }}" class="mt-1 py-2.5 px-3 block w-full shadow-sm focus:ring-[#115789] focus:border-[#115789] border-gray-300 rounded-md bg-gray-50" required placeholder="Pematang Duku Timur">
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="parent_region_id" class="block text-sm font-medium text-gray-700">Menginduk ke Wilayah Mana?</label>
                                    <select id="parent_region_id" name="parent_region_id" class="mt-1 py-2.5 px-3 block w-full shadow-sm focus:ring-[#115789] focus:border-[#115789] border-gray-300 rounded-md bg-gray-50" required>
                                        <option value="" disabled selected>Pilih Wilayah Induk</option>
                                        @foreach($regions as $region)
                                            <option value="{{ $region->id }}" {{ old('parent_region_id') == $region->id ? 'selected' : '' }}>
                                                [{{ strtoupper($region->type) }}] {{ $region->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="sm:col-span-2">
                                    <label for="document" class="block text-sm font-medium text-gray-700">Unggah SK/Surat Tugas (Max 5MB)</label>
                                    <div class="mt-1 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-6 bg-gray-50 hover:bg-gray-100 transition-colors">
                                        <div class="text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                                            </svg>
                                            <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                                                <label for="document" class="relative cursor-pointer rounded-md bg-white font-semibold text-[#115789] focus-within:outline-none focus-within:ring-2 focus-within:ring-[#115789] focus-within:ring-offset-2 hover:text-blue-500">
                                                    <span>Upload file</span>
                                                    <input id="document" name="document" type="file" class="sr-only" required accept=".pdf,.jpg,.jpeg,.png">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs leading-5 text-gray-500" id="file-name-display">PDF, PNG, JPG up to 5MB</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="reason" class="block text-sm font-medium text-gray-700">Pesan Tambahan</label>
                                    <textarea id="reason" name="reason" rows="3" class="mt-1 py-2.5 px-3 block w-full shadow-sm focus:ring-[#115789] focus:border-[#115789] border-gray-300 rounded-md bg-gray-50" required placeholder="Alasan mengapa desa Anda ingin bergabung...">{{ old('reason') }}</textarea>
                                </div>
                            </div>
                            
                            <div class="mt-6 sm:flex sm:flex-row-reverse border-t pt-4">
                                <button type="submit" class="inline-flex w-full justify-center rounded-md bg-[#115789] px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-800 sm:ml-3 sm:w-auto transition-colors">Kirim Pengajuan</button>
                                <button type="button" onclick="closeModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-6 py-3 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">Batal</button>
                            </div>
                        </form>
                    </div>
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
    // Modal logic
    function openModal() {
        document.getElementById('application-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
    
    function closeModal() {
        document.getElementById('application-modal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // File name display logic
    document.getElementById('document').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        document.getElementById('file-name-display').textContent = 'File terpilih: ' + fileName;
        document.getElementById('file-name-display').classList.add('text-[#115789]', 'font-medium');
    });

</script>



</style>
@endsection
