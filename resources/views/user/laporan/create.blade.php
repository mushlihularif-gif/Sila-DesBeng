@extends('layouts.user')

@section('title', 'Buat Laporan Warga')

@section('page')
    <section
        class="min-h-screen bg-gray-50 py-16 px-8 flex flex-col justify-center items-center"
        data-aos="fade-up">
        <div class="max-w-4xl w-full bg-white border border-gray-200 rounded-3xl shadow-xl p-10">

            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6 text-center">📢 Formulir Pengaduan Warga</h1>
            <p class="text-center text-gray-600 mb-10">
                Sampaikan keluhan atau saran Anda dengan sopan dan jujur untuk kemajuan bersama.
            </p>

            <!-- Alert Success -->
            @if (session('success'))
                <div class="mb-4 bg-green-600 text-white px-4 py-2 rounded-lg text-center shadow">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <!-- Alert Error -->
            @if ($errors->any())
                <div class="mb-4 bg-red-600 text-white px-4 py-2 rounded-lg shadow">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('user.laporan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Nama (auto-fill dari user login) -->
                <div>
                    <label class="block font-semibold mb-2">Nama Lengkap <span class="text-red-400">*</span></label>
                    <input type="text" name="nama" required value="{{ old('nama', Auth::user()->name) }}"
                        class="w-full p-3 rounded-lg bg-[#004635] border border-yellow-400 focus:ring-2 focus:ring-yellow-300 text-white placeholder-gray-300 @error('nama') border-red-500 @enderror"
                        placeholder="Masukkan nama Anda">
                    @error('nama')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kategori -->
                <div>
                    <label class="block font-semibold mb-2">Kategori <span class="text-red-400">*</span></label>
                    <select name="kategori" required
                        class="w-full p-3 rounded-lg bg-[#004635] border border-yellow-400 focus:ring-2 focus:ring-yellow-300 text-white @error('kategori') border-red-500 @enderror">
                        <option value="" disabled {{ old('kategori') ? '' : 'selected' }}>Pilih kategori laporan
                        </option>
                        <option value="Kebersihan" {{ old('kategori') == 'Kebersihan' ? 'selected' : '' }}>🧹 Kebersihan
                        </option>
                        <option value="Keamanan" {{ old('kategori') == 'Keamanan' ? 'selected' : '' }}>🔒 Keamanan</option>
                        <option value="Fasilitas" {{ old('kategori') == 'Fasilitas' ? 'selected' : '' }}>🏢 Fasilitas Umum
                        </option>
                        <option value="Infrastruktur" {{ old('kategori') == 'Infrastruktur' ? 'selected' : '' }}>🏗️
                            Infrastruktur</option>
                        <option value="Lingkungan" {{ old('kategori') == 'Lingkungan' ? 'selected' : '' }}>🌳 Lingkungan
                        </option>
                        <option value="Pelayanan Publik" {{ old('kategori') == 'Pelayanan Publik' ? 'selected' : '' }}>👥
                            Pelayanan Publik</option>
                        <option value="Administrasi" {{ old('kategori') == 'Administrasi' ? 'selected' : '' }}>📋
                            Administrasi</option>
                        <option value="Lainnya" {{ old('kategori') == 'Lainnya' ? 'selected' : '' }}>📌 Lainnya</option>
                    </select>
                    @error('kategori')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lokasi -->
                <div>
                    <label class="block font-semibold mb-2">Lokasi Kejadian <span class="text-red-400">*</span></label>
                    <input type="text" name="lokasi" required value="{{ old('lokasi') }}"
                        class="w-full p-3 rounded-lg bg-[#004635] border border-yellow-400 focus:ring-2 focus:ring-yellow-300 text-white placeholder-gray-300 @error('lokasi') border-red-500 @enderror"
                        placeholder="Contoh: Jalan Mawar RT 21/RW 6">
                    @error('lokasi')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block font-semibold mb-2">Deskripsi Laporan <span class="text-red-400">*</span></label>
                    <textarea name="deskripsi" rows="4" required
                        class="w-full p-3 rounded-lg bg-[#004635] border border-yellow-400 focus:ring-2 focus:ring-yellow-300 text-white placeholder-gray-300 @error('deskripsi') border-red-500 @enderror"
                        placeholder="Jelaskan keluhan Anda dengan detail (minimal 20 karakter)...">{{ old('deskripsi') }}</textarea>
                    <small class="text-gray-400">Minimal 20 karakter</small>
                    @error('deskripsi')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bukti -->
                <div>
                    <label class="block font-semibold mb-2">Unggah Bukti (Opsional)</label>
                    <input type="file" name="bukti" id="bukti" accept="image/jpeg,image/jpg,image/png"
                        class="w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-yellow-400 file:text-[#004635] hover:file:bg-yellow-300 @error('bukti') border-red-500 @enderror">
                    <small class="text-gray-400">Format: JPG, JPEG, PNG | Maksimal 2MB</small>
                    @error('bukti')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Preview -->
                    <div id="preview-container" class="mt-4 hidden">
                        <p class="text-sm text-yellow-300 mb-2">📷 Pratinjau Gambar:</p>
                        <img id="preview-image" class="rounded-lg border border-yellow-400 w-full max-h-64 object-cover"
                            alt="Preview">
                    </div>
                </div>

                <!-- Info User dengan Avatar -->
                <div class="bg-yellow-400/10 border border-yellow-400/30 rounded-lg p-4">
                    <div class="flex items-center gap-4">
                        {{-- Avatar User --}}
                        @if (Auth::user()->avatar)
                            <img src="{{ asset(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}"
                                class="w-16 h-16 rounded-full object-cover border-2 border-yellow-400 shadow-lg flex-shrink-0">
                        @else
                            <div
                                class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center border-2 border-yellow-400 shadow-lg flex-shrink-0">
                                <span
                                    class="text-2xl font-bold text-[#004635]">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            </div>
                        @endif

                        {{-- Info User --}}
                        <div class="flex-1">
                            <p class="text-gray-300 text-sm">
                                📍 Laporan akan dikirim atas nama: <strong
                                    class="text-yellow-400">{{ Auth::user()->name }}</strong><br>
                                📧 Email: <strong class="text-yellow-400">{{ Auth::user()->email }}</strong><br>
                                📍 RW/RT Anda: <strong class="text-yellow-400">RW {{ Auth::user()->rw ?? 6 }} / RT
                                    {{ Auth::user()->rt ?? 21 }}</strong>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Tombol -->
                <div class="flex gap-4 justify-center pt-4">
                    <a href="{{ route('user.laporan.index') }}"
                        class="bg-gray-600 text-white font-bold px-8 py-3 rounded-lg shadow-md hover:bg-gray-500 transition">
                        ← Kembali
                    </a>
                    <button type="submit"
                        class="bg-yellow-400 text-[#004635] font-bold px-10 py-3 rounded-lg shadow-md hover:bg-yellow-300 transition">
                        ✉️ Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </section>

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
@endsection
