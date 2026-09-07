@extends('layouts.user')

@section('title', 'Verifikasi KTP & Wajah')

@section('page')
<main class="flex-grow relative w-full">
    {{-- Custom Vector Abstract Background --}}
    @include('partials.abstract-bg')

    <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8 mt-20 relative z-10 animate-section">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-900">Verifikasi Identitas</h2>
                <p class="mt-2 text-sm text-gray-600">Selesaikan verifikasi untuk mendapatkan fitur penuh SiladesBeng.</p>
            </div>

            <!-- Stepper -->
            <div class="flex items-center justify-center mb-8">
                <div class="flex items-center w-full max-w-sm">
                    <div id="step-1-indicator" class="w-10 h-10 shrink-0 mx-[-1px] bg-blue-600 p-1.5 flex items-center justify-center rounded-full text-white font-bold transition-all duration-300 shadow-sm ring-4 ring-blue-100 cursor-pointer">
                        <span id="step-1-content">1</span>
                    </div>
                    <div class="w-full h-1 bg-gray-200 transition-all duration-500 ease-in-out" id="line-1"></div>
                    <div id="step-2-indicator" class="w-10 h-10 shrink-0 mx-[-1px] bg-gray-200 p-1.5 flex items-center justify-center rounded-full text-gray-500 font-bold transition-all duration-300">
                        <span id="step-2-content">2</span>
                    </div>
                </div>
            </div>

            <!-- Step 1: Upload KTP -->
            <div id="step-1">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Langkah 1: Unggah Foto KTP</h3>
                <p class="text-sm text-gray-600 mb-6">Pastikan foto KTP terlihat jelas, terang, dan teks dapat terbaca.</p>
                
                <form id="form-ktp" enctype="multipart/form-data" data-turbo="false" action="javascript:void(0)">
                    @csrf
                    <label for="ktp_image" class="mt-1 flex flex-col items-center justify-center p-6 border-2 border-gray-300 border-dashed rounded-xl relative hover:bg-gray-50 hover:border-gray-400 transition-all cursor-pointer group text-center overflow-hidden" id="drop-zone">
                        <div class="space-y-1 text-center w-full relative z-10" id="drop-zone-text">
                            <div class="mx-auto w-16 h-16 rounded-full bg-white flex items-center justify-center mb-3 shadow-sm text-slate-500 border border-slate-200 group-hover:scale-110 transition-transform duration-300">
                                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                            <div class="flex flex-col text-center">
                                <span class="font-bold text-gray-700 text-base">Pilih dari Penyimpanan</span>
                                <input id="ktp_image" name="ktp_image" type="file" class="sr-only" accept="image/*" >
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG up to 5MB</p>
                            </div>
                        </div>
                        <div id="preview-container-ktp" class="w-full relative z-20" style="display: none;">
                            <img id="ktp-preview" class="w-full h-auto rounded-lg object-contain" src="" alt="Preview KTP">
                            <button type="button" id="btn-remove-ktp" class="absolute top-2 right-2 w-8 h-8 bg-red-500/80 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-opacity z-30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </label>

                    <div class="mt-6">
                        <button type="button" id="btn-process-ktp" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition">
                            Proses & Lanjut Scan Wajah
                        </button>
                    </div>
                </form>
            </div>

            <!-- Step 1.5: Review KTP -->
            <div id="step-1-half" class="hidden">
                
                <h3 class="text-xl font-bold text-gray-800 mb-4">Konfirmasi Data KTP</h3>
                
                <div class="mb-6 rounded-lg overflow-hidden border border-gray-200 shadow-sm">
                    <img id="ktp-review-image" src="" alt="Foto KTP Anda" class="w-full h-auto object-contain max-h-64 bg-gray-50">
                    <div class="bg-gray-100 px-4 py-2 text-xs text-gray-600 text-center border-t border-gray-200">
                        Foto KTP yang Anda unggah
                    </div>
                </div>

                <p class="text-sm text-gray-600 mb-6">Berikut adalah data yang terbaca dari KTP Anda. Pastikan data ini sesuai.</p>

                
                <div class="space-y-4 mb-6">
                    <!-- NIK -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <!-- SVG Icon Info -->
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="ml-3 w-full">
                                <label class="text-sm font-medium text-blue-800" for="edit-nik">NIK</label>
                                <input type="text" id="edit-nik" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Masukkan NIK Anda" />
                                <p class="text-xs text-blue-600 mt-1">Jika kosong/salah, silakan perbaiki NIK Anda.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Nama Lengkap -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="ml-3 w-full">
                                <label class="text-sm font-medium text-blue-800" for="edit-nama">Nama Lengkap</label>
                                <input type="text" id="edit-nama" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Masukkan Nama Lengkap Anda" />
                                <p class="text-xs text-blue-600 mt-1">Pastikan Nama Lengkap sesuai dengan KTP Anda.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="ml-3 w-full">
                                <label class="text-sm font-medium text-blue-800" for="edit-alamat">Alamat Lengkap</label>
                                <textarea id="edit-alamat" rows="2" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Masukkan Alamat Lengkap"></textarea>
                                <p class="text-xs text-blue-600 mt-1">Pastikan Alamat sesuai dengan KTP Anda.</p>
                            </div>
                        </div>
                    </div>

                    <!-- RT dan RW -->
                    <div class="flex space-x-4">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg flex-1">
                            <label class="text-sm font-medium text-blue-800" for="edit-rt">RT</label>
                            <input type="text" id="edit-rt" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="001" />
                        </div>
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg flex-1">
                            <label class="text-sm font-medium text-blue-800" for="edit-rw">RW</label>
                            <input type="text" id="edit-rw" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="002" />
                        </div>
                    </div>

                    <!-- Desa dan Kecamatan -->
                    <div class="flex space-x-4 mt-4">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg flex-1">
                            <label class="text-sm font-medium text-blue-800" for="edit-desa">Kelurahan/Desa</label>
                            <input type="text" id="edit-desa" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Nama Desa" />
                        </div>
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg flex-1">
                            <label class="text-sm font-medium text-blue-800" for="edit-kecamatan">Kecamatan</label>
                            <input type="text" id="edit-kecamatan" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md text-sm shadow-sm placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Nama Kecamatan" />
                        </div>
                    </div>


                </div>

                <div class="flex justify-between space-x-4">
                    <button type="button" id="btn-reupload-ktp" class="w-1/2 py-3 px-4 border border-gray-300 rounded-full shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Unggah Ulang
                    </button>
                    <button type="button" id="btn-confirm-ktp" class="w-1/2 py-3 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Data Sesuai, Lanjut
                    </button>
                </div>
            </div>

            <!-- Step 2: Liveness Detection -->
            <div id="step-2" class="hidden">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Langkah 2: Verifikasi Wajah</h3>
                <p class="text-sm text-gray-600 mb-2">Posisikan wajah Anda di dalam bingkai dan ikuti instruksi di layar.</p>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 mb-6 rounded-r-md">
                    <p class="text-xs text-yellow-700">
                        <strong class="font-bold">Perhatian:</strong> Harap lepas masker, topi, atau kacamata gelap agar sistem dapat memverifikasi wajah Anda dengan optimal. Data Anda dienkripsi dengan aman.
                </div>

                <!-- Pilihan Metode Verifikasi Wajah -->
                <div class="flex items-center justify-center p-1 bg-gray-100 rounded-xl mb-5 max-w-sm mx-auto">
                    <button type="button" id="tab-mode-camera" class="flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition-all duration-200 bg-white text-blue-600 shadow-sm flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Scan Kamera</span>
                    </button>
                    <button type="button" id="tab-mode-manual" class="flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>Unggah Foto Selfie</span>
                    </button>
                </div>

                <div class="relative w-full max-w-sm mx-auto overflow-hidden rounded-2xl bg-gray-100 shadow-inner" style="aspect-ratio: 3/4;">
                    <!-- Manual Fallback Container (Hidden by Default) -->
                    <div id="manual-selfie-container" class="absolute inset-0 z-30 bg-white flex flex-col items-center justify-center p-6 hidden">
                        <!-- State Sebelum Pilih Foto -->
                        <div id="manual-selfie-prompt" class="flex flex-col items-center text-center">
                            <div class="w-20 h-20 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-4 shadow-sm border border-blue-100">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h4 class="text-base font-bold text-gray-800 mb-1">Ambil atau Unggah Foto Wajah</h4>
                            <p class="text-xs text-gray-500 mb-6 max-w-xs leading-relaxed">Pilih foto selfie atau wajah Anda yang jelas dan menghadap ke depan.</p>
                            
                            <label for="manual_selfie_input" class="bg-blue-600 hover:bg-blue-700 text-white px-7 py-3 rounded-full cursor-pointer transition shadow-md hover:shadow-lg font-semibold text-sm flex items-center space-x-2">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span>Pilih Foto Wajah</span>
                                <input type="file" id="manual_selfie_input" accept="image/*" class="sr-only">
                            </label>
                        </div>

                        <!-- State Sesudah Pilih Foto (Pratinjau) -->
                        <div id="manual-selfie-preview-wrapper" class="absolute inset-0 w-full h-full hidden">
                            <img id="manual-selfie-preview" class="w-full h-full object-cover" alt="Preview Selfie">
                            <div class="absolute inset-x-0 bottom-4 flex justify-center z-40">
                                <label for="manual_selfie_change_input" class="bg-black/75 hover:bg-black text-white px-5 py-2.5 rounded-full cursor-pointer transition text-xs font-semibold backdrop-blur shadow-lg flex items-center space-x-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    <span>Ganti Foto</span>
                                    <input type="file" id="manual_selfie_change_input" accept="image/*" class="sr-only">
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Video Stream -->
                    <video id="webcam" class="absolute w-full h-full object-cover transform -scale-x-100" autoplay playsinline></video>
                    <!-- Overlay Canvas -->
                    <canvas id="output_canvas" class="absolute w-full h-full object-cover transform -scale-x-100 pointer-events-none"></canvas>
                    
                    <!-- Liveness Overlay -->
                    <div id="liveness-overlay" class="absolute inset-0 border-[20px] border-white/50 rounded-2xl pointer-events-none z-10 flex flex-col items-center justify-between p-4">
                        <div id="liveness-instruction" class="bg-black/70 text-white px-4 py-2 rounded-full font-bold text-sm text-center shadow-lg mt-4 animate-bounce">
                            Memuat Kamera...
                        </div>

                        <!-- Visual Animation Container -->
                        <div id="liveness-animation" class="mt-4 mb-auto text-white">
                            <svg class="w-16 h-16 opacity-0 transition-opacity duration-300" viewBox="0 0 24 24"></svg>
                        </div>

                        <div class="w-48 h-64 border-4 border-dashed border-white/70 rounded-full"></div>

                        <div id="liveness-status" class="bg-blue-600 text-white px-4 py-2 rounded-full font-bold text-sm shadow-lg mb-4 flex items-center space-x-2">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                            </span>
                            <span>Mendeteksi Wajah</span>
                        </div>
                    </div>
                </div>

                <!-- Hint deteksi pencahayaan gelap & kamera buram -->
                <div id="smart-lighting-hint" class="hidden mt-3 max-w-sm mx-auto p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs flex items-center justify-between shadow-xs">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Pencahayaan gelap. Pindah ke tempat terang atau gunakan foto.</span>
                    </span>
                    <button type="button" class="btn-switch-to-manual font-bold text-amber-900 underline ml-2 shrink-0">Unggah Foto</button>
                </div>

                <div id="smart-blur-hint" class="hidden mt-3 max-w-sm mx-auto p-3 bg-blue-50 border border-blue-200 rounded-xl text-blue-800 text-xs flex items-center justify-between shadow-xs">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Kamera buram atau wajah belum terdeteksi?</span>
                    </span>
                    <button type="button" class="btn-switch-to-manual font-bold text-blue-700 underline ml-2 shrink-0">Unggah Foto</button>
                </div>

                <!-- Tombol Paksa Manual -->
                <div id="btn-force-manual-container" class="mt-4 mb-2 text-center w-full">
                    <button type="button" id="btn-force-manual" class="text-xs text-gray-500 hover:text-blue-600 transition font-medium">
                        Kamera bermasalah atau buram? Klik untuk unggah foto wajah dari galeri
                    </button>
                </div>
                <div class="mt-8 flex justify-center space-x-4">
                    <button type="button" id="btn-back-step-1" class="py-2.5 px-6 border border-gray-300 rounded-full shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Kembali
                    </button>
                    <button type="button" id="btn-submit-kyc" disabled class="py-2.5 px-6 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition cursor-not-allowed">
                        Kirim Data
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Loading -->
<div id="loading-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-gray-900/60"></div>
    <div class="bg-white p-8 rounded-2xl shadow-2xl relative z-10 flex flex-col items-center">
        <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-lg font-bold text-gray-800" id="loading-text">Memproses Data KTP...</p>
        <p class="text-sm text-gray-500 mt-2 text-center max-w-xs">Sistem SiladesBeng sedang mengekstrak data KTP Anda. Mohon tunggu sebentar...</p>
    </div>
</div>
</main>
@endsection

@push('styles')
<style>
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
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    .animate-section.is-visible {
        opacity: 1;
        transform: translateY(0);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js" crossorigin="anonymous"></script>

<script>
document.addEventListener('change', function(e) {
    if (e.target && e.target.id === 'ktp_image') {
        const input = e.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(evt) {
                document.getElementById('ktp-preview').src = evt.target.result;
                const dropZoneText = document.getElementById('drop-zone-text');
                if(dropZoneText) dropZoneText.style.display = 'none';
                document.getElementById('preview-container-ktp').style.display = 'block';
                document.getElementById('btn-process-ktp').disabled = false;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
});

document.addEventListener('click', async function(e) {
    if (e.target && e.target.closest('#btn-remove-ktp')) {
        e.preventDefault();
        document.getElementById('ktp_image').value = '';
        document.getElementById('preview-container-ktp').style.display = 'none';
        document.getElementById('ktp-preview').src = '';
        const dropZoneText = document.getElementById('drop-zone-text');
        if(dropZoneText) dropZoneText.style.display = 'flex';
        document.getElementById('btn-process-ktp').disabled = true;
    }

    if (e.target && e.target.closest('#btn-process-ktp')) {
        e.preventDefault();
        const fileInput = document.getElementById('ktp_image');
        if(!fileInput.files[0]) {
            showToast('Silakan pilih foto KTP terlebih dahulu', 'warning');
            return;
        }

        const btn = document.getElementById('btn-process-ktp');
        const modal = document.getElementById('loading-modal');
        const form = document.getElementById('form-ktp');
        const formData = new FormData(form);
        
        btn.disabled = true;
        modal.classList.remove('hidden');

        try {
            const response = await fetch('{{ route("kyc.process") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                window.currentKycId = data.kyc_id;
                document.getElementById('edit-nik').value = data.ocr_data.nik || '';
                document.getElementById('edit-nama').value = data.ocr_data.name || '';
                document.getElementById('edit-alamat').value = data.ocr_data.address || '';
                document.getElementById('edit-rt').value = data.ocr_data.rt || '';
                document.getElementById('edit-rw').value = data.ocr_data.rw || '';
                
                const toTitleCase = (str) => {
                    return str.toLowerCase().split(' ').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
                };
                
                document.getElementById('edit-desa').value = data.ocr_data.desa ? toTitleCase(data.ocr_data.desa) : '';
                document.getElementById('edit-kecamatan').value = data.ocr_data.kecamatan ? toTitleCase(data.ocr_data.kecamatan) : '';

                if(!data.ocr_data.nik) {
                    showToast('Teks Tidak Terbaca. Silakan isi data secara manual di form.', 'warning');
                }

                document.getElementById('step-1').classList.add('hidden');
                document.getElementById('ktp-review-image').src = document.getElementById('ktp-preview').src;
                document.getElementById('step-1-half').classList.remove('hidden');
            } else {
                showToast(data.message || 'Terjadi kesalahan saat membaca KTP.', 'error');
            }
        } catch (error) {
            showToast('Terjadi kesalahan server.', 'error');
            console.error(error);
        } finally {
            btn.disabled = false;
            modal.classList.add('hidden');
        }
    }
});

let camera = null;
let faceMesh = null;
let livenessState = 0;
let collectedFaceData = [];
let faceSnapshot = null;
let isBuildingCollage = false;
let frameFront = null, frameBlink = null, frameTurn = null;

async function buildCollage(img1, img2, img3) {
    const canvas = document.createElement('canvas');
    canvas.width = 600; canvas.height = 200;
    const ctx = canvas.getContext('2d');
    
    const loadImage = (src) => new Promise(resolve => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.src = src;
    });

    try {
        const [i1, i2, i3] = await Promise.all([loadImage(img1), loadImage(img2), loadImage(img3)]);
        ctx.drawImage(i1, 0, 0, 200, 200);
        ctx.drawImage(i2, 200, 0, 200, 200);
        ctx.drawImage(i3, 400, 0, 200, 200);
        return canvas.toDataURL('image/jpeg', 0.8);
    } catch(e) { return null; }
}

function onResults(results) {
    const canvasElement = document.getElementById('output_canvas');
    const canvasCtx = canvasElement.getContext('2d');
    const instructionEl = document.getElementById('liveness-instruction');
    const submitBtn = document.getElementById('btn-submit-kyc');
    
    canvasCtx.save();
    canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
    canvasCtx.drawImage(results.image, 0, 0, canvasElement.width, canvasElement.height);

    if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
        const landmarks = results.multiFaceLandmarks[0];
        
        const leftEye = [33, 160, 158, 133, 153, 144].map(i => landmarks[i]);
        const rightEye = [362, 385, 387, 263, 373, 380].map(i => landmarks[i]);
        const calcEAR = (eye) => (Math.hypot(eye[1].x - eye[5].x, eye[1].y - eye[5].y) + Math.hypot(eye[2].x - eye[4].x, eye[2].y - eye[4].y)) / (2.0 * Math.hypot(eye[0].x - eye[3].x, eye[0].y - eye[3].y));
        const avgEAR = (calcEAR(leftEye) + calcEAR(rightEye)) / 2;

        const nose = landmarks[1];
        const leftCheek = landmarks[234];
        const rightCheek = landmarks[454];
        const noseTurn = (nose.x - leftCheek.x) / (rightCheek.x - leftCheek.x);
        
        collectedFaceData.push({ ear: avgEAR, turn: noseTurn, ts: Date.now() });

        if (livenessState === 0) {
            instructionEl.innerText = "Posisikan Wajah di Tengah";
            if (noseTurn > 0.4 && noseTurn < 0.6) {
                if(!frameFront) frameFront = canvasElement.toDataURL("image/jpeg", 0.9);
                livenessState = 1;
            }
        } else if (livenessState === 1) {
            instructionEl.innerText = "Kedipkan Mata Anda";
            if (avgEAR < 0.25) {
                if(!frameBlink) frameBlink = canvasElement.toDataURL("image/jpeg", 0.9);
                livenessState = 2;
            }
        } else if (livenessState === 2) {
            instructionEl.innerText = "Tolehkan Kepala ke Kanan/Kiri";
            if (noseTurn < 0.35 || noseTurn > 0.65) {
                if(!frameTurn) frameTurn = canvasElement.toDataURL("image/jpeg", 0.9);
                livenessState = 3;
            }
        } else if (livenessState === 3) {
            instructionEl.innerText = "Terverifikasi!";
            instructionEl.classList.replace('bg-black/70', 'bg-green-600');
            if (!faceSnapshot && frameFront && frameBlink && frameTurn && !isBuildingCollage) {
                isBuildingCollage = true;
                buildCollage(frameFront, frameBlink, frameTurn).then(data => {
                    faceSnapshot = data;
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('cursor-not-allowed');
                    setTimeout(() => { if(camera) camera.stop(); }, 500);
                });
            }
        }
    } else {
        instructionEl.innerText = "Wajah Tidak Terdeteksi";
        // Cek apakah kamera gelap atau buram jika wajah belum terdeteksi
        try {
            const imgData = canvasCtx.getImageData(0, 0, canvasElement.width, canvasElement.height);
            let colorSum = 0;
            const step = 32;
            let sampleCount = 0;
            for (let i = 0; i < imgData.data.length; i += 4 * step) {
                colorSum += (imgData.data[i] + imgData.data[i+1] + imgData.data[i+2]) / 3;
                sampleCount++;
            }
            const brightness = Math.floor(colorSum / sampleCount);
            const lightHint = document.getElementById('smart-lighting-hint');
            if (brightness < 38) {
                instructionEl.innerText = "Pencahayaan Terlalu Gelap";
                if (lightHint) lightHint.classList.remove('hidden');
            } else {
                if (lightHint) lightHint.classList.add('hidden');
            }
        } catch(e) {}
    }
    canvasCtx.restore();
}

function updateModeTabs(mode) {
    const tabCamera = document.getElementById('tab-mode-camera');
    const tabManual = document.getElementById('tab-mode-manual');
    if (!tabCamera || !tabManual) return;
    
    if (mode === 'manual') {
        tabManual.className = 'flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition-all duration-200 bg-white text-blue-600 shadow-sm flex items-center justify-center gap-1.5';
        tabCamera.className = 'flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 flex items-center justify-center gap-1.5';
    } else {
        tabCamera.className = 'flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition-all duration-200 bg-white text-blue-600 shadow-sm flex items-center justify-center gap-1.5';
        tabManual.className = 'flex-1 py-2 px-3 rounded-lg text-xs font-semibold transition-all duration-200 text-gray-600 hover:text-gray-900 flex items-center justify-center gap-1.5';
    }
}

function activateManualMode() {
    if (camera) {
        try { camera.stop(); } catch(e) {}
    }
    updateModeTabs('manual');

    const livenessOverlay = document.getElementById('liveness-overlay');
    const webcam = document.getElementById('webcam');
    const outputCanvas = document.getElementById('output_canvas');
    const manualContainer = document.getElementById('manual-selfie-container');
    const forceContainer = document.getElementById('btn-force-manual-container');
    const lightHint = document.getElementById('smart-lighting-hint');
    const blurHint = document.getElementById('smart-blur-hint');

    if (livenessOverlay) livenessOverlay.style.display = 'none';
    if (webcam) webcam.style.display = 'none';
    if (outputCanvas) outputCanvas.style.display = 'none';
    if (manualContainer) manualContainer.classList.remove('hidden');
    if (forceContainer) forceContainer.style.display = 'none';
    if (lightHint) lightHint.classList.add('hidden');
    if (blurHint) blurHint.classList.add('hidden');
}

function activateCameraMode() {
    const isSecure = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
    if (!isSecure || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showToast('Koneksi HTTP terdeteksi. Peramban memerlukan koneksi aman (HTTPS) untuk kamera langsung. Silakan gunakan mode Unggah Foto Selfie.', 'warning');
        activateManualMode();
        return;
    }

    updateModeTabs('camera');

    const livenessOverlay = document.getElementById('liveness-overlay');
    const webcam = document.getElementById('webcam');
    const outputCanvas = document.getElementById('output_canvas');
    const manualContainer = document.getElementById('manual-selfie-container');
    const forceContainer = document.getElementById('btn-force-manual-container');

    if (manualContainer) manualContainer.classList.add('hidden');
    if (livenessOverlay) livenessOverlay.style.display = '';
    if (webcam) webcam.style.display = '';
    if (outputCanvas) outputCanvas.style.display = '';
    if (forceContainer) forceContainer.style.display = '';

    startLivenessDetection();
}

function handleManualSelfieFile(file) {
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(evt) {
        const img = new Image();
        img.onload = function() {
            // Kompresi resolusi maksimal 1080px agar pengiriman cepat dan tidak melebihi batas request
            const maxDim = 1080;
            let width = img.width;
            let height = img.height;
            if (width > maxDim || height > maxDim) {
                if (width > height) {
                    height = Math.round((height * maxDim) / width);
                    width = maxDim;
                } else {
                    width = Math.round((width * maxDim) / height);
                    height = maxDim;
                }
            }
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            const compressedDataUrl = canvas.toDataURL('image/jpeg', 0.85);

            const preview = document.getElementById('manual-selfie-preview');
            const previewWrapper = document.getElementById('manual-selfie-preview-wrapper');
            const promptEl = document.getElementById('manual-selfie-prompt');
            
            if (preview) preview.src = compressedDataUrl;
            if (previewWrapper) previewWrapper.classList.remove('hidden');
            if (promptEl) promptEl.classList.add('hidden');

            faceSnapshot = compressedDataUrl;
            collectedFaceData = []; // empty array for manual fallback

            const submitBtn = document.getElementById('btn-submit-kyc');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('cursor-not-allowed', 'opacity-50');
            }
            showToast('Foto wajah berhasil dimuat. Silakan klik tombol Kirim Data.', 'success');
        };
        img.src = evt.target.result;
    };
    reader.readAsDataURL(file);
}

function startLivenessDetection() {
    const videoElement = document.getElementById('webcam');
    const canvasElement = document.getElementById('output_canvas');
    
    // 1. Cek keamanan protokol (Chrome otomatis memblokir kamera jika bukan HTTPS/localhost)
    const isSecure = window.isSecureContext || location.hostname === 'localhost' || location.hostname === '127.0.0.1';
    if (!isSecure || !navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showToast('Koneksi HTTP terdeteksi. Mengalihkan langsung ke mode unggah foto wajah.', 'info');
        activateManualMode();
        return;
    }

    // 2. Safety Timeout: Jika pemuatan AI MediaPipe / kamera memakan waktu > 5 detik (perangkat spesifikasi rendah atau jaringan lambat)
    const fallbackTimeout = setTimeout(() => {
        if (livenessState === 0 && !faceSnapshot) {
            console.warn('Inisialisasi kamera/AI memakan waktu lama, mengalihkan ke mode manual.');
            showToast('Kamera memuat terlalu lama. Mengalihkan ke mode unggah foto agar lebih ringan.', 'info');
            activateManualMode();
        }
    }, 5000);

    // 3. Saran alternatif jika setelah 4 detik wajah belum terdeteksi (membantu kamera buram / gelap)
    setTimeout(() => {
        if (livenessState === 0 && !faceSnapshot) {
            const blurHint = document.getElementById('smart-blur-hint');
            if (blurHint) blurHint.classList.remove('hidden');
        }
    }, 4000);

    try {
        if (!faceMesh) {
            faceMesh = new FaceMesh({locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`});
            faceMesh.setOptions({maxNumFaces: 1, refineLandmarks: true});
            faceMesh.onResults(onResults);
        }
        camera = new Camera(videoElement, {
            onFrame: async () => {
                canvasElement.width = videoElement.videoWidth;
                canvasElement.height = videoElement.videoHeight;
                await faceMesh.send({image: videoElement});
            }, width: 480, height: 640
        });
        
        camera.start().then(() => {
            clearTimeout(fallbackTimeout);
            livenessState = 0;
            document.getElementById('btn-submit-kyc').disabled = true;
        }).catch((err) => {
            clearTimeout(fallbackTimeout);
            console.error(err);
            showToast('Kamera tidak dapat diakses. Mode unggah foto manual diaktifkan.', 'warning');
            activateManualMode();
        });
    } catch (err) {
        clearTimeout(fallbackTimeout);
        console.error(err);
        activateManualMode();
    }
}

function updateStepper(step) {
    const step1Ind = document.getElementById('step-1-indicator');
    const step2Ind = document.getElementById('step-2-indicator');
    const line1 = document.getElementById('line-1');
    const step1Content = document.getElementById('step-1-content');
    const step2Content = document.getElementById('step-2-content');

    if (!step1Ind || !step2Ind || !line1) return;

    if (step === 1) {
        step1Ind.className = 'w-10 h-10 shrink-0 mx-[-1px] bg-blue-600 p-1.5 flex items-center justify-center rounded-full text-white font-bold transition-all duration-300 shadow-sm ring-4 ring-blue-100 cursor-pointer';
        if (step1Content) {
            step1Content.innerHTML = '1';
        } else {
            step1Ind.innerHTML = '1';
        }

        line1.className = 'w-full h-1 bg-gray-200 transition-all duration-500 ease-in-out';

        step2Ind.className = 'w-10 h-10 shrink-0 mx-[-1px] bg-gray-200 p-1.5 flex items-center justify-center rounded-full text-gray-500 font-bold transition-all duration-300';
        if (step2Content) {
            step2Content.innerHTML = '2';
        } else {
            step2Ind.innerHTML = '2';
        }
    } else if (step === 2) {
        step1Ind.className = 'w-10 h-10 shrink-0 mx-[-1px] bg-blue-600 p-1.5 flex items-center justify-center rounded-full text-white font-bold transition-all duration-300 shadow-sm cursor-pointer hover:bg-blue-700';
        if (step1Content) {
            step1Content.innerHTML = '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>';
        } else {
            step1Ind.innerHTML = '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>';
        }

        line1.className = 'w-full h-1 bg-blue-600 transition-all duration-500 ease-in-out';

        step2Ind.className = 'w-10 h-10 shrink-0 mx-[-1px] bg-blue-600 p-1.5 flex items-center justify-center rounded-full text-white font-bold transition-all duration-300 shadow-sm ring-4 ring-blue-100 cursor-pointer';
        if (step2Content) {
            step2Content.innerHTML = '2';
        } else {
            step2Ind.innerHTML = '2';
        }
    }
}

function initKycPage() {
    if (!document.getElementById('form-ktp')) return;

    // Inisialisasi stepper sesuai step yang aktif
    const step2El = document.getElementById('step-2');
    if (step2El && !step2El.classList.contains('hidden')) {
        updateStepper(2);
    } else {
        updateStepper(1);
    }

    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('ktp_image');
    if(dropZone) {
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('bg-blue-50'); });
        dropZone.addEventListener('dragleave', (e) => { e.preventDefault(); dropZone.classList.remove('bg-blue-50'); });
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault(); dropZone.classList.remove('bg-blue-50');
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change', {bubbles:true}));
            }
        });
    }

    document.getElementById('btn-reupload-ktp').addEventListener('click', () => {
        document.getElementById('step-1-half').classList.add('hidden');
        document.getElementById('step-1').classList.remove('hidden');
        updateStepper(1);
    });

    const btnForce = document.getElementById('btn-force-manual');
    if (btnForce) {
        btnForce.addEventListener('click', () => {
            activateManualMode();
            showToast('Mode manual diaktifkan. Silakan pilih foto wajah Anda.', 'info');
        });
    }

    const tabCamera = document.getElementById('tab-mode-camera');
    if (tabCamera) {
        tabCamera.addEventListener('click', () => {
            activateCameraMode();
        });
    }

    const tabManual = document.getElementById('tab-mode-manual');
    if (tabManual) {
        tabManual.addEventListener('click', () => {
            activateManualMode();
        });
    }

    document.querySelectorAll('.btn-switch-to-manual').forEach(btn => {
        btn.addEventListener('click', () => {
            activateManualMode();
        });
    });

    const manualInput = document.getElementById('manual_selfie_input');
    if (manualInput) {
        manualInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                handleManualSelfieFile(e.target.files[0]);
            }
        });
    }

    const manualChangeInput = document.getElementById('manual_selfie_change_input');
    if (manualChangeInput) {
        manualChangeInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                handleManualSelfieFile(e.target.files[0]);
            }
        });
    }
    
    document.getElementById('btn-confirm-ktp').addEventListener('click', () => {
        document.getElementById('step-1-half').classList.add('hidden');
        document.getElementById('step-2').classList.remove('hidden');
        updateStepper(2);
        startLivenessDetection();
    });

    document.getElementById('btn-back-step-1').addEventListener('click', () => {
        document.getElementById('step-2').classList.add('hidden');
        document.getElementById('step-1-half').classList.remove('hidden');
        if (camera) camera.stop();
        updateStepper(1);
    });

    // Interaktivitas indikator stepper jika diklik
    const step1Ind = document.getElementById('step-1-indicator');
    if (step1Ind) {
        step1Ind.addEventListener('click', () => {
            const step2El = document.getElementById('step-2');
            if (step2El && !step2El.classList.contains('hidden')) {
                step2El.classList.add('hidden');
                document.getElementById('step-1-half').classList.remove('hidden');
                if (camera) camera.stop();
                updateStepper(1);
            }
        });
    }

    const step2Ind = document.getElementById('step-2-indicator');
    if (step2Ind) {
        step2Ind.addEventListener('click', () => {
            const step1HalfEl = document.getElementById('step-1-half');
            if (window.currentKycId && step1HalfEl && !step1HalfEl.classList.contains('hidden')) {
                step1HalfEl.classList.add('hidden');
                document.getElementById('step-2').classList.remove('hidden');
                updateStepper(2);
                startLivenessDetection();
            }
        });
    }

    document.getElementById('btn-submit-kyc').addEventListener('click', async function() {
        if (!faceSnapshot) {
            showToast('Silakan ambil atau unggah foto wajah terlebih dahulu.', 'warning');
            return;
        }

        const originalText = this.innerText;
        this.disabled = true;
        this.innerText = 'Mengirim Data...';

        try {
            const response = await fetch('{{ route("kyc.submit") }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify({
                    kyc_id: window.currentKycId,
                    face_data: collectedFaceData || [],
                    face_image: faceSnapshot,
                    edited_nik: document.getElementById('edit-nik').value,
                    edited_nama: document.getElementById('edit-nama').value,
                    edited_alamat: document.getElementById('edit-alamat').value,
                    edited_rt: document.getElementById('edit-rt').value,
                    edited_rw: document.getElementById('edit-rw').value,
                    edited_desa: document.getElementById('edit-desa').value,
                    edited_kecamatan: document.getElementById('edit-kecamatan').value
                })
            });
            const data = await response.json();
            if (response.ok && data.success) {
                showToast(data.message || 'Berhasil menyimpan data.', 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("beranda") }}';
                }, 1000);
            } else {
                let errorMsg = data.message || 'Gagal menyimpan data.';
                if (data.errors) {
                    errorMsg = Object.values(data.errors).flat().join('<br>');
                }
                showToast(errorMsg, 'error');
                this.disabled = false;
                this.innerText = originalText;
            }
        } catch (error) {
            console.error('Submit KYC Error:', error);
            showToast('Terjadi gangguan jaringan atau server saat mengirim data.', 'error');
            this.disabled = false;
            this.innerText = originalText;
        }
    });
}

document.addEventListener('DOMContentLoaded', initKycPage);
document.addEventListener('turbo:load', initKycPage);
if (document.readyState !== 'loading') initKycPage();

</script>
@endpush
