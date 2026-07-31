@extends('layouts.user')

@section('title', 'Verifikasi KTP & Wajah')

@section('page')
<main class="flex-grow relative w-full">
    {{-- Custom Vector Abstract Background --}}
    @include('partials.abstract-bg')

    <div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8 mt-20 relative z-10">
        <div class="bg-white/60 backdrop-blur-md rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-900">Verifikasi Identitas (KYC)</h2>
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
                
                <form id="form-ktp" enctype="multipart/form-data">
                    @csrf
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md relative hover:bg-gray-50 transition cursor-pointer overflow-hidden" id="drop-zone">
                        <div class="space-y-1 text-center" id="drop-zone-text">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="ktp_image" class="relative cursor-pointer bg-transparent rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Pilih File KTP</span>
                                    <input id="ktp_image" name="ktp_image" type="file" class="hidden" accept="image/*" required>
                                </label>
                                <p class="pl-1">atau drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 5MB</p>
                        </div>
                        <img id="ktp-preview" class="hidden max-h-48 mx-auto rounded-lg shadow-sm" src="" alt="Preview KTP">
                    </div>

                    <div class="mt-6">
                        <button type="submit" id="btn-process-ktp" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-full shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- MediaPipe Scripts -->
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/face_mesh.js" crossorigin="anonymous"></script>

<script>
    let currentKycId = null;
    let faceSnapshot = null;
    
    // --- Step 1: KTP Upload ---
    const fileInput = document.getElementById('ktp_image');
    const dropZoneText = document.getElementById('drop-zone-text');
    const previewImage = document.getElementById('ktp-preview');
    const dropZone = document.getElementById('drop-zone');

    // Memicu klik file input ketika area dropZone diklik
    dropZone.addEventListener('click', () => {
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

    document.getElementById('form-ktp').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        if(!fileInput.files[0]) {
            Swal.fire('Error', 'Silakan pilih foto KTP terlebih dahulu', 'error');
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
                Swal.fire('Gagal', data.message || 'Terjadi kesalahan saat membaca KTP.', 'error');
            }
        } catch (error) {
            Swal.fire('Gagal', 'Terjadi kesalahan server.', 'error');
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

    // Animations
    const animDepan = `<svg class="w-16 h-16 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
    const animKedip = `<svg class="w-16 h-16 animate-ping" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>`;
    const animKananKiri = `<svg class="w-16 h-16 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>`;
    const animSelesai = `<svg class="w-16 h-16 text-green-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>`;
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

    // --- Submit KYC ---
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
                Swal.fire('Gagal', data.message, 'error');
                this.disabled = false;
                this.innerHTML = "Kirim Data";
            }
        } catch (error) {
            Swal.fire('Gagal', 'Terjadi kesalahan server.', 'error');
            console.error(error);
            this.disabled = false;
            this.innerHTML = "Kirim Data";
        }
    });

</script>
@endpush
