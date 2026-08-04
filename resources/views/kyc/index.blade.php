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
                    <div id="step-1-indicator" class="w-10 h-10 shrink-0 mx-[-1px] bg-blue-500 p-1.5 flex items-center justify-center rounded-full text-white font-bold transition-colors">1</div>
                    <div class="w-full h-1 bg-gray-200" id="line-1"></div>
                    <div id="step-2-indicator" class="w-10 h-10 shrink-0 mx-[-1px] bg-gray-200 p-1.5 flex items-center justify-center rounded-full text-gray-600 font-bold transition-colors">2</div>
                </div>
            </div>

            <!-- Step 1: Upload KTP -->
            <div id="step-1">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Langkah 1: Unggah Foto KTP</h3>
                <p class="text-sm text-gray-600 mb-6">Pastikan foto KTP terlihat jelas, terang, dan teks dapat terbaca.</p>
                
                <form id="form-ktp" enctype="multipart/form-data" data-turbo="false" action="javascript:void(0)">
                    @csrf
                    <div class="mt-1 flex flex-col items-center justify-center p-6 border-2 border-gray-300 border-dashed rounded-xl relative hover:bg-gray-50 hover:border-gray-400 transition-all cursor-pointer group text-center overflow-hidden" id="drop-zone">
                        <div class="space-y-1 text-center w-full" id="drop-zone-text">
                            <div class="mx-auto w-16 h-16 rounded-full bg-white flex items-center justify-center mb-3 shadow-sm text-slate-500 border border-slate-200 group-hover:scale-110 transition-transform duration-300">
                                <svg class="h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                            <div class="flex flex-col text-center">
                                <label for="ktp_image" class="relative cursor-pointer">
                                    <span class="font-bold text-gray-700 text-base">Pilih dari Penyimpanan</span>
                                    <input id="ktp_image" name="ktp_image" type="file" class="hidden" accept="image/*">
                                </label>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG up to 5MB</p>
                            </div>
                        </div>
                        <img id="ktp-preview" class="hidden max-h-48 mx-auto rounded-lg shadow-sm" src="" alt="Preview KTP">
                    </div>

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
                    </p>
                </div>

                <div class="relative w-full max-w-sm mx-auto overflow-hidden rounded-2xl bg-gray-100 shadow-inner" style="aspect-ratio: 3/4;">
                    <!-- Video Stream -->
                    <video id="webcam" class="absolute w-full h-full object-cover transform -scale-x-100" autoplay playsinline></video>
                    <!-- Overlay Canvas -->
                    <canvas id="output_canvas" class="absolute w-full h-full object-cover transform -scale-x-100 pointer-events-none"></canvas>
                    
                    <!-- Liveness Overlay -->
                    <div class="absolute inset-0 border-[20px] border-white/50 rounded-2xl pointer-events-none z-10 flex flex-col items-center justify-between p-4">
                        <div id="liveness-instruction" class="bg-black/70 text-white px-4 py-2 rounded-full font-bold text-sm text-center shadow-lg mt-4 animate-bounce">
                            Memuat Kamera...
                        </div>

                        <!-- Visual Animation Container -->
                        <div id="liveness-animation" class="mt-4 mb-auto text-white">
                            <!-- SVG Placeholder for Animations -->
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

                <div class="mt-8 flex justify-center space-x-4">
                    <button type="button" id="btn-back-step-1" class="py-2.5 px-6 border border-gray-300 rounded-full shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Kembali
                    </button>
                    <button type="button" id="btn-submit-kyc" disabled class="py-2.5 px-6 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 transition cursor-not-allowed">
                        Kirim Data
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Loading -->
<div id="loading-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
    <div class="bg-white p-8 rounded-2xl shadow-2xl relative z-10 flex flex-col items-center">
        <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mb-4"></div>
        <p class="text-lg font-bold text-gray-800" id="loading-text">Memproses Data KTP...</p>
        <p class="text-sm text-gray-500 mt-2 text-center max-w-xs">AI kami sedang membaca KTP Anda. Mohon tunggu sebentar.</p>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- MediaPipe Scripts -->
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js" crossorigin="anonymous"></script>

<script>
(function() {
    function initKycPage() {
        // Pastikan kita ada di halaman KYC
        if (!document.getElementById('form-ktp')) return;
        
        // --- SweetAlert Toast Configuration (Lazy loaded to prevent Turbo race conditions) ---
        const Toast = {
            fire: function(options) {
                if (typeof Swal !== 'undefined') {
                    Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    }).fire(options);
                } else {
                    alert(options.title); // Fallback
                }
            }
        };

        // --- Intersection Observer for Entrance Animations ---
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-section').forEach((el) => {
            observer.observe(el);
        });

        let currentKycId = null;
        let faceSnapshot = null;
        
        // --- Step 1: KTP Upload ---
        const fileInput = document.getElementById('ktp_image');
        const dropZoneText = document.getElementById('drop-zone-text');
        const previewImage = document.getElementById('ktp-preview');
        const dropZone = document.getElementById('drop-zone');

        // Memicu klik file input ketika area dropZone diklik
        dropZone.addEventListener('click', (e) => {
            // Cegah double-trigger jika user mengklik label atau isi di dalam label
            if (e.target.closest('label') || e.target.tagName.toLowerCase() === 'input') {
                return;
            }
            fileInput.click();
        });

    // Mencegah file input diklik dua kali jika label di dalamnya diklik
    fileInput.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Menangani Event Drag and Drop
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('bg-blue-50', 'border-blue-400');
    });

    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('bg-blue-50', 'border-blue-400');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('bg-blue-50', 'border-blue-400');
        
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            fileInput.files = e.dataTransfer.files;
            
            // Panggil event 'change' secara manual untuk memperbarui preview
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }
    });

    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewImage.classList.remove('hidden');
                dropZoneText.classList.add('hidden');
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    document.getElementById('btn-process-ktp').addEventListener('click', async function(e) {
        e.preventDefault();
        
        const form = document.getElementById('form-ktp');
        const formData = new FormData(form);
        
        if(!fileInput.files[0]) {
            Toast.fire({
                icon: 'error',
                title: 'Silakan pilih foto KTP terlebih dahulu'
            });
            return;
        }

        const btn = document.getElementById('btn-process-ktp');
        const modal = document.getElementById('loading-modal');
        
        btn.disabled = true;
        modal.classList.remove('hidden');

        try {
            const response = await fetch('{{ route('kyc.process') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                currentKycId = data.kyc_id;
                
                // Show notification showing the extracted data (optional)
                // Populate Step 1.5 Editable Inputs
                document.getElementById('edit-nik').value = data.ocr_data.nik || '';
                document.getElementById('edit-nama').value = data.ocr_data.name || '';
                let alamatText = data.ocr_data.address || '';
                if (data.ocr_data.rt) alamatText += ' RT ' + data.ocr_data.rt;
                if (data.ocr_data.rw) alamatText += ' RW ' + data.ocr_data.rw;
                if (data.ocr_data.desa) alamatText += ', ' + data.ocr_data.desa;
                if (data.ocr_data.kecamatan) alamatText += ', ' + data.ocr_data.kecamatan;
                document.getElementById('edit-alamat').value = alamatText;

                if(!data.ocr_data.nik) {
                    Swal.fire({
                        title: 'Teks Tidak Terbaca',
                        text: 'Sistem OCR kesulitan membaca KTP Anda. Silakan periksa gambar KTP atau isi data secara manual di form berikutnya.',
                        icon: 'info',
                        timer: 4000
                    });
                }

                // Pindah ke step 1.5
                document.getElementById('step-1').classList.add('hidden');
                document.getElementById('step-1-half').classList.remove('hidden');
            } else {
                Toast.fire({
                    icon: 'error',
                    title: data.message || 'Terjadi kesalahan saat membaca KTP.'
                });
            }
        } catch (error) {
            Toast.fire({
                icon: 'error',
                title: 'Terjadi kesalahan server.'
            });
            console.error(error);
        } finally {
            btn.disabled = false;
            modal.classList.add('hidden');
        }
    });

    document.getElementById('btn-reupload-ktp').addEventListener('click', () => {
        document.getElementById('step-1-half').classList.add('hidden');
        document.getElementById('step-1').classList.remove('hidden');
    });

    document.getElementById('btn-confirm-ktp').addEventListener('click', () => {
        document.getElementById('step-1-half').classList.add('hidden');
        document.getElementById('step-2').classList.remove('hidden');
        
        document.getElementById('step-2-indicator').classList.remove('bg-gray-200', 'text-gray-600');
        document.getElementById('step-2-indicator').classList.add('bg-blue-500', 'text-white');
        document.getElementById('line-1').classList.replace('bg-gray-200', 'bg-blue-500');

        startLivenessDetection();
    });

    document.getElementById('btn-back-step-1').addEventListener('click', () => {
        document.getElementById('step-2').classList.add('hidden');
        document.getElementById('step-1-half').classList.remove('hidden');
        
        document.getElementById('step-2-indicator').classList.add('bg-gray-200', 'text-gray-600');
        document.getElementById('step-2-indicator').classList.remove('bg-blue-500', 'text-white');
        document.getElementById('line-1').classList.replace('bg-blue-500', 'bg-gray-200');

        if (camera) {
            camera.stop();
        }
    });

    // --- Step 2: Liveness Detection (MediaPipe) ---
    const videoElement = document.getElementById('webcam');
    const canvasElement = document.getElementById('output_canvas');
    const canvasCtx = canvasElement.getContext('2d');
    
    const instructionEl = document.getElementById('liveness-instruction');
    const statusEl = document.getElementById('liveness-status');
    const submitBtn = document.getElementById('btn-submit-kyc');

    let camera = null;
    let faceMesh = null;
    
    // Liveness State
    let livenessState = 0; // 0: Tunggu Hadap Depan, 1: Tunggu Kedip, 2: Tunggu Toleh Kanan/Kiri, 3: Selesai
    let collectedFaceData = []; // Array of EAR and head pose data
    let blinkThreshold = 0.25;
    let headTurnThreshold = 0.05; 

    function calculateEAR(landmarks, eyeIndices) {
        // Simple Eye Aspect Ratio calculation
        // eyeIndices: [p1, p2, p3, p4, p5, p6]
        const p2_p6 = Math.hypot(landmarks[eyeIndices[1]].x - landmarks[eyeIndices[5]].x, landmarks[eyeIndices[1]].y - landmarks[eyeIndices[5]].y);
        const p3_p5 = Math.hypot(landmarks[eyeIndices[2]].x - landmarks[eyeIndices[4]].x, landmarks[eyeIndices[2]].y - landmarks[eyeIndices[4]].y);
        const p1_p4 = Math.hypot(landmarks[eyeIndices[0]].x - landmarks[eyeIndices[3]].x, landmarks[eyeIndices[0]].y - landmarks[eyeIndices[3]].y);
        
        return (p2_p6 + p3_p5) / (2.0 * p1_p4);
    }

    // Base SVG Defs for the 3D Tanjak AI Robot
    const svgDefs = `
        <defs>
            <radialGradient id="bodyGrad" cx="40%" cy="30%" r="60%">
                <stop offset="0%" stop-color="#ffffff"/>
                <stop offset="70%" stop-color="#e2e8f0"/>
                <stop offset="100%" stop-color="#94a3b8"/>
            </radialGradient>
            <linearGradient id="visorGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#0a1128"/>
                <stop offset="100%" stop-color="#1e293b"/>
            </linearGradient>
            <linearGradient id="blueGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#3b82f6"/>
                <stop offset="100%" stop-color="#1d4ed8"/>
            </linearGradient>
            <linearGradient id="yellowGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#fde047"/>
                <stop offset="100%" stop-color="#ca8a04"/>
            </linearGradient>
        </defs>
    `;

    const svgBody = `
        <!-- Left Ear (Headphone) -->
        <rect x="18" y="55" width="12" height="30" rx="4" fill="url(#blueGrad)" transform="rotate(15, 24, 70)" />
        <circle cx="20" cy="70" r="14" fill="#0f172a" />
        <circle cx="20" cy="70" r="12" fill="url(#blueGrad)" />
        <circle cx="20" cy="70" r="6" fill="#38bdf8" />
        
        <!-- Right Ear (Headphone) -->
        <rect x="90" y="55" width="12" height="30" rx="4" fill="url(#blueGrad)" transform="rotate(-15, 96, 70)" />
        <circle cx="100" cy="70" r="14" fill="#0f172a" />
        <circle cx="100" cy="70" r="12" fill="url(#blueGrad)" />
        <circle cx="100" cy="70" r="6" fill="#38bdf8" />
        
        <!-- Mic Boom (Right side) -->
        <path d="M 100 80 Q 95 100 80 95" fill="none" stroke="url(#blueGrad)" stroke-width="4" stroke-linecap="round" />
        <rect x="74" y="92" width="8" height="6" rx="3" fill="url(#yellowGrad)" transform="rotate(-20, 78, 95)" />

        <!-- Main Body -->
        <circle cx="60" cy="65" r="38" fill="url(#bodyGrad)" stroke="#cbd5e1" stroke-width="1"/>
        
        <!-- Visor (Face Screen) -->
        <rect x="30" y="48" width="60" height="42" rx="21" fill="url(#visorGrad)" stroke="#334155" stroke-width="2"/>
        
        <!-- Tanjak (Headgear) -->
        <path d="M 35 45 C 50 20, 65 10, 80 5 C 75 25, 80 40, 85 45 Z" fill="url(#blueGrad)" />
        <path d="M 45 42 C 60 30, 75 20, 85 20 C 82 30, 85 38, 88 45 Z" fill="url(#yellowGrad)" />
        <path d="M 24 50 Q 60 38 96 50 L 92 40 Q 60 28 28 40 Z" fill="url(#yellowGrad)" />
    `;

    // Animations - Tanjak AI Assistant
    const animDepan = \`<svg class="w-32 h-32 mx-auto drop-shadow-2xl" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
        \${svgDefs}
        <g class="animate-[bounce_2s_infinite]">
            \${svgBody}
            <!-- Mouth -->
            <path d="M 54 78 Q 60 85 66 78 Z" fill="url(#yellowGrad)" />
            <!-- Eyes -->
            <g class="animate-pulse">
                <ellipse cx="45" cy="62" rx="7" ry="10" fill="#00ffff" />
                <circle cx="42" cy="58" r="2.5" fill="#ffffff" />
                <ellipse cx="75" cy="62" rx="7" ry="10" fill="#00ffff" />
                <circle cx="72" cy="58" r="2.5" fill="#ffffff" />
            </g>
        </g>
    </svg>\`;
    
    const animKedip = \`<svg class="w-32 h-32 mx-auto drop-shadow-2xl" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
        <style>
            @keyframes aiBlink { 0%, 70%, 100% { transform: scaleY(1); } 85% { transform: scaleY(0.1); } }
            .eye-blink { transform-origin: 60px 62px; animation: aiBlink 1.5s infinite; }
        </style>
        \${svgDefs}
        <g class="animate-[bounce_2s_infinite]">
            \${svgBody}
            <!-- Mouth -->
            <path d="M 54 78 Q 60 85 66 78 Z" fill="url(#yellowGrad)" />
            <!-- Eyes -->
            <g class="eye-blink">
                <ellipse cx="45" cy="62" rx="7" ry="10" fill="#00ffff" />
                <circle cx="42" cy="58" r="2.5" fill="#ffffff" />
                <ellipse cx="75" cy="62" rx="7" ry="10" fill="#00ffff" />
                <circle cx="72" cy="58" r="2.5" fill="#ffffff" />
            </g>
        </g>
    </svg>\`;
    
    const animKananKiri = \`<svg class="w-32 h-32 mx-auto drop-shadow-2xl" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
        <style>
            @keyframes aiLook { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-12px); } 75% { transform: translateX(12px); } }
            .eye-look { animation: aiLook 2s infinite ease-in-out; }
        </style>
        \${svgDefs}
        <g class="animate-[bounce_2s_infinite]">
            \${svgBody}
            <!-- Mouth -->
            <path d="M 56 78 Q 60 82 64 78 Z" fill="url(#yellowGrad)" />
            <!-- Eyes -->
            <g class="eye-look">
                <ellipse cx="45" cy="62" rx="7" ry="10" fill="#00ffff" />
                <circle cx="42" cy="58" r="2.5" fill="#ffffff" />
                <ellipse cx="75" cy="62" rx="7" ry="10" fill="#00ffff" />
                <circle cx="72" cy="58" r="2.5" fill="#ffffff" />
            </g>
        </g>
    </svg>\`;
    
    const animSelesai = \`<svg class="w-32 h-32 mx-auto drop-shadow-2xl" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
        \${svgDefs}
        <g class="animate-bounce">
            \${svgBody}
            <!-- Happy Mouth -->
            <path d="M 50 76 Q 60 88 70 76 Z" fill="url(#yellowGrad)" />
            <!-- Happy Eyes -->
            <path d="M 38 64 Q 45 54 52 64" fill="none" stroke="#4ade80" stroke-width="5" stroke-linecap="round" />
            <path d="M 68 64 Q 75 54 82 64" fill="none" stroke="#4ade80" stroke-width="5" stroke-linecap="round" />
        </g>
    </svg>\`;
    const animContainer = document.getElementById('liveness-animation');

    let frameFront = null;
    let frameBlink = null;
    let frameTurn = null;
    let isBuildingCollage = false;

    async function buildCollage(src1, src2, src3) {
        const loadImage = (src) => {
            return new Promise((resolve) => {
                const img = new Image();
                img.onload = () => resolve(img);
                img.src = src;
            });
        };
        
        const img1 = await loadImage(src1);
        const img2 = await loadImage(src2);
        const img3 = await loadImage(src3);
        
        const width = img1.width;
        const height = img1.height;
        
        const canvas = document.createElement('canvas');
        canvas.width = width * 3;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        
        // Jika kamera pakai mirror flip (scaleX -1), gambar asli sebenarnya tidak terbalik
        // Tapi mari kita buat kolase tetap seperti apa adanya
        ctx.drawImage(img1, 0, 0);
        ctx.drawImage(img2, width, 0);
        ctx.drawImage(img3, width * 2, 0);
        
        // Tambahkan label pita hitam transparan di bawah
        ctx.fillStyle = "rgba(0,0,0,0.6)";
        ctx.fillRect(0, height - 50, canvas.width, 50);
        
        ctx.fillStyle = "white";
        ctx.font = "bold 24px Arial";
        ctx.textAlign = "center";
        ctx.fillText("HADAP DEPAN", width/2, height - 18);
        ctx.fillText("MATA KEDIP", width + width/2, height - 18);
        ctx.fillText("TOLEH KANAN/KIRI", width*2 + width/2, height - 18);
        
        // Kompres 70% agar base64 tidak terlalu besar saat di-upload
        return canvas.toDataURL("image/jpeg", 0.7);
    }

    function onResults(results) {
        canvasCtx.save();
        canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
        
        if (results.multiFaceLandmarks && results.multiFaceLandmarks.length > 0) {
            const landmarks = results.multiFaceLandmarks[0];
            
            // Draw face mesh
            drawConnectors(canvasCtx, landmarks, FACEMESH_TESSELATION, {color: '#C0C0C070', lineWidth: 1});
            
            // Calculate Head Pose (roughly using nose tip and side of face)
            const noseTip = landmarks[1];
            const leftCheek = landmarks[234];
            const rightCheek = landmarks[454];
            
            // Normalize nose x position relative to cheeks
            const faceWidth = Math.hypot(rightCheek.x - leftCheek.x, rightCheek.y - leftCheek.y);
            const noseTurn = (noseTip.x - leftCheek.x) / faceWidth;
            // noseTurn approx 0.5 is facing forward. < 0.3 is turning right, > 0.7 is turning left
            
            // Calculate EAR for both eyes
            const leftEyeIndices = [33, 160, 158, 133, 153, 144];
            const rightEyeIndices = [362, 385, 387, 263, 373, 380];
            const leftEAR = calculateEAR(landmarks, leftEyeIndices);
            const rightEAR = calculateEAR(landmarks, rightEyeIndices);
            const avgEAR = (leftEAR + rightEAR) / 2;

            collectedFaceData.push({ ear: avgEAR, pose: noseTurn, time: Date.now() });
            if (collectedFaceData.length > 50) collectedFaceData.shift(); // Keep last 50 frames

            // State Machine
            if (livenessState === 0) {
                instructionEl.innerText = "Hadap ke Depan";
                statusEl.innerHTML = `<svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg> Analisa Wajah...`;
                if(animContainer.innerHTML !== animDepan) animContainer.innerHTML = animDepan;
                
                if (noseTurn > 0.4 && noseTurn < 0.6) {
                    // Capture snapshot Depan
                    if(!frameFront) frameFront = canvasElement.toDataURL("image/jpeg", 0.9);
                    livenessState = 1;
                }
            } 
            else if (livenessState === 1) {
                instructionEl.innerText = "Kedipkan Mata Anda";
                statusEl.innerHTML = `<svg class="w-4 h-4 inline-block mr-1 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menunggu Kedipan...`;
                if(animContainer.innerHTML !== animKedip) animContainer.innerHTML = animKedip;
                
                if (avgEAR < blinkThreshold) {
                    // Capture snapshot Kedip
                    if(!frameBlink) frameBlink = canvasElement.toDataURL("image/jpeg", 0.9);
                    livenessState = 2;
                }
            } 
            else if (livenessState === 2) {
                instructionEl.innerText = "Tolehkan Kepala ke Kanan/Kiri";
                statusEl.innerHTML = `<svg class="w-4 h-4 inline-block mr-1 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menunggu Gerakan...`;
                if(animContainer.innerHTML !== animKananKiri) animContainer.innerHTML = animKananKiri;
                
                if (noseTurn < 0.35 || noseTurn > 0.65) {
                    // Capture snapshot Toleh
                    if(!frameTurn) frameTurn = canvasElement.toDataURL("image/jpeg", 0.9);
                    livenessState = 3;
                }
            }
            else if (livenessState === 3) {
                instructionEl.innerHTML = `<svg class="w-5 h-5 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> Terverifikasi!`;
                instructionEl.classList.replace('bg-black/70', 'bg-green-600');
                statusEl.innerHTML = `<svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg> Sukses!`;
                statusEl.classList.replace('bg-blue-600', 'bg-green-600');
                if(animContainer.innerHTML !== animSelesai) animContainer.innerHTML = animSelesai;
                
                // Build Collage from the 3 frames
                if (!faceSnapshot && frameFront && frameBlink && frameTurn && !isBuildingCollage) {
                    isBuildingCollage = true;
                    statusEl.innerHTML = `<span class="animate-pulse">Menyusun Kolase Foto...</span>`;
                    
                    buildCollage(frameFront, frameBlink, frameTurn).then(collageData => {
                        faceSnapshot = collageData;
                        statusEl.innerHTML = `Siap Dikirim!`;
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('cursor-not-allowed');
                        
                        // Stop camera once verified to save resources
                        setTimeout(() => {
                            if(camera) camera.stop();
                        }, 500);
                    });
                }
            }

        } else {
            instructionEl.innerText = "Wajah Tidak Terdeteksi";
            statusEl.innerHTML = `<svg class="w-4 h-4 inline-block mr-1 text-red-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg> Posisi Wajah di Tengah`;
        }
        canvasCtx.restore();
    }

    function startLivenessDetection() {
        if (!faceMesh) {
            faceMesh = new FaceMesh({locateFile: (file) => {
                return `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`;
            }});
            faceMesh.setOptions({
                maxNumFaces: 1,
                refineLandmarks: true,
                minDetectionConfidence: 0.5,
                minTrackingConfidence: 0.5
            });
            faceMesh.onResults(onResults);
        }

        // Coba akses kamera terlebih dahulu sebelum memulai MediaPipe Camera
        navigator.mediaDevices.getUserMedia({ video: true })
            .then((stream) => {
                // Hentikan stream testing
                stream.getTracks().forEach(track => track.stop());
                
                camera = new Camera(videoElement, {
                    onFrame: async () => {
                        canvasElement.width = videoElement.videoWidth;
                        canvasElement.height = videoElement.videoHeight;
                        await faceMesh.send({image: videoElement});
                    },
                    width: 480,
                    height: 640
                });
                camera.start();
                
                livenessState = 0;
                submitBtn.disabled = true;
            })
            .catch((err) => {
                // Kamera gagal dimuat (tidak ada izin, tidak ada kamera, atau error HTTPS)
                Swal.fire({
                    icon: 'error',
                    title: 'Kamera Bermasalah',
                    text: 'Sistem tidak dapat mengakses kamera Anda untuk Scan Wajah Cerdas. Anda akan dialihkan ke halaman Verifikasi Manual.',
                    confirmButtonText: 'Lanjut ke Manual',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('user.verifikasi.index') }}";
                    }
                });
            });
    }

    // --- Submit Verifikasi Identitas ---
    submitBtn.addEventListener('click', async function() {
        this.disabled = true;
        this.innerHTML = "Mengirim...";
        
        try {
            const response = await fetch('{{ route('kyc.submit') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    kyc_id: currentKycId,
                    face_data: collectedFaceData,
                    face_image: faceSnapshot,
                    edited_nik: document.getElementById('edit-nik').value,
                    edited_nama: document.getElementById('edit-nama').value,
                    edited_alamat: document.getElementById('edit-alamat').value
                })
            });

            const data = await response.json();
            if (data.success) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'Lanjut ke Beranda'
                }).then(() => {
                    window.location.href = '{{ route('beranda') }}';
                });
            } else {
                Toast.fire({
                    icon: 'error',
                    title: data.message
                });
                this.disabled = false;
                this.innerHTML = "Kirim Data";
            }
        } catch (error) {
            Toast.fire({
                icon: 'error',
                title: 'Terjadi kesalahan server.'
            });
            console.error(error);
            this.disabled = false;
            this.innerHTML = "Kirim Data";
        }
        });
    }

    // --- Event Listeners untuk Inisialisasi ---
    document.addEventListener('DOMContentLoaded', initKycPage);
    document.addEventListener('turbo:load', initKycPage);
    
    // Jalankan langsung jika DOM sudah siap
    if (document.readyState !== 'loading') {
        initKycPage();
    }
})();
</script>
@endpush
