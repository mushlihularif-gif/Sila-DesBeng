@extends('layouts.user')

@section('title', 'Buat Laporan Warga')

@section('page')
    <div class="min-h-screen bg-[#f0f4f8] pt-32 pb-20 text-gray-800 relative">
        {{-- Custom Vector Abstract Background --}}
        <div class="fixed inset-0 overflow-hidden z-0 pointer-events-none" id="premium-bg">
            <canvas id="abstract-canvas" class="w-full h-full absolute inset-0"></canvas>
        </div>
        <div class="max-w-4xl mx-auto px-6 relative z-10 mb-20" data-aos="fade-up">
            <div class="bg-white/60 backdrop-blur-md border border-gray-100 rounded-2xl shadow-sm pt-6 pb-8 px-8 md:pt-8 md:pb-10 md:px-10">

                {{-- Kop Surat Resmi Pemerintahan --}}
                <div class="mb-6" style="padding: 0 0 1.5rem;">
                    <div class="flex items-center justify-between">
                        {{-- Logo Kabupaten (Kiri) --}}
                        <div class="flex-shrink-0 flex justify-center items-center md:pl-4 w-[80px] md:w-[120px]">
                            <img src="{{ asset('Admin/img/illustrations/logokab.png') }}" alt="Logo Kabupaten Bengkalis" class="h-20 w-20 md:h-[100px] md:w-[100px] object-contain drop-shadow-sm">
                        </div>

                        {{-- Judul Tengah --}}
                        <div class="text-center flex-1 px-2 md:px-4">
                            <h3 class="text-xl md:text-3xl font-bold text-gray-900 uppercase tracking-wide notranslate" translate="no">
                                Form Pelaporan
                            </h3>
                            <p class="text-xs md:text-sm text-gray-500 mt-2">Sampaikan keluhan atau saran Anda secara jujur dan beretika</p>
                            <p class="text-[10px] md:text-xs text-gray-400 mt-1">Sistem Sinergi Layanan dan Aspirasi Desa di Kabupaten Bengkalis</p>
                        </div>

                        {{-- Logo SiladesBeng (Kanan) --}}
                        <div class="flex-shrink-0 flex justify-center items-center md:pr-4 w-[96px] md:w-[140px]">
                            <img src="{{ asset('Admin/img/illustrations/logodomain.webp') }}" alt="Logo SiladesBeng" class="h-24 w-24 md:h-[115px] md:w-[115px] object-contain drop-shadow-sm">
                        </div>
                    </div>

                    {{-- Garis pemisah kop surat --}}
                    <div class="mt-4 md:mt-6 border-b-[3px] border-gray-800"></div>
                    <div class="mt-1 border-b border-gray-400"></div>
                </div>

                <!-- Alert Success -->
                @if (session('success'))
                    <div class="mb-8 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm">
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

                <!-- Alert Error -->
                @if ($errors->any())
                    <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-0.5">
                                <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Terdapat beberapa kesalahan:</h3>
                                <ul class="mt-1 list-disc list-inside text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('user.laporan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama (Dinamis: Terkunci jika KYC Approved, Bisa diedit jika belum) -->
                        @php
                            $isVerified = Auth::user()->kycVerification && Auth::user()->kycVerification->status === 'approved';
                        @endphp
                        <div>
                            <label class="block font-semibold text-gray-800 mb-2">Nama Lengkap 
                                @if($isVerified)
                                    <span class="text-gray-500 text-xs font-normal ml-1">(Sesuai KTP)</span>
                                @else
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>

                            @if($isVerified)
                                <input type="text" name="nama" required value="{{ Auth::user()->name }}" readonly
                                    class="w-full px-4 py-3 rounded-xl bg-gray-100 border border-gray-200 text-gray-600 font-medium cursor-not-allowed opacity-90 shadow-sm focus:outline-none">
                                <p class="text-xs text-green-600 mt-2.5 font-medium">
                                    * Terverifikasi KTP & Dikunci demi validitas.
                                </p>
                            @else
                                <input type="text" name="nama" required value="{{ old('nama', Auth::user()->name) }}"
                                    class="w-full px-4 py-3 rounded-xl bg-white border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800 transition-all shadow-sm hover:border-gray-400 @error('nama') border-red-500 @enderror"
                                    placeholder="Masukkan nama asli Anda">
                                <p class="text-xs mt-2.5 italic font-medium" style="color: #2f80ed;">
                                    * Anda belum verifikasi KTP. Silakan perbaiki nama jika tidak sesuai.
                                </p>
                                @error('nama')
                                    <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                                @enderror
                            @endif
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label class="block font-semibold text-gray-800 mb-2">Kategori <span class="text-red-500">*</span></label>
                            <select name="kategori" required
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800 transition-all shadow-sm @error('kategori') border-red-500 @enderror">
                                <option value="" disabled {{ old('kategori') ? '' : 'selected' }}>Pilih kategori laporan</option>
                                <option value="Kebersihan" {{ old('kategori') == 'Kebersihan' ? 'selected' : '' }}>Kebersihan</option>
                                <option value="Keamanan" {{ old('kategori') == 'Keamanan' ? 'selected' : '' }}>Keamanan</option>
                                <option value="Fasilitas" {{ old('kategori') == 'Fasilitas' ? 'selected' : '' }}>Fasilitas Umum</option>
                                <option value="Infrastruktur" {{ old('kategori') == 'Infrastruktur' ? 'selected' : '' }}>Infrastruktur</option>
                                <option value="Lingkungan" {{ old('kategori') == 'Lingkungan' ? 'selected' : '' }}>Lingkungan</option>
                                <option value="Pelayanan Publik" {{ old('kategori') == 'Pelayanan Publik' ? 'selected' : '' }}>Pelayanan Publik</option>
                                <option value="Administrasi" {{ old('kategori') == 'Administrasi' ? 'selected' : '' }}>Administrasi</option>
                                <option value="Lainnya" {{ old('kategori') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('kategori')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tujuan Laporan -->
                    <div>
                        <label class="block font-semibold text-gray-800 mb-3">Tujuan Pelaporan <span class="text-red-500">*</span></label>
                        
                        {{-- Hidden input untuk menyimpan region_id tujuan --}}
                        <input type="hidden" name="target_region_id" id="target_region_id" value="{{ old('target_region_id') }}">

                        <style>
                            /* Menangani state checked manual karena class peer-checked Tailwind ter-purge */
                            input[name="tujuan_laporan"]:checked ~ .custom-radio {
                                border-color: #3b82f6 !important; /* blue-500 */
                                background-color: #3b82f6 !important;
                            }
                            input[name="tujuan_laporan"]:checked ~ .custom-radio .custom-radio-inner {
                                opacity: 1 !important;
                            }
                            input[name="tujuan_laporan"]:checked ~ .card-outline {
                                border-color: #3b82f6 !important;
                            }
                        </style>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Opsi RT -->
                            <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all duration-300 {{ $hasAdminRT ? 'bg-white border-gray-200 hover:border-blue-400 hover:shadow-lg hover:shadow-blue-500/10 hover:-translate-y-1' : 'bg-gray-50 border-gray-200 opacity-60 cursor-not-allowed' }}">
                                <input type="radio" name="tujuan_laporan" value="rt" class="absolute opacity-0 w-0 h-0" {{ old('tujuan_laporan') == 'rt' ? 'checked' : '' }} {{ !$hasAdminRT ? 'disabled' : '' }} required>
                                
                                <div class="custom-radio absolute top-4 right-4 w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center transition-all">
                                    <div class="custom-radio-inner w-2 h-2 rounded-full bg-white opacity-0 transition-opacity"></div>
                                </div>
                                
                                <div class="flex items-center gap-3 mb-4 mt-1">
                                    <div class="w-10 h-10 rounded-xl {{ $hasAdminRT ? 'bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 shadow-sm border border-blue-100' : 'bg-gray-200 text-gray-400' }} flex items-center justify-center flex-shrink-0 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800 text-base leading-tight">Pengurus RT</h3>
                                        @if($hasAdminRT)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700 mt-1">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Tersedia
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700 mt-1">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                Belum Tersedia
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <p class="text-xs text-gray-500 mt-auto leading-relaxed border-t border-gray-100 pt-3">
                                    {{ $hasAdminRT ? 'Pilih RT tujuan secara spesifik dari daftar pencarian.' : 'Opsi ini tidak dapat dipilih karena belum ada satupun Pengurus RT yang terdaftar.' }}
                                </p>
                                
                                <div class="card-outline absolute inset-0 rounded-xl border-2 border-transparent pointer-events-none transition-colors"></div>
                            </label>

                            <!-- Opsi RW -->
                            <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all duration-300 {{ $hasAdminRW ? 'bg-white border-gray-200 hover:border-blue-400 hover:shadow-lg hover:shadow-blue-500/10 hover:-translate-y-1' : 'bg-gray-50 border-gray-200 opacity-60 cursor-not-allowed' }}">
                                <input type="radio" name="tujuan_laporan" value="rw" class="absolute opacity-0 w-0 h-0" {{ old('tujuan_laporan') == 'rw' ? 'checked' : '' }} {{ !$hasAdminRW ? 'disabled' : '' }}>
                                
                                <div class="custom-radio absolute top-4 right-4 w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center transition-all">
                                    <div class="custom-radio-inner w-2 h-2 rounded-full bg-white opacity-0 transition-opacity"></div>
                                </div>
                                
                                <div class="flex items-center gap-3 mb-4 mt-1">
                                    <div class="w-10 h-10 rounded-xl {{ $hasAdminRW ? 'bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 shadow-sm border border-blue-100' : 'bg-gray-200 text-gray-400' }} flex items-center justify-center flex-shrink-0 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800 text-base leading-tight">Pengurus RW</h3>
                                        @if($hasAdminRW)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700 mt-1">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Tersedia
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700 mt-1">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                Belum Tersedia
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <p class="text-xs text-gray-500 mt-auto leading-relaxed border-t border-gray-100 pt-3">
                                    {{ $hasAdminRW ? 'Pilih RW tujuan secara spesifik dari daftar pencarian.' : 'Opsi ini tidak dapat dipilih karena belum ada satupun Pengurus RW yang terdaftar.' }}
                                </p>
                                
                                <div class="card-outline absolute inset-0 rounded-xl border-2 border-transparent pointer-events-none transition-colors"></div>
                            </label>

                            <!-- Opsi Desa -->
                            <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all duration-300 bg-white border-gray-200 hover:border-blue-400 hover:shadow-lg hover:shadow-blue-500/10 hover:-translate-y-1">
                                <input type="radio" name="tujuan_laporan" value="desa" class="absolute opacity-0 w-0 h-0" {{ old('tujuan_laporan') == 'desa' ? 'checked' : '' }}>
                                
                                <div class="custom-radio absolute top-4 right-4 w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center transition-all">
                                    <div class="custom-radio-inner w-2 h-2 rounded-full bg-white opacity-0 transition-opacity"></div>
                                </div>
                                
                                <div class="flex items-center gap-3 mb-4 mt-1">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 text-blue-600 shadow-sm border border-blue-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-800 text-base leading-tight">Pemerintah Desa</h3>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700 mt-1">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Selalu Aktif
                                        </span>
                                    </div>
                                </div>
                                
                                <p class="text-xs text-gray-500 mt-auto leading-relaxed border-t border-gray-100 pt-3">
                                    Laporan langsung ditujukan ke Pemerintah Desa/Kelurahan untuk mendapatkan respon tingkat tinggi.
                                </p>
                                
                                <div class="card-outline absolute inset-0 rounded-xl border-2 border-transparent pointer-events-none transition-colors"></div>
                            </label>
                        </div>
                        @error('tujuan_laporan')
                            <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                        @enderror

                        {{-- Searchable Dropdown RT/RW (muncul dinamis sesuai pilihan radio) --}}
                        <div id="dropdown-tujuan-wrapper" class="mt-4 hidden">
                            <label id="dropdown-tujuan-label" class="block text-sm font-semibold text-gray-700 mb-1.5">Pilih RT Tujuan</label>
                            <div class="relative" id="searchable-select-container">
                                {{-- Trigger Button --}}
                                <button type="button" id="dropdown-trigger"
                                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800 transition-all shadow-sm text-left text-sm">
                                    <span id="dropdown-trigger-text" class="text-gray-500">Pilih tujuan...</span>
                                    <svg class="w-4 h-4 text-gray-400 transition-transform" id="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>

                                {{-- Dropdown Panel --}}
                                <div id="dropdown-panel" class="absolute z-50 w-full mt-1.5 bg-white border border-gray-200 rounded-xl shadow-lg hidden overflow-hidden">
                                    {{-- Search Input --}}
                                    <div class="border-b border-gray-100" style="padding: 12px;">
                                        <div class="relative">
                                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="pointer-events: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                            <input type="text" id="dropdown-search" placeholder="Cari nama atau nomor..."
                                                class="w-full pl-9 pr-4 py-2 text-sm rounded-lg bg-gray-50 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800 outline-none transition-all shadow-inner"
                                                style="box-sizing: border-box; padding-left: 2.5rem; width: 100%; max-width: 100%; margin: 0;">
                                        </div>
                                    </div>
                                    {{-- Options List --}}
                                    <ul id="dropdown-options" class="max-h-48 overflow-y-auto py-2">
                                        {{-- Diisi oleh JavaScript --}}
                                    </ul>
                                    {{-- Empty State --}}
                                    <div id="dropdown-empty" class="hidden px-4 py-6 text-center">
                                        <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p class="text-gray-400 text-xs">Tidak ditemukan. Pengurus RT/RW ini belum bergabung.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Lokasi + Peta --}}
                    <div>
                        <label class="block font-semibold text-gray-800 mb-1">Lokasi Kejadian <span class="text-red-500">*</span></label>
                        <p class="text-sm text-gray-500 mb-3"> Klik pada peta atau gunakan tombol GPS untuk menentukan lokasi kejadian secara tepat.</p>

                        {{-- Tombol GPS Otomatis --}}
                        <button type="button" id="btn-gps" onclick="getMyLocation()"
                            class="mb-3 inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-300 hover:border-blue-400 hover:bg-blue-50 text-gray-700 font-semibold rounded-xl shadow-sm transition-all duration-300 text-sm group">
                            <svg id="gps-icon" class="w-5 h-5 group-hover:scale-110 transition-transform" style="color: #2f80ed;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <svg id="gps-spinner" class="w-5 h-5 animate-spin hidden" style="color: #2f80ed;" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span id="gps-text">Gunakan Lokasi Saya (GPS)</span>
                        </button>

                        <div style="position:relative; z-index:0;">
                            <div id="map" style="height:420px; width:100%; border-radius:12px; border:2px solid #e5e7eb; z-index: 1;"></div>
                        </div>

                        <input type="hidden" name="latitude"    id="latitude">
                        <input type="hidden" name="longitude"   id="longitude">
                        <input type="hidden" name="lokasi" id="nama_lokasi">

                        {{-- Tampilan nama lokasi hasil klik --}}
                        <div class="mt-3 p-4 rounded-xl bg-gray-50 border border-gray-200 min-h-[52px] flex items-center gap-3 transition-colors" id="lokasi-display">
                            <div>
                                <p id="lokasi-nama" class="text-gray-400 text-sm italic">Belum ada lokasi dipilih. Klik peta di atas atau gunakan tombol GPS.</p>
                                <p id="lokasi-coords" class="text-gray-500 text-xs mt-0.5 hidden"></p>
                            </div>
                        </div>
                        @error('lokasi')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block font-semibold text-gray-800 mb-2">Deskripsi Laporan <span class="text-red-500">*</span></label>
                        <textarea name="deskripsi" rows="5" required
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800 transition-all shadow-sm @error('deskripsi') border-red-500 @enderror"
                            placeholder="Jelaskan keluhan Anda dengan detail...">{{ old('deskripsi') }}</textarea>
                        <div class="flex justify-between items-center mt-1">
                            <small class="text-gray-500">Minimal 20 karakter</small>
                            @error('deskripsi')
                                <p class="text-red-500 text-xs font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Bukti -->
                    <div>
                        <label class="block font-semibold text-gray-800 mb-2">Unggah Bukti Laporan <span class="text-gray-500 font-normal">(Maks. 3 Foto)</span></label>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <!-- Tombol Kamera -->
                            <label for="bukti-kamera" class="flex flex-col items-center justify-center p-4 rounded-xl border-2 border-dashed border-blue-300 bg-blue-50 hover:bg-blue-100 hover:border-blue-500 transition-all cursor-pointer group text-center">
                                <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center mb-3 shadow-sm text-blue-500 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <span class="font-bold text-blue-700 text-sm">Ambil Foto (Kamera)</span>
                                <span class="text-xs text-blue-500 mt-1">Otomatis deteksi lokasi (GPS)</span>
                                <input id="bukti-kamera" type="file" accept="image/jpeg,image/png,image/jpg" capture class="hidden" onchange="handleFileSelect(event, true)">
                            </label>

                            <!-- Tombol Galeri -->
                            <label for="bukti-galeri" class="flex flex-col items-center justify-center p-4 rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 hover:bg-gray-100 hover:border-gray-400 transition-all cursor-pointer group text-center">
                                <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center mb-3 shadow-sm text-gray-500 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                                <span class="font-bold text-gray-700 text-sm">Pilih dari Penyimpanan</span>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG</p>
                                <input id="bukti-galeri" type="file" accept="image/jpeg,image/jpg,image/png" multiple class="hidden" onchange="handleFileSelect(event, false)">
                            </label>
                        </div>

                        <!-- Real hidden input that gets submitted to backend -->
                        <input type="file" name="bukti[]" id="bukti-real" multiple class="hidden">
                        
                        @error('bukti')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                        @error('bukti.*')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror

                        <!-- Preview Container for Multiple Images -->
                        <div id="multi-preview-container" class="grid grid-cols-2 sm:grid-cols-3 gap-3 hidden mt-4">
                            <!-- Thumbnails injected by JS -->
                        </div>
                    </div>

                    <!-- Info User dengan Avatar -->
                    <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-6">
                        <div class="flex items-center gap-5">
                            {{-- Avatar User --}}
                            @if (Auth::user()->file)
                                <img src="{{ Auth::user()->file->file_stream }}" alt="{{ Auth::user()->name }}"
                                    class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-md flex-shrink-0">
                            @else
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full flex items-center justify-center border-2 border-white shadow-md flex-shrink-0">
                                    <span class="text-2xl font-bold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                </div>
                            @endif

                            {{-- Info User --}}
                            <div class="flex-1">
                                <p class="text-gray-600 text-sm leading-relaxed">
                                    @if($isVerified)
                                        Nama Pelapor: <strong class="text-gray-800">{{ Auth::user()->name }}</strong> 
                                        <span class="inline-flex items-center text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full ml-1 font-medium border border-green-200">
                                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            Sesuai KTP
                                        </span><br>
                                    @else
                                        Nama Akun Pelapor: <strong class="text-gray-800">{{ Auth::user()->name }}</strong> 
                                        <span class="inline-flex items-center text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full ml-1 font-medium border border-amber-200">
                                            <svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            Belum Verifikasi KTP
                                        </span><br>
                                        Nama Pelapor: <strong class="text-blue-600 italic">Sesuai nama yang Anda ketik di atas</strong><br>
                                    @endif
                                    Email: <strong class="text-gray-800">{{ Auth::user()->email }}</strong><br>
                                    Wilayah Anda: <strong class="text-gray-800" id="display-wilayah-user">RW {{ Auth::user()->rw ?? '-' }} / RT {{ Auth::user()->rt ?? '-' }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol -->
                    <div class="flex flex-col-reverse sm:flex-row gap-4 justify-end pt-4 border-t border-gray-100">
                        <a href="{{ route('user.laporan.index') }}"
                            class="inline-flex items-center justify-center px-8 py-3 bg-white text-gray-600 font-semibold border border-gray-200 rounded-full hover:bg-gray-50 hover:text-gray-900 shadow-sm transition-all duration-300 w-full sm:w-auto">
                            Batalkan
                        </a>
                        <button type="submit"
                            class="inline-flex items-center justify-center px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full shadow-sm hover:shadow-md transition-colors duration-300 w-full sm:w-auto">
                            Kirim Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== MODAL KONFIRMASI LOKASI ===== --}}
    <div id="location-modal"
        class="fixed inset-0 flex items-center justify-center hidden"
        style="z-index: 99999; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px);">
        <div class="bg-white border border-gray-100 rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-blue-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </span>
                <h3 class="text-gray-800 font-bold text-lg">Konfirmasi Lokasi</h3>
            </div>
            <p class="text-gray-500 text-sm mb-4">Apakah ini lokasi kejadian yang Anda maksud?</p>

            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-5">
                <p id="modal-address" class="text-gray-800 font-semibold text-sm leading-relaxed"></p>
                <p id="modal-coords"  class="text-gray-500 text-xs mt-1"></p>
            </div>

            <div class="flex gap-3">
                <button type="button" id="btn-confirm"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition text-sm shadow-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Ya, Benar
                </button>
                <button type="button" id="btn-cancel"
                    class="flex-1 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 hover:text-red-500 text-gray-700 font-semibold rounded-xl transition text-sm shadow-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Pilih Ulang
                </button>
            </div>
        </div>
    </div>

    {{-- ===== SCRIPT PETA (GOOGLE MAPS) ===== --}}
    <script>
    let map, marker, geocoder;
    let pendingLat = null, pendingLng = null, pendingAddress = null;

    function initMap() {
        const pakning = { lat: 1.0916, lng: 102.0724 };

        geocoder = new google.maps.Geocoder();

        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 15,
            center: pakning,
            mapTypeId: "roadmap",
            mapTypeControl: true,
            streetViewControl: false,
            fullscreenControl: true,
        });

        marker = new google.maps.Marker({
            position: pakning,
            map: map,
            draggable: true,
            animation: google.maps.Animation.DROP,
            title: "Geser atau klik peta untuk mengubah lokasi",
            icon: { url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png" }
        });

        map.addListener("click", function(event) {
            showLocationModal(event.latLng.lat(), event.latLng.lng());
        });

        marker.addListener("dragend", function(event) {
            showLocationModal(event.latLng.lat(), event.latLng.lng());
        });

        document.getElementById("btn-confirm").addEventListener("click", confirmLocation);
        document.getElementById("btn-cancel").addEventListener("click", cancelLocation);

        document.getElementById("location-modal").addEventListener("click", function(e) {
            if (e.target === this) cancelLocation();
        });
    }

    function showLocationModal(lat, lng) {
        pendingLat = lat;
        pendingLng = lng;

        geocoder.geocode({ location: { lat: lat, lng: lng } }, function(results, status) {
            let address = "Lokasi tidak dikenali";
            if (status === "OK" && results[0]) {
                address = results[0].formatted_address;
            }
            pendingAddress = address;

            document.getElementById("modal-address").innerText = address;
            document.getElementById("modal-coords").innerText  =
                "Lat: " + lat.toFixed(6) + "  â€¢  Lng: " + lng.toFixed(6);

            marker.setPosition({ lat: lat, lng: lng });
            marker.setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(() => marker.setAnimation(null), 700);

            document.getElementById("location-modal").classList.remove("hidden");
        });
    }

    function confirmLocation() {
        document.getElementById("latitude").value    = pendingLat;
        document.getElementById("longitude").value   = pendingLng;
        document.getElementById("nama_lokasi").value = pendingAddress;

        document.getElementById("lokasi-nama").innerText = pendingAddress;
        document.getElementById("lokasi-nama").classList.remove("italic", "text-gray-400");
        document.getElementById("lokasi-nama").classList.add("text-gray-800", "font-semibold");

        document.getElementById("lokasi-coords").innerText =
            "Lat: " + pendingLat.toFixed(6) + "  â€¢  Lng: " + pendingLng.toFixed(6);
        document.getElementById("lokasi-coords").classList.remove("hidden");

        document.getElementById("lokasi-display").classList.remove("border-gray-200", "bg-gray-50");
        document.getElementById("lokasi-display").classList.add("border-blue-400", "bg-blue-50");

        document.getElementById("location-modal").classList.add("hidden");
    }

    function cancelLocation() {
        pendingLat = null;
        pendingLng = null;
        pendingAddress = null;
        document.getElementById("location-modal").classList.add("hidden");

        const confirmedLat = document.getElementById("latitude").value;
        const confirmedLng = document.getElementById("longitude").value;
        if (confirmedLat && confirmedLng) {
            marker.setPosition({ lat: parseFloat(confirmedLat), lng: parseFloat(confirmedLng) });
        }
    }

    // âœ… Fix 2: Fungsi GPS Otomatis (Share Location)
    function getMyLocation() {
        const btn = document.getElementById('btn-gps');
        const icon = document.getElementById('gps-icon');
        const spinner = document.getElementById('gps-spinner');
        const text = document.getElementById('gps-text');

        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung fitur GPS/Geolocation.');
            return;
        }

        // Tampilkan loading state
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-wait');
        icon.classList.add('hidden');
        spinner.classList.remove('hidden');
        text.innerText = 'Mendeteksi lokasi Anda...';

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Pindahkan peta ke lokasi user
                map.setCenter({ lat: lat, lng: lng });
                map.setZoom(18);

                // Tampilkan modal konfirmasi seperti klik biasa
                showLocationModal(lat, lng);

                // Reset tombol
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-wait');
                icon.classList.remove('hidden');
                spinner.classList.add('hidden');
                text.innerText = 'Gunakan Lokasi Saya (GPS)';
            },
            function(error) {
                let msg = 'Gagal mendapatkan lokasi. ';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        msg += 'Anda menolak izin akses lokasi. Silakan aktifkan GPS di pengaturan browser Anda.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        msg += 'Informasi lokasi tidak tersedia.';
                        break;
                    case error.TIMEOUT:
                        msg += 'Permintaan lokasi melebihi batas waktu. Coba lagi.';
                        break;
                    default:
                        msg += 'Terjadi kesalahan yang tidak diketahui.';
                }
                alert(msg);

                // Reset tombol
                btn.disabled = false;
                btn.classList.remove('opacity-75', 'cursor-wait');
                icon.classList.remove('hidden');
                spinner.classList.add('hidden');
                text.innerText = 'Gunakan Lokasi Saya (GPS)';
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    }
    </script>

    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap&loading=async">
    </script>

    <script>
        const maxFiles = 3;
        let selectedFiles = []; // Array of File objects
        const dataTransfer = new DataTransfer(); // Used to sync with the hidden input
        
        const realInput = document.getElementById('bukti-real');
        const previewContainer = document.getElementById('multi-preview-container');

        function handleFileSelect(event, isCamera) {
            const files = Array.from(event.target.files);
            if (!files.length) return;

            // Trigger GPS if camera was used
            if (isCamera) {
                getMyLocation();
            }

            // Check if adding these files exceeds maxFiles
            if (selectedFiles.length + files.length > maxFiles) {
                alert(`Anda hanya dapat mengunggah maksimal ${maxFiles} foto.`);
                event.target.value = ''; // Reset input
                return;
            }

            // Validate and add files
            let validFilesAdded = false;
            files.forEach(file => {
                if (file.size > 2 * 1024 * 1024) {
                    alert(`File ${file.name} terlalu besar. Maksimal 2MB.`);
                } else {
                    selectedFiles.push(file);
                    dataTransfer.items.add(file);
                    validFilesAdded = true;
                }
            });

            // Update real hidden input
            realInput.files = dataTransfer.files;

            // Render previews
            if (validFilesAdded) {
                renderPreviews();
            }
            
            // Reset the original inputs so the same file can be selected again if needed
            event.target.value = '';
        }

        function renderPreviews() {
            previewContainer.innerHTML = '';
            
            if (selectedFiles.length === 0) {
                previewContainer.classList.add('hidden');
                return;
            }
            
            previewContainer.classList.remove('hidden');

            // 1. Render actual images using Object URL (Synchronous & Faster)
            selectedFiles.forEach((file, index) => {
                const objectUrl = URL.createObjectURL(file);
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `
                    <img src="${objectUrl}" class="rounded-xl border border-gray-200 w-full h-32 object-cover shadow-sm" onload="URL.revokeObjectURL(this.src)">
                    <button type="button" onclick="removeFile(${index})" class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-md hover:bg-red-600 z-10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                `;
                previewContainer.appendChild(div);
            });

            // 2. Render empty slots (Visual placeholders)
            const emptySlots = maxFiles - selectedFiles.length;
            for(let i = 0; i < emptySlots; i++) {
                const div = document.createElement('div');
                div.className = 'w-full h-32 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 flex flex-col items-center justify-center text-gray-400';
                div.innerHTML = `
                    <svg class="w-6 h-6 mb-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="text-xs font-medium opacity-70">Slot Tersedia</span>
                `;
                previewContainer.appendChild(div);
            }
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            
            // Rebuild DataTransfer
            dataTransfer.items.clear();
            selectedFiles.forEach(file => dataTransfer.items.add(file)); // Keep sync
            
            realInput.files = dataTransfer.files;
            renderPreviews();
        }
    </script>

    {{-- Modal RTRW legacy telah dihapus â€” domisili kini dikelola oleh KYC KTP --}}

    {{-- ===== SCRIPT SEARCHABLE DROPDOWN TUJUAN PELAPORAN ===== --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data RT/RW dari backend (semua RT/RW disertakan, dengan flag has_admin)
        const allRTData = {!! json_encode($allRTData->map(fn($rt) => ['id' => $rt->id, 'name' => $rt->name, 'rw_name' => $rt->rw_name, 'has_admin' => $rt->has_admin])->values()->all()) !!};
        const allRWData = {!! json_encode($allRWData->map(fn($rw) => ['id' => $rw->id, 'name' => $rw->name, 'has_admin' => $rw->has_admin])->values()->all()) !!};

        // DOM Elements
        const radios = document.querySelectorAll('input[name="tujuan_laporan"]');
        const wrapper = document.getElementById('dropdown-tujuan-wrapper');
        const label = document.getElementById('dropdown-tujuan-label');
        const trigger = document.getElementById('dropdown-trigger');
        const triggerText = document.getElementById('dropdown-trigger-text');
        const arrow = document.getElementById('dropdown-arrow');
        const panel = document.getElementById('dropdown-panel');
        const searchInput = document.getElementById('dropdown-search');
        const optionsList = document.getElementById('dropdown-options');
        const emptyState = document.getElementById('dropdown-empty');
        const hiddenInput = document.getElementById('target_region_id');

        let currentItems = [];
        let isOpen = false;

        // Render opsi dropdown
        function renderOptions(items, searchTerm = '') {
            optionsList.innerHTML = '';
            const filtered = items.filter(item => {
                const label = item.label || item.name;
                return label.toLowerCase().includes(searchTerm.toLowerCase());
            });

            if (filtered.length === 0) {
                emptyState.classList.remove('hidden');
                optionsList.classList.add('hidden');
                return;
            }

            emptyState.classList.add('hidden');
            optionsList.classList.remove('hidden');

            filtered.forEach(item => {
                const li = document.createElement('li');
                
                if (item.has_admin) {
                    li.className = 'px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 cursor-pointer transition-colors flex items-center justify-between border-b border-gray-50 last:border-0';
                } else {
                    li.className = 'px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed flex items-center justify-between bg-gray-50/50 border-b border-gray-100 last:border-0';
                }
                
                const labelContainer = document.createElement('div');
                labelContainer.className = 'flex items-center gap-2';

                const labelSpan = document.createElement('span');
                labelSpan.className = item.has_admin ? 'font-medium' : '';
                labelSpan.textContent = item.label || item.name;
                labelContainer.appendChild(labelSpan);

                if (item.badge) {
                    const badge = document.createElement('span');
                    badge.className = 'text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-gray-200 text-gray-600';
                    badge.textContent = item.badge;
                    labelContainer.appendChild(badge);
                }
                
                li.appendChild(labelContainer);

                if (item.has_admin) {
                    const statusBadge = document.createElement('span');
                    statusBadge.className = 'text-[10px] font-medium px-2 py-0.5 rounded bg-green-100 text-green-700 whitespace-nowrap';
                    statusBadge.textContent = 'âœ“ Aktif';
                    li.appendChild(statusBadge);

                    li.addEventListener('click', () => {
                        selectItem(item);
                    });
                } else {
                    const statusBadge = document.createElement('span');
                    statusBadge.className = 'text-[10px] font-medium px-2 py-0.5 rounded bg-red-100 text-red-600 whitespace-nowrap';
                    statusBadge.textContent = 'Admin Belum Bergabung';
                    li.appendChild(statusBadge);
                }

                optionsList.appendChild(li);
            });
        }

        // Pilih item
        function selectItem(item) {
            hiddenInput.value = item.id;
            triggerText.textContent = item.label || item.name;
            triggerText.classList.remove('text-gray-400');
            triggerText.classList.add('text-gray-800');
            closeDropdown();
        }

        // Buka dropdown
        function openDropdown() {
            panel.classList.remove('hidden');
            arrow.classList.add('rotate-180');
            isOpen = true;
            searchInput.value = '';
            renderOptions(currentItems);
            setTimeout(() => searchInput.focus(), 50);
        }

        // Tutup dropdown
        function closeDropdown() {
            panel.classList.add('hidden');
            arrow.classList.remove('rotate-180');
            isOpen = false;
        }

        // Toggle dropdown
        trigger.addEventListener('click', () => {
            isOpen ? closeDropdown() : openDropdown();
        });

        // Search filter
        searchInput.addEventListener('input', (e) => {
            renderOptions(currentItems, e.target.value);
        });

        // Tutup saat klik di luar
        document.addEventListener('click', (e) => {
            const container = document.getElementById('searchable-select-container');
            if (container && !container.contains(e.target)) {
                closeDropdown();
            }
        });

        // Handle radio button change
        radios.forEach(radio => {
            radio.addEventListener('change', function() {
                const val = this.value;
                hiddenInput.value = ''; // Reset pilihan
                triggerText.textContent = 'Ketik untuk mencari...';
                triggerText.classList.add('text-gray-400');
                triggerText.classList.remove('text-gray-800');

                if (val === 'rt' && allRTData.length > 0) {
                    label.textContent = 'Pilih RT Tujuan';
                    currentItems = allRTData.map(rt => ({
                        id: rt.id,
                        name: rt.name,
                        label: rt.name + ' â€” ' + rt.rw_name,
                        badge: rt.rw_name,
                        has_admin: rt.has_admin
                    }));
                    wrapper.classList.remove('hidden');
                } else if (val === 'rw' && allRWData.length > 0) {
                    label.textContent = 'Pilih RW Tujuan';
                    currentItems = allRWData.map(rw => ({
                        id: rw.id,
                        name: rw.name,
                        label: rw.name,
                        has_admin: rw.has_admin
                    }));
                    wrapper.classList.remove('hidden');
                } else {
                    wrapper.classList.add('hidden');
                    currentItems = [];
                }

                closeDropdown();
            });
        });

        // Restore state jika ada old value
        const oldTujuan = '{{ old('tujuan_laporan') }}';
        const oldRegionId = '{{ old('target_region_id') }}';
        if (oldTujuan && oldRegionId) {
            const checkedRadio = document.querySelector(`input[name="tujuan_laporan"][value="${oldTujuan}"]`);
            if (checkedRadio) {
                checkedRadio.dispatchEvent(new Event('change'));
                // Set selected item
                setTimeout(() => {
                    const found = currentItems.find(i => i.id == oldRegionId);
                    if (found) selectItem(found);
                }, 100);
            }
        }
    });
    </script>
@endsection

@push('scripts')
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Override native browser alert dengan SweetAlert2 untuk tampilan profesional
        window.alert = function(message) {
            let iconType = 'info';
            // Deteksi konteks error secara sederhana dari isi pesan
            if (message.toLowerCase().includes('gagal') || message.toLowerCase().includes('error') || message.toLowerCase().includes('tidak mendukung') || message.toLowerCase().includes('terlalu besar') || message.toLowerCase().includes('tidak didukung')) {
                iconType = 'error';
            } else if (message.toLowerCase().includes('berhasil')) {
                iconType = 'success';
            } else if (message.toLowerCase().includes('karena rw/rt anda belum diisi')) {
                iconType = 'warning';
            }

            // Konfigurasi SweetAlert2 sebagai Toast (Pojok Kanan Atas)
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-xl shadow-lg border border-gray-100 mt-16', // mt-16 agar tidak tertutup navbar
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: iconType,
                title: message
            });
        };
    </script>
<script>
    // Canvas Vector Abstract Background Script (Turbo-Compatible)
    if (!window.initAbstractCanvas) {
        window.initAbstractCanvas = function() {
            if (window.abstractCanvasAnimationId) {
                cancelAnimationFrame(window.abstractCanvasAnimationId);
            }

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

            window.removeEventListener('resize', window._abstractCanvasResize);
            window.removeEventListener('mousemove', window._abstractCanvasMouseMove);
            window.removeEventListener('mouseout', window._abstractCanvasMouseOut);
            window.removeEventListener('scroll', window._abstractCanvasScroll);

            window._abstractCanvasResize = resize;
            window._abstractCanvasMouseMove = (e) => {
                targetMouse.x = e.clientX;
                targetMouse.y = e.clientY;
            };
            window._abstractCanvasMouseOut = () => {
                targetMouse.x = -1000;
                targetMouse.y = -1000;
            };
            
            let scrollY = window.scrollY;
            window._abstractCanvasScroll = () => {
                scrollY = window.scrollY;
            };

            window.addEventListener('resize', window._abstractCanvasResize);
            window.addEventListener('mousemove', window._abstractCanvasMouseMove);
            window.addEventListener('mouseout', window._abstractCanvasMouseOut);
            window.addEventListener('scroll', window._abstractCanvasScroll);

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
                    let numPoints = Math.ceil(width / 25) + 2; 
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
                        
                        let targetY = pt.baseY + Math.sin(this.time + pt.x / this.wavelength) * this.amplitude;
                        
                        let dx = mouse.x - pt.x;
                        let dy = mouse.y - targetY;
                        let distance = Math.sqrt(dx*dx + dy*dy);
                        
                        if (distance < 200) {
                            let force = Math.pow((200 - distance) / 200, 2); 
                            let pushDir = (dy > 0) ? -1 : 1; 
                            targetY += pushDir * force * 60;
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
                    new Wave((ctx, w, h) => {
                        let grad = ctx.createLinearGradient(0, h*0.2, 0, h*1.2);
                        grad.addColorStop(0, 'rgba(140, 190, 250, 0.7)');
                        grad.addColorStop(1, 'rgba(180, 215, 255, 0.1)');
                        return grad;
                    }, 0.35, 40, 0.005, 600),

                    new Wave((ctx, w, h) => {
                        let grad = ctx.createLinearGradient(0, h*0.3, 0, h*1.2);
                        grad.addColorStop(0, 'rgba(255, 255, 255, 1)');
                        grad.addColorStop(1, 'rgba(245, 250, 255, 0.5)');
                        return grad;
                    }, 0.45, 30, 0.003, 500),

                    new Wave((ctx, w, h) => {
                        let grad = ctx.createLinearGradient(0, h*0.4, 0, h*1.1);
                        grad.addColorStop(0, 'rgba(245, 225, 130, 0.5)'); 
                        grad.addColorStop(1, 'rgba(255, 255, 255, 0)'); 
                        return grad;
                    }, 0.55, 45, 0.007, 700)
                ];
                waves.forEach(w => w.init());
            }

            function animate() {
                if (!canvas.isConnected) {
                    cancelAnimationFrame(window.abstractCanvasAnimationId);
                    return;
                }

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

                waves.forEach(w => {
                    w.update();
                    w.draw();
                });

                ctx.save();
                ctx.translate(width * 0.9, height * 0.08);
                
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

                ctx.restore(); 
                ctx.restore(); 

                window.abstractCanvasAnimationId = requestAnimationFrame(animate);
            }

            resize();
            animate();
        };

        document.addEventListener('turbo:load', window.initAbstractCanvas);
    }
    
    // Always trigger init on execution if DOM is ready
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(window.initAbstractCanvas, 100);
    }
</script>
@endpush

