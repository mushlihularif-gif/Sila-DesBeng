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
        {{-- Judul Header --}}
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white drop-shadow-[0_4px_12px_rgba(0,0,0,0.3)]">
                Profil Saya
            </h1>
        </div>

        {{-- Peringatan Sukses --}}
        @if(session('success'))
        <div id="success-alert" class="mb-6 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-xl transition-opacity duration-300">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        </div>
        @endif

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
                                    @if($user->file)
                                        <img id="avatar-preview" src="{{ $user->file->file_stream }}" alt="Avatar" class="w-full h-full object-cover">
                                    @else
                                        <img id="avatar-preview" src="" alt="Avatar" class="w-full h-full object-cover hidden">
                                        {{-- Placeholder Ikon Pengguna SVG --}}
                                        <div id="avatar-placeholder" class="w-full h-full flex items-center justify-center">
                                            <svg class="w-24 h-24 text-white" viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                            </svg>
                                        </div>
                                    @endif
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

                        {{-- RW --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">RW</label>
                            <input type="text" value="{{ $rw_name }}" disabled 
                                   class="w-full px-4 py-2.5 bg-white/60 border border-white/40 rounded-xl text-gray-700 cursor-not-allowed glass-input text-sm">
                        </div>

                        {{-- RT --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-800 mb-2">RT</label>
                            <input type="text" value="{{ $rt_name }}" disabled 
                                   class="w-full px-4 py-2.5 bg-white/60 border border-white/40 rounded-xl text-gray-700 cursor-not-allowed glass-input text-sm">
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
    // Sembunyikan otomatis peringatan sukses setelah 5 detik
    document.addEventListener('DOMContentLoaded', function() {
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

        if (profileInput) {
            profileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const clientErrorProfile = document.getElementById('client-error-profile');

                if (file) {
                    // Validasi ukuran di sisi klien (Maks 8MB)
                    if (file.size > 8 * 1024 * 1024) {
                        if (clientErrorProfile) {
                            clientErrorProfile.textContent = 'Ukuran foto Anda ' + (file.size / 1024 / 1024).toFixed(2) + ' MB. Maksimal 8 MB.';
                            clientErrorProfile.classList.remove('hidden');
                        }
                        this.value = ''; // Reset input agar tidak terkirim

                        // Kembalikan ke tampilan awal/sebelumnya
                        if (avatarPreview) {
                            avatarPreview.src = '';
                            avatarPreview.classList.add('hidden');
                        }
                        if (avatarPlaceholder) avatarPlaceholder.classList.remove('hidden');
                        if (deletePhotoBtn) deletePhotoBtn.style.display = 'none';
                        return; // Berhenti memproses gambar
                    } else {
                        if (clientErrorProfile) clientErrorProfile.classList.add('hidden');
                    }

                    // Reset flag hapus (kita mengganti, bukan hanya menghapus)
                    if(deleteAvatarInput) deleteAvatarInput.value = '0';

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        if (avatarPreview) {
                            avatarPreview.src = event.target.result;
                            avatarPreview.classList.remove('hidden');
                        }
                        if (avatarPlaceholder) {
                            avatarPlaceholder.classList.add('hidden');
                        }
                        if(uploadHint) uploadHint.classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Tangani Tombol Hapus Foto (Ditunda)
        if (deletePhotoBtn) {
            deletePhotoBtn.addEventListener('click', function() {
                // Setel flag untuk menghapus saat disimpan
                 if(deleteAvatarInput) deleteAvatarInput.value = '1';
                
                // Bersihkan nilai input agar jika mereka mengunggah file yang sama lagi, itu memicu perubahan
                if(profileInput) profileInput.value = '';

                // Tampilkan placeholder secara visual
                if (avatarPreview) {
                    avatarPreview.src = '';
                    avatarPreview.classList.add('hidden');
                }
                if (avatarPlaceholder) {
                    avatarPlaceholder.classList.remove('hidden');
                }
                
                // Sembunyikan tombol hapus itu sendiri sebentar atau biarkan saja? 
                // Biasanya kita bisa menyembunyikannya atau mengubahnya menjadi "Batalkan". Untuk saat ini, mari kita sembunyikan saja.
                deletePhotoBtn.style.display = 'none';
                if(uploadHint) uploadHint.classList.remove('hidden');
            });
        }


        // Efek Ripple Tombol
        const interactiveButtons = document.querySelectorAll('.button-interactive');
        interactiveButtons.forEach(button => {
            button.addEventListener('click', function(e) {
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
    });
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