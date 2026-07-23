@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full bg-gray-50">
    <section class="relative z-10 min-h-screen pt-32 pb-16">
        <div class="max-w-3xl mx-auto px-6 relative z-20">
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="inline-block p-4 rounded-full bg-blue-100 mb-4">
                    <i class='bx bx-id-card text-4xl text-blue-600'></i>
                </div>
                <h1 class="text-3xl md:text-4xl font-black mb-3">
                    <span class="text-gray-900">Verifikasi </span>
                    <span class="text-blue-600">Identitas Diri</span>
                </h1>
                <p class="text-gray-600 font-medium text-lg">
                    Untuk keamanan dan kenyamanan bersama, mohon unggah foto KTP dan foto Wajah (Selfie) Anda. Data ini dienkripsi aman.
                </p>
            </div>

            @if($user->verification_status == 'rejected')
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-r-lg shadow-sm">
                <div class="flex">
                    <i class='bx bx-error-circle text-red-500 text-2xl mr-3'></i>
                    <div>
                        <p class="font-bold text-red-800">Verifikasi Sebelumnya Ditolak</p>
                        <p class="text-red-700 text-sm mt-1">Alasan: {{ $user->ktp_rejection_reason }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($user->verification_status == 'pending')
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded-2xl shadow-lg text-center mb-8">
                <i class='bx bx-time-five text-5xl text-yellow-500 mb-3 animate-spin-slow'></i>
                <h3 class="text-2xl font-bold text-yellow-800 mb-2">Menunggu Persetujuan</h3>
                <p class="text-yellow-700">Data verifikasi Anda sedang diperiksa oleh Admin Desa. Proses ini biasanya memakan waktu 1x24 jam.</p>
                <a href="{{ route('user.profile') }}" class="mt-4 inline-block px-6 py-2 bg-yellow-500 text-white font-bold rounded-lg hover:bg-yellow-600 transition">
                    Kembali ke Profil
                </a>
            </div>
            @else
            <!-- Form Upload -->
            <form id="verifikasi-form" action="{{ route('user.verifikasi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                
                <!-- KTP Upload -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class='bx bx-credit-card-front text-blue-500'></i> 1. Foto e-KTP Asli
                    </h2>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:bg-gray-50 transition relative overflow-hidden" id="ktp-dropzone">
                        <input type="file" name="ktp_photo" id="ktp_photo" accept="image/*" capture="environment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required>
                        <div id="ktp-preview-container" class="hidden absolute inset-0 z-0">
                            <img id="ktp-preview" src="" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition">
                                <span class="text-white font-bold">Ganti Foto</span>
                            </div>
                        </div>
                        <div id="ktp-placeholder" class="z-0 relative pointer-events-none">
                            <i class='bx bx-camera text-5xl text-gray-400 mb-3'></i>
                            <p class="font-bold text-gray-700">Tap untuk Ambil Foto KTP</p>
                            <p class="text-sm text-gray-500 mt-1">Pastikan tulisan terbaca jelas dan tidak silau</p>
                        </div>
                    </div>
                </div>

                <!-- Face Upload -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class='bx bx-user-pin text-blue-500'></i> 2. Foto Wajah (Selfie)
                    </h2>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:bg-gray-50 transition relative overflow-hidden" id="face-dropzone">
                        <input type="file" name="face_photo" id="face_photo" accept="image/*" capture="user" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required>
                        <div id="face-preview-container" class="hidden absolute inset-0 z-0">
                            <img id="face-preview" src="" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition">
                                <span class="text-white font-bold">Ganti Selfie</span>
                            </div>
                        </div>
                        <div id="face-placeholder" class="z-0 relative pointer-events-none">
                            <i class='bx bx-face text-5xl text-gray-400 mb-3'></i>
                            <p class="font-bold text-gray-700">Tap untuk Selfie</p>
                            <p class="text-sm text-gray-500 mt-1">Gunakan kamera depan, hindari memakai masker/topi</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-4 text-sm">
                    <button type="button" id="use-gallery-btn" class="text-blue-600 hover:text-blue-800 font-semibold underline">
                        Kamera rusak? Gunakan Galeri
                    </button>
                    <p class="text-gray-500">* Maksimal 5MB per file</p>
                </div>

                <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-xl rounded-xl shadow-xl hover:shadow-2xl transition-all transform hover:-translate-y-1">
                    KIRIM DATA VERIFIKASI
                </button>
            </form>

            <!-- Pesan Smart Guidance (Hidden by default) -->
            <div id="smart-guidance-alert" class="hidden fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-full shadow-2xl font-bold flex items-center gap-2 z-50">
                <i class='bx bx-brightness-half text-xl'></i>
                <span id="smart-guidance-text">Foto terlalu gelap, mohon cari tempat terang!</span>
            </div>
            @endif

        </div>
    </section>
</main>

<canvas id="checker-canvas" class="hidden"></canvas>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ktpInput = document.getElementById('ktp_photo');
        const faceInput = document.getElementById('face_photo');
        const galleryBtn = document.getElementById('use-gallery-btn');
        const guidanceAlert = document.getElementById('smart-guidance-alert');
        const guidanceText = document.getElementById('smart-guidance-text');
        const canvas = document.getElementById('checker-canvas');
        const ctx = canvas ? canvas.getContext('2d') : null;

        // Hilangkan atribut capture (izinkan galeri)
        if(galleryBtn) {
            galleryBtn.addEventListener('click', function() {
                ktpInput.removeAttribute('capture');
                faceInput.removeAttribute('capture');
                Swal.fire({
                    icon: 'info',
                    title: 'Galeri Diaktifkan',
                    text: 'Anda sekarang bisa memilih foto dari memori HP.',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }

        function handleImagePreview(input, previewId, placeholderId, containerId) {
            if(!input) return;
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                // Smart Guidance: Cek kecerahan menggunakan Canvas
                if(file.type.match('image.*') && ctx) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const img = new Image();
                        img.onload = function() {
                            // Resize small for fast processing
                            canvas.width = 100;
                            canvas.height = 100;
                            ctx.drawImage(img, 0, 0, 100, 100);
                            
                            const imageData = ctx.getImageData(0, 0, 100, 100);
                            const data = imageData.data;
                            let colorSum = 0;
                            
                            for (let i = 0; i < data.length; i += 4) {
                                // R + G + B
                                colorSum += data[i] + data[i+1] + data[i+2];
                            }
                            // Calculate average brightness
                            let brightness = Math.floor(colorSum / (100 * 100 * 3));
                            
                            if (brightness < 40) {
                                guidanceText.innerText = "Foto Anda terlalu gelap! Sistem mungkin akan menolaknya.";
                                guidanceAlert.classList.remove('hidden');
                                setTimeout(() => guidanceAlert.classList.add('hidden'), 5000);
                            } else if (brightness > 240) {
                                guidanceText.innerText = "Foto terlalu silau/terang! Hindari pantulan flash.";
                                guidanceAlert.classList.remove('hidden');
                                setTimeout(() => guidanceAlert.classList.add('hidden'), 5000);
                            }
                            
                            // Set Preview
                            document.getElementById(previewId).src = event.target.result;
                            document.getElementById(containerId).classList.remove('hidden');
                            document.getElementById(placeholderId).classList.add('hidden');
                        }
                        img.src = event.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        handleImagePreview(ktpInput, 'ktp-preview', 'ktp-placeholder', 'ktp-preview-container');
        handleImagePreview(faceInput, 'face-preview', 'face-placeholder', 'face-preview-container');
    });
</script>
@endpush
@endsection
