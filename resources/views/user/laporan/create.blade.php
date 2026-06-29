@extends('layouts.user')

@section('title', 'Buat Laporan Warga')

@section('page')
    <div class="min-h-screen bg-[#f0f4f8] pt-32 pb-20 text-gray-800 relative" style="background: #f0f4f8 url('{{ asset("Admin/img/elements/background.png") }}') no-repeat center center fixed; background-size: cover;">
        <div class="max-w-4xl mx-auto px-6 relative z-10" data-aos="fade-up">
            <div class="bg-white/95 backdrop-blur-md border border-gray-100 rounded-3xl shadow-xl p-8 md:p-10">

                <div class="text-center mb-10">
                    <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-[#115789] to-blue-300 bg-clip-text text-transparent relative inline-block drop-shadow-[0_0_15px_rgba(59,130,246,0.5)] mb-4">Formulir Pengaduan</h1>
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
                    <div class="p-5 border-2 border-dashed border-gray-200 rounded-2xl bg-gray-50/50 hover:bg-blue-50/50 transition-colors">
                        <label class="block font-semibold text-[#1e3a5f] mb-2">Unggah Bukti (Opsional)</label>
                        <input type="file" name="bukti" id="bukti" accept="image/jpeg,image/jpg,image/png"
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 transition-colors cursor-pointer @error('bukti') border-red-500 @enderror">
                        <small class="text-gray-400 block mt-2">Format: JPG, JPEG, PNG | Maksimal 2MB</small>
                        @error('bukti')
                            <p class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</p>
                        @enderror

                        <!-- Preview -->
                        <div id="preview-container" class="mt-4 hidden">
                            <p class="text-sm font-medium text-gray-600 mb-2">Pratinjau Gambar:</p>
                            <img id="preview-image" class="rounded-xl border border-gray-200 w-full max-h-64 object-cover shadow-sm"
                                alt="Preview">
                        </div>
                    </div>

                    <!-- Info User dengan Avatar -->
                    <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-6">
                        <div class="flex items-center gap-5">
                            {{-- Avatar User --}}
                            @if (Auth::user()->avatar)
                                <img src="{{ asset(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}"
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
                            class="inline-flex items-center justify-center px-8 py-3 bg-white text-gray-600 font-bold border border-gray-200 rounded-xl hover:bg-gray-50 hover:text-gray-900 shadow-sm transition-all">
                            Batalkan
                        </a>
                        <button type="submit"
                            class="sd-btn-register hover:-translate-y-1 transition-transform duration-300 w-full sm:w-auto" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; font-weight: bold;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                            </svg>
                            <span>Kirim Laporan</span>
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
