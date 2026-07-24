@extends('layouts.user')

@section('page')
<main class="flex-grow relative w-full">
    {{-- Custom Vector Abstract Background --}}
    @include('partials.abstract-bg')

    <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8 mt-20 relative z-10">
        <div class="bg-white/60 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-900">Verifikasi Identitas (KYC)</h2>
                <p class="mt-2 text-sm text-gray-600">Untuk keamanan dan kenyamanan bersama, mohon unggah foto KTP dan foto Wajah (Selfie) Anda. Data ini dienkripsi aman.</p>
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
                <div>
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class='bx bx-credit-card-front text-blue-500'></i> 1. Foto e-KTP Asli
                    </h3>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md relative hover:bg-gray-50 transition cursor-pointer" id="ktp-dropzone">
                        <input type="file" name="ktp_photo" id="ktp_photo" accept="image/*" capture="environment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required>
                        <div id="ktp-preview-container" class="hidden absolute inset-0 z-0">
                            <img id="ktp-preview" src="" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 hover:opacity-100 transition">
                                <span class="text-white font-bold">Ganti Foto</span>
                            </div>
                        </div>
                        <div id="ktp-placeholder" class="space-y-1 text-center z-0 relative pointer-events-none">
                            <i class='bx bx-camera text-5xl text-gray-400 mb-3 block'></i>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <span class="relative bg-transparent rounded-md font-medium text-blue-600">Tap untuk Ambil Foto KTP</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Pastikan tulisan terbaca jelas dan tidak silau</p>
                        </div>
                    </div>
                </div>

                <!-- Face Upload (Webcam Live) -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i class='bx bx-user-pin text-blue-500'></i> 2. Foto Wajah (Selfie)
                        </h3>
                        <!-- Tombol Tukar Kamera (Muncul jika ada >1 kamera) -->
                        <button type="button" id="flip-camera-btn" class="hidden text-sm text-gray-600 hover:text-blue-600 bg-gray-100 px-3 py-1 rounded-full font-medium transition">
                            <i class='bx bx-refresh text-lg align-middle'></i> Tukar Kamera
                        </button>
                    </div>

                    <div class="mt-1 border-2 border-gray-300 border-dashed rounded-xl p-4 text-center relative overflow-hidden bg-gray-50" id="camera-container">
                        <input type="file" name="face_photo" id="face_photo" accept="image/*" class="hidden" required>
                        
                        <!-- Initial State -->
                        <div id="camera-start-view" class="py-8">
                            <i class='bx bx-camera text-5xl text-gray-400 mb-3'></i>
                            <p class="font-bold text-gray-700 mb-4">Siap untuk Selfie?</p>
                            <button type="button" id="start-camera-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-full transition shadow-md">
                                Buka Kamera
                            </button>
                        </div>

                        <!-- Live Video State -->
                        <div id="camera-live-view" class="hidden relative w-full max-w-sm mx-auto bg-black rounded-lg overflow-hidden shadow-inner aspect-[3/4]">
                            <video id="webcam-video" class="absolute inset-0 w-full h-full object-cover transform -scale-x-100" autoplay playsinline></video>
                            
                            <div class="absolute bottom-4 left-0 right-0 flex justify-center">
                                <button type="button" id="take-photo-btn" class="w-16 h-16 bg-white/30 border-4 border-white rounded-full flex items-center justify-center hover:bg-white/50 transition">
                                    <div class="w-12 h-12 bg-white rounded-full"></div>
                                </button>
                            </div>
                        </div>

                        <!-- Result State -->
                        <div id="camera-result-view" class="hidden relative w-full max-w-sm mx-auto rounded-lg overflow-hidden shadow-md aspect-[3/4]">
                            <img id="selfie-preview" class="absolute inset-0 w-full h-full object-cover transform -scale-x-100" src="">
                            <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-4 px-4">
                                <button type="button" id="retake-photo-btn" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded-full transition shadow-md">
                                    Ulangi
                                </button>
                                <span class="flex-1 bg-green-500 text-white font-bold py-2 px-4 rounded-full shadow-md text-center flex items-center justify-center">
                                    <i class='bx bx-check mr-1'></i> Oke
                                </span>
                            </div>
                        </div>

                        <canvas id="snapshot-canvas" class="hidden"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 text-center">Gunakan kamera, pastikan wajah terlihat terang dan hindari memakai masker/topi.</p>
                </div>

                <div class="flex items-center justify-between mt-6 text-sm mb-6">
                    <button type="button" id="use-gallery-btn" class="text-blue-600 hover:text-blue-800 font-semibold underline">
                        Pilih foto dari Galeri
                    </button>
                    <p class="text-gray-500">* Maksimal 5MB per file</p>
                </div>

                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Proses & Kirim Data Verifikasi
                    </button>
            </form>

            <!-- Pesan Smart Guidance (Hidden by default) -->
            <div id="smart-guidance-alert" class="hidden fixed bottom-10 left-1/2 transform -translate-x-1/2 bg-red-600 text-white px-6 py-3 rounded-full shadow-2xl font-bold flex items-center gap-2 z-50">
                <i class='bx bx-brightness-half text-xl'></i>
                <span id="smart-guidance-text">Foto terlalu gelap, mohon cari tempat terang!</span>
            </div>
            @endif

        </div>
        </div>
    </div>
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

        // Hilangkan atribut capture (izinkan galeri) HANYA untuk KTP
        if(galleryBtn) {
            galleryBtn.addEventListener('click', function() {
                ktpInput.removeAttribute('capture');
                // faceInput TETAP dibiarkan agar wajib kamera depan
                Swal.fire({
                    icon: 'info',
                    title: 'Galeri KTP Diaktifkan',
                    text: 'Anda sekarang bisa memilih foto KTP dari memori HP. Namun, Selfie tetap wajib menggunakan kamera langsung.',
                    timer: 3000,
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

        // Webcam Live Logic
        const startCameraBtn = document.getElementById('start-camera-btn');
        const cameraLiveView = document.getElementById('camera-live-view');
        const cameraStartView = document.getElementById('camera-start-view');
        const cameraResultView = document.getElementById('camera-result-view');
        const webcamVideo = document.getElementById('webcam-video');
        const takePhotoBtn = document.getElementById('take-photo-btn');
        const retakePhotoBtn = document.getElementById('retake-photo-btn');
        const selfiePreview = document.getElementById('selfie-preview');
        const snapshotCanvas = document.getElementById('snapshot-canvas');
        const snapCtx = snapshotCanvas ? snapshotCanvas.getContext('2d') : null;
        const flipCameraBtn = document.getElementById('flip-camera-btn');
        
        let stream = null;
        let useFrontCamera = true;
        let hasMultipleCameras = false;

        // Cek jumlah kamera
        async function checkCameras() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoInputs = devices.filter(device => device.kind === 'videoinput');
                if (videoInputs.length > 1) {
                    hasMultipleCameras = true;
                    flipCameraBtn.classList.remove('hidden');
                }
            } catch (err) {
                console.error("Gagal mendeteksi perangkat kamera.", err);
            }
        }
        checkCameras();

        async function initCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: (useFrontCamera ? "user" : "environment") },
                    audio: false
                });
                webcamVideo.srcObject = stream;
                cameraStartView.classList.add('hidden');
                cameraLiveView.classList.remove('hidden');
                
                // Mirror image jika front camera
                if (useFrontCamera) {
                    webcamVideo.classList.add('transform', '-scale-x-100');
                    selfiePreview.classList.add('transform', '-scale-x-100');
                } else {
                    webcamVideo.classList.remove('transform', '-scale-x-100');
                    selfiePreview.classList.remove('transform', '-scale-x-100');
                }
            } catch (err) {
                Swal.fire('Gagal!', 'Kamera tidak dapat diakses. Pastikan Anda memberi izin kamera di browser.', 'error');
            }
        }

        if(startCameraBtn) {
            startCameraBtn.addEventListener('click', initCamera);
        }

        if(flipCameraBtn) {
            flipCameraBtn.addEventListener('click', function() {
                useFrontCamera = !useFrontCamera;
                initCamera();
            });
        }

        if(takePhotoBtn) {
            takePhotoBtn.addEventListener('click', function() {
                snapshotCanvas.width = webcamVideo.videoWidth;
                snapshotCanvas.height = webcamVideo.videoHeight;
                
                // Balik canvas jika pakai front camera agar hasil jepretan tidak terbalik
                if(useFrontCamera){
                    snapCtx.translate(snapshotCanvas.width, 0);
                    snapCtx.scale(-1, 1);
                }
                
                snapCtx.drawImage(webcamVideo, 0, 0, snapshotCanvas.width, snapshotCanvas.height);
                
                const dataUrl = snapshotCanvas.toDataURL('image/jpeg', 0.9);
                selfiePreview.src = dataUrl;
                
                snapshotCanvas.toBlob(function(blob) {
                    const file = new File([blob], "selfie.jpg", { type: "image/jpeg" });
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    faceInput.files = dataTransfer.files;
                }, 'image/jpeg', 0.9);

                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }

                cameraLiveView.classList.add('hidden');
                cameraResultView.classList.remove('hidden');
            });
        }

        if(retakePhotoBtn) {
            retakePhotoBtn.addEventListener('click', function() {
                cameraResultView.classList.add('hidden');
                faceInput.value = ''; 
                initCamera(); // Langsung buka kamera lagi
            });
        }
    });
</script>
@endpush
@endsection
