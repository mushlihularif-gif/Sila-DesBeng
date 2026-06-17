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
                        <select name="tujuan_laporan" required
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

                    <!-- Lokasi -->
                    <div>
                        <label class="block font-semibold text-[#1e3a5f] mb-2">Lokasi Kejadian <span class="text-red-500">*</span></label>
                        <input type="text" name="lokasi" required value="{{ old('lokasi') }}"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-800 transition-all shadow-sm @error('lokasi') border-red-500 @enderror"
                            placeholder="Contoh: Jalan Mawar RT 21/RW 6">
                        @error('lokasi')
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
                                    Wilayah Anda: <strong class="text-[#1e3a5f]">RW {{ Auth::user()->rw ?? 6 }} / RT {{ Auth::user()->rt ?? 21 }}</strong>
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
