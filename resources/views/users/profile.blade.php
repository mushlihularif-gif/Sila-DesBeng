@extends('layouts.user')

@section('page')
<section class="relative z-10 min-h-screen pt-40 pb-16">
    {{-- Gambar Latar Belakang dengan Overlay --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <img src="{{ asset('User/img/backgrounds/3.webp') }}" alt="Background" 
             class="absolute inset-0 w-full h-full object-cover opacity-60">
    </div>

    {{-- Overlay Gradien --}}
    <div class="absolute top-0 left-0 w-[700px] h-[550px] pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-400/50 via-blue-500/30 to-transparent rounded-br-[40%]"></div>
    </div>

    <div class="absolute bottom-0 right-0 w-[650px] h-[450px] pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-tl from-yellow-300/40 via-yellow-400/25 to-transparent rounded-tl-[40%]"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6">
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white drop-shadow-[0_4px_12px_rgba(0,0,0,0.3)] flex items-center gap-3">
                Profil Saya
                @if($user->verification_status === 'verified')
                <div class="bg-blue-500 rounded-full p-1 shadow-lg" title="Warga Terverifikasi">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                @endif
            </h1>
        </div>

        {{-- Peringatan Sukses ditangani secara global oleh AlpineJS Toast di app.blade.php --}}

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                {{-- KOLOM KIRI: Kartu Avatar (span 4 kolom) --}}
                <div class="lg:col-span-4">
                    <div class="glass-card rounded-3xl p-6 border border-white/50 shadow-lg">
                        <div class="flex flex-col items-center">
                            {{-- Avatar dengan Border Biru --}}
                            <div class="relative group">
                                <div class="w-44 h-44 rounded-full overflow-hidden border-[5px] border-blue-400 shadow-xl bg-[#D1D5DB]">
                                    <img id="avatar-preview" src="{{ $user->file ? $user->file->file_stream : '' }}" alt="Avatar" class="w-full h-full object-cover {{ $user->file ? '' : 'hidden' }}">
                                    
                                    {{-- Placeholder Ikon Pengguna SVG --}}
                                    <div id="avatar-placeholder" class="w-full h-full flex items-center justify-center {{ $user->file ? 'hidden' : '' }}">
                                        <svg class="w-24 h-24 text-white" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                        </svg>
                                    </div>
                                </div>

                                {{-- Overlay Unggah --}}
                                <div class="absolute inset-0 bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center cursor-pointer"
                                     onclick="document.getElementById('profile-input').click()">
                                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>

                                <input type="file" id="profile-input" name="profile" accept="image/jpeg,image/jpg,image/png" class="hidden">
                            </div>

                            {{-- Pilih File Button --}}
                            <button type="button" onclick="document.getElementById('profile-input').click()" 
                                    class="mt-5 px-7 py-2.5 bg-blue-500 text-white rounded-full font-semibold text-sm hover:bg-blue-600 shadow-lg transition-all duration-300 hover:shadow-xl">
                                Pilih File
                            </button>

                            {{-- Link Unduh Foto --}}
                            {{-- Tombol Hapus Foto (Ditunda) --}}
                            @if($user->file)
                            <button type="button" id="delete-photo-btn"
                               class="mt-2.5 text-red-500 hover:text-red-700 font-medium text-sm transition-colors">
                                Hapus Foto
                            </button>
                            @endif
                            <p id="upload-hint" class="mt-2.5 text-gray-600 text-xs text-center {{ $user->file ? 'hidden' : '' }}">
                                JPG, PNG (Max 8MB)
                            </p>

                            {{-- Memotong foto belum berarti menyimpannya. Tombol di dalam
                                 pemotong berbunyi "Simpan Foto", jadi sangat mudah dikira
                                 sudah selesai — padahal fotonya baru menempel di formulir
                                 dan hilang begitu halaman dimuat ulang. --}}
                            <p id="belum-tersimpan"
                               class="hidden mt-2.5 text-center text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                                Foto belum tersimpan — tekan <strong>Simpan</strong> di bawah.
                            </p>

                            <p id="client-error-profile" class="mt-2 text-sm text-red-600 text-center font-medium hidden"></p>

                            @error('profile')
                            <p class="mt-2 text-sm text-red-600 text-center font-medium">{{ $message }}</p>
                            @enderror
                            
                            {{-- Flag tersembunyi untuk penghapusan yang ditunda --}}
                            <input type="hidden" name="delete_avatar" id="delete_avatar" value="0">
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: Kartu Form (span 8 kolom) --}}
                <div class="lg:col-span-8 space-y-5">
                    
                    {{-- GAS PARTNER INFO ALERT --}}
                    @if(isset($setting) && $setting->whatsapp_number)
                    <div class="bg-indigo-50 border border-indigo-100 rounded-3xl p-5 shadow-sm flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                        <div class="bg-indigo-100 p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-indigo-900 text-lg mb-1">Bapak/Ibu Punya Warung?</h3>
                            <p class="text-indigo-700 text-sm">Ingin jualan Gas Daerah? Silakan hubungi admin kami untuk mendaftar sebagai pengecer resmi.</p>
                        </div>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $setting->whatsapp_number) }}" target="_blank" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow transition shrink-0 inline-flex items-center gap-2 text-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                            Halo Layanan
                        </a>
                    </div>
                    @endif

                    {{-- KTP DIGITAL (Fase 5) --}}
                    @if($user->verification_status === 'verified')
                    <div class="glass-card rounded-3xl p-0 border border-blue-300 shadow-[0_0_20px_rgba(59,130,246,0.3)] overflow-hidden relative group">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-blue-500/10 opacity-50 group-hover:opacity-100 transition"></div>
                        <div class="absolute top-0 right-0 p-4">
                            <img src="{{ asset('Admin/img/illustrations/isewalogo.webp') }}" class="w-16 opacity-30">
                        </div>
                        <div class="p-6">
                            <div class="flex items-center justify-between border-b border-blue-200/50 pb-3 mb-4">
                                <h3 class="text-xl font-black text-blue-900 tracking-wider">KTP DIGITAL</h3>
                                <span class="bg-blue-600 text-white text-xs px-3 py-1 rounded-full font-bold shadow">TERVERIFIKASI</span>
                            </div>
                            <div class="flex gap-6 items-center">
                                {{-- Cadangannya dulu menunjuk Admin/img/avatars/1.png, berkas
                                     yang tidak ada di public/ — sehingga warga tanpa foto profil
                                     melihat ikon gambar rusak di kartu KTP-nya. Diganti penampung
                                     yang memang dirancang untuk keadaan kosong. --}}
                                <div class="w-24 h-32 rounded-lg border-2 border-blue-300 overflow-hidden shadow-md shrink-0 bg-blue-50">
                                    @if($user->file)
                                        <img src="{{ $user->file->file_stream }}" alt="Foto profil" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center text-blue-300 gap-1">
                                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-[9px] text-center leading-tight px-1">Belum ada foto</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 space-y-2 text-sm text-blue-900 font-medium">
                                    <div class="grid grid-cols-[100px_1fr]">
                                        <span class="text-blue-700 font-bold">NIK</span>
                                        <span class="font-mono bg-blue-100 px-2 py-0.5 rounded tracking-widest">{{ $user->nik ? substr($user->nik, 0, 4) . '********' . substr($user->nik, -4) : 'Belum diisi' }}</span>
                                    </div>
                                    <div class="grid grid-cols-[100px_1fr]">
                                        <span class="text-blue-700 font-bold">NAMA</span>
                                        <span class="uppercase font-bold flex items-center gap-1">
                                            {{ $user->name }}
                                            <i class='bx bxs-badge-check text-blue-500 text-lg' title="Terverifikasi"></i>
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-[100px_1fr]">
                                        <span class="text-blue-700 font-bold">ALAMAT</span>
                                        <span>RT {{ $user->rt }}/RW {{ $user->rw }} - (Disensor)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-6 shadow-sm flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-yellow-800 text-lg mb-1">Identitas Belum Terverifikasi</h3>
                            <p class="text-yellow-700 text-sm">Anda belum dapat mengakses layanan publik (seperti meminjam fasilitas) sebelum memverifikasi KTP & Wajah.</p>
                        </div>
                        <a href="{{ route('kyc.index') }}" class="px-6 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded-xl shadow transition shrink-0">
                            Verifikasi Sekarang
                        </a>
                    </div>
                    @endif

                    {{-- KARTU 1: Info Dasar (Username, Nama, Email, Telepon) --}}
                    <div class="glass-card rounded-3xl p-6 border border-white/50 shadow-lg">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- Nama Pengguna (dapat diedit) --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-800 mb-2">Nama Pengguna</label>
                                <input type="text" name="username" value="{{ old('username', $user->username) }}" 
                                       class="w-full px-4 py-2.5 bg-white/80 border border-white/60 rounded-xl focus:border-blue-400 focus:ring-2 focus:ring-blue-200/50 outline-none transition glass-input text-gray-800 text-sm">
                                @error('username')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Nama Lengkap --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-800 mb-2">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                                       class="w-full px-4 py-2.5 bg-white/80 border border-white/60 rounded-xl focus:border-blue-400 focus:ring-2 focus:ring-blue-200/50 outline-none transition glass-input text-gray-800 text-sm">
                                @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email (dinonaktifkan) --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-800 mb-2">Email</label>
                                <input type="email" value="{{ $user->email }}" disabled 
                                       class="w-full px-4 py-2.5 bg-white/60 border border-white/40 rounded-xl text-gray-700 cursor-not-allowed glass-input text-sm">
                            </div>

                            {{-- Nomor Telepon --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-800 mb-2">Nomor Telepon</label>
                                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" 
                                       class="w-full px-4 py-2.5 bg-white/80 border border-white/60 rounded-xl focus:border-blue-400 focus:ring-2 focus:ring-blue-200/50 outline-none transition glass-input text-gray-800 text-sm">
                                @error('phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        
                        {{-- Jenis Kelamin (Full Width) --}}
                        <div class="mt-5 md:col-span-2">
                            <label class="block text-sm font-bold text-gray-800 mb-2">Jenis Kelamin</label>
                            <div class="relative">
                                <select name="gender" 
                                        class="appearance-none w-full px-4 py-2.5 pr-10 bg-white/80 border border-white/60 rounded-xl focus:border-blue-400 focus:ring-2 focus:ring-blue-200/50 outline-none transition glass-input text-gray-800 text-sm">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="laki-laki" {{ old('gender', $user->gender) == 'laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="perempuan" {{ old('gender', $user->gender) == 'perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('gender')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div> {{-- Menutup Grid Utama (Kiri & Kanan) --}}
        </div>

            {{-- KOLOM BAWAH: Detail Tambahan (Di luar grid utama agar otomatis 100% lebar) --}}
            <div class="mt-5 w-full">
                <div class="glass-card rounded-3xl p-6 border border-white/50 shadow-lg">
                    {{-- Alamat (Full Width) --}}
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-800 mb-2">Alamat Detail</label>
                        <textarea name="address" rows="2.5" placeholder="Contoh: Jl. Soekarno Hatta No. 12"
                                  class="w-full px-4 py-2.5 bg-white/80 border border-white/60 rounded-xl focus:border-blue-400 focus:ring-2 focus:ring-blue-200/50 outline-none transition resize-none glass-input text-gray-800 text-sm">{{ old('address', $user->address) }}</textarea>
                        @error('address')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Grid 2 Kolom untuk wilayah --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        {{-- Kecamatan --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Kecamatan</label>
                            <input type="text" value="{{ $kecamatan_name }}" disabled 
                                   class="w-full px-4 py-2.5 bg-white/60 border border-white/40 rounded-xl text-gray-700 cursor-not-allowed glass-input text-sm">
                        </div>

                        {{-- Desa / Kelurahan --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">Desa / Kelurahan</label>
                            <input type="text" value="{{ $desa_name }}" disabled 
                                   class="w-full px-4 py-2.5 bg-white/60 border border-white/40 rounded-xl text-gray-700 cursor-not-allowed glass-input text-sm">
                        </div>

                        {{-- RW & RT bisa disunting.
                             Kecamatan dan desa di atas memang dikunci — keduanya
                             ditetapkan lewat verifikasi KTP dan diubah lewat
                             pengajuan mutasi, bukan diketik sendiri. Tetapi RW dan
                             RT dulu ikut terkunci, padahal tidak ada jalur lain
                             untuk mengisinya: updateRtRw() menuntut RW dan RT ada
                             sebagai baris `regions`, sedangkan pohon wilayah belum
                             memuat satu pun. Akibatnya warga yang RT/RW-nya tidak
                             terbaca OCR tidak akan pernah bisa melengkapinya —
                             padahal laporan warga disalurkan berdasarkan RT/RW. --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">RW</label>
                            <input type="text" name="rw" value="{{ old('rw', $user->rw) }}"
                                   placeholder="Contoh: 005" maxlength="10"
                                   class="w-full px-4 py-2.5 bg-white/80 border border-white/60 rounded-xl focus:border-blue-400 focus:ring-2 focus:ring-blue-200/50 outline-none transition glass-input text-gray-800 text-sm">
                            @error('rw')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">RT</label>
                            <input type="text" name="rt" value="{{ old('rt', $user->rt) }}"
                                   placeholder="Contoh: 013" maxlength="10"
                                   class="w-full px-4 py-2.5 bg-white/80 border border-white/60 rounded-xl focus:border-blue-400 focus:ring-2 focus:ring-blue-200/50 outline-none transition glass-input text-gray-800 text-sm">
                            @error('rt')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Kata Sandi (Full Width) --}}
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-800 mb-2">Kata Sandi</label>
                        <input type="password" value="••••••••" disabled 
                               class="w-full px-4 py-2.5 bg-white/60 border border-white/40 rounded-xl text-gray-700 cursor-not-allowed glass-input text-sm">
                    </div>

                    {{-- 3 Tombol Aksi - Tata Letak Horizontal --}}
                    <div class="pt-4 mt-2 border-t border-white/30">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            {{-- Ubah Sandi - Button Border Putih --}}
                            <button type="button" id="btn-open-change-password"
                                    class="button-interactive py-3.5 bg-white/70 backdrop-blur-sm text-blue-500 rounded-full font-semibold text-base transition-all duration-300 shadow-md border border-white/80 hover:bg-white/90 hover:shadow-lg hover:scale-105 active:scale-95">
                                Ubah Sandi
                            </button>
                            
                            {{-- Simpan - Button Border Putih --}}
                            <button type="submit" 
                                    class="button-interactive py-3.5 bg-white/70 backdrop-blur-sm text-blue-500 rounded-full font-semibold text-base transition-all duration-300 shadow-md border border-white/80 hover:bg-white/90 hover:shadow-lg hover:scale-105 active:scale-95">
                                Simpan
                            </button>
                            
                            {{-- Keluar - Button Border Putih --}}
                            <button type="button" id="btn-open-logout-profile"
                                    class="button-interactive py-3.5 bg-white/70 backdrop-blur-sm text-red-500 rounded-full font-semibold text-base transition-all duration-300 shadow-md border border-white/80 hover:bg-white/90 hover:shadow-lg hover:scale-105 active:scale-95">
                                Keluar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- Pengajuan Mutasi / Pindah Desa --}}
        @if($user->verification_status === 'verified')
        <div class="mt-8 w-full glass-card rounded-3xl p-6 border border-white/50 shadow-lg relative overflow-hidden">
            <div class="absolute top-0 left-0 w-2 h-full bg-orange-500"></div>
            <h3 class="text-xl font-bold text-gray-800 mb-2 flex items-center gap-2">
                <i class='bx bx-transfer-alt text-orange-500'></i> Pengajuan Pindah Desa (Mutasi)
            </h3>
            
            @php
                $pendingMutasi = \App\Models\MutasiPenduduk::where('user_id', $user->id)->where('status', 'pending')->first();
            @endphp

            @if($pendingMutasi)
                <div class="bg-orange-50 border border-orange-200 text-orange-800 p-4 rounded-xl mt-4">
                    <p class="font-bold flex items-center gap-2">
                        <i class='bx bx-time-five animate-spin-slow'></i> Sedang Diproses
                    </p>
                    <p class="text-sm mt-1">Pengajuan pindah Anda ke <strong>{{ $pendingMutasi->toRegion->desa }}</strong> sedang menunggu persetujuan (Handshake) dari Kepala Desa saat ini.</p>
                </div>
            @else
                <p class="text-gray-600 text-sm mb-4">Jika Anda berpindah domisili ke desa lain, Anda bisa mengajukan perpindahan data secara digital. Kades asal harus menyetujui pelepasan data Anda.</p>
                <form action="{{ route('user.mutasi.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4" data-konfirmasi="Apakah Anda yakin ingin mengajukan pindah desa? Anda tidak dapat memesan fasilitas desa hingga proses ini selesai.">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Pilih Desa Tujuan</label>
                        <select name="to_region_id" class="w-full px-4 py-2 bg-white/80 border border-white/60 rounded-xl focus:border-orange-400 outline-none text-gray-800 text-sm" required>
                            <option value="">-- Pilih Desa --</option>
                            @foreach(\App\Models\Region::where('id', '!=', $user->region_id)->get() as $reg)
                                <option value="{{ $reg->id }}">{{ $reg->kecamatan }} - {{ $reg->desa }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Alasan Pindah</label>
                        <input type="text" name="reason" placeholder="Contoh: Ikut suami, pindah domisili, dll" class="w-full px-4 py-2 bg-white/80 border border-white/60 rounded-xl focus:border-orange-400 outline-none text-gray-800 text-sm" required>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow transition">
                            Ajukan Pindah Sekarang
                        </button>
                    </div>
                </form>
            @endif
        </div>
        @endif

        {{-- Form Logout (Tersembunyi) --}}
        <form id="logout-form" action="{{ route('auth.logout') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>
</section>

{{-- ✅ INCLUDE MODALS & SCRIPTS DARI AUTH --}}
@include('auth.profile-modals')
@endsection

@push('scripts')
@include('auth.profile-scripts')

<script>
    function initProfilePage() {
        // Kembalikan posisi scroll jika pengguna baru saja menyimpan profil
        const savedScroll = sessionStorage.getItem('profileFormSubmitScroll');
        if (savedScroll) {
            setTimeout(() => {
                window.scrollTo({ top: parseInt(savedScroll), behavior: 'instant' });
            }, 10);
            sessionStorage.removeItem('profileFormSubmitScroll');
        }

        // Simpan posisi scroll saat form disubmit
        const profileForm = document.querySelector('form[action="{{ route('profile.update') }}"]');
        if (profileForm) {
            profileForm.addEventListener('submit', function() {
                sessionStorage.setItem('profileFormSubmitScroll', window.scrollY);
            });
        }

        const alert = document.getElementById('success-alert');
        if (alert) {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }

        // Fungsionalitas Pratinjau Avatar
        const profileInput = document.getElementById('profile-input');
        const avatarPreview = document.getElementById('avatar-preview');
        const avatarPlaceholder = document.getElementById('avatar-placeholder');
        const deletePhotoBtn = document.getElementById('delete-photo-btn');
        const deleteAvatarInput = document.getElementById('delete_avatar');
        const uploadHint = document.getElementById('upload-hint');

        // Penanda, BUKAN cloneNode.
        //
        // Sebelumnya input berkas diganti salinannya setiap initProfilePage
        // berjalan, untuk mencegah pendengar ganda. Masalahnya halaman ini
        // memuat Turbo, sehingga fungsi itu berjalan pada DOMContentLoaded
        // MAUPUN turbo:load — dan cloneNode() TIDAK ikut membawa berkas yang
        // sudah dipilih. Setiap kali itu terjadi setelah foto dipilih, fotonya
        // lenyap diam-diam: pratinjau tetap terlihat karena memakai blob, tetapi
        // formulir mengirim input yang sudah kosong, jadi tidak ada yang tersimpan.
        if (profileInput && !profileInput.dataset.siap) {
            profileInput.dataset.siap = '1';

            profileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const clientErrorProfile = document.getElementById('client-error-profile');

                if (file) {
                    if (file.size > 8 * 1024 * 1024) {
                        if (clientErrorProfile) {
                            clientErrorProfile.textContent = 'Ukuran foto Anda ' + (file.size / 1024 / 1024).toFixed(2) + ' MB. Maksimal 8 MB.';
                            clientErrorProfile.classList.remove('hidden');
                        }
                        this.value = '';
                        return;
                    }
                    
                    if (typeof initGlobalCropper === 'function') {
                        initGlobalCropper(this, 'avatar-preview', 1);
                        if (avatarPlaceholder) avatarPlaceholder.classList.add('hidden');
                        if (deletePhotoBtn) deletePhotoBtn.style.display = 'inline-block';
                        if (uploadHint) uploadHint.classList.add('hidden');
                        if (clientErrorProfile) clientErrorProfile.classList.add('hidden');
                    } else {
                        console.error('Cropper is not initialized properly in layout');
                    }
                }
            });
        }

        // Tangani Tombol Hapus Foto (Ditunda)
        if (deletePhotoBtn && !deletePhotoBtn.dataset.siap) {
            deletePhotoBtn.dataset.siap = '1';

            deletePhotoBtn.addEventListener('click', function() {
                // Setel flag untuk menghapus saat disimpan
                 if(deleteAvatarInput) deleteAvatarInput.value = '1';
                
                // Bersihkan nilai input agar jika mereka mengunggah file yang sama lagi, itu memicu perubahan
                const currentProfileInput = document.getElementById('profile-input');
                if(currentProfileInput) currentProfileInput.value = '';

                // Tampilkan placeholder secara visual
                if (avatarPreview) {
                    avatarPreview.src = '';
                    avatarPreview.classList.add('hidden');
                }
                if (avatarPlaceholder) {
                    avatarPlaceholder.classList.remove('hidden');
                }
                
                newDeleteBtn.style.display = 'none';
                if(uploadHint) uploadHint.classList.remove('hidden');
                
                // Tambahkan toast notifikasi informatif
                if (typeof showToast === 'function') {
                    showToast('Silakan klik tombol "Simpan" di bawah untuk menghapus foto secara permanen', 'info');
                }
            });
        }


        // Efek Ripple Tombol
        const interactiveButtons = document.querySelectorAll('.button-interactive');
        interactiveButtons.forEach(button => {
            // Clone to remove old listeners
            const newBtn = button.cloneNode(true);
            button.parentNode.replaceChild(newBtn, button);

            newBtn.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.5)';
                ripple.style.pointerEvents = 'none';
                ripple.style.animation = 'ripple 0.6s ease-out';
                
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initProfilePage);
    document.addEventListener('turbo:load', initProfilePage);
</script>
@endpush

@push('styles')
<style>
    * {
        font-family: 'Inter', sans-serif;
    }
    
    /* Glass morphism dengan prefix webkit */
    .glass-card {
        background: rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    
    .glass-input {
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    
    /* Efek Interaktif Tombol */
    .button-interactive {
        position: relative;
        overflow: hidden;
        transform: translateZ(0);
        will-change: transform, box-shadow;
    }
    
    .button-interactive::before {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at center, rgba(255,255,255,0.3) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .button-interactive:hover::before {
        opacity: 1;
    }
    
    .button-interactive:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    
    .button-interactive:active {
        transform: translateY(0) scale(0.98);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.1s ease;
    }
    
    /* Efek Ripple saat Klik */
    @keyframes ripple {
        0% {
            transform: scale(0);
            opacity: 1;
        }
        100% {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    /* Penyesuaian Responsif */
    @media (max-width: 1024px) {
        .lg\:col-span-4 {
            grid-column: span 12;
        }
        .lg\:col-span-8 {
            grid-column: span 12;
        }
    }
</style>
@endpush
