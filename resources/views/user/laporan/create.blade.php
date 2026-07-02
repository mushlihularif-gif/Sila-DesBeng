@extends('layouts.user')

@section('title', 'Buat Laporan Warga')

@section('page')
    <div class="min-h-screen bg-[#f0f4f8] pt-32 pb-20 text-gray-800 relative">
        {{-- Custom Vector Abstract Background --}}
        <div class="fixed inset-0 overflow-hidden z-0 pointer-events-none" id="premium-bg">
            <canvas id="abstract-canvas" class="w-full h-full absolute inset-0"></canvas>
        </div>
        <div class="max-w-4xl mx-auto px-6 relative z-10 mb-20" data-aos="fade-up">
            <div class="bg-white/60 backdrop-blur-md border border-gray-100 rounded-2xl shadow-sm p-8 md:p-10">

                <div class="text-center mb-10">
                    <h1 class="text-3xl md:text-4xl font-bold mb-4 relative inline-block">
                        <span class="bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">Formulir</span>
                        <span class="bg-gradient-to-r from-[#115789] to-[#60a5fa] bg-clip-text text-transparent">Pengaduan</span>
                    </h1>
                    <p class="text-gray-500">
                        Sampaikan keluhan atau saran Anda dengan sopan dan jujur untuk kemajuan bersama.
                    </p>
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
                        <!-- Nama (auto-fill dari user login) -->
                        <div>
                            <label class="block font-semibold text-[#1e3a5f] mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" required value="{{ old('nama', Auth::user()->name) }}"
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800 transition-all shadow-sm @error('nama') border-red-500 @enderror"
                                placeholder="Masukkan nama Anda">
                            @error('nama')
                                <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label class="block font-semibold text-[#1e3a5f] mb-2">Kategori <span class="text-red-500">*</span></label>
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
                        <label class="block font-semibold text-[#1e3a5f] mb-2">Tujuan Pelaporan <span class="text-red-500">*</span></label>
                        <select name="tujuan_laporan" id="tujuan_laporan" required
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800 transition-all shadow-sm @error('tujuan_laporan') border-red-500 @enderror">
                            <option value="" disabled {{ old('tujuan_laporan') ? '' : 'selected' }}>Pilih tujuan laporan</option>
                            <option value="rt" {{ old('tujuan_laporan') == 'rt' ? 'selected' : '' }}>Laporkan kepada RT dan Pemerintah Desa</option>
                            <option value="rw" {{ old('tujuan_laporan') == 'rw' ? 'selected' : '' }}>Laporkan kepada RW dan Pemerintah Desa</option>
                            <option value="desa" {{ old('tujuan_laporan') == 'desa' ? 'selected' : '' }}>Laporkan kepada Pemerintah Desa Saja</option>
                        </select>
                        @error('tujuan_laporan')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Lokasi + Peta --}}
                    <div>
                        <label class="block font-semibold text-[#1e3a5f] mb-1">Lokasi Kejadian <span class="text-red-500">*</span></label>
                        <p class="text-sm text-gray-500 mb-3"> Klik pada peta untuk menentukan lokasi kejadian secara tepat.</p>

                        <div style="position:relative; z-index:0;">
                            <div id="map" style="height:420px; width:100%; border-radius:12px; border:2px solid #e5e7eb; z-index: 1;"></div>
                        </div>

                        <input type="hidden" name="latitude"    id="latitude">
                        <input type="hidden" name="longitude"   id="longitude">
                        <input type="hidden" name="nama_lokasi" id="nama_lokasi">

                        {{-- Tampilan nama lokasi hasil klik --}}
                        <div class="mt-3 p-4 rounded-xl bg-gray-50 border border-gray-200 min-h-[52px] flex items-center gap-3 transition-colors" id="lokasi-display">
                            <div>
                                <p id="lokasi-nama" class="text-gray-400 text-sm italic">Belum ada lokasi dipilih. Klik peta di atas.</p>
                                <p id="lokasi-coords" class="text-gray-500 text-xs mt-0.5 hidden"></p>
                            </div>
                        </div>
                        @error('nama_lokasi')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block font-semibold text-[#1e3a5f] mb-2">Deskripsi Laporan <span class="text-red-500">*</span></label>
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
                        <label class="block font-semibold text-[#1e3a5f] mb-2">Unggah Bukti (Opsional)</label>
                        <div class="relative group border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50 hover:bg-blue-50/50 hover:border-blue-400 transition-colors duration-300 text-center flex flex-col items-center justify-center py-10 px-6 cursor-pointer">
                            <input type="file" name="bukti" id="bukti" accept="image/jpeg,image/jpg,image/png"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10 @error('bukti') border-red-500 @enderror">
                            
                            <div class="w-16 h-16 mb-4 rounded-full bg-blue-100 text-blue-500 flex items-center justify-center group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 shadow-sm">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-[#1e3a5f] mb-1">Klik atau seret file ke sini untuk mengunggah</p>
                            <p class="text-xs text-gray-500">Format: JPG, JPEG, PNG | Maksimal 2MB</p>
                        </div>
                        @error('bukti')
                            <p class="text-red-500 text-xs mt-2 font-medium">{{ $message }}</p>
                        @enderror

                        <!-- Preview -->
                        <div id="preview-container" class="mt-4 hidden">
                            <p class="text-sm font-semibold text-[#1e3a5f] mb-2">Pratinjau Gambar:</p>
                            <div class="relative inline-block w-full">
                                <img id="preview-image" class="rounded-2xl border border-gray-200 w-full h-64 object-cover shadow-sm" alt="Preview">
                            </div>
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
                                    Laporan akan dikirim atas nama: <strong class="text-[#1e3a5f]">{{ Auth::user()->name }}</strong><br>
                                    Email: <strong class="text-[#1e3a5f]">{{ Auth::user()->email }}</strong><br>
                                    Wilayah Anda: <strong class="text-[#1e3a5f]" id="display-wilayah-user">RW {{ Auth::user()->rw ?? '-' }} / RT {{ Auth::user()->rt ?? '-' }}</strong>
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
                <span class="text-2xl">📍</span>
                <h3 class="text-[#1e3a5f] font-bold text-lg">Konfirmasi Lokasi</h3>
            </div>
            <p class="text-gray-500 text-sm mb-4">Apakah ini lokasi kejadian yang Anda maksud?</p>

            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-5">
                <p id="modal-address" class="text-gray-800 font-semibold text-sm leading-relaxed"></p>
                <p id="modal-coords"  class="text-gray-500 text-xs mt-1"></p>
            </div>

            <div class="flex gap-3">
                <button type="button" id="btn-confirm"
                    class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition text-sm shadow-sm">
                    ✅ Ya, Benar
                </button>
                <button type="button" id="btn-cancel"
                    class="flex-1 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 hover:text-red-500 text-gray-700 font-semibold rounded-xl transition text-sm">
                    ❌ Pilih Ulang
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
                "Lat: " + lat.toFixed(6) + "  •  Lng: " + lng.toFixed(6);

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
            "Lat: " + pendingLat.toFixed(6) + "  •  Lng: " + pendingLng.toFixed(6);
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
    </script>

    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initMap&loading=async">
    </script>

    <script>
        const inputBukti = document.getElementById('bukti');
        const previewContainer = document.getElementById('preview-container');
        const previewImage = document.getElementById('preview-image');

        inputBukti.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    alert('⚠️ Ukuran file terlalu besar! Maksimal 2MB');
                    this.value = '';
                    previewContainer.classList.add('hidden');
                    return;
                }

                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('⚠️ Format file tidak didukung! Gunakan JPG, JPEG, atau PNG');
                    this.value = '';
                    previewContainer.classList.add('hidden');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.add('hidden');
            }
        });
    </script>

    {{-- ===== MODAL LENGKAPI RT/RW ===== --}}
    <div id="rtrw-modal" class="fixed inset-0 items-center justify-center hidden" style="z-index: 99999; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px);">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 relative overflow-hidden">
            <div class="text-center mb-6">
                <div class="bg-blue-100 text-blue-600 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.242-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <h3 class="text-[#1e3a5f] font-bold text-xl">Lengkapi Profil Wilayah</h3>
                <p class="text-gray-500 text-sm mt-2">Bantu kami mengarahkan laporan Anda dengan tepat. Silakan pilih RW dan RT domisili Anda.</p>
            </div>

            <form id="form-update-rtrw">
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih RW <span class="text-red-500">*</span></label>
                        <select id="rw-select" required class="w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 text-gray-800">
                            <option value="">Memuat RW...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih RT <span class="text-red-500">*</span></label>
                        <select id="rt-select" required class="w-full px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 text-gray-800" disabled>
                            <option value="">Pilih RW Terlebih Dahulu</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" id="btn-save-rtrw" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition text-sm shadow-sm flex items-center justify-center gap-2">
                        <span>Simpan Profil</span>
                    </button>
                    <button type="button" id="btn-skip-rtrw" class="w-full py-2.5 bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 font-semibold rounded-xl transition text-sm">
                        RW/RT Saya Belum Ada di Opsi Pilihan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== SCRIPT RT/RW MODAL ===== --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const isRtRwEmpty = {{ empty(Auth::user()->rw) || empty(Auth::user()->rt) ? 'true' : 'false' }};
        const modal = document.getElementById('rtrw-modal');
        const rwSelect = document.getElementById('rw-select');
        const rtSelect = document.getElementById('rt-select');
        const formUpdate = document.getElementById('form-update-rtrw');
        const btnSkip = document.getElementById('btn-skip-rtrw');
        const tujuanLaporanSelect = document.getElementById('tujuan_laporan');
        const displayWilayah = document.getElementById('display-wilayah-user');
        
        let allRegions = [];
        // User's village id
        const userDesaId = {{ Auth::user()->region_id ?? 'null' }};

        if (isRtRwEmpty) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Lock background scroll
            document.body.style.overflow = 'hidden';

            // Fetch regions
            fetch('/api/regions')
                .then(res => res.json())
                .then(data => {
                    allRegions = data;
                    
                    // Filter RW based on desa id
                    const rws = data.filter(d => d.type === 'rw' && d.parent_id == userDesaId);
                    
                    rwSelect.innerHTML = '<option value="">Pilih RW</option>';
                    rws.forEach(rw => {
                        rwSelect.innerHTML += `<option value="${rw.id}">${rw.name}</option>`;
                    });
                })
                .catch(err => {
                    console.error('Error fetching regions:', err);
                    rwSelect.innerHTML = '<option value="">Gagal memuat data</option>';
                });
                
            // When RW changes, load RT
            rwSelect.addEventListener('change', function() {
                const rwId = this.value;
                if(rwId) {
                    const rts = allRegions.filter(d => d.type === 'rt' && d.parent_id == rwId);
                    rtSelect.innerHTML = '<option value="">Pilih RT</option>';
                    rts.forEach(rt => {
                        rtSelect.innerHTML += `<option value="${rt.id}">${rt.name}</option>`;
                    });
                    rtSelect.disabled = false;
                } else {
                    rtSelect.innerHTML = '<option value="">Pilih RW Terlebih Dahulu</option>';
                    rtSelect.disabled = true;
                }
            });
        }

        // Handle skip button
        btnSkip.addEventListener('click', function() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';

            // Lock tujuan_laporan to Desa only
            Array.from(tujuanLaporanSelect.options).forEach(opt => {
                if(opt.value === 'rt' || opt.value === 'rw') {
                    opt.style.display = 'none';
                    opt.disabled = true;
                }
            });
            tujuanLaporanSelect.value = 'desa';
            
            alert('Karena RW/RT Anda belum diisi atau belum terdaftar, opsi tujuan laporan akan dikunci agar diteruskan langsung ke Pemerintah Desa.');
        });

        // Handle Form Submit (AJAX)
        formUpdate.addEventListener('submit', function(e) {
            e.preventDefault();
            const btnSave = document.getElementById('btn-save-rtrw');
            
            if(!rwSelect.value || !rtSelect.value) {
                alert('Silakan lengkapi pilihan RW dan RT');
                return;
            }

            btnSave.disabled = true;
            btnSave.innerHTML = 'Menyimpan...';

            fetch('{{ route('profile.update-rtrw') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    rw_id: rwSelect.value,
                    region_id: rtSelect.value
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.style.overflow = 'auto';
                    
                    // Update display text
                    const rwName = rwSelect.options[rwSelect.selectedIndex].text.replace(/RW\s+/i, '');
                    const rtName = rtSelect.options[rtSelect.selectedIndex].text.replace(/RT\s+/i, '');
                    displayWilayah.innerHTML = `RW ${rwName} / RT ${rtName}`;
                    
                    alert('Profil RT/RW berhasil disimpan. Silakan lanjutkan pelaporan.');
                } else {
                    alert(data.message || 'Gagal menyimpan data.');
                    btnSave.disabled = false;
                    btnSave.innerHTML = 'Simpan Profil';
                }
            })
            .catch(err => {
                console.error('Error saving profile:', err);
                alert('Terjadi kesalahan jaringan saat menyimpan.');
                btnSave.disabled = false;
                btnSave.innerHTML = 'Simpan Profil';
            });
        });
    });
    </script>
@endsection

@push('scripts')
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
